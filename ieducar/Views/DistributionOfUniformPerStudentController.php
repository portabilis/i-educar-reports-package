<?php

require_once 'Portabilis/String/Utils.php';
require_once 'Portabilis/Date/Utils.php';
require_once 'lib/Portabilis/Controller/ReportCoreController.php';
require_once 'Reports/Reports/DistributionOfUniformPerStudentReport.php';

class DistributionOfUniformPerStudentController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var int
     */
    protected $_processoAp = 999224;

    /**
     * @var string
     */
    protected $_titulo = 'Distribução de uniforme por aluno';

    /**
     * @inheritdoc
     */
    protected function _preRender()
    {
        parent::_preRender();

        Portabilis_View_Helper_Application::loadStylesheet($this, 'intranet/styles/localizacaoSistema.css');

        $this->breadcrumb('Distribução de uniforme por aluno', [
            'educar_index.php' => 'Escola',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function form()
    {
        $this->inputsHelper()->dynamic(['ano', 'instituicao', 'escola', 'curso', 'serie', 'turma']);
        $this->inputsHelper()->dynamic('ano', ['required' => false]);
        $this->inputsHelper()->dynamic('curso', ['required' => false]);
        $this->inputsHelper()->dynamic('serie', ['required' => false]);
        $this->inputsHelper()->dynamic('turma', ['required' => false]);
        $this->inputsHelper()->simpleSearchAluno(null, ['required' => false]);
    }

    /**
     * @inheritdoc
     */
    public function beforeValidation()
    {
        $this->report->addArg('ano', (int) $this->getRequest()->ano);
        $this->report->addArg('instituicao', (int) $this->getRequest()->ref_cod_instituicao);
        $this->report->addArg('escola', (int) $this->getRequest()->ref_cod_escola);
        $this->report->addArg('aluno', (int) $this->getRequest()->aluno);
        $this->report->addArg('curso', (int) $this->getRequest()->ref_cod_curso);
        $this->report->addArg('serie', (int) $this->getRequest()->ref_cod_serie);
        $this->report->addArg('turma', (int) $this->getRequest()->ref_cod_turma);
    }

    /**
     * @return DistributionOfUniformPerStudentReport
     *
     * @throws Exception
     */
    public function report()
    {
        return new DistributionOfUniformPerStudentReport();
    }
}


