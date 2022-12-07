<?php

use iEducar\Reports\BaseModifier;

class MinutesFinalResultModifier extends BaseModifier
{
    private $disciplines = [];
    private $statistics = [
        'students' => 0,
        'approved' => 0,
        'disapproved' => 0,
        'studying' => 0,
        'transferred' => 0,
        'reclassified' => 0,
        'abandonment' => 0,
        'deceased' => 0,
        'dependency' => 0,
        'relocated' => 0
    ];

    /**
     * modify
     *
     * @param array $data
     *
     * @return array
     */
    public function modify($data)
    {
        $main = $data['main'];
        $main = $this->getAverage($main);
        $students = $this->getStudents($main);
        $this->disciplines = $this->getDisciplines($main);
        $getStudentDisciplines = $this->getStudentAndDisciplines($main);
        $studentDisciplines = $this->fillWithDisciplinesFake($getStudentDisciplines);
        $data['main'] = $this->fillData($studentDisciplines);
        $data['statistics'] = $this->fillStatistics($students, $main);

        return $data;
    }

    /**
     * getAverage
     *
     * @param array $main
     *
     * @return array
     */
    private function getAverage($main)
    {
        $database = config('legacy.report.mostrar_relatorios');
        $level = $this->args['serie'];

        if (! $this->doAverage($database, $level)) {
            return $main;
        }

        $disciplinesScores = $this->getScores($main);
        $students = $this->calcAverage($disciplinesScores);

        foreach ($students as $key => $value) {
            foreach ($main as $k => $v) {
                $main[$k]['usa_media_geral'] = true;
                if ($v['cod_matricula'] == $key) {
                    $main[$k]['media_geral'] = bcdiv($value, 1, 1);
                }
            }
        }

        return $main;
    }

    private function doAverage($database, $level)
    {
        $levels = [
            'saomigueldoscampos' => [40, 39, 30, 26, 24, 23, 7, 5, 2],
            'pilar' => [11, 9]
        ];

        if(! array_key_exists($database, $levels)) {
            return false;
        }

        if (in_array($level, $levels[$database])) {
            return true;
        }

        return false;
    }

    /**
     * getScores
     *
     * @param array $main
     *
     * @return array
     */
    private function getScores($main)
    {
        foreach ($main as $key => $value) {
            if (is_numeric($value['nota']) == 1) {
                $disciplinesScores[$value['cod_matricula']][] = $value['nota'];
            }
        }

        return $disciplinesScores;
    }

    /**
     * calcAverage
     *
     * @param array $disciplinesScores
     *
     * @return array
     */
    private function calcAverage($disciplinesScores)
    {
        $students = [];

        foreach ($disciplinesScores as $studentId => $value) {
            $value = array_filter($value);
            $students[$studentId]  = count($value) == 0 ? null : array_sum($value)/ count($value);
        }

        return $students;
    }

    /**
     * getStudents
     *
     * @param array $main
     *
     * @return array
     */
    private function getStudents($main)
    {
        $students = [];
        foreach ($main as $key => $value) {
            if (!in_array($value['cod_matricula'], $students)) {
                $students[$key] = $value['cod_matricula'];
            }
        }

        return $students;
    }

    /**
     * getDisciplines
     *
     * @param array $main
     *
     * @return array
     */
    private function getDisciplines($main)
    {
        foreach ($main as $key => $value) {
            if (!in_array($value['componente_curricular_id'], $this->disciplines)) {
                array_push($this->disciplines, $value['componente_curricular_id']);
            }
        }

        return $this->disciplines;
    }

    /**
     * fillStatistics
     *
     * @param array $students
     * @param array $main
     *
     * @return array
     */
    private function fillStatistics($students, $main)
    {
        foreach ($students as $key => $value) {
            $this->statistics['students']++;
            $this->statistics($main[$key]['aprovado']);
            if ($main[$key]['dependencia']) {
                $this->countDependency();
            }
        }

        $this->statistics['relocated'] = $main[0]['remanejado'];

        return $this->statistics;
    }

    /**
     * countDependency
     *
     * @return void
     */
    private function countDependency()
    {
        $this->statistics['dependency']++;
    }

    /**
     * getStudentAndDisciplines
     *
     * @param array $main
     *
     * @return array
     */
    private function getStudentAndDisciplines($main)
    {
        foreach ($main as $key => $value) {
            $student = $value['cod_matricula'];
            $disciplines = $value['componente_curricular'];
            $studentDisciplines[$student][$disciplines] = $value;
        }

        return $studentDisciplines;
    }

    /**
     * fillWithDisciplinesFake
     *
     * @param array $studentDisciplines
     *
     * @return array
     */
    private function fillWithDisciplinesFake($studentDisciplines)
    {
        $disciplines = count($this->disciplines);
        $absenceDisciplines = $this->calculateDisciplines();

        foreach ($studentDisciplines as $key => $value) {
            for ($i = 0; $i < $absenceDisciplines; $i++) {
                $studentDisciplines[$key][$disciplines] =  $this->inserted(current($value), $i);
                $disciplines++;
            }
        }

        return $studentDisciplines;
    }

    /**
     * fillData
     *
     * @param array $input
     *
     * @return array
     */
    private function fillData($input)
    {
        foreach ($input as $key => $value) {
            foreach ($value as $k => $v) {
                $output[] = $v;
            }
        }

        return $output;
    }

    /**
     * statistics
     *
     * @param int $status
     *
     * @return void
     */
    private function statistics($status)
    {
        switch ($status) {
            case App_Model_MatriculaSituacao::APROVADO:
                $this->statistics['approved']++;
                break;
            case App_Model_MatriculaSituacao::REPROVADO:
                $this->statistics['disapproved']++;
                break;
            case App_Model_MatriculaSituacao::REPROVADO_POR_FALTAS:
                $this->statistics['disapproved']++;
                break;
            case App_Model_MatriculaSituacao::EM_ANDAMENTO:
                $this->statistics['studying']++;
                break;
            case App_Model_MatriculaSituacao::TRANSFERIDO:
                $this->statistics['transferred']++;
                break;
            case App_Model_MatriculaSituacao::RECLASSIFICADO:
                $this->statistics['reclassified']++;
                break;
            case App_Model_MatriculaSituacao::ABANDONO:
                $this->statistics['abandonment']++;
                break;
            case App_Model_MatriculaSituacao::FALECIDO:
                $this->statistics['deceased']++;
                break;
        }
    }

    /**
     * inserted
     *
     * @param array $arr
     * @param int   $i
     *
     * @return array
     */
    private function inserted($arr = [], $i)
    {
        $arr['componente_curricular_id'] = 9999 . $i;
        $arr['componente_curricular'] = "- <id = {$arr['componente_curricular_id']}>";
        $arr['carga_horaria_componente'] = null;
        $arr['nota'] = null;
        $arr['media'] = null;
        $arr['faltas_componente'] = null;
        $arr['faltas_gerais'] = null;

        return $arr;
    }

    /**
     * calculateDisciplines
     *
     * @return int
     */
    private function calculateDisciplines()
    {
        $disciplines = count($this->disciplines);
        $totalPages = $this->totalPages($disciplines);
        $pages = $this->pages($totalPages);

        return $pages - $disciplines;
    }

    /**
     * totalPages
     *
     * @param int $disciplines
     *
     * @return float
     */
    private function totalPages($disciplines)
    {
        return ceil($disciplines / $this->disciplinesPage());
    }

    /**
     * pages
     *
     * @param int $totalPages
     *
     * @return float
     */
    private function pages($totalPages)
    {
        return $totalPages * $this->disciplinesPage();
    }

    private function disciplinesPage()
    {
        return $this->templateName == 'portabilis_ata_resultado_final_2' ? 9 : 12;
    }
}
