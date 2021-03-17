<?php

use App\Menu;

class VacancyCertificateController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var int
     */
    protected $_processoAp = 999100;

    /**
     * @var string
     */
    protected $_titulo = 'Relatório Atestado de Vaga';

    /**
     * @return string
     */
    public function titulo()
    {
        $menu = Menu::query()->where('process', $this->_processoAp)->first();

        return $menu->title;
    }

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
        $this->inputsHelper()->checkbox('branco', ['label' => 'Emitir em branco?', 'required' => false]);
        $this->inputsHelper()->dynamic(['ano', 'instituicao', 'escola']);
        $this->inputsHelper()->dynamic('curso');
        $this->inputsHelper()->dynamic('serie');
        $this->campoTexto('aluno', 'Aluno', '', 40, 255, true);
        $this->inputsHelper()->multipleSearchDocumentosAtestadoVaga(null, ['label' => 'Documentos obrigatórios', 'required' => false]);
        $this->inputsHelper()->checkbox('emitir_nome_diretor', ['label' => 'Emitir assinatura do gestor escolar']);
        $this->inputsHelper()->checkbox('emitir_secretario_escolar', ['label' => 'Emitir assinatura do secretário escolar']);
        $this->inputsHelper()->checkbox('emitir_validade', ['label' => 'Emitir a data de validade do documento?']);
        $this->inputsHelper()->text('campo_validade', ['required' => false, 'label' => 'Validade', 'size' => 45, 'max_length' => 3, 'placeholder' => 'Informe o número de dias para a validade']);
        $this->loadResourceAssets($this->getDispatcher());
    }

    /**
     * @inheritdoc
     */
    public function beforeValidation()
    {
        $branco = $this->getRequest()->branco ? 1 : 0;
        $this->report->addArg('ano', (int) $this->getRequest()->ano);
        $this->report->addArg('instituicao', (int) $this->getRequest()->ref_cod_instituicao);
        $this->report->addArg('escola', (int) $this->getRequest()->ref_cod_escola);
        $this->report->addArg('curso', (int) $this->getRequest()->ref_cod_curso);
        $this->report->addArg('serie', (int) $this->getRequest()->ref_cod_serie);
        $this->report->addArg('aluno', $this->getRequest()->aluno);
        $this->report->addArg('branco', $branco);
        $this->report->addArg('cabecalho_alternativo', (int) $GLOBALS['coreExt']['Config']->report->header->alternativo);
        $this->report->addArg('emitir_nome_diretor', (bool) $this->getRequest()->emitir_nome_diretor ? true : false);
        $this->report->addArg('emitir_secretario_escolar', (bool) $this->getRequest()->emitir_secretario_escolar ? true : false);
        $this->report->addArg('emitir_validade', (bool) $this->getRequest()->emitir_validade);
        $this->report->addArg('campo_validade', (string) $this->getRequest()->campo_validade);
        $this->report->addArg('titulo', (string) $this->titulo());

        $documentos = $this->getRequest()->documentos;

        if (isset($documentos)) {
            foreach ($documentos as $documento) {
                if (!empty($documento)) {
                    $this->report->addArg($documento, true);
                }
            }
        }

        if (!$branco) {
            $this->report->addRequiredArg('curso');
            $this->report->addRequiredArg('serie');
            $this->report->addRequiredArg('aluno');
        }
    }

    /**
     * @return VacancyCertificateReport
     *
     * @throws Exception
     */
    public function report()
    {
        return new VacancyCertificateReport();
    }
}
