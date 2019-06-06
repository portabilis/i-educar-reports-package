<?php

require_once 'lib/Portabilis/Controller/ReportCoreController.php';
require_once 'Reports/Reports/StudentDisciplinaryOccurrenceReport.php';

class StudentDisciplinaryOccurrenceController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var int
     */
    protected $_processoAp = 999217;

    /**
     * @var string
     */
    protected $_titulo = 'Relatório de Ocorrências Disciplinares';

    /**
     * @inheritdoc
     */
    protected function _preRender()
    {
        parent::_preRender();

        Portabilis_View_Helper_Application::loadStylesheet($this, 'intranet/styles/localizacaoSistema.css');
        
        $this->breadcrumb('Relatório de Ocorrências Disciplinares', ['educar_index.php' => 'Escola']);
    }

    /**
     * @inheritdoc
     */
    public function form()
    {
        $this->inputsHelper()->dynamic(['ano', 'instituicao', 'escola']);
        $this->inputsHelper()->dynamic('curso', ['required' => false]);
        $this->inputsHelper()->dynamic('serie', ['required' => false]);
        $this->inputsHelper()->dynamic('turma', ['required' => false]);
        $this->inputsHelper()->simpleSearchAluno('aluno', ['required' => false]);
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
        $this->report->addArg('aluno', trim($this->getRequest()->aluno_aluno) == '' ? 0 : (int)$this->getRequest()->aluno_id);
    }

    /**
     * @return StudentDisciplinaryOccurrenceReport
     *
     * @throws Exception
     */
    public function report()
    {
        return new StudentDisciplinaryOccurrenceReport();
    }
}
