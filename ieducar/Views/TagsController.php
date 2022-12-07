<?php

class TagsController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var int
     */
    protected $_processoAp = 999235;

    protected $_titulo = 'Relação de etiquetas para mala direta';

    protected function _preRender()
    {
        parent::_preRender();
        $this->breadcrumb('Relação de etiquetas para mala direta', [
            'educar_index.php' => 'Escola',
        ]);
    }

    public function form()
    {
        $this->inputsHelper()->dynamic(['ano', 'instituicao', 'escola']);
        $this->inputsHelper()->dynamic('curso', ['required' => false]);
        $this->inputsHelper()->dynamic('serie', ['required' => false]);
        $this->inputsHelper()->dynamic('turma', ['required' => false]);

        $opcoes = [1 => 'Aprovado', 2 => 'Reprovado', 3 => 'Cursando', 4 => 'Transferido',
            6 => 'Abandono', 9 => 'Exceto Transferidos/Abandono', 10 => 'Todas', 12 => 'Aprovado com dependência', 13 => 'Aprovado pelo conselho'];

        $this->campoLista('situacao', 'Situação', $opcoes, 10);
    }

    public function report()
    {
        return new TagsReport();
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
        $this->report->addArg('proerd', $this->getRequest()->proerd ? 1 : 0);
    }
}
