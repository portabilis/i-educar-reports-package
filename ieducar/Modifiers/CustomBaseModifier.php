<?php

use iEducar\Reports\BaseModifier;

abstract class CustomBaseModifier extends BaseModifier
{
    /**
     * @var Portabilis_Model_Report_TipoBoletim
     */
    protected $templates;

    /**
     * @var array
     */
    protected $disciplinesToMerge = [
        86, //Matemática
        64, //Estudo e Prática de Situações Matemáticas
    ];

    /**
     * @var int
     */
    protected $courseToMergeDisciplines = 11; //Ensino Fundamental 9 Anos - Finais de 6º ao 9º

    /**
     * @inheritdoc
     */
    public function __construct($templateName, $args)
    {
        parent::__construct($templateName, $args);

        $this->templates = Portabilis_Model_Report_TipoBoletim::getInstance()->getReports();
    }

    /**
     * @param array  $data
     * @param string $key
     *
     * @return int|float|mixed
     */
    protected function sum($data, $key)
    {
        $val1 = $data[0][$key] ?? null;
        $val2 = $data[1][$key] ?? null;

        if (!boolval($val1) && !boolval($val2)) {
            return boolval($val1) ? $val1 : $val2;
        }

        return $val1 + $val2 + 0;
    }

    /**
     * @param array    $data
     * @param callable $callback
     *
     * @return array
     */
    protected function process($data, $callback)
    {
        $reportsSource = config('legacy.report.mostrar_relatorios');

        if (
            $reportsSource !== 'resende' ||
            (int) $this->args['curso'] !== $this->courseToMergeDisciplines
        ) {
            return $data;
        }

        $prev = ['matricula' => 0];
        $data[] = ['matricula' => 999999];
        $tmp = [];
        $newData = [];

        foreach ($data as $v) {
            if (
                $v['matricula'] !== $prev['matricula'] &&
                $prev['matricula'] !== 0 &&
                !empty($tmp)
            ) {
                $new = array_merge($prev, $callback($tmp));
                $newData[] = $new;
                $tmp = [];
            }

            if ($v['matricula'] === 999999) {
                break;
            }

            $prev = $v;

            if (in_array((int) $v['id_disciplina'], $this->disciplinesToMerge)) {
                if ($this->disciplinesToMerge[0] == $v['id_disciplina']) {
                    array_unshift($tmp, $v);
                } else {
                    $tmp[] = $v;
                }

                continue;
            }

            $newData[] = $v;
        }

        return $newData;
    }

    /**
     * @inheritdoc
     */
    public function modify($data)
    {
        return $data;
    }
}
