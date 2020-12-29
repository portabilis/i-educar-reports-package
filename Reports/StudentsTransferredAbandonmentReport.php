<?php

use iEducar\Reports\JsonDataSource;

require_once 'lib/Portabilis/Report/ReportCore.php';
require_once 'App/Model/IedFinder.php';

class StudentsTransferredAbandonmentReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        return 'students-transferred-abandonment';
    }

    /**
     * @inheritdoc
     */
    public function requiredArgs()
    {
        $this->addRequiredArg('ano');
        $this->addRequiredArg('instituicao');
        $this->addRequiredArg('dt_inicial');
        $this->addRequiredArg('dt_final');
        $this->addRequiredArg('situacao');
    }

    /**
     * Retorna o SQL para buscar os dados do relatório principal.
     *
     * @return string
     */
    public function getSqlMainReport()
    {
        $dt_inicial = $this->args['dt_inicial'];
        $dt_final = $this->args['dt_final'];
        $instituicao = $this->args['instituicao'] ?: 0;
        $escola = $this->args['escola'] ?: 0;
        $curso = $this->args['curso'] ?: 0;
        $serie = $this->args['serie'] ?: 0;
        $ano = $this->args['ano'] ?: 0;
        $situacao = $this->args['situacao'] ?: 0;

        return "
SELECT public.fcn_upper(nm_instituicao) AS nome_instituicao,
       public.fcn_upper(nm_responsavel) AS nome_responsavel,
       instituicao.cidade AS cidade_instituicao,
       public.fcn_upper(ref_sigla_uf) AS uf_instituicao,
       aluno.cod_aluno,
       escola.cod_escola,

      (SELECT nm_serie
         FROM pmieducar.serie
        WHERE serie.cod_serie = matricula.ref_ref_cod_serie) AS serie,

      (SELECT view_dados_escola.nome
          FROM relatorio.view_dados_escola
        WHERE view_dados_escola.cod_escola = escola.cod_escola) AS nm_escola,

(case when (
		(select ref_cod_escola_destino from pmieducar.transferencia_solicitacao where ref_cod_matricula_saida = matricula.cod_matricula and ativo = 1 order by data_transferencia limit 1) = null
             or (select ref_cod_escola_destino from pmieducar.transferencia_solicitacao where ref_cod_matricula_saida = matricula.cod_matricula and ativo = 1 order by data_transferencia limit 1) = 0
           )
		then
		     (select escola_destino_externa
		       from pmieducar.transferencia_solicitacao
		      where ref_cod_matricula_saida = matricula.cod_matricula
		        and transferencia_solicitacao.ativo = 1
		   order by data_transferencia limit 1)
		else
		     (select view_dados_escola.nome
		       from relatorio.view_dados_escola
		 inner join pmieducar.transferencia_solicitacao on (view_dados_escola.cod_escola = transferencia_solicitacao.ref_cod_escola_destino)
		      where ref_cod_matricula_saida = matricula.cod_matricula
		        and transferencia_solicitacao.ativo = 1
		   order by data_transferencia limit 1)
      end) as nome_escola_destino,

      (case when (
		     (select ref_cod_escola_destino from pmieducar.transferencia_solicitacao where ref_cod_matricula_saida = matricula.cod_matricula and ativo = 1 order by data_transferencia limit 1) = null
		  or (select ref_cod_escola_destino from pmieducar.transferencia_solicitacao where ref_cod_matricula_saida = matricula.cod_matricula and ativo = 1 order by data_transferencia limit 1) = 0
                 )
		then
		     (select municipio_escola_destino_externa
		       from pmieducar.transferencia_solicitacao
		      where ref_cod_matricula_saida = matricula.cod_matricula
 		        and transferencia_solicitacao.ativo = 1
		   order by data_transferencia limit 1)
		else
		     (select view_dados_escola.municipio
		       from relatorio.view_dados_escola
		 inner join pmieducar.transferencia_solicitacao on (view_dados_escola.cod_escola = transferencia_solicitacao.ref_cod_escola_destino)
		      where ref_cod_matricula_saida = matricula.cod_matricula
		        and transferencia_solicitacao.ativo = 1
		   order by data_transferencia limit 1)
      end) as municipio_escola_destino,

      (case when (
		    (select ref_cod_escola_destino from pmieducar.transferencia_solicitacao where ref_cod_matricula_saida = matricula.cod_matricula and ativo = 1 order by data_transferencia limit 1) = null
		 or (select ref_cod_escola_destino from pmieducar.transferencia_solicitacao where ref_cod_matricula_saida = matricula.cod_matricula and ativo = 1 order by data_transferencia limit 1) = 0
		 )
		then
		     (select estado_escola_destino_externa
		       from pmieducar.transferencia_solicitacao
		      where ref_cod_matricula_saida = matricula.cod_matricula
		        and transferencia_solicitacao.ativo = 1
		   order by data_transferencia limit 1)
		else
		     (select view_dados_escola.uf_municipio
		       from relatorio.view_dados_escola
		 inner join pmieducar.transferencia_solicitacao on (view_dados_escola.cod_escola = transferencia_solicitacao.ref_cod_escola_destino)
		      where ref_cod_matricula_saida = matricula.cod_matricula
		        and transferencia_solicitacao.ativo = 1
		   order by data_transferencia limit 1)
      end) as estado_escola_destino,

       to_char(CURRENT_DATE,'dd/mm/yyyy') AS data_atual,
       to_char(current_timestamp, 'HH24:MI:SS') AS hora_atual,
       matricula.cod_matricula,

       (SELECT translate(pessoa.nome,'åáàãâäéèêëíìîïóòõôöúùüûçÿýñÅÁÀÃÂÄÉÈÊËÍÌÎÏÓÒÕÔÖÚÙÛÜÇÝÑ', 'aaaaaaeeeeiiiiooooouuuucyynAAAAAAEEEEIIIIOOOOOUUUUCYN')
          FROM cadastro.pessoa,
               cadastro.fisica
         WHERE aluno.ref_idpes = fisica.idpes AND
               fisica.idpes = pessoa.idpes) as nome_aluno,

       (SELECT CASE WHEN fisica.sexo = 'M' THEN
                    'Masculino'
                ELSE
                    'Feminino'
                END
          FROM cadastro.pessoa,
               cadastro.fisica
         WHERE aluno.ref_idpes = fisica.idpes AND
               fisica.idpes = pessoa.idpes) as sexo,
       (SELECT to_char(fisica.data_nasc,'dd/mm/yyyy')
          FROM cadastro.pessoa,
               cadastro.fisica
         WHERE aluno.ref_idpes = fisica.idpes AND
               fisica.idpes = pessoa.idpes) as data_nasc,
       (SELECT educacenso_cod_aluno.cod_aluno_inep
          FROM modules.educacenso_cod_aluno
         WHERE educacenso_cod_aluno.cod_aluno = aluno.cod_aluno) as cod_aluno_inep,

         matricula.aprovado AS cod_situacao,

 (SELECT CASE WHEN matricula.aprovado = 4 THEN 'Trs'
              WHEN matricula.aprovado = 6 THEN 'Aba'
          END) as situacao,

(select max(transferencia_solicitacao.observacao)
  from pmieducar.transferencia_solicitacao
 where transferencia_solicitacao.ativo = 1 AND
       transferencia_solicitacao.data_exclusao IS NULL AND
       transferencia_solicitacao.ref_cod_matricula_saida = matricula.cod_matricula) as observacao,

(select m.observacao
   from pmieducar.matricula as m
  where m.ativo = 1
    and m.cod_matricula = matricula.cod_matricula
    and m.aprovado = 6) as observacao_abandono,

(select COALESCE((select min(turma.nm_turma)
  from  pmieducar.turma,
        pmieducar.matricula_turma
  where turma.cod_turma = matricula_turma.ref_cod_turma AND
        matricula_turma.ref_cod_matricula = matricula.cod_matricula and matricula_turma.ativo = 1),
(select min(turma.nm_turma)
  from  pmieducar.turma,
        pmieducar.matricula_turma
  where turma.cod_turma = matricula_turma.ref_cod_turma AND
        matricula_turma.ref_cod_matricula = matricula.cod_matricula
        and matricula_turma.ativo = 0
       AND (date(coalesce(matricula.data_cancel, matricula.data_exclusao)) >= (substr('{$dt_inicial}',7,10) || '-' || substr('{$dt_inicial}',4,2) || '-' || substr('{$dt_inicial}',1,2))::date)
       AND (date(coalesce(matricula.data_cancel, matricula.data_exclusao)) <= (substr('{$dt_final}',7,10) || '-' || substr('{$dt_final}',4,2) || '-' || substr('{$dt_final}',1,2))::date)))) as nome_turma,

coalesce(matricula.data_cancel, matricula.data_exclusao) as data_exclusao

  FROM pmieducar.instituicao,
       pmieducar.matricula,
       pmieducar.aluno,
       pmieducar.escola
 WHERE instituicao.cod_instituicao = {$instituicao} AND
       instituicao.cod_instituicao = escola.ref_cod_instituicao AND
       (SELECT CASE WHEN {$escola} = 0 THEN
                 matricula.ref_ref_cod_escola = escola.cod_escola
               ELSE
                 matricula.ref_ref_cod_escola = escola.cod_escola AND
                 escola.cod_escola = {$escola}
               END) AND

              CASE WHEN {$curso} <> 0 THEN
	       matricula.ref_cod_curso = {$curso}
               ELSE
                 1=1
               END AND

              CASE WHEN {$serie} <> 0 THEN
                 matricula.ref_ref_cod_serie = {$serie}
              ELSE
                 1=1
              END AND

       matricula.ref_cod_aluno = aluno.cod_aluno AND
       matricula.ativo = 1 AND
       aluno.ativo = 1 AND
       matricula.ano = {$ano} AND
       (date(coalesce(matricula.data_cancel, matricula.data_exclusao)) >= (substr('{$dt_inicial}',7,10) || '-' || substr('{$dt_inicial}',4,2) || '-' ||        substr('{$dt_inicial}',1,2))::date) AND
       (date(coalesce(matricula.data_cancel, matricula.data_exclusao)) <= (substr('{$dt_final}',7,10) || '-' || substr('{$dt_final}',4,2) || '-' ||             substr('{$dt_final}',1,2))::date) AND

      (SELECT CASE WHEN {$situacao} = 9 THEN
                   matricula.aprovado in ('4','6')
              ELSE
                   matricula.aprovado = (SELECT CASE WHEN {$situacao} = 1 THEN
                                                   6
                                                ELSE
                                                   4
                                                END)
              END)
ORDER BY nome_turma, nm_escola, nome_aluno
        ";
    }
}
