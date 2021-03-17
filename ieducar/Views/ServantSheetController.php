<?php

class ServantSheetController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @inheritdoc
     */
    protected $_processoAp = 999822;

    /**
     * @inheritdoc
     */
    protected $_titulo = 'Ficha do servidor';

    /**
     * @inheritdoc
     */
    protected function _preRender()
    {
        parent::_preRender();

        Portabilis_View_Helper_Application::loadStylesheet($this, 'intranet/styles/localizacaoSistema.css');

        $this->breadcrumb('Ficha do servidor', [
            'educar_servidores_index.php' => 'Servidores',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function form()
    {
        $this->inputsHelper()->dynamic(['ano', 'instituicao', 'escola']);
        $this->inputsHelper()->checkbox('branco', ['label' => 'Emitir em branco?', 'required' => false]);
        $this->inputsHelper()->simpleSearchServidor('servidor', ['label' => 'Servidor', 'required' => false]);
        $this->loadResourceAssets($this->getDispatcher());
    }

    /**
     * @inheritdoc
     */
    public function beforeValidation()
    {
        $this->report->addArg('ano', (int) $this->getRequest()->ano);
        $this->report->addArg('instituicao', (int) $this->getRequest()->ref_cod_instituicao);
        $this->report->addArg('escola', (int) $this->getRequest()->ref_cod_escola);
        $branco = (bool) $this->getRequest()->branco;

        if (!$branco) {
            $this->report->addArg('servidor', (int) $this->getRequest()->servidor_id);
        }

        $this->report->addArg('branco', $branco);
    }

    /**
     * @return ServantSheetReport
     *
     * @throws Exception
     */
    public function report()
    {
        return new ServantSheetReport();
    }
}
