<?php

use iEducar\Reports\JsonDataSource;

class PendingStudentsReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource, PendingStudentsTrait {
        PendingStudentsTrait::query as QueryPendingStudents;
    }

    public function templateName()
    {
        return 'pending-students';
    }

    public function requiredArgs()
    {

        $this->addRequiredArg('ano');
        $this->addRequiredArg('instituicao');
    }

    public function getJsonData()
    {
        return [
            'main' => Portabilis_Utils_Database::fetchPreparedQuery($this->QueryPendingStudents()),
            'header' => Portabilis_Utils_Database::fetchPreparedQuery($this->getSqlHeaderReport())
        ];
    }
}
