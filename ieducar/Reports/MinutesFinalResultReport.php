<?php

use iEducar\Reports\JsonDataSource;

class MinutesFinalResultReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    public $modifiers = [
        MinutesFinalResultModifier::class
    ];

    public function templateName()
    {
        return 'minutes-final-result';
    }

    public function requiredArgs()
    {
        $this->addRequiredArg('ano');
        $this->addRequiredArg('instituicao');
        $this->addRequiredArg('escola');
        $this->addRequiredArg('curso');
        $this->addRequiredArg('serie');
        $this->addRequiredArg('turma');
    }

    public function getJsonData()
    {
        return [
            'main' => (new QueryMinutesFinalResult())->get($this->args),
            'header' => Portabilis_Utils_Database::fetchPreparedQuery($this->getSqlHeaderReport())
        ];
    }
}
