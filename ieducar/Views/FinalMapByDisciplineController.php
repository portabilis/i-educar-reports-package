<?php

use App\Models\LegacyInstitution;

class FinalMapByDisciplineController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var int
     */
    protected $_processoAp = 999886;

    protected $_titulo = 'Mapa final por disciplina';

    protected function _preRender()
    {
        parent::_preRender();
        $this->breadcrumb('Mapa final por disciplina', [
            'educar_index.php' => 'Escola',
        ]);
    }

    public function form()
    {
        $this->inputsHelper()->dynamic([
            'ano',
            'instituicao',
            'escola',
            'curso',
            'serie',
            'turma'
        ]);
        $this->inputsHelper()->dynamic('componenteCurricular', ['required' => false]);
        $this->inputsHelper()->dynamic('situacaoMatricula');
        $modelos = [
            2 => 'Modelo dinâmico',
            1 => 'Modelo trimestral - Recuperação específica',
        ];
        $this->inputsHelper()->select('modelo', ['resources' => $modelos]);
    }

    public function report()
    {
        return new FinalMapByDisciplineReport();
    }

    public function beforeValidation()
    {
        $institution = app(LegacyInstitution::class);
        $mostrar_relatorios = config('legacy.report.mostrar_relatorios');
        $this->report->addArg('order_sequential', (int) $institution->ordenar_alunos_sequencial_enturmacao ?: 0);
        $this->report->addArg('ano', (int)$this->getRequest()->ano);
        $this->report->addArg('instituicao', (int)$this->getRequest()->ref_cod_instituicao);
        $this->report->addArg('escola', (int)$this->getRequest()->ref_cod_escola);
        $this->report->addArg('curso', (int)$this->getRequest()->ref_cod_curso);
        $this->report->addArg('serie', (int)$this->getRequest()->ref_cod_serie);
        $this->report->addArg('turma', (int)$this->getRequest()->ref_cod_turma);
        $this->report->addArg('disciplina', (int)$this->getRequest()->ref_cod_componente_curricular);
        $this->report->addArg('situacao', (int)$this->getRequest()->situacao_matricula_id);
        $this->report->addArg('modelo', (int)$this->getRequest()->modelo);
        $this->report->addArg('mostrar_relatorios', $mostrar_relatorios);
    }
}
