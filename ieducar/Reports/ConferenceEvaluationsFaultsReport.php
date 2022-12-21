<?php

use iEducar\Reports\JsonDataSource;

class ConferenceEvaluationsFaultsReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        if ($this->useJson()) {
            return 'conference-evaluations-faults';
        }

        return  $this->args['modelo'] == 0 ? 'portabilis_conferencia_notas_faltas' : 'portabilis_conferencia_notas_faltas_simplificado';
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
    }


    /**
     * @inheritdoc
     */
    public function useJson()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getJsonData()
    {
        $queryMainReport = $this->getSqlMainReport();
        $queryHeaderReport = $this->getSqlHeaderReport();

        return [
            'main' => Portabilis_Utils_Database::fetchPreparedQuery($queryMainReport),
            'header' => Portabilis_Utils_Database::fetchPreparedQuery($queryHeaderReport),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getJsonQuery()
    {
        return 'main';
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
        $instituicao = $this->args['instituicao'];
        $ano = $this->args['ano'];
        $escola = $this->args['escola'];
        $curso = $this->args['curso'];
        $serie = $this->args['serie'];
        $turma = $this->args['turma'];
        $situacaoMatricula = $this->args['situacaoMatricula'];


        return "
SELECT matricula.cod_matricula AS cod_matricula,
       aluno.cod_aluno AS cod_aluno,
       relatorio.get_texto_sem_caracter_especial(pessoa.nome) AS nm_aluno,
       view_situacao.texto_situacao_simplificado AS situacao_simplificado,
       CASE
           WHEN matricula_turma.remanejado = true THEN null
           ELSE
              trim(to_char(modules.frequencia_da_matricula(matricula.cod_matricula),'99999999999D99'))::character varying
       END AS frequencia_geral,
       CASE
           WHEN matricula_turma.remanejado = true THEN '-'
           ELSE
              CASE WHEN nota_componente_curricular_etapa1.nota_arredondada ~ '^-?[0-9]+\.?[0-9]*$' THEN
                replace(trunc(nota_componente_curricular_etapa1.nota_arredondada::numeric, COALESCE(regra_avaliacao.qtd_casas_decimais, 1))::varchar, '.', ',')
              ELSE
                nota_componente_curricular_etapa1.nota_arredondada
              END
       END AS nota1,
       CASE
           WHEN matricula_turma.remanejado = true THEN '-'
           ELSE
              CASE WHEN nota_componente_curricular_etapa2.nota_arredondada ~ '^-?[0-9]+\.?[0-9]*$' THEN
                replace(trunc(nota_componente_curricular_etapa2.nota_arredondada::numeric, COALESCE(regra_avaliacao.qtd_casas_decimais, 1))::varchar, '.', ',')
              ELSE
                nota_componente_curricular_etapa2.nota_arredondada
              END
       END AS nota2,
       CASE
           WHEN matricula_turma.remanejado = true THEN '-'
           ELSE
              CASE WHEN nota_componente_curricular_etapa3.nota_arredondada ~ '^-?[0-9]+\.?[0-9]*$' THEN
                replace(trunc(nota_componente_curricular_etapa3.nota_arredondada::numeric, COALESCE(regra_avaliacao.qtd_casas_decimais, 1))::varchar, '.', ',')
              ELSE
                nota_componente_curricular_etapa3.nota_arredondada
              END
       END AS nota3,
       CASE
           WHEN matricula_turma.remanejado = true THEN '-'
           ELSE
              CASE WHEN nota_componente_curricular_etapa4.nota_arredondada ~ '^-?[0-9]+\.?[0-9]*$' THEN
                replace(trunc(nota_componente_curricular_etapa4.nota_arredondada::numeric, COALESCE(regra_avaliacao.qtd_casas_decimais, 1))::varchar, '.', ',')
              ELSE
                nota_componente_curricular_etapa4.nota_arredondada
              END
       END AS nota4,
  (SELECT replace(textcat_all(abv), '<br>','|') FROM (SELECT abreviatura || ' - ' || nome AS abv
      FROM relatorio.view_componente_curricular
      WHERE view_componente_curricular.cod_turma = turma.cod_turma
      ORDER BY abreviatura, nome)  tabl) as legenda,

  CASE
      WHEN matricula_turma.remanejado = true THEN null
      ELSE
         (SELECT COALESCE(
                     (SELECT SUM(quantidade)
                      FROM modules.falta_componente_curricular, modules.falta_aluno
                      WHERE falta_componente_curricular.falta_aluno_id = falta_aluno.id
                        AND falta_componente_curricular.componente_curricular_id = view_componente_curricular.id
                        AND falta_aluno.matricula_id = matricula.cod_matricula
                        AND falta_componente_curricular.etapa = '1'
                        AND falta_aluno.tipo_falta = 2),
                     (SELECT SUM(quantidade)
                      FROM modules.falta_geral, modules.falta_aluno
                      WHERE falta_geral.falta_aluno_id = falta_aluno.id
                        AND falta_aluno.matricula_id = matricula.cod_matricula
                        AND falta_geral.etapa = '1'
                        AND falta_aluno.tipo_falta = 1)))::character varying
  END AS falta1,

  CASE
      WHEN matricula_turma.remanejado = true THEN null
      ELSE
         (SELECT COALESCE(
                     (SELECT SUM(quantidade)
                      FROM modules.falta_componente_curricular, modules.falta_aluno
                      WHERE falta_componente_curricular.falta_aluno_id = falta_aluno.id
                        AND falta_componente_curricular.componente_curricular_id = view_componente_curricular.id
                        AND falta_aluno.matricula_id = matricula.cod_matricula
                        AND falta_componente_curricular.etapa = '2'
                        AND falta_aluno.tipo_falta = 2),
                     (SELECT SUM(quantidade)
                      FROM modules.falta_geral, modules.falta_aluno
                      WHERE falta_geral.falta_aluno_id = falta_aluno.id
                        AND falta_aluno.matricula_id = matricula.cod_matricula
                        AND falta_geral.etapa = '2'
                        AND falta_aluno.tipo_falta = 1)))::character varying
  END AS falta2,

  CASE
      WHEN matricula_turma.remanejado = true THEN null
      ELSE
         (SELECT COALESCE(
                     (SELECT SUM(quantidade)
                      FROM modules.falta_componente_curricular, modules.falta_aluno
                      WHERE falta_componente_curricular.falta_aluno_id = falta_aluno.id
                        AND falta_componente_curricular.componente_curricular_id = view_componente_curricular.id
                        AND falta_aluno.matricula_id = matricula.cod_matricula
                        AND falta_componente_curricular.etapa = '3'
                        AND falta_aluno.tipo_falta = 2),
                     (SELECT SUM(quantidade)
                      FROM modules.falta_geral, modules.falta_aluno
                      WHERE falta_geral.falta_aluno_id = falta_aluno.id
                        AND falta_aluno.matricula_id = matricula.cod_matricula
                        AND falta_geral.etapa = '3'
                        AND falta_aluno.tipo_falta = 1))) ::character varying
  END AS falta3,

  CASE
      WHEN matricula_turma.remanejado = true THEN null
      ELSE
         (SELECT COALESCE(
                     (SELECT SUM(quantidade)
                      FROM modules.falta_componente_curricular, modules.falta_aluno
                      WHERE falta_componente_curricular.falta_aluno_id = falta_aluno.id
                        AND falta_componente_curricular.componente_curricular_id = view_componente_curricular.id
                        AND falta_aluno.matricula_id = matricula.cod_matricula
                        AND falta_componente_curricular.etapa = '4'
                        AND falta_aluno.tipo_falta = 2),
                     (SELECT SUM(quantidade)
                      FROM modules.falta_geral, modules.falta_aluno
                      WHERE falta_geral.falta_aluno_id = falta_aluno.id
                        AND falta_aluno.matricula_id = matricula.cod_matricula
                        AND falta_geral.etapa = '4'
                        AND falta_aluno.tipo_falta = 1)))::character varying
  END AS falta4,
      view_componente_curricular.ordenamento AS componente_order,
      view_componente_curricular.abreviatura AS nm_componente_curricular,
      matricula.ano AS ano,
      curso.nm_curso AS nome_curso,
      serie.nm_serie AS nome_serie,
      turma.nm_turma AS nome_turma,
      relatorio.get_qtde_modulo(turma.cod_turma) AS qtd_modulo,
      turma_turno.nome AS periodo
 FROM pmieducar.instituicao
INNER JOIN pmieducar.escola ON (escola.ref_cod_instituicao = instituicao.cod_instituicao)
INNER JOIN pmieducar.escola_curso ON (escola_curso.ref_cod_escola = escola.cod_escola)
INNER JOIN pmieducar.curso ON (curso.cod_curso = escola_curso.ref_cod_curso)
INNER JOIN pmieducar.escola_serie ON (escola_serie.ref_cod_escola = escola.cod_escola)
INNER JOIN pmieducar.serie ON (serie.cod_serie = escola_serie.ref_cod_serie)
INNER JOIN pmieducar.turma ON (turma.ref_ref_cod_serie = serie.cod_serie)
LEFT JOIN modules.regra_avaliacao_serie_ano rasa
ON turma.ano = rasa.ano_letivo
AND rasa.serie_id = serie.cod_serie
LEFT JOIN modules.regra_avaliacao ON modules.regra_avaliacao.id = rasa.regra_avaliacao_id
INNER JOIN pmieducar.turma_turno ON (turma_turno.id = turma.turma_turno_id)
INNER JOIN pmieducar.matricula_turma ON (matricula_turma.ref_cod_turma = turma.cod_turma)
INNER JOIN pmieducar.matricula ON (matricula.cod_matricula = matricula_turma.ref_cod_matricula)
INNER JOIN pmieducar.aluno ON (aluno.cod_aluno = matricula.ref_cod_aluno)
INNER JOIN cadastro.pessoa ON (pessoa.idpes = aluno.ref_idpes)
INNER JOIN relatorio.view_componente_curricular ON (view_componente_curricular.cod_turma = turma.cod_turma)
LEFT JOIN modules.nota_aluno ON (nota_aluno.matricula_id = matricula.cod_matricula)
LEFT JOIN modules.nota_componente_curricular AS nota_componente_curricular_etapa1 ON (nota_componente_curricular_etapa1.nota_aluno_id = nota_aluno.id
                                                                                      AND nota_componente_curricular_etapa1.componente_curricular_id = view_componente_curricular.id
                                                                                      AND nota_componente_curricular_etapa1.etapa = '1')
LEFT JOIN modules.nota_componente_curricular AS nota_componente_curricular_etapa2 ON (nota_componente_curricular_etapa2.nota_aluno_id = nota_aluno.id
                                                                                      AND nota_componente_curricular_etapa2.componente_curricular_id = view_componente_curricular.id
                                                                                      AND nota_componente_curricular_etapa2.etapa = '2')
LEFT JOIN modules.nota_componente_curricular AS nota_componente_curricular_etapa3 ON (nota_componente_curricular_etapa3.nota_aluno_id = nota_aluno.id
                                                                                      AND nota_componente_curricular_etapa3.componente_curricular_id = view_componente_curricular.id
                                                                                      AND nota_componente_curricular_etapa3.etapa = '3')
LEFT JOIN modules.nota_componente_curricular AS nota_componente_curricular_etapa4 ON (nota_componente_curricular_etapa4.nota_aluno_id = nota_aluno.id
                                                                                      AND nota_componente_curricular_etapa4.componente_curricular_id = view_componente_curricular.id
                                                                                      AND nota_componente_curricular_etapa4.etapa = '4')
INNER JOIN relatorio.view_situacao ON (view_situacao.cod_matricula = matricula.cod_matricula
                                       AND view_situacao.sequencial = matricula_turma.sequencial
                                       AND view_situacao.cod_situacao = {$situacaoMatricula})
WHERE instituicao.cod_instituicao = {$instituicao}
  AND matricula.ano = {$ano}
  AND escola.cod_escola = {$escola}
  AND curso.cod_curso = {$curso}
  AND serie.cod_serie = {$serie}
  AND turma.cod_turma = {$turma}
  AND matricula.ativo = 1
ORDER BY sequencial_fechamento,
         nm_aluno,
         cod_aluno,
         componente_order,
         nm_componente_curricular
";
    }
}
