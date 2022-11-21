<?php

use iEducar\Reports\JsonDataSource;

class WhiteStudentFormReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    public function templateName()
    {
        $modelos = [
            1 => 'white-student-form-model1',
            2 => 'white-student-form-model2'
        ];

        return $modelos[$this->args['modelo']];
    }

    public function requiredArgs()
    {
        $this->addRequiredArg('instituicao');
        $this->addRequiredArg('escola');
    }

    public function getJsonData()
    {
        return [
            'main' => (new QueryWhiteStudentFormModel)->get($this->args),
            'header' => Portabilis_Utils_Database::fetchPreparedQuery($this->getSqlHeaderReport())
        ];
    }
}
