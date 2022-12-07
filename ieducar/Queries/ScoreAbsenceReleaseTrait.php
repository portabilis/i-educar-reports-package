<?php

trait ScoreAbsenceReleaseTrait
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
        $sexo = $this->args['sexo'];

        return <<<SQL
            SELECT
                relatorio.get_nome_escola(escola.cod_escola) AS escola,
                curso.nm_curso  AS curso,
                serie.nm_serie AS serie,
                turma.nm_turma AS turma,
                (
                    SELECT COALESCE(count(*),0)
                    FROM modules.nota_aluno
                    INNER JOIN modules.nota_componente_curricular ON (nota_componente_curricular.nota_aluno_id = nota_aluno.id)
                    WHERE nota_aluno.matricula_id IN (
                        SELECT ref_cod_matricula
                        FROM pmieducar.matricula_turma
                        WHERE ref_cod_turma = turma.cod_turma
                        AND ativo = 1
                    )
                ) AS notas_lancadas,
                (
                    COALESCE(
                        SUM((
                            relatorio.get_qtde_modulo(cod_turma) *
                            (
                                SELECT COUNT(vcc.cod_turma)
                                FROM relatorio.view_componente_curricular vcc
                                WHERE vcc.cod_turma = turma.cod_turma
                            ) + (
                                SELECT COALESCE(count(*),0)
                                FROM modules.nota_aluno
                                INNER JOIN modules.nota_componente_curricular ON (nota_componente_curricular.nota_aluno_id = nota_aluno.id)
                                WHERE nota_componente_curricular.etapa = 'Rc'
                                AND nota_aluno.matricula_id IN (
                                    SELECT ref_cod_matricula
                                    FROM pmieducar.matricula_turma
                                    WHERE ref_cod_turma = turma.cod_turma
                                    AND ativo = 1
                                )
                            )
                        )),0
                    )
                ) AS total_notas_serem_lancadas,
                (
                    SELECT COALESCE(count(*),0) + (
                        SELECT (COALESCE(count(*),0) * relatorio.get_qtde_modulo(cod_turma))
                        FROM modules.falta_aluno
                        WHERE falta_aluno.tipo_falta = 2
                        AND falta_aluno.matricula_id IN (
                            SELECT ref_cod_matricula
                            FROM pmieducar.matricula_turma
                            WHERE ref_cod_turma = turma.cod_turma
                            AND ativo = 1
                        )
                    )
                    FROM modules.falta_aluno
                    INNER JOIN modules.falta_geral ON (falta_geral.falta_aluno_id = falta_aluno.id)
                    WHERE falta_aluno.tipo_falta = 1
                    AND falta_aluno.matricula_id IN (
                        SELECT ref_cod_matricula
                        FROM pmieducar.matricula_turma
                        WHERE ref_cod_turma = turma.cod_turma
                        AND ativo = 1
                    )
                ) AS falta_lancadas,
                (
                    SELECT (COALESCE(count(*),0) * relatorio.get_qtde_modulo(cod_turma)) + (
                        SELECT (COALESCE(count(*),0) * relatorio.get_qtde_modulo(cod_turma))
                        FROM modules.falta_aluno
                        WHERE falta_aluno.tipo_falta = 2
                        AND falta_aluno.matricula_id IN (
                            SELECT ref_cod_matricula
                            FROM pmieducar.matricula_turma
                            WHERE ref_cod_turma = turma.cod_turma
                            AND ativo = 1
                        )
                    )
                    FROM modules.falta_aluno
                    WHERE falta_aluno.tipo_falta = 1
                    AND falta_aluno.matricula_id IN (
                        SELECT ref_cod_matricula
                        FROM pmieducar.matricula_turma
                        WHERE ref_cod_turma = turma.cod_turma
                        AND ativo = 1
                    )
                ) AS total_faltas_serem_lancadas
            FROM pmieducar.instituicao
            INNER JOIN pmieducar.escola ON (escola.ref_cod_instituicao = instituicao.cod_instituicao)
            INNER JOIN pmieducar.escola_ano_letivo ON (escola_ano_letivo.ref_cod_escola = escola.cod_escola)
            INNER JOIN pmieducar.escola_curso ON (escola_curso.ref_cod_escola = escola.cod_escola AND escola_curso.ativo = 1)
            INNER JOIN pmieducar.escola_serie ON (escola_serie.ref_cod_escola = escola.cod_escola AND escola_serie.ativo = 1)
            INNER JOIN pmieducar.curso ON (curso.cod_curso = escola_curso.ref_cod_curso AND curso.ativo = 1)
            INNER JOIN pmieducar.serie ON (serie.cod_serie = escola_serie.ref_cod_serie AND serie.ativo = 1)
            INNER JOIN pmieducar.turma ON (turma.ref_ref_cod_escola = escola.cod_escola
                AND turma.ref_cod_curso = curso.cod_curso
                AND turma.ref_ref_cod_serie = serie.cod_serie
                AND turma.ano = escola_ano_letivo.ano
                AND turma.ativo = 1)
            INNER JOIN pmieducar.matricula_turma ON (matricula_turma.ref_cod_turma = turma.cod_turma)
            INNER JOIN pmieducar.matricula ON (matricula.cod_matricula = matricula_turma.ref_cod_matricula
                AND matricula.ref_ref_cod_escola = escola.cod_escola
                AND matricula.ref_cod_curso = curso.cod_curso
                AND matricula.ref_ref_cod_serie = serie.cod_serie
                AND matricula.ano = escola_ano_letivo.ano
                AND matricula.ativo = 1)
            INNER JOIN pmieducar.aluno ON (matricula.ref_cod_aluno = aluno.cod_aluno)
            INNER JOIN cadastro.fisica ON (fisica.idpes = aluno.ref_idpes)
            WHERE true
                AND instituicao.cod_instituicao = {$instituicao}
                AND escola_ano_letivo.ano = {$ano}
                AND matricula.ativo = 1
                AND matricula_turma.ativo = 1
                AND ({$escola} = 0 OR escola.cod_escola = {$escola})
                AND ({$curso} = 0 OR curso.cod_curso = {$curso})
                AND ({$serie} = 0 OR serie.cod_serie = {$serie})
                AND ({$turma} = 0 OR turma.cod_turma = {$turma})
                AND ('{$sexo}' = '' OR fisica.sexo = '{$sexo}')
            GROUP BY
                escola,
                escola.cod_escola,
                curso.nm_curso,
                serie.nm_serie,
                turma.nm_turma,
                turma.cod_turma
            ORDER BY escola
SQL;
    }
}
