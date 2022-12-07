<?php

use iEducar\Reports\JsonDataSource;

class StudentCardReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    public $modifiers = [
        PhotoPresignerModifier::class,
    ];

    public function templateName()
    {
        $modelos = [
            1 => 'student-card-model1',
            2 => 'student-card-model2',
            3 => 'student-card-model3',
        ];

        return $modelos[$this->args['modelo']];
    }

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

    public function getJsonData()
    {
        return [
            'main' => (new QueryStudentCard())->get($this->args),
            'header' => Portabilis_Utils_Database::fetchPreparedQuery($this->getSqlHeaderReport())
        ];
    }
}
