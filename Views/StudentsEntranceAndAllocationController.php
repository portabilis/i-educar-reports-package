<?php

require_once 'lib/Portabilis/Controller/ReportCoreController.php';
require_once 'Reports/Reports/StudentsEntranceAndAllocationReport.php';
require_once 'Portabilis/Date/Utils.php';

class StudentsEntranceAndAllocationController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var int
     */
    protected $_processoAp = 999871;

    /**
     * @var string
     */
    protected $_titulo = 'Relação de alunos por data de entrada e enturmação';

    /**
     * @inheritdoc
     */
    protected function _preRender()
    {
        parent::_preRender();

        Portabilis_View_Helper_Application::loadStylesheet($this, 'intranet/styles/localizacaoSistema.css');

        $this->breadcrumb('Relação de alunos por data de entrada e enturmação', [
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
        $this->inputsHelper()->dynamic('escola', ['required' => false]);
        $this->inputsHelper()->dynamic('curso', ['required' => false]);
        $this->inputsHelper()->dynamic('serie', ['required' => false]);
        $this->inputsHelper()->dynamic('turma', ['required' => false]);
        $this->inputsHelper()->dynamic('situacaoMatricula', ['required' => false]);
        $this->inputsHelper()->date('data_inicial', ['required' => false, 'label' => 'Data inicial']);
        $this->inputsHelper()->date('data_final', ['required' => false, 'label' => 'Data final']);
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
        $this->report->addArg('situacao', (int) $this->getRequest()->situacao_matricula_id);

        if ($this->getRequest()->data_inicial) {
            $this->report->addArg('data_inicial', Portabilis_Date_Utils::brToPgSQL($this->getRequest()->data_inicial) . ' 00:00:00');
        } else {
            $this->report->addArg('data_inicial', Portabilis_Date_Utils::brToPgSQL($this->getRequest()->data_inicial));
        }

        if ($this->getRequest()->data_final) {
            $this->report->addArg('data_final', Portabilis_Date_Utils::brToPgSQL($this->getRequest()->data_final) . ' 23:59:59');
        } else {
            $this->report->addArg('data_final', Portabilis_Date_Utils::brToPgSQL($this->getRequest()->data_final));
        }
    }

    /**
     * @return StudentsEntranceAndAllocationReport
     *
     * @throws Exception
     */
    public function report()
    {
        return new StudentsEntranceAndAllocationReport();
    }
}
