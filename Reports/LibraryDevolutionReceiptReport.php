<?php

use iEducar\Reports\JsonDataSource;

require_once 'lib/Portabilis/Report/ReportCore.php';
require_once 'App/Model/IedFinder.php';

class LibraryDevolutionReceiptReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        return 'library-devolution-receipt-with-copy';
    }

    /**
     * @inheritdoc
     */
    public function requiredArgs()
    {
        $this->addRequiredArg('instituicao');
        $this->addRequiredArg('escola');
        $this->addRequiredArg('cliente');
    }

    /**
     * @inheritdoc
     */
    public function getJsonData()
    {
        $queryReceiptReport = $this->getSqlReceiptReport();

        return [
            'main' => [1],
            'receipt' => Portabilis_Utils_Database::fetchPreparedQuery($queryReceiptReport),
        ];
    }

    public function getSqlReceiptReport()
    {
        $instituicao = $this->args['instituicao'] ?: 0;
        $escola = $this->args['escola'] ?: 0;
        $dt_inicial = $this->args['dt_inicial'] ?: 0;
        $dt_final = $this->args['dt_final'] ?: 0;
        $cliente = $this->args['cliente'] ?: 0;

        return "
        SELECT public.fcn_upper(instituicao.nm_instituicao) AS nm_instituicao,
       exemplar_emprestimo.cod_emprestimo,
       exemplar_emprestimo.ref_cod_cliente,
       exemplar_emprestimo.ref_cod_exemplar,
       exemplar.tombo,
  (SELECT acervo.titulo
   FROM pmieducar.acervo,
        pmieducar.exemplar
   WHERE acervo.cod_acervo = exemplar.ref_cod_acervo
     AND exemplar.ativo = 1
     AND acervo.ativo = 1
     AND exemplar_emprestimo.ref_cod_exemplar = exemplar.cod_exemplar) AS titulo,

  (SELECT cadastro.pessoa.nome
   FROM cadastro.pessoa,
        cadastro.fisica,
        pmieducar.cliente
   WHERE pessoa.idpes = fisica.idpes
     AND fisica.idpes = cliente.ref_idpes
     AND cliente.cod_cliente = exemplar_emprestimo.ref_cod_cliente) AS cliente,

  (SELECT to_char(fisica.cpf,'000\".\"000\".\"000\"-\"00')
   FROM cadastro.fisica,
        pmieducar.cliente
   WHERE fisica.idpes = cliente.ref_idpes
     AND cliente.cod_cliente = exemplar_emprestimo.ref_cod_cliente) AS cpf_cliente,
       to_char(data_retirada,'dd/mm/yyyy') AS dt_retirada,
       to_char(data_devolucao,'dd/mm/yyyy') AS dt_devolucao,
       exemplar_emprestimo.valor_multa,

  (SELECT biblioteca.nm_biblioteca
   FROM pmieducar.biblioteca
   WHERE acervo.ref_cod_biblioteca = biblioteca.cod_biblioteca) AS nm_biblioteca,

  (SELECT COALESCE(
                     (SELECT COALESCE (fcn_upper(ps.nome),fcn_upper(juridica.fantasia))
                      FROM cadastro.pessoa ps, cadastro.juridica
                      WHERE escola.ref_idpes = juridica.idpes
                        AND juridica.idpes = ps.idpes
                        AND ps.idpes = escola.ref_idpes),
                     (SELECT nm_escola
                      FROM pmieducar.escola_complemento
                      WHERE ref_cod_escola = escola.cod_escola))) AS nm_escola,

  (SELECT COALESCE(
                     (SELECT COALESCE(
                                        (SELECT logradouro.nome
                                         FROM public.logradouro, cadastro.juridica, cadastro.pessoa ps, cadastro.endereco_pessoa
                                         WHERE juridica.idpes = ps.idpes
                                           AND ps.idpes = endereco_pessoa.idpes
                                           AND endereco_pessoa.idlog = logradouro.idlog
                                           AND juridica.idpes = escola.ref_idpes),
                                        (SELECT endereco_externo.logradouro
                                         FROM cadastro.endereco_externo
                                         WHERE endereco_externo.idpes = escola.ref_idpes))),
                     (SELECT logradouro
                      FROM pmieducar.escola_complemento
                      WHERE ref_cod_escola = escola.cod_escola))) AS logradouro,

  (SELECT COALESCE(
                     (SELECT COALESCE(
                                        (SELECT bairro.nome
                                         FROM public.municipio, cadastro.endereco_pessoa, cadastro.juridica, public.bairro
                                         WHERE endereco_pessoa.idbai = bairro.idbai
                                           AND bairro.idmun = municipio.idmun
                                           AND juridica.idpes = endereco_pessoa.idpes
                                           AND juridica.idpes = escola.ref_idpes),
                                        (SELECT endereco_externo.bairro
                                         FROM cadastro.endereco_externo
                                         WHERE endereco_externo.idpes = escola.ref_idpes))),
                     (SELECT bairro
                      FROM pmieducar.escola_complemento
                      WHERE ref_cod_escola = escola.cod_escola))) AS bairro,

  (SELECT COALESCE(
                     (SELECT COALESCE (
                                         (SELECT municipio.nome
                                          FROM public.municipio, cadastro.endereco_pessoa, cadastro.juridica, public.bairro
                                          WHERE endereco_pessoa.idbai = bairro.idbai
                                            AND bairro.idmun = municipio.idmun
                                            AND juridica.idpes = endereco_pessoa.idpes
                                            AND juridica.idpes = escola.ref_idpes),
                                         (SELECT endereco_externo.cidade
                                          FROM cadastro.endereco_externo
                                          WHERE endereco_externo.idpes = escola.ref_idpes))),
                     (SELECT municipio
                      FROM pmieducar.escola_complemento
                      WHERE ref_cod_escola = escola.cod_escola))) AS municipio,

  (SELECT COALESCE(
                     (SELECT COALESCE(
                                        (SELECT endereco_pessoa.numero
                                         FROM cadastro.endereco_pessoa, cadastro.juridica
                                         WHERE juridica.idpes = endereco_pessoa.idpes
                                           AND juridica.idpes = escola.ref_idpes),
                                        (SELECT endereco_externo.numero
                                         FROM cadastro.endereco_externo
                                         WHERE endereco_externo.idpes = escola.ref_idpes))),
                     (SELECT numero
                      FROM pmieducar.escola_complemento
                      WHERE ref_cod_escola = escola.cod_escola))) AS numero,

  (SELECT COALESCE(
                     (SELECT COALESCE(
                                        (SELECT municipio.sigla_uf
                                         FROM public.municipio, cadastro.endereco_pessoa, cadastro.juridica, public.bairro
                                         WHERE endereco_pessoa.idbai = bairro.idbai
                                           AND bairro.idmun = municipio.idmun
                                           AND juridica.idpes = endereco_pessoa.idpes
                                           AND juridica.idpes = escola.ref_idpes),
                                        (SELECT endereco_externo.sigla_uf
                                         FROM cadastro.endereco_externo
                                         WHERE endereco_externo.idpes = escola.ref_idpes))),
                     (SELECT inst.ref_sigla_uf
                      FROM pmieducar.instituicao inst
                      WHERE inst.cod_instituicao = instituicao.cod_instituicao))) AS uf_municipio,

  (SELECT COALESCE(
                     (SELECT min(to_char(fone_pessoa.ddd,'99'))
                      FROM cadastro.fone_pessoa, cadastro.juridica
                      WHERE juridica.idpes = fone_pessoa.idpes
                        AND juridica.idpes = escola.ref_idpes),COALESCE(
                                                                          (SELECT min(to_char(ddd_telefone,'99'))
                                                                           FROM pmieducar.escola_complemento
                                                                           WHERE ref_cod_escola = escola.cod_escola),''))) AS fone_ddd,

  (SELECT COALESCE(
                     (SELECT COALESCE(
                                        (SELECT to_char(endereco_pessoa.cep, '99999-999')
                                         FROM cadastro.endereco_pessoa, cadastro.juridica
                                         WHERE juridica.idpes = endereco_pessoa.idpes
                                           AND juridica.idpes = escola.ref_idpes),
                                        (SELECT to_char(endereco_externo.cep,'99999-999')
                                         FROM cadastro.endereco_externo
                                         WHERE endereco_externo.idpes = escola.ref_idpes))),
                     (SELECT to_char(escola_complemento.cep,'99999-999')
                      FROM pmieducar.escola_complemento
                      WHERE escola_complemento.ref_cod_escola = escola.cod_escola))) AS cep,

  (SELECT COALESCE(
                     (SELECT min(to_char(fone_pessoa.fone, '9999-9999'))
                      FROM cadastro.fone_pessoa, cadastro.juridica
                      WHERE juridica.idpes = fone_pessoa.idpes
                        AND juridica.idpes = escola.ref_idpes),COALESCE(
                                                                          (SELECT min(to_char(telefone, '9999-9999'))
                                                                           FROM pmieducar.escola_complemento
                                                                           WHERE escola_complemento.ref_cod_escola = escola.cod_escola),''))) AS fone,

  (SELECT COALESCE(
                     (SELECT ps.email
                      FROM cadastro.pessoa ps, cadastro.juridica
                      WHERE juridica.idpes = ps.idpes
                        AND juridica.idpes = escola.ref_idpes),
                     (SELECT email
                      FROM pmieducar.escola_complemento
                      WHERE ref_cod_escola = escola.cod_escola))) AS email,

  (SELECT acervo_autor.nm_autor
   FROM pmieducar.acervo_autor,
        pmieducar.acervo_acervo_autor
   WHERE acervo_acervo_autor.ref_cod_acervo_autor = acervo_autor.cod_acervo_autor
     AND acervo_acervo_autor.ref_cod_acervo = acervo.cod_acervo
     AND acervo_acervo_autor.principal = 1) AS autor,

  (SELECT min(fone)
   FROM cadastro.fone_pessoa,
        cadastro.pessoa,
        cadastro.fisica,
        pmieducar.cliente
   WHERE fone_pessoa.idpes = pessoa.idpes
     AND pessoa.idpes = fisica.idpes
     AND fisica.idpes = cliente.ref_idpes
     AND cliente.cod_cliente = exemplar_emprestimo.ref_cod_cliente) AS telefone,

  (SELECT coalesce(
                     (SELECT logradouro.nome
                      FROM public.logradouro, cadastro.endereco_pessoa, cadastro.pessoa, cadastro.fisica, pmieducar.cliente
                      WHERE logradouro.idlog = endereco_pessoa.idlog
                        AND endereco_pessoa.idpes = pessoa.idpes
                        AND pessoa.idpes = fisica.idpes
                        AND fisica.idpes = cliente.ref_idpes
                        AND cliente.cod_cliente = exemplar_emprestimo.ref_cod_cliente),
                     (SELECT logradouro
                      FROM cadastro.endereco_externo, cadastro.pessoa, cadastro.fisica, pmieducar.cliente
                      WHERE endereco_externo.idpes = pessoa.idpes
                        AND pessoa.idpes = fisica.idpes
                        AND fisica.idpes = cliente.ref_idpes
                        AND cliente.cod_cliente = exemplar_emprestimo.ref_cod_cliente))) AS endereco,

  (SELECT to_char(CAST(exemplar_emprestimo.data_retirada as DATE) + (SELECT CAST(dias_emprestimo as integer)
  FROM pmieducar.cliente_tipo_exemplar_tipo,
       pmieducar.cliente_tipo,
       pmieducar.cliente,
       pmieducar.cliente_tipo_cliente,
       pmieducar.exemplar_tipo
 WHERE cliente_tipo_exemplar_tipo.ref_cod_cliente_tipo = cliente_tipo.cod_cliente_tipo AND
       cliente_tipo.ref_cod_biblioteca = biblioteca.cod_biblioteca AND
       cliente.cod_cliente = cliente_tipo_cliente.ref_cod_cliente AND
       cliente_tipo_cliente.ref_cod_cliente = exemplar_emprestimo.ref_cod_cliente AND
       cliente_tipo_cliente.ref_cod_cliente_tipo = cliente_tipo.cod_cliente_tipo AND
       cliente_tipo_exemplar_tipo.ref_cod_exemplar_tipo = exemplar_tipo.cod_exemplar_tipo AND
       exemplar_tipo.cod_exemplar_tipo = acervo.ref_cod_exemplar_tipo), 'DD/MM/YYYY')) as data_entrega,

   (SELECT pessoa.email
    FROM cadastro.pessoa,
         cadastro.fisica,
         pmieducar.cliente
    WHERE fisica.idpes = pessoa.idpes
      AND fisica.idpes = cliente.ref_idpes
      AND cliente.cod_cliente = exemplar_emprestimo.ref_cod_cliente) AS email_cliente,
      pessoa_pai.nome AS nome_pai,
      pessoa_mae.nome AS nome_mae,
      to_char(fisica_pai.cpf,'000\".\"000\".\"000\"-\"00')  AS cpf_pai,
      to_char(fisica_mae.cpf,'000\".\"000\".\"000\"-\"00')  AS cpf_mae,
      EXTRACT(YEAR from AGE(fisica.data_nasc)) AS idade
 FROM pmieducar.acervo,
      pmieducar.biblioteca,
      pmieducar.instituicao,
      pmieducar.escola,
      pmieducar.exemplar,
      pmieducar.exemplar_emprestimo,
      pmieducar.cliente
INNER JOIN cadastro.fisica ON (fisica.idpes = cliente.ref_idpes)
 LEFT JOIN cadastro.pessoa pessoa_pai ON (pessoa_pai.idpes = fisica.idpes_pai)
 LEFT JOIN cadastro.pessoa AS pessoa_mae ON (pessoa_mae.idpes = fisica.idpes_mae)
 LEFT JOIN cadastro.fisica fisica_pai ON (fisica_pai.idpes = fisica.idpes_pai)
 LEFT JOIN cadastro.fisica fisica_mae ON (fisica_mae.idpes = fisica.idpes_mae)
WHERE instituicao.cod_instituicao = {$instituicao}
  AND instituicao.cod_instituicao = biblioteca.ref_cod_instituicao
  AND acervo.ref_cod_biblioteca = biblioteca.cod_biblioteca
  AND escola.cod_escola = {$escola}
  AND biblioteca.ref_cod_escola = escola.cod_escola
  AND acervo.ativo = 1
  AND exemplar.ref_cod_acervo = acervo.cod_acervo
  AND exemplar.cod_exemplar = exemplar_emprestimo.ref_cod_exemplar
  AND cliente.cod_cliente = exemplar_emprestimo.ref_cod_cliente
  AND (date(exemplar_emprestimo.data_devolucao) >= (substr('{$dt_inicial}',7,10) || '-' || substr('{$dt_inicial}',4,2) || '-' || substr('{$dt_inicial}',1,2))::date)
  AND (date(exemplar_emprestimo.data_devolucao) <= (substr('{$dt_final}',7,10) || '-' || substr('{$dt_final}',4,2) || '-' || substr('{$dt_final}',1,2))::date)
  AND exemplar_emprestimo.data_devolucao IS NOT NULL
  AND
    (SELECT CASE
                WHEN {$cliente} <> 0 THEN exemplar_emprestimo.ref_cod_cliente = {$cliente}
                ELSE exemplar_emprestimo.ref_cod_cliente = exemplar_emprestimo.ref_cod_cliente
            END)
        ";
    }
}
