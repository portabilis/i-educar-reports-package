<?php

use iEducar\Reports\JsonDataSource;

class ParentSignatureReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource, ParentSignatureTrait {
        ParentSignatureTrait::query as QueryParentSignature;
    }

    public function templateName()
    {
        return 'parent-signature';
    }

    public function requiredArgs()
    {
        $this->addRequiredArg('ano');
        $this->addRequiredArg('instituicao');
        $this->addRequiredArg('escola');
    }

    public function getJsonData()
    {
        return [
            'main' => Portabilis_Utils_Database::fetchPreparedQuery($this->QueryParentSignature()),
            'header' => Portabilis_Utils_Database::fetchPreparedQuery($this->getSqlHeaderReport())
        ];
    }
}
