<?php

use iEducar\Reports\JsonDataSource;

class EnrollmentQuantitativeMapReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    public $modifiers = [
        MapaQuantitativoMatridocculasModifier::class,
    ];

    public function templateName()
    {
        $modelos = [
            1 => 'enrollment-quantitative-map-detailed',
            2 => 'enrollment-quantitative-map-school',
            3 => 'enrollment-quantitative-map-course',
            4 => 'enrollment-quantitative-map-school-grade',
            5 => 'enrollment-quantitative-map-school-grade-period'
        ];

        return $modelos[$this->args['modelo']];
    }

    private function getQueryByTemplate()
    {
        $queries =  [
            1 => EnrollmentQuantitativeMapDefailed::class,
            2 => EnrollmentQuantitativeMapSchool::class,
            3 => EnrollmentQuantitativeMapCourse::class,
            4 => EnrollmentQuantitativeMapSchoolGrade::class,
            5 => EnrollmentQuantitativeMapSchoolGradePeriod::class
        ];

        return new $queries[$this->args['modelo']];
    }

    public function requiredArgs()
    {
        $this->addRequiredArg('ano');
        $this->addRequiredArg('instituicao');
        $this->addRequiredArg('situacao');
        $this->addRequiredArg('modelo');
    }

    public function getJsonData()
    {
        return [
            'main' => $this->getQueryByTemplate()->get($this->args),
            'header' => Portabilis_Utils_Database::fetchPreparedQuery($this->getSqlHeaderReport())
        ];
    }
}
