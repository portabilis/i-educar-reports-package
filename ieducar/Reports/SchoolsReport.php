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

        return <<<SQL
select
	p.email,
	fp_cel.ddd as cel_ddd,
	to_char(fp_cel.fone, '99999-9999') as cel,
	fp.ddd as fone_ddd,
	to_char(fp.fone, '99999-9999') as fone,
	'RUA' as tipo_logradouro,
	a.address as logradouro,
	a.neighborhood as bairro,
	a."number" as numero,
	a.postal_code as cep,
	a.state_abbreviation as uf,
	a.city as cidade,
	ece.cod_escola_inep,
	p.nome as nm_escola,
	to_char(current_date,'dd/mm/yyyy') AS data_atual,
	to_char(current_timestamp, 'HH24:MI:SS') AS hora_atual
from pmieducar.instituicao i 
inner join pmieducar.escola e 
on e.ref_cod_instituicao = i.cod_instituicao 
inner join cadastro.pessoa p 
on p.idpes = e.ref_idpes 
inner join pmieducar.escola_ano_letivo eal 
on eal.ref_cod_escola = e.cod_escola 
left join public.person_has_place php 
on php.person_id = p.idpes 
left join public.addresses a 
on a.id = php.place_id 
left join cadastro.fone_pessoa fp 
on fp.idpes = p.idpes 
and fp.tipo = 1
left join cadastro.fone_pessoa fp_cel
on fp_cel.idpes = p.idpes 
and fp_cel.tipo = 3
left join modules.educacenso_cod_escola ece 
on ece.cod_escola = e.cod_escola 
where true 
and i.cod_instituicao = $instituicao
and e.ativo = 1 
and eal.ano = $ano 
and eal.ativo = 1 
and (
	select case when $curso = 0 then 1 = 1
	else (
		select 1 
		from pmieducar.escola_curso ec 
		where ec.ref_cod_escola = e.cod_escola 
		and ec.ref_cod_curso = $curso
	) is not null
	end
)
order by 
	p.nome
SQL;
    }
}
