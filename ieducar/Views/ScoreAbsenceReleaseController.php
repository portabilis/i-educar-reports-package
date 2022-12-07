<?php

class ScoreAbsenceReleaseController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var int
     */
    protected $_processoAp = 999231;

    protected $_titulo = 'Relatório de Notas e Faltas lançadas por escola';

    protected function _preRender()
    {
        parent::_preRender();
        $this->breadcrumb('Relatório de notas/faltas lançadas por escola', [
            'educar_index' => 'Escola',
        ]);
    }

    public function form()
    {
        $this->inputsHelper()->dynamic(['ano', 'instituicao']);
        $this->inputsHelper()->dynamic('EscolaObrigatorioParaNivelEscolar', ['required' => false]);
        $this->inputsHelper()->dynamic('curso', ['required' => false]);
        $this->inputsHelper()->dynamic('serie', ['required' => false]);
        $this->inputsHelper()->dynamic('turma', ['required' => false]);
        $sexo = ['' => 'Ambos',
            'M' => 'Masculino',
            'F' => 'Feminino'];

        $options = ['label' => 'Sexo', 'resources' => $sexo, 'value' => '', 'required' => false];
        $this->inputsHelper()->select('sexo', $options);
    }

    public function report()
    {
        return new ScoreAbsenceReleaseReport();
    }

    public function beforeValidation()
    {
        $this->report->addArg('ano', (int) $this->getRequest()->ano);
        $this->report->addArg('instituicao', (int) $this->getRequest()->ref_cod_instituicao);
        $this->report->addArg('escola', (int) $this->getRequest()->ref_cod_escola);
        $this->report->addArg('curso', (int) $this->getRequest()->ref_cod_curso);
        $this->report->addArg('serie', (int) $this->getRequest()->ref_cod_serie);
        $this->report->addArg('turma', (int) $this->getRequest()->ref_cod_turma);
        $this->report->addArg('sexo', (string) $this->getRequest()->sexo);
    }
}
