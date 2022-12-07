<?php

trait TagsTrait
{
    /**
     * @inheritdoc
     */
    protected function query()
    {
        $instituicao = $this->args['instituicao'];
        $escola = $this->args['escola'];
        $curso = $this->args['curso'];
        $serie = $this->args['serie'];
        $turma = $this->args['turma'];
        $ano = $this->args['ano'];
        $situacao = $this->args['situacao'];

        return <<<SQL
                    SELECT
                        matricula_turma.sequencial_fechamento,
                        aluno.cod_aluno,
                        translate(public.fcn_upper(pessoa.nome),'åáàãâäéèêëíìîïóòõôöúùüûçÿýñÅÁÀÃÂÄÉÈÊËÍÌÎÏÓÒÕÔÖÚÙÛÜÇÝÑ', 'aaaaaaeeeeiiiiooooouuuucyynAAAAAAEEEEIIIIOOOOOUUUUCYN') as nome_aluno,
                        (SELECT fone_pessoa.fone FROM cadastro.fone_pessoa WHERE fone_pessoa.idpes = fisica.idpes AND fone_pessoa.tipo = 1) AS fone,
                        (SELECT fone_pessoa.ddd FROM cadastro.fone_pessoa WHERE fone_pessoa.idpes = fisica.idpes AND fone_pessoa.tipo = 1) AS ddd,
                        (SELECT fone_pessoa.fone FROM cadastro.fone_pessoa WHERE fone_pessoa.idpes = fisica.idpes AND fone_pessoa.tipo = 2) AS fone2,
                        (SELECT fone_pessoa.ddd FROM cadastro.fone_pessoa WHERE fone_pessoa.idpes = fisica.idpes AND fone_pessoa.tipo = 2) AS ddd2,
                        (SELECT ps.nome FROM cadastro.pessoa ps WHERE ps.idpes = fisica.idpes_mae) AS nm_mae,
                        (SELECT ps.nome FROM cadastro.pessoa ps WHERE ps.idpes = fisica.idpes_pai) AS nm_pai,
                        (SELECT COALESCE((SELECT endereco_pessoa.numero FROM cadastro.endereco_pessoa WHERE endereco_pessoa.idpes = fisica.idpes ),
                        (SELECT endereco_externo.numero FROM cadastro.endereco_externo WHERE endereco_externo.idpes = fisica.idpes))) AS numero_aluno,
                        (SELECT COALESCE((SELECT endereco_pessoa.apartamento FROM cadastro.endereco_pessoa WHERE endereco_pessoa.idpes = fisica.idpes),
                        (SELECT endereco_externo.apartamento FROM cadastro.endereco_externo WHERE endereco_externo.idpes = fisica.idpes))) AS apartamento_aluno,
                        (SELECT COALESCE((SELECT endereco_pessoa.complemento FROM cadastro.endereco_pessoa WHERE endereco_pessoa.idpes = fisica.idpes),
                        (SELECT endereco_externo.complemento FROM cadastro.endereco_externo WHERE endereco_externo.idpes = fisica.idpes))) AS complemento_aluno,
                        (SELECT COALESCE((SELECT endereco_pessoa.cep FROM cadastro.endereco_pessoa WHERE endereco_pessoa.idpes = fisica.idpes),
                        (SELECT endereco_externo.cep FROM cadastro.endereco_externo WHERE endereco_externo.idpes = fisica.idpes))) AS cep_aluno,
                        (
                            SELECT COALESCE((SELECT logradouro.nome
                            FROM public.logradouro,
                                cadastro.endereco_pessoa
                            WHERE logradouro.idlog = endereco_pessoa.idlog AND
                                endereco_pessoa.idpes = fisica.idpes),(SELECT endereco_externo.logradouro FROM cadastro.endereco_externo WHERE endereco_externo.idpes = fisica.idpes))
                        ) AS logradouro_aluno,
                        (
                            SELECT COALESCE((SELECT municipio.nome
                            FROM public.municipio,
                                public.logradouro,
                                cadastro.endereco_pessoa
                            WHERE municipio.idmun = logradouro.idmun AND
                                logradouro.idlog = endereco_pessoa.idlog AND
                                endereco_pessoa.idpes = fisica.idpes),(SELECT endereco_externo.cidade FROM cadastro.endereco_externo WHERE endereco_externo.idpes = fisica.idpes))
                        ) AS municipio_aluno,
                        (
                            SELECT COALESCE((SELECT municipio.sigla_uf
                            FROM public.municipio,
                                public.logradouro,
                                cadastro.endereco_pessoa
                            WHERE municipio.idmun = logradouro.idmun AND
                                logradouro.idlog = endereco_pessoa.idlog AND
                                endereco_pessoa.idpes = fisica.idpes),(SELECT endereco_externo.sigla_uf FROM cadastro.endereco_externo WHERE endereco_externo.idpes = fisica.idpes))
                        ) AS sigla_uf_aluno,
                        (
                            SELECT COALESCE((SELECT min(bairro.nome)
                            FROM public.bairro,
                                public.municipio,
                                public.logradouro,
                                cadastro.endereco_pessoa
                            WHERE bairro.idmun =  municipio.idmun AND
                                municipio.idmun = logradouro.idmun AND
                                logradouro.idlog = endereco_pessoa.idlog AND
                                endereco_pessoa.idpes = fisica.idpes AND
                                endereco_pessoa.idbai = bairro.idbai),(SELECT endereco_externo.bairro FROM cadastro.endereco_externo WHERE endereco_externo.idpes = fisica.idpes))
                        ) AS bairro_aluno
                    FROM pmieducar.instituicao,
                       pmieducar.aluno,
                       cadastro.fisica,
                       cadastro.pessoa,
                       pmieducar.curso,
                       pmieducar.matricula,
                       pmieducar.matricula_turma,
                       pmieducar.turma,
                       pmieducar.serie,
                       pmieducar.escola,
                       pmieducar.escola_ano_letivo,
                       pmieducar.escola_curso,
                       pmieducar.escola_serie
                    WHERE  escola_ano_letivo.ano = {$ano} AND
                           instituicao.cod_instituicao = {$instituicao} AND
                           escola.cod_escola = {$escola} AND
                           ((escola_curso.ref_cod_curso = curso.cod_curso) AND (curso.cod_curso = {$curso} OR {$curso} = 0)) AND
                           ((escola_serie.ref_cod_serie = serie.cod_serie) AND (matricula.ref_ref_cod_serie = {$serie} OR {$serie} = 0)) AND
                           ((turma.cod_turma = matricula_turma.ref_cod_turma) AND(turma.cod_turma = {$turma} OR {$turma} = 0)) AND
                           escola_curso.ref_cod_escola = escola.cod_escola AND
                           escola_ano_letivo.ref_cod_escola = escola.cod_escola AND
                           escola_serie.ref_cod_escola = escola.cod_escola AND
                           escola.ref_cod_instituicao = instituicao.cod_instituicao AND
                           pessoa.idpes = fisica.idpes AND
                           aluno.ref_idpes = fisica.idpes AND
                           aluno.cod_aluno = matricula.ref_cod_aluno AND
                           aluno.ativo = 1 AND
                           matricula.cod_matricula = matricula_turma.ref_cod_matricula AND
                           turma.ref_ref_cod_serie = escola_serie.ref_cod_serie AND
                           turma.ref_ref_cod_escola = escola_serie.ref_cod_escola AND
                           turma.ref_cod_curso = curso.cod_curso AND
                           matricula.ativo = 1 AND
                           matricula.ano = {$ano} AND
                           matricula.ref_ref_cod_serie = serie.cod_serie AND
                           (
                                CASE WHEN matricula.aprovado = 4 THEN (matricula_turma.ativo = 1 OR (exists( SELECT 1 FROM pmieducar.transferencia_solicitacao WHERE ref_cod_matricula_saida = matricula.cod_matricula AND ativo = 1 LIMIT 1)
                                          AND (matricula_turma.sequencial = (SELECT max(sequencial) FROM pmieducar.matricula_turma WHERE ref_cod_matricula = matricula.cod_matricula))) OR matricula_turma.transferido)
                                     WHEN matricula.aprovado = 6 THEN (matricula_turma.ativo = 1 OR ((matricula_turma.sequencial = (SELECT max(sequencial) FROM pmieducar.matricula_turma WHERE ref_cod_matricula = matricula.cod_matricula))) OR matricula_turma.abandono)
                                     ELSE matricula_turma.ativo = 1 OR (matricula_turma.abandono OR matricula_turma.reclassificado OR matricula_turma.transferido OR matricula_turma.remanejado)
                                END
                           ) AND
                           (
                              SELECT CASE WHEN {$situacao} = 10 THEN
                                    matricula.aprovado IN ('1', '2', '3', '4', '5', '6', '12')
                              WHEN {$situacao} = 9 THEN
                                    matricula.aprovado IN ('1', '2', '3', '12')
                                    AND (NOT matricula_turma.reclassificado
                                    OR matricula_turma.reclassificado IS NULL)
                                    AND (NOT matricula_turma.abandono
                                    OR matricula_turma.abandono IS NULL)
                                    AND (NOT matricula_turma.remanejado
                                    OR matricula_turma.remanejado IS NULL)
                                    AND (NOT matricula_turma.transferido
                                    OR matricula_turma.transferido IS NULL)
                              WHEN {$situacao} in ('1', '2', '3', '12') THEN
                                    matricula.aprovado = {$situacao}
                                    AND (NOT matricula_turma.reclassificado
                                    OR matricula_turma.reclassificado IS NULL)
                                    AND (NOT matricula_turma.abandono
                                    OR matricula_turma.abandono IS NULL)
                                    AND (NOT matricula_turma.remanejado
                                    OR matricula_turma.remanejado IS NULL)
                                    AND (NOT matricula_turma.transferido
                                    OR matricula_turma.transferido IS NULL)
                              ELSE
                                    matricula.aprovado = {$situacao}
                              END
                          )
                    ORDER BY nome_aluno
SQL;
    }
}
