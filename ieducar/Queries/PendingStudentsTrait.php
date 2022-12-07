<?php

trait PendingStudentsTrait
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
        $ano = $this->args['ano'];
        $etapa = $this->args['etapa'];
        $turma = $this->args['turma'];

        return <<<SQL
                    SELECT
                            *
                    FROM
                            (
                                SELECT
                                    relatorio.get_nome_escola(t.ref_ref_cod_escola) AS nm_escola,
                                    c.nm_curso,
                                    s.nm_serie,
                                    t.nm_turma,
                                    (
                                        SELECT
                                            count(
                                                distinct m.cod_matricula
                                            )
                                        FROM
                                            pmieducar.matricula_turma mt
                                        INNER JOIN
                                            pmieducar.matricula m ON true
                                                    AND m.cod_matricula = mt.ref_cod_matricula
                                        INNER JOIN
                                            pmieducar.turma ON true
                                                AND turma.cod_turma = mt.ref_cod_turma
                                        INNER JOIN
                                            relatorio.view_situacao vs ON true
                                                    AND vs.cod_matricula = m.cod_matricula
                                                    AND vs.cod_turma = mt.ref_cod_turma
                                                    AND vs.cod_situacao = 9
                                                    AND vs.sequencial = mt.sequencial
                                        INNER JOIN
                                            relatorio.view_componente_curricular vcc ON true
                                                    AND vcc.cod_turma = mt.ref_cod_turma
                                                    AND vcc.cod_serie = m.ref_ref_cod_serie
                                        INNER JOIN
                                            relatorio.view_dados_modulo vdm ON true
                                                    AND vdm.cod_turma = mt.ref_cod_turma
                                        LEFT JOIN
                                            modules.nota_aluno na ON true
                                                    AND na.matricula_id = m.cod_matricula
                                        LEFT JOIN
                                            modules.nota_componente_curricular ncc ON true
                                                    AND ncc.nota_aluno_id = na.id
                                                    AND ncc.componente_curricular_id = vcc.id
                                                    AND ncc.etapa = vdm.sequencial::varchar
                                        LEFT JOIN
                                            pmieducar.dispensa_disciplina dd ON true
                                                    AND dd.ref_cod_matricula = m.cod_matricula
                                                    AND dd.ref_cod_disciplina = vcc.id
                                        LEFT JOIN
                                            pmieducar.dispensa_etapa de ON true
                                                    AND de.ref_cod_dispensa = dd.cod_dispensa
                                                    AND de.etapa = vdm.sequencial
                                        WHERE true
                                            AND mt.ref_cod_turma = t.cod_turma
                                            AND m.ref_ref_cod_serie = s.cod_serie
                                            AND
                                                (
                                                    CASE WHEN
                                                        {$etapa} = '0'
                                                    THEN
                                                        TRUE
                                                    ELSE
                                                        vdm.sequencial IN ({$etapa})
                                                    END
                                                )
                                            AND ncc.nota_original IS NULL
                                            AND dd.ref_cod_matricula IS NULL
                                            AND COALESCE(turma.ref_cod_disciplina_dispensada, 0) <> vcc.id
                                    ) as alunos_sem_todas_notas
                                FROM
                                    pmieducar.turma t
                                LEFT JOIN pmieducar.turma_serie ts ON ts.turma_id = t.cod_turma
                                INNER JOIN
                                    pmieducar.curso c ON true
                                            AND c.cod_curso = t.ref_cod_curso
                                INNER JOIN
                                    pmieducar.serie s ON true
                                            AND s.cod_serie = coalesce(ts.serie_id, t.ref_ref_cod_serie)
                                WHERE true
                                    AND t.ano = {$ano}
                                    AND t.ref_cod_instituicao = {$instituicao}
                                    AND ({$escola} = 0 OR t.ref_ref_cod_escola = {$escola})
                                    AND ({$curso} = 0 OR c.cod_curso = {$curso})
                                    AND ({$serie} = 0 OR s.cod_serie = {$serie})
                                    AND ({$turma} = 0 OR t.cod_turma = {$turma})
                                ORDER BY
                                    nm_escola,
                                    cod_curso,
                                    nm_serie,
                                    nm_turma
                            ) as t
                    WHERE
                        t.alunos_sem_todas_notas > 0
SQL;
    }
}
