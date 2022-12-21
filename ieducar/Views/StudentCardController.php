<?php

use App\Models\LegacyInstitution;

class StudentCardController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var int
     */
    protected $_processoAp = 999602;

    protected $_titulo = 'Relatório Carteira de Estudante';

    protected function _preRender()
    {
        parent::_preRender();

        $this->breadcrumb('Emissão de carteira de estudante', [
            url('educar_index.php') => 'Escola',
        ]);
    }

    public function form()
    {
        $this->inputsHelper()->dynamic(['ano', 'instituicao', 'escola', 'curso', 'serie', 'turma']);
        $this->inputsHelper()->dynamic('matricula', ['required' => false]);

        $options = [
            'label' => 'Situação da matrícula',
            'resources' => [
                1 => 'Aprovado',
                2 => 'Reprovado',
                14 => 'Reprovado por falta',
                3 => 'Cursando',
                4 => 'Transferido',
                5 => 'Reclassificado',
                6 => 'Abandono',
                9 => 'Exceto Transferidos/Abandono',
                10 => 'Todas',
                12 => 'Aprovado com dependência',
                16 => 'Aprovado após exame'
            ],
            'required' => false,
            'value' => 9
        ];
        $this->inputsHelper()->select('situacao_matricula', $options);

        if (config('legacy.report.mostrar_relatorios') == 'botucatu') {
            $this->inputsHelper()->hidden('modelo', ['value' => 3]);
        } else {
            $resources = [
                1 => 'Modelo 1',
                2 => 'Modelo 2',
                3 => 'Modelo 3'
            ];

            $options = ['label' => 'Modelo', 'resources' => $resources, 'value' => 1];
            $this->inputsHelper()->select('modelo', $options);
        }

        $this->inputsHelper()->text('validade', [
            'required' => false,
            'label' => 'Validade',
            'size' => 45,
            'max_length' => 7,
            'placeholder' => 'Informe uma data (mes/ano) (ex.: 01/2015)'
        ]);

        $this->inputsHelper()->checkbox('imprimir_serie', ['label' => 'Imprimir nome da série ao lado da turma?']);
        $colors = [
            1 => 'Amarelo',
            2 => 'Azul',
            3 => 'Laranja',
            4 => 'Roxo',
            5 => 'Verde',
            6 => 'Vermelho'
        ];
        $options = [
            'label' => 'Cor de fundo',
            'resources' => $colors,
            'value' => 1
        ];
        $this->inputsHelper()->select('cor_de_fundo', $options);

        $this->loadResourceAssets($this->getDispatcher());
    }

    public function report()
    {
        return new StudentCardReport();
    }

    public function beforeValidation()
    {
        $this->report->addArg('ano', (int) $this->getRequest()->ano);
        $this->report->addArg('instituicao', (int) $this->getRequest()->ref_cod_instituicao);
        $this->report->addArg('escola', (int) $this->getRequest()->ref_cod_escola);
        $this->report->addArg('curso', (int) $this->getRequest()->ref_cod_curso);
        $this->report->addArg('serie', (int) $this->getRequest()->ref_cod_serie);
        $this->report->addArg('turma', (int) $this->getRequest()->ref_cod_turma);
        $this->report->addArg('validade', $this->getRequest()->validade);
        $this->report->addArg('cor_de_fundo', (int) $this->getRequest()->cor_de_fundo);

        $configPath = config('legacy.report.caminho_fundo_carteira_transporte');
        $path = empty($configPath) ? '/var/www/ieducar/ieducar/modules/Reports/Assets/Images/StudentCard' : $configPath;

        $this->report->addArg('caminho_fundo_carteira_transporte', $path);
        if (!isset($_POST['ref_cod_matricula'])) {
            $this->report->addArg('matricula', 0);
        } else {
            $this->report->addArg('matricula', (int) $this->getRequest()->ref_cod_matricula);
        }
        $this->report->addArg('situacao_matricula', $this->getRequest()->situacao_matricula);

        $this->report->addArg('modelo', (int) $this->getRequest()->modelo);

        if (((int) $this->getRequest()->modelo) == 1 || ((int) $this->getRequest()->modelo == 3)) {
            $this->report->addArg('leiestudante', config('legacy.report.lei_estudante'));
            $this->report->addArg('diretorioimg', config('legacy.app.database.dbname'));
            switch (config('legacy.report.carteira_estudante.codigo')) {
                case 'codigo_inep':
                    $this->report->addArg('codigo', 1);
                    break;
                case 'codigo_aluno':
                    $this->report->addArg('codigo', 2);
                    break;
                case 'codigo_estado':
                    $this->report->addArg('codigo', 3);
                    break;
            }
        }
        if ((int) $this->getRequest()->modelo == 1) {
            $this->report->addArg('imprimir_serie', $this->getRequest()->imprimir_serie ? 1 : 0);
        }
    }
}
