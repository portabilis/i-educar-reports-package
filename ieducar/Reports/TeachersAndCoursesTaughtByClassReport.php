<?php

use iEducar\Reports\JsonDataSource;

require_once 'lib/Portabilis/Report/ReportCore.php';
require_once 'App/Model/IedFinder.php';

class TeachersAndCoursesTaughtByClassReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        return 'teachers-and-courses-taught-by-class';
    }

    /**
     * @inheritdoc
     */
    public function requiredArgs()
    {
        $this->addRequiredArg('ano');
        $this->addRequiredArg('instituicao');
        $this->addRequiredArg('escola');
        $this->addRequiredArg('curso');
        $this->addRequiredArg('serie');
    }
    
    public function getSqlMainReport()
    {
        $ano = $this->args['ano'] ?: 0;
        $instituicao = $this->args['instituicao'] ?: 0;
        $escola = $this->args['escola'] ?: 0;
        $curso = $this->args['curso'] ?: 0;
        $serie = $this->args['serie'] ?: 0;
        $turma = $this->args['turma'] ?: 0;

        return "

SELECT 
    nm_curso,
    nm_serie,
    turma.cod_turma,
    turma.nm_turma,
    (
        CASE WHEN ARRAY[2,3,4,5,6] <@ dias_semana THEN 
            'Seg à Sex' 
        ELSE 
            '' 
        END
    ) AS seg_sex,
    replace
    (
        trim
        (
            (CASE WHEN ARRAY[1] <@ dias_semana THEN 'Dom ' ELSE '' END) ||
            (CASE WHEN ARRAY[2] <@ dias_semana THEN 'Seg ' ELSE '' END) ||
            (CASE WHEN ARRAY[3] <@ dias_semana THEN 'Ter ' ELSE '' END) ||
            (CASE WHEN ARRAY[4] <@ dias_semana THEN 'Qua ' ELSE '' END) ||
            (CASE WHEN ARRAY[5] <@ dias_semana THEN 'Qui ' ELSE '' END) ||
            (CASE WHEN ARRAY[6] <@ dias_semana THEN 'Sex ' ELSE '' END) ||
            (CASE WHEN ARRAY[7] <@ dias_semana THEN 'Sab ' ELSE '' END)
        ), ' ', ', '
    ) AS dias_semana,
    pessoa.nome AS nm_docente,
    fisica.data_nasc,
    educacenso_cod_docente.cod_docente_inep AS inep,
    escolaridade.descricao AS escolaridade,
    view_componente_curricular.nome AS disciplina,
    (
        SELECT 
            to_char(sum(cast(servidor_alocacao.carga_horaria as interval)), 'HH24:MI')::varchar
        FROM 
            pmieducar.servidor_alocacao
        WHERE TRUE 
            AND servidor_alocacao.ref_cod_servidor = servidor.cod_servidor
            AND servidor_alocacao.ref_cod_escola   = escola.cod_escola
            AND servidor_alocacao.ano = {$ano}
    ) AS carga_horaria,
    (
        SELECT DISTINCT '' || (
            replace(
                textcat_all(
                    (
                        CASE WHEN servidor_alocacao.periodo = 1 THEN 'Mat.'
                        WHEN servidor_alocacao.periodo = 2 THEN 'Vesp.'
                        WHEN servidor_alocacao.periodo = 3 THEN 'Not.'
                        END
                    )
                ), ' <br> ', ', '
            )
        )
        FROM pmieducar.servidor_alocacao
        WHERE servidor_alocacao.ref_cod_servidor = servidor.cod_servidor
        AND servidor_alocacao.ref_cod_escola = escola.cod_escola
        AND servidor_alocacao.ano = {$ano}
        ORDER BY 1
    ) AS periodo,
    (
        CASE WHEN MAX(tipo_vinculo) = 1 THEN
            'Efetivo'
        WHEN MAX(tipo_vinculo) = 2 THEN
            'Temporário'
        WHEN MAX(tipo_vinculo) = 3 THEN
            'Terceirizado'
        WHEN MAX(tipo_vinculo) = 4 THEN
            'CLT'
        END
    ) AS vinculo
FROM pmieducar.instituicao
INNER JOIN pmieducar.escola ON TRUE 
    AND escola.ref_cod_instituicao = instituicao.cod_instituicao
INNER JOIN pmieducar.escola_curso ON TRUE 
    AND escola_curso.ativo = 1
    AND escola_curso.ref_cod_escola = escola.cod_escola
INNER JOIN pmieducar.curso ON TRUE 
    AND curso.cod_curso = escola_curso.ref_cod_curso
    AND curso.ativo = 1
INNER JOIN pmieducar.escola_serie ON TRUE 
    AND escola_serie.ativo = 1
    AND escola_serie.ref_cod_escola = escola.cod_escola
INNER JOIN pmieducar.serie ON TRUE 
    AND serie.cod_serie = escola_serie.ref_cod_serie
    AND serie.ativo = 1
INNER JOIN pmieducar.turma ON TRUE 
    AND turma.ref_ref_cod_escola = escola.cod_escola
    AND turma.ref_cod_curso = escola_curso.ref_cod_curso
    AND turma.ref_ref_cod_serie = escola_serie.ref_cod_serie
    AND turma.ativo = 1
INNER JOIN modules.professor_turma ON TRUE 
    AND professor_turma.turma_id = turma.cod_turma
    AND professor_turma.funcao_exercida IN(1,5,6)
INNER JOIN pmieducar.servidor ON TRUE 
    AND servidor.cod_servidor = professor_turma.servidor_id
    AND servidor.ativo = 1
INNER JOIN cadastro.pessoa ON TRUE 
    AND pessoa.idpes = servidor.cod_servidor
INNER JOIN cadastro.fisica ON TRUE 
    AND fisica.idpes = servidor.cod_servidor
LEFT JOIN educacenso_cod_docente ON TRUE 
    AND educacenso_cod_docente.cod_servidor = servidor.cod_servidor
LEFT JOIN cadastro.escolaridade ON TRUE 
    AND escolaridade.idesco = servidor.ref_idesco
INNER JOIN relatorio.view_componente_curricular ON TRUE 
    AND view_componente_curricular.cod_turma = turma.cod_turma
INNER JOIN modules.professor_turma_disciplina ON TRUE 
    AND professor_turma_disciplina.professor_turma_id = professor_turma.id
    AND professor_turma_disciplina.componente_curricular_id = view_componente_curricular.id
WHERE TRUE 
    AND instituicao.cod_instituicao = {$instituicao}
    AND escola.cod_escola = {$escola}
    AND curso.cod_curso = {$curso}
    AND serie.cod_serie = {$serie}
    AND 
    (
        CASE WHEN {$turma} = 0 THEN 
            TRUE 
        ELSE 
            turma.cod_turma = {$turma} 
        END
    )
    AND turma.ano = {$ano}
GROUP BY
    escola.cod_escola,
    curso.nm_curso,
    serie.nm_serie,
    turma.cod_turma,
    turma.nm_turma,
    pessoa.nome,
    fisica.data_nasc,
    educacenso_cod_docente.cod_docente_inep,
    escolaridade.descricao,
    view_componente_curricular.nome,
    servidor.cod_servidor
ORDER BY 
    curso.nm_curso,
    serie.nm_serie,
    turma.nm_turma,
    pessoa.nome,
    disciplina
    
        ";
    }
}
