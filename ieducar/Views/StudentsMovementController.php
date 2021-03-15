<?php

require_once 'lib/Portabilis/Date/Utils.php';
require_once 'lib/Portabilis/Controller/ReportCoreController.php';
require_once 'Reports/Reports/StudentsMovementReport.php';

class StudentsMovementController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var int
     */
    protected $_processoAp = 999201;

    /**
     * @var string
     */
    protected $_titulo = 'Relatório de Movimento de Alunos e Enturmações';

    /**
     * @inheritdoc
     */
    protected function _preRender()
    {
        parent::_preRender();

        Portabilis_View_Helper_Application::loadStylesheet($this, 'intranet/styles/localizacaoSistema.css');

        $this->breadcrumb('Relatório de movimentos de alunos e enturmações', [
            'educar_index.php' => 'Escola',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function form()
    {
        $this->inputsHelper()->dynamic(['ano', 'instituicao', 'escola', 'curso']);
        $this->inputsHelper()->dynamic('serie', ['required' => false]);
        $this->inputsHelper()->dynamic('turma', ['required' => false]);
        $this->inputsHelper()->dynamic('etapaEscola', ['required' => false]);
        $this->inputsHelper()->select('modelo', [
            'label' => 'Modelo',
            'resources' => [
                1 => 'Modelo 1',
            ],
            'value' => 1
        ]);
        $this->inputsHelper()->dynamic(['dataInicial', 'dataFinal']);
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
        $this->report->addArg('curso', (int) $this->getRequest()->ref_cod_curso);
        $this->report->addArg('serie', (int) $this->getRequest()->ref_cod_serie);
        $this->report->addArg('turma', (int) $this->getRequest()->ref_cod_turma);
        $this->report->addArg('data_inicial', Portabilis_Date_Utils::brToPgSQL($this->getRequest()->data_inicial));
        $this->report->addArg('data_final', Portabilis_Date_Utils::brToPgSQL($this->getRequest()->data_final));
        $this->report->addArg('etapa', (int) $this->getRequest()->etapa);
        $this->report->addArg('modelo', (int) $this->getRequest()->modelo);
    }

    /**
     * @return StudentsMovementReport
     *
     * @throws Exception
     */
    public function report()
    {
        return new StudentsMovementReport();
    }
}
