<?php

use iEducar\Community\Reports\Services\SchoolHistory\Objects\SchoolHistory;
use iEducar\Community\Reports\Services\SchoolHistory\SchoolHistoryService;
use App\Models\LegacyRegistration;
use iEducar\Reports\BaseModifier;

class SchoolHistoryModifier extends BaseModifier
{
    /**
     * @inheritdoc
     */
    public function modify($data)
    {

        $main = $data['main'];
        $alunos = collect($main)->unique('cod_aluno')->pluck('cod_aluno')->toArray();
        $schoolHistory = new SchoolHistory(new SchoolHistoryService, true);

        foreach ($alunos as $aluno) {
            $this->args['aluno'] = $aluno;

            $data['scores'] = array_merge((new QuerySchoolHistoryScores())->get($this->args), $data['scores'] ?: []);
            $data['scores_diversified'] = array_merge((new QuerySchoolHistoryScoresDiversified())->get($this->args), $data['scores_diversified'] ?: []);
            $this->args['aluno'] = 0;
        }

        $matriculasTransferido = $this->getTranfers($alunos);

        $this->args['matriculas_transferido'] = $matriculasTransferido->implode(',') ?: 0;

        $matriculasTransferido = $matriculasTransferido->toArray();

        foreach ($main as $history) {
            $schoolHistory->addDataGroupByDiscipline($history);
        }

        $schoolHistory->makeAllObservations();
        $schoolHistory->makeFooterData();

        $data['main'] = $schoolHistory->getLines();

        foreach ($data['main'] as $key => $value) {
            $line = $data['main'][$key];

            $line['matricula_transferido'] = $matriculasTransferido[$value['cod_aluno']];

            $data['main'][$key] = $line;
        }

        $this->args['tipo_base'] = ComponenteCurricular_Model_TipoBase::COMUM;
        $data['registration_transfer_common'] = (new QuerySchoolHistoryRegistrationTransferred())->get($this->args);

        $this->args['tipo_base'] = ComponenteCurricular_Model_TipoBase::DIVERSIFICADA;
        $data['registration_transfer_diversified'] = (new QuerySchoolHistoryRegistrationTransferred())->get($this->args);

        $data['registration_transfer_absence'] = (new QuerySchoolHistoryRegistrationTransferredAbsence())->get($this->args);

        $data['query_empty_sub']  = [1];

        return $data;
    }

    private function getTranfers($students)
    {
        return LegacyRegistration::active()
            ->currentYear()
            ->where('aprovado', App_Model_MatriculaSituacao::TRANSFERIDO)
            ->whereHas('course', function ($q) {
                return $q->where('curso.modalidade_curso', 1);
            })
            ->whereHas(
                'enrollments.schoolClass', function ($q) {
                return $q->where('turma.tipo_atendimento', '<>', 4)->orWhereNull('turma.tipo_atendimento');
            })
            ->whereHas('student', static function ($q) {
                $q->whereDoesntHave('registrations', static function ($q) {
                    $q->currentYear();
                    $q->active();
                    $q->whereIn('aprovado', [1, 2, 12, 13, 14]);
                });
            })
            ->whereIn('ref_cod_aluno', $students)
            ->orderBy('cod_matricula', 'desc')
            ->pluck('cod_matricula', 'ref_cod_aluno');
    }
}
