<?php

use App\Menu;

class FrequencyCertificateController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var int
     */
    protected $_processoAp = 999102;

    /**
     * @var string
     */
    protected $_titulo = 'Atestado de Frequência';

    /**
     * @inheritdoc
     */
    protected function _preRender()
    {
        parent::_preRender();

        Portabilis_View_Helper_Application::loadStylesheet($this, 'intranet/styles/localizacaoSistema.css');

        $this->breadcrumb($this->titulo(), [
            'educar_index.php' => 'Escola',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function form()
    {
        $this->inputsHelper()->dynamic(['ano', 'instituicao']);
        $this->inputsHelper()->dynamic(['escola'], ['required' => false]);
        $this->inputsHelper()->dynamic(['curso', 'serie', 'turma']);
        $this->inputsHelper()->select('modelo', ['label' => 'Modelo', 'resources' => [1 => 'Modelo 1'], 'value' => 1]);
        $this->inputsHelper()->simpleSearchMatricula(null, ['required' => false]);
        $this->campoMemo('observacao', 'Observação', $this->observacao, 48, 5, false);
        $this->inputsHelper()->checkbox('emitir_frequencia', ['label' => 'Emitir a percentagem de frequência do aluno']);
        $this->inputsHelper()->checkbox('emitir_nome_diretor', ['label' => 'Emitir assinatura do gestor escolar']);
        $this->inputsHelper()->checkbox('emitir_secretario_escolar', ['label' => 'Emitir assinatura do secretário escolar']);
        $this->inputsHelper()->checkbox('lote', ['label' => 'Emitir em lote?']);
        $this->inputsHelper()->checkbox('emitir_validade', ['label' => 'Emitir a data de validade do documento?']);
        $this->inputsHelper()->text('campo_validade', ['required' => false, 'label' => 'Validade', 'size' => 45, 'max_length' => 3, 'placeholder' => 'Informe o número de dias para a validade']);
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
        $this->report->addArg('matricula', (int) $this->getRequest()->matricula_id);
        $this->report->addArg('observacao', $this->getRequest()->observacao);
        $this->report->addArg('modelo', $this->getRequest()->modelo);
        $this->report->addArg('emitir_frequencia', (bool) $this->getRequest()->emitir_frequencia);
        $this->report->addArg('cabecalho_alternativo', (int) $GLOBALS['coreExt']['Config']->report->header->alternativo);
        $this->report->addArg('emitir_nome_diretor', (bool) $this->getRequest()->emitir_nome_diretor);
        $this->report->addArg('emitir_secretario_escolar', (bool) $this->getRequest()->emitir_secretario_escolar);
        $this->report->addArg('lote', (bool) $this->getRequest()->lote);
        $this->report->addArg('emitir_validade', (bool) $this->getRequest()->emitir_validade);
        $this->report->addArg('campo_validade', (string) $this->getRequest()->campo_validade);
        $this->report->addArg('titulo', (string) $this->titulo());

        if ((bool) $this->getRequest()->lote) {
            $this->report->addArg('curso', (int) $this->getRequest()->ref_cod_curso);
            $this->report->addArg('serie', (int) $this->getRequest()->ref_cod_serie);
            $this->report->addArg('turma', (int) $this->getRequest()->ref_cod_turma);
        }
    }

    /**
     * @return FrequencyCertificateReport
     *
     * @throws Exception
     */
    public function report()
    {
        return new FrequencyCertificateReport();
    }

    /**
     * @return string
     */
    public function titulo()
    {
        $menu = Menu::query()->where('process', $this->_processoAp)->first();

        return $menu->title;
    }
}
