<?php

use iEducar\Reports\JsonDataSource;

class StudentsAverageReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        return 'students-average';
    }

    /**
     * @inheritdoc
     */
    public function requiredArgs()
    {
        $this->addRequiredArg('ano');
        $this->addRequiredArg('instituicao');
        $this->addRequiredArg('escola');
    }

    /**
     * Retorna o SQL para buscar os dados do relatório principal.
     *
     * @return string
     */
    public function getSqlMainReport()
    {
        $etapa = $this->args['etapa'] ?: 0;
        $ano = $this->args['ano'] ?: 0;
        $instituicao = $this->args['instituicao'] ?: 0;
        $escola = $this->args['escola'] ?: 0;
        $curso = $this->args['curso'] ?: 0;
        $serie = $this->args['serie'] ?: 0;
        $turma = $this->args['turma'] ?: 0;
        $limite = $this->args['limite'] ?: 1000;

        return "
        select
   turma.ano,
   instituicao.nm_instituicao,
   curso.nm_curso,
   serie.nm_serie,
   turma.nm_turma,
   relatorio.get_nome_escola(escola.cod_escola) as escola,
   ROUND(avg(nota_componente_curricular.nota), 2) as media,
   pessoa.nome,
   (CASE WHEN  '{$etapa}' = '0' THEN 'NDA' ELSE nota_componente_curricular.etapa END) as etapa_case




   from cadastro.juridica
   inner join pmieducar.escola on (escola.ref_idpes = juridica.idpes)
   inner  join pmieducar.escola_curso on (escola.cod_escola = escola_curso.ref_cod_escola)
   inner  join pmieducar.curso on (escola_curso.ref_cod_curso = curso.cod_curso)
   inner  join pmieducar.serie on (curso.cod_curso = serie.ref_cod_curso)
   inner  join pmieducar.turma on (turma.ref_ref_cod_serie = serie.cod_serie and turma.ref_ref_cod_escola = escola.cod_escola and curso.cod_curso = turma.ref_cod_curso)
   inner  join pmieducar.matricula_turma on (turma.cod_turma = matricula_turma.ref_cod_turma)
   inner  join pmieducar.matricula on (matricula_turma.ref_cod_matricula = matricula.cod_matricula and escola.cod_escola = matricula.ref_ref_cod_escola and matricula.ref_ref_cod_serie = serie.cod_serie)
   inner  join pmieducar.aluno on (matricula.ref_cod_aluno = aluno.cod_aluno)
   inner  join cadastro.pessoa on (aluno.ref_idpes = pessoa.idpes)
   inner  join cadastro.fisica on (pessoa.idpes = fisica.idpes)
   inner  join pmieducar.instituicao  on (escola.ref_cod_instituicao = instituicao.cod_instituicao)
   inner  join modules.nota_aluno on (nota_aluno.matricula_id = matricula.cod_matricula)
   inner  join modules.nota_componente_curricular on (nota_componente_curricular.nota_aluno_id = nota_aluno.id)

   where turma.ano = {$ano}
     and escola.ref_cod_instituicao = {$instituicao}
     and escola.ativo = 1
     and escola_curso.ativo = 1
     and curso.ativo = 1
     and serie.ativo = 1
     and turma.ativo = 1
     and matricula_turma.ativo = 1
     and matricula.ativo = 1
     and aluno.ativo = 1
     and instituicao.ativo = 1
     and (CASE WHEN {$escola} = 0 THEN true ELSE {$escola} = escola.cod_escola END)
     and (CASE WHEN {$curso}  = 0 THEN true ELSE  {$curso} = curso.cod_curso   END)
     and (CASE WHEN {$serie}  = 0 THEN true ELSE {$serie} = serie.cod_serie   END)
     and (CASE WHEN {$turma}  = 0 THEN true ELSE {$turma}  = turma.cod_turma   END)
     and (CASE WHEN '{$etapa}'  = '0' THEN true ELSE '{$etapa}' = nota_componente_curricular.etapa   END)
     group by turma.ano, nm_instituicao, nm_curso, nm_serie, nm_turma, escola, pessoa.nome, etapa_case
     order by  media DESC, nome ASC
     LIMIT {$limite}
        ";
    }
}
