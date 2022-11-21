<?php

class EnrollmentQuantitativeMapSchoolGradePeriod extends QueryBridge
{
    /**
     * @inheritdoc
     */
    protected function query()
    {
        return <<<'SQL'
            SELECT
                relatorio.get_nome_escola(escola.cod_escola) AS nm_escola,
                serie.nm_serie AS nm_serie,
                turma_turno.nome as periodo,
                COUNT(matricula.cod_matricula) AS total_alunos,
                COUNT(turma.cod_turma) as total_turmas,
                COUNT(CASE WHEN fisica.sexo = 'M' THEN 1 ELSE null END) AS total_masculino,
                COUNT(CASE WHEN fisica.sexo = 'F' THEN 1 ELSE null END) AS total_feminino
            FROM pmieducar.instituicao
            INNER JOIN pmieducar.escola ON pmieducar.escola.ref_cod_instituicao = pmieducar.instituicao.cod_instituicao
            INNER JOIN pmieducar.escola_ano_letivo ON pmieducar.escola_ano_letivo.ref_cod_escola = pmieducar.escola.cod_escola
            INNER JOIN pmieducar.escola_curso ON true
                AND escola_curso.ativo = 1
                AND escola_curso.ref_cod_escola = escola.cod_escola
            INNER JOIN pmieducar.curso ON true
                AND curso.cod_curso = escola_curso.ref_cod_curso
                AND curso.ativo = 1
            INNER JOIN pmieducar.escola_serie ON true
                AND escola_serie.ativo = 1
                AND escola_serie.ref_cod_escola = escola.cod_escola
            INNER JOIN pmieducar.serie ON true
                AND serie.cod_serie = escola_serie.ref_cod_serie
                AND serie.ativo = 1
            INNER JOIN pmieducar.turma ON true
                AND turma.ref_ref_cod_escola = escola.cod_escola
                AND turma.ativo = 1
            INNER JOIN pmieducar.matricula_turma ON matricula_turma.ref_cod_turma = turma.cod_turma
            INNER JOIN pmieducar.matricula ON true
                AND matricula.cod_matricula = matricula_turma.ref_cod_matricula
                AND matricula.ref_cod_curso = curso.cod_curso
                AND matricula.ref_ref_cod_serie = serie.cod_serie
            INNER JOIN relatorio.view_situacao ON true
                AND view_situacao.cod_matricula = matricula.cod_matricula
			    AND view_situacao.cod_turma = matricula_turma.ref_cod_turma
			    AND view_situacao.sequencial = matricula_turma.sequencial
            INNER JOIN pmieducar.aluno ON pmieducar.matricula.ref_cod_aluno = pmieducar.aluno.cod_aluno
            INNER JOIN cadastro.fisica ON pmieducar.aluno.ref_idpes = cadastro.fisica.idpes
            LEFT JOIN pmieducar.turma_turno ON turma_turno.id = turma.turma_turno_id
            WHERE true
                AND instituicao.cod_instituicao = $P{instituicao}
                AND CASE
                    WHEN '$P!{sexo}' = 'M' THEN cadastro.fisica.sexo = 'M'
	                WHEN '$P!{sexo}' = 'F' THEN cadastro.fisica.sexo = 'F'
                    ELSE true
	            END
                AND pmieducar.escola_ano_letivo.ano = $P{ano}
                AND pmieducar.matricula.ano = pmieducar.escola_ano_letivo.ano
                AND CASE WHEN $P{escola} = 0 THEN TRUE ELSE escola.cod_escola = $P{escola} END
                AND CASE WHEN '$P!{cursos}' = '0' THEN TRUE ELSE curso.cod_curso IN ($P!{cursos}) END
                AND view_situacao.cod_situacao = $P{situacao}
                AND CASE
                    WHEN $P{dependencia} = 1 THEN matricula.dependencia = TRUE
                    WHEN $P{dependencia} = 2 THEN matricula.dependencia = FALSE
                    ELSE true
                END
                GROUP BY
                    nm_escola,
                    nm_serie,
                    periodo,
                    turma.cod_turma
                ORDER BY
                    nm_escola,
                    nm_serie,
                    periodo,
                    turma.cod_turma;
SQL;
    }
}
