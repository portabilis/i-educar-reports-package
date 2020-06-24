<?php

use iEducar\Reports\JsonDataSource;

require_once 'lib/Portabilis/Report/ReportCore.php';
require_once 'App/Model/IedFinder.php';
require_once 'Reports/Queries/QuerySchoolHistorySeriesYears.php';
require_once 'Reports/Queries/QuerySchoolHistoryCrosstab.php';
require_once 'Reports/Queries/QuerySchoolHistoryCrosstabDataset.php';

class SchoolHistoryReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        $templates = [
          1 => 'school-history-series-years',
          2 => 'school-history-crosstab',
        ];

        return $templates[$this->args['modelo']];
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

        if ($this->args['modelo'] == 2) {
            return [
                'main' => (new QuerySchoolHistoryCrosstab)->get($this->args),
                'school-history-crosstab-dataset' => (new QuerySchoolHistoryCrosstabDataset)->get($this->args),
                'header' => Portabilis_Utils_Database::fetchPreparedQuery($queryHeaderReport)
            ];
        }

        return [
            'main' => (new QuerySchoolHistorySeriesYears)->get($this->args),
            'header' => Portabilis_Utils_Database::fetchPreparedQuery($queryHeaderReport)
        ];
    }
}
