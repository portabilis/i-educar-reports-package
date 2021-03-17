<?php

use iEducar\Reports\JsonDataSource;

class TransportationUsersReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        return 'transportation-users';
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
        $destino = $this->args['destino'] ?: 0;

        return "
        SELECT pessoa.nome AS nome_usuario_transporte,
       destino.nome AS nome_destino_user,
       destino_rota.nome AS nome_destino_rota,
       (SELECT replace(textcat_all(descricao), '<br>', ', ')
        FROM (select DISTINCT tv.descricao from modules.tipo_veiculo tv
	   	left join modules.veiculo ON (cod_tipo_veiculo = ref_cod_tipo_veiculo)
	   	left join modules.itinerario_transporte_escolar as ite ON (ite.ref_cod_rota_transporte_escolar = pessoa_transporte.ref_cod_rota_transporte_escolar)
	   	order by descricao
             )
	tabl) as tipo_veiculo,
       rota_transporte_escolar.descricao AS rota,
       pessoa_transporte.turno AS turno,
       rota_transporte_escolar.km_pav as km_pav,
       rota_transporte_escolar.km_npav as km_npav,
       rota_transporte_escolar.km_pav + km_npav as km_total,
       logradouro.idtlog as tipo_endereco,
       logradouro.nome as endereco,
       fone_pessoa.ddd as ddd,
       MAX(fone_pessoa.fone) as telefone,
       to_char(now(), 'dd/MM/yyyy HH:mm') AS data_hora
  FROM modules.pessoa_transporte
 INNER JOIN cadastro.pessoa ON (pessoa.idpes = pessoa_transporte.ref_idpes)
  LEFT JOIN cadastro.endereco_pessoa ON (endereco_pessoa.idpes = pessoa.idpes)
  LEFT JOIN public.logradouro ON (logradouro.idlog = endereco_pessoa.idlog)
  LEFT JOIN cadastro.fone_pessoa ON (fone_pessoa.idpes = pessoa.idpes)
 INNER JOIN modules.rota_transporte_escolar ON (rota_transporte_escolar.cod_rota_transporte_escolar = pessoa_transporte.ref_cod_rota_transporte_escolar)
  LEFT JOIN cadastro.pessoa AS destino ON (destino.idpes = pessoa_transporte.ref_idpes_destino)
  LEFT JOIN cadastro.pessoa AS destino_rota ON (destino_rota.idpes = rota_transporte_escolar.ref_idpes_destino)
 WHERE rota_transporte_escolar.ano = {$ano}
   AND     CASE WHEN {$destino} = 0 THEN TRUE ELSE (
		CASE WHEN (select min(ref_idpes_destino) from modules.pessoa_transporte where pessoa_transporte.cod_pessoa_transporte = pessoa_transporte.cod_pessoa_transporte) is not null THEN
			{$destino} = pessoa_transporte.ref_idpes_destino
		ELSE
			{$destino} = rota_transporte_escolar.ref_idpes_destino
		END) END
 GROUP BY nome_usuario_transporte, nome_destino_user, nome_destino_rota, tipo_veiculo, rota, km_pav, km_npav, km_total, tipo_endereco, endereco, ddd, turno
 ORDER BY rota, relatorio.get_texto_sem_caracter_especial(pessoa.nome)
        ";
    }
}
