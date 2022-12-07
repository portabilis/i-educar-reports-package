<?php

class QuerySchoolHistoryScores extends QueryBridge
{
    /**
     * @inheritdoc
     */
    protected function query()
    {
        return <<<'SQL'
            SELECT
                vhsa.cod_aluno AS cod_aluno,
                vhsa.disciplina,
                vhsa.nota_1serie,
                vhsa.nota_2serie,
                vhsa.nota_3serie,
                vhsa.nota_4serie,
                vhsa.nota_5serie
            FROM relatorio.$P!{view_score} vhsa
            WHERE cod_aluno = $P{aluno}
            AND (select max(unnest) from unnest(tipos_base)) != 2;
SQL;
    }
}
