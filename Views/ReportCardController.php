<?php

require_once 'lib/Portabilis/Controller/ReportCoreController.php';
require_once 'Reports/Reports/ReportCardReport.php';

class ReportCardController extends Portabilis_Controller_ReportCoreController
{
    /**
     * @var int
     */
    protected $_processoAp = 999202;

    /**
     * @var string
     */
    protected $_titulo = 'Boletim Escolar';

    /**
     * @inheritdoc
     */
    protected function _preRender()
    {
        parent::_preRender();

        $this->acao_enviar = 'customPrintReport()';

        $this->breadcrumb('Boletim escolar', [
            '/intranet/educar_index.php' => 'Escola'
        ]);
    }

    /**
     * @inheritdoc
     */
    public function form()
    {
        $this->inputsHelper()->dynamic([
            'ano',
            'instituicao',
            'escola',
            'curso',
            'serie',
            'turma'
        ]);
        $this->inputsHelper()->dynamic('matricula', ['required' => false]);

        $resources = [
            1 => 'Aprovado',
            2 => 'Reprovado',
            14 => 'Reprovado por falta',
            3 => 'Cursando',
            4 => 'Transferido',
            5 => 'Reclassificado',
            6 => 'Abandono',
            7 => 'Em exame',
            9 => 'Exceto Transferidos/Abandono',
            10 => 'Todas',
            12 => 'Aprovado com dependência',
            16 => 'Aprovado após exame'
        ];

        $options = [
            'label' => 'Situação do aluno',
            'resources' => $resources,
            'value' => 10
        ];

        $this->inputsHelper()->select('situacao_matricula', $options);
        $this->inputsHelper()->checkbox('imprimir_mensagem_aniversario', ['label' => 'Imprimir mensagem de aniversário?']);
        $this->inputsHelper()->textArea('mensagem_aniversario', [
            'required' => false,
            'label' => 'Mensagem de aniversário',
            'value' => 'Feliz aniversário. Acredite nos seus sonhos, pois com muita dedicação e estudo se tornarão realidade.'
        ]);
        $this->inputsHelper()->checkbox('grafico_media_turma', ['label' => 'Imprimir gráfico de média aluno x turma?']);
        $this->inputsHelper()->checkbox('grafico_preto', ['label' => 'Imprimir os gráficos em escala de cinza?']);
        $this->inputsHelper()->textArea('observacoes', [
            'required' => false,
            'label' => 'Observações',
            'placeholder' => 'Utilize este espaço para exibir uma mensagem ou recado no boletim.'
        ]);

        $this->loadResourceAssets($this->getDispatcher());
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
        $this->report->addArg('situacao_matricula', (int) $this->getRequest()->situacao_matricula);
        $this->report->addArg('imprimir_mensagem_aniversario', (bool) $this->getRequest()->imprimir_mensagem_aniversario);
        $this->report->addArg('mensagem_aniversario', $this->getRequest()->imprimir_mensagem_aniversario ? $this->getRequest()->mensagem_aniversario : '');
        $this->report->addArg('grafico_media_turma', (bool) $this->getRequest()->grafico_media_turma);
        $this->report->addArg('grafico_preto', (bool) $this->getRequest()->grafico_preto);
        $this->report->addArg('alunos_diferenciados', (int) ($this->getRequest()->alunos_diferenciados ?: 0));
        $this->report->addArg('observacoes', $this->getRequest()->observacoes);

        if (is_null($this->getRequest()->ref_cod_matricula)) {
            $this->report->addArg('matricula', 0);
        } else {
            $this->report->addArg('matricula', (int) $this->getRequest()->ref_cod_matricula);
        }
    }

    /**
     * @return ReportCardReport
     *
     * @throws Exception
     */
    public function report()
    {
        return new ReportCardReport();
    }
}
