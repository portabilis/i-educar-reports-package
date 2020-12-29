<?php

trait SchoolHistoryCrosstabTrait
{
    /**
     * @inheritdoc
     */
    protected function query()
    {
        $aluno = $this->args['aluno'];
        $ano_ini = $this->args['ano_ini'];
        $ano_fim = $this->args['ano_fim'];
        $instituicao = $this->args['instituicao'];
        $escola = $this->args['escola'];
        $sequencial = $this->args['sequencial'];
        $nao_emitir_reprovado = $this->args['nao_emitir_reprovado'];
        $cursoaluno = $this->args['cursoaluno'];

        return <<<SQL
            SELECT
                ano,
                carga_horaria,
                dias_letivos,
                escola,
                escola_cidade,
                escola_uf,
                observacao,
                aprovado,
                nm_serie,
                frequencia,
                registro,
                livro,
                folha,
                ref_cod_aluno,
                historico_disciplinas.nm_disciplina,
                historico_disciplinas.nota,
                historico_escolar.carga_horaria,
                to_char(CURRENT_DATE,'dd/mm/yyyy') AS data_atual,
                to_char(CURRENT_TIMESTAMP, 'HH24:MI:SS') AS hora_atual,
                (
                    SELECT ' ' || (replace(textcat_all(observacao),'<br>',E'\n'))
                    FROM pmieducar.historico_escolar phe
                    WHERE phe.ref_cod_aluno = $aluno
                    AND phe.ativo = 1
                    AND (CASE WHEN $nao_emitir_reprovado THEN phe.aprovado <> 2 ELSE 1=1 END)
                    AND (CASE WHEN $ano_ini = 0 THEN 1=1 ELSE phe.ano >= $ano_ini END)
                    AND (CASE WHEN $ano_fim = 0 THEN 1=1 ELSE phe.ano <= $ano_fim END)
                    AND (
                        (phe.aprovado <> 2)
                        OR (
                            phe.sequencial = (
                                SELECT max(he.sequencial)
                                FROM pmieducar.historico_escolar he
                                WHERE he.ref_cod_instituicao = phe.ref_cod_instituicao
                                AND he.ref_cod_aluno = phe.ref_cod_aluno
                                AND ativo = 1
                            )
                        )
                    )
                ) AS observacao_all,
                (
                    SELECT SUM(hd.carga_horaria_disciplina)
                    FROM historico_disciplinas hd
                    WHERE hd.ref_sequencial = historico_disciplinas.ref_sequencial
                    AND hd.ref_ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno
                )  AS carga_horaria_disciplina,
                (
                    SELECT cod_aluno_inep
                    FROM modules.educacenso_cod_aluno
                    WHERE cod_aluno = $aluno
                ) AS INEP,
                (
                    CASE
                        WHEN aprovado = 1 THEN 'Apro'
                        WHEN aprovado = 12 THEN 'AprDep'
                        WHEN aprovado = 13 THEN 'AprCo'
                        WHEN aprovado = 2 THEN 'Repr'
                        WHEN aprovado = 3 THEN 'Curs'
                        WHEN aprovado = 4 THEN 'Tran'
                        WHEN aprovado = 5 THEN 'Recl'
                        WHEN aprovado = 6 THEN 'Aban'
                        WHEN aprovado = 14 THEN 'RpFt'
                        WHEN aprovado = 15 THEN 'Fal'
                        ELSE ''
                    END
                ) AS situacao,
                (
                    SELECT aprovado
                    FROM pmieducar.historico_escolar
                    WHERE ref_cod_instituicao = $instituicao
                    AND ativo = 1
                    AND ref_cod_aluno = $aluno
                    ORDER BY ano DESC LIMIT 1
                ) AS situacao_aluno,
                (
                    SELECT (
                        CASE
                            WHEN historico_grade_curso_id = 1 THEN 'reprovou na ' || (substring(nm_serie,1,1)) || 'ª Série'
                            WHEN historico_grade_curso_id = 2 THEN 'reprovou no ' || (substring(nm_serie,1,1)) || 'º Ano'
                        END
                    ) AS serie
                    FROM pmieducar.historico_escolar
                    WHERE ref_cod_instituicao = $instituicao
                    AND ativo = 1
                    AND ref_cod_aluno = $aluno
                    ORDER BY ano DESC LIMIT 1
                ) AS serie,
                (
                    SELECT COALESCE(
                        (
                            SELECT COALESCE (fcn_upper(ps.nome),fcn_upper(juridica.fantasia))
                            FROM cadastro.pessoa ps, cadastro.juridica, pmieducar.escola
                            WHERE escola.cod_escola = $escola
                            AND escola.ref_idpes = juridica.idpes
                            AND juridica.idpes = ps.idpes
                            AND ps.idpes = escola.ref_idpes
                        ), (
                            SELECT nm_escola
                            FROM pmieducar.escola_complemento
                            WHERE ref_cod_escola = $escola
                        )
                    )
                ) AS nm_escola,
                (
                    SELECT COALESCE(
                        (
                            SELECT COALESCE(
                                (
                                    SELECT municipio.nome
                                    FROM public.municipio, cadastro.endereco_pessoa, cadastro.juridica, public.bairro, pmieducar.escola
                                    WHERE escola.cod_escola = $escola
                                    AND endereco_pessoa.idbai = bairro.idbai
                                    AND bairro.idmun = municipio.idmun
                                    AND juridica.idpes = endereco_pessoa.idpes
                                    AND juridica.idpes = escola.ref_idpes
                                ), (
                                    SELECT endereco_externo.cidade
                                    FROM cadastro.endereco_externo, pmieducar.escola
                                    WHERE endereco_externo.idpes = escola.ref_idpes
                                    AND escola.cod_escola = $escola
                                )
                            )
                        ), (
                            SELECT municipio
                            FROM pmieducar.escola_complemento
                            WHERE ref_cod_escola = $escola
                        )
                    )
                ) AS municipio,
                (
                    SELECT fcn_upper(p.nome)
                    FROM cadastro.pessoa p
                    INNER JOIN pmieducar.escola e ON (e.ref_idpes_gestor = p.idpes)
                    WHERE e.cod_escola = $escola
                ) AS diretor,
                (
                    SELECT fcn_upper(p.nome)
                    FROM cadastro.pessoa p
                    INNER JOIN pmieducar.escola e ON (p.idpes = e.ref_idpes_secretario_escolar)
                    WHERE e.cod_escola = $escola
                ) AS secretario,
                (
                    SELECT COALESCE(
                        (
                            SELECT ps.email
                            FROM cadastro.pessoa ps, cadastro.juridica, pmieducar.escola
                            WHERE escola.cod_escola = $escola
                            AND juridica.idpes = ps.idpes
                            AND juridica.idpes = escola.ref_idpes
                        ), (
                            SELECT email
                            FROM pmieducar.escola_complemento
                            WHERE ref_cod_escola = $escola
                        )
                    )
                ) AS email,
                (
                    SELECT public.fcn_upper(pessoa.nome)
                    FROM
                        pmieducar.aluno,
                        cadastro.fisica,
                        cadastro.pessoa
                    WHERE pessoa.idpes = fisica.idpes
                    AND fisica.idpes = aluno.ref_idpes
                    AND aluno.cod_aluno = $aluno
                ) AS nome_aluno,
                (
                    SELECT public.fcn_upper(fisica.nome_social)
                    FROM
                        pmieducar.aluno,
                        cadastro.fisica,
                        cadastro.pessoa
                    WHERE pessoa.idpes = fisica.idpes
                    AND fisica.idpes = aluno.ref_idpes
                    AND aluno.cod_aluno = $aluno
                ) AS nome_social_aluno,
                (
                    SELECT municipio.nome || '/' || municipio.sigla_uf
                    FROM
                        public.municipio,
                        cadastro.fisica,
                        pmieducar.aluno
                    WHERE fisica.idmun_nascimento = municipio.idmun
                    AND fisica.idpes = aluno.ref_idpes
                    AND aluno.cod_aluno = $aluno
                ) AS cidade_nascimento_uf,
                (
                    SELECT municipio.nome
                    FROM
                        public.municipio,
                        cadastro.fisica,
                        pmieducar.aluno
                    WHERE fisica.idmun_nascimento = municipio.idmun
                    AND fisica.idpes = aluno.ref_idpes
                    AND aluno.cod_aluno = $aluno
                ) AS cidade_nascimento,
                (
                    SELECT municipio.sigla_uf
                    FROM
                        public.municipio,
                        cadastro.fisica,
                        pmieducar.aluno
                    WHERE fisica.idmun_nascimento = municipio.idmun
                    AND fisica.idpes = aluno.ref_idpes
                    AND aluno.cod_aluno = $aluno
                ) AS uf_nascimento,
                (
                    SELECT to_char(fisica.data_nasc,'DD/MM/YYYY')
                    FROM
                        pmieducar.aluno,
                        cadastro.fisica,
                        cadastro.pessoa
                    WHERE pessoa.idpes = fisica.idpes
                    AND fisica.idpes = aluno.ref_idpes
                    AND aluno.cod_aluno = $aluno
                ) AS data_nasc,
                (
                    SELECT public.fcn_upper(
                        COALESCE(
                            (
                                SELECT pessoa_pai.nome
                                FROM cadastro.fisica AS pessoa_aluno, cadastro.pessoa AS pessoa_pai, pmieducar.aluno
                                WHERE aluno.cod_aluno = $aluno
                                AND aluno.ref_idpes = pessoa_aluno.idpes
                                AND pessoa_pai.idpes = pessoa_aluno.idpes_pai
                            ), (
                                SELECT aluno.nm_pai
                                FROM pmieducar.aluno
                                WHERE aluno.cod_aluno = $aluno
                            )
                        )
                    )
                ) AS nome_do_pai,
                (
                    SELECT public.fcn_upper(
                        COALESCE(
                            (
                                SELECT pessoa_mae.nome
                                FROM cadastro.fisica AS pessoa_aluno, cadastro.pessoa AS pessoa_mae, pmieducar.aluno
                                WHERE aluno.cod_aluno = $aluno
                                AND aluno.ref_idpes = pessoa_aluno.idpes
                                AND pessoa_mae.idpes = pessoa_aluno.idpes_mae
                            ), (
                                SELECT aluno.nm_mae
                                FROM pmieducar.aluno
                                WHERE aluno.cod_aluno = $aluno
                            )
                        )
                    )
                ) AS nome_da_mae,
                (
                    SELECT public.fcn_upper(nm_instituicao)
                    FROM pmieducar.instituicao
                    WHERE instituicao.cod_instituicao = historico_escolar.ref_cod_instituicao
                ) AS nm_instituicao,
                (
                    SELECT public.fcn_upper(nm_responsavel)
                    FROM pmieducar.instituicao
                    WHERE instituicao.cod_instituicao = historico_escolar.ref_cod_instituicao
                ) AS nm_responsavel,
                (
                    SELECT he.nm_serie
                    FROM pmieducar.historico_escolar he
                    WHERE he.sequencial = historico_escolar.sequencial
                    AND he.ref_cod_aluno = historico_escolar.ref_cod_aluno
                    AND historico_escolar.data_cadastro = (
                        SELECT max(hi.data_cadastro)
                        FROM pmieducar.historico_escolar hi
                        WHERE hi.ref_cod_aluno = he.ref_cod_aluno
                        AND hi.sequencial = he.sequencial
                    )
                ) AS nm_serie_cursou,
                (
                    SELECT
                        CASE
                            WHEN he.aprovado = 3 THEN 'está cursando o '
                            ELSE 'concluiu o(a) '
                        END ||
                        CASE
                            WHEN (substring(nm_serie,1,1) = '9') THEN 'ENSINO FUNDAMENTAL (9 ANOS)'
                            ELSE nm_serie
                        END
                    FROM pmieducar.historico_escolar he
                    WHERE he.ref_cod_aluno = historico_escolar.ref_cod_aluno
                    AND he.sequencial = (
                        SELECT he.sequencial
                        FROM pmieducar.historico_escolar he
                        WHERE he.ref_cod_instituicao = historico_escolar.ref_cod_instituicao
                        AND he.ref_cod_aluno = historico_escolar.ref_cod_aluno
                        AND he.ativo = 1
                        AND he.aprovado NOT IN (2, 4, 6)
                        ORDER BY ano desc, substring(nm_serie,1,1) desc
                        LIMIT 1
                    )
                    AND he.ref_cod_instituicao = historico_escolar.ref_cod_instituicao
                    AND ativo = 1 LIMIT 1
                ) AS nome_serie_aux,
                (
                    SELECT nm_curso
                    FROM pmieducar.historico_escolar
                    WHERE historico_escolar.ref_cod_aluno = $aluno
                    AND historico_escolar.ativo = 1
                    AND historico_escolar.ano = (
                        SELECT max(he.ano)
                        FROM pmieducar.historico_escolar AS he
                        WHERE he.ref_cod_aluno = historico_escolar.ref_cod_aluno
                        AND he.ativo = 1
                        AND (CASE WHEN $ano_fim = 0 THEN TRUE ELSE he.ano <= $ano_fim END)
                        AND (CASE WHEN $sequencial = 0 THEN TRUE ELSE (he.nm_curso) IN ($cursoaluno::varchar) END))
                    AND (CASE WHEN $sequencial = 0 THEN TRUE ELSE (historico_escolar.nm_curso) IN ($cursoaluno::varchar) END) LIMIT 1
                ) AS nome_curso,
                (
                    SELECT count(hd.nota)
                    FROM pmieducar.historico_disciplinas hd
                    WHERE hd.ref_ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno
                    AND CASE WHEN isnumeric(replace(hd.nota, ',', '.')) THEN replace(hd.nota, ',', '.')::float > 10 ELSE FALSE END
                ) AS notas_maiores_que_dez
            FROM
                pmieducar.historico_escolar,
                pmieducar.historico_disciplinas
            WHERE historico_escolar.ref_cod_aluno = $aluno
            AND historico_escolar.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno
            AND (CASE WHEN $ano_ini = 0 THEN 1=1 ELSE historico_escolar.ano >= $ano_ini END)
            AND (CASE WHEN $ano_fim = 0 THEN 1=1 ELSE historico_escolar.ano <= $ano_fim END)
            AND (CASE WHEN $nao_emitir_reprovado THEN historico_escolar.aprovado <> 2 ELSE 1=1 END)
            AND (CASE WHEN $sequencial = 0 THEN TRUE ELSE (historico_escolar.nm_curso) IN ($cursoaluno::varchar) END)
            AND historico_escolar.sequencial = historico_disciplinas.ref_sequencial
            AND historico_escolar.ref_cod_instituicao = $instituicao
            AND historico_escolar.ref_cod_aluno = $aluno
            AND (
                (historico_escolar.aprovado <> 2)
                OR (
                    historico_escolar.sequencial = (
                        SELECT max(he.sequencial)
                        FROM pmieducar.historico_escolar he
                        WHERE he.ref_cod_instituicao = historico_escolar.ref_cod_instituicao
                        AND he.ref_cod_aluno = historico_escolar.ref_cod_aluno
                        AND ativo = 1
                    )
                )
            )
            ORDER BY ano ASC;
SQL;
    }
}
