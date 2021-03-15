<?php

use iEducar\Reports\JsonDataSource;

class LibraryLoansReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        return 'library-loans';
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
        $escola = $this->args['escola'] ?: 0;
        $instituicao = $this->args['instituicao'] ?: 0;
        $dt_inicial = $this->args['dt_inicial'] ?: 0;
        $dt_final = $this->args['dt_final'] ?: 0;
        $cliente = $this->args['cliente'] ?: 0;

        return "
        SELECT public.fcn_upper(instituicao.nm_instituicao) AS nm_instituicao,
       exemplar_emprestimo.cod_emprestimo AS cod_emprestimo,
       exemplar_emprestimo.ref_cod_cliente AS ref_cod_cliente,
       exemplar.cod_exemplar AS ref_cod_exemplar,
       exemplar.tombo AS tombo,
       acervo.titulo AS titulo,
       pessoa.nome AS cliente,
       to_char(exemplar_emprestimo.data_retirada, 'DD/MM/YYYY') AS dt_retirada,
       to_char(exemplar_emprestimo.data_devolucao, 'DD/MM/YYYY') AS dt_devolucao,
       exemplar_emprestimo.valor_multa AS valor_multa,
       biblioteca.nm_biblioteca AS nm_biblioteca,
       relatorio.get_nome_escola(escola.cod_escola) AS nm_escola,
       relatorio.get_nome_escola(matricula.ref_ref_cod_escola) AS escola_aluno,
       view_dados_escola.logradouro AS logradouro,
       view_dados_escola.bairro AS bairro,
       view_dados_escola.municipio AS municipio,
       view_dados_escola.numero AS numero,
       view_dados_escola.uf_municipio AS uf_municipio,
       view_dados_escola.telefone_ddd AS fone_ddd,
       view_dados_escola.cep AS cep,
       view_dados_escola.telefone AS fone,
       view_dados_escola.email AS email,
       acervo_autor.nm_autor AS autor,
       cliente_tipo_exemplar_tipo.dias_emprestimo AS dias,
       CAST (exemplar_emprestimo.data_retirada AS DATE) + CAST(dias_emprestimo AS INTEGER) AS data_entrega,

                                                              curso.nm_curso AS curso_aluno,
                                                              serie.nm_serie AS serie_aluno,
                                                              turma.nm_turma AS aluno_turma,
                                                              COALESCE(fone_pessoa.fone, '0') AS telefone,
                                                              logradouro.nome AS endereco,
                                                              pessoa.email AS email_cliente
FROM pmieducar.instituicao
INNER JOIN pmieducar.escola ON (escola.ref_cod_instituicao = instituicao.cod_instituicao
                                AND escola.cod_escola = {$escola})
LEFT JOIN relatorio.view_dados_escola ON (view_dados_escola.cod_escola = escola.cod_escola)
INNER JOIN pmieducar.biblioteca ON (biblioteca.ref_cod_escola = escola.cod_escola
                                    AND instituicao.cod_instituicao = biblioteca.ref_cod_instituicao)
INNER JOIN pmieducar.acervo ON (acervo.ref_cod_biblioteca = biblioteca.cod_biblioteca
                                AND acervo.ativo = 1)
INNER JOIN pmieducar.exemplar ON (exemplar.ref_cod_acervo = acervo.cod_acervo
                                  AND exemplar.ativo = 1)
INNER JOIN pmieducar.exemplar_emprestimo ON (exemplar_emprestimo.ref_cod_exemplar = exemplar.cod_exemplar)
INNER JOIN pmieducar.cliente ON (cliente.cod_cliente = exemplar_emprestimo.ref_cod_cliente)
INNER JOIN cadastro.pessoa ON (pessoa.idpes = cliente.ref_idpes)
INNER JOIN cadastro.fisica ON (fisica.idpes = cliente.ref_idpes)
LEFT JOIN cadastro.endereco_externo ON (endereco_externo.idpes = cliente.ref_idpes)
LEFT JOIN cadastro.endereco_pessoa ON (endereco_pessoa.idpes = cliente.ref_idpes)
LEFT JOIN public.logradouro ON (logradouro.idlog = endereco_pessoa.idlog)
LEFT JOIN pmieducar.acervo_acervo_autor ON (acervo_acervo_autor.ref_cod_acervo = acervo.cod_acervo)
LEFT JOIN pmieducar.acervo_autor ON (acervo_autor.cod_acervo_autor = acervo_acervo_autor.ref_cod_acervo_autor)
INNER JOIN pmieducar.cliente_tipo_cliente ON (cliente_tipo_cliente.ref_cod_cliente = cliente.cod_cliente)
INNER JOIN pmieducar.cliente_tipo ON (cliente_tipo.cod_cliente_tipo = cliente_tipo_cliente.ref_cod_cliente_tipo)
INNER JOIN pmieducar.exemplar_tipo ON (exemplar_tipo.cod_exemplar_tipo = acervo.ref_cod_exemplar_tipo)
INNER JOIN pmieducar.cliente_tipo_exemplar_tipo ON (cliente_tipo_exemplar_tipo.ref_cod_cliente_tipo = cod_cliente_tipo
                                                    AND cliente_tipo_exemplar_tipo.ref_cod_exemplar_tipo = exemplar_tipo.cod_exemplar_tipo)
LEFT JOIN
  (SELECT max(matricula.cod_matricula) AS matricula_aluno,
          aluno.ref_idpes AS idpes
   FROM pmieducar.matricula
   INNER JOIN pmieducar.aluno ON (aluno.cod_aluno = matricula.ref_cod_aluno)
   WHERE matricula.ativo = 1
   GROUP BY aluno.ref_idpes) ultima_matricula_aluno ON (ultima_matricula_aluno.idpes = cliente.ref_idpes)
LEFT JOIN pmieducar.matricula ON (matricula.cod_matricula = ultima_matricula_aluno.matricula_aluno)
LEFT JOIN pmieducar.curso ON (curso.cod_curso = matricula.ref_cod_curso)
LEFT JOIN pmieducar.serie ON (serie.cod_serie = matricula.ref_ref_cod_serie)
LEFT JOIN pmieducar.matricula_turma ON (matricula_turma.ref_cod_matricula = ultima_matricula_aluno.matricula_aluno
                                        AND matricula_turma.ativo = 1)
LEFT JOIN pmieducar.turma ON (turma.cod_turma = matricula_turma.ref_cod_turma)
LEFT JOIN
  (SELECT max(fone) AS fone,
          idpes
   FROM cadastro.fone_pessoa
   GROUP BY idpes) fone_pessoa ON (fone_pessoa.idpes = cliente.ref_idpes)
WHERE instituicao.cod_instituicao = {$instituicao}
  AND acervo.ativo = 1
  AND (date(exemplar_emprestimo.data_retirada) >= (substr('{$dt_inicial}',7,10) || '-' || substr('{$dt_inicial}',4,2) || '-' || substr('{$dt_inicial}',1,2))::date)
  AND (date(exemplar_emprestimo.data_retirada) <= (substr('{$dt_final}',7,10) || '-' || substr('{$dt_final}',4,2) || '-' || substr('{$dt_final}',1,2))::date)
  AND
    (SELECT CASE WHEN {$cliente} <> 0 THEN exemplar_emprestimo.ref_cod_cliente = {$cliente} ELSE exemplar_emprestimo.ref_cod_cliente = exemplar_emprestimo.ref_cod_cliente END)
  AND exemplar_emprestimo.ref_cod_exemplar = exemplar_emprestimo.ref_cod_exemplar
  AND exemplar_emprestimo.data_devolucao IS NULL
  AND escola.cod_escola = {$escola}
ORDER BY cliente
        ";
    }
}
