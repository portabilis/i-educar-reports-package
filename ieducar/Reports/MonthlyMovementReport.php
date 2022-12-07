<?php

use iEducar\Reports\JsonDataSource;

class MonthlyMovementReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;
    use MonthlyMovementTrait {
        MonthlyMovementTrait::query as QueryMonthlyMovementTrait;
    }

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        return 'monthly-movement';
    }

    /**
     * @inheritdoc
     */
    public function requiredArgs()
    {
        $this->addRequiredArg('ano');
        $this->addRequiredArg('instituicao');
    }

    public function getJsonData()
    {
        return [
            'main' => Portabilis_Utils_Database::fetchPreparedQuery($this->QueryMonthlyMovementTrait()),
            'header' => Portabilis_Utils_Database::fetchPreparedQuery($this->getSqlHeaderReport())
        ];
    }

}
