<?php

use App\Models\LegacyInstitution;

class MinutesFinalResultController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var int
     */
    protected $_processoAp = 9998911;

    protected $_titulo = 'Relatório Ata de Resultado Final';

    protected function _preRender()
    {
        parent::_preRender();

        $this->breadcrumb('Emissão ata de resultado final', [
            url('educar_index.php') => 'Escola',
        ]);
    }

    public function form()
    {
        $this->inputsHelper()->dynamic(['ano', 'instituicao', 'escola', 'curso', 'serie', 'turma']);

        $this->inputsHelper()->dynamic('situacaoMatricula', ['value' => 10]);

        $this->inputsHelper()->textArea('observacao', ['required' => false, 'label' => 'Observações', 'placeholder' => 'Utilize este espaço para exibir uma mensagem ou recado na ata de resutado final.']);

        $helperOptions = ['objectName' => 'areaconhecimento'];
        $options = [
            'label' => 'Áreas de conhecimento',
            'size' => 50,
            'required' => false,
            'placeholder' => 'Todas',
            'options' => ['value' => null]
        ];
        $this->inputsHelper()->multipleSearchAreasConhecimento('', $options, $helperOptions);

        $this->loadResourceAssets($this->getDispatcher());
    }

    public function report()
    {
        return new MinutesFinalResultReport();
    }

    public function beforeValidation()
    {
        $this->report->addArg('ano', (int) $this->getRequest()->ano);
        $this->report->addArg('instituicao', (int) $this->getRequest()->ref_cod_instituicao);
        $this->report->addArg('escola', (int) $this->getRequest()->ref_cod_escola);
        $this->report->addArg('curso', (int) $this->getRequest()->ref_cod_curso);
        $this->report->addArg('serie', (int) $this->getRequest()->ref_cod_serie);
        $this->report->addArg('turma', (int) $this->getRequest()->ref_cod_turma);
        $this->report->addArg('situacao', (int) $this->getRequest()->situacao_matricula_id);
        $this->report->addArg('observacao', $this->getRequest()->observacao);

        $areasConhecimento = $this->getRequest()->areaconhecimento ?? [];
        $areasConhecimento = implode(',', array_filter($areasConhecimento));

        $this->report->addArg('areas_conhecimento', trim($areasConhecimento) == '' ? 0 : $areasConhecimento);
        $this->report->addArg('filtro_areas_conhecimento', trim($areasConhecimento) == '');
    }
}
