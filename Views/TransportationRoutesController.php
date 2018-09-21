<?php

require_once 'lib/Portabilis/Controller/ReportCoreController.php';
require_once 'Reports/Reports/TransportationRoutesReport.php';

class TransportationRoutesController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var string
     */
    protected $_titulo = 'i-Educar - Rotas de Transporte';

    /**
     * @var int
     */
    protected $_processoAp = 21242;

    /**
     * @inheritdoc
     */
    protected function _preRender()
    {
        parent::_preRender();

        Portabilis_View_Helper_Application::loadStylesheet($this, 'intranet/styles/localizacaoSistema.css');

        $this->breadcrumb('Relatório de rotas do transporte', [
            'educar_transporte_escolar_index.php' => 'Transporte escolar',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function form()
    {
        $this->inputsHelper()->dynamic(['ano', 'instituicao']);
        $this->inputsHelper()->simpleSearchEmpresa(null, ['required' => false]);
        $this->inputsHelper()->select('modelo', [
            'label' => 'Modelo',
            'resources' => [
                1 => 'Simples',
                2 => 'Detalhado'
            ],
            'value' => 1
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
        $this->report->addArg('empresa', ((int) $this->getRequest()->empresa_id == null ? 0 : (int) $this->getRequest()->pessoaj_id));
        $this->report->addArg('modelo', (int) $this->getRequest()->modelo);
    }

    /**
     * @return TransportationRoutesReport
     *
     * @throws Exception
     */
    public function report()
    {
        return new TransportationRoutesReport();
    }
}
