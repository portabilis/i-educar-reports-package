<?php

use iEducar\Reports\JsonDataSource;

class ScoreAbsenceReleaseReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource, ScoreAbsenceReleaseTrait {
        ScoreAbsenceReleaseTrait::query as QueryNotesFoulsRelease;
    }

    public function templateName()
    {
        return 'score-absence-release';
    }

    public function requiredArgs()
    {
        $this->addRequiredArg('ano');
        $this->addRequiredArg('instituicao');
    }

    public function getJsonData()
    {
        return [
            'main' => Portabilis_Utils_Database::fetchPreparedQuery($this->QueryNotesFoulsRelease()),
            'header' => Portabilis_Utils_Database::fetchPreparedQuery($this->getSqlHeaderReport())
        ];
    }
}
