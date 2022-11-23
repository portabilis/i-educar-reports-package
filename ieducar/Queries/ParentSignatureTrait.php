<?php

trait ParentSignatureTrait
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
        $turma = $this->args['turma'];
        $situacao = $this->args['situacao'];

        return <<<SQL
                SELECT sequencial_fechamento,
                       fcn_upper(pessoa.nome) AS nome,
                       turma.cod_turma,
                       upper(turma.nm_turma) AS nome_turma
                  FROM pmieducar.instituicao
                 INNER JOIN pmieducar.escola ON (escola.ref_cod_instituicao = instituicao.cod_instituicao)
                 INNER JOIN pmieducar.escola_curso ON (escola_curso.ativo = 1
                                                   AND escola_curso.ref_cod_escola = escola.cod_escola)
                 INNER JOIN pmieducar.curso ON (curso.cod_curso = escola_curso.ref_cod_curso
                                            AND curso.ativo = 1)
                 INNER JOIN pmieducar.escola_serie ON (escola_serie.ativo = 1
                                                   AND escola_serie.ref_cod_escola = escola.cod_escola)
                 INNER JOIN pmieducar.serie ON (serie.cod_serie = escola_serie.ref_cod_serie
                                            AND serie.ativo = 1)
                 INNER JOIN pmieducar.turma ON (turma.ref_ref_cod_escola = escola.cod_escola
                                            AND turma.ref_cod_curso = escola_curso.ref_cod_curso
                                            AND turma.ref_ref_cod_serie = escola_serie.ref_cod_serie
                                            AND turma.ativo = 1)
                 INNER JOIN pmieducar.matricula_turma ON (matricula_turma.ref_cod_turma = turma.cod_turma)
                 INNER JOIN pmieducar.matricula ON (matricula.cod_matricula = matricula_turma.ref_cod_matricula)
                 INNER JOIN relatorio.view_situacao ON (view_situacao.cod_matricula = matricula.cod_matricula
                                    AND view_situacao.cod_turma = turma.cod_turma
                                        AND view_situacao.sequencial = matricula_turma.sequencial)
                 INNER JOIN pmieducar.aluno ON (aluno.cod_aluno = matricula.ref_cod_aluno)
                 INNER JOIN cadastro.pessoa ON (pessoa.idpes = aluno.ref_idpes)
                 WHERE instituicao.cod_instituicao = {$instituicao}
                   AND turma.ano = {$ano}
                   AND escola.cod_escola = {$escola}
                   AND ({$curso} = 0 OR curso.cod_curso = {$curso})
                   AND ({$serie} = 0 OR serie.cod_serie = {$serie})
                   AND ({$turma} = 0 OR turma.cod_turma = {$turma})
                   AND cod_situacao = {$situacao}
                ORDER BY
                    curso.nm_curso,
                    serie.nm_serie,
                    turma.nm_turma,
                    sequencial_fechamento,
                    nome
SQL;
    }
}
