<?php

use App\Menu;

class TransferenceCertificateController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var int
     */
    protected $_processoAp = 999216;

    /**
     * @var string
     */
    protected $_titulo = 'Atestado de Transferência';

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
        $this->inputsHelper()->simpleSearchMatricula(null, ['required' => false]);
        $this->campoMemo('observacao', 'Observação', $this->observacao, 48, 5, false);
        $this->inputsHelper()->checkbox('emitir_nome_diretor', ['label' => 'Emitir assinatura do gestor escolar']);
        $this->inputsHelper()->checkbox('emitir_secretario_escolar', ['label' => 'Emitir assinatura do secretário escolar']);
        $this->inputsHelper()->checkbox('mostrar_prazo_entrega_historico', ['label' => 'Emitir prazo de entrega do histórico escolar?']);
        $options = [
            'required' => false,
            'label' => 'Prazo de entrega do histórico escolar.',
            'placeholder' => '',
            'value' => 30,
            'max_length' => 3,
            'size' => 20
        ];
        $this->inputsHelper()->integer('prazo_entrega_historico', $options);
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
        $this->report->addArg('matricula', (int) $this->getRequest()->matricula_id);
        $this->report->addArg('observacao', $this->getRequest()->observacao);
        $this->report->addArg('cabecalho_alternativo', (int) $GLOBALS['coreExt']['Config']->report->header->alternativo);
        $this->report->addArg('emitir_nome_diretor', (bool) $this->getRequest()->emitir_nome_diretor);
        $this->report->addArg('emitir_secretario_escolar', (bool) $this->getRequest()->emitir_secretario_escolar);
        $this->report->addArg('mostrar_prazo_entrega_historico', (bool) $this->getRequest()->mostrar_prazo_entrega_historico);
        $this->report->addArg('prazo_entrega_historico', (int) $this->getRequest()->prazo_entrega_historico);
        $this->report->addArg('titulo', (string) $this->titulo());
    }

    /**
     * @return TransferenceCertificateReport
     *
     * @throws Exception
     */
    public function report()
    {
        return new TransferenceCertificateReport();
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
