<?php

trait SchoolHistoryCrosstabDatasetTrait
{
    /**
     * @inheritdoc
     */
    protected function query()
    {
        $aluno = $this->args['aluno'];
        $ano_ini = $this->args['ano_ini'];
        $ano_fim = $this->args['ano_fim'];
        $sequencial = $this->args['sequencial'];
        $instituicao = $this->args['instituicao'];
        $nao_emitir_reprovado = $this->args['nao_emitir_reprovado'];
        $cursoaluno = $this->args['cursoaluno'];
        $apenas_ultimo_registro = $this->args['apenas_ultimo_registro'];

        return <<<SQL
            SELECT
                ano,
                escola,
                escola_cidade,
                escola_uf,
                nm_serie
            FROM pmieducar.historico_escolar
            WHERE historico_escolar.ref_cod_aluno = $aluno
            AND (
                CASE
                    WHEN $ano_ini = 0 THEN 1=1
                    ELSE historico_escolar.ano >= $ano_ini
                END
            )
            AND (
                CASE
                    WHEN $ano_fim = 0 THEN 1=1
                    ELSE historico_escolar.ano <= $ano_fim
                END
            )
            AND (
                CASE
                    WHEN $nao_emitir_reprovado THEN historico_escolar.aprovado <> 2
                    ELSE 1=1
                END
            )
            AND (
                CASE
                    WHEN $sequencial = 0 THEN TRUE
                    ELSE (historico_escolar.nm_curso) IN ($cursoaluno::varchar)
                END
            )
            AND historico_escolar.ref_cod_instituicao = $instituicao
            AND historico_escolar.ref_cod_aluno = $aluno
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
                CASE WHEN  $apenas_ultimo_registro THEN ano END DESC,
                CASE WHEN not $apenas_ultimo_registro THEN ano END ASC
            LIMIT CASE WHEN $apenas_ultimo_registro THEN 1 END;
SQL;
    }
}
