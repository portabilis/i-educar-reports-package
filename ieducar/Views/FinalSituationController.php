<?php

class FinalSituationController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var int
     */
    protected $_processoAp = 999882;

    /**
     * @var string
     */
    protected $_titulo = 'Quadro de situação final';

    /**
     * @inheritdoc
     */
    protected function _preRender()
    {
        parent::_preRender();

        $this->breadcrumb($this->_titulo, [
            url('intranet/educar_index.php') => 'Escola',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function form()
    {
        $this->inputsHelper()->dynamic(['ano', 'instituicao']);
        $this->inputsHelper()->dynamic('EscolaObrigatorioParaNivelEscolar', ['required' => false]);
        $this->inputsHelper()->multipleSearchCurso(null, ['label' => 'Curso(s)', 'required' => false]);
        $this->inputsHelper()->date('data_inicial', [
            'placeholder' => '',
            'label' => 'Data Inicial',
            'value' => date('01/m/Y')
        ]);
        $this->inputsHelper()->date('data_final', [
            'placeholder' => '',
            'label' => 'Data Final',
            'value' => date('t/m/Y')
        ]);
    }

    /**
     * @inheritdoc
     */
    public function beforeValidation()
    {
        $this->report->addArg('ano', (int) $this->getRequest()->ano);
        $this->report->addArg('assinatura_diretor', _cl('report.termo_assinatura_diretor'));
        $this->report->addArg('instituicao', (int) $this->getRequest()->ref_cod_instituicao);
        $this->report->addArg('escola', (int) $this->getRequest()->ref_cod_escola);
        $this->report->addArg('data_inicial', Portabilis_Date_Utils::brToPgSQL($this->getRequest()->data_inicial));
        $this->report->addArg('data_final', Portabilis_Date_Utils::brToPgSQL($this->getRequest()->data_final));

        $array_cursos = array_filter($this->getRequest()->curso ?? []);
        $cursos = implode(',', $array_cursos);
        $this->report->addArg('curso', trim($cursos) == '' ? 0 : $cursos);
    }

    /**
     * @return FinalSituationReport
     */
    public function report()
    {
        return new FinalSituationReport();
    }
}
