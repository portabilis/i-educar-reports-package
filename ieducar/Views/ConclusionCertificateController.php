<?php

class ConclusionCertificateController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var int
     */
    protected $_processoAp = 999812;

    /**
     * @var string
     */
    protected $_titulo = 'Declaração de Conclusão de Curso';

    /**
     * @inheritdoc
     */
    protected function _preRender()
    {
        parent::_preRender();

        Portabilis_View_Helper_Application::loadStylesheet($this, 'intranet/styles/localizacaoSistema.css');

        $this->breadcrumb('Emissão de declaração de conclusão de curso', [
            'educar_index.php' => 'Escola',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function form()
    {
        $this->inputsHelper()->dynamic(['ano', 'instituicao', 'escola', 'curso', 'serie']);
        $this->inputsHelper()->dynamic('turma', (['required' => false]));
        $this->inputsHelper()->simpleSearchMatricula(null, ['required' => false]);
        $this->inputsHelper()->checkbox('lote', ['label' => 'Emitir em lote?']);
        $this->inputsHelper()->checkbox('mostrar_prazo_entrega_historico', ['label' => 'Emitir prazo de entrega do histórico escolar?']);
        $this->inputsHelper()->integer('prazo_entrega_historico', [
            'required' => false,
            'label' => 'Prazo de entrega do histórico escolar.',
            'placeholder' => '',
            'value' => 30,
            'max_length' => 3,
            'size' => 20
        ]);
        $this->campoMemo('observacao', 'Observação', $this->observacao, 48, 5, false);

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

        if ((bool) $this->getRequest()->lote) {
            $this->report->addArg('curso', (int) $this->getRequest()->ref_cod_curso);
            $this->report->addArg('serie', (int) $this->getRequest()->ref_cod_serie);
            $this->report->addArg('turma', (int) $this->getRequest()->ref_cod_turma);
        }

        $this->report->addArg('mostrar_prazo_entrega_historico', (bool) $this->getRequest()->mostrar_prazo_entrega_historico);
        $this->report->addArg('prazo_entrega_historico', (int) $this->getRequest()->prazo_entrega_historico);
        $this->report->addArg('observacao', $this->getRequest()->observacao);
    }

    /**
     * @return ConclusionCertificateReport
     *
     * @throws Exception
     */
    public function report()
    {
        return new ConclusionCertificateReport();
    }
}
