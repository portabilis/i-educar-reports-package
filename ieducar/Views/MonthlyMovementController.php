<?php

use App\Models\LegacySchoolClass;
use App\Services\SchoolClass\SchoolClassService;

class MonthlyMovementController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var int
     */
    protected $_processoAp = 9998862;

    /**
     * @var string
     */
    protected $_titulo = 'Relatório de Movimento Mensal';

    public function __construct()
    {
        parent::__construct();

        Portabilis_View_Helper_Application::loadJavascript($this, [
            '/vendor/legacy/Portabilis/Assets/Plugins/Chosen/chosen.jquery.min.js',
            '/intranet/scripts/movimento_mensal.js',
        ]);

        Portabilis_View_Helper_Application::loadStylesheet($this, [
            '/vendor/legacy/Portabilis/Assets/Plugins/Chosen/chosen.css'
        ]);
    }

    /**
     * @inheritdoc
     */
    protected function _preRender()
    {
        parent::_preRender();

        $this->breadcrumb($this->_titulo, [
            'educar_index.php' => 'Escola',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function form()
    {
        $this->inputsHelper()->dynamic(['ano', 'instituicao']);
        $this->inputsHelper()->dynamic(['EscolaObrigatorioParaNivelEscolar', 'curso', 'serie', 'turma'], ['required' => false]);

        $helperOptions = [
            'objectName' => 'modalidade'
        ];
        $options = [
            'label' => 'Modalidade',
            'required' => false,
            'size' => 50,
            'value' => '',
            'options' => [
                'all_values' => [
                    0 => 'Regular',
                    5 => 'Atendimento Educacional Especializado - AEE',
                    4 => 'Atividade complementar',
                    3 => 'Educação de Jovens e Adultos - EJA',
                ],
            ]
        ];
        $this->inputsHelper()->multipleSearchCustom('', $options, $helperOptions);

        $calendars = $this->getCalendars();
        $this->addHtml(
            view('form.calendar')
                ->with('calendars', $calendars)
        );

        $this->inputsHelper()->date('data_inicial', [
            'placeholder' => '',
            'label' => 'Data inicial',
            'value' => date('01/m/Y')
        ]);
        $this->inputsHelper()->date('data_final', [
            'placeholder' => '',
            'label' => 'Data final',
            'value' => date('t/m/Y')
        ]);
        $this->loadResourceAssets($this->getDispatcher());
    }

    /**
     * @inheritdoc
     */
    public function beforeValidation()
    {
        $modalidades = $this->getRequest()->modalidade;
        $tiposAtendimento = [];
        $modalidadeEja = 0;
        $tipoAtendimento = [];

        foreach ($modalidades as $key => $modalidade) {
            if ($modalidade != 3) {
                $tipoAtendimento[] = $modalidade;
                continue;
            }

            $modalidadeEja = $modalidade;
        }

        if (is_array($modalidades)) {
            $modalidades = implode(', ', $modalidades);
        }

        $tiposAtendimento = implode(', ', $tipoAtendimento);

        if ($tiposAtendimento == '') {
            $tiposAtendimento = 10;
        }

        $this->report->addArg('ano', (int)$this->getRequest()->ano);
        $this->report->addArg('instituicao', (int)$this->getRequest()->ref_cod_instituicao);
        $this->report->addArg('escola', (int)$this->getRequest()->ref_cod_escola);
        $this->report->addArg('curso', (int)$this->getRequest()->ref_cod_curso);
        $this->report->addArg('serie', (int)$this->getRequest()->ref_cod_serie);
        $this->report->addArg('turma', (int)$this->getRequest()->ref_cod_turma);
        $this->report->addArg('data_inicial', Portabilis_Date_Utils::brToPgSQL($this->getRequest()->data_inicial));
        $this->report->addArg('data_final', Portabilis_Date_Utils::brToPgSQL($this->getRequest()->data_final));
        $this->report->addArg('modalidade', $modalidades);
        $this->report->addArg('tipos_atendimento', $tiposAtendimento);
        $this->report->addArg('modalidade_eja', $modalidadeEja);

        $startDate = [];
        $endDate = [];
        $filtrarDatas = true;

        if (empty($this->getRequest()->calendars)) {
            $filtrarDatas = false;
        }

        $this->report->addArg('filtrar_datas_calendario', $filtrarDatas);

        foreach ($this->getRequest()->calendars as $datas) {
            $arrayDatas = explode(' ', $datas);
            $startDate[] = $arrayDatas[0];
            $endDate[] = $arrayDatas[1];
        }

        $this->report->addArg('data_inicial_calendario', implode('\',\'', $startDate));
        $this->report->addArg('data_final_calendario', implode('\',\'', $endDate));
    }

    /**
     * @throws Exception
     *
     * @return MonthlyMovementReport
     */
    public function report()
    {
        return new MonthlyMovementReport();
    }

    private function getCalendars()
    {
        $schoolClass = $this->getSchoolClass();

        $schoolClassService = new SchoolClassService();

        return $schoolClassService->getCalendars($schoolClass);
    }

    private function getSchoolClass()
    {
        $request = $this->getRequest();
        if ($request->ref_cod_turma) {
            return [$request->ref_cod_turma];
        }

        return LegacySchoolClass::query()
            ->where('ano', ($request->ano ?: date('Y')))
            ->whereHas('course', function ($courseQuery) {
                $courseQuery->isEja();
            })
            ->when($request->ref_cod_escola, function ($query) {
                $query->where('ref_ref_cod_escola', $this->getRequest()->ref_cod_escola);
            })
            ->when($request->ref_cod_serie, function ($query) {
                $query->where('ref_ref_cod_serie', $this->getRequest()->ref_cod_serie);
            })
            ->when($request->ref_cod_curso, function ($query) {
                $query->where('ref_cod_curso', $this->getRequest()->ref_cod_curso);
            })
            ->get(['cod_turma'])->pluck('cod_turma')->all();
    }
}
