<?php

use iEducar\Reports\JsonDataSource;

require_once 'lib/Portabilis/Report/ReportCore.php';
require_once 'App/Model/IedFinder.php';

class DriversReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        return 'drivers';
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
     */
    public function getSqlMainReport()
    {
        $instituicao = $this->args['instituicao'] ?: 0;
        $ano = $this->args['ano'] ?: 0;
        $motorista = $this->args['motorista'] ?: 0;

        return "
select DISTINCT
	rte.cod_rota_transporte_escolar as cod_rota,
	rte.descricao as nm_rota,
	(select
		coordenador_transporte
		from pmieducar.instituicao
		where cod_instituicao = {$instituicao}) as nm_coordenador,
	(select
		hora
		from modules.itinerario_transporte_escolar as ite
		where ite.ref_cod_rota_transporte_escolar = rte.cod_rota_transporte_escolar
		order by ite.cod_itinerario_transporte_escolar ASC limit 1
	) as hora_inicial,
	(select
		hora
		from modules.itinerario_transporte_escolar as ite
		where ite.ref_cod_rota_transporte_escolar = rte.cod_rota_transporte_escolar
		order by ite.cod_itinerario_transporte_escolar DESC limit 1
	) as hora_final,
	(select
		descricao
		from ponto_transporte_escolar
		where cod_ponto_transporte_escolar =
			(select ref_cod_ponto_transporte_escolar
				from modules.itinerario_transporte_escolar
				where ref_cod_rota_transporte_escolar = rte.cod_rota_transporte_escolar
				order by cod_itinerario_transporte_escolar ASC limit 1
			)
	) as nm_ponto_inicial,
	(select
		descricao
		from ponto_transporte_escolar
		where cod_ponto_transporte_escolar =
			(select ref_cod_ponto_transporte_escolar
				from modules.itinerario_transporte_escolar
				where ref_cod_rota_transporte_escolar = rte.cod_rota_transporte_escolar
				order by cod_itinerario_transporte_escolar DESC limit 1
			)
	) as nm_ponto_final,
	(select
		REPLACE(textcat_all(p.nome),'<br>',',')
		from modules.motorista as m
		left join cadastro.pessoa as p on (m.ref_idpes = p.idpes)
		where cod_motorista in (select DISTINCT ref_cod_motorista
					  from modules.veiculo
					  where cod_veiculo in (select ref_cod_veiculo
									from modules.itinerario_transporte_escolar as ite
									where ref_cod_rota_transporte_escolar = rte.cod_rota_transporte_escolar
								)
				      )
	) as nm_motorista
	from modules.rota_transporte_escolar as rte
	inner join modules.itinerario_transporte_escolar as ite on (rte.cod_rota_transporte_escolar = ite.ref_cod_rota_transporte_escolar)
	inner join modules.veiculo as v on (v.cod_veiculo = ite.ref_cod_veiculo)
	where rte.ano = {$ano}
	and CASE WHEN {$motorista} > 0
		THEN v.ref_cod_motorista = {$motorista}
		ELSE true
	   END
        ";
    }
}
