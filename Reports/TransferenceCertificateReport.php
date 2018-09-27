<?php

use iEducar\Reports\JsonDataSource;

require_once 'lib/Portabilis/Report/ReportCore.php';
require_once 'App/Model/IedFinder.php';

class TransferenceCertificateReport extends Portabilis_Report_ReportCore
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
        return 'transference-certificate';
    }

    /**
     * @inheritdoc
     */
    public function requiredArgs()
    {
        $this->addRequiredArg('ano');
        $this->addRequiredArg('instituicao');
        $this->addRequiredArg('matricula');
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
        $escola = $this->args['escola'] ?: 0;
        $ano = $this->args['ano'] ?: 0;
        $instituicao = $this->args['instituicao'] ?: 0;
        $matricula = $this->args['matricula'] ?: 0;

        return "
        SELECT fcn_upper(instituicao.nm_instituicao) AS nm_instituicao,
       fcn_upper(instituicao.nm_responsavel) AS nm_responsavel,
       coalesce(instituicao.altera_atestado_para_declaracao, false) AS altera_atestado_para_declaracao,
       escola.cod_escola as cod_escola,
       escola_ano_letivo.ano,
       aluno.cod_aluno as cod_aluno,
       matricula.aprovado AS cod_situacao,
       matricula.cod_matricula as cod_matricula,
       (SELECT public.fcn_upper(curso.nm_curso)
        FROM pmieducar.curso
      WHERE  curso.cod_curso = matricula.ref_cod_curso AND
                     curso.ativo = 1) as nm_curso,
       (SELECT serie.nm_serie
  FROM pmieducar.serie
         WHERE serie.cod_serie = matricula.ref_ref_cod_serie AND
               serie.ativo = 1) as nm_serie,

       fcn_upper(pessoa.nome) as nome,
       fcn_upper(instituicao.cidade) as cidade,
       to_char(CURRENT_DATE,'dd/mm/yyyy') as data_atual,
     to_char(fisica.data_nasc,'dd/mm/yyyy') as data_nasc,

      COALESCE((SELECT municipio.nome || ' - ' || sigla_uf
       FROM public.municipio
        WHERE municipio.idmun = fisica.idmun_nascimento),'Não informado') as municipio_uf_nascimento,

    (SELECT to_char(COALESCE(data_matricula,data_cadastro),'dd/mm/yyyy')
         FROM pmieducar.matricula mt
        WHERE mt.cod_matricula = matricula.cod_matricula AND
              mt.ativo = 1) as dt_matricula,

    (SELECT to_char(COALESCE(data_cancel),'dd/mm/yyyy')
         FROM pmieducar.matricula mt
        WHERE mt.cod_matricula = matricula.cod_matricula AND
              mt.ativo = 1) as dt_saida,

  fcn_upper(COALESCE((select pessoa_pai.nome from cadastro.pessoa as pessoa_pai where
  pessoa_pai.idpes = fisica.idpes_pai), aluno.nm_pai, '')) as nm_pai,

  fcn_upper(COALESCE((select pessoa_mae.nome from cadastro.pessoa as pessoa_mae where
  pessoa_mae.idpes = fisica.idpes_mae), aluno.nm_mae, '')) as nm_mae,

      (SELECT COALESCE((SELECT COALESCE (fcn_upper(ps.nome),fcn_upper(juridica.fantasia))
          FROM cadastro.pessoa ps,
               cadastro.juridica
         WHERE escola.ref_idpes = juridica.idpes AND
               juridica.idpes = ps.idpes AND
         ps.idpes = escola.ref_idpes),(SELECT nm_escola FROM pmieducar.escola_complemento where ref_cod_escola = escola.cod_escola))) AS nm_escola,

      (SELECT fcn_upper(substring(logradouro.idtlog from 1 for 1)) ||
              lower(substring(logradouro.idtlog, 2))
         FROM public.logradouro,
              cadastro.juridica,
              cadastro.pessoa ps,
              cadastro.endereco_pessoa
        WHERE juridica.idpes = ps.idpes AND
              ps.idpes = endereco_pessoa.idpes AND
              endereco_pessoa.idlog = logradouro.idlog AND
              juridica.idpes = escola.ref_idpes) AS tipo_logradouro,

      (SELECT COALESCE((SELECT COALESCE((SELECT logradouro.nome
         FROM public.logradouro,
              cadastro.juridica,
              cadastro.pessoa ps,
              cadastro.endereco_pessoa
        WHERE juridica.idpes = ps.idpes AND
              ps.idpes = endereco_pessoa.idpes AND
              endereco_pessoa.idlog = logradouro.idlog AND
              juridica.idpes = escola.ref_idpes),(SELECT endereco_externo.logradouro FROM cadastro.endereco_externo WHERE endereco_externo.idpes = escola.ref_idpes))),(SELECT logradouro FROM pmieducar.escola_complemento where ref_cod_escola = escola.cod_escola))) AS logradouro,

      (SELECT COALESCE((SELECT COALESCE((SELECT bairro.nome
         FROM public.municipio,
              cadastro.endereco_pessoa,
              cadastro.juridica,
              public.bairro
        WHERE endereco_pessoa.idbai = bairro.idbai AND
              bairro.idmun = municipio.idmun AND
              juridica.idpes = endereco_pessoa.idpes AND
              juridica.idpes = escola.ref_idpes),(SELECT endereco_externo.bairro FROM cadastro.endereco_externo WHERE endereco_externo.idpes = escola.ref_idpes))),(SELECT bairro FROM pmieducar.escola_complemento where ref_cod_escola = escola.cod_escola))) AS bairro,

      (SELECT COALESCE((SELECT COALESCE ((SELECT municipio.nome
         FROM public.municipio,
              cadastro.endereco_pessoa,
              cadastro.juridica,
              public.bairro
        WHERE endereco_pessoa.idbai = bairro.idbai AND
              bairro.idmun = municipio.idmun AND
              juridica.idpes = endereco_pessoa.idpes AND
              juridica.idpes = escola.ref_idpes), (SELECT endereco_externo.cidade FROM cadastro.endereco_externo WHERE endereco_externo.idpes = escola.ref_idpes))),(SELECT municipio FROM pmieducar.escola_complemento where ref_cod_escola = escola.cod_escola))) AS municipio,

      (SELECT COALESCE((SELECT COALESCE((SELECT endereco_pessoa.numero
         FROM cadastro.endereco_pessoa,
              cadastro.juridica
        WHERE juridica.idpes = endereco_pessoa.idpes AND
              juridica.idpes = escola.ref_idpes),(SELECT endereco_externo.numero FROM cadastro.endereco_externo WHERE endereco_externo.idpes = escola.ref_idpes))),(SELECT numero FROM pmieducar.escola_complemento where ref_cod_escola = escola.cod_escola))) AS numero,


      (SELECT COALESCE((SELECT COALESCE((SELECT municipio.sigla_uf
         FROM public.municipio,
              cadastro.endereco_pessoa,
              cadastro.juridica,
              public.bairro
        WHERE endereco_pessoa.idbai = bairro.idbai AND
              bairro.idmun = municipio.idmun AND
              juridica.idpes = endereco_pessoa.idpes AND
              juridica.idpes = escola.ref_idpes),(SELECT endereco_externo.sigla_uf FROM cadastro.endereco_externo WHERE endereco_externo.idpes = escola.ref_idpes))),(select inst.ref_sigla_uf from pmieducar.instituicao inst where inst.cod_instituicao = instituicao.cod_instituicao))) AS uf_municipio,

     (SELECT COALESCE((SELECT min(fone_pessoa.ddd)
         FROM cadastro.fone_pessoa,
              cadastro.juridica
        WHERE juridica.idpes = fone_pessoa.idpes AND
              juridica.idpes = escola.ref_idpes),(SELECT min(ddd_telefone) FROM pmieducar.escola_complemento where ref_cod_escola = escola.cod_escola))) AS fone_ddd,

     (SELECT COALESCE((SELECT COALESCE((SELECT to_char(endereco_pessoa.cep, '99999-999')
         FROM cadastro.endereco_pessoa,
              cadastro.juridica
        WHERE juridica.idpes = endereco_pessoa.idpes AND
              juridica.idpes = escola.ref_idpes),(SELECT to_char(endereco_externo.cep,'99999-999') FROM cadastro.endereco_externo WHERE endereco_externo.idpes = escola.ref_idpes))),(SELECT to_char(escola_complemento.cep,'99999-999') FROM pmieducar.escola_complemento where escola_complemento.ref_cod_escola = escola.cod_escola))) AS cep,


     (SELECT COALESCE((SELECT min(to_char(fone_pessoa.fone, '9999-9999'))
         FROM cadastro.fone_pessoa,
              cadastro.juridica
        WHERE juridica.idpes = fone_pessoa.idpes AND
              juridica.idpes = escola.ref_idpes),(SELECT min(to_char(telefone, '9999-9999')) FROM pmieducar.escola_complemento where escola_complemento.ref_cod_escola = escola.cod_escola))) AS fone,

   (SELECT COALESCE((SELECT ps.email
         FROM cadastro.pessoa ps,
              cadastro.juridica
        WHERE juridica.idpes = ps.idpes AND
              juridica.idpes = escola.ref_idpes),(SELECT email FROM pmieducar.escola_complemento where ref_cod_escola = escola.cod_escola))) AS email,

       to_char(current_date,'dd/mm/yyyy') AS data_atual,
       to_char(current_timestamp, 'HH24:MI:SS') AS hora_atual,

    (select cod_aluno_inep
          from modules.educacenso_cod_aluno
         where educacenso_cod_aluno.cod_aluno = aluno.cod_aluno) as cod_inep,

        aluno.aluno_estado_id as aluno_estado_id,
        (SELECT fcn_upper(p.nome) FROM cadastro.pessoa p WHERE escola.ref_idpes_gestor = p.idpes) as diretor,

        (SELECT fcn_upper(p.nome)
         FROM    cadastro.pessoa p
         INNER JOIN pmieducar.escola e ON (p.idpes = e.ref_idpes_secretario_escolar)
         WHERE e.cod_escola = {$escola}) as secretario,

           fisica.nis_pis_pasep

  FROM pmieducar.aluno,
       pmieducar.matricula,
       cadastro.fisica,
       cadastro.pessoa,
       pmieducar.instituicao,
       pmieducar.escola,
       pmieducar.escola_ano_letivo

 WHERE escola_ano_letivo.ano = {$ano} AND
       instituicao.cod_instituicao = {$instituicao} AND
       escola.cod_escola =
       CASE
          WHEN
            {$escola} > 0
          THEN
            {$escola}
          ELSE
            (select ref_ref_cod_escola from pmieducar.matricula where cod_matricula = {$matricula})
       END AND
       matricula.cod_matricula = (SELECT cod_matricula
                                   from pmieducar.matricula mt,
                        pmieducar.aluno al
                    where mt.ref_cod_aluno = matricula.ref_cod_aluno AND
                        matricula.cod_matricula = {$matricula}  AND
                      mt.ano = matricula.ano AND
                      mt.ref_ref_cod_escola = matricula.ref_ref_cod_escola AND
                      mt.ref_ref_cod_serie = matricula.ref_ref_cod_serie AND
                      mt.ativo  = 1
                      order by mt.data_exclusao DESC
                      limit 1) AND
       pessoa.idpes = fisica.idpes AND
       fisica.idpes = aluno.ref_idpes AND
       aluno.cod_aluno = matricula.ref_cod_aluno AND
       matricula.ref_cod_aluno = aluno.cod_aluno AND
       escola.ref_cod_instituicao = instituicao.cod_instituicao AND
       escola_ano_letivo.ref_cod_escola = escola.cod_escola AND
       matricula.ano = escola_ano_letivo.ano AND
       matricula.ativo  = 1 AND
       aluno.ativo = 1 AND
       escola.ativo = 1 AND
       instituicao.ativo = 1
        ";
    }
}
