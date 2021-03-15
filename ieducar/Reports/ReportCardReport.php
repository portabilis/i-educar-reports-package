<?php

use iEducar\Reports\JsonDataSource;

class ReportCardReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource, GeneralOpinionsTrait, ReportCardTrait, DescriptiveOpinionsTrait {
        DescriptiveOpinionsTrait::query insteadof GeneralOpinionsTrait;
        GeneralOpinionsTrait::query insteadof ReportCardTrait;
        DescriptiveOpinionsTrait::query as QueryDescriptiveOpinions;
        GeneralOpinionsTrait::query as QueryGeneralOpinions;
        ReportCardTrait::query as QueryReportCard;
    }

    /**
     * @var array
     */
    public $modifiers = [
        ReportCardModifier::class,
    ];

    /**
     * @return string
     *
     * @throws App_Model_Exception
     */

    public function templateName()
    {
        $flagTipoBoletimTurma = App_Model_IedFinder::getTurma($codTurma = $this->args['turma']);

        if (
            $flagTipoBoletimTurma['tipo_boletim_diferenciado']
            && $flagTipoBoletimTurma['tipo_boletim_diferenciado'] != $flagTipoBoletimTurma['tipo_boletim']
            && $codMatricula = $this->args['matricula']
        ) {
            $matricula = App_Model_IedFinder::getMatricula($codMatricula);
            $possuiDeficiencia = App_Model_IedFinder::verificaSePossuiDeficiencia($matricula['ref_cod_aluno']);

            $flagTipoBoletimTurma = $flagTipoBoletimTurma['tipo_boletim' . ($possuiDeficiencia ? '_diferenciado' : '')];
        } elseif (
            $flagTipoBoletimTurma['tipo_boletim_diferenciado']
            && $flagTipoBoletimTurma['tipo_boletim_diferenciado'] != $flagTipoBoletimTurma['tipo_boletim']
            && $this->args['alunos_diferenciados'] == 2
        ) {
            $flagTipoBoletimTurma = $flagTipoBoletimTurma['tipo_boletim_diferenciado'];
        } else {
            $flagTipoBoletimTurma = $flagTipoBoletimTurma['tipo_boletim'];
        }

        if (empty($flagTipoBoletimTurma)) {
            throw new Exception('Não foi definido o tipo de boletim no cadastro de turmas.');
        }

        $templates = Portabilis_Model_Report_TipoBoletim::getInstance()->getReports();
        $template = !empty($templates[$flagTipoBoletimTurma]) ? $templates[$flagTipoBoletimTurma] : '';

        if ($this->args['orientacao'] == 2) {
            $template = $templates[Portabilis_Model_Report_TipoBoletim::CONCEPTUAL_LANDSCAPE];
        }

        if (empty($template)) {
            throw new Exception('Não foi possivel recuperar nome do template para o boletim.');
        }

        return $template;
    }

    /**
     * @inheritdoc
     */
    public function requiredArgs()
    {
        $this->addRequiredArg('ano');
        $this->addRequiredArg('instituicao');
        $this->addRequiredArg('escola');
        $this->addRequiredArg('curso');
        $this->addRequiredArg('serie');
        $this->addRequiredArg('turma');
    }

    public function getJsonData()
    {
        $template = $this->templateName();

        return [
            'main' => Portabilis_Utils_Database::fetchPreparedQuery($this->getQueryByTemplate()[$template]),
            'header' => Portabilis_Utils_Database::fetchPreparedQuery($this->getSqlHeaderReport())
        ];
    }

    private function getQueryByTemplate()
    {
        $templates = Portabilis_Model_Report_TipoBoletim::getInstance()->getReports();

        return [
            $templates[Portabilis_Model_Report_TipoBoletim::NUMERIC] => $this->QueryReportCard(),
            $templates[Portabilis_Model_Report_TipoBoletim::CONCEPTUAL] => $this->QueryReportCard(),
            $templates[Portabilis_Model_Report_TipoBoletim::CONCEPTUAL_LANDSCAPE] => $this->QueryReportCard(),
            $templates[Portabilis_Model_Report_TipoBoletim::PARECER_DESCRITIVO_COMPONENTE] => $this->QueryDescriptiveOpinions(),
            $templates[Portabilis_Model_Report_TipoBoletim::PARECER_DESCRITIVO_GERAL] => $this->QueryGeneralOpinions(),

        ];
    }
}
