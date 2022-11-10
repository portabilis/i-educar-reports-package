<?php

class UserAccessController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var int
     */
    protected $_processoAp = 999223;

    protected $_titulo = 'Usuários e acessos';

    protected function _preRender()
    {
        parent::_preRender();
        $this->breadcrumb('Relatório de usuários e acessos', [
            'educar_index.php' => 'Escola',
        ]);
    }

    public function form()
    {
        $this->inputsHelper()->dynamic(['instituicao']);
        $resources = [
            1 => 'Ativo',
            0 => 'Inativo',
            2 => 'Todos'
        ];
        $options = [
            'label' => 'Usuário',
            'resources' => $resources,
            'value' => 1
        ];
        $this->inputsHelper()->select('ativo', $options);
        $this->inputsHelper()->date('data_inicial', ['label' => 'Data inicial']);
        $this->inputsHelper()->date('data_final', ['label' => 'Data final']);
        $this->inputsHelper()->dynamic('EscolaObrigatorioParaNivelEscolar', ['required' => false]);
        $this->inputsHelper()->simpleSearchPessoa('pessoa_id', ['required' => false]);
    }

    public function report()
    {
        return new UserAccessReport();
    }

    public function beforeValidation()
    {
        $this->report->addArg('instituicao', (int)$this->getRequest()->ref_cod_instituicao);
        $this->report->addArg('ativo', (int)$this->getRequest()->ativo);
        $this->report->addArg('data_inicial', Portabilis_Date_Utils::brToPgSQL($this->getRequest()->data_inicial));
        $this->report->addArg('data_final', Portabilis_Date_Utils::brToPgSQL($this->getRequest()->data_final));
        $this->report->addArg('escola', ($this->getRequest()->ref_cod_escola == '' ? 0 : (int)$this->getRequest()->ref_cod_escola));
        $this->report->addArg('pessoa', ($this->getRequest()->pessoa_id == '' ? 0 : (int)$this->getRequest()->pessoa_id));
    }
}
