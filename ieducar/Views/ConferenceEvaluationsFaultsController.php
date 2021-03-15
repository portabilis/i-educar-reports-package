<?php

class ConferenceEvaluationsFaultsController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var int
     */
    protected $_processoAp = 999809;

    /**
     * @var string
     */
    protected $_titulo = 'Relatório de Conferência de Notas e Faltas';

    /**
     * @inheritdoc
     */
    protected function _preRender()
    {
        parent::_preRender();

        Portabilis_View_Helper_Application::loadStylesheet($this, 'intranet/styles/localizacaoSistema.css');

        $this->breadcrumb('Emissão do relatório de conferência de notas e faltas', [
            'educar_index.php' => 'Escola',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function form()
    {
        $this->inputsHelper()->dynamic(['ano', 'instituicao', 'escola', 'curso', 'serie', 'turma']);
        $this->inputsHelper()->dynamic(['etapa'], ['required' => false]);
        $this->inputsHelper()->checkbox('emitir_legenda', ['label' => 'Emitir legenda?', 'required' => false]);
        $this->campoLista('modelo', 'Modelo', [
            1 => 'Simplificado'
        ], $this->modelo);
    }

    /**
     * @inheritdoc
     */
    public function beforeValidation()
    {
        $this->report->addArg('ano', (int)$this->getRequest()->ano);
        $this->report->addArg('instituicao', (int)$this->getRequest()->ref_cod_instituicao);
        $this->report->addArg('escola', (int)$this->getRequest()->ref_cod_escola);
        $this->report->addArg('curso', (int)$this->getRequest()->ref_cod_curso);
        $this->report->addArg('serie', (int)$this->getRequest()->ref_cod_serie);
        $this->report->addArg('turma', (int)$this->getRequest()->ref_cod_turma);
        $this->report->addArg('etapa', (int)$this->getRequest()->etapa);
        $this->report->addArg('modelo', (int)$this->getRequest()->modelo);
        $this->report->addArg('emitir_legenda', (bool)$this->getRequest()->emitir_legenda);
    }

    /**
     * @return ConferenceEvaluationsFaultsReport
     *
     * @throws Exception
     */
    public function report()
    {
        return new ConferenceEvaluationsFaultsReport();
    }
}
