<?php

use iEducar\Reports\JsonDataSource;

require_once 'lib/Portabilis/Report/ReportCore.php';
require_once 'Reports/Tipos/TipoBoletim.php';
require_once 'App/Model/IedFinder.php';
require_once 'Reports/Queries/GeneralOpinionsTrait.php';
require_once 'Reports/Queries/BimonthlyReportCardTrait.php';
require_once 'Reports/Modifiers/ReportCardModifier.php';

class ReportCardReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource, GeneralOpinionsTrait, BimonthlyReportCardTrait {
        GeneralOpinionsTrait::query insteadof BimonthlyReportCardTrait;
        GeneralOpinionsTrait::query AS QueryGeneralOpinions;
        BimonthlyReportCardTrait::query AS QueryBimonthlyReportCard;
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
            $templates[Portabilis_Model_Report_TipoBoletim::BIMESTRAL] => $this->QueryBimonthlyReportCard(),
            $templates[Portabilis_Model_Report_TipoBoletim::BIMESTRAL_CONCEITUAL] => $this->QueryBimonthlyReportCard(),
            $templates[Portabilis_Model_Report_TipoBoletim::PARECER_DESCRITIVO_GERAL] => $this->QueryGeneralOpinions(),
        ];
    }
}
