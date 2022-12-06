<?php

class QuerySchoolHistoryRegistrationTransferred extends QueryBridge
{
    /**
     * @inheritdoc
     */
    protected function query()
    {
        return <<<'SQL'
            SELECT
                ROW_NUMBER () OVER (ORDER BY view_componente_curricular.nome) AS sequencial,
                matricula.ano AS ano,
                cod_matricula,
                curso.nm_curso AS nm_curso,
                serie.nm_serie AS nm_serie,
                turma.nm_turma AS nm_turma,
                turma_turno.nome AS periodo,
                upper(view_componente_curricular.nome) AS disciplina,
                ncc1.nota_arredondada AS nota1,
                ncc2.nota_arredondada AS nota2,
                ncc3.nota_arredondada AS nota3,
                ncc4.nota_arredondada AS nota4
            FROM pmieducar.matricula
            INNER JOIN pmieducar.matricula_turma ON (matricula_turma.ref_cod_matricula = matricula.cod_matricula)
            INNER JOIN pmieducar.turma ON (turma.cod_turma = matricula_turma.ref_cod_turma)
            INNER JOIN pmieducar.curso ON (curso.cod_curso = matricula.ref_cod_curso)
            INNER JOIN pmieducar.serie ON (serie.cod_serie = matricula.ref_ref_cod_serie)
            LEFT JOIN pmieducar.turma_turno ON (turma_turno.id = turma.turma_turno_id)
            INNER JOIN relatorio.view_componente_curricular ON (true
                AND view_componente_curricular.cod_turma = turma.cod_turma
                AND view_componente_curricular.cod_serie = serie.cod_serie)
            LEFT JOIN modules.nota_aluno ON (nota_aluno.matricula_id = matricula.cod_matricula)
            LEFT JOIN modules.nota_componente_curricular ncc1 ON (ncc1.nota_aluno_id = nota_aluno.id
                AND ncc1.componente_curricular_id = view_componente_curricular.id
                AND ncc1.etapa = '1')
            LEFT JOIN modules.nota_componente_curricular ncc2 ON (ncc2.nota_aluno_id = nota_aluno.id
                AND ncc2.componente_curricular_id = view_componente_curricular.id
                AND ncc2.etapa = '2')
            LEFT JOIN modules.nota_componente_curricular ncc3 ON (ncc3.nota_aluno_id = nota_aluno.id
                AND ncc3.componente_curricular_id = view_componente_curricular.id
                AND ncc3.etapa = '3')
            LEFT JOIN modules.nota_componente_curricular ncc4 ON (ncc4.nota_aluno_id = nota_aluno.id
                AND ncc4.componente_curricular_id = view_componente_curricular.id
                AND ncc4.etapa = '4')
            WHERE matricula.cod_matricula IN ($P!{matriculas_transferido})
                AND matricula.ativo = 1
                AND view_componente_curricular.tipo_base = $P{tipo_base}
                AND matricula_turma.sequencial = relatorio.get_max_sequencial_matricula(matricula.cod_matricula);
SQL;
    }
}
