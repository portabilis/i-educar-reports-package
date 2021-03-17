<?php

use iEducar\Reports\JsonDataSource;

class StudentsMovementReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        return 'students-movement';
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
    }

    /**
     * Retorna o SQL para buscar os dados do relatório principal.
     *
     * TODO #refatorar
     *
     * @return string
     */
    public function getSqlMainReport()
    {
        $etapa = $this->args['etapa'] ?: 0;
        $ano = $this->args['ano'] ?: 0;
        $instituicao = $this->args['instituicao'] ?: 0;
        $escola = $this->args['escola'] ?: 0;
        $curso = $this->args['curso'] ?: 0;
        $serie = $this->args['serie'] ?: 0;
        $turma = $this->args['turma'] ?: 0;

        return "

SELECT
    public.fcn_upper(nm_instituicao) AS nome_instituicao,
    public.fcn_upper(nm_responsavel) AS nome_responsavel,
    instituicao.cidade AS cidade_instituicao,
    public.fcn_upper(ref_sigla_uf) AS uf_instituicao,
    escola.cod_escola AS cod_escola,
    escola_ano_letivo.ano,
    curso.nm_curso,
    serie.nm_serie,
    turma.nm_turma,
    to_char(CURRENT_DATE,'dd/mm/yyyy') AS data_atual,
    to_char(current_timestamp, 'HH24:MI:SS') AS hora_atual,
    to_char(alm.data_inicio,'dd/mm/yyyy') AS data_inicial,
    to_char(alm.data_fim,'dd/mm/yyyy') AS data_final,
    turma_turno.nome AS turno,
    to_char(COALESCE(instituicao.data_base_remanejamento, alm_ini.data_inicio),'dd/mm/yyyy') AS data_matricula_inicial,

(SELECT COUNT(DISTINCT(m.cod_matricula))
   FROM pmieducar.matricula_turma mt
  INNER JOIN pmieducar.matricula m ON (m.cod_matricula = mt.ref_cod_matricula)
  INNER JOIN pmieducar.aluno a ON (a.cod_aluno = m.ref_cod_aluno)
  INNER JOIN cadastro.fisica f ON (f.idpes = a.ref_idpes)
  INNER JOIN relatorio.view_situacao vs ON (vs.cod_matricula = m.cod_matricula
                                            AND vs.cod_turma = mt.ref_cod_turma
                                            AND vs.sequencial = mt.sequencial)
  WHERE mt.ref_cod_turma = turma.cod_turma
    AND f.sexo = 'M'
    AND m.ano = escola_ano_letivo.ano
    AND vs.cod_situacao = 10
    AND m.dependencia NOT IN (true)
    AND DATE(COALESCE(mt.data_enturmacao, m.data_matricula,m.data_cadastro)) <= (CASE WHEN {$etapa} IN (0,1) THEN
                                                                       COALESCE(instituicao.data_base_remanejamento, alm.data_inicio)
                                                                  ELSE alm.data_inicio
                                                             END)
    AND (CASE WHEN DATE(COALESCE(date(mt.data_exclusao),date(m.data_cancel))) IS NOT NULL
                   THEN DATE(COALESCE(date(mt.data_exclusao),date(m.data_cancel))) >= (CASE WHEN {$etapa} IN (0,1) THEN
                                                                       COALESCE(instituicao.data_base_remanejamento, alm.data_inicio)
                                                                  ELSE alm.data_inicio
                                                             END)
              ELSE TRUE
        END)) AS qtd_matr_masc,

(SELECT COUNT(DISTINCT(m.cod_matricula))
   FROM pmieducar.matricula_turma mt
  INNER JOIN pmieducar.matricula m ON (m.cod_matricula = mt.ref_cod_matricula)
  INNER JOIN pmieducar.aluno a ON (a.cod_aluno = m.ref_cod_aluno)
  INNER JOIN cadastro.fisica f ON (f.idpes = a.ref_idpes)
  INNER JOIN relatorio.view_situacao vs ON (vs.cod_matricula = m.cod_matricula
                                            AND vs.cod_turma = mt.ref_cod_turma
                                            AND vs.sequencial = mt.sequencial)
  WHERE mt.ref_cod_turma = turma.cod_turma
    AND f.sexo = 'F'
    AND m.ano = escola_ano_letivo.ano
    AND vs.cod_situacao = 10
    AND m.dependencia NOT IN (true)
    AND DATE(COALESCE(mt.data_enturmacao, m.data_matricula,m.data_cadastro)) <= (CASE WHEN {$etapa} IN (0,1) THEN
                                                                       COALESCE(instituicao.data_base_remanejamento, alm.data_inicio)
                                                                  ELSE alm.data_inicio
                                                             END)
    AND (CASE WHEN DATE(COALESCE(date(mt.data_exclusao),date(m.data_cancel))) IS NOT NULL
                   THEN DATE(COALESCE(date(mt.data_exclusao),date(m.data_cancel))) >= (CASE WHEN {$etapa} IN (0,1) THEN
                                                                       COALESCE(instituicao.data_base_remanejamento, alm.data_inicio)
                                                                  ELSE alm.data_inicio
                                                             END)
              ELSE TRUE
        END)) AS qtd_matr_femi,

(SELECT COUNT(DISTINCT(m.cod_matricula))
   FROM pmieducar.matricula_turma mt
  INNER JOIN pmieducar.matricula m ON (m.cod_matricula = mt.ref_cod_matricula)
  INNER JOIN pmieducar.aluno a ON (a.cod_aluno = m.ref_cod_aluno)
  INNER JOIN cadastro.fisica f ON (f.idpes = a.ref_idpes)
  INNER JOIN relatorio.view_situacao vs ON (vs.cod_matricula = m.cod_matricula
                                            AND vs.cod_turma = mt.ref_cod_turma
                                            AND vs.sequencial = mt.sequencial)
  WHERE mt.ref_cod_turma = turma.cod_turma
    AND f.sexo = 'M'
    AND m.ano = escola_ano_letivo.ano
    AND vs.cod_situacao = 4
    AND m.dependencia NOT IN (true)
    AND mt.transferido = true
    AND DATE(COALESCE(date(m.data_cancel),date(m.data_exclusao))) >= (CASE WHEN {$etapa} IN (0,1) THEN
                                                                                COALESCE(instituicao.data_base_remanejamento, alm.data_inicio)
                                                                           ELSE alm.data_inicio
                                                                      END)
    AND (CASE WHEN {$etapa} = 0 THEN DATE(COALESCE(date(m.data_cancel),date(m.data_exclusao))) <= CURRENT_DATE ELSE DATE(COALESCE(date(m.data_cancel),date(m.data_exclusao))) <= alm.data_fim END)) AS qtd_tran_masc,

(SELECT COUNT(DISTINCT(m.cod_matricula))
   FROM pmieducar.matricula_turma mt
  INNER JOIN pmieducar.matricula m ON (m.cod_matricula = mt.ref_cod_matricula)
  INNER JOIN pmieducar.aluno a ON (a.cod_aluno = m.ref_cod_aluno)
  INNER JOIN cadastro.fisica f ON (f.idpes = a.ref_idpes)
  INNER JOIN relatorio.view_situacao vs ON (vs.cod_matricula = m.cod_matricula
                                            AND vs.cod_turma = mt.ref_cod_turma
                                            AND vs.sequencial = mt.sequencial)
  WHERE mt.ref_cod_turma = turma.cod_turma
    AND f.sexo = 'F'
    AND m.ano = escola_ano_letivo.ano
    AND vs.cod_situacao = 4
    AND m.dependencia NOT IN (true)
    AND mt.transferido = true
    AND DATE(COALESCE(date(m.data_cancel),date(m.data_exclusao))) >= (CASE WHEN {$etapa} IN (0,1) THEN
                                                                                COALESCE(instituicao.data_base_remanejamento, alm.data_inicio)
                                                                           ELSE alm.data_inicio
                                                                      END)
    AND (CASE WHEN {$etapa} = 0 THEN DATE(COALESCE(date(m.data_cancel),date(m.data_exclusao))) <= CURRENT_DATE ELSE DATE(COALESCE(date(m.data_cancel),date(m.data_exclusao))) <= alm.data_fim END)) AS qtd_tran_femi,

(SELECT COUNT(DISTINCT(m.cod_matricula))
   FROM pmieducar.matricula_turma mt
  INNER JOIN pmieducar.matricula m ON (m.cod_matricula = mt.ref_cod_matricula)
  INNER JOIN pmieducar.aluno a ON (a.cod_aluno = m.ref_cod_aluno)
  INNER JOIN cadastro.fisica f ON (f.idpes = a.ref_idpes)
  INNER JOIN relatorio.view_situacao vs ON (vs.cod_matricula = m.cod_matricula
                                            AND vs.cod_turma = mt.ref_cod_turma
                                            AND vs.sequencial = mt.sequencial)
  WHERE mt.ref_cod_turma = turma.cod_turma
    AND f.sexo = 'M'
    AND m.ano = escola_ano_letivo.ano
    AND vs.cod_situacao = 6
    AND m.dependencia NOT IN (true)
    AND mt.abandono = true
    AND DATE(COALESCE(date(m.data_cancel),date(m.data_exclusao))) >= (CASE WHEN {$etapa} IN (0,1) THEN
                                                                                COALESCE(instituicao.data_base_remanejamento, alm.data_inicio)
                                                                           ELSE alm.data_inicio
                                                                      END)
    AND (CASE WHEN {$etapa} = 0 THEN DATE(COALESCE(date(m.data_cancel),date(m.data_exclusao))) <= CURRENT_DATE ELSE DATE(COALESCE(date(m.data_cancel),date(m.data_exclusao))) <= alm.data_fim END)) AS qtd_aban_masc,

(SELECT COUNT(DISTINCT(m.cod_matricula))
   FROM pmieducar.matricula_turma mt
  INNER JOIN pmieducar.matricula m ON (m.cod_matricula = mt.ref_cod_matricula)
  INNER JOIN pmieducar.aluno a ON (a.cod_aluno = m.ref_cod_aluno)
  INNER JOIN cadastro.fisica f ON (f.idpes = a.ref_idpes)
  INNER JOIN relatorio.view_situacao vs ON (vs.cod_matricula = m.cod_matricula
                                            AND vs.cod_turma = mt.ref_cod_turma
                                            AND vs.sequencial = mt.sequencial)
  WHERE mt.ref_cod_turma = turma.cod_turma
    AND f.sexo = 'F'
    AND m.ano = escola_ano_letivo.ano
    AND vs.cod_situacao = 6
    AND m.dependencia NOT IN (true)
    AND mt.abandono = true
    AND DATE(COALESCE(date(m.data_cancel),date(m.data_exclusao))) >= (CASE WHEN {$etapa} IN (0,1) THEN
                                                                                COALESCE(instituicao.data_base_remanejamento, alm.data_inicio)
                                                                           ELSE alm.data_inicio
                                                                      END)
    AND (CASE WHEN {$etapa} = 0 THEN DATE(COALESCE(date(m.data_cancel),date(m.data_exclusao))) <= CURRENT_DATE ELSE DATE(COALESCE(date(m.data_cancel),date(m.data_exclusao))) <= alm.data_fim END)) AS qtd_aban_femi,

(SELECT COUNT(DISTINCT(m.cod_matricula))
   FROM pmieducar.matricula_turma mt
  INNER JOIN pmieducar.matricula m ON (m.cod_matricula = mt.ref_cod_matricula)
  INNER JOIN pmieducar.aluno a ON (a.cod_aluno = m.ref_cod_aluno)
  INNER JOIN cadastro.fisica f ON (f.idpes = a.ref_idpes)
  INNER JOIN relatorio.view_situacao vs ON (vs.cod_matricula = m.cod_matricula
                                            AND vs.cod_turma = mt.ref_cod_turma
                                            AND vs.sequencial = mt.sequencial)
  WHERE mt.ref_cod_turma = turma.cod_turma
    AND f.sexo = 'M'
    AND m.ano = escola_ano_letivo.ano
    AND vs.cod_situacao = 10
    AND m.dependencia NOT IN (true)
    AND DATE(COALESCE(mt.data_enturmacao, m.data_matricula,m.data_cadastro)) > (CASE WHEN {$etapa} IN (0,1) THEN
                                                                      COALESCE(instituicao.data_base_remanejamento, alm.data_inicio)
                                                                 ELSE alm.data_inicio
                                                            END)
    AND (CASE WHEN {$etapa} = 0 THEN DATE(COALESCE(mt.data_enturmacao, m.data_matricula, m.data_cadastro)) <= CURRENT_DATE ELSE DATE(COALESCE(mt.data_enturmacao, m.data_matricula, m.data_cadastro)) <= alm.data_fim END)) AS qtd_admitidos_masc,

(SELECT COUNT(DISTINCT(m.cod_matricula))
   FROM pmieducar.matricula_turma mt
  INNER JOIN pmieducar.matricula m ON (m.cod_matricula = mt.ref_cod_matricula)
  INNER JOIN pmieducar.aluno a ON (a.cod_aluno = m.ref_cod_aluno)
  INNER JOIN cadastro.fisica f ON (f.idpes = a.ref_idpes)
  INNER JOIN relatorio.view_situacao vs ON (vs.cod_matricula = m.cod_matricula
                                            AND vs.cod_turma = mt.ref_cod_turma
                                            AND vs.sequencial = mt.sequencial)
  WHERE mt.ref_cod_turma = turma.cod_turma
    AND f.sexo = 'F'
    AND m.ano = escola_ano_letivo.ano
    AND vs.cod_situacao = 10
    AND m.dependencia NOT IN (true)
    AND DATE(COALESCE(mt.data_enturmacao, m.data_matricula,m.data_cadastro)) > (CASE WHEN {$etapa} IN (0,1) THEN
                                                                      COALESCE(instituicao.data_base_remanejamento, alm.data_inicio)
                                                                 ELSE alm.data_inicio
                                                            END)
    AND (CASE WHEN {$etapa} = 0 THEN DATE(COALESCE(mt.data_enturmacao, m.data_matricula, m.data_cadastro)) <= CURRENT_DATE ELSE DATE(COALESCE(mt.data_enturmacao, m.data_matricula, m.data_cadastro)) <= alm.data_fim END)) AS qtd_admitidos_femi,

(SELECT COUNT(DISTINCT(m.cod_matricula))
   FROM pmieducar.matricula_turma mt
  INNER JOIN pmieducar.matricula m ON (m.cod_matricula = mt.ref_cod_matricula)
  INNER JOIN pmieducar.aluno a ON (a.cod_aluno = m.ref_cod_aluno)
  INNER JOIN cadastro.fisica f ON (f.idpes = a.ref_idpes)
  INNER JOIN relatorio.view_situacao vs ON (vs.cod_matricula = m.cod_matricula
                                            AND vs.cod_turma = mt.ref_cod_turma
                                            AND vs.sequencial = mt.sequencial)
  WHERE mt.ref_cod_turma = turma.cod_turma
    AND f.sexo = 'M'
    AND m.ano = escola_ano_letivo.ano
    AND vs.cod_situacao = 10
    AND m.dependencia NOT IN (true)
    AND DATE(COALESCE(mt.data_enturmacao, m.data_matricula,m.data_cadastro)) <=  (CASE WHEN {$etapa} = 0 THEN CURRENT_DATE
                                                                                           ELSE alm.data_fim
                                                                                      END)
    AND (CASE WHEN DATE(COALESCE(date(mt.data_exclusao),date(m.data_cancel))) IS NOT NULL
                   THEN DATE(COALESCE(date(mt.data_exclusao),date(m.data_cancel))) > (CASE WHEN {$etapa} = 0 THEN CURRENT_DATE
                                                                                           ELSE alm.data_fim
                                                                                      END)
              ELSE TRUE
        END)) AS qtd_matr_final_masc,

(SELECT COUNT(DISTINCT(m.cod_matricula))
   FROM pmieducar.matricula_turma mt
  INNER JOIN pmieducar.matricula m ON (m.cod_matricula = mt.ref_cod_matricula)
  INNER JOIN pmieducar.aluno a ON (a.cod_aluno = m.ref_cod_aluno)
  INNER JOIN cadastro.fisica f ON (f.idpes = a.ref_idpes)
  INNER JOIN relatorio.view_situacao vs ON (vs.cod_matricula = m.cod_matricula
                                            AND vs.cod_turma = mt.ref_cod_turma
                                            AND vs.sequencial = mt.sequencial)
  WHERE mt.ref_cod_turma = turma.cod_turma
    AND f.sexo = 'F'
    AND m.ano = escola_ano_letivo.ano
    AND vs.cod_situacao = 10
    AND m.dependencia NOT IN (true)
    AND DATE(COALESCE(mt.data_enturmacao, m.data_matricula,m.data_cadastro)) <= (CASE WHEN {$etapa} = 0 THEN CURRENT_DATE
                                                                                           ELSE alm.data_fim
                                                                                      END)
    AND (CASE WHEN DATE(COALESCE(date(mt.data_exclusao),date(m.data_cancel))) IS NOT NULL
                   THEN DATE(COALESCE(date(mt.data_exclusao),date(m.data_cancel))) > (CASE WHEN {$etapa} = 0 THEN CURRENT_DATE
                                                                                           ELSE alm.data_fim
                                                                                      END)
              ELSE TRUE
        END)) AS qtd_matr_final_femi,

(SELECT COUNT(DISTINCT(m.cod_matricula))
   FROM pmieducar.matricula_turma mt
  INNER JOIN pmieducar.matricula m ON (m.cod_matricula = mt.ref_cod_matricula)
  INNER JOIN pmieducar.aluno a ON (a.cod_aluno = m.ref_cod_aluno)
  INNER JOIN cadastro.fisica f ON (f.idpes = a.ref_idpes)
  INNER JOIN relatorio.view_situacao vs ON (vs.cod_matricula = m.cod_matricula
                                            AND vs.cod_turma = mt.ref_cod_turma
                                            AND vs.sequencial = mt.sequencial)
  WHERE mt.ref_cod_turma = turma.cod_turma
    AND f.sexo = 'M'
    AND m.ano = escola_ano_letivo.ano
    AND vs.cod_situacao IN(1, 12, 13)
    AND (mt.remanejado = FALSE OR mt.remanejado IS NULL)
    AND m.dependencia NOT IN (true)) AS qtd_aprovado_masc,

(SELECT COUNT(DISTINCT(m.cod_matricula))
   FROM pmieducar.matricula_turma mt
  INNER JOIN pmieducar.matricula m ON (m.cod_matricula = mt.ref_cod_matricula)
  INNER JOIN pmieducar.aluno a ON (a.cod_aluno = m.ref_cod_aluno)
  INNER JOIN cadastro.fisica f ON (f.idpes = a.ref_idpes)
  INNER JOIN relatorio.view_situacao vs ON (vs.cod_matricula = m.cod_matricula
                                            AND vs.cod_turma = mt.ref_cod_turma
                                            AND vs.sequencial = mt.sequencial)
  WHERE mt.ref_cod_turma = turma.cod_turma
    AND f.sexo = 'F'
    AND m.ano = escola_ano_letivo.ano
    AND vs.cod_situacao IN(1, 12, 13)
    AND (mt.remanejado = FALSE OR mt.remanejado IS NULL)
    AND m.dependencia NOT IN (true)) AS qtd_aprovado_femi,

(SELECT COUNT(DISTINCT(m.cod_matricula))
   FROM pmieducar.matricula_turma mt
  INNER JOIN pmieducar.matricula m ON (m.cod_matricula = mt.ref_cod_matricula)
  INNER JOIN pmieducar.aluno a ON (a.cod_aluno = m.ref_cod_aluno)
  INNER JOIN cadastro.fisica f ON (f.idpes = a.ref_idpes)
  INNER JOIN relatorio.view_situacao vs ON (vs.cod_matricula = m.cod_matricula
                                            AND vs.cod_turma = mt.ref_cod_turma
                                            AND vs.sequencial = mt.sequencial)
  WHERE mt.ref_cod_turma = turma.cod_turma
    AND f.sexo = 'M'
    AND m.ano = escola_ano_letivo.ano
    AND vs.cod_situacao IN(2, 14)
    AND (mt.remanejado = FALSE OR mt.remanejado IS NULL)
    AND m.dependencia NOT IN (true)) AS qtd_reprovado_masc,

(SELECT COUNT(DISTINCT(m.cod_matricula))
   FROM pmieducar.matricula_turma mt
  INNER JOIN pmieducar.matricula m ON (m.cod_matricula = mt.ref_cod_matricula)
  INNER JOIN pmieducar.aluno a ON (a.cod_aluno = m.ref_cod_aluno)
  INNER JOIN cadastro.fisica f ON (f.idpes = a.ref_idpes)
  INNER JOIN relatorio.view_situacao vs ON (vs.cod_matricula = m.cod_matricula
                                            AND vs.cod_turma = mt.ref_cod_turma
                                            AND vs.sequencial = mt.sequencial)
  WHERE mt.ref_cod_turma = turma.cod_turma
    AND f.sexo = 'F'
    AND m.ano = escola_ano_letivo.ano
    AND vs.cod_situacao IN(2, 14)
    AND (mt.remanejado = FALSE OR mt.remanejado IS NULL)
    AND m.dependencia NOT IN (true)) AS qtd_reprovado_femi

  FROM pmieducar.instituicao
 INNER JOIN pmieducar.escola ON (escola.ref_cod_instituicao = instituicao.cod_instituicao)
 INNER JOIN pmieducar.escola_ano_letivo ON (escola_ano_letivo.ref_cod_escola = escola.cod_escola)
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
 INNER JOIN pmieducar.turma_turno ON (turma_turno.id = turma.turma_turno_id)
 INNER JOIN pmieducar.ano_letivo_modulo alm ON (alm.ref_ano = escola_ano_letivo.ano
                                                AND alm.ref_ref_cod_escola = escola.cod_escola)
 INNER JOIN pmieducar.ano_letivo_modulo alm_ini ON (alm_ini.ref_ano = escola_ano_letivo.ano
                                                    AND alm_ini.ref_ref_cod_escola = escola.cod_escola
                                                    AND alm_ini.sequencial = 1)
 WHERE escola_ano_letivo.ano = {$ano}
   AND instituicao.cod_instituicao = {$instituicao}
   AND escola.cod_escola = {$escola}
   AND curso.cod_curso = {$curso}
   AND turma.ano = {$ano}
   AND (CASE WHEN {$serie} = 0 THEN TRUE ELSE serie.cod_serie = {$serie} END)
   AND (CASE WHEN {$turma} = 0 THEN TRUE ELSE turma.cod_turma = {$turma} END)
   AND (CASE WHEN {$etapa} = 0 THEN alm.sequencial = 1 ELSE alm.sequencial = {$etapa} END)

order by nm_serie, nm_turma
        ";
    }
}
