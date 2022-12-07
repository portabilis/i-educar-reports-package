<?php

use iEducar\Reports\JsonDataSource;

class RegistrationSchoolReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource, RegistrationSchoolTrait {
        RegistrationSchoolTrait::query as QueryRegistrationSchoolReport;
    }

    public function templateName()
    {
        return 'registration-school';
    }

    public function requiredArgs()
    {
        $this->addRequiredArg('ano');
        $this->addRequiredArg('instituicao');
        $this->addRequiredArg('escola');
        $this->addRequiredArg('situacao');
    }

    public function getJsonData()
    {
        return [
            'main' => Portabilis_Utils_Database::fetchPreparedQuery($this->QueryRegistrationSchoolReport()),
            'header' => Portabilis_Utils_Database::fetchPreparedQuery($this->getSqlHeaderReport())
        ];
    }
}
