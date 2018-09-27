<?php

use iEducar\Reports\JsonDataSource;

require_once 'lib/Portabilis/Report/ReportCore.php';
require_once 'App/Model/IedFinder.php';

class SchoolsReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        return 'schools';
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
     * TODO #refatorar
     *
     * @return string
     */
    public function getSqlMainReport()
    {
        $curso = $this->args['curso'] ?: 0;
        $instituicao = $this->args['instituicao'] ?: 0;
        $ano = $this->args['ano'] ?: date('Y');

        return "
        select pessoa.email as email,
(SELECT
              initcap(lower(logradouro.idtlog))
         FROM public.logradouro,
              cadastro.juridica,
              cadastro.pessoa ps,
              cadastro.endereco_pessoa
        WHERE juridica.idpes = ps.idpes AND
              ps.idpes = endereco_pessoa.idpes AND
              endereco_pessoa.idlog = logradouro.idlog AND
              juridica.idpes = escola.ref_idpes) as tipo_logradouro,

(SELECT fone_pessoa.ddd
         FROM cadastro.fone_pessoa,
              cadastro.juridica
        WHERE juridica.idpes = fone_pessoa.idpes AND
              juridica.idpes = escola.ref_idpes AND tipo = 3 ) AS cel_ddd,

 (SELECT to_char(fone_pessoa.fone, '99999-9999')
         FROM cadastro.fone_pessoa,
              cadastro.juridica
        WHERE juridica.idpes = fone_pessoa.idpes AND
              juridica.idpes = escola.ref_idpes and tipo = 3) AS cel,

 (SELECT COALESCE((SELECT COALESCE((SELECT initcap(lower(logradouro.nome))
         FROM public.logradouro,
              cadastro.juridica,
              cadastro.pessoa ps,
              cadastro.endereco_pessoa
        WHERE juridica.idpes = ps.idpes AND
              ps.idpes = endereco_pessoa.idpes AND
              endereco_pessoa.idlog = logradouro.idlog AND
              juridica.idpes = escola.ref_idpes),(SELECT initcap(lower(endereco_externo.logradouro)) FROM cadastro.endereco_externo WHERE endereco_externo.idpes = escola.ref_idpes))),(SELECT logradouro FROM pmieducar.escola_complemento where ref_cod_escola = escola.cod_escola))) AS logradouro,

(SELECT COALESCE((SELECT COALESCE((SELECT initcap(lower(bairro.nome))
         FROM public.municipio,
              cadastro.endereco_pessoa,
              cadastro.juridica,
              public.bairro
        WHERE endereco_pessoa.idbai = bairro.idbai AND
              bairro.idmun = municipio.idmun AND
              juridica.idpes = endereco_pessoa.idpes AND
              juridica.idpes = escola.ref_idpes),(SELECT initcap(lower(endereco_externo.bairro)) FROM cadastro.endereco_externo WHERE endereco_externo.idpes = escola.ref_idpes))),(SELECT bairro FROM pmieducar.escola_complemento where ref_cod_escola = escola.cod_escola))) AS bairro,

(SELECT COALESCE((SELECT COALESCE((SELECT endereco_pessoa.numero
         FROM cadastro.endereco_pessoa,
              cadastro.juridica
        WHERE juridica.idpes = endereco_pessoa.idpes AND
              juridica.idpes = escola.ref_idpes),(SELECT endereco_externo.numero FROM cadastro.endereco_externo WHERE endereco_externo.idpes = escola.ref_idpes))),(SELECT numero FROM pmieducar.escola_complemento where ref_cod_escola = escola.cod_escola))) AS numero,

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

(SELECT COALESCE((SELECT min(to_char(fone_pessoa.fone, '99999-9999'))
         FROM cadastro.fone_pessoa,
              cadastro.juridica
        WHERE juridica.idpes = fone_pessoa.idpes AND
              juridica.idpes = escola.ref_idpes),(SELECT min(to_char(telefone, '99999-9999')) FROM pmieducar.escola_complemento where escola_complemento.ref_cod_escola = escola.cod_escola))) AS fone,

               instituicao.ref_sigla_uf as uf,
  instituicao.cidade,
       (SELECT educacenso_cod_escola.cod_escola_inep
          FROM modules.educacenso_cod_escola
         WHERE educacenso_cod_escola.cod_escola = escola.cod_escola) AS cod_escola_inep,

      (SELECT COALESCE((SELECT COALESCE (fcn_upper(ps.nome),fcn_upper(juridica.fantasia))
          FROM cadastro.pessoa ps,
               cadastro.juridica
         WHERE escola.ref_idpes = juridica.idpes AND
               juridica.idpes = ps.idpes AND
	       ps.idpes = escola.ref_idpes),(SELECT nm_escola
                                               FROM pmieducar.escola_complemento
                                              WHERE ref_cod_escola = escola.cod_escola))) AS nm_escola,
       to_char(current_date,'dd/mm/yyyy') AS data_atual,
       to_char(current_timestamp, 'HH24:MI:SS') AS hora_atual,
       (SELECT CASE WHEN {$curso} = 0 THEN ''
                   ELSE ' no curso '||(select nm_curso from curso where cod_curso = {$curso})
          END) as curso

from pmieducar.instituicao,
       pmieducar.escola,
       cadastro.pessoa,
       pmieducar.escola_ano_letivo

where instituicao.cod_instituicao = {$instituicao} AND
       escola.ativo = 1 AND
       escola.ref_cod_instituicao = instituicao.cod_instituicao AND
       escola.ativo = 1 AND
       escola.ref_idpes = pessoa.idpes AND
       escola_ano_letivo.ref_cod_escola = escola.cod_escola AND
       escola_ano_letivo.ano = {$ano} AND
       escola_ano_letivo.ativo = 1 AND
       (SELECT CASE WHEN {$curso} = 0 THEN 1 = 1
                   ELSE (select 1 from pmieducar.escola_curso ec WHERE ec.ref_cod_escola = escola.cod_escola AND ec.ref_cod_curso = {$curso}) IS NOT NULL
          END)

ORDER BY nm_escola
        ";
    }
}
