<?php

use iEducar\Reports\JsonDataSource;

require_once 'lib/Portabilis/Report/ReportCore.php';
require_once 'App/Model/IedFinder.php';
require_once 'Reports/Queries/QuerySchoolHistorySeriesYears.php';

class SchoolHistoryReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        return 'school-history-series-years';
    }

    /**
     * @inheritdoc
     */
    public function requiredArgs()
    {
        $this->addRequiredArg('instituicao');
        $this->addRequiredArg('escola');
        $this->addRequiredArg('modelo');
    }
    
    public function getJsonData()
    {
        $queryHeaderReport = $this->getSqlHeaderReport();

        return [
            'main' => (new QuerySchoolHistorySeriesYears)->get($this->args),
            'header' => Portabilis_Utils_Database::fetchPreparedQuery($queryHeaderReport)
        ];
    }
}
