<?php

class TransportationUsersController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var string
     */
    protected $_titulo = 'i-Educar - Relação Usuários de Transporte';

    /**
     * @var int
     */
    protected $_processoAp = 999825;

    /**
     * @inheritdoc
     */
    protected function _preRender()
    {
        parent::_preRender();

        Portabilis_View_Helper_Application::loadStylesheet($this, 'intranet/styles/localizacaoSistema.css');

        $this->breadcrumb('Relatório de usuários do transporte', [
            'educar_transporte_escolar_index.php' => 'Transporte escolar',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function form()
    {
        $this->inputsHelper()->dynamic(['ano', 'instituicao']);
        $this->inputsHelper()->simpleSearchPessoaj('destino', ['label' => 'Destino', 'required' => false]);
    }

    /**
     * @inheritdoc
     */
    public function beforeValidation()
    {
        $this->report->addArg('ano', (int) $this->getRequest()->ano);
        $this->report->addArg('instituicao', (int) $this->getRequest()->ref_cod_instituicao);
        $this->report->addArg('destino', (int) $this->getRequest()->pessoaj_destino);
    }

    /**
     * @return TransportationUsersReport
     *
     * @throws Exception
     */
    public function report()
    {
        return new TransportationUsersReport();
    }
}
