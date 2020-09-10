<?php

use App\Models\LegacyEvaluationRuleGradeYear;
use App\Models\LegacySchoolClass;
use iEducar\Reports\BaseModifier;

require_once 'Portabilis/Utils/CustomLabel.php';

class ReportCardModifier extends BaseModifier
{
    /**
    * @inheritdoc
    */
    public function modify($data)
    {
        $main = $data['main'];
        $templates = Portabilis_Model_Report_TipoBoletim::getInstance()->getReports();
        $templetesUsingThisModifier = [
            $templates[Portabilis_Model_Report_TipoBoletim::NUMERIC],
            $templates[Portabilis_Model_Report_TipoBoletim::CONCEPTUAL],
            $templates[Portabilis_Model_Report_TipoBoletim::CONCEPTUAL_LANDSCAPE]
        ];

        if (!in_array($this->templateName, $templetesUsingThisModifier)) {
            return $data;
        }

        $scoreCaption = $this->getScoreCaption($this->args['serie'], $this->args['ano']);
        $numberOfSteps = $this->getNumeberOfSteps($this->args['turma']);

        foreach ($main as $key => $value) {
            $line = $main[$key];
            $examAverage = bcdiv(($value['media_recuperacao'] ?: 0.00), 1, 1);
            $score1 = $this->getFormattedScore($value['nota1'], $value['qtd_casas_decimais']);
            $score2 = $this->getFormattedScore($value['nota2'], $value['qtd_casas_decimais']);
            $score3 = $this->getFormattedScore($value['nota3'], $value['qtd_casas_decimais']);
            $score4 = $this->getFormattedScore($value['nota4'], $value['qtd_casas_decimais']);
            $totalAbsencesByDiscipline = $value['total_faltas_componente'];
            $workloadByDiscipline = $value['carga_horaria_componente'];
            $absenceHours = $value['curso_hora_falta'];
            $line['nome_instituicao'] = strtoupper($value['nome_instituicao']);
            $line['nome_responsavel'] = strtoupper($value['nome_responsavel']);
            $line['nome_aluno'] = strtoupper($value['nome_aluno']);
            $line['nota1'] = ($value['nota1num'] < $examAverage ? "<b> $score1 </b>" : $score1);
            $line['nota2'] = ($value['nota2num'] < $examAverage ? "<b> $score2 </b>" : $score2);
            $line['nota3'] = ($value['nota3num'] < $examAverage ? "<b> $score3 </b>" : $score3);
            $line['nota4'] = ($value['nota4num'] < $examAverage ? "<b> $score4 </b>" : $score4);
            $line['curso_hora_falta'] = $absenceHours;
            $line['frequencia'] = $this->getAttendanceByDiscipline($totalAbsencesByDiscipline, $absenceHours, $workloadByDiscipline);
            $line['nota_exame'] = $this->getFormattedScore($value['nota_exame'], $value['qtd_casas_decimais']);

            $absenceType = $line['tipo_presenca'];
            $schoolDays = $line['dias_letivos'];
            $workload = $line['carga_horaria_serie'];
            $grandTotalOfAbsences = $absenceType == RegraAvaliacao_Model_TipoPresenca::GERAL ? $value['total_faltas'] : $value['total_geral_faltas_componente'] ;

            $line['media_frequencia'] = $this->getAttendance($absenceType, $grandTotalOfAbsences, $schoolDays, $workload, $absenceHours);
            
            $numericScores = [
                $value['nota1num'],
                $value['nota2num'],
                $value['nota3num'],
                $value['nota4num'],
            ];

            $average = $this->getAverage(
                $value['media'],
                $numericScores,
                $numberOfSteps,
                $examAverage,
                $value['qtd_casas_decimais']
            );

            $line['media_recuperacao'] = $examAverage;
            $line['media'] = $average;
            $line['media_grafico'] = $this->calculatesAverageForTheGraph($numericScores);
            $line['resultado_exame'] = _cl('report.termo_recuperacao_final');
            $line['legenda_notas'] = $scoreCaption;
            $line['quantidade_etapas'] = $numberOfSteps;

            $data['main'][$key] = $line;
        }

        return $data;
    }

    public function getAttendance($absenceType, $totalAbsences, $schoolDays, $workload, $absenceHours)
    {
        if ($absenceType == RegraAvaliacao_Model_TipoPresenca::GERAL) {
            return bcdiv(((($schoolDays - $totalAbsences) * 100) / $schoolDays), 1, 1);
        }

        return bcdiv(100 - (($totalAbsences * ($absenceHours * 100)) / $workload), 1, 1);
    }

    public function getAttendanceByDiscipline($totalAbsencesByDiscipline, $absenceHours, $workloadByDiscipline) {
        if (empty($totalAbsencesByDiscipline)) {
            return 100.0;
        }

        if (empty($workloadByDiscipline) || $workloadByDiscipline == 0) {
            throw new Exception('Não foi possivel calcular a frequência, pois existem disciplinas sem carga horária informada.');
        }

        return bcdiv(100 - (($totalAbsencesByDiscipline * ($absenceHours * 100)) / $workloadByDiscipline), 1, 1);
    }

    public function getFormattedScore($score, $decimalPlace)
    {
        if (!is_numeric($score) || empty($score)) {
            return $score;
        }

        return str_replace('.', ',', bcdiv($score, 1, $decimalPlace));
    }

    /**
     * Calcula média usada no gráfico
     *
     * @param array $notas
     * @return float|void
     */
    private function calculatesAverageForTheGraph($scores = [])
    {
        $scores = array_filter($scores);

        if (count($scores) == 0) {
            return;
        }

        return array_sum($scores) / count($scores);
    }

    private function getScoreCaption($grade, $year)
    {
        $evaluationRuleGradeYear = LegacyEvaluationRuleGradeYear::query()
            ->where('serie_id', $grade)
            ->where('ano_letivo', $year)
            ->first();

        $roundingValues = $evaluationRuleGradeYear
            ->evaluationRule
            ->roundingTable
            ->roundingValues
            ->pluck('descricao', 'nome')
            ->toArray();

        return implode(', ', array_map(
            function ($value, $key) {
                return sprintf("%s - %s", $key, $value);
            },
            $roundingValues,
            array_keys($roundingValues)
        ));
    }

    private function getNumeberOfSteps($schoolClassId)
    {
        return LegacySchoolClass::find($schoolClassId)->stages->count();
    }

    private function getAverage($average, $scores, $numberOfSteps, $examAverage, $decimalPlace)
    {
        $scores = array_filter($scores);
        $formattedAverage = $this->getFormattedScore($average, $decimalPlace);

        if (empty($average)) {
            return;
        }

        if (count($scores) < $numberOfSteps) {
            return;
        }

        if ($average < $examAverage) {
            return "<b> $formattedAverage </b>";
        }

        return $formattedAverage;
    }
}
