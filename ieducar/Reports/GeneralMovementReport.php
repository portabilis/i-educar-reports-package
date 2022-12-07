<?php

use iEducar\Reports\JsonDataSource;

class GeneralMovementReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource, GeneralMovementTrait {
        GeneralMovementTrait::query as QueryGeneralMovement;
    }

    public $modifiers = [
        GeneralMovementModifier::class,
    ];

    public function templateName()
    {
        return 'general-movement';
    }

    public function requiredArgs()
    {
        $this->addRequiredArg('ano');
        $this->addRequiredArg('instituicao');
    }

    public function getJsonData()
    {
        return [
            'main' => Portabilis_Utils_Database::fetchPreparedQuery($this->QueryGeneralMovement()),
            'header' => Portabilis_Utils_Database::fetchPreparedQuery($this->getSqlHeaderReport())
        ];
    }

}
