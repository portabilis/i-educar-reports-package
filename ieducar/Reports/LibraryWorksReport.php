<?php

use iEducar\Reports\JsonDataSource;

class LibraryWorksReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        return 'library-works';
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
     * TODO refactor query
     *
     * @return string
     */
    public function getSqlMainReport()
    {
        $instituicao = $this->args['instituicao'] ?: 0;
        $escola = $this->args['escola'] ?: 0;

        return "
        SELECT public.fcn_upper(instituicao.nm_instituicao) AS nm_instituicao,
       biblioteca.cod_biblioteca,
       acervo.cod_acervo,
       acervo.titulo,
       acervo.estante,
       acervo.cdd,
       acervo.sub_titulo,
       acervo.cdu,
       acervo.cutter,
       acervo.volume,
       acervo.num_edicao,
       acervo.ano,
       acervo.num_paginas,
       acervo.ref_cod_biblioteca,
       acervo.ref_cod_tipo_autor,
       acervo.tipo_autor,
       cast(acervo.isbn AS varchar),
       fcn_upper(biblioteca.nm_biblioteca) AS nm_biblioteca,
       (SELECT replace(textcat_all(nm_autor),' <br>' , ';')
          FROM pmieducar.acervo_autor
         INNER JOIN pmieducar.acervo_acervo_autor ON (acervo_acervo_autor.ref_cod_acervo_autor = acervo_autor.cod_acervo_autor)
         WHERE acervo_acervo_autor.ref_cod_acervo = acervo.cod_acervo) AS autor,
       exemplar_tipo.nm_tipo AS nm_tipo_exemplar,
       acervo_colecao.nm_colecao AS nm_colecao,
       acervo_idioma.nm_idioma AS nm_idioma,
       acervo_editora.nm_editora AS nm_editora
  FROM pmieducar.instituicao
 INNER JOIN pmieducar.escola ON (escola.ref_cod_instituicao = instituicao.cod_instituicao)
 INNER JOIN pmieducar.biblioteca ON (biblioteca.ref_cod_escola = escola.cod_escola)
 INNER JOIN pmieducar.acervo ON (acervo.ref_cod_biblioteca = biblioteca.cod_biblioteca
                                 AND acervo.ativo = 1)
  LEFT JOIN pmieducar.exemplar_tipo ON (exemplar_tipo.cod_exemplar_tipo = acervo.ref_cod_exemplar_tipo)
  LEFT JOIN pmieducar.acervo_colecao ON (acervo_colecao.cod_acervo_colecao = acervo.ref_cod_acervo_colecao)
  LEFT JOIN pmieducar.acervo_idioma ON (acervo_idioma.cod_acervo_idioma = acervo.ref_cod_acervo_idioma)
  LEFT JOIN pmieducar.acervo_editora ON (acervo_editora.cod_acervo_editora = acervo.ref_cod_acervo_editora)
 WHERE instituicao.cod_instituicao = {$instituicao} AND
       escola.cod_escola = {$escola}
ORDER BY acervo.titulo
        ";
    }
}
