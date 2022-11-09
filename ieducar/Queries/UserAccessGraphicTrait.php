<?php

trait UserAccessGraphicTrait
{
    /**
     * @inheritdoc
     */
    protected function query()
    {
        $instituicao = $this->args['instituicao'];
        $escola = $this->args['escola'];
        $data_inicial = $this->args['data_inicial'];
        $data_final = $this->args['data_final'];
        $curso = $this->args['curso'];

        return <<<SQL
                SELECT
                        pessoa.nome AS nome_escola,
                        (SELECT COUNT(1)
                            FROM portal.acesso
                            INNER JOIN portal.funcionario ON portal.funcionario.ref_cod_pessoa_fj = portal.acesso.cod_pessoa
                            INNER JOIN pmieducar.usuario ON usuario.cod_usuario = portal.acesso.cod_pessoa
                            INNER JOIN pmieducar.instituicao ON instituicao.cod_instituicao = {$instituicao} AND usuario.ref_cod_instituicao = instituicao.cod_instituicao
                            INNER JOIN pmieducar.escola_usuario ON (escola_usuario.ref_cod_usuario = usuario.cod_usuario)
                            WHERE escola.cod_escola = escola_usuario.ref_cod_escola
                            AND date(data_hora) >= '{$data_inicial}'::date AND date(data_hora) <= '{$data_final}'::date
                        ) AS acessos
                FROM pmieducar.escola
                INNER JOIN cadastro.pessoa ON pessoa.idpes = escola.ref_idpes
                WHERE ({$escola} = 0 OR escola.cod_escola = {$escola})
                  AND ({$curso} = 0 OR (escola.cod_escola IN
                    (SELECT ref_cod_escola
                    FROM pmieducar.escola_curso
                    WHERE ref_cod_curso = {$curso}
                    AND ativo = 1)))
                ORDER BY acessos DESC
SQL;
    }
}
