<?php

use App\Models\LegacyRegistration;
use iEducar\Reports\BaseModifier;

class SchoolHistoryNineYearsModifier extends BaseModifier
{
    /**
     * @inheritdoc
     */
    public function modify($data)
    {
        $main = $data['main'];
        $matriculasTransferido = array_unique(array_map(function ($aluno) {
            return $aluno['matricula_transferido'];
        },$main));

        $this->args['matriculas_transferido'] = implode(',', $matriculasTransferido) ?: 0;

        $data['registration_transfer'] = (new QuerySchoolHistoryNineYearsTransfer())->get($this->args);

        return $data;
    }
}
