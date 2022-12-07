<?php

use iEducar\Reports\BaseModifier;

class GeneralMovementModifier extends BaseModifier
{
    /**
    * @inheritdoc
    */
    public function modify($data)
    {
        $main = $data['main'];

        $totalLinhaRodape = 0;
        $totalGeralLinhaRodape = 0;

        foreach ($main as $key => $value) {
            $line = $main[$key];

            $totalLinha = array_sum([
                $value['ed_inf_int'],
                $value['ed_inf_parc'],
                $value['ano_1'],
                $value['ano_2'],
                $value['ano_3'],
                $value['ano_4'],
                $value['ano_5'],
                $value['ano_6'],
                $value['ano_7'],
                $value['ano_8'],
                $value['ano_9'],
            ]);

            $alunosQueSairam = array_sum([
                $value['aband'],
                $value['transf'],
                $value['obito'],
                $value['recla']
            ]);

            $line['total_linha'] = $totalLinha;

            $totalGeralLinha = ($totalLinha + $value['admitidos'] - $alunosQueSairam);
            $line['total_geral_linha'] = $totalGeralLinha;

            $totalLinhaRodape += $totalLinha;
            $line['total_linha_rodape'] = $totalLinhaRodape;

            $totalGeralLinhaRodape += $totalGeralLinha;
            $line['total_geral_linha_rodape'] = $totalGeralLinhaRodape;

            $data['main'][$key] = $line;
        }

        return $data;
    }
}
