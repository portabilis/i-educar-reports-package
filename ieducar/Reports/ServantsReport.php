<?php

use iEducar\Reports\JsonDataSource;

require_once 'lib/Portabilis/Report/ReportCore.php';
require_once 'App/Model/IedFinder.php';

class ServantsReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        return 'servants';
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
     *
     * @throws Exception
     */
    public function getSqlMainReport()
    {
        $ano = $this->args['ano'] ?: 0;
        $instituicao = $this->args['instituicao'] ?: 0;
        $escola = $this->args['escola'] ?: 0;
        $funcao = $this->args['funcao'] ?: 0;
        $vinculo = $this->args['vinculo'] ?: 0;
        $periodo = $this->args['periodo'] ?: 0;
        $nao_emitir_afastados = $this->args['nao_emitir_afastados'];

        return "
SELECT DISTINCT COALESCE(pessoa_juridica.nome, '') AS nm_escola_servidor,
       pessoa.nome AS nm_servidor,
       relatorio.get_texto_sem_caracter_especial(pessoa.nome) AS nm_servidor_order,
       escolaridade.descricao AS escolaridade,
       date_part('hour', SUM(servidor_alocacao.carga_horaria))::int as carga_horaria_total,
       (SELECT  DISTINCT '' || (replace(textcat_all((CASE WHEN vinculo_mono.cod_funcionario_vinculo = 3 THEN 'Efet'
                                                                                 WHEN vinculo_mono.cod_funcionario_vinculo = 4 THEN 'Cont'
                                                                                 WHEN vinculo_mono.cod_funcionario_vinculo = 5 THEN 'Com'
                                                                                 WHEN vinculo_mono.cod_funcionario_vinculo = 6 THEN 'Est' END)), ' <br> ','/')) AS vinculo
        FROM (SELECT DISTINCT fv.cod_funcionario_vinculo
                FROM pmieducar.instituicao AS inst
          INNER JOIN pmieducar.servidor AS s ON (s.ref_cod_instituicao = inst.cod_instituicao)
          INNER JOIN cadastro.pessoa AS p ON (p.idpes = s.cod_servidor)
              LEFT JOIN pmieducar.servidor_alocacao AS sa ON (sa.ref_cod_servidor = s.cod_servidor)
              LEFT JOIN portal.funcionario AS func ON (s.cod_servidor = func.ref_cod_pessoa_fj)
              LEFT JOIN portal.funcionario_vinculo AS fv ON (fv.cod_funcionario_vinculo = sa.ref_cod_funcionario_vinculo)
                WHERE p.idpes = pessoa.idpes
	   AND sa.ref_cod_escola = escola.cod_escola
	   AND sa.ano ={$ano}) AS vinculo_mono) AS vinculo,
       public.formata_cpf(fisica.cpf) AS cpf,
       COALESCE(fone_pessoa.fone, 0) AS telefone,
       fone_pessoa.ddd AS ddd_telefone,
       (SELECT DISTINCT '' || (replace(textcat_all((SELECT CASE WHEN servidor_alocacao_aux.periodo = 3 THEN 'N'
					    WHEN servidor_alocacao_aux.periodo = 2 THEN 'V'
					    WHEN servidor_alocacao_aux.periodo = 1 THEN 'M'
			   END)),' <br> ','/'))
	 FROM pmieducar.servidor_alocacao servidor_alocacao_aux
	WHERE servidor_alocacao_aux.ref_cod_servidor = servidor_alocacao.ref_cod_servidor
	  AND servidor_alocacao_aux.ref_cod_escola = escola.cod_escola
	  AND servidor_alocacao_aux.ano = {$ano}) AS periodo
  FROM pmieducar.instituicao
 INNER JOIN pmieducar.servidor ON (servidor.ref_cod_instituicao = instituicao.cod_instituicao)
 INNER JOIN cadastro.pessoa ON (pessoa.idpes = servidor.cod_servidor)
  LEFT JOIN pmieducar.servidor_alocacao ON (servidor_alocacao.ref_cod_servidor = servidor.cod_servidor)
  LEFT JOIN pmieducar.servidor_funcao ON (servidor_funcao.cod_servidor_funcao = servidor_alocacao.ref_cod_servidor_funcao)
  LEFT JOIN pmieducar.funcao ON (servidor_funcao.ref_cod_funcao = funcao.cod_funcao)
  LEFT JOIN cadastro.escolaridade ON (escolaridade.idesco = servidor.ref_idesco)
  LEFT JOIN cadastro.fisica ON (fisica.idpes = pessoa.idpes)
  LEFT JOIN cadastro.fone_pessoa ON (fone_pessoa.idpes = pessoa.idpes
                                     AND fone_pessoa.tipo = (SELECT COALESCE(MIN(fone_pessoa_aux.tipo),1)
			                                       FROM cadastro.fone_pessoa AS fone_pessoa_aux
				 			      WHERE fone_pessoa_aux.fone <> 0
							        AND fone_pessoa_aux.idpes = pessoa.idpes))
  LEFT JOIN portal.funcionario ON (servidor.cod_servidor = funcionario.ref_cod_pessoa_fj)
  LEFT JOIN portal.funcionario_vinculo ON (funcionario_vinculo.cod_funcionario_vinculo = servidor_alocacao.ref_cod_funcionario_vinculo)
  LEFT JOIN pmieducar.escola ON (servidor_alocacao.ref_cod_escola = escola.cod_escola)
  LEFT JOIN cadastro.pessoa pessoa_juridica ON (pessoa_juridica.idpes = escola.ref_idpes)
  LEFT JOIN pmieducar.escola_ano_letivo ON (escola_ano_letivo.ref_cod_escola = escola.cod_escola AND escola_ano_letivo.ano = {$ano})
 WHERE instituicao.cod_instituicao = {$instituicao}
   AND servidor.ativo = 1
   AND servidor_alocacao.ano = {$ano}
   AND (CASE WHEN {$escola} = 0 THEN TRUE ELSE escola.cod_escola = {$escola} END)
   AND (CASE WHEN {$funcao} = 0 THEN TRUE ELSE funcao.cod_funcao = {$funcao} END)
   AND (CASE WHEN {$vinculo} = 0 THEN TRUE ELSE funcionario_vinculo.cod_funcionario_vinculo = {$vinculo} END)
   AND (CASE WHEN {$periodo} = 0 THEN TRUE ELSE servidor_alocacao.periodo = {$periodo} END)
   AND (CASE WHEN {$nao_emitir_afastados} = TRUE
             THEN cod_servidor NOT IN (SELECT sa.ref_cod_servidor
				    FROM pmieducar.servidor_afastamento sa
                                       WHERE sa.ativo = 1)
             ELSE TRUE
        END)
group by nm_escola_servidor, pessoa.nome, escolaridade.descricao, pessoa.idpes, escola.cod_escola, fisica.cpf, fone_pessoa.fone, fone_pessoa.ddd, servidor_alocacao.ref_cod_servidor
 ORDER BY nm_escola_servidor, nm_servidor_order
        ";
    }
}
