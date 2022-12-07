<?php

class PendingStudentsController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var int
     */
    protected $_processoAp = 999883;

    protected $_titulo = 'Relatório quantitativo de alunos sem notas';

    protected function _preRender()
    {
        parent::_preRender();
        $this->breadcrumb('Relatório quantitativo de alunos sem notas', [
            'educar_index.php' => 'Escola',
        ]);
    }

    public function form()
    {
        $this->inputsHelper()->dynamic(['ano', 'instituicao']);
        $this->inputsHelper()->dynamic(['EscolaObrigatorioParaNivelEscolar', 'curso', 'serie', 'turma'], ['required' => false]);
        $this->inputsHelper()->text('etapa', ['label' => 'Etapas', 'required' => false, 'size' => 32, 'placeholder' => 'Ex: 1, 2, 3']);
    }

    public function report()
    {
        return new PendingStudentsReport();
    }

    public function beforeValidation()
    {
        $etapa = (string) $this->getRequest()->etapa == '' ? 0 : trim($this->getRequest()->etapa);
        if (is_string($etapa)) {
            $etapa = array_filter(explode(',', $etapa),'ctype_digit');
            $etapa = implode(',', $etapa);
        }

        if(empty($etapa)) {
            $etapa = 0;
        }

        $this->report->addArg('ano', (int) $this->getRequest()->ano);
        $this->report->addArg('instituicao', (int) $this->getRequest()->ref_cod_instituicao);
        $this->report->addArg('escola', (int) $this->getRequest()->ref_cod_escola);
        $this->report->addArg('curso', (int) $this->getRequest()->ref_cod_curso);
        $this->report->addArg('serie', (int) $this->getRequest()->ref_cod_serie);
        $this->report->addArg('turma', (int) $this->getRequest()->ref_cod_turma);
        $this->report->addArg('etapa', $etapa);
    }
}
