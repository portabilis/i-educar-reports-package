<?php

require_once 'lib/Portabilis/Controller/ReportCoreController.php';
require_once 'Reports/Reports/LibraryClientsReport.php';
require_once 'lib/Portabilis/Date/Utils.php';

class LibraryClientsController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var int
     */
    protected $_processoAp = 999845;

    /**
     * @var string
     */
    protected $_titulo = 'Relatório de clientes';

    /**
     * @inheritdoc
     */
    protected function _preRender()
    {
        parent::_preRender();

        Portabilis_View_Helper_Application::loadStylesheet($this, 'intranet/styles/localizacaoSistema.css');

        $this->breadcrumb('Relatório de clientes', [
            'educar_biblioteca_index.php' => 'Biblioteca',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function form()
    {
        $this->inputsHelper()->dynamic(['instituicao', 'escola']);
        $this->inputsHelper()->dynamic('biblioteca', ['required' => false]);
        $this->inputsHelper()->dynamic('bibliotecaTipoCliente', ['required' => false]);
    }

    /**
     * @inheritdoc
     */
    public function beforeValidation()
    {
        $this->report->addArg('instituicao', (int) $this->getRequest()->ref_cod_instituicao);
        $this->report->addArg('escola', (int) $this->getRequest()->ref_cod_escola);
        $this->report->addArg('biblioteca', (int) $this->getRequest()->ref_cod_biblioteca);
        $this->report->addArg('tipo_cliente', (int) $this->getRequest()->ref_cod_cliente_tipo);
    }

    /**
     * @return LibraryClientsReport
     *
     * @throws Exception
     */
    public function report()
    {
        return new LibraryClientsReport();
    }
}
