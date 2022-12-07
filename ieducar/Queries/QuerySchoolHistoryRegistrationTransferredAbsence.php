<?php

class QuerySchoolHistoryRegistrationTransferredAbsence extends QueryBridge
{
    /**
     * @inheritdoc
     */
    protected function query()
    {
        return <<<'SQL'
            SELECT tipo_falta,
                matricula.ref_cod_aluno AS cod_aluno,
                matricula.cod_matricula,
                CASE tipo_falta
                WHEN 1 THEN (
                    SELECT sum(falta_geral.quantidade)
                    FROM modules.falta_geral
                    WHERE falta_geral.falta_aluno_id = falta_aluno.id
                )
                WHEN 2 THEN (
                    SELECT sum(falta_componente_curricular.quantidade)
                    FROM modules.falta_componente_curricular
                    WHERE falta_componente_curricular.falta_aluno_id = falta_aluno.id
                )
                END AS total_faltas,
                modules.frequencia_da_matricula(cod_matricula) AS frequencia
            FROM pmieducar.matricula
            INNER JOIN modules.falta_aluno ON (falta_aluno.matricula_id = matricula.cod_matricula)
            WHERE matricula.cod_matricula IN ($P!{matriculas_transferido})
SQL;
    }
}
