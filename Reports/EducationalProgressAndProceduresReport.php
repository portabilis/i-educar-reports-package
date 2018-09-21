<?php

use iEducar\Reports\JsonDataSource;

require_once 'lib/Portabilis/Report/ReportCore.php';
require_once 'App/Model/IedFinder.php';

class EducationalProgressAndProceduresReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        return 'educational-progress-and-procedures';
    }

    /**
     * @inheritdoc
     */
    public function requiredArgs()
    {
        $this->addRequiredArg('ano');
        $this->addRequiredArg('instituicao');
    }

    /**
     * @inheritdoc
     */
    public function getJsonData()
    {
        $queryMainReport = $this->getSqlMainReport();
        $queryHeaderReport = $this->getSqlHeaderReport();
        $datasets = $this->getJsonDataFromDatasets();

        return array_merge([
            'main' => Portabilis_Utils_Database::fetchPreparedQuery($queryMainReport),
            'header' => Portabilis_Utils_Database::fetchPreparedQuery($queryHeaderReport),
        ], $datasets);
    }

    /**
     * Retorna o SQL para buscar os dados do relatório principal.
     *
     * @return string
     *
     * @throws Exception
     */
    public function getSqlMainReport()
    {
        $ano = $this->args['ano'] ?: 0;
        $escola = $this->args['escola'] ?: 0;

        return "
            SELECT relatorio.get_nome_escola(escola.cod_escola) AS nome_escola,
                curso.nm_curso AS nome_curso,
                serie.nm_serie AS nome_serie,
                COUNT(matricula.cod_matricula) AS qtde_matricula,

                (SELECT COUNT(m.cod_matricula)
                    FROM pmieducar.matricula m
                    INNER JOIN relatorio.view_situacao vs ON(vs.cod_matricula = m.cod_matricula AND vs.cod_situacao = m.aprovado)
                    WHERE m.ref_ref_cod_escola = escola.cod_escola
                    AND m.ref_ref_cod_serie = serie.cod_serie
                    AND m.ano = escola_ano_letivo.ano
                    AND vs.cod_situacao IN (1, 12 ,13)
                    AND COALESCE(m.dependencia, false) = false) AS qtde_aprovado,

                (SELECT COUNT(m.cod_matricula)
                    FROM pmieducar.matricula m
                    INNER JOIN relatorio.view_situacao vs ON(vs.cod_matricula = m.cod_matricula AND vs.cod_situacao = m.aprovado)
                    WHERE m.ref_ref_cod_escola = escola.cod_escola
                    AND m.ref_ref_cod_serie = serie.cod_serie
                    AND m.ano = escola_ano_letivo.ano
                    AND vs.cod_situacao IN (2, 14)
                    AND COALESCE(m.dependencia, false) = false) AS qtde_reprovado,

                (SELECT COUNT(m.cod_matricula)
                    FROM pmieducar.matricula m
                    INNER JOIN relatorio.view_situacao vs ON(vs.cod_matricula = m.cod_matricula AND vs.cod_situacao = m.aprovado)
                    WHERE m.ref_ref_cod_escola = escola.cod_escola
                    AND m.ref_ref_cod_serie = serie.cod_serie
                    AND m.ano = escola_ano_letivo.ano
                    AND vs.cod_situacao = 4
                    AND COALESCE(m.dependencia, false) = false) AS qtde_transferido,

                (SELECT COUNT(m.cod_matricula)
                    FROM pmieducar.matricula m
                    INNER JOIN relatorio.view_situacao vs ON(vs.cod_matricula = m.cod_matricula  AND vs.cod_situacao = m.aprovado)
                    WHERE m.ref_ref_cod_escola = escola.cod_escola
                    AND m.ref_ref_cod_serie = serie.cod_serie
                    AND m.ano = escola_ano_letivo.ano
                    AND vs.cod_situacao = 15
                    AND COALESCE(m.dependencia, false) = false) AS qtde_falecidos,

                (SELECT COUNT(m.cod_matricula)
                    FROM pmieducar.matricula m
                    INNER JOIN relatorio.view_situacao vs ON(vs.cod_matricula = m.cod_matricula AND vs.cod_situacao = m.aprovado)
                    WHERE m.ref_ref_cod_escola = escola.cod_escola
                    AND m.ref_ref_cod_serie = serie.cod_serie
                    AND m.ano = escola_ano_letivo.ano
                    AND vs.cod_situacao = 6
                    AND COALESCE(m.dependencia, false) = false) AS qtde_desistencia,

                (SELECT round((COUNT(m.cod_matricula)*100)/COUNT(matricula.cod_matricula)::decimal, 1)
                    FROM pmieducar.matricula m
                    INNER JOIN relatorio.view_situacao vs ON(vs.cod_matricula = m.cod_matricula AND vs.cod_situacao = m.aprovado)
                    WHERE m.ref_ref_cod_escola = escola.cod_escola
                    AND m.ref_ref_cod_serie = serie.cod_serie
                    AND m.ano = escola_ano_letivo.ano
                    AND vs.cod_situacao IN (1, 12 ,13)
                    AND COALESCE(m.dependencia, false) = false) AS perc_aprovado,

                (SELECT round((COUNT(m.cod_matricula)*100)/COUNT(matricula.cod_matricula)::decimal, 1)
                    FROM pmieducar.matricula m
                    INNER JOIN relatorio.view_situacao vs ON(vs.cod_matricula = m.cod_matricula AND vs.cod_situacao = m.aprovado)
                    WHERE m.ref_ref_cod_escola = escola.cod_escola
                    AND m.ref_ref_cod_serie = serie.cod_serie
                    AND m.ano = escola_ano_letivo.ano
                    AND vs.cod_situacao IN (2, 14)
                    AND COALESCE(m.dependencia, false) = false) AS perc_reprovado,

                (SELECT round((COUNT(m.cod_matricula)*100)/COUNT(matricula.cod_matricula)::decimal, 1)
                    FROM pmieducar.matricula m
                    INNER JOIN relatorio.view_situacao vs ON(vs.cod_matricula = m.cod_matricula AND vs.cod_situacao = m.aprovado)
                    WHERE m.ref_ref_cod_escola = escola.cod_escola
                    AND m.ref_ref_cod_serie = serie.cod_serie
                    AND m.ano = escola_ano_letivo.ano
                    AND vs.cod_situacao = 4
                    AND COALESCE(m.dependencia, false) = false) AS perc_transferido,

                (SELECT round((COUNT(m.cod_matricula)*100)/COUNT(matricula.cod_matricula)::decimal, 1)
                    FROM pmieducar.matricula m
                    INNER JOIN relatorio.view_situacao vs ON(vs.cod_matricula = m.cod_matricula  AND vs.cod_situacao = m.aprovado)
                    WHERE m.ref_ref_cod_escola = escola.cod_escola
                    AND m.ref_ref_cod_serie = serie.cod_serie
                    AND m.ano = escola_ano_letivo.ano
                    AND vs.cod_situacao = 15
                    AND COALESCE(m.dependencia, false) = false) AS perc_falecidos,

                (SELECT round((COUNT(m.cod_matricula)*100)/COUNT(matricula.cod_matricula)::decimal, 1)
                    FROM pmieducar.matricula m
                    INNER JOIN relatorio.view_situacao vs ON(vs.cod_matricula = m.cod_matricula AND vs.cod_situacao = m.aprovado)
                    WHERE m.ref_ref_cod_escola = escola.cod_escola
                    AND m.ref_ref_cod_serie = serie.cod_serie
                    AND m.ano = escola_ano_letivo.ano
                    AND vs.cod_situacao = 6
                    AND COALESCE(m.dependencia, false) = false) AS perc_desistencia


            FROM pmieducar.instituicao
            INNER JOIN pmieducar.escola ON (escola.ref_cod_instituicao = instituicao.cod_instituicao)
            INNER JOIN pmieducar.escola_ano_letivo ON (pmieducar.escola_ano_letivo.ref_cod_escola = pmieducar.escola.cod_escola)
            INNER JOIN pmieducar.escola_curso ON (escola_curso.ativo = 1 AND escola_curso.ref_cod_escola = escola.cod_escola)
            INNER JOIN pmieducar.escola_serie ON (escola_serie.ativo = 1 AND escola_serie.ref_cod_escola = escola.cod_escola)
            INNER JOIN pmieducar.curso ON (curso.cod_curso = escola_curso.ref_cod_curso AND curso.ativo = 1)
            INNER JOIN pmieducar.serie ON (serie.cod_serie = escola_serie.ref_cod_serie AND serie.ativo = 1)
            INNER JOIN pmieducar.turma ON (turma.ref_ref_cod_escola = escola.cod_escola AND turma.ref_cod_curso = escola_curso.ref_cod_curso
                                                                                        AND turma.ref_ref_cod_serie = escola_serie.ref_cod_serie
                                                                                        AND turma.ativo = 1)
            INNER JOIN pmieducar.matricula_turma ON (matricula_turma.ref_cod_turma = turma.cod_turma)
            INNER JOIN pmieducar.matricula ON (matricula.cod_matricula = matricula_turma.ref_cod_matricula AND matricula.ativo = 1)
            INNER JOIN relatorio.view_situacao ON (view_situacao.cod_matricula = matricula.cod_matricula AND view_situacao.cod_turma = turma.cod_turma
                                                                                                        AND view_situacao.cod_situacao = 10
                                                                                                        AND matricula_turma.sequencial = view_situacao.sequencial)
            WHERE pmieducar.instituicao.cod_instituicao = 1
            AND pmieducar.escola_ano_letivo.ano = {$ano}
            AND pmieducar.matricula.ano = pmieducar.escola_ano_letivo.ano
            AND matricula.aprovado <> 5 -- Não contabiliza reclassificados
            AND COALESCE(matricula_turma.remanejado, false) = false
            AND (SELECT CASE WHEN {$escola} = 0 THEN TRUE ELSE escola.cod_escola = {$escola} END)

            GROUP BY escola_ano_letivo.ano, escola.cod_escola, curso.nm_curso, serie.nm_serie, serie.cod_serie
            ORDER BY nome_escola, nome_curso, nome_serie
        ";
    }

    public function getJsonDataFromDatasets()
    {
        $queriesDatasets = $this->getSqlsForDatasets();
        $jsonData = [];
        foreach ($queriesDatasets as $name => $query) {
            $jsonData[$name] = Portabilis_Utils_Database::fetchPreparedQuery($query);
        }
        return $jsonData;
    }

    public function getSqlsForDatasets()
    {
        $ano = $this->args['ano'] ?: 0;
        $escola = $this->args['escola'] ?: 0;

        $quantitativeChart = "
            SELECT round((count(m.cod_matricula)*100)/relatorio.get_qtde_alunos({$ano}, {$escola})::decimal, 1) AS percentual,
                'Aprovado' AS situacao
            FROM pmieducar.matricula m
            WHERE (CASE WHEN 0 = {$escola} THEN TRUE ELSE m.ref_ref_cod_escola = {$escola} END)
            AND m.ano = {$ano}
            AND m.aprovado = 1
            UNION ALL
            SELECT round((count(m.cod_matricula)*100)/relatorio.get_qtde_alunos({$ano}, {$escola})::decimal, 1),
                'Reprovado'
            FROM pmieducar.matricula m
            WHERE (CASE WHEN 0 = {$escola} THEN TRUE ELSE m.ref_ref_cod_escola = {$escola} END)
            AND m.ano = {$ano}
            AND m.aprovado = 2
            UNION ALL
            SELECT round((count(m.cod_matricula)*100)/relatorio.get_qtde_alunos({$ano}, {$escola})::decimal, 1),
                'Transferido'
            FROM pmieducar.matricula m
            WHERE (CASE WHEN 0 = {$escola} THEN TRUE ELSE m.ref_ref_cod_escola = {$escola} END)
            AND m.ano = {$ano}
            AND m.aprovado = 4
            UNION ALL
            SELECT round((count(m.cod_matricula)*100)/relatorio.get_qtde_alunos({$ano}, {$escola})::decimal, 1),
                'Falecidos'
            FROM pmieducar.matricula m
            WHERE (CASE WHEN 0 = {$escola} THEN TRUE ELSE m.ref_ref_cod_escola = {$escola} END)
            AND m.ano = {$ano}
            AND m.ref_cod_abandono_tipo = 2
            UNION ALL
            SELECT round((count(m.cod_matricula)*100)/relatorio.get_qtde_alunos({$ano}, {$escola})::decimal, 1),
                'Desistências'
            FROM pmieducar.matricula m
            WHERE (CASE WHEN 0 = {$escola} THEN TRUE ELSE m.ref_ref_cod_escola = {$escola} END)
            AND m.ano = {$ano}
            AND m.ref_cod_abandono_tipo = 1
        ";
        return [
            'quantitative_chart' => $quantitativeChart
        ];
    }
}
