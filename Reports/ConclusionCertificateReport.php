<?php

use iEducar\Reports\JsonDataSource;

require_once 'lib/Portabilis/Report/ReportCore.php';

class ConclusionCertificateReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        return 'conclusion-certificate';
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
        $instituicao = $this->args['instituicao'] ?: 0;
        $escola = $this->args['escola'] ?: 0;
        $ano = $this->args['ano'] ?: 0;
        $curso = $this->args['curso'] ?: 0;
        $serie = $this->args['serie'] ?: 0;
        $turma = $this->args['turma'] ?: 0;
        $matricula = $this->args['matricula'] ?: 0;

        return "
        SELECT public.fcn_upper(instituicao.nm_instituicao) AS nm_instituicao,
       public.fcn_upper(instituicao.nm_responsavel) AS nm_responsavel,
       escola.cod_escola as cod_escola,
       escola_ano_letivo.ano,
       curso.nm_curso as nm_curso,
       public.fcn_upper(curso.sgl_curso) as sigla_curso,
       serie.nm_serie as nm_serie,
       turma.nm_turma as nm_turma,
       COALESCE(turma_turno.nome, 'não informado') as periodo,
       aluno.cod_aluno as cod_aluno,
       matricula.cod_matricula as cod_matricula,
       public.fcn_upper(pessoa.nome) as nome,
       public.fcn_upper(instituicao.cidade) as cidade,
       public.data_para_extenso(CURRENT_DATE) as data_atual,
     to_char(fisica.data_nasc,'dd/mm/yyyy') as data_nasc,
      COALESCE((SELECT municipio.nome || ' - ' || sigla_uf
       FROM public.municipio
        WHERE municipio.idmun = fisica.idmun_nascimento),'Não informado') as municipio_uf_nascimento,

    (SELECT to_char(coalesce(mt.data_matricula, mt.data_cadastro),'dd/mm/yyyy')
         FROM pmieducar.matricula mt
        WHERE mt.cod_matricula = matricula.cod_matricula AND
              mt.ativo = 1 AND
              mt.ultima_matricula = 1) as dt_matricula,
     matricula.ano as matricula_ano,
       public.fcn_upper(instituicao.cidade) as cidade,

  fcn_upper(COALESCE((select pessoa_pai.nome from cadastro.pessoa as pessoa_pai where
  pessoa_pai.idpes = fisica.idpes_pai), aluno.nm_pai, '')) as nm_pai,

  fcn_upper(COALESCE((select pessoa_mae.nome from cadastro.pessoa as pessoa_mae where
  pessoa_mae.idpes = fisica.idpes_mae), aluno.nm_mae, '')) as nm_mae,

      relatorio.get_nome_escola(escola.cod_escola) AS nm_escola,

       to_char(current_date,'dd/mm/yyyy') AS data_atual,
       to_char(current_timestamp, 'HH24:MI:SS') AS hora_atual,

   (SELECT cod_aluno_inep
      FROM modules.educacenso_cod_aluno
     WHERE educacenso_cod_aluno.cod_aluno = aluno.cod_aluno) as cod_inep,

   (SELECT MAX(f.nis_pis_pasep)
      FROM pmieducar.matricula m,
           pmieducar.aluno a,
           cadastro.fisica f
     WHERE matricula.cod_matricula = m.cod_matricula
       AND m.ref_cod_aluno = a.cod_aluno
       AND a.ref_idpes = f.idpes) AS cod_nis,

        aluno.aluno_estado_id as aluno_estado_id,
        trunc(modules.frequencia_da_matricula(matricula.cod_matricula)::numeric,2) as frequencia,
        (SELECT p.nome FROM cadastro.pessoa p WHERE escola.ref_idpes_gestor = p.idpes) as diretor
  FROM pmieducar.instituicao
 INNER JOIN pmieducar.escola ON (escola.ref_cod_instituicao = instituicao.cod_instituicao)
 INNER JOIN pmieducar.escola_ano_letivo ON (escola_ano_letivo.ref_cod_escola = escola.cod_escola)
 INNER JOIN pmieducar.escola_curso ON (escola_curso.ref_cod_escola = escola.cod_escola AND escola_curso.ativo = 1)
 INNER JOIN pmieducar.escola_serie ON (escola_serie.ref_cod_escola = escola.cod_escola AND escola_serie.ativo = 1)
 INNER JOIN pmieducar.serie ON (serie.cod_serie = escola_serie.ref_cod_serie
				AND serie.ref_cod_curso = escola_curso.ref_cod_curso)
 INNER JOIN pmieducar.turma ON (turma.ref_ref_cod_escola = escola.cod_escola
				AND turma.ref_ref_cod_serie = serie.cod_serie
				AND turma.ref_cod_curso = escola_curso.ref_cod_curso
				AND turma.ativo = 1)
 INNER JOIN pmieducar.curso ON (escola_curso.ref_cod_escola = escola.cod_escola
				AND turma.ref_cod_curso = curso.cod_curso)
 INNER JOIN pmieducar.matricula ON (matricula.ref_ref_cod_escola = escola.cod_escola
				    AND matricula.ref_ref_cod_serie = serie.cod_serie
				    AND matricula.ativo = 1)
 INNER JOIN pmieducar.matricula_turma ON (matricula_turma.ref_cod_turma = turma.cod_turma
					  AND matricula_turma.ref_cod_matricula = matricula.cod_matricula)
  LEFT JOIN pmieducar.turma_turno ON (turma_turno.id = turma.turma_turno_id
				      AND turma.cod_turma = matricula_turma.ref_cod_turma)
 INNER JOIN pmieducar.aluno ON (matricula.ref_cod_aluno = aluno.cod_aluno)
 INNER JOIN cadastro.fisica ON (fisica.idpes = aluno.ref_idpes)
 INNER JOIN cadastro.pessoa ON (pessoa.idpes = fisica.idpes)
 INNER JOIN relatorio.view_situacao ON (view_situacao.cod_matricula = matricula.cod_matricula
                                        AND view_situacao.cod_turma = turma.cod_turma
                                        AND view_situacao.cod_situacao = 10)

 WHERE instituicao.cod_instituicao = {$instituicao}
   AND escola.cod_escola = {$escola}
   AND escola_ano_letivo.ano = {$ano}
   AND matricula.ano = escola_ano_letivo.ano
   AND (CASE WHEN {$curso} = 0 THEN true ELSE curso.cod_curso = {$curso} END)
   AND (CASE WHEN {$serie} = 0 THEN true ELSE serie.cod_serie = {$serie} END)
   AND (CASE WHEN {$turma} = 0 THEN true ELSE turma.cod_turma = {$turma} END)
   AND (CASE WHEN {$matricula} = 0 THEN true ELSE matricula.cod_matricula = {$matricula} END)
ORDER BY pessoa.nome
        ";
    }
}
