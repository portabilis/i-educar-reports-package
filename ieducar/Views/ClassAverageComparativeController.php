<?php

class ClassAverageComparativeController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var int
     */
    protected $_processoAp = 999872;

    /**
     * @var string
     */
    protected $_titulo = 'Comparativo de média da turma';

    /**
     * @inheritdoc
     */
    protected function _preRender()
    {
        parent::_preRender();

        Portabilis_View_Helper_Application::loadStylesheet($this, 'intranet/styles/localizacaoSistema.css');

        $this->breadcrumb('Relatório comparativo de média da turma', [
            'educar_index.php' => 'Escola',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function form()
    {
        $this->inputsHelper()->dynamic('ano', ['required' => true]);
        $this->inputsHelper()->dynamic('instituicao', ['required' => true]);
        $this->inputsHelper()->dynamic('escola', ['required' => true]);
        $this->inputsHelper()->dynamic('curso', ['required' => true]);
        $this->inputsHelper()->dynamic('serie', ['required' => false]);
        $this->inputsHelper()->dynamic('turma', ['required' => false]);

        $opcoes = [
            0 => 'Selecione',
            1 => 'Etapa 1',
            2 => 'Etapa 2',
            3 => 'Etapa 3',
            4 => 'Etapa 4',
        ];

        $this->campoLista('etapa', 'Etapa', $opcoes, null, '', false, '', '', false, true);
    }

    /**
     * @inheritdoc
     */
    public function beforeValidation()
    {
        $this->report->addArg('ano', (int) $this->getRequest()->ano);
        $this->report->addArg('instituicao', (int) $this->getRequest()->ref_cod_instituicao);
        $this->report->addArg('escola', (int) $this->getRequest()->ref_cod_escola);
        $this->report->addArg('curso', (int) $this->getRequest()->ref_cod_curso);
        $this->report->addArg('serie', (int) $this->getRequest()->ref_cod_serie);
        $this->report->addArg('turma', (int) $this->getRequest()->ref_cod_turma);
        $this->report->addArg('etapa', (int) $this->getRequest()->etapa);
    }

    /**
     * @return ClassAverageComparativeReport
     *
     * @throws Exception
     */
    public function report()
    {
        return new ClassAverageComparativeReport();
    }
}
