<?php

use iEducar\Reports\JsonDataSource;

require_once 'lib/Portabilis/Report/ReportCore.php';
require_once 'App/Model/IedFinder.php';

class StudentsPerProjectsReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        return 'students-per-projects';
    }

    /**
     * @inheritdoc
     */
    public function requiredArgs()
    {
        $this->addRequiredArg('ano');
        $this->addRequiredArg('instituicao');
    }

    /**
     * Retorna o SQL para buscar os dados do relatório principal.
     *
     * @return string
     */
    public function getSqlMainReport()
    {
        $escola = $this->args['escola'] ?: 0;
        $curso = $this->args['curso'] ?: 0;
        $serie = $this->args['serie'] ?: 0;
        $turma = $this->args['turma'] ?: 0;
        $projeto = $this->args['projeto'] ?: 0;
        $ano = $this->args['ano'] ?: 0;

        return "
SELECT
    distinct relatorio.get_nome_escola(matricula.ref_ref_cod_escola) AS escola,
         cod_aluno,
         pessoa.nome AS aluno,
         to_char(fisica.data_nasc, 'dd/MM/yyyy') AS data_nascimento,
translate(pessoa.nome,'åáàãâäéèêëíìîïóòõôöúùüûçÿýñÅÁÀÃÂÄÉÈÊËÍÌÎÏÓÒÕÔÖÚÙÛÜÇÝÑ', 'aaaaaaeeeeiiiiooooouuuucyynAAAAAAEEEEIIIIOOOOOUUUUCYN'),
projeto.nome as projeto,
projeto_aluno.data_inclusao,
projeto_aluno.data_desligamento,
(CASE projeto_aluno.turno
  WHEN 1 THEN 'M'
  WHEN 2 THEN 'V'
  WHEN 3 THEN 'N'
  ELSE ''
  END) AS turno,

  (select cod_aluno_inep
          from modules.educacenso_cod_aluno
         where educacenso_cod_aluno.cod_aluno = aluno.cod_aluno) as cod_inep,
       to_char(now(), 'dd/MM/yyyy HH:mm') AS data_hora,
       serie.nm_serie,
       turma.nm_turma

  FROM cadastro.fisica,
       cadastro.pessoa,
       pmieducar.aluno
       LEFT JOIN pmieducar.matricula ON (matricula.ref_cod_aluno = aluno.cod_aluno)
       INNER JOIN pmieducar.projeto_aluno ON (projeto_aluno.ref_cod_aluno = aluno.cod_aluno)
       INNER JOIN pmieducar.projeto ON (projeto_aluno.ref_cod_projeto = projeto.cod_projeto)
       LEFT JOIN pmieducar.serie ON (serie.cod_serie = matricula.ref_ref_cod_serie)
       LEFT JOIN pmieducar.curso ON (curso.cod_curso = matricula.ref_cod_curso)
       LEFT JOIN pmieducar.matricula_turma ON (matricula_turma.ref_cod_matricula = matricula.cod_matricula)
       LEFT JOIN pmieducar.turma ON (matricula_turma.ref_cod_turma = turma.cod_turma)

 WHERE aluno.ref_idpes = fisica.idpes AND
    matricula.ultima_matricula = 1 AND
    matricula.ativo = 1 AND
   aluno.ativo = 1 AND

       fisica.idpes = pessoa.idpes AND
      (select case when {$escola} = 0 then
            1=1 ELSE
          matricula.ref_ref_cod_escola = {$escola}
          END) AND
       (select case when {$curso} = 0 then
        1=1 ELSE
          curso.cod_curso = {$curso}
          END) AND
       (select case when {$serie} = 0 then
        1=1 ELSE
          serie.cod_serie = {$serie}
          END) AND
       (select case when {$turma} = 0 then
        1=1 ELSE
          turma.cod_turma = {$turma}
          END) AND
          (select case when {$projeto} = 0 then
        1=1 ELSE
          projeto.cod_projeto = {$projeto}
          END) AND
  matricula.ano = {$ano} AND
  matricula_turma.ativo = 1

order by escola, translate(pessoa.nome,'åáàãâäéèêëíìîïóòõôöúùüûçÿýñÅÁÀÃÂÄÉÈÊËÍÌÎÏÓÒÕÔÖÚÙÛÜÇÝÑ', 'aaaaaaeeeeiiiiooooouuuucyynAAAAAAEEEEIIIIOOOOOUUUUCYN'), projeto
        ";
    }
}
