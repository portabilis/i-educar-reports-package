<?php

class SchoolingCertificateController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var int
     */
    protected $_processoAp = 999810;

    protected $_titulo = 'Atestado de escolaridade';

    protected function _preRender()
    {
        parent::_preRender();
        $this->breadcrumb('Atestado de escolaridade', [
            'educar_index.php' => 'Escola',
        ]);
    }

    public function form()
    {
        $assinatura_secretario = _cl('report.termo_assinatura_secretario');
        $this->campoOculto('assinatura_secretario', $assinatura_secretario);
        $this->inputsHelper()->dynamic(['ano', 'instituicao']);
        $this->inputsHelper()->dynamic(['escolaObrigatorioParaNivelEscolar'], ['required' => false]);
        $this->inputsHelper()->simpleSearchMatricula(null, ['required' => true]);
        $this->campoMemo('observacao', 'Observação', $this->observacao, 48, 5, false);
        $this->inputsHelper()->checkbox('emitir_nome_diretor', ['label' => 'Emitir assinatura do gestor escolar']);
        $this->inputsHelper()->checkbox('emitir_secretario_escolar', ['label' => "Emitir assinatura do {$assinatura_secretario} escolar"]);
        $this->loadResourceAssets($this->getDispatcher());
    }

    public function report()
    {
        return new SchoolingCertificateReport();
    }

    public function beforeValidation()
    {
        $emitirRa = config('legacy.report.mostrar_relatorios') == 'camposdojordao';
        $this->report->addArg('emitir_ra', (string) $emitirRa);
        $this->report->addArg('ano', (int) $this->getRequest()->ano);
        $this->report->addArg('instituicao', (int) $this->getRequest()->ref_cod_instituicao);
        $this->report->addArg('escola', (int) $this->getRequest()->ref_cod_escola);
        $this->report->addArg('matricula', (int) $this->getRequest()->matricula_id);
        $this->report->addArg('observacao', (string) $this->getRequest()->observacao);
        $this->report->addArg('emitir_nome_diretor', (bool) $this->getRequest()->emitir_nome_diretor);
        $this->report->addArg('emitir_secretario_escolar', (bool) $this->getRequest()->emitir_secretario_escolar);
        $this->report->addArg('titulo', 'Atestado de escolaridade');
        $this->report->addArg('assinatura_secretario', urldecode((string) $this->getRequest()->assinatura_secretario));
        $this->report->addArg('assinatura_diretor', _cl('report.termo_assinatura_diretor'));
    }
}
