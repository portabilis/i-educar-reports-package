<?php

use iEducar\Reports\JsonDataSource;

class FinalMapByDisciplineReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    public function templateName()
    {
        $modelos = [
            1 => 'final-map-by-discipline-model1',
            2 => 'final-map-by-discipline-model2'
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
        $this->addRequiredArg('situacao');
    }

    private function getQueryByTemplate()
    {
        $queries =  [
            1 => QueryFinalMapByDisciplineModel1::class,
            2 => QueryFinalMapByDisciplineModel2::class,
        ];

        return new $queries[$this->args['modelo']];
    }

    public function getJsonData()
    {
        return [
            'main' => $this->getQueryByTemplate()->get($this->args),
            'header' => Portabilis_Utils_Database::fetchPreparedQuery($this->getSqlHeaderReport())
        ];
    }
}
