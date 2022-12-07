<?php

class QueryWhiteStudentFormModel extends QueryBridge
{
    /**
     * @inheritdoc
     */
    protected function query()
    {
        return <<<'SQL'
                SELECT public.fcn_upper(nm_instituicao) AS nome_instituicao,
                       public.fcn_upper(nm_responsavel) AS nome_responsavel,
                       instituicao.cidade AS cidade_instituicao,
                       public.fcn_upper(ref_sigla_uf) AS uf_instituicao,
                       to_char(CURRENT_DATE,'dd/mm/yyyy') AS data_atual,
                       to_char(current_timestamp, 'HH24:MI:SS') AS hora_atual,
                       (SELECT COALESCE((SELECT COALESCE (fcn_upper(ps.nome),fcn_upper(juridica.fantasia))
                           FROM cadastro.pessoa ps,
                               cadastro.juridica,
                               pmieducar.escola
                           WHERE escola.ref_idpes = ps.idpes AND
                               ps.idpes = juridica .idpes AND
                           escola.cod_escola = $P{escola}),(SELECT nm_escola FROM pmieducar.escola_complemento where ref_cod_escola = $P{escola}))
                        ) AS nm_escola
                 FROM pmieducar.instituicao
                 WHERE instituicao.cod_instituicao = $P{instituicao}
SQL;
    }
}
