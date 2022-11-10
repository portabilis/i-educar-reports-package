<?php

trait UserAccessTrait
{
    /**
     * @inheritdoc
     */
    protected function query()
    {
        $instituicao = $this->args['instituicao'];
        $escola = $this->args['escola'];
        $ativo = $this->args['ativo'];
        $pessoa = $this->args['pessoa'];
        $data_inicial = $this->args['data_inicial'];
        $data_final = $this->args['data_final'];

        return <<<SQL
            SELECT inst.nm_instituicao AS nm_instituicao,
                   inst.nm_responsavel AS nm_responsavel,
                   ace.cod_pessoa AS cod_pessoa,
                   pes.nome AS nome,
                   to_char(ace.data_hora, 'dd/MM/yyyy HH:mm') AS data_hora,
                   ace.ip_externo AS ip_externo,
                   fun.matricula AS matricula,
                   tipusu.nm_tipo AS tipo_usuario,
                   pesesc.nome AS nome_escola
            FROM portal.acesso ace
            LEFT JOIN cadastro.pessoa pes ON pes.idpes = ace.cod_pessoa
            LEFT JOIN pmieducar.usuario usu ON usu.cod_usuario = ace.cod_pessoa
            LEFT JOIN pmieducar.tipo_usuario tipusu ON tipusu.cod_tipo_usuario = usu.ref_cod_tipo_usuario
            LEFT JOIN portal.funcionario fun ON fun.ref_cod_pessoa_fj = ace.cod_pessoa
            LEFT JOIN pmieducar.instituicao inst ON inst.cod_instituicao = usu.ref_cod_instituicao
            LEFT JOIN pmieducar.escola_usuario escusu ON escusu.ref_cod_usuario = usu.cod_usuario
            LEFT JOIN pmieducar.escola esc ON esc.cod_escola = escusu.ref_cod_escola
            LEFT JOIN cadastro.pessoa pesesc ON pesesc.idpes = esc.ref_idpes
            WHERE ace.data_hora::date between '{$data_inicial}'::date AND '{$data_final}'::date AND inst.cod_instituicao = {$instituicao}
                AND ({$ativo} = 2 OR fun.ativo = {$ativo})
                AND ({$pessoa} = 0 OR pes.idpes = {$pessoa})
                AND ({$escola} = 0 OR escusu.ref_cod_escola = {$escola})
            ORDER BY ace.data_hora DESC
SQL;
    }
}
