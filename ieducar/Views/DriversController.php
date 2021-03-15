<?php

require_once 'lib/Portabilis/Controller/ReportCoreController.php';
require_once 'Reports/Reports/DriversReport.php';

class DriversController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var string
     */
    protected $_titulo = 'i-Educar - Motoristas de Transporte';

    /**
     * @var int
     */
    protected $_processoAp = 21252;

    /**
     * @inheritdoc
     */
    protected function _preRender()
    {
        parent::_preRender();

        Portabilis_View_Helper_Application::loadStylesheet($this, 'intranet/styles/localizacaoSistema.css');

        $this->breadcrumb('Relatório de motoristas do transporte', [
            'educar_transporte_escolar_index.php' => 'Transporte escolar',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function form()
    {
        $this->inputsHelper()->dynamic(['ano', 'instituicao']);
        $this->inputsHelper()->simpleSearchMotorista(null, ['required' => false]);
        $this->inputsHelper()->date('data', [
            'label' => 'Data',
            'required' => true
        ]);
        $this->inputsHelper()->text('observacao', [
            'required' => false,
            'label' => 'Observação',
            'placeholder' => 'Digite aqui uma observação',
            'max_length' => 65,
            'size' => 67,
            'inline' => false
        ]);
        $this->loadResourceAssets($this->getDispatcher());
    }

    /**
     * @inheritdoc
     */
    public function beforeValidation()
    {
        $this->report->addArg('instituicao', (int) $this->getRequest()->ref_cod_instituicao);
        $this->report->addArg('ano', (int) $this->getRequest()->ano);
        $this->report->addArg('motorista', (int) $this->getRequest()->motorista);
        $this->report->addArg('observacao', $this->getRequest()->observacao);
        $this->report->addArg('data', $this->getRequest()->data);
    }

    /**
     * @return DriversReport
     *
     * @throws Exception
     */
    public function report()
    {
        return new DriversReport();
    }
}
