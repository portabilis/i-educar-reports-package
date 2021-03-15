<?php

require_once 'lib/Portabilis/Controller/ReportCoreController.php';
require_once 'Reports/Reports/StudentsAverageReport.php';
require_once 'Portabilis/String/Utils.php';

class StudentsAverageController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var int
     */
    protected $_processoAp = 999834;

    /**
     * @var string
     */
    protected $_titulo = 'Relatório da relação de alunos com o melhor desempenho';

    /**
     * @inheritdoc
     */
    protected function _preRender()
    {
        parent::_preRender();

        Portabilis_View_Helper_Application::loadStylesheet($this, 'intranet/styles/localizacaoSistema.css');

        $this->breadcrumb('Emissão da relação de alunos com o melhor desempenho', [
            'educar_index.php' => 'Escola',
        ]);
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
        $this->inputsHelper()->dynamic('etapa', ['required' => false]);
        $this->inputsHelper()->text('limite', ['required' => false, 'label' => 'Limite de posições', 'size' => 5, 'max_length' => 7, 'placeholder' => ' ']);
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

        if ($this->getRequest()->limite == 0 || (is_null($this->getRequest()->limite)) || (is_numeric(!$this->getRequest()->limite))) {
            $this->getRequest()->limite == 1000000;
        } else {
            if (is_null($this->getRequest()->limite)) {
                $this->report->addArg('limite', 10000);
            } else {
                $this->report->addArg('limite', (int) $this->getRequest()->limite);
            }
        }
    }

    /**
     * @return StudentsAverageReport
     *
     * @throws Exception
     */
    public function report()
    {
        return new StudentsAverageReport();
    }
}
