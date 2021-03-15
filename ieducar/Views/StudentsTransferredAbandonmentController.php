<?php

require_once 'lib/Portabilis/Controller/ReportCoreController.php';
require_once 'Reports/Reports/StudentsTransferredAbandonmentReport.php';

class StudentsTransferredAbandonmentController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var int 
     */
    protected $_processoAp = 999607;

    /**
     * @var string
     */
    protected $_titulo = 'Alunos Transferidos/Abandono';

    /**
     * @inheritdoc
     */
    protected function _preRender()
    {
        parent::_preRender();

        Portabilis_View_Helper_Application::loadStylesheet($this, 'intranet/styles/localizacaoSistema.css');

        $this->breadcrumb('Relatório de alunos transferidos e em abandono', [
            'educar_index.php' => 'Escola',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function form()
    {
        $this->inputsHelper()->dynamic(['ano', 'instituicao']);
        $this->inputsHelper()->dynamic('escola', ['required' => false]);
        $this->inputsHelper()->dynamic('curso', ['required' => false]);
        $this->inputsHelper()->dynamic('serie', ['required' => false]);
        $this->inputsHelper()->dynamic(['dataInicial', 'dataFinal']);
        $opcoes = [1 => 'Abandono', 2 => 'Transferido', 9 => 'Ambos'];
        $this->campoLista('situacao', 'Situa&ccedil;&atilde;o', $opcoes, 9);
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
        $this->report->addArg('dt_inicial', $this->getRequest()->data_inicial);
        $this->report->addArg('dt_final', $this->getRequest()->data_final);
        $this->report->addArg('situacao', (int) $this->getRequest()->situacao);
    }

    /**
     * @return StudentsTransferredAbandonmentReport
     *
     * @throws Exception
     */
    public function report()
    {
        return new StudentsTransferredAbandonmentReport();
    }
}
