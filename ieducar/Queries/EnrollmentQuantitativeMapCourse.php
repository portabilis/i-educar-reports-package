<?php

class EnrollmentQuantitativeMapCourse extends QueryBridge
{
    /**
     * @inheritdoc
     */
    protected function query()
    {
        return <<<'SQL'
            SELECT
                public.fcn_upper(nm_instituicao) AS nome_instituicao,
                public.fcn_upper(nm_responsavel) AS nome_responsavel,
                curso.nm_curso,
                CASE
                    WHEN $P{situacao} = 1 THEN 'Aprovado'
	                WHEN $P{situacao} = 2 THEN 'Reprovado'
	                WHEN $P{situacao} = 3 THEN 'Cursando'
	                WHEN $P{situacao} = 4 THEN 'Transferido'
	                WHEN $P{situacao} = 5 THEN 'Reclassificado'
	                WHEN $P{situacao} = 6 THEN 'Abandono'
	                WHEN $P{situacao} = 9 THEN 'Exceto Transferidos/Abandono'
	                WHEN $P{situacao} = 10 THEN 'Todas'
	                WHEN $P{situacao} = 12 THEN 'Aprovado com dependência'
	                WHEN $P{situacao} = 13 THEN 'Aprovado pelo conselho'
	                WHEN $P{situacao} = 14 THEN 'Reprovado por faltas'
                    WHEN $P{situacao} = 15 THEN 'Falecido'
                END AS situacao,
                MAX(COALESCE(matricula.data_matricula, matricula.data_cadastro)) AS ultima_matricula,
                COUNT(matricula.cod_matricula) AS total_alunos,
                COUNT(CASE WHEN fisica.sexo = 'M' THEN 1 ELSE null END) AS total_masculino,
                COUNT(CASE WHEN fisica.sexo = 'F' THEN 1 ELSE null END) AS total_feminino
            FROM pmieducar.instituicao
            INNER JOIN pmieducar.escola ON (pmieducar.escola.ref_cod_instituicao = pmieducar.instituicao.cod_instituicao)
            INNER JOIN pmieducar.escola_ano_letivo ON (pmieducar.escola_ano_letivo.ref_cod_escola = pmieducar.escola.cod_escola) INNER JOIN pmieducar.escola_curso ON (escola_curso.ativo = 1
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
				AND view_situacao.cod_turma = matricula_turma.ref_cod_turma
				AND view_situacao.sequencial = matricula_turma.sequencial)
            INNER JOIN pmieducar.aluno on (pmieducar.matricula.ref_cod_aluno = pmieducar.aluno.cod_aluno)
            INNER JOIN cadastro.fisica on (pmieducar.aluno.ref_idpes = cadastro.fisica.idpes)
            WHERE TRUE
                AND instituicao.cod_instituicao = $P{instituicao}
                AND CASE
                    WHEN '$P!{sexo}' = 'A' THEN cadastro.fisica.sexo IN ('M', 'F')
	                WHEN '$P!{sexo}' = 'M' THEN cadastro.fisica.sexo = 'M'
	                WHEN '$P!{sexo}' = 'F' THEN cadastro.fisica.sexo = 'F'
	            END
                AND pmieducar.escola_ano_letivo.ano = $P{ano}
                AND pmieducar.matricula.ano = pmieducar.escola_ano_letivo.ano
                AND CASE WHEN $P{escola} = 0 THEN TRUE ELSE escola.cod_escola = $P{escola} END
                AND CASE
                    WHEN '$P!{data_ini}' <> '' THEN DATE(COALESCE(matricula.data_matricula,matricula.data_cadastro)) >= nullif('$P!{data_ini}', '')::date
                    ELSE TRUE
                END
                AND CASE
                    WHEN '$P!{data_fim}' <> '' THEN DATE(COALESCE(matricula.data_matricula,matricula.data_cadastro)) <= nullif('$P!{data_fim}', '')::date
                    ELSE TRUE
                END
                AND view_situacao.cod_situacao = $P{situacao}
                AND CASE
                    WHEN $P{dependencia} = 1 THEN matricula.dependencia = TRUE
                    WHEN $P{dependencia} = 2 THEN matricula.dependencia = FALSE
                    ELSE true
                END
            GROUP BY
                nm_instituicao,
                nm_responsavel,
                cod_curso,
                nm_curso
            ORDER BY nm_curso;
SQL;
    }
}
