<?php

use iEducar\Reports\JsonDataSource;

class EmployeeAllocatedTimeReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource, EmployeeAllocatedTimeTrait {
        EmployeeAllocatedTimeTrait::query as QueryServerAllocatedTime;
    }

    public function templateName()
    {
        return 'employee-allocated-time';
    }

    public function requiredArgs()
    {
        $this->addRequiredArg('ano');
        $this->addRequiredArg('instituicao');
    }

    public function getJsonData()
    {
        return [
            'main' => Portabilis_Utils_Database::fetchPreparedQuery($this->QueryServerAllocatedTime()),
            'header' => Portabilis_Utils_Database::fetchPreparedQuery($this->getSqlHeaderReport())
        ];
    }
}
