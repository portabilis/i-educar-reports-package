<?php

use iEducar\Reports\JsonDataSource;

class StudentsWithDisabilitiesReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        return 'students-with-disabilities';
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
     *
     * @throws Exception
     */
    public function getSqlMainReport()
    {
        $situacao = $this->args['situacao'] ?: 0;
        $instituicao = $this->args['instituicao'] ?: 0;
        $escola = $this->args['escola'] ?: 0;
        $ano = $this->args['ano'] ?: 0;
        $curso = $this->args['curso'] ?: 0;
        $serie = $this->args['serie'] ?: 0;
        $turma = $this->args['turma'] ?: 0;

        return "
        SELECT aluno.cod_aluno AS cod_aluno,
       pessoa.nome     AS aluno,
       educacenso_cod_aluno.cod_aluno_inep AS cod_inep,

(SELECT textcat_all(distinct d.nm_deficiencia) as nm_deficiencia
          FROM pmieducar.aluno as a
         INNER JOIN cadastro.fisica_deficiencia as fd ON (fd.ref_idpes = a.ref_idpes)
	 INNER JOIN cadastro.deficiencia as d ON (fd.ref_cod_deficiencia = d.cod_deficiencia)
         WHERE a.cod_aluno = aluno.cod_aluno) AS nm_deficiencia,

       (SELECT textcat_all(distinct t.nm_turma || ' - ' || tt.nome) AS nome_turma_turno
          FROM pmieducar.matricula m
         INNER JOIN pmieducar.matricula_turma mt ON (mt.ref_cod_matricula = m.cod_matricula)
         INNER JOIN pmieducar.turma t ON (t.cod_turma = mt.ref_cod_turma)
         INNER JOIN pmieducar.turma_turno tt ON (tt.id = t.turma_turno_id)
         WHERE m.ref_cod_aluno = aluno.cod_aluno
           AND m.ativo = 1
           AND m.ano = {$ano}
           AND m.ref_ref_cod_escola = {$escola}
           AND mt.sequencial = (SELECT MAX(mtt.sequencial)
                                  FROM matricula_turma mtt
                                 WHERE mtt.ref_cod_matricula = m.cod_matricula AND mtt.ativo = 1)) AS nome_turma_turno

  FROM pmieducar.instituicao
INNER JOIN pmieducar.escola ON (escola.ref_cod_instituicao = instituicao.cod_instituicao)
   INNER JOIN pmieducar.escola_curso ON (escola_curso.ativo = 1
                                         AND escola_curso.ref_cod_escola = escola.cod_escola)
   INNER JOIN pmieducar.curso ON (curso.cod_curso = escola_curso.ref_cod_curso
                                  AND curso.ativo = 1)
   INNER JOIN pmieducar.escola_serie ON (escola_serie.ativo = 1
                                         AND escola_serie.ref_cod_escola = escola.cod_escola)
   INNER JOIN pmieducar.serie ON (serie.cod_serie = escola_serie.ref_cod_serie
                                  AND serie.ativo = 1)
   INNER JOIN pmieducar.turma ON (turma.ref_ref_cod_escola = escola.cod_escola
                                  AND turma.ref_cod_curso = escola_curso.ref_cod_curso
                                  AND turma.ref_ref_cod_serie = escola_serie.ref_cod_serie
                                  AND turma.ativo = 1)
   INNER JOIN pmieducar.turma_turno ON (turma_turno.id = turma.turma_turno_id)
   INNER JOIN pmieducar.matricula_turma ON (matricula_turma.ref_cod_turma = turma.cod_turma)
   INNER JOIN pmieducar.matricula ON (matricula.cod_matricula = matricula_turma.ref_cod_matricula
                                      AND matricula.ativo = 1)
 INNER JOIN relatorio.view_situacao ON (view_situacao.cod_matricula = matricula.cod_matricula
					AND view_situacao.cod_turma = turma.cod_turma
					AND view_situacao.sequencial = matricula_turma.sequencial
					AND view_situacao.cod_situacao =  {$situacao})
 INNER JOIN pmieducar.aluno ON (aluno.cod_aluno = matricula.ref_cod_aluno)
  LEFT JOIN modules.educacenso_cod_aluno ON (educacenso_cod_aluno.cod_aluno = aluno.cod_aluno)
 INNER JOIN cadastro.pessoa ON (pessoa.idpes = aluno.ref_idpes)
INNER JOIN cadastro.fisica_deficiencia ON (fisica_deficiencia.ref_idpes = aluno.ref_idpes)
 WHERE instituicao.cod_instituicao = {$instituicao}
   AND escola.cod_escola = {$escola}
   AND matricula.ano = {$ano}
   AND (CASE WHEN 0={$curso} THEN TRUE ELSE curso.cod_curso = {$curso} END)
   AND (CASE WHEN 0={$serie} THEN TRUE ELSE serie.cod_serie = {$serie} END)
   AND (CASE WHEN 0={$turma} THEN TRUE ELSE turma.cod_turma = {$turma} END)
 GROUP BY aluno.cod_aluno,
          pessoa.nome,
          educacenso_cod_aluno.cod_aluno_inep
ORDER BY relatorio.get_texto_sem_caracter_especial(pessoa.nome), nm_deficiencia
        ";
    }
}
