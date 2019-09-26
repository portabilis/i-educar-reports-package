<?php

use App\Menu;

require_once 'lib/Portabilis/Controller/ReportCoreController.php';
require_once 'Reports/Reports/RegistrationCertificateReport.php';

class RegistrationCertificateController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var int
     */
    protected $_processoAp = 999103;

    /**
     * @var string
     */
    protected $_titulo = 'Relatório Atestado de Matrícula';

    /**
     * @inheritdoc
     */
    protected function _preRender()
    {
        parent::_preRender();

        Portabilis_View_Helper_Application::loadStylesheet($this, 'intranet/styles/localizacaoSistema.css');

        $this->breadcrumb($this->titulo(), [
            'educar_index.php' => 'Escola',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function form()
    {
        $this->inputsHelper()->dynamic(['ano', 'instituicao']);
        $this->inputsHelper()->dynamic(['escola'], ['required' => false]);
        $this->inputsHelper()->simpleSearchMatricula('Matrícula', ['required' => false]);
        $this->inputsHelper()->select('modelo', [
            'label' => 'Modelo',
            'resources' => [
                1 => 'Modelo 1',
            ],
            'value' => 1
        ]);
        $this->campoMemo('observacoes', 'Observações', $this->observacao, 48, 5, false);
        $this->inputsHelper()->checkbox('emitir_nome_diretor', ['label' => 'Emitir nome do diretor na assinatura']);
        $this->inputsHelper()->checkbox('emitir_secretario_escolar', ['label' => 'Emitir assinatura do secretário escolar']);
    }

    /**
     * @inheritdoc
     */
    public function beforeValidation()
    {
        $this->report->addArg('ano', (int) $this->getRequest()->ano);
        $this->report->addArg('instituicao', (int) $this->getRequest()->ref_cod_instituicao);
        $this->report->addArg('escola', (int) $this->getRequest()->ref_cod_escola);
        $this->report->addArg('matricula', (int) $this->getRequest()->matricula_id);
        $this->report->addArg('modelo', (int) $this->getRequest()->modelo);
        $this->report->addArg('cabecalho_alternativo', (int) $GLOBALS['coreExt']['Config']->report->header->alternativo);
        $this->report->addArg('emitir_nome_diretor', (bool) $this->getRequest()->emitir_nome_diretor);
        $this->report->addArg('emitir_secretario_escolar', (bool) $this->getRequest()->emitir_secretario_escolar);
        $this->report->addArg('observacoes', (string) $this->getRequest()->observacoes);
        $this->report->addArg('titulo', (string) $this->titulo());
    }

    /**
     * @return RegistrationCertificateReport
     *
     * @throws Exception
     */
    public function report()
    {
        return new RegistrationCertificateReport();
    }

    /**
     * @return string
     */
    public function titulo()
    {
        $menu = Menu::query()->where('process', $this->_processoAp)->first();

        return $menu->title;
    }
}
