<?php

use iEducar\Reports\JsonDataSource;

class FinalResultReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        $modelos = [
            1 => 'final-result-numeric',
            2 => 'final-result-conceptual'
        ];

        return $modelos[$this->args['modelo']];
    }

    private function getQueryByTemplate()
    {
        $queries =  [
            1 => QueryFinalResultNumeric::class,
            2 => QueryFinalResultConceptual::class,
        ];

        return new $queries[$this->args['modelo']];
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
