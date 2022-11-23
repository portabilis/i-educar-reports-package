<?php

trait RegistrationSchoolTrait
{
    /**
     * @inheritdoc
     */
    protected function query()
    {
        $ano = $this->args['ano'];
        $instituicao = $this->args['instituicao'];
        $escola = $this->args['escola'];
        $curso = $this->args['curso'];
        $serie = $this->args['serie'];
        $situacao = $this->args['situacao'];
        $dependencia = $this->args['dependencia'];

        return <<<SQL
            SELECT public.fcn_upper(instituicao.nm_instituicao) AS nm_instituicao,
                   public.fcn_upper(instituicao.nm_responsavel) AS nm_responsavel,
                   instituicao.cidade AS cidade_instituicao,
                   public.fcn_upper(instituicao.ref_sigla_uf) AS uf_instituicao,
                   aluno.cod_aluno AS cod_aluno,
                   serie.nm_serie AS serie,
                     (SELECT COALESCE((SELECT COALESCE (fcn_upper(ps.nome),fcn_upper(juridica.fantasia))
                      FROM cadastro.pessoa ps,
                           cadastro.juridica,
                           pmieducar.escola
                     WHERE escola.ref_idpes = ps.idpes
                       AND ps.idpes = juridica .idpes
                       AND escola.cod_escola = {$escola}),(SELECT nm_escola FROM pmieducar.escola_complemento where ref_cod_escola = {$escola}))) AS nm_escola,
                   to_char(current_date,'dd/mm/yyyy') AS data_atual,
                   to_char(current_timestamp, 'HH24:MI:SS') AS hora_atual,
                   matricula.cod_matricula AS cod_matricula,
                   fcn_upper(pessoa.nome) AS nome_aluno,
                   relatorio.get_texto_sem_caracter_especial(pessoa.nome) AS nome_aluno_order,
                   fisica.sexo AS sexo,
                   to_char(fisica.data_nasc,'dd/mm/yyyy') AS data_nasc,
                   educacenso_cod_aluno.cod_aluno_inep AS cod_aluno_inep,
                   (SELECT count(matricula.cod_matricula)
                  FROM pmieducar.instituicao,
                           pmieducar.matricula,
                           pmieducar.aluno,
                           pmieducar.escola
                     WHERE instituicao.cod_instituicao = 1 AND
                           instituicao.cod_instituicao = escola.ref_cod_instituicao AND
                           (SELECT CASE WHEN {$escola} = 0 THEN
                                matricula.ref_ref_cod_escola = escola.cod_escola
                           ELSE
                                matricula.ref_ref_cod_escola = escola.cod_escola AND
                                escola.cod_escola = {$escola}
                           END) AND
                           matricula.ref_cod_aluno = aluno.cod_aluno AND
                           matricula.ativo = 1 AND
                           matricula.ano = {$ano} AND
                           matricula.ultima_matricula = 1) AS total_alunos,
             matricula_turma.sequencial_fechamento as seque_fecha,
                    view_situacao.texto_situacao AS situacao
              FROM pmieducar.instituicao
            INNER JOIN pmieducar.escola ON (pmieducar.escola.ref_cod_instituicao = pmieducar.instituicao.cod_instituicao)
            INNER JOIN pmieducar.escola_ano_letivo ON (pmieducar.escola_ano_letivo.ref_cod_escola = pmieducar.escola.cod_escola)
            INNER JOIN pmieducar.escola_curso ON (pmieducar.escola_curso.ref_cod_escola = pmieducar.escola.cod_escola AND pmieducar.escola_curso.ativo = 1)
            INNER JOIN pmieducar.escola_serie ON (pmieducar.escola_serie.ref_cod_escola = pmieducar.escola.cod_escola AND pmieducar.escola_serie.ativo = 1)
            INNER JOIN pmieducar.serie ON (pmieducar.serie.cod_serie = pmieducar.escola_serie.ref_cod_serie
                            AND pmieducar.serie.ref_cod_curso = pmieducar.escola_curso.ref_cod_curso)
            INNER JOIN pmieducar.turma ON (pmieducar.turma.ref_ref_cod_escola = pmieducar.escola.cod_escola AND turma.ativo = 1)
            INNER JOIN pmieducar.curso ON (escola_curso.ref_cod_escola = escola.cod_escola
                            AND turma.ref_cod_curso = curso.cod_curso)
            INNER JOIN pmieducar.matricula ON (pmieducar.matricula.ref_ref_cod_escola = pmieducar.escola.cod_escola
                                AND pmieducar.matricula.ref_cod_curso = pmieducar.escola_curso.ref_cod_curso
                                AND pmieducar.matricula.ref_ref_cod_serie = pmieducar.escola_serie.ref_cod_serie
                                AND pmieducar.matricula.ativo = 1)
            INNER JOIN pmieducar.matricula_turma ON (pmieducar.matricula_turma.ref_cod_turma = pmieducar.turma.cod_turma
                                  AND pmieducar.matricula_turma.ref_cod_matricula = pmieducar.matricula.cod_matricula)
            INNER JOIN pmieducar.turma_turno ON (turma_turno.id = turma.turma_turno_id
                                  AND turma.cod_turma = matricula_turma.ref_cod_turma)
            INNER JOIN pmieducar.aluno ON (pmieducar.matricula.ref_cod_aluno = pmieducar.aluno.cod_aluno)
            INNER JOIN cadastro.fisica ON (cadastro.fisica.idpes = pmieducar.aluno.ref_idpes)
            INNER JOIN cadastro.pessoa ON (cadastro.pessoa.idpes = cadastro.fisica.idpes)
            LEFT JOIN cadastro.fone_pessoa ON (fone_pessoa.idpes = escola.ref_idpes
                                                 AND fone_pessoa.tipo = (SELECT COALESCE(MIN(fone_pessoa_aux.tipo),1)
                                                               FROM cadastro.fone_pessoa AS fone_pessoa_aux
                                              WHERE fone_pessoa_aux.fone <> 0
                                                AND fone_pessoa_aux.idpes = escola.ref_idpes))
            LEFT JOIN cadastro.juridica ON (juridica.idpes = escola.ref_idpes)
            LEFT JOIN cadastro.pessoa pessoa_juridica ON (pessoa_juridica.idpes = juridica.idpes)
            LEFT JOIN cadastro.endereco_pessoa ON (juridica.idpes = endereco_pessoa.idpes)
            LEFT JOIN public.logradouro ON (logradouro.idlog = endereco_pessoa.idlog)
            LEFT JOIN public.bairro ON (bairro.idbai = endereco_pessoa.idbai)
            LEFT JOIN public.municipio ON (municipio.idmun = bairro.idmun)
            LEFT JOIN cadastro.documento ON (documento.idpes = fisica.idpes)
            LEFT JOIN modules.educacenso_cod_aluno ON (educacenso_cod_aluno.cod_aluno = aluno.cod_aluno)
            INNER JOIN relatorio.view_situacao ON (view_situacao.cod_matricula = matricula.cod_matricula
                                AND view_situacao.cod_turma = turma.cod_turma
                                    AND view_situacao.cod_situacao = {$situacao}
                                    AND matricula_turma.sequencial = view_situacao.sequencial) --Garante que irá trazer a enturmação correta
            WHERE pmieducar.instituicao.cod_instituicao = {$instituicao}
               AND pmieducar.escola.cod_escola = {$escola}
               AND pmieducar.escola_ano_letivo.ano = {$ano}
               AND pmieducar.matricula.ano = pmieducar.escola_ano_letivo.ano
               AND ({$curso} = 0 OR pmieducar.escola_curso.ref_cod_curso = {$curso})
               AND ({$serie} = 0 OR pmieducar.serie.cod_serie = {$serie})
               AND (CASE WHEN {$dependencia} = 1 THEN
                        matricula.dependencia = TRUE
                   WHEN {$dependencia} = 2 THEN
                        matricula.dependencia = FALSE
                   ELSE
                        TRUE
                    END)
            ORDER BY nm_escola, nome_aluno_order, seque_fecha
SQL;
    }
}
