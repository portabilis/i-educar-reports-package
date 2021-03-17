<?php

use iEducar\Reports\JsonDataSource;

class SchoolHistoryReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource, SchoolHistorySeriesYearsTrait, SchoolHistoryCrosstabTrait, SchoolHistoryCrosstabDatasetTrait {
        SchoolHistorySeriesYearsTrait::query insteadof SchoolHistoryCrosstabTrait, SchoolHistoryCrosstabDatasetTrait;
        SchoolHistorySeriesYearsTrait::query AS querySchoolHistorySeriesYears;
        SchoolHistoryCrosstabTrait::query AS querySchoolHistoryCrosstab;
        SchoolHistoryCrosstabDatasetTrait::query AS querySchoolHistoryCrosstabDataset;
    }

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
                'main' => Portabilis_Utils_Database::fetchPreparedQuery($this->querySchoolHistoryCrosstab()),
                'school-history-crosstab-dataset' => Portabilis_Utils_Database::fetchPreparedQuery($this->querySchoolHistoryCrosstabDataset()),
                'header' => Portabilis_Utils_Database::fetchPreparedQuery($queryHeaderReport)
            ];
        }

        return [
            'main' => Portabilis_Utils_Database::fetchPreparedQuery($this->querySchoolHistorySeriesYears()),
            'header' => Portabilis_Utils_Database::fetchPreparedQuery($queryHeaderReport)
        ];
    }
}
