<?php

use App\Models\LegacyInstitution;

class ClassBoardMapController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var int
     */
    protected $_processoAp = 999609;

    protected $_titulo = 'Relatório Mapa do Conselho de Classe';

    protected function _preRender()
    {
        parent::_preRender();

        $this->breadcrumb('Emissão do mapa do conselho de classe', [
            url('educar_index.php') => 'Escola',
        ]);
    }

    public function form()
    {
        $this->inputsHelper()->dynamic(['ano', 'instituicao', 'escola', 'curso', 'serie', 'turma']);
        $this->inputsHelper()->dynamic(['etapa'], ['required' => false]);

        if (config('legacy.app.matricula.dependencia') == 1) {
            $options = ['label' => 'Alunos com dependência',
                'resources' => [0 => 'Todos',
                    1 => 'Somente alunos com dependência',
                    2 => 'Não exibir alunos com dependência'],
                'required' => false,
                'value' => 0];
            $this->inputsHelper()->select('dependencia', $options);
        } else {
            $this->inputsHelper()->hidden('dependencia', ['value' => 0]);
        }

        $this->inputsHelper()->dynamic('situacaoMatricula');

        $options = ['label' => 'Orientação',
            'resources' => ['paisagem' => 'Paisagem',
                'retrato' => 'Retrato'],
            'required' => false,
            'value' => 1];

        $this->inputsHelper()->select('orientacao', $options);

        $this->inputsHelper()->checkbox('emitir_assinaturas', ['label' => 'Emitir assinaturas abaixo do mapa?']);

        //Carrega javascript
        $this->loadResourceAssets($this->getDispatcher());
    }

    public function report()
    {
        return new ClassBoardMapReport();
    }

    public function beforeValidation()
    {
        $institution = app(LegacyInstitution::class);
        $this->report->addArg('order_sequential', (int) $institution->ordenar_alunos_sequencial_enturmacao ?: 0);
        $this->report->addArg('ano', (int) $this->getRequest()->ano);
        $this->report->addArg('instituicao', (int) $this->getRequest()->ref_cod_instituicao);
        $this->report->addArg('escola', (int) $this->getRequest()->ref_cod_escola);
        $this->report->addArg('curso', (int) $this->getRequest()->ref_cod_curso);
        $this->report->addArg('serie', (int) $this->getRequest()->ref_cod_serie);
        $this->report->addArg('turma', (int) $this->getRequest()->ref_cod_turma);
        $this->report->addArg('etapa', (int) $this->getRequest()->etapa);
        $this->report->addArg('dependencia', (int) $this->getRequest()->dependencia);
        $this->report->addArg('emitir_assinaturas', (bool) $this->getRequest()->emitir_assinaturas);
        $this->report->addArg('situacao', (int) $this->getRequest()->situacao_matricula_id);
        $this->report->addArg('orientacao', (string) $this->getRequest()->orientacao);
    }
}
