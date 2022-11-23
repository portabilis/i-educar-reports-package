<?php

class EmployeeAllocatedTimeController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var int
     */
    protected $_processoAp = 999107;

    protected $_titulo = 'Relatório de Horas Alocadas por Servidor';

    protected function _preRender()
    {
        parent::_preRender();
        $this->breadcrumb('Horas alocadas por servidor', [
            'educar_servidores_index.php' => 'Servidores',
        ]);
    }

    public function form()
    {
        $this->inputsHelper()->dynamic(['ano', 'instituicao']);
        $this->inputsHelper()->dynamic('escola', ['required' => false]);
    }

    public function report()
    {
        return new EmployeeAllocatedTimeReport();
    }

    public function beforeValidation()
    {
        $this->report->addArg('ano', (int) $this->getRequest()->ano);
        $this->report->addArg('instituicao', (int) $this->getRequest()->ref_cod_instituicao);
        $this->report->addArg('escola', (int) $this->getRequest()->ref_cod_escola);
    }
}
