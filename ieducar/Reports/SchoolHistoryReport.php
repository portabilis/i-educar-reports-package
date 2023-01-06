<?php

use App\Models\LegacyRegistration;
use iEducar\Reports\JsonDataSource;

class SchoolHistoryReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        $templates = [
            1 => 'school-history-series-years',
            2 => 'school-history-crosstab',
            3 => 'school-history-early-years',
            4 => 'school-history-eja',
            5 => 'school-history-nine-years'
        ];

        $this->lote = $this->args['lote'];

        return $templates[$this->args['modelo']];
    }

    /**
     * @inheritdoc
     */
    public function requiredArgs()
    {
        $this->addRequiredArg('instituicao');
        $this->addRequiredArg('escola');
        $this->addRequiredArg('modelo');
    }

    public function getJsonData()
    {
        $queryHeaderReport = $this->getSqlHeaderReport();

        if ($this->args['modelo'] == 2) {
            return [
                'main' => (new QuerySchoolHistoryCrosstab())->get($this->args),
                'school-history-crosstab-dataset' => (new QuerySchoolHistoryCrosstabDataset())->get($this->args),
                'header' => Portabilis_Utils_Database::fetchPreparedQuery($queryHeaderReport)
            ];
        }

        if (in_array($this->args['modelo'], [3, 4])) {
            $this->args['grade_curso_eja'] = $this->args['modelo'] === 4 ? 3 : 0;
            $this->args['view_score'] = $this->args['modelo'] === 4 ? 'view_historico_eja' : 'view_historico_series_anos';
            $this->args['alunos'] = 0;
            if ($this->lote) {
                $this->args['alunos'] = $this->getStudentsByShoolClass() ?: 0;
            }

            $this->modifiers[] = SchoolHistoryModifier::class;

            return [
                'main' => (new QueryDefaultSchoolHistory)->get($this->args),
                'header' => Portabilis_Utils_Database::fetchPreparedQuery($queryHeaderReport)
            ];
        }

        if ($this->args['modelo'] == 5) {
            $this->args['alunos'] = 0;
            if ($this->lote) {
                $this->args['alunos'] = $this->getStudentsByShoolClass() ?: 0;
            }

            $this->modifiers[] = SchoolHistoryNineYearsModifier::class;

            return [
                'main' => (new QuerySchoolHistoryNineYears())->get($this->args),
                'extra-curricular-dataset' => (new QuerySchoolHistoryNineYearsExtraCurricular())->get($this->args),
                'header' => Portabilis_Utils_Database::fetchPreparedQuery($queryHeaderReport)
            ];
        }

        return [
            'main' => (new QuerySchoolHistorySeriesYears)->get($this->args),
            'header' => Portabilis_Utils_Database::fetchPreparedQuery($queryHeaderReport)
        ];
    }

    private function getStudentsByShoolClass()
    {
        $schoolClassId = $this->args['turma'];

        $students = LegacyRegistration::active()
            ->whereHas('enrollments', function ($enrollmentQuery) use ($schoolClassId) {
                $enrollmentQuery
                    ->where('ref_cod_turma', $schoolClassId)
                    ->where('ativo', 1);
            })
            ->pluck('ref_cod_aluno')
            ->toArray();

        return implode(',', $students);
    }
}
