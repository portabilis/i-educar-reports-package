<?php

require_once 'lib/Portabilis/Controller/ReportCoreController.php';
require_once 'Reports/Reports/LibraryPublishersReport.php';

class LibraryPublishersController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var int
     */
    protected $_processoAp = 999616;

    /**
     * @var string
     */
    protected $_titulo = 'Relatório de editoras';

    /**
     * @inheritdoc
     */
    protected function _preRender()
    {
        parent::_preRender();

        Portabilis_View_Helper_Application::loadStylesheet($this, 'intranet/styles/localizacaoSistema.css');

        $this->breadcrumb('Relatório de editoras', [
            'educar_biblioteca_index.php' => 'Biblioteca',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function form()
    {
        $this->inputsHelper()->dynamic(['instituicao', 'escola']);
    }

    /**
     * @inheritdoc
     */
    public function beforeValidation()
    {
        $this->report->addArg('instituicao', (int) $this->getRequest()->ref_cod_instituicao);
        $this->report->addArg('escola', (int) $this->getRequest()->ref_cod_escola);
    }

    /**
     * @return LibraryPublishersReport
     *
     * @throws Exception
     */
    public function report()
    {
        return new LibraryPublishersReport();
    }
}
