<?php

require_once 'lib/Portabilis/Controller/ReportCoreController.php';
require_once 'Reports/Reports/SchoolTransportationUsersReport.php';
require_once 'App/Model/ZonaLocalizacao.php';

class SchoolTransportationUsersController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var string
     */
    protected $_titulo = 'Relatório de Usuários de Transporte';

    /**
     * @inheritdoc
     */
    protected function _preRender()
    {
        parent::_preRender();

        Portabilis_View_Helper_Application::loadStylesheet($this, 'intranet/styles/localizacaoSistema.css');

        $this->breadcrumb('Relatório de usuários de transporte por escola', [
            'educar_index.php' => 'Escola',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function form()
    {
        $this->inputsHelper()->dynamic(['ano', 'instituicao']);
        $this->inputsHelper()->dynamic('escola', ['required' => false]);
        $this->inputsHelper()->dynamic('curso', ['required' => false]);
        $this->inputsHelper()->dynamic('serie', ['required' => false]);
        $this->inputsHelper()->dynamic('turma', ['required' => false]);
        $this->inputsHelper()->turmaTurno(['required' => false, 'label' => 'Período']);

        $zonas = App_Model_ZonaLocalizacao::getInstance();
        $zonas = $zonas->getEnums();
        $zonas = Portabilis_Array_Utils::insertIn(null, 'Selecione', $zonas);

        $this->inputsHelper()->select('zona_localizacao', [
            'label' => 'Zona localização',
            'placeholder' => '',
            'value' => $this->zona_localizacao,
            'resources' => $zonas,
            'required' => false
        ]);
    }

    /**
     * @inheritdoc
     */
    public function beforeValidation()
    {
        $this->report->addArg('ano', (int) $this->getRequest()->ano);
        $this->report->addArg('instituicao', (int) $this->getRequest()->ref_cod_instituicao);
        $this->report->addArg('escola', (int) $this->getRequest()->ref_cod_escola);
        $this->report->addArg('curso', (int) $this->getRequest()->ref_cod_curso);
        $this->report->addArg('serie', (int) $this->getRequest()->ref_cod_serie);
        $this->report->addArg('turma', (int) $this->getRequest()->ref_cod_turma);
        $this->report->addArg('zona_localizacao', (int) $this->getRequest()->zona_localizacao);
        $this->report->addArg('periodo', (int) $this->getRequest()->turma_turno_id);
    }

    /**
     * @return SchoolTransportationUsersReport
     *
     * @throws Exception
     */
    public function report()
    {
        return new SchoolTransportationUsersReport();
    }
}
