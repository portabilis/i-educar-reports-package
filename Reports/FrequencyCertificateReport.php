<?php

use iEducar\Reports\JsonDataSource;

require_once 'lib/Portabilis/Report/ReportCore.php';
require_once 'App/Model/IedFinder.php';

class FrequencyCertificateReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @var string
     */
    private $modelo;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        return 'frequency-certificate';
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
        $instituicao = $this->args['instituicao'] ?: 0;
        $escola = $this->args['escola'] ?: 0;
        $curso = $this->args['curso'] ?: 0;
        $serie = $this->args['serie'] ?: 0;
        $turma = $this->args['turma'] ?: 0;
        $matricula = $this->args['matricula'] ?: 0;
        $ano = $this->args['ano'] ?: 0;

        return "
SELECT vde.cod_escola        AS cod_escola,
       fcn_upper(pessoa_gestor.nome)    AS diretor,
       fcn_upper(pessoa_secr.nome)      AS secretario,
       coalesce(instituicao.altera_atestado_para_declaracao, false) AS altera_atestado_para_declaracao,
       aluno.cod_aluno       AS cod_aluno,
       public.fcn_upper(pessoa.nome)            AS nome,
       relatorio.get_mae_aluno(aluno.cod_aluno) AS nm_mae,
       relatorio.get_pai_aluno(aluno.cod_aluno) AS nm_pai,
       to_char(fisica.data_nasc,'dd/mm/yyyy')   AS data_nasc,
       instituicao.cidade    AS cidade,
       municipio.nome || ' - ' || municipio.sigla_uf AS municipio_uf_nascimento,
       aluno.aluno_estado_id AS aluno_estado_id,
       curso.nm_curso        AS nm_curso,
       serie.nm_serie        AS nm_serie,
       (SELECT DISTINCT '' || (replace(textcat_all(turma.nm_turma),' <br> ',' e '))) AS nm_turma,
(SELECT DISTINCT '' || (replace(textcat_all((CASE WHEN turma_turno.id = 4 THEN (SELECT nome FROM pmieducar.turma_turno WHERE id = COALESCE(matricula_turma.turno_id,4)) ELSE turma_turno.nome END)),' <br> ',' e '))) AS periodo,
       (CASE WHEN modules.frequencia_da_matricula(matricula.cod_matricula) = 100 THEN
	  100
                ELSE round(modules.frequencia_da_matricula(matricula.cod_matricula)::numeric, 2) END) AS frequencia,
       educacenso_cod_aluno.cod_aluno_inep AS cod_inep,
       fisica.nis_pis_pasep                AS cod_nis,
       to_char(CURRENT_DATE,'dd/mm/yyyy')  AS data_atual
  FROM  pmieducar.instituicao
 INNER JOIN pmieducar.escola ON (escola.ref_cod_instituicao = instituicao.cod_instituicao)
 INNER JOIN relatorio.view_dados_escola vde ON (vde.cod_escola = escola.cod_escola)
  LEFT JOIN cadastro.pessoa pessoa_gestor ON (pessoa_gestor.idpes = escola.ref_idpes_gestor)
  LEFT JOIN cadastro.pessoa pessoa_secr ON (pessoa_secr.idpes = escola.ref_idpes_secretario_escolar)
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
 INNER JOIN pmieducar.matricula_turma ON (matricula_turma.ref_cod_turma = turma.cod_turma
                                                                     AND matricula_turma.ativo = 1)
 INNER JOIN pmieducar.matricula ON (matricula.cod_matricula = matricula_turma.ref_cod_matricula
			     AND matricula.ativo = 1)
 INNER JOIN pmieducar.aluno ON (aluno.cod_aluno = matricula.ref_cod_aluno)
  LEFT JOIN modules.educacenso_cod_aluno ON (educacenso_cod_aluno.cod_aluno = aluno.cod_aluno)
 INNER JOIN cadastro.pessoa ON (pessoa.idpes = aluno.ref_idpes)
 INNER JOIN cadastro.fisica ON (fisica.idpes = aluno.ref_idpes)
  LEFT JOIN public.municipio ON (municipio.idmun = fisica.idmun_nascimento)
 WHERE instituicao.cod_instituicao = {$instituicao}
   AND (CASE WHEN {$escola} = 0
             THEN TRUE
        ELSE
             escola.cod_escola = {$escola}
        END)
   AND (CASE WHEN {$curso} = 0
             THEN TRUE
        ELSE
             curso.cod_curso = {$curso}
        END)
   AND (CASE WHEN {$serie} = 0
             THEN TRUE
        ELSE
             serie.cod_serie = {$serie}
        END)
   AND (CASE WHEN {$turma} = 0
             THEN TRUE
        ELSE
             turma.cod_turma = {$turma}
        END)
   AND (CASE WHEN {$matricula} = 0
             THEN TRUE
        ELSE
             matricula.cod_matricula = {$matricula}
        END)
   AND turma.ano = {$ano}
GROUP BY vde.cod_escola,
       pessoa_gestor.nome,
       pessoa_secr.nome,
       aluno.cod_aluno,
       pessoa.nome,
       fisica.data_nasc,
       instituicao.cidade,
       municipio.nome,
       municipio.sigla_uf,
       aluno.aluno_estado_id,
       educacenso_cod_aluno.cod_aluno_inep,
       fisica.nis_pis_pasep,
       curso.nm_curso,
       serie.nm_serie,
       matricula.cod_matricula,
       altera_atestado_para_declaracao
ORDER BY nome
        ";
    }
}
