<?php

class QuerySchoolHistoryNineYearsExtraCurricular extends QueryBridge
{
    /**
     * @inheritdoc
     */
    protected function query()
    {
        return <<<'SQL'
              SELECT aluno.cod_aluno AS cod_aluno,
                   public.fcn_upper(pessoa.nome) AS nome_aluno,
                   view_historico_9anos.ano_1serie AS ano_1serie,
                   view_historico_9anos.ano_2serie AS ano_2serie,
                   view_historico_9anos.ano_3serie AS ano_3serie,
                   view_historico_9anos.ano_4serie AS ano_4serie,
                   view_historico_9anos.ano_5serie AS ano_5serie,
                   view_historico_9anos.ano_6serie AS ano_6serie,
                   view_historico_9anos.ano_7serie AS ano_7serie,
                   view_historico_9anos.ano_8serie AS ano_8serie,
                   view_historico_9anos.ano_9serie AS ano_9serie,
                   view_historico_9anos.disciplina AS nm_disciplina,
                   view_historico_9anos.transferido1 AS transferido1,
                   view_historico_9anos.transferido2 AS transferido2,
                   view_historico_9anos.transferido3 AS transferido3,
                   view_historico_9anos.transferido4 AS transferido4,
                   view_historico_9anos.transferido5 AS transferido5,
                   view_historico_9anos.transferido6 AS transferido6,
                   view_historico_9anos.transferido7 AS transferido7,
                   view_historico_9anos.transferido8 AS transferido8,
                   view_historico_9anos.transferido9 AS transferido9,
                   view_historico_9anos.nota_1serie AS nota_1serie,
                   view_historico_9anos.nota_2serie AS nota_2serie,
                   view_historico_9anos.nota_3serie AS nota_3serie,
                   view_historico_9anos.nota_4serie AS nota_4serie,
                   view_historico_9anos.nota_5serie AS nota_5serie,
                   view_historico_9anos.nota_6serie AS nota_6serie,
                   view_historico_9anos.nota_7serie AS nota_7serie,
                   view_historico_9anos.nota_8serie AS nota_8serie,
                   view_historico_9anos.nota_9serie AS nota_9serie,
                   view_historico_9anos.carga_horaria1 AS carga_horaria1,
                   view_historico_9anos.carga_horaria2 AS carga_horaria2,
                   view_historico_9anos.carga_horaria3 AS carga_horaria3,
                   view_historico_9anos.carga_horaria4 AS carga_horaria4,
                   view_historico_9anos.carga_horaria5 AS carga_horaria5,
                   view_historico_9anos.carga_horaria6 AS carga_horaria6,
                   view_historico_9anos.carga_horaria7 AS carga_horaria7,
                   view_historico_9anos.carga_horaria8 AS carga_horaria8,
                   view_historico_9anos.carga_horaria9 AS carga_horaria9
            FROM pmieducar.aluno
            LEFT JOIN relatorio.view_historico_9anos_extra_curricular view_historico_9anos ON (aluno.cod_aluno = view_historico_9anos.cod_aluno)
            INNER JOIN cadastro.pessoa ON (pessoa.idpes = aluno.ref_idpes)
            INNER JOIN cadastro.fisica ON (fisica.idpes = pessoa.idpes)
              AND CASE WHEN $P{turma} > 0 THEN aluno.cod_aluno IN ($P!{alunos}) ELSE aluno.cod_aluno = $P{aluno} END
SQL;
    }
}
