<?php

trait FinalSituationTrait
{
    /**
     * @inheritdoc
     */
    protected function query()
    {
        $data_inicial = $this->args['data_inicial'];
        $data_final = $this->args['data_final'];
        $curso = $this->args['curso'];
        $escola = $this->args['escola'];
        $ano = $this->args['ano'];
        $instituicao = $this->args['instituicao'];


        return "SELECT
                nm_curso,
                nm_serie,
                count(distinct cod_turma) AS qtd_turmas,
                sum(CASE WHEN matricula_ativa AND sem_dependencia AND entrou_antes_inicio AND saiu_depois_inicio THEN 1 ELSE 0 END) AS qtd_inicial,
                sum(CASE WHEN matricula_ativa AND progrediu AND ultima_enturmacao AND sem_dependencia THEN 1 ELSE 0 END) AS qtd_final,
                sum(CASE WHEN matricula_ativa AND ultima_enturmacao AND sem_dependencia AND saiu_depois_fim THEN 1 ELSE 0 END) AS qtd_maxima,
                sum(CASE WHEN matricula_ativa and sequencial = 1 and entrada_reclassificado = false and entrou_durante then 1 else 0 end) as qtd_admitidos,
                SUM ( CASE WHEN matricula_ativa AND enturmacao_inativa AND saiu_durante AND sequencial < maior_sequencial THEN 1 ELSE 0 END ) AS qtd_remanejados,
                sum(CASE WHEN saiu_durante AND transferido AND enturmacao_transferida THEN 1 ELSE 0 END) AS qtd_transferidos,
                sum(CASE WHEN matricula_ativa AND sem_dependencia AND ultima_enturmacao AND abandono THEN 1 ELSE 0 END) AS qtd_abandono,
                sum(CASE WHEN falecido AND saiu_durante THEN 1 ELSE 0 END) AS qtd_obito,
                sum(CASE WHEN matricula_ativa AND aprovou AND ultima_enturmacao AND sem_dependencia THEN 1 ELSE 0 END) AS qtd_aprovados,
                sum(CASE WHEN matricula_ativa AND reprovou AND ultima_enturmacao AND sem_dependencia THEN 1 ELSE 0 END) AS qtd_reprovados
            FROM (
                SELECT
                    curso.nm_curso,
                    serie.nm_serie,
                    turma.cod_turma,
                    ie.registration_active AS matricula_ativa,
                    ie.registration_transferred AS transferido,
                    ie.enrollment_transferred AS enturmacao_transferida,
                    ie.registration_reclassified AS reclassificado,
                    ie.registration_abandoned AS abandono,
                    ie.registration_deceased AS falecido,
                    ie.registration_next AS progrediu,
                    ie.registration_approved AS aprovou,
                    ie.registration_reproved AS reprovou,
                    ie.enrollment_relocated AS remanejado,
                    ie.sequential as sequencial,
                    ie.last_sequential AS maior_sequencial,
                    ie.registration_was_reclassified as entrada_reclassificado,
                    ie.dependence NOT IN (true) AS sem_dependencia,
                    ie.enrollment_active = FALSE AS enturmacao_inativa,
                    ie.start_date < date('{$data_inicial}') AS entrou_antes,
                    ie.start_date >= date('{$data_inicial}') AS entrou_depois,
                    ie.start_date < date('{$data_inicial}') as entrou_antes_inicio,
                    ie.start_date <= date('{$data_final}') as entrou_antes_fim,
                    ie.start_date between date('{$data_inicial}') and date('{$data_final}') as entrou_durante,
                    ie.end_date is null or ie.end_date >= date('{$data_inicial}') as saiu_depois_inicio,
                    ie.end_date is null or ie.end_date > date('{$data_final}') as saiu_depois_fim,
                    ie.end_date between date('{$data_inicial}') and date('{$data_final}') as saiu_durante,
                    ie.sequential = ie.last_sequential AS ultima_enturmacao
                FROM pmieducar.instituicao
                INNER JOIN pmieducar.escola ON (escola.ref_cod_instituicao = instituicao.cod_instituicao)
                INNER JOIN pmieducar.escola_ano_letivo ON (escola_ano_letivo.ref_cod_escola = escola.cod_escola)
                INNER JOIN pmieducar.escola_curso ON (escola_curso.ref_cod_escola = escola.cod_escola AND escola_curso.ativo = 1)
                INNER JOIN pmieducar.curso ON (curso.cod_curso = escola_curso.ref_cod_curso AND curso.ativo = 1)
                INNER JOIN pmieducar.escola_serie ON (escola_serie.ref_cod_escola = escola.cod_escola AND escola_serie.ativo = 1)
                INNER JOIN pmieducar.serie ON (serie.cod_serie = escola_serie.ref_cod_serie AND serie.ativo = 1)
                INNER JOIN pmieducar.turma ON (turma.ref_ref_cod_escola = escola.cod_escola
                AND turma.ref_cod_curso = curso.cod_curso
                AND turma.ano = escola_ano_letivo.ano
                AND turma.ativo = 1)
                INNER JOIN pmieducar.matricula_turma ON (matricula_turma.ref_cod_turma = turma.cod_turma)
                INNER JOIN pmieducar.matricula ON (matricula.cod_matricula = matricula_turma.ref_cod_matricula
                    AND matricula.ref_ref_cod_escola = escola.cod_escola
                    AND matricula.ref_cod_curso = curso.cod_curso
                    AND matricula.ref_ref_cod_serie = serie.cod_serie
                    AND matricula.ano = escola_ano_letivo.ano)
                INNER JOIN public.info_enrollment ie ON (ie.enrollment_id = matricula_turma.id)
                WHERE instituicao.cod_instituicao = {$instituicao}
                AND escola_ano_letivo.ano = {$ano}
                AND CASE WHEN {$escola} = 0 THEN true ELSE escola.cod_escola = {$escola} END
                AND CASE WHEN coalesce({$curso}) = 0 THEN true ELSE curso.cod_curso IN ({$curso}) END
            ) AS dados
            GROUP BY
                nm_curso,
                nm_serie
            ORDER BY
                nm_curso,
                nm_serie;";
    }
}
