<?php

use iEducar\Reports\JsonDataSource;

require_once 'lib/Portabilis/Report/ReportCore.php';
require_once 'App/Model/IedFinder.php';

class ClassAverageComparativeReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        return 'class-average-comparative';
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
        $this->addRequiredArg('etapa');
    }

    /**
     * @inheritdoc
     */
    public function getSqlMainReport()
    {
        $etapa = $this->args['etapa'] ?: 0;
        $ano = $this->args['ano'] ?: 0;
        $escola = $this->args['escola'] ?: 0;
        $curso = $this->args['curso'] ?: 0;
        $serie = $this->args['serie'] ?: 0;
        $turma = $this->args['turma'] ?: 0;

        return "
                    
            SELECT 
                relatorio.get_nome_escola(e.cod_escola) AS nome_escola,
                c.nm_curso,
                s.nm_serie,
                t.nm_turma,
                ra.media AS medial_geral,
                (
                    CASE WHEN {$etapa} = '1' THEN
                        '1º ' || relatorio.get_nome_modulo(t.cod_turma)
                    WHEN {$etapa} = '2' THEN
                        '2º ' || relatorio.get_nome_modulo(t.cod_turma)
                    WHEN {$etapa} = '3' THEN
                        '3º ' || relatorio.get_nome_modulo(t.cod_turma)
                    WHEN {$etapa} = '4' THEN
                        '4º ' || relatorio.get_nome_modulo(t.cod_turma)
                    end
                ) AS etapa,
                count(distinct (m.ref_cod_aluno)) AS qtde_alunos,
                count(distinct (ncc_abaixo.nota_aluno_id)) AS alunos_abaixo_media,
                count(distinct (ncc_acima.nota_aluno_id)) AS alunos_igual_acima_media
            FROM pmieducar.escola e
            INNER JOIN pmieducar.escola_ano_letivo eal ON TRUE 
                AND eal.ref_cod_escola = e.cod_escola
            INNER JOIN pmieducar.escola_curso ec ON TRUE 
                AND ec.ref_cod_escola = e.cod_escola
            INNER JOIN pmieducar.escola_serie es ON TRUE 
                AND es.ref_cod_escola = e.cod_escola
            INNER JOIN pmieducar.curso c ON TRUE 
                AND c.cod_curso = ec.ref_cod_curso
            INNER JOIN pmieducar.serie s ON TRUE 
                AND s.cod_serie = es.ref_cod_serie
            INNER JOIN modules.regra_avaliacao_serie_ano rasa ON TRUE 
                AND rasa.serie_id = s.cod_serie
                AND eal.ano = rasa.ano_letivo
            INNER JOIN modules.regra_avaliacao ra ON TRUE 
                AND ra.id = rasa.regra_avaliacao_id
            INNER JOIN pmieducar.matricula m ON TRUE 
                AND m.ref_ref_cod_escola = e.cod_escola
                AND m.ano = eal.ano
                AND m.ref_cod_curso = c.cod_curso
                AND m.ref_ref_cod_serie = s.cod_serie
                AND m.dependencia = 'f'
                AND m.ativo = 1
            INNER JOIN pmieducar.matricula_turma mt ON TRUE 
                AND mt.ref_cod_matricula = m.cod_matricula and mt.ativo = 1
            INNER JOIN pmieducar.turma t ON TRUE 
                AND t.cod_turma = mt.ref_cod_turma
            INNER JOIN relatorio.view_situacao ON TRUE 
                AND view_situacao.cod_turma = t.cod_turma
                AND view_situacao.cod_matricula = m.cod_matricula
                AND view_situacao.cod_situacao = 9
            INNER JOIN modules.nota_aluno na ON TRUE 
                AND na.matricula_id = m.cod_matricula
            LEFT JOIN modules.nota_componente_curricular ncc_abaixo ON TRUE 
                AND ncc_abaixo.nota_aluno_id = na.id
                AND ncc_abaixo.nota < ra.media
                AND CASE WHEN {$etapa} <> '0'  THEN ncc_abaixo.etapa = '{$etapa}' END
            LEFT JOIN modules.nota_componente_curricular ncc_acima ON TRUE 
                AND ncc_acima.nota_aluno_id = na.id
            AND ncc_acima.nota >= ra.media
            AND CASE WHEN {$etapa} <> '0'  THEN ncc_acima.etapa = '{$etapa}' END
            AND ncc_abaixo.nota_aluno_id IS NULL
            WHERE TRUE 
                AND eal.ano = {$ano}
                AND e.cod_escola = {$escola}
                AND c.cod_curso = {$curso}
                AND (CASE WHEN {$serie} = 0 THEN TRUE ELSE s.cod_serie = {$serie} END)
                AND (CASE WHEN {$turma} = 0 THEN TRUE ELSE t.cod_turma = {$turma} END)
                AND m.dependencia = 'f'
            GROUP BY
                e.cod_escola,
                c.nm_curso,
                s.nm_serie,
                t.nm_turma,
                ra.media,
                t.cod_turma
            ORDER BY  
                nome_escola,
                c.nm_curso,
                s.nm_serie,
                t.nm_turma
                    
        ";
    }
}
