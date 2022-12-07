<?php

class ClassBoardMapModifier extends CustomBaseModifier
{
    /**
     * @inheritdoc
     */
    public function modify($data)
    {
        $data['main'] = $this->process($data['main'], function ($tmp) {
            $sum = function ($key) use ($tmp) {
                return $this->sum($tmp, $key);
            };

            return [
                'nm_componente_curricular' => 'Mat/EPSM',
                'nota1' => $sum('nota1'),
                'nota2' => $sum('nota2'),
                'nota3' => $sum('nota3'),
                'nota4' => $sum('nota4'),
                'falta1' => $sum('falta1'),
                'falta2' => $sum('falta2'),
                'falta3' => $sum('falta3'),
                'falta4' => $sum('falta4'),
            ];
        });

        return $data;
    }
}
