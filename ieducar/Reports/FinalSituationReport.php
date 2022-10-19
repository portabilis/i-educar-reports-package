<?php

use iEducar\Reports\JsonDataSource;

class FinalSituationReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource, FinalSituationTrait {
        FinalSituationTrait::query as QueryFinalSituation;
    }

    public function templateName()
    {
        return 'final-situation';
    }

    public function requiredArgs()
    {
        $this->addRequiredArg('ano');
        $this->addRequiredArg('instituicao');
    }

    public function getJsonData()
    {
        return [
            'main' => Portabilis_Utils_Database::fetchPreparedQuery($this->QueryFinalSituation()),
            'header' => Portabilis_Utils_Database::fetchPreparedQuery($this->getSqlHeaderReport())
        ];
    }


}
