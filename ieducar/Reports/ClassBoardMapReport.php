<?php

use iEducar\Reports\JsonDataSource;

class ClassBoardMapReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    public $modifiers = [
        ClassBoardMapModifier::class
    ];

    public function templateName()
    {
        return $this->args['orientacao'] === 'retrato'
            ? 'class-board-map-portrait'
            : 'class-board-map-landscape';
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
            'main' => (new QueryClassBoardMap())->get($this->args),
            'header' => Portabilis_Utils_Database::fetchPreparedQuery($this->getSqlHeaderReport())
        ];
    }
}
