<?php

use App\Models\LegacyInstitution;

class EnrollmentQuantitativeMapController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var int
     */
    protected $_processoAp = 999218;

    protected $_titulo = 'Mapa quantitativo de matrículas enturmadas';

    protected function _preRender()
    {
        parent::_preRender();

        $this->breadcrumb('Relatório Mapa quantitativo de matrículas enturmadas', [
            url('educar_index.php') => 'Escola',
        ]);
    }

    public function form()
    {
        $this->inputsHelper()->dynamic(['ano', 'instituicao']);
        $this->inputsHelper()->dynamic('EscolaObrigatorioParaNivelEscolar', ['required' => false]);

        $opcoes = ['A' => 'Ambos', 'M' => 'Masculino', 'F' => 'Feminino'];

        $this->campoLista('sexo', 'Sexo', $opcoes, 1);
        $this->inputsHelper()->dynamic('situacaoMatricula');

        $resources = [
            1 => 'Detalhado',
            2 => 'Por escola',
            3 => 'Por curso',
            4 => 'Tabela Escola x Série',
            5 => 'Tabela Escola x Série x Turno'
        ];

        $options = ['label' => 'Modelo', 'resources' => $resources, 'value' => 1];

        $this->inputsHelper()->select('modelo', $options);
        $this->inputsHelper()->date('data_ini', ['label' => 'Data início', 'required' => false]);
        $this->inputsHelper()->date('data_fim', ['label' => 'Data fim', 'required' => false]);
        $this->inputsHelper()->dynamic('curso', ['required' => false]);
        $this->inputsHelper()->multipleSearchCurso('', ['label' => 'Cursos', 'required' => false]);
        $this->inputsHelper()->checkbox('exibir_quantidade_salas', ['label' => 'Exibir colunas com número de turmas?']);

        $options = [
            'value' => 'turno',
            'resources' => [
                0 => 'Selecione',
                1 => 'Matutino',
                2 => 'Vespertino',
                3 => 'Noturno',
                4 => 'Integral'
            ], 'required' => false
        ];

        $this->inputsHelper()->select('turno', $options);

        $permiteMatriculasDeDependencia = config('legacy.app.matricula.dependencia') == 1;

        if ($permiteMatriculasDeDependencia) {
            $options = [
                'label' => 'Alunos com dependência',
                'resources' => [
                    0 => 'Todos',
                    1 => 'Somente alunos com dependência',
                    2 => 'Não exibir alunos com dependência'
                ],
                'required' => false,
                'value' => 0,
            ];
            $this->inputsHelper()->select('dependencia', $options);
        } else {
            $this->inputsHelper()->hidden('dependencia', ['value' => 2]);
        }

        $this->loadResourceAssets($this->getDispatcher());
    }

    public function report()
    {
        return new EnrollmentQuantitativeMapReport();
    }

    public function beforeValidation()
    {
        $cursos = implode(',', $this->getRequest()->curso ?: []);
        $this->report->addArg('ano', (int) $this->getRequest()->ano);
        $this->report->addArg('instituicao', (int) $this->getRequest()->ref_cod_instituicao);
        $this->report->addArg('escola', ($this->getRequest()->ref_cod_escola == '' ? 0 : (int) $this->getRequest()->ref_cod_escola));
        $this->report->addArg('curso', ($this->getRequest()->ref_cod_curso == '' ? 0 : (int) $this->getRequest()->ref_cod_curso));
        $this->report->addArg('cursos', $cursos ?: '0');
        $this->report->addArg('sexo', $this->getRequest()->sexo);
        $this->report->addArg('situacao', (int) $this->getRequest()->situacao_matricula_id);
        $this->report->addArg('modelo', (int) $this->getRequest()->modelo);
        $this->report->addArg('data_ini', ($this->getRequest()->data_ini == '' ? '' : Portabilis_Date_Utils::brToPgSQL($this->getRequest()->data_ini)));
        $this->report->addArg('data_fim', ($this->getRequest()->data_fim == '' ? '' : Portabilis_Date_Utils::brToPgSQL($this->getRequest()->data_fim)));
        $this->report->addArg('exibir_quantidade_salas', (bool) $this->getRequest()->exibir_quantidade_salas);
        $this->report->addArg('turno', (int) $this->getRequest()->turno);
        $this->report->addArg('dependencia', (int) $this->getRequest()->dependencia);
    }
}
