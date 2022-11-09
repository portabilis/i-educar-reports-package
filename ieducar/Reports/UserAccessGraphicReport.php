<?php

use iEducar\Reports\JsonDataSource;

class UserAccessGraphicReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource, UserAccessGraphicTrait {
        UserAccessGraphicTrait::query as QueryUserGraphicAccess;
    }

    public function templateName()
    {
        return 'user-access-graphic';
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
            'main' => Portabilis_Utils_Database::fetchPreparedQuery($this->QueryUserGraphicAccess()),
            'header' => Portabilis_Utils_Database::fetchPreparedQuery($this->getSqlHeaderReport())
        ];
    }
}
