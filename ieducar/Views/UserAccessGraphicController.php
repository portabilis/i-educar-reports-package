<?php

class UserAccessGraphicController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var int
     */
    protected $_processoAp = 999244;

    protected $_titulo = 'Relatório gráfico de usuários e acessos';

    protected function _preRender()
    {
        parent::_preRender();
        $this->breadcrumb('Relatório gráfico de usuários e acessos', [
            'educar_index.php' => 'Escola',
        ]);
    }

    public function form()
    {
        $this->inputsHelper()->dynamic(['instituicao']);
        $this->inputsHelper()->dynamic(['curso', 'escola'], ['required' => false]);

        $this->inputsHelper()->date('data_inicial', ['label' => 'Data inicial']);
        $this->inputsHelper()->date('data_final', [ 'label' => 'Data final']);

        $this->inputsHelper()->checkbox(
            'imprime_grafico',
            [
                'label' => 'Imprimir gráfico?'
            ]
        );

        $this->loadResourceAssets($this->getDispatcher());
    }

    public function report()
    {
        return new UserAccessGraphicReport();
    }

    public function beforeValidation()
    {
        $this->report->addArg('instituicao', (int)$this->getRequest()->ref_cod_instituicao);
        $this->report->addArg('data_inicial', Portabilis_Date_Utils::brToPgSQL($this->getRequest()->data_inicial));
        $this->report->addArg('data_final', Portabilis_Date_Utils::brToPgSQL($this->getRequest()->data_final));
        $this->report->addArg('escola', ($this->getRequest()->ref_cod_escola=='' ? 0 : (int)$this->getRequest()->ref_cod_escola));
        $this->report->addArg('curso', ($this->getRequest()->ref_cod_curso=='' ? 0 : (int)$this->getRequest()->ref_cod_curso));
        $this->report->addArg('imprime_grafico', (bool) $this->getRequest()->imprime_grafico);
    }
}
