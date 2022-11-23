<?php

trait TeachersPerSchoolClassTrait
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

        return <<<SQL
            SELECT nm_curso,
            nm_serie,
            cod_turma,
            nm_turma,
            (SELECT COUNT(*)
                FROM relatorio.view_situacao
                WHERE cod_situacao = 10
                    AND cod_turma = turma.cod_turma AND texto_situacao_simplificado IN ('Cur', 'Trs')) AS qtd_matricula,
            CASE tipo_atendimento
                WHEN 0 THEN 'Escolarização'
                WHEN 4 THEN 'Atividade Complementar'
                WHEN 5 THEN 'Atendimento educacional especializado (AEE)'
            END AS tipo_atendimento,
            (SELECT count(*)
                FROM modules.professor_turma
                WHERE funcao_exercida = 1
                AND turma_id = turma.cod_turma AND professor_turma.ano = {$ano})::int AS docente,
            (SELECT count(*)
                FROM modules.professor_turma
                WHERE funcao_exercida = 2
                AND turma_id = turma.cod_turma AND professor_turma.ano = {$ano}) AS assistente_educacional,
            (SELECT count(*)
                FROM modules.professor_turma
                WHERE funcao_exercida = 3
                AND turma_id = turma.cod_turma AND professor_turma.ano = {$ano}) AS professor_ativ_complementar,
            (SELECT count(*)
                FROM modules.professor_turma
                WHERE funcao_exercida = 4
                AND turma_id = turma.cod_turma AND professor_turma.ano = {$ano}) AS tradutor_libras,
            (SELECT count(*)
                FROM modules.professor_turma
                WHERE funcao_exercida = 5
                AND turma_id = turma.cod_turma AND professor_turma.ano = {$ano}) AS docente_titular,
            (SELECT count(*)
                FROM modules.professor_turma
                WHERE funcao_exercida = 6
                AND turma_id = turma.cod_turma AND professor_turma.ano = {$ano}) AS docente_tutor
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
        INNER JOIN modules.professor_turma ON (professor_turma.turma_id = turma.cod_turma)
        WHERE instituicao.cod_instituicao = {$instituicao}
        AND escola.cod_escola = {$escola}
        AND (CASE WHEN {$curso} = 0 THEN true ELSE curso.cod_curso = {$curso} END)
        AND (CASE WHEN {$serie} = 0 THEN true ELSE serie.cod_serie = {$serie} END)
        AND (CASE WHEN {$turma} = 0 THEN true ELSE turma.cod_turma = {$turma} END)
        AND turma.ano = {$ano}
        GROUP BY turma.cod_turma,
                turma.nm_turma,
                serie.cod_serie,
                curso.cod_curso,
                turma.tipo_atendimento,
                curso.nm_curso,
                serie.nm_serie,
                escola.cod_escola
        ORDER BY nm_curso, nm_serie, nm_turma;
SQL;
    }
}
