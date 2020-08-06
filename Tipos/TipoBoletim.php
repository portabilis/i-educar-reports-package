<?php

require_once 'CoreExt/Enum.php';

class Portabilis_Model_Report_TipoBoletim extends CoreExt_Enum
{
    const BIMESTRAL = 1;
    const BIMESTRAL_CONCEITUAL = 2;
    const PARECER_DESCRITIVO_GERAL = 10;

    protected $_data = [
        self::BIMESTRAL => 'Bimestral',
        self::BIMESTRAL_CONCEITUAL => 'Bimestral conceitual',
        self::PARECER_DESCRITIVO_GERAL => 'Parecer descritivo geral'
    ];

    protected $_reports = [
        self::BIMESTRAL => 'report-card',
        self::BIMESTRAL_CONCEITUAL => 'conceptual-bimonthly-report-card',
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
