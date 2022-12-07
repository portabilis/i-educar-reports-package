<?php

class GeneralMovementController extends Portabilis_Controller_ReportCoreController
{
    protected $_processoAp = 9998868;

    protected $_titulo = 'Relatório de movimento geral';

    protected function _preRender()
    {
        parent::_preRender();

        $this->breadcrumb('Relatório de movimento geral', [
            'educar_index.php' => 'Escola',
        ]);
    }

    public function form()
    {
        $this->inputsHelper()->dynamic(['ano', 'instituicao']);
        $this->inputsHelper()->multipleSearchCurso('', ['label' => 'Cursos', 'required' => false]);
        $this->inputsHelper()->dynamic(['dataInicial', 'dataFinal']);
        $this->loadResourceAssets($this->getDispatcher());
    }

    public function beforeValidation()
    {
        $curso = $this->getRequest()->curso ?? [];
        $curso = implode(',', array_filter($curso));
        $this->report->addArg('ano', (int) $this->getRequest()->ano);
        $this->report->addArg('instituicao', (int) $this->getRequest()->ref_cod_instituicao);
        $this->report->addArg('curso', trim($curso) == '' ? 0 : $curso);
        $this->report->addArg('data_inicial', Portabilis_Date_Utils::brToPgSQL($this->getRequest()->data_inicial));
        $this->report->addArg('data_final', Portabilis_Date_Utils::brToPgSQL($this->getRequest()->data_final));
    }

    public function report(): GeneralMovementReport
    {
        return new GeneralMovementReport();
    }
}
