<?php

require_once 'CoreExt/Enum.php';

class Portabilis_Model_Report_TipoBoletim extends CoreExt_Enum
{
    const BIMESTRAL = 1;

    protected $_data = array(
        self::BIMESTRAL => 'Bimestral'
    );

    protected $_reports = array(
        self::BIMESTRAL => 'report-card'
    );

    public function getReports()
    {
        return $this->_reports;
    }

    public static function getInstance()
    {
        return self::_getInstance(__CLASS__);
    }
}