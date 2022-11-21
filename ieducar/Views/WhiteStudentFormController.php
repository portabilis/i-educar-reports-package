<?php

use App\Models\LegacyInstitution;

class WhiteStudentFormController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var int
     */
    protected $_processoAp = 999204;

    protected $_titulo = 'Relatório Ficha do Aluno em Branco';

    protected function _preRender()
    {
        parent::_preRender();

        $this->breadcrumb('Emissão da ficha do aluno em branco', [
            url('educar_index.php') => 'Escola',
        ]);
    }

    public function form()
    {
        $this->inputsHelper()->dynamic(['instituicao', 'escola']);
        $resources = [1 => 'Modelo 1', 2 => 'Modelo 2'];
        $options = ['label' => 'Modelo', 'resources' => $resources, 'value' => 1];

        $this->inputsHelper()->select('modelo', $options);
    }

    public function report()
    {
        return new WhiteStudentFormReport();
    }

    public function beforeValidation()
    {
        $this->report->addArg('instituicao', (int) $this->getRequest()->ref_cod_instituicao);
        $this->report->addArg('escola', (int) $this->getRequest()->ref_cod_escola);
        $this->report->addArg('modelo', (int) $this->getRequest()->modelo);
    }
}
