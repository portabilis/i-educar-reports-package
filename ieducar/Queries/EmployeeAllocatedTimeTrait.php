<?php

trait EmployeeAllocatedTimeTrait
{
    /**
     * @inheritdoc
     */
    protected function query()
    {
        $ano = $this->args['ano'];
        $instituicao = $this->args['instituicao'];
        $escola = $this->args['escola'];

        return <<<SQL
            SELECT
                relatorio.get_nome_escola(servidor_alocacao.ref_cod_escola) AS nm_escola,
                servidor_alocacao.ref_cod_escola AS cod_escola,
                servidor_alocacao.ano,
                servidor.cod_servidor,
                upper(pessoa.nome) AS nm_servidor,
                COALESCE(escolaridade.descricao, 'Não informada') AS escolaridade,
                funcao_servidor.nm_funcao AS nm_funcao,
                string_agg(DISTINCT(
                    CASE servidor_alocacao.periodo
                        WHEN 1 THEN 'M'
                        WHEN 2 THEN 'V'
                        WHEN 3 THEN 'N'
                    ELSE ''
                END
                ), ', ') AS periodo,
                string_agg(DISTINCT(funcionario_vinculo.abreviatura), ', ') AS vinculo,
                left(make_interval(0,0,0,0, servidor.carga_horaria::int)::text,-3) AS carga_horaria_total,
                left(sum(servidor_alocacao.carga_horaria)::text,-3) AS carga_horaria_alocada,
                left(COALESCE(dados_quadro.carga_horaria_atribuida, '00:00:00')::text,-3) AS carga_horaria_atribuida,
                left((sum(servidor_alocacao.carga_horaria) - COALESCE(dados_quadro.carga_horaria_atribuida, '00:00:00'))::text,-3) AS saldo_carga_horaria
            FROM pmieducar.instituicao
            INNER JOIN pmieducar.servidor ON (servidor.ref_cod_instituicao = instituicao.cod_instituicao)
            INNER JOIN cadastro.pessoa ON (pessoa.idpes = servidor.cod_servidor)
            INNER JOIN pmieducar.servidor_alocacao ON (servidor_alocacao.ref_cod_servidor = servidor.cod_servidor)
            LEFT JOIN portal.funcionario_vinculo ON (funcionario_vinculo.cod_funcionario_vinculo = servidor_alocacao.ref_cod_funcionario_vinculo)
            LEFT JOIN cadastro.escolaridade ON (escolaridade.idesco = servidor.ref_idesco)
            LEFT JOIN LATERAL (
                SELECT
                    servidor_funcao.ref_cod_servidor,
                    string_agg(DISTINCT(funcao.nm_funcao), ', ') AS nm_funcao,
                    string_agg(funcao.abreviatura, ', ') AS nm_funcao_abreviada
                FROM pmieducar.servidor_funcao
                INNER JOIN pmieducar.funcao ON (funcao.cod_funcao = servidor_funcao.ref_cod_funcao)
                INNER JOIN pmieducar.servidor_alocacao  ON (
                        servidor_alocacao.ref_cod_servidor_funcao = servidor_funcao.cod_servidor_funcao
                    AND servidor_alocacao.ref_cod_servidor = servidor_funcao.ref_cod_servidor
                    )
                WHERE servidor_alocacao.ano = {$ano}
                GROUP BY servidor_funcao.ref_cod_servidor
            ) funcao_servidor ON (funcao_servidor.ref_cod_servidor = servidor.cod_servidor)
            LEFT JOIN LATERAL (
                SELECT
                    ref_cod_escola,
                    ref_servidor,
                    ano,
                    sum(qhh.hora_final - qhh.hora_inicial) AS carga_horaria_atribuida
                FROM pmieducar.quadro_horario qh
                INNER JOIN pmieducar.quadro_horario_horarios qhh ON (qhh.ref_cod_quadro_horario = qh.cod_quadro_horario)
                WHERE qh.ativo = 1
                    AND qhh.ativo = 1
                    AND qhh.sequencial = (
                        SELECT s_qhh.sequencial
                        FROM pmieducar.quadro_horario_horarios s_qhh
                        WHERE s_qhh.dia_semana = qhh.dia_semana
                        AND s_qhh.hora_inicial = qhh.hora_inicial
                        AND s_qhh.ref_cod_quadro_horario = qh.cod_quadro_horario
                        AND s_qhh.hora_final = qhh.hora_final
                        ORDER BY s_qhh.sequencial DESC
                        LIMIT 1
                    )
                GROUP BY
                    qh.ano,
                    ref_cod_escola,
                    qhh.ref_servidor
            ) dados_quadro ON (dados_quadro.ref_servidor = servidor.cod_servidor
                AND dados_quadro.ref_cod_escola = servidor_alocacao.ref_cod_escola
                AND dados_quadro.ano = servidor_alocacao.ano
            )
            WHERE instituicao.cod_instituicao = {$instituicao}
                AND servidor.ativo = 1
                AND servidor_alocacao.ano = {$ano}
                AND ({$escola} = 0 OR servidor_alocacao.ref_cod_escola = {$escola})
                AND (servidor_alocacao.data_saida > now() or servidor_alocacao.data_saida is null)
            GROUP BY
                servidor_alocacao.ref_cod_escola,
                nm_escola,
                servidor_alocacao.ano,
                cod_servidor,
                nm_servidor,
                funcao_servidor.nm_funcao,
                escolaridade.idesco,
                servidor.carga_horaria,
                dados_quadro.carga_horaria_atribuida
            ORDER BY
                nm_escola,
                nm_servidor;
SQL;
    }
}
