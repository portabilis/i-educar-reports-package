<?php

class StudentsPerClassController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var int
     */
    protected $_processoAp = 999101;

    /**
     * @inheritdoc
     */
    protected $_titulo = 'Relação de Alunos por Turma';

    /**
     * @inheritdoc
     */
    protected function _preRender()
    {
        parent::_preRender();

        Portabilis_View_Helper_Application::loadStylesheet($this, 'intranet/styles/localizacaoSistema.css');

        $this->breadcrumb('Relação de alunos por turma', [
            'educar_index.php' => 'Escola'
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
        $this->inputsHelper()->dynamic('turma', ['required' => false]);
        $this->inputsHelper()->dynamic('situacaoMatricula');
        $this->inputsHelper()->checkbox('proerd', ['label' => 'Modelo PROERD?']);
        $this->inputsHelper()->date('data_inicial', ['required' => false, 'label' => 'Data inicial']);
        $this->inputsHelper()->date('data_final', ['required' => false, 'label' => 'Data final']);

        if ($GLOBALS['coreExt']['Config']->app->matricula->dependencia == 1) {
            $this->inputsHelper()->select('dependencia', [
                'label' => 'Alunos com dependência',
                'required' => false,
                'value' => 0,
                'resources' => [
                    0 => 'Todos',
                    1 => 'Somente alunos com dependência',
                    2 => 'Não exibir alunos com dependência'
                ],
            ]);
        } else {
            $this->inputsHelper()->hidden('dependencia', ['value' => 0]);
        }

        $this->loadResourceAssets($this->getDispatcher());
    }

    /**
     * Retorna a classe report.
     *
     * @return StudentsPerClassReport
     *
     * @throws Exception
     */
    public function report()
    {
        return new StudentsPerClassReport();
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
        $this->report->addArg('data_inicial', Portabilis_Date_Utils::brToPgSQL($this->getRequest()->data_inicial));
        $this->report->addArg('data_final', Portabilis_Date_Utils::brToPgSQL($this->getRequest()->data_final));
        $this->report->addArg('proerd', $this->getRequest()->proerd ? 1 : 0);
        $this->report->addArg('dependencia', (int) $this->getRequest()->dependencia);
    }
}
