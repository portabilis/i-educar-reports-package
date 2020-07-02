<?php

trait SchoolHistorySeriesYearsTrait
{
    /**
     * @inheritdoc
     */
    protected function query()
    {
        $escola = $this->args['escola'];
        $aluno = $this->args['aluno'];
        $nao_emitir_reprovado = $this->args['nao_emitir_reprovado'];

        return <<<SQL
            SELECT
                vhsa.cod_aluno,
                vhsa.disciplina AS nm_disciplina,
                pessoa.nome AS nome_aluno,
                eca.cod_aluno_inep AS cod_inep,
                municipio.nome || '/' || municipio.sigla_uf AS cidade_nascimento_uf,
                municipio.sigla_uf AS uf_nascimento,
                municipio.nome AS cidade_nascimento,
                to_char(fisica.data_nasc,'DD/MM/YYYY') AS data_nasc,
                relatorio.get_pai_aluno(vhsa.cod_aluno) AS nome_do_pai,
                relatorio.get_mae_aluno(vhsa.cod_aluno) AS nome_da_mae,
                vhsa.carga_horaria_disciplina1 AS chd1,
                vhsa.carga_horaria_disciplina2 AS chd2,
                vhsa.carga_horaria_disciplina3 AS chd3,
                vhsa.carga_horaria_disciplina4 AS chd4,
                vhsa.carga_horaria_disciplina5 AS chd5,
                vhsa.carga_horaria_disciplina6 AS chd6,
                vhsa.carga_horaria_disciplina7 AS chd7,
                vhsa.carga_horaria_disciplina8 AS chd8,
                vhsa.carga_horaria_disciplina9 AS chd9,
                vhsa.status_serie1,
                vhsa.status_serie2,
                vhsa.status_serie3,
                vhsa.status_serie4,
                vhsa.status_serie5,
                vhsa.status_serie6,
                vhsa.status_serie7,
                vhsa.status_serie8,
                vhsa.status_serie9,
                vhsa.carga_horaria1,
                vhsa.carga_horaria2,
                vhsa.carga_horaria3,
                vhsa.carga_horaria4,
                vhsa.carga_horaria5,
                vhsa.carga_horaria6,
                vhsa.carga_horaria7,
                vhsa.carga_horaria8,
                vhsa.carga_horaria9,
                vhsa.frequencia1 AS freq1,
                vhsa.frequencia2 AS freq2,
                vhsa.frequencia3 AS freq3,
                vhsa.frequencia4 AS freq4,
                vhsa.frequencia5 AS freq5,
                vhsa.frequencia6 AS freq6,
                vhsa.frequencia7 AS freq7,
                vhsa.frequencia8 AS freq8,
                vhsa.frequencia9 AS freq9,
                vhsa.nota_1serie,
                vhsa.nota_2serie,
                vhsa.nota_3serie,
                vhsa.nota_4serie,
                vhsa.nota_5serie,
                vhsa.nota_6serie,
                vhsa.nota_7serie,
                vhsa.nota_8serie,
                vhsa.nota_9serie,
                vhsa.ano_1serie,
                vhsa.ano_2serie,
                vhsa.ano_3serie,
                vhsa.ano_4serie,
                vhsa.ano_5serie,
                vhsa.ano_6serie,
                vhsa.ano_7serie,
                vhsa.ano_8serie,
                vhsa.ano_9serie,
                vhsa.escola_1serie,
                vhsa.escola_2serie,
                vhsa.escola_3serie,
                vhsa.escola_4serie,
                vhsa.escola_5serie,
                vhsa.escola_6serie,
                vhsa.escola_7serie,
                vhsa.escola_8serie,
                vhsa.escola_9serie,
                vhsa.escola_uf_1serie,
                vhsa.escola_uf_2serie,
                vhsa.escola_uf_3serie,
                vhsa.escola_uf_4serie,
                vhsa.escola_uf_5serie,
                vhsa.escola_uf_6serie,
                vhsa.escola_uf_7serie,
                vhsa.escola_uf_8serie,
                vhsa.escola_uf_9serie,
                vhsa.escola_cidade_1serie,
                vhsa.escola_cidade_2serie,
                vhsa.escola_cidade_3serie,
                vhsa.escola_cidade_4serie,
                vhsa.escola_cidade_5serie,
                vhsa.escola_cidade_6serie,
                vhsa.escola_cidade_7serie,
                vhsa.escola_cidade_8serie,
                vhsa.escola_cidade_9serie,
                (
                    SELECT municipio
                    FROM relatorio.view_dados_escola
                    WHERE cod_escola = $escola
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
                    SELECT max(COALESCE(ato_poder_publico,''))
                    FROM pmieducar.curso
                ) AS ato_poder_publico,
                (
                    SELECT COALESCE(fcn_upper(nm_curso),'')
                    FROM pmieducar.historico_escolar he
                    WHERE he.ref_cod_aluno = $aluno
                    AND he.ativo = 1
                    ORDER BY ano DESC, relatorio.prioridade_historico(he.aprovado) ASC
                    LIMIT 1
                ) AS nome_curso,
                to_char(CURRENT_DATE,'dd/mm/yyyy') AS data_atual,
                to_char(CURRENT_TIMESTAMP, 'HH24:MI:SS') AS hora_atual,
                public.data_para_extenso(CURRENT_DATE) AS data_atual_extenso,
                (
                    SELECT
                        CASE
                            WHEN he.aprovado = 3 THEN 'está cursando '
                            ELSE 'concluiu '
                        END || (
                            CASE
                                WHEN ((substring(nm_serie,1,1)::integer = 8
                                    AND historico_grade_curso_id = 1)
                                    OR (substring(nm_serie,1,1)::integer = 9)
                                    AND historico_grade_curso_id = 2) THEN 'o ENSINO FUNDAMENTAL'
                                ELSE
                                    CASE
                                        WHEN (
                                            SELECT substring(nm_serie,1,1)::integer
                                            FROM pmieducar.historico_escolar he
                                            WHERE he.ref_cod_aluno = vhsa.cod_aluno
                                            AND he.ativo = 1
                                            AND he.historico_grade_curso_id = 2
                                            ORDER BY he.ano DESC LIMIT 1
                                        ) = 1 THEN 'o ' || (substring(nm_serie,1,1)::integer::numeric) || 'º ano'
                                        ELSE
                                            CASE
                                                WHEN historico_grade_curso_id = 1 THEN
                                                    CASE
                                                        WHEN substring(nm_serie,1,1)::integer = '0' THEN 'o ' || (substring(nm_serie,1,1)::integer::numeric +1) || 'º ano'
                                                        ELSE 'a ' || substring(nm_serie,1,1)::integer || 'ª série/' || (substring(nm_serie,1,1)::integer::numeric +1) || 'º ano'
                                                    END
                                                ELSE
                                                    CASE
                                                        WHEN (substring(nm_serie,1,1)::integer::numeric -1) = '0' THEN 'o ' || substring(nm_serie,1,1)::integer || 'º ano'
                                                        ELSE 'a ' || (substring(nm_serie,1,1)::integer::numeric -1) || 'ª série/' || substring(nm_serie,1,1)::integer || 'º ano'
                                                    END
                                            END
                                    END
                            END
                        )
                    FROM pmieducar.historico_escolar he
                    WHERE he.ref_cod_aluno = vhsa.cod_aluno
                    AND he.aprovado NOT IN (2,3,4,6)
                    AND he.extra_curricular = 0
                    AND ativo = 1
                    ORDER BY ano DESC, relatorio.prioridade_historico(he.aprovado) ASC
                    LIMIT 1
                ) AS nome_serie_aux,

                (
                    SELECT count(hd.nota)
                    FROM pmieducar.historico_disciplinas hd
                    INNER JOIN pmieducar.historico_escolar he ON (he.ref_cod_aluno = hd.ref_ref_cod_aluno
                        AND he.sequencial = hd.ref_sequencial)
                    WHERE hd.ref_ref_cod_aluno = vhsa.cod_aluno
                    AND
                        CASE
                            WHEN isnumeric(replace(hd.nota, ',', '.')) THEN replace(hd.nota, ',', '.')::float > 10
                            ELSE FALSE
                        END
                ) AS qtde_notas_maiores_dez,

                (
                    SELECT DISTINCT '' || (replace(textcat_all((' ')),' <br> ','<br>'))
                    FROM generate_series(1,(
                        SELECT ROUND((330 - (COUNT(DISTINCT trim(relatorio.get_texto_sem_caracter_especial(nm_disciplina))) * 12)) / 12)
                        FROM historico_disciplinas hd
                        INNER JOIN pmieducar.historico_escolar he ON (he.ref_cod_aluno = hd.ref_ref_cod_aluno
                            AND hd.ref_sequencial = he.sequencial)
                        WHERE ref_ref_cod_aluno = vhsa.cod_aluno
                    )::INTEGER)
                ) AS espaco_branco,

                (
                    SELECT COUNT(1)
                    FROM pmieducar.historico_escolar he
                    WHERE he.ref_cod_aluno = vhsa.cod_aluno
                    AND he.ativo = 1
                    AND (CASE WHEN $nao_emitir_reprovado THEN he.aprovado <> 2 ELSE 1=1 END)
                    AND he.dependencia = 't'
                ) AS possui_historico_dependencia,

                (
                    SELECT textcat_all(obs)
                    FROM (
                        SELECT observacao AS obs
                        FROM pmieducar.historico_escolar phe
                        WHERE phe.ref_cod_aluno = vhsa.cod_aluno
                        AND phe.ativo = 1
                        AND (CASE WHEN $nao_emitir_reprovado THEN phe.aprovado <> 2 ELSE 1=1 END)
                        ORDER BY phe.ano
                    )tabl
                ) AS observacao_all
                FROM relatorio.view_historico_series_anos vhsa
                INNER JOIN pmieducar.aluno ON (aluno.cod_aluno = vhsa.cod_aluno)
                INNER JOIN cadastro.pessoa ON (pessoa.idpes = aluno.ref_idpes)
                INNER JOIN cadastro.fisica ON (fisica.idpes = aluno.ref_idpes)
                LEFT JOIN modules.educacenso_cod_aluno eca ON (eca.cod_aluno = aluno.cod_aluno)
                LEFT JOIN public.municipio ON (municipio.idmun = fisica.idmun_nascimento)
                WHERE vhsa.cod_aluno = $aluno;

SQL;
    }
}
