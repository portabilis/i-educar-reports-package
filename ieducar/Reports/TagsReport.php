<?php

use iEducar\Reports\JsonDataSource;

class TagsReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource, TagsTrait {
        TagsTrait::query as QueryTags;
    }

    public function templateName()
    {
        return 'tags';
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
            'main' => Portabilis_Utils_Database::fetchPreparedQuery($this->QueryTags()),
            'header' => Portabilis_Utils_Database::fetchPreparedQuery($this->getSqlHeaderReport())
        ];
    }
}
