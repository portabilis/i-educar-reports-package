<?php

use iEducar\Reports\JsonDataSource;

class StudentsWithBenefitsReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        return 'students-with-benefits';
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
        $situacao = $this->args['situacao'];
        $instituicao = $this->args['instituicao'];
        $escola = $this->args['escola'];
        $ano = $this->args['ano'];
        $curso = $this->args['curso'];
        $serie = $this->args['serie'];
        $turma = $this->args['turma'];

        return "
SELECT cod_aluno,
       pessoa.nome AS aluno,
       pessoa.nome,
       fisica.nis_pis_pasep,
       serie.nm_serie,
       turma.nm_turma,
       textcat_all(distinct nm_beneficio) AS nm_beneficio,
       (SELECT cod_aluno_inep
          FROM modules.educacenso_cod_aluno
         WHERE educacenso_cod_aluno.cod_aluno = aluno.cod_aluno) AS cod_inep,
       view_situacao.texto_situacao AS situacao
  FROM pmieducar.instituicao,
       cadastro.fisica,
       cadastro.pessoa,
       pmieducar.escola
  LEFT JOIN pmieducar.matricula ON (matricula.ref_ref_cod_escola = escola.cod_escola)
  LEFT JOIN pmieducar.aluno ON (matricula.ref_cod_aluno = aluno.cod_aluno)
 INNER JOIN pmieducar.aluno_aluno_beneficio ON (aluno_aluno_beneficio.aluno_id = aluno.cod_aluno)
 INNER JOIN pmieducar.aluno_beneficio ON (aluno_beneficio.cod_aluno_beneficio = aluno_aluno_beneficio.aluno_beneficio_id)
  LEFT JOIN pmieducar.serie ON (serie.cod_serie = matricula.ref_ref_cod_serie)
  LEFT JOIN pmieducar.curso ON (curso.cod_curso = matricula.ref_cod_curso)
  LEFT JOIN pmieducar.matricula_turma ON (matricula_turma.ref_cod_matricula = matricula.cod_matricula)
  LEFT JOIN pmieducar.turma ON (matricula_turma.ref_cod_turma = turma.cod_turma)
 INNER JOIN relatorio.view_situacao ON (view_situacao.cod_matricula = matricula.cod_matricula
					AND view_situacao.cod_turma = turma.cod_turma
				        AND view_situacao.cod_situacao = {$situacao}
				        AND matricula_turma.sequencial = view_situacao.sequencial)
 WHERE instituicao.cod_instituicao = {$instituicao}
   AND aluno.ref_idpes = fisica.idpes
   AND matricula.ultima_matricula = 1
   AND matricula.ativo = 1
   AND aluno.ativo = 1
   AND fisica.idpes = pessoa.idpes
   AND escola.cod_escola = {$escola}
   AND matricula.ano = {$ano}
   AND (CASE WHEN {$curso} = 0 THEN TRUE ELSE curso.cod_curso = {$curso} END)
   AND (CASE WHEN {$serie} = 0 THEN TRUE ELSE serie.cod_serie = {$serie} END)
   AND (CASE WHEN {$turma} = 0 THEN TRUE ELSE turma.cod_turma = {$turma} END)
 GROUP BY cod_aluno, aluno, nm_serie, nm_turma, fisica.nis_pis_pasep, texto_situacao
 ORDER BY relatorio.get_texto_sem_caracter_especial(pessoa.nome), nm_beneficio
        ";
    }
}
