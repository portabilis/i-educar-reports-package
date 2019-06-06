<?php

require_once 'lib/Portabilis/Controller/ReportCoreController.php';
require_once 'Reports/Reports/ClassRecordBookReport.php';

class ClassRecordBookController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var int
     */
    protected $_processoAp = 999816;

    protected $_titulo = 'Diário de classe';

    protected function _preRender()
    {
        parent::_preRender();
        $this->breadcrumb('Diário de classe',['/intranet/educar_index.php' => 'Escola']);
    }

    public function form()
    {
        $clsInstituicao = new clsPmieducarInstituicao();
        $instituicao = $clsInstituicao->primeiraAtiva();
        $exibirApenasProfessoresAlocados = dbBool($instituicao['exibir_apenas_professores_alocados']);
        $this->campoOculto('exibir_apenas_professores_alocados', $exibirApenasProfessoresAlocados);

        $this->inputsHelper()->dynamic(['ano', 'instituicao', 'escola', 'curso', 'serie', 'turma']);

        $this->inputsHelper()->dynamic(['etapa'], ['required' => false]);
        $opcoes = [
            1 => 'Anos iniciais',
            2 => 'Anos finais',
            3 => 'Educação infantil'
        ];
        $this->campoLista('modelo', 'Modelo', $opcoes, 10);
        $opcoes = [
            1 => 'Aprovado',
            2 => 'Reprovado',
            3 => 'Cursando',
            4 => 'Transferido',
            5 => 'Reclassificado',
            6 => 'Abandono',
            9 => 'Exceto Transferidos/Abandono',
            10 => 'Todas'
        ];
        $this->campoLista('situacao', 'Situação', $opcoes, 10);
        
        if ($exibirApenasProfessoresAlocados) {
            $this->inputsHelper()->checkbox('buscar_professor', ['label' => 'Buscar professor alocado?']);
            $this->inputsHelper()->simpleSearchServidor(null, ['required' => false, 'label' => 'Professor(a): ', 'size' => 30 ]);
        } else {
            $this->inputsHelper()->checkbox('buscar_professor', ['label' => 'Buscar professor alocado?']);
            $options = ['label' => 'Professor(a):', 'required' => false, 'size' => 30];
            $this->inputsHelper()->text('professor', $options);
            $this->inputsHelper()->simpleSearchServidor(null, ['required' => false, 'label' => 'Professor(a): ', 'size' => 30 ]);
        }

        $this->inputsHelper()->checkbox('buscar_disciplina', ['label' => 'Buscar disciplina?']);
        $options = ['label' => 'Disciplina:', 'required' => false, 'size' => 30];
        $this->inputsHelper()->text('disciplina', $options);
        $this->inputsHelper()->dynamic(['componenteCurricular'], ['required' => false, 'label' => 'Disciplina: ']);
        $this->campoNumero('linha', 'Linhas em branco', 0, 2, 2, true);
        $this->loadResourceAssets($this->getDispatcher());
    }

    public function report()
    {
        return new ClassRecordBookReport();
    }

    public function beforeValidation()
    {
        $this->report->addArg('ano', (int)$this->getRequest()->ano);
        $this->report->addArg('instituicao', (int)$this->getRequest()->ref_cod_instituicao);
        $this->report->addArg('escola', (int)$this->getRequest()->ref_cod_escola);
        $this->report->addArg('curso', (int)$this->getRequest()->ref_cod_curso);
        $this->report->addArg('serie', (int)$this->getRequest()->ref_cod_serie);
        $this->report->addArg('turma', (int)$this->getRequest()->ref_cod_turma);
        $this->report->addArg('situacao', (int)$this->getRequest()->situacao);
        $this->report->addArg('modelo', (int)$this->getRequest()->modelo);
        $this->report->addArg('servidor_id', (int)$this->getRequest()->servidor_id);
        $this->report->addArg('ref_cod_componente_curricular', (int)$this->getRequest()->ref_cod_componente_curricular);
        $this->report->addArg('etapa', (int)$this->getRequest()->etapa);
        $linha = !isset($this->getRequest()->linha);
        if ($linha) {
            $this->report->addArg('linha', 0);
        } else {
            $this->report->addArg('linha', (int)$this->getRequest()->linha);
        }
        $this->report->addArg('professor', (string)$this->getRequest()->professor);
        $this->report->addArg('buscar_professor', (bool)$this->getRequest()->buscar_professor);
        $this->report->addArg('disciplina', (string)$this->getRequest()->disciplina);
        $this->report->addArg('buscar_disciplina', (bool)$this->getRequest()->buscar_disciplina);
    }
}
