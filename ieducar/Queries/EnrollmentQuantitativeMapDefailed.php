<?php

class EnrollmentQuantitativeMapDefailed extends QueryBridge
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
                curso.nm_curso AS nm_curso,
                serie.nm_serie AS nm_serie,
                turma.nm_turma AS nm_turma,
                turma.cod_turma AS id_turma,
                relatorio.get_nome_escola(escola.cod_escola) AS nm_escola,
                CASE $P{situacao}
                    WHEN 1 THEN 'Aprovado'
	                WHEN 2 THEN 'Reproado'
	                WHEN 3 THEN 'Cursando'
	                WHEN 4 THEN 'Transferido'
	                WHEN 5 THEN 'Reclassificado'
	                WHEN 6 THEN 'Abandono'
	                WHEN 9 THEN 'Exceto Transferidos/Abandono'
	                WHEN 10 THEN 'Todas'
	                WHEN 12 THEN 'Aprovado com dependência'
	                WHEN 13 THEN 'Aprovado pelo conselho'
	                WHEN 14 THEN 'Reprovado por faltas'
	                WHEN 15 THEN 'Falecido'
	            END AS situacao,
                MAX(COALESCE(matricula.data_matricula, matricula.data_cadastro)) AS ultima_matricula,
                COUNT(matricula.cod_matricula) AS total_alunos,
                COUNT(CASE WHEN fisica.sexo = 'M' THEN 1 ELSE null END) AS total_masculino,
                COUNT(CASE WHEN fisica.sexo = 'F' THEN 1 ELSE null END) AS total_feminino,
                (
                    SELECT count(1)
                    FROM pmieducar.turma t1
                    WHERE t1.ref_ref_cod_escola = escola.cod_escola
                    AND t1.ativo = 1
                    AND t1.turma_turno_id = 1
                    AND COALESCE(t1.ano, date_part('year', t1.data_cadastro)) = $P{ano}
                ) AS total_turma_1,
                (
                    SELECT count(1)
                    FROM pmieducar.turma t2
                    WHERE t2.ref_ref_cod_escola = escola.cod_escola
                    AND t2.ativo = 1
                    AND t2.turma_turno_id = 2
                    AND COALESCE(t2.ano, date_part('year', t2.data_cadastro)) = $P{ano}
                ) AS total_turma_2,
                (
                    SELECT count(1)
                    FROM pmieducar.turma t3
                    WHERE t3.ref_ref_cod_escola = escola.cod_escola
                    AND t3.ativo = 1
                    AND t3.turma_turno_id = 3
                    AND COALESCE(t3.ano, date_part('year', t3.data_cadastro)) = $P{ano}
                ) AS total_turma_3,
                (
                    SELECT count(1)
                    FROM pmieducar.turma t4
                    WHERE t4.ref_ref_cod_escola = escola.cod_escola
                    AND t4.ativo = 1
                    AND t4.turma_turno_id = 4
                    AND COALESCE(t4.ano, date_part('year', t4.data_cadastro)) = $P{ano}
                ) AS total_turma_4,
                (
                    SELECT count(1)
                    FROM pmieducar.turma
                    WHERE turma.ref_ref_cod_escola = escola.cod_escola
                    AND turma.ativo = 1
                    AND COALESCE(turma.ano, date_part('year',turma.data_cadastro)) = $P{ano}
                ) AS total_turma
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
			    AND view_situacao.cod_turma = matricula_turma.ref_cod_turma
			    AND view_situacao.sequencial = matricula_turma.sequencial)
            INNER JOIN pmieducar.aluno ON (aluno.cod_aluno = matricula.ref_cod_aluno)
            INNER JOIN cadastro.fisica ON (fisica.idpes = aluno.ref_idpes)
            WHERE CASE WHEN $P{escola} = 0 THEN TRUE ELSE escola.cod_escola = $P{escola} END
            AND matricula.ano = $P{ano}
            AND view_situacao.cod_situacao = $P{situacao}
            AND CASE
                WHEN '$P!{sexo}' = 'M' THEN cadastro.fisica.sexo = 'M'
	            WHEN '$P!{sexo}' = 'F' THEN cadastro.fisica.sexo = 'F'
                ELSE true
	        END
            AND CASE
                WHEN '$P!{data_ini}' <> '' THEN DATE(COALESCE(matricula.data_matricula,matricula.data_cadastro)) >= nullif('$P!{data_ini}', '')::date
                ELSE true
            END
            AND CASE
                WHEN '$P!{data_fim}' <> '' THEN DATE(COALESCE(matricula.data_matricula,matricula.data_cadastro)) <= nullif('$P!{data_fim}', '')::date
                ELSE true
            END
            AND CASE
                WHEN $P{dependencia} = 1 THEN matricula.dependencia = TRUE
                WHEN $P{dependencia} = 2 THEN matricula.dependencia = FALSE
                ELSE true
            END
            GROUP BY
                turma.cod_turma,
                turma.nm_turma,
                serie.nm_serie,
                curso.nm_curso,
                escola.cod_escola,
                instituicao.nm_instituicao,
                instituicao.nm_responsavel,
                turma.nm_turma
            ORDER BY
                nm_escola,
                nm_curso,
                nm_serie,
                nm_turma;
SQL;
    }
}
