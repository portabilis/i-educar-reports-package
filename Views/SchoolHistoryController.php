<?php

require_once 'lib/Portabilis/Controller/ReportCoreController.php';
require_once 'Reports/Reports/SchoolHistoryReport.php';

class SchoolHistoryController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var int
     */
    protected $_processoAp = 999200;

    /**
     * @var string
     */
    protected $_titulo = 'Histórico Escolar';

    /**
     * @inheritdoc
     */
    protected function _preRender()
    {
        parent::_preRender();

        Portabilis_View_Helper_Application::loadStylesheet($this, 'intranet/styles/localizacaoSistema.css');

        $this->breadcrumb('Emissão do histórico escolar', [
            'educar_index.php' => 'Escola',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function form()
    {
        $this->inputsHelper()->dynamic(['instituicao', 'escola', 'curso', 'serie', 'turma', 'ano']);
        $this->inputsHelper()->simpleSearchAluno(null);

        $modelo_visivel = true;
        $mostrar_imprimir_nome_secretario_diretor = true;
        $mostrar_nao_emitir_aprovacao_pontos = true;
        $mostrar_relatorios = $GLOBALS['coreExt']['Config']->report->mostrar_relatorios;

        $this->inputsHelper()->checkbox('lote', ['label' => 'Emitir em lote?']);

        $resources = [
            3 => 'Série/Anos',
        ];

        $this->inputsHelper()->checkbox('emitir_historico_dependencia', [
            'label' => 'Emitir histórico de dependências?'
        ]);

        if ($mostrar_imprimir_nome_secretario_diretor) {
            $this->inputsHelper()->checkbox('imprime_diretor_secretario', [
                'label' => 'Imprimir o nome do(a) secretário(a) e diretor(a)?'
            ]);
        }

        $this->inputsHelper()->checkbox('apenas_ultimo_registro', [
            'label' => 'Emitir informações do ano letivo apenas do último registro?'
        ]);

        $options = ['label' => 'Diretor(a):', 'size' => 50, 'max_length' => 100, 'required' => false, 'placeholder' => ''];
        $this->inputsHelper()->text('nm_diretor', $options);

        $options = ['label' => 'Secretário(a):', 'size' => 50, 'max_length' => 100, 'required' => false, 'placeholder' => ''];
        $this->inputsHelper()->text('nm_secretario', $options);

        if ($mostrar_nao_emitir_aprovacao_pontos) {
            $mensagemAprovacaoPontos = 'Se esta opção for selecionada, a informação (' . $GLOBALS['coreExt']['Config']->report->portaria_aprovacao_pontos . ') não será apresentada';

            $this->inputsHelper()->checkbox('mostrar_msg', ['label' => 'Não emitir aprovação de pontos?', $mensagemAprovacaoPontos]);
        }

        $this->inputsHelper()->checkbox('emitir_carga_horaria_frequentada', ['label' => 'Emitir Carga horária frequentada']);

        if ($modelo_visivel) {
            $options = ['label' => 'Modelo', 'resources' => $resources, 'value' => 1];
            $this->inputsHelper()->select('modelo', $options);
        }

        $this->inputsHelper()->integer('ano_ini', ['placeholder' => '', 'required' => false, 'label' => 'Ano início', 'max_length' => 4, 'size' => 4]);
        $this->inputsHelper()->integer('ano_fim', ['placeholder' => '', 'required' => false, 'label' => 'Ano final', 'max_length' => 4, 'size' => 4]);

        $helperOptions = ['objectName' => 'cursoaluno'];
        $options = ['label' => 'Cursos do aluno', 'size' => 120, 'required' => false, 'placeholder' => Todas, 'options' => ['value' => null]];
        $this->inputsHelper()->multipleSearchCursoAluno('', $options, $helperOptions);

        $this->campoOculto('sequencial', $this->sequencial);

        $this->loadResourceAssets($this->getDispatcher());
    }

    /**
     * @inheritdoc
     */
    public function beforeValidation()
    {
        $this->report->addArg('instituicao', (int) $this->getRequest()->ref_cod_instituicao);
        $this->report->addArg('escola', (int) $this->getRequest()->ref_cod_escola);
        $this->report->addArg('aluno', (int) $this->getRequest()->aluno_id);
        $this->report->addArg('modelo', (int) $this->getRequest()->modelo);
        $this->report->addArg('ano_ini', ($this->getRequest()->ano_ini == '' ? 0 : (int) $this->getRequest()->ano_ini));
        $this->report->addArg('ano_fim', ($this->getRequest()->ano_fim == '' ? 0 : (int) $this->getRequest()->ano_fim));
        $this->report->addArg('apenas_ultimo_registro', (bool) $this->getRequest()->apenas_ultimo_registro);
        $this->report->addArg('cabecalho_alternativo', (int) $GLOBALS['coreExt']['Config']->report->header->alternativo);
        $this->report->addArg('portaria_aprovacao_pontos', (string) $GLOBALS['coreExt']['Config']->report->portaria_aprovacao_pontos);
        $this->report->addArg('boletim_transferencia', (bool) $this->getRequest()->boletim_transferencia);
        $this->report->addArg('nao_emitir_reprovado', (bool) $this->getRequest()->nao_emitir_reprovado);
        $this->report->addArg('mostrar_msg', (bool) $this->getRequest()->mostrar_msg);
        $this->report->addArg('nm_diretor', $this->getRequest()->nm_diretor);
        $this->report->addArg('nm_secretario', $this->getRequest()->nm_secretario);
        $this->report->addArg('curso', (int) $this->getRequest()->ref_cod_curso);
        $this->report->addArg('serie', (int) $this->getRequest()->ref_cod_serie);
        $this->report->addArg('turma', (int) $this->getRequest()->ref_cod_turma);
        $this->report->addArg('ano', (int) $this->getRequest()->ano);
        $this->report->addArg('emitir_nome_diretor', (bool) $this->getRequest()->emitir_nome_diretor);
        $this->report->addArg('diretor', (string) $this->getRequest()->gestor);
        $this->report->addArg('emitir_secretario_escolar', (bool) $this->getRequest()->emitir_secretario_escolar);
        $this->report->addArg('secretario', (string) $this->getRequest()->secretario);
        $this->report->addArg('emitir_historico_dependencia', (bool) $this->getRequest()->emitir_historico_dependencia);
        $this->report->addArg('emitir_assinatura_secretario_diretor', (bool) $this->getRequest()->emitir_assinatura_secretario_diretor);
        $this->report->addArg('emitir_carga_horaria_frequentada', (bool) $this->getRequest()->emitir_carga_horaria_frequentada);

        $cursoaluno = implode(',', array_filter($this->getRequest()->cursoaluno));

        $this->report->addArg('cursoaluno', trim($cursoaluno) == '' ? 0 : $cursoaluno);
        $this->report->addArg('sequencial', (int) $this->getRequest()->sequencial);
    }

    /**
     * @return SchoolHistoryReport
     *
     * @throws Exception
     */
    public function report()
    {
        return new SchoolHistoryReport();
    }
}
