<?php

use iEducar\Reports\JsonDataSource;

require_once 'lib/Portabilis/Report/ReportCore.php';
require_once 'App/Model/IedFinder.php';

class LibraryDevolutionsReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        return 'library-devolutions';
    }

    /**
     * @inheritdoc
     */
    public function requiredArgs()
    {
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
        $instituicao = $this->args['instituicao'] ?: 0;
        $escola = $this->args['escola'] ?: 0;
        $dt_inicial = $this->args['dt_inicial'] ?: 0;
        $dt_final = $this->args['dt_final'] ?: 0;
        $cliente = $this->args['cliente'] ?: 0;

        return "
        SELECT public.fcn_upper(instituicao.nm_instituicao) as nm_instituicao,
       exemplar_emprestimo.cod_emprestimo,
       exemplar_emprestimo.ref_cod_cliente,
       exemplar_emprestimo.ref_cod_exemplar,
       (select acervo.titulo
          from pmieducar.acervo,
               pmieducar.exemplar
         where acervo.cod_acervo = exemplar.ref_cod_acervo AND
               exemplar.ativo = 1 AND
               acervo.ativo = 1 AND
               exemplar_emprestimo.ref_cod_exemplar = exemplar.cod_exemplar) as titulo,

       (select cadastro.pessoa.nome
          from cadastro.pessoa,
               cadastro.fisica,
               pmieducar.cliente
         where pessoa.idpes = fisica.idpes AND
               fisica.idpes = cliente.ref_idpes AND
               cliente.cod_cliente = exemplar_emprestimo.ref_cod_cliente) as cliente,

       to_char(data_retirada,'dd/mm/yyyy') as dt_retirada,
       to_char(data_devolucao,'dd/mm/yyyy') as dt_devolucao,
       exemplar_emprestimo.valor_multa,

       (SELECT biblioteca.nm_biblioteca
          FROM pmieducar.biblioteca
         WHERE acervo.ref_cod_biblioteca = biblioteca.cod_biblioteca) as nm_biblioteca,

      (SELECT COALESCE((SELECT COALESCE (fcn_upper(ps.nome),fcn_upper(juridica.fantasia))
          FROM cadastro.pessoa ps,
               cadastro.juridica
         WHERE escola.ref_idpes = juridica.idpes AND
               juridica.idpes = ps.idpes AND
	     ps.idpes = escola.ref_idpes),(SELECT nm_escola
                                               FROM pmieducar.escola_complemento
                                              WHERE ref_cod_escola = escola.cod_escola))) AS nm_escola,

       (SELECT COALESCE((SELECT COALESCE((SELECT logradouro.nome
         FROM public.logradouro,
              cadastro.juridica,
              cadastro.pessoa ps,
              cadastro.endereco_pessoa
        WHERE juridica.idpes = ps.idpes AND
              ps.idpes = endereco_pessoa.idpes AND
              endereco_pessoa.idlog = logradouro.idlog AND
              juridica.idpes = escola.ref_idpes),(SELECT endereco_externo.logradouro
                                                    FROM cadastro.endereco_externo
                                                   WHERE endereco_externo.idpes = escola.ref_idpes))),(SELECT logradouro FROM pmieducar.escola_complemento where ref_cod_escola = escola.cod_escola))) AS logradouro,

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

     (SELECT COALESCE((SELECT min(to_char(fone_pessoa.ddd,'99'))
         FROM cadastro.fone_pessoa,
              cadastro.juridica
        WHERE juridica.idpes = fone_pessoa.idpes AND
              juridica.idpes = escola.ref_idpes),COALESCE((SELECT min(to_char(ddd_telefone,'99')) FROM pmieducar.escola_complemento where ref_cod_escola = escola.cod_escola),''))) AS fone_ddd,

     (SELECT COALESCE((SELECT COALESCE((SELECT to_char(endereco_pessoa.cep, '99999-999')
         FROM cadastro.endereco_pessoa,
              cadastro.juridica
        WHERE juridica.idpes = endereco_pessoa.idpes AND
              juridica.idpes = escola.ref_idpes),(SELECT to_char(endereco_externo.cep,'99999-999') FROM cadastro.endereco_externo WHERE endereco_externo.idpes = escola.ref_idpes))),(SELECT to_char(escola_complemento.cep,'99999-999') FROM pmieducar.escola_complemento where escola_complemento.ref_cod_escola = escola.cod_escola))) AS cep,


     (SELECT COALESCE((SELECT min(to_char(fone_pessoa.fone, '9999-9999'))
         FROM cadastro.fone_pessoa,
              cadastro.juridica
        WHERE juridica.idpes = fone_pessoa.idpes AND
              juridica.idpes = escola.ref_idpes),COALESCE((SELECT min(to_char(telefone, '9999-9999')) FROM pmieducar.escola_complemento where escola_complemento.ref_cod_escola = escola.cod_escola),''))) AS fone,

   (SELECT COALESCE((SELECT ps.email
         FROM cadastro.pessoa ps,
              cadastro.juridica
        WHERE juridica.idpes = ps.idpes AND
              juridica.idpes = escola.ref_idpes),(SELECT email FROM pmieducar.escola_complemento where ref_cod_escola = escola.cod_escola))) AS email,

   (select acervo_autor.nm_autor
      from pmieducar.acervo_autor,
           pmieducar.acervo_acervo_autor
     where acervo_acervo_autor.ref_cod_acervo_autor = acervo_autor.cod_acervo_autor AND
           acervo_acervo_autor.ref_cod_acervo = acervo.cod_acervo AND
           acervo_acervo_autor.principal = 1) as autor,

(select min(fone)
   from cadastro.fone_pessoa,
        cadastro.pessoa,
        cadastro.fisica,
        pmieducar.cliente
  where fone_pessoa.idpes = pessoa.idpes AND
        pessoa.idpes = fisica.idpes AND
        fisica.idpes = cliente.ref_idpes AND
        cliente.cod_cliente = exemplar_emprestimo.ref_cod_cliente) as telefone,

(select coalesce((select logradouro.nome
  from public.logradouro,
       cadastro.endereco_pessoa,
       cadastro.pessoa,
       cadastro.fisica,
       pmieducar.cliente
 where logradouro.idlog = endereco_pessoa.idlog AND
       endereco_pessoa.idpes = pessoa.idpes AND
       pessoa.idpes = fisica.idpes AND
       fisica.idpes = cliente.ref_idpes AND
       cliente.cod_cliente = exemplar_emprestimo.ref_cod_cliente),(select logradouro
   from cadastro.endereco_externo,
        cadastro.pessoa,
        cadastro.fisica,
        pmieducar.cliente
  where endereco_externo.idpes = pessoa.idpes AND
        pessoa.idpes = fisica.idpes AND
        fisica.idpes = cliente.ref_idpes AND
        cliente.cod_cliente = exemplar_emprestimo.ref_cod_cliente))) as endereco,

   (SELECT pessoa.email
         FROM cadastro.pessoa,
              cadastro.fisica,
              pmieducar.cliente
        WHERE fisica.idpes = pessoa.idpes AND
              fisica.idpes = cliente.ref_idpes AND
              cliente.cod_cliente = exemplar_emprestimo.ref_cod_cliente) AS email_cliente


  FROM pmieducar.acervo,
       pmieducar.biblioteca,
       pmieducar.instituicao,
       pmieducar.escola,
       pmieducar.exemplar,
       pmieducar.exemplar_emprestimo
 WHERE instituicao.cod_instituicao = {$instituicao} AND
       instituicao.cod_instituicao = biblioteca.ref_cod_instituicao AND
       acervo.ref_cod_biblioteca = biblioteca.cod_biblioteca AND
       escola.cod_escola = {$escola} AND
       biblioteca.ref_cod_escola = escola.cod_escola AND
       acervo.ativo = 1 AND
       exemplar.ref_cod_acervo = acervo.cod_acervo AND
       exemplar.cod_exemplar = exemplar_emprestimo.ref_cod_exemplar AND
       (date(exemplar_emprestimo.data_devolucao) >= (substr('{$dt_inicial}',7,10) || '-' || substr('{$dt_inicial}',4,2) || '-' || substr('{$dt_inicial}',1,2))::date) AND

       (date(exemplar_emprestimo.data_devolucao) <= (substr('{$dt_final}',7,10) || '-' || substr('{$dt_final}',4,2) || '-' || substr('{$dt_final}',1,2))::date) AND
        exemplar_emprestimo.data_devolucao is not null AND
       (SELECT CASE WHEN {$cliente} <> 0 THEN
                     exemplar_emprestimo.ref_cod_cliente = {$cliente}
                   ELSE
                     exemplar_emprestimo.ref_cod_cliente = exemplar_emprestimo.ref_cod_cliente
                   END)
        ";
    }
}
