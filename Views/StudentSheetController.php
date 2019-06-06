<?php

require_once 'lib/Portabilis/Controller/ReportCoreController.php';
require_once 'Reports/Reports/StudentSheetReport.php';

class StudentSheetController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var int
     */
    protected $_processoAp = 999203;

    /**
     * @var string
     */
    protected $_titulo = 'Ficha do Aluno';

    /**
     * @inheritdoc
     */
    protected function _preRender()
    {
        parent::_preRender();

        Portabilis_View_Helper_Application::loadStylesheet($this, 'intranet/styles/localizacaoSistema.css');

        $this->breadcrumb('Emissão da ficha do aluno', [
            'educar_index.php' => 'Escola',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function form()
    {
        $this->inputsHelper()->dynamic(['ano', 'instituicao', 'escola', 'curso', 'serie', 'turma']);
        $this->inputsHelper()->dynamic('matricula', [], ['options' => ['required' => false]]);
        $this->inputsHelper()->select('modelo', [
            'label' => 'Modelo',
            'resources' => [
                1 => 'Modelo 1',
            ], 'value' => 1
        ]);
    }

    /**
     * @inheritdoc
     */
    public function beforeValidation()
    {
        $this->report->addArg('ano', (int) $this->getRequest()->ano);
        $this->report->addArg('instituicao', (int) $this->getRequest()->ref_cod_instituicao);
        $this->report->addArg('escola', (int) $this->getRequest()->ref_cod_escola);
        $this->report->addArg('modelo', (int) $this->getRequest()->modelo);
        $this->report->addArg('curso', (int) $this->getRequest()->ref_cod_curso);
        $this->report->addArg('serie', (int) $this->getRequest()->ref_cod_serie);
        $this->report->addArg('turma', (int) $this->getRequest()->ref_cod_turma);
        $this->report->addArg('matricula', (int) $this->getRequest()->ref_cod_matricula);
    }

    /**
     * @return StudentSheetReport
     *
     * @throws Exception
     */
    public function report()
    {
        return new StudentSheetReport();
    }
}
