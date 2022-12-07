<?php

use iEducar\Reports\JsonDataSource;

class UserAccessReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource, UserAccessTrait {
        UserAccessTrait::query as QueryUserAccess;
    }

    public function templateName()
    {
        return 'user-access';
    }

    public function requiredArgs()
    {
        $this->addRequiredArg('instituicao');
        $this->addRequiredArg('data_inicial');
        $this->addRequiredArg('data_final');
    }

    public function getJsonData()
    {
        return [
            'main' => Portabilis_Utils_Database::fetchPreparedQuery($this->QueryUserAccess()),
            'header' => Portabilis_Utils_Database::fetchPreparedQuery($this->getSqlHeaderReport())
        ];
    }
}
