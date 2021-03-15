<?php

require_once 'lib/Portabilis/Controller/ReportCoreController.php';
require_once 'Reports/Reports/TeacherReportCardReport.php';

class TeacherReportCardController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var int
     */
    protected $_processoAp = 999205;

    /**
     * @var string
     */
    protected $_titulo = 'Boletim do Professor';

    /**
     * @inheritdoc
     */
    protected function _preRender()
    {
        parent::_preRender();

        Portabilis_View_Helper_Application::loadStylesheet($this, 'intranet/styles/localizacaoSistema.css');

        $this->breadcrumb('Boletim do professor', [
            'educar_index.php' => 'Escola'
        ]);
    }

    /**
     * @inheritdoc
     */
    public function form()
    {
        $this->inputsHelper()->dynamic(['ano', 'instituicao', 'escola', 'curso', 'serie']);
        $this->inputsHelper()->dynamic('turma', ['required' => false]);
        $this->inputsHelper()->dynamic('componenteCurricular', ['required' => false]);
        $options = [
            'label' => 'Situação',
            'resources' => [
                0 => 'Todos',
                1 => 'Aprovado',
                2 => 'Reprovado',
                3 => 'Cursando',
                4 => 'Transferido',
                5 => 'Reclassificado',
                6 => 'Abandono',
                7 => 'Em exame',
                8 => 'Aprovado após exame',
                10 => 'Aprovado sem exame'
            ],
            'required' => false,
            'value' => 0
        ];
        $this->inputsHelper()->select('situacao', $options);

        $options = [
            'label' => 'Orientação da página',
            'resources' => [1 => 'Paisagem', 2 => 'Retrato'],
            'required' => false,
            'value' => 1
        ];
        $this->inputsHelper()->select('orientacao', $options);
        $this->inputsHelper()->checkbox('buscar_professor', ['label' => 'Buscar professor alocado?']);
        $options = [
            'label' => 'Professor(a):',
            'required' => false,
            'size' => 30
        ];
        $this->inputsHelper()->text('professor', $options);
        $this->inputsHelper()->simpleSearchServidor(null, ['required' => false, 'label' => 'Professor(a): ', 'size' => 30]);
        $this->campoNumero('linha', 'Linhas em branco', 0, 2, 2, true);
        $this->inputsHelper()->checkbox('emitir_assinaturas', ['label' => 'Emitir assinaturas no rodapé?']);
        $this->inputsHelper()->checkbox('data_manual', ['label' => 'Data manual?']);
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
        $this->report->addArg('disciplina', (int) $this->getRequest()->ref_cod_componente_curricular);
        $this->report->addArg('situacao', (int) $this->getRequest()->situacao);
        $this->report->addArg('professor', $this->getRequest()->professor);
        $this->report->addArg('servidor', $this->getRequest()->servidor);
        $this->report->addArg('linha', ((int) $this->getRequest()->ref_cod_componente_curricular == 0?0:(int) $this->getRequest()->linha));
        $this->report->addArg('orientacao', (int) $this->getRequest()->orientacao);
        $this->report->addArg('emitir_assinaturas', (bool) $this->getRequest()->emitir_assinaturas);
        $this->report->addArg('data_manual', (bool) $this->getRequest()->data_manual);
    }

    /**
     * @return TeacherReportCardReport
     *
     * @throws Exception
     */
    public function report()
    {
        return new TeacherReportCardReport();
    }
}
