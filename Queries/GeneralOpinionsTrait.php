<?php

trait GeneralOpinionsTrait
{
    /**
     * @inheritdoc
     */
    protected function query()
    {
        $matricula = $this->args['matricula'];
        $turma = $this->args['turma'];
        $serie = $this->args['serie'];
        $turma = $this->args['turma'];
        $instituicao = $this->args['instituicao'];
        $escola = $this->args['escola'];
        $ano = $this->args['ano'];
        $curso = $this->args['curso'];
        $situacao_matricula = $this->args['situacao_matricula'];
        $etapa = $this->args['etapa'];
        $alunos_diferenciados = $this->args['alunos_diferenciados'];

        return <<<SQL
                SELECT
            instituicao.nm_instituicao AS nome_instituicao,
            instituicao.nm_responsavel AS nome_responsavel,
            vde.nome AS nm_escola,
            vde.logradouro,
            vde.email,
            vde.telefone AS fone,
            curso.nm_curso AS nome_curso,
            tt.nome AS periodo,
            serie.nm_serie AS nome_serie,
            turma.nm_turma AS nome_turma,
            pessoa.nome AS nome_aluno,
            fisica.data_nasc,
            m.cod_matricula AS matricula,
            vs.texto_situacao AS situacao,
            pg.parecer,
            regra_avaliacao.parecer_descritivo,
            (
                (
                    CASE
                        WHEN fa.tipo_falta = 1 THEN
                            (SELECT SUM(fg.quantidade) FROM modules.falta_geral AS fg WHERE fg.falta_aluno_id = fa.id AND fg.etapa = '$etapa')
                        WHEN fa.tipo_falta = 2 THEN
                            (SELECT SUM(fcc.quantidade) FROM modules.falta_componente_curricular AS fcc WHERE fcc.falta_aluno_id = fa.id AND fcc.etapa = '$etapa')
                    END
                )
            ) AS falta,
            modules.frequencia_da_matricula(m.cod_matricula) AS frequencia,
            (
                SELECT CASE
                    WHEN (SELECT padrao_ano_escolar FROM pmieducar.curso c WHERE cod_curso = curso.cod_curso) = 1 THEN
                        ('$etapa'||'º '||(
                            SELECT
                                modulo.nm_tipo
                            FROM
                                pmieducar.ano_letivo_modulo AS padrao,
                                pmieducar.modulo
                            WHERE
                                padrao.ref_ano = turma.ano
                                AND padrao.ref_ref_cod_escola = escola.cod_escola
                                AND padrao.ref_cod_modulo = modulo.cod_modulo
                                AND modulo.ativo = 1
                                AND padrao.sequencial::varchar = '$etapa' LIMIT 1
                            )
                        )
                    ELSE
                        ('$etapa'||'º '||(
                            SELECT
                                modulo.nm_tipo
                            FROM
                                pmieducar.turma_modulo AS tm,
                                pmieducar.modulo
                                WHERE
                                    tm.ref_cod_turma = turma.cod_turma
                                    AND tm.ref_cod_modulo = modulo.cod_modulo
                                    AND modulo.ativo = 1
                                    AND tm.sequencial::varchar = '$etapa' LIMIT 1
                            )
                        )
                    END
            ) AS etapa
        FROM
            pmieducar.instituicao AS instituicao
        INNER JOIN
            pmieducar.escola AS escola
            ON escola.ref_cod_instituicao = instituicao.cod_instituicao
        INNER JOIN
            relatorio.view_dados_escola AS vde
            ON vde.cod_escola = escola.cod_escola
        INNER JOIN
            pmieducar.escola_curso AS escola_curso
            ON escola_curso.ativo = 1
            AND escola_curso.ref_cod_escola = escola.cod_escola
        INNER JOIN
            pmieducar.curso AS curso
            ON curso.cod_curso = escola_curso.ref_cod_curso
            AND curso.ativo = 1
        INNER JOIN
            pmieducar.escola_serie AS escola_serie
            ON escola_serie.ativo = 1
            AND escola_serie.ref_cod_escola = escola.cod_escola
        INNER JOIN
            pmieducar.serie AS serie
            ON serie.cod_serie = escola_serie.ref_cod_serie
            AND serie.ativo = 1
        INNER JOIN pmieducar.turma AS turma
            ON turma.ref_ref_cod_escola = escola.cod_escola
            AND turma.ref_cod_curso = escola_curso.ref_cod_curso
            AND turma.ref_ref_cod_serie = escola_serie.ref_cod_serie
            AND turma.ativo = 1
        JOIN modules.regra_avaliacao_serie_ano rasa
          ON serie.cod_serie = rasa.serie_id
          AND turma.ano = rasa.ano_letivo
        JOIN modules.regra_avaliacao
          ON rasa.regra_avaliacao_id = regra_avaliacao.id
        INNER JOIN
            pmieducar.turma_turno AS tt
            ON tt.id = turma.turma_turno_id
        INNER JOIN
            pmieducar.matricula_turma AS mt
            ON mt.ref_cod_turma = turma.cod_turma
        INNER JOIN
            pmieducar.matricula AS m
            ON m.cod_matricula = mt.ref_cod_matricula
                          AND m.ano = turma.ano
        INNER JOIN
            pmieducar.aluno AS aluno
            ON aluno.cod_aluno = m.ref_cod_aluno
        INNER JOIN
            cadastro.pessoa AS pessoa
            ON pessoa.idpes = aluno.ref_idpes
        INNER JOIN
            cadastro.fisica AS fisica
            ON fisica.idpes = aluno.ref_idpes
        INNER JOIN
            relatorio.view_situacao AS vs
            ON vs.cod_matricula = m.cod_matricula
            AND vs.cod_turma = turma.cod_turma
            AND mt.sequencial = vs.sequencial
        LEFT JOIN
            modules.parecer_aluno AS pa
            ON pa.matricula_id = m.cod_matricula
        LEFT JOIN
            modules.parecer_geral AS pg
            ON pg.parecer_aluno_id = pa.id
            AND pg.etapa = '$etapa'
        LEFT JOIN
            modules.falta_aluno AS fa
            ON fa.matricula_id = m.cod_matricula
        LEFT JOIN
            modules.falta_geral AS fg
            ON fg.falta_aluno_id = fa.id
            AND fg.etapa = '$etapa'
        LEFT JOIN
            modules.falta_componente_curricular AS fcc
            ON fcc.falta_aluno_id = fa.id
            AND fcc.etapa = '$etapa'
        WHERE
            instituicao.cod_instituicao = $instituicao
            AND escola.cod_escola = $escola
            AND curso.cod_curso = $curso
            AND serie.cod_serie = $serie
            AND turma.cod_turma = $turma
            AND turma.ano = $ano
            AND (
                CASE
                    WHEN $matricula = 0
                THEN TRUE
                ELSE
                    m.cod_matricula = $matricula
                END
            )
            AND vs.cod_situacao = $situacao_matricula
            AND relatorio.exibe_aluno_conforme_parametro_alunos_diferenciados(cod_aluno,  $alunos_diferenciados)
        GROUP BY
            instituicao.nm_instituicao,
            instituicao.nm_responsavel,
            vde.nome,
            vde.logradouro,
            vde.email,
            vde.telefone,
            curso.nm_curso,
            serie.nm_serie,
            turma.nm_turma,
            tt.nome,
            m.cod_matricula,
            pessoa.nome,
            fisica.data_nasc,
            vs.texto_situacao,
            pg.parecer,
            fa.tipo_falta,
            fa.id,
            curso.cod_curso,
            turma.ano,
            escola.cod_escola,
            turma.cod_turma,
            regra_avaliacao.parecer_descritivo,
            (
                CASE
                    WHEN regra_avaliacao.parecer_descritivo = 0 THEN
                        (NULL)
                    WHEN regra_avaliacao.parecer_descritivo <> 3  AND regra_avaliacao.parecer_descritivo <> 6 THEN
                        (CASE WHEN fa.tipo_falta = 1 THEN fg.quantidade WHEN fa.tipo_falta = 2 THEN fcc.quantidade END)
                END
            )

        ORDER BY relatorio.get_texto_sem_caracter_especial(pessoa.nome)
SQL;
    }
}
