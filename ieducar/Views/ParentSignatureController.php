<?php

class ParentSignatureController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var int
     */
    protected $_processoAp = 999870;

    protected $_titulo = 'Lista de alunos para assinatura dos pais';

    protected function _preRender()
    {
        parent::_preRender();
        $this->breadcrumb('Lista de alunos para assinatura dos pais', [
            'educar_index' => 'Escola',
        ]);
    }

    public function form()
    {
        $this->inputsHelper()->dynamic(['ano', 'instituicao', 'escola']);
        $this->inputsHelper()->dynamic(['curso', 'serie', 'turma'], ['required' => false]);

        $resources = [
            10 => 'Todas',
            9 => 'Exceto Transferidos/Abandono',
            1 => 'Aprovado',
            2 => 'Reprovado',
            3 => 'Cursando',
            4 => 'Transferido',
            5 => 'Reclassificado',
            6 => 'Abandono',
            7 => 'Em exame',
            12 => 'Aprovado com dependência'
        ];

        $options = [
            'label' => 'Situação',
            'resources' => $resources,
            'value' => 9,
            'required' => false
        ];

        $this->inputsHelper()->select('situacao', $options);
        $this->inputsHelper()->checkbox('definir_titulo', ['label' => 'Definir título do relatório?', 'required' => false]);
        $this->inputsHelper()->text('titulo', ['label' => 'Título', 'required' => false, 'size' => 30]);
        $this->loadResourceAssets($this->getDispatcher());
    }

    public function report()
    {
        return new ParentSignatureReport();
    }

    public function beforeValidation()
    {
        $this->report->addArg('ano', (int) $this->getRequest()->ano);
        $this->report->addArg('instituicao', (int) $this->getRequest()->ref_cod_instituicao);
        $this->report->addArg('escola', (int) $this->getRequest()->ref_cod_escola);
        $this->report->addArg('curso', (int) $this->getRequest()->ref_cod_curso);
        $this->report->addArg('serie', (int) $this->getRequest()->ref_cod_serie);
        $this->report->addArg('turma', (int) $this->getRequest()->ref_cod_turma);
        $this->report->addArg('situacao', (int) $this->getRequest()->situacao);
        $this->report->addArg('definir_titulo', (bool) $this->getRequest()->definir_titulo);
        $this->report->addArg('titulo', $this->getRequest()->titulo);
    }
}
