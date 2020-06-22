<?php

use iEducar\Reports\JsonDataSource;

require_once 'lib/Portabilis/Report/ReportCore.php';
require_once 'App/Model/IedFinder.php';

class VacancyCertificateReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        return 'vacancy-certificate';
    }

    /**
     * @inheritdoc
     */
    public function requiredArgs()
    {
        $this->addRequiredArg('ano');
        $this->addRequiredArg('instituicao');
        $this->addRequiredArg('escola');
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
        $instituicao = $this->args['instituicao'] ?: 0;
        $escola = $this->args['escola'] ?: 0;
        $curso = $this->args['curso'] ?: 0;
        $serie = $this->args['serie'] ?: 0;

        return "
SELECT public.fcn_upper(nm_instituicao) AS nome_instituicao,
       public.fcn_upper(nm_responsavel) AS nm_responsavel,
       coalesce(instituicao.altera_atestado_para_declaracao, false) AS altera_atestado_para_declaracao,
       instituicao.cidade AS cidade_instituicao,
       public.fcn_upper(ref_sigla_uf) AS uf_instituicao,
       escola.cod_escola AS cod_escola,
      (SELECT COALESCE((SELECT COALESCE (fcn_upper(ps.nome),fcn_upper(juridica.fantasia))
          FROM cadastro.pessoa ps,
               cadastro.juridica
         WHERE escola.ref_idpes = juridica.idpes AND
               juridica.idpes = ps.idpes AND
			   ps.idpes = escola.ref_idpes),(SELECT nm_escola FROM pmieducar.escola_complemento where ref_cod_escola = escola.cod_escola))) AS nm_escola,

       curso.nm_curso,
       serie.nm_serie,
       to_char(CURRENT_DATE,'dd/mm/yyyy') AS data_atual,
       to_char(current_timestamp, 'HH24:MI:SS') AS hora_atual,
       (SELECT fcn_upper(p.nome) FROM cadastro.pessoa p WHERE escola.ref_idpes_gestor = p.idpes) as diretor,

       (SELECT fcn_upper(p.nome)
         FROM    cadastro.pessoa p
         INNER JOIN pmieducar.escola e ON (p.idpes = e.ref_idpes_secretario_escolar)
         WHERE e.cod_escola = {$escola}) as secretario

  FROM pmieducar.instituicao,
       cadastro.pessoa RIGHT OUTER JOIN pmieducar.escola ON (escola.ref_idpes = pessoa.idpes),
       pmieducar.escola_ano_letivo,
       pmieducar.escola_curso,
       pmieducar.escola_serie,
       pmieducar.curso,
       pmieducar.serie

 WHERE
       instituicao.cod_instituicao = {$instituicao} AND
       escola.cod_escola = {$escola} AND
       curso.cod_curso = {$curso} AND
       serie.cod_serie = {$serie} AND
       escola_serie.ref_cod_serie = serie.cod_serie AND
       escola_curso.ref_cod_curso = curso.cod_curso AND
       escola_curso.ref_cod_escola = escola.cod_escola AND
       escola_serie.ref_cod_escola = escola.cod_escola AND
       escola.ref_cod_instituicao = instituicao.cod_instituicao AND
       escola_ano_letivo.ref_cod_escola = escola.cod_escola AND
       escola.ativo = 1
limit 1;
        ";
    }
}
