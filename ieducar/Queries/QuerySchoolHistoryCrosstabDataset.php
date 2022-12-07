<?php

class QuerySchoolHistoryCrosstabDataset extends QueryBridge
{
    /**
     * @inheritdoc
     */
    protected function query()
    {
        return <<<'SQL'
            SELECT
                ano,
                escola,
                escola_cidade,
                escola_uf,
                nm_serie
            FROM pmieducar.historico_escolar
            WHERE historico_escolar.ref_cod_aluno = $P{aluno}
            AND (
                CASE
                    WHEN $P{ano_ini} = 0 THEN 1=1
                    ELSE historico_escolar.ano >= $P{ano_ini}
                END
            )
            AND (
                CASE
                    WHEN $P{ano_fim} = 0 THEN 1=1
                    ELSE historico_escolar.ano <= $P{ano_fim}
                END
            )
            AND (
                CASE
                    WHEN $P!{nao_emitir_reprovado} THEN historico_escolar.aprovado <> 2
                    ELSE 1=1
                END
            )
            AND (
                CASE
                    WHEN $P{sequencial} = 0 THEN TRUE
                    ELSE (historico_escolar.nm_curso) IN ($P{cursoaluno}::varchar)
                END
            )
            AND historico_escolar.ref_cod_instituicao = $P{instituicao}
            AND historico_escolar.ref_cod_aluno = $P{aluno}
            AND (
                (historico_escolar.aprovado <> 2)
                OR (
                    historico_escolar.sequencial = (
                        SELECT max(he.sequencial)
                        FROM pmieducar.historico_escolar he
                        WHERE he.ref_cod_instituicao = historico_escolar.ref_cod_instituicao
                        AND he.ref_cod_aluno = historico_escolar.ref_cod_aluno
                        AND ativo = 1
                    )
                )
            )
            ORDER BY
                CASE WHEN  $P!{apenas_ultimo_registro} THEN ano END DESC,
                CASE WHEN not $P!{apenas_ultimo_registro} THEN ano END ASC
            LIMIT CASE WHEN $P!{apenas_ultimo_registro} THEN 1 END;
SQL;
    }
}
