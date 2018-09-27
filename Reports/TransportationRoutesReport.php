<?php

use iEducar\Reports\JsonDataSource;

require_once 'lib/Portabilis/Report/ReportCore.php';
require_once 'App/Model/IedFinder.php';

class TransportationRoutesReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        $modelos = [
            1 => 'transportation-routes',
            2 => 'detailed-transportation-routes'
        ];

        return $modelos[$this->args['modelo']];
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
        switch ($this->args['modelo']) {
            case 1: return $this->getSqlTransportationRoutesReport();
            case 2: return $this->getSqlDetailedTransportationRoutesReport();
        }
    }

    /**
     * Retorna o SQL específico para o modelo "simplificado".
     * 
     * @return string
     */
    private function getSqlTransportationRoutesReport()
    {
        $instituicao = $this->args['instituicao'] ?: 0;
        $ano = $this->args['ano'] ?: 0;
        $empresa = $this->args['empresa'] ?: 0;

        return "
SELECT public.fcn_upper(nm_instituicao) AS nome_instituicao,
       public.fcn_upper(nm_responsavel) AS nome_responsavel,
	(select nome from cadastro.pessoa where emp.ref_idpes = pessoa.idpes) as nome_empresa,
       rt.descricao,
       rt.km_pav,
       rt.km_npav,
       (select nome from cadastro.pessoa where rt.ref_idpes_destino = pessoa.idpes) as nome_destino,
       rt.tercerizado

  FROM  pmieducar.instituicao,
        modules.empresa_transporte_escolar emp

 inner join modules.rota_transporte_escolar rt on (emp.cod_empresa_transporte_escolar = rt.ref_cod_empresa_transporte_escolar)

  WHERE instituicao.cod_instituicao = {$instituicao} and
        rt.ano = {$ano} and
 (select case when {$empresa} = 0 then
	1=1
	else
	cod_empresa_transporte_escolar = {$empresa}
	end)
        ";
    }

    /**
     * Retorna o SQL específico para o modelo "detalhado".
     *
     * @return string
     */
    private function getSqlDetailedTransportationRoutesReport()
    {
        $instituicao = $this->args['instituicao'];
        $ano = $this->args['ano'];
        $empresa = $this->args['empresa'];

        return "
        SELECT public.fcn_upper(nm_instituicao) AS nome_instituicao,
       public.fcn_upper(nm_responsavel) AS nome_responsavel,
  (select nome from cadastro.pessoa where emp.ref_idpes = pessoa.idpes) as nome_empresa,
      rt.cod_rota_transporte_escolar,
       rt.descricao,
       rt.km_pav,
       rt.km_npav,
       (select nome from cadastro.pessoa where rt.ref_idpes_destino = pessoa.idpes) as nome_destino,
       rt.tercerizado,
       it.seq,
       (select descricao from modules.ponto_transporte_escolar where cod_ponto_transporte_escolar = it.ref_cod_ponto_transporte_escolar) as nome_ponto,
       it.tipo,
       it.hora,
       (select upper(descricao) || '-' || upper(placa) from modules.veiculo where cod_veiculo = it.ref_cod_veiculo) as nome_veiculo

  FROM  pmieducar.instituicao,
        modules.empresa_transporte_escolar emp

 inner join modules.rota_transporte_escolar rt on (emp.cod_empresa_transporte_escolar = rt.ref_cod_empresa_transporte_escolar)
 left join modules.itinerario_transporte_escolar it on (it.ref_cod_rota_transporte_escolar = rt.cod_rota_transporte_escolar)
  WHERE instituicao.cod_instituicao = {$instituicao} and
        rt.ano = {$ano} and
 (select case when {$empresa} = 0 then
	1=1
	else
	cod_empresa_transporte_escolar = {$empresa}
	end)
order by nome_empresa, rt.descricao, it.seq
        ";
    }
}
