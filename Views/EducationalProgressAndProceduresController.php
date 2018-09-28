<?php

require_once 'lib/Portabilis/Controller/ReportCoreController.php';
require_once 'Reports/Reports/EducationalProgressAndProceduresReport.php';

class EducationalProgressAndProceduresController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var string
     */
    protected $_titulo = 'Relatório de rendimento e movimento escolar';

    /**
     * @inheritdoc
     */
    protected function _preRender()
    {
        parent::_preRender();

        $this->breadcrumb('Relatório de rendimento e movimento escolar', [
            '/intranet/educar_index.php' => 'Escola'
        ]);
    }

    /**
     * @inheritdoc
     */
    public function form()
    {
        $this->inputsHelper()->dynamic(['ano', 'instituicao', 'escola']);
        $this->inputsHelper()->dynamic('escola', ['required' => false]);
        $this->inputsHelper()->checkbox('imprimir_grafico', ['label' => 'Imprimir gráfico?']);
    }

    /**
     * @inheritdoc
     */
    public function beforeValidation()
    {
        $this->report->addArg('ano', (int) $this->getRequest()->ano);
        $this->report->addArg('instituicao', (int) $this->getRequest()->ref_cod_instituicao);
        $this->report->addArg('escola', (int) $this->getRequest()->ref_cod_escola);
        $this->report->addArg('imprimir_grafico', (bool) $this->getRequest()->imprimir_grafico);
    }

    /**
     * @return EducationalProgressAndProceduresReport
     *
     * @throws Exception
     */
    public function report()
    {
        return new EducationalProgressAndProceduresReport();
    }
}