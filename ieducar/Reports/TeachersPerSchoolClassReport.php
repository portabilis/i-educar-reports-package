<?php

use iEducar\Reports\JsonDataSource;

class TeachersPerSchoolClassReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource, TeachersPerSchoolClassTrait {
        TeachersPerSchoolClassTrait::query as QueryTeachersPerSchoolClass;
    }

    public function templateName()
    {
        return 'teachers-per-schoolclass';
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
            'main' => Portabilis_Utils_Database::fetchPreparedQuery($this->QueryTeachersPerSchoolClass()),
            'header' => Portabilis_Utils_Database::fetchPreparedQuery($this->getSqlHeaderReport())
        ];
    }
}
