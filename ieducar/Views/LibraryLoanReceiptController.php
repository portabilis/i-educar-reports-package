<?php

require_once 'lib/Portabilis/Controller/ReportCoreController.php';
require_once 'Reports/Reports/LibraryLoanReceiptReport.php';

class LibraryLoanReceiptController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var int
     */
    protected $_processoAp = 999620;

    /**
     * @var string
     */
    protected $_titulo = 'Comprovante de Empréstimo';

    /**
     * @inheritdoc
     */
    protected function _preRender()
    {
        parent::_preRender();

        Portabilis_View_Helper_Application::loadStylesheet($this, 'intranet/styles/localizacaoSistema.css');

        $this->breadcrumb('Comprovante de Empréstimo', [
            'educar_biblioteca_index.php' => 'Biblioteca',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function form()
    {
        $this->inputsHelper()->dynamic(['instituicao', 'escola']);
        $this->inputsHelper()->dynamic(['BibliotecaPesquisaCliente', 'dataInicial', 'dataFinal'], ['required' => false]);
        $this->inputsHelper()->textArea('observacao', ['required' => false, 'label' => 'Observações', 'placeholder' => 'Utilize este espaço para exibir uma mensagem para o aluno.']);
        $this->inputsHelper()->checkbox('emitir_2via', ['label' => 'Emitir segunda via do documento?']);
    }

    /**
     * @inheritdoc
     */
    public function beforeValidation()
    {
        $this->report->addArg('instituicao', (int) $this->getRequest()->ref_cod_instituicao);
        $this->report->addArg('escola', (int) $this->getRequest()->ref_cod_escola);
        $this->report->addArg('observacao', $this->getRequest()->observacao);
        if (!isset($_POST['ref_cod_cliente']) || trim($_POST['ref_cod_cliente']) == '') {
            $this->report->addArg('cliente', 0);
        } else {
            $this->report->addArg('cliente', (int) $this->getRequest()->ref_cod_cliente);
        }
        $this->report->addArg('dt_inicial', $this->getRequest()->data_inicial);
        $this->report->addArg('dt_final', $this->getRequest()->data_final);
        $this->report->addArg('emitir_2via', (bool) $this->getRequest()->emitir_2via);
    }

    /**
     * @return LibraryLoanReceiptReport
     *
     * @throws Exception
     */
    public function report()
    {
        return new LibraryLoanReceiptReport();
    }
}
