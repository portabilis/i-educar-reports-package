<?php

use iEducar\Reports\JsonDataSource;

class LibraryClientsReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        return 'library-clients';
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
        $biblioteca = $this->args['biblioteca'] ?: 0;
        $tipo_cliente = $this->args['tipo_cliente'] ?: 0;

        return "
        SELECT biblioteca.nm_biblioteca AS biblioteca,
       cliente.cod_cliente,
       pessoa.nome AS cliente,
       cliente_tipo.nm_tipo AS tipo_cliente,
       to_char(fisica.data_nasc, 'dd/MM/yyyy') AS data_nascimento,
       to_char(now(), 'dd/MM/yyyy HH:mm') AS data_hora
  FROM pmieducar.instituicao
 INNER JOIN pmieducar.escola ON (escola.ref_cod_instituicao = instituicao.cod_instituicao)
 INNER JOIN pmieducar.biblioteca ON (biblioteca.ref_cod_escola = escola.cod_escola)
 INNER JOIN pmieducar.cliente_tipo_cliente ON (cliente_tipo_cliente.ref_cod_biblioteca = biblioteca.cod_biblioteca)
 INNER JOIN pmieducar.cliente ON (cliente.cod_cliente = cliente_tipo_cliente.ref_cod_cliente)
 INNER JOIN pmieducar.cliente_tipo ON (cliente_tipo.cod_cliente_tipo = cliente_tipo_cliente.ref_cod_cliente_tipo)
 INNER JOIN cadastro.pessoa ON (pessoa.idpes = cliente.ref_idpes)
 INNER JOIN cadastro.fisica ON (fisica.idpes = pessoa.idpes)
 WHERE instituicao.cod_instituicao = {$instituicao}
   AND escola.cod_escola = {$escola}
   AND (CASE WHEN {$biblioteca} = 0 THEN TRUE ELSE biblioteca.cod_biblioteca = {$biblioteca} END)
   AND (CASE WHEN {$tipo_cliente} = 0 THEN TRUE ELSE cliente_tipo.cod_cliente_tipo = {$tipo_cliente} END)
   AND cliente.ativo = 1
 ORDER BY biblioteca.nm_biblioteca, relatorio.get_texto_sem_caracter_especial(pessoa.nome)
        ";
    }
}
