<?php

use iEducar\Reports\JsonDataSource;

class FinalResultReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        return 'final-result';
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
        $this->addRequiredArg('turma');
        $this->addRequiredArg('modelo');
    }

    /**
     * Retorna o SQL para buscar os dados do relatório principal.
     *
     * @return string
     */
    public function getSqlMainReport()
    {
        $escola = $this->args['escola'] ?: 0;
        $curso = $this->args['curso'] ?: 0;
        $situacao = $this->args['situacao'] ?: 0;
        $ano = $this->args['ano'] ?: 0;
        $turma = $this->args['turma'] ?: 0;
        $dependencia = $this->args['dependencia'] ?: 0;

        return "
SELECT DISTINCT matricula.cod_matricula,
       matricula.dependencia AS dependencia,
       matricula.ref_cod_aluno AS cod_aluno,
       relatorio.get_texto_sem_caracter_especial(public.fcn_upper(pessoa.nome)) AS nm_aluno,
       view_situacao.texto_situacao_simplificado AS situacao,
       round((modules.frequencia_da_matricula(matricula.cod_matricula))::numeric, 1) AS frequencia_geral,
       CASE WHEN matricula.aprovado IN (4,6) THEN '-'
            WHEN matricula_turma.remanejado THEN '-'
            WHEN isnumeric(nccm.media_arredondada) THEN replace(trunc(nccm.media_arredondada::numeric, ra.qtd_casas_decimais)::TEXT,'.',',')
            ELSE nccm.media_arredondada
       END AS media,
       CASE WHEN isnumeric(nccm.media_arredondada) AND nccm.media::decimal > 10 THEN 1 ELSE NULL END AS nota_maior_dez,
       componente_curricular.abreviatura AS nm_componente_curricular,
       componente_curricular.ordenamento AS ordenamento,
       matricula.ano AS ano,
       curso.nm_curso AS nome_curso,
       serie.nm_serie AS nome_serie,
       turma.nm_turma AS nome_turma,
       turma_turno.nome AS periodo,
       sequencial_fechamento
FROM pmieducar.instituicao
INNER JOIN pmieducar.escola ON escola.ref_cod_instituicao = instituicao.cod_instituicao
INNER JOIN pmieducar.escola_ano_letivo ON escola_ano_letivo.ref_cod_escola = escola.cod_escola
INNER JOIN pmieducar.escola_curso ON (escola_curso.ref_cod_escola = escola.cod_escola AND escola_curso.ativo = 1)
INNER JOIN pmieducar.escola_serie ON (escola_serie.ref_cod_escola = escola.cod_escola AND escola_serie.ativo = 1)
INNER JOIN pmieducar.curso ON (curso.cod_curso = escola_curso.ref_cod_curso AND curso.ativo = 1)
INNER JOIN pmieducar.serie ON (serie.cod_serie = escola_serie.ref_cod_serie AND serie.ativo = 1)
INNER JOIN pmieducar.turma ON (turma.ref_ref_cod_escola = escola.cod_escola
                          AND turma.ref_cod_curso = curso.cod_curso
                          AND turma.ref_ref_cod_serie = serie.cod_serie
                          AND turma.ano = escola_ano_letivo.ano
                          AND turma.ativo = 1)
INNER JOIN modules.regra_avaliacao_serie_ano rasa
  ON rasa.serie_id = serie.cod_serie
  AND turma.ano = rasa.ano_letivo
INNER JOIN modules.regra_avaliacao ra ON ra.id = rasa.regra_avaliacao_id
INNER JOIN pmieducar.turma_turno ON turma_turno.id = turma.turma_turno_id
INNER JOIN pmieducar.matricula_turma ON (matricula_turma.ref_cod_turma = turma.cod_turma)
INNER JOIN pmieducar.matricula ON (matricula.cod_matricula = matricula_turma.ref_cod_matricula
      AND matricula.ref_ref_cod_escola = escola.cod_escola
      AND matricula.ref_cod_curso = curso.cod_curso
      AND matricula.ref_ref_cod_serie = serie.cod_serie
      AND matricula.ano = escola_ano_letivo.ano
                              AND matricula.ativo = 1)
INNER JOIN relatorio.view_situacao ON (view_situacao.cod_matricula = matricula.cod_matricula
                                       AND view_situacao.cod_turma = matricula_turma.ref_cod_turma
                                       AND matricula_turma.sequencial = view_situacao.sequencial)
INNER JOIN relatorio.view_componente_curricular componente_curricular ON componente_curricular.cod_turma = turma.cod_turma
LEFT JOIN modules.nota_aluno ON nota_aluno.matricula_id = matricula.cod_matricula
LEFT JOIN modules.nota_componente_curricular_media nccm ON nccm.nota_aluno_id = nota_aluno.id AND nccm.componente_curricular_id = componente_curricular.id
INNER JOIN pmieducar.aluno ON (matricula.ref_cod_aluno = aluno.cod_aluno)
INNER JOIN cadastro.fisica ON (fisica.idpes = aluno.ref_idpes)
INNER JOIN cadastro.pessoa ON pessoa.idpes = fisica.idpes

WHERE escola_ano_letivo.ativo = 1
  AND escola_ano_letivo.ano = {$ano}
  AND matricula.ref_ref_cod_escola = {$escola}
  AND matricula.ref_cod_curso = {$curso}
  AND view_situacao.cod_situacao = {$situacao}
  AND (SELECT 1
         FROM pmieducar.aluno
        WHERE aluno.cod_aluno = matricula.ref_cod_aluno
          AND aluno.ativo = 1) > 0
  AND matricula.ano = {$ano}
  AND matricula_turma.ref_cod_turma = {$turma}
  AND (CASE WHEN {$dependencia} = 1 THEN matricula.dependencia = TRUE WHEN {$dependencia} = 2 THEN matricula.dependencia = FALSE ELSE TRUE END)
  AND matricula_turma.ref_cod_matricula = matricula.cod_matricula
  AND (CASE WHEN matricula.dependencia THEN EXISTS (SELECT 1
                                                      FROM pmieducar.disciplina_dependencia
                                                     WHERE ref_cod_matricula = matricula.cod_matricula
                                                       AND nccm.componente_curricular_id = ref_cod_disciplina) ELSE TRUE END)
  AND matricula_turma.sequencial = (SELECT MAX(mt.sequencial)
                                      FROM pmieducar.matricula_turma mt
                                     WHERE mt.ref_cod_matricula = matricula.cod_matricula
                                       AND mt.ref_cod_turma = turma.cod_turma)
  AND NOT verifica_existe_matricula_posterior_mesma_turma(view_situacao.cod_matricula, view_situacao.cod_turma)
GROUP BY matricula.cod_matricula,
         sequencial_fechamento,
         nm_aluno,
         view_situacao.texto_situacao_simplificado,
         matricula_turma.remanejado,
         ordenamento,
         matricula.ano,
         curso.nm_curso,
         serie.nm_serie,
         turma.nm_turma,
         turma_turno.nome,
         ra.qtd_casas_decimais,
         componente_curricular.id,
         componente_curricular.abreviatura,
         nccm.media,
         nccm.media_arredondada
ORDER BY sequencial_fechamento,
         nm_aluno,
         cod_matricula,
         situacao,
         ordenamento,
         nm_componente_curricular
        ";
    }
}
