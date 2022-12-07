<?php

use iEducar\Reports\JsonDataSource;

class NotEnrollmentReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource, NotEnrollmentTrait {
        NotEnrollmentTrait::query as QueryNotEnrollment;
    }

    public function templateName()
    {
        return 'not-enrollment';
    }

    public function requiredArgs()
    {

        $this->addRequiredArg('ano');
        $this->addRequiredArg('instituicao');
    }

    public function getJsonData()
    {
        return [
            'main' => Portabilis_Utils_Database::fetchPreparedQuery($this->QueryNotEnrollment()),
            'header' => Portabilis_Utils_Database::fetchPreparedQuery($this->getSqlHeaderReport())
        ];
    }
}
