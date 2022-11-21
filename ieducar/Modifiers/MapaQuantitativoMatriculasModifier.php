<?php

use iEducar\Reports\BaseModifier;

class MapaQuantitativoMatriculasModifier extends BaseModifier
{
    /**
     * @inheritdoc
     */
    public function modify($data)
    {
        $usingThisModifier = [
            'portabilis_alunos_geral_instituicao',
            'portabilis_alunos_geral_instituicao_2',
            'portabilis_alunos_geral_instituicao_3',
        ];

        if (!in_array($this->templateName, $usingThisModifier)) {
            return $data;
        }

        $main = $data['main'];

        $ultimaMatriculaPorLinha = array_map(function ($student) {
            return $student['ultima_matricula'];
        }, $main);

        $ultimaMatricula = '';
        if(! empty($ultimaMatriculaPorLinha)) {
            $ultimaMatricula = new DateTime(max($ultimaMatriculaPorLinha));
        }

        foreach ($main as $key => $value) {
            $line = $main[$key];
            $line['ultima_matricula'] = empty($ultimaMatricula) ? $ultimaMatricula : $ultimaMatricula->format('d/m/Y');
            $data['main'][$key] = $line;
        }

        return $data;
    }
}
