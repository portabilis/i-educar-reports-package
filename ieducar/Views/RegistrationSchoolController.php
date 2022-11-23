<?php

class RegistrationSchoolController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var int
     */
    protected $_processoAp = 999859;

    protected $_titulo = 'Relatório de matrículas de alunos por escola';

    protected function _preRender()
    {
        parent::_preRender();
        $this->breadcrumb('Relatório de matrículas de alunos por escola', [
            'educar_index' => 'Escola',
        ]);
    }

    public function form()
    {
        $this->inputsHelper()->dynamic([
            'ano',
            'instituicao',
            'escola'
        ]);
        $this->inputsHelper()->dynamic('curso', ['required' => false]);
        $this->inputsHelper()->dynamic('serie', ['required' => false]);
        $this->inputsHelper()->dynamic('situacaoMatricula');
        $options = [
            'label' => 'Alunos com dependência',
            'resources' => [
                'Todos',
                'Somente alunos com dependência',
                'Não exibir alunos com dependência'
            ],
            'required' => false,
            'value' => 0
        ];
        $this->inputsHelper()->select('dependencia', $options);
    }

    public function report()
    {
        return new RegistrationSchoolReport();
    }

    public function beforeValidation()
    {
        $this->report->addArg('ano', (int) $this->getRequest()->ano);
        $this->report->addArg('instituicao', (int) $this->getRequest()->ref_cod_instituicao);
        $this->report->addArg('escola', (int) $this->getRequest()->ref_cod_escola);
        $this->report->addArg('curso', (int) $this->getRequest()->ref_cod_curso);
        $this->report->addArg('serie', (int) $this->getRequest()->ref_cod_serie);
        $this->report->addArg('situacao', (int) $this->getRequest()->situacao_matricula_id);
        $this->report->addArg('dependencia', (int) $this->getRequest()->dependencia);
    }
}
