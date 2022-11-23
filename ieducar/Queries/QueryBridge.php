<?php

class QueryBridge
{
    /**
     * @var array
     */
    protected $forceString = [];

    /**
     * @var integer
     */
    protected $fetchMode = PDO::FETCH_ASSOC;

    /**
     * @return string
     */
    protected function query()
    {
        return '';
    }

    /**
     * @param array $data
     *
     * @return string
     */
    protected function compile($data = [])
    {
        $replacePairs = [];

        foreach ($data as $k => $v) {

            if (is_bool($v)) {
                $value = $v ? 'true' : 'false';
            } elseif (!is_numeric($v) || in_array($k, $this->forceString)) {
                $value = sprintf('"%s"', (string) $v);
            } else {
                $value = $v;
            }

            $replacePairs['$P{' . $k . '}'] = $value;
            $replacePairs['$P!{' . $k . '}'] = $v;
        }

        return strtr($this->query(), $replacePairs);
    }

    /**
     * @param array $data
     *
     * @return array
     *
     * @throws Exception
     */
    public function get($data = [])
    {
        $defaultData = $this->getDefaultData();
        $data = array_merge($defaultData, $data);

        $query = $this->compile($data);

        return Portabilis_Utils_Database::fetchPreparedQuery($query, ['fetchMode' => $this->fetchMode]);
    }

    /**
     * Retorna os parâmetros default do report
     *
     * @return array
     */
    protected function getDefaultData()
    {
        return [];
    }
}
