<?php

use iEducar\Reports\JsonDataSource;

class SchoolingCertificateReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource, SchoolingCertificateTrait {
        SchoolingCertificateTrait::query as QuerySchoolingCertificate;
    }

    public function templateName()
    {
        return 'schooling-certificate';
    }

    public function requiredArgs()
    {
        $this->addRequiredArg('ano');
        $this->addRequiredArg('instituicao');
        $this->addRequiredArg('matricula');
    }

    public function getJsonData()
    {
        return [
            'main' => Portabilis_Utils_Database::fetchPreparedQuery($this->QuerySchoolingCertificate()),
            'header' => Portabilis_Utils_Database::fetchPreparedQuery($this->getSqlHeaderReport())
        ];
    }
}
