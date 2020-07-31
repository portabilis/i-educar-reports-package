<?php

require_once 'CoreExt/Enum.php';

class Portabilis_Model_Report_TipoBoletim extends CoreExt_Enum
{
    const BIMESTRAL = 1;
    const PARECER_DESCRITIVO_GERAL = 10;

    protected $_data = [
        self::BIMESTRAL => 'Bimestral',
        self::PARECER_DESCRITIVO_GERAL => 'Parecer descritivo geral'
    ];

    protected $_reports = [
        self::BIMESTRAL => 'report-card',
        self::PARECER_DESCRITIVO_GERAL => 'general-opinion-report-card'
    ];

    public function getReports()
    {
        return $this->_reports;
    }

    public static function getInstance()
    {
        return self::_getInstance(__CLASS__);
    }
}
