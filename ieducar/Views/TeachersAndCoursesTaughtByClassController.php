<?php

require_once 'lib/Portabilis/Controller/ReportCoreController.php';
require_once 'Reports/Reports/TeachersAndCoursesTaughtByClassReport.php';

class TeachersAndCoursesTaughtByClassController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var int
     */
    protected $_processoAp = 999860;

    /**
     * @var string
     */
    protected $_titulo = 'Relatório de docentes e disciplinas lecionadas por turma';

    /**
     * @inheritdoc
     */
    protected function _preRender()
    {
        parent::_preRender();

        Portabilis_View_Helper_Application::loadStylesheet($this, 'intranet/styles/localizacaoSistema.css');

        $this->breadcrumb('Relatório de docentes e disciplinas lecionadas por turma', [
            'educar_servidores_index.php' => 'Servidores',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function form()
    {
        $this->inputsHelper()->dynamic(['ano', 'instituicao']);
        $this->inputsHelper()->dynamic('escola');
        $this->inputsHelper()->dynamic('curso');
        $this->inputsHelper()->dynamic('serie');
        $this->inputsHelper()->dynamic('turma', ['required' => false]);
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
    }

    /**
     * @return TeachersAndCoursesTaughtByClassReport
     *
     * @throws Exception
     */
    public function report()
    {
        return new TeachersAndCoursesTaughtByClassReport();
    }
}
