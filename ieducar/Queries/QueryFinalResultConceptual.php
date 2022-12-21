<?php

class QueryFinalResultConceptual extends QueryBridge
{
    /**
     * @inheritdoc
     */
    protected function query()
    {
        return <<<'SQL'
            SELECT instituicao.nm_instituicao AS nm_instituicao,
                   instituicao.nm_responsavel AS nm_responsavel,
                   curso.nm_curso   AS nome_curso,
                   serie.nm_serie   AS nome_serie,
                   turma.nm_turma   AS nome_turma,
                   turma.ano        AS ano,
                   turma_turno.nome AS periodo,
                   sequencial_fechamento AS sequencial_fechamento,
                   matricula.cod_matricula,
                   relatorio.get_texto_sem_caracter_especial(pessoa.nome) AS nm_aluno,
                   relatorio.get_texto_sem_caracter_especial(fisica.nome_social) AS nm_social_aluno,
                   aluno.cod_aluno  AS cod_aluno,
                   round((modules.frequencia_da_matricula(matricula.cod_matricula))::numeric, 1) AS frequencia_geral,
                   view_situacao.texto_situacao_simplificado AS situacao,
                   componente_curricular.abreviatura AS nm_componente_curricular,
                   componente_curricular.id AS componente_id,
                   CASE
                          WHEN matricula.aprovado IN (4,
                                                      6) THEN '-'
                          WHEN matricula_turma.remanejado THEN '-'
            WHEN matricula.dependencia AND NOT EXISTS(select 1 from pmieducar.disciplina_dependencia where ref_cod_matricula = matricula.cod_matricula AND componente_curricular.id = ref_cod_disciplina) THEN  '-'
                          ELSE
                               tabela_arredondamento_valor.descricao
                   END AS media,
                   nota_aluno.id AS nota_id,
                   turma.ano AS ano
            FROM pmieducar.instituicao
            INNER JOIN pmieducar.escola ON (escola.ref_cod_instituicao = instituicao.cod_instituicao)
            INNER JOIN pmieducar.escola_curso ON (escola_curso.ativo = 1
                                                  AND escola_curso.ref_cod_escola = escola.cod_escola)
            INNER JOIN pmieducar.curso ON (curso.cod_curso = escola_curso.ref_cod_curso
                                           AND curso.ativo = 1)
            INNER JOIN pmieducar.escola_serie ON (escola_serie.ativo = 1
                                                  AND escola_serie.ref_cod_escola = escola.cod_escola)
            INNER JOIN pmieducar.serie ON (serie.cod_serie = escola_serie.ref_cod_serie
                                           AND serie.ativo = 1)
            INNER JOIN pmieducar.turma ON (turma.ref_ref_cod_escola = escola.cod_escola
                                           AND turma.ativo = 1)
            INNER JOIN pmieducar.turma_turno ON (turma_turno.id = turma.turma_turno_id)
            INNER JOIN pmieducar.matricula_turma ON (matricula_turma.ref_cod_turma = turma.cod_turma)
            INNER JOIN pmieducar.matricula ON (matricula.cod_matricula = matricula_turma.ref_cod_matricula
                                           AND matricula.ref_ref_cod_serie = serie.cod_serie
                                           AND matricula.ref_cod_curso = curso.cod_curso
                                           AND matricula.ativo = 1)
            INNER JOIN pmieducar.aluno ON (aluno.cod_aluno = matricula.ref_cod_aluno)
            INNER JOIN cadastro.pessoa ON (pessoa.idpes = aluno.ref_idpes)
            INNER JOIN cadastro.fisica ON (fisica.idpes = aluno.ref_idpes)
            INNER JOIN relatorio.view_situacao ON (view_situacao.cod_matricula = matricula.cod_matricula
                                                   AND view_situacao.cod_turma = matricula_turma.ref_cod_turma
                                                   AND matricula_turma.sequencial = view_situacao.sequencial)
             LEFT JOIN modules.componente_curricular_turma ON (componente_curricular_turma.turma_id = turma.cod_turma
                           AND componente_curricular_turma.escola_id = escola.cod_escola)
             LEFT JOIN pmieducar.escola_serie_disciplina ON (escola_serie_disciplina.ref_ref_cod_escola = escola.cod_escola
                                                             AND escola_serie_disciplina.ref_ref_cod_serie = serie.cod_serie
                                                             AND turma.ano = ANY(escola_serie_disciplina.anos_letivos)
                                                             AND escola_serie_disciplina.ativo = 1)
            INNER JOIN modules.componente_curricular ON (componente_curricular.id = escola_serie_disciplina.ref_cod_disciplina
                                                         OR componente_curricular.id = componente_curricular_turma.componente_curricular_id)
            LEFT JOIN modules.nota_aluno ON (nota_aluno.matricula_id = matricula.cod_matricula)
            LEFT JOIN modules.nota_componente_curricular_media nccm ON (nccm.nota_aluno_id = nota_aluno.id
                                                                         AND nccm.componente_curricular_id = componente_curricular.id)
            LEFT JOIN modules.regra_avaliacao_serie_ano rasa
              ON rasa.serie_id = serie.cod_serie
              AND turma.ano = rasa.ano_letivo
            LEFT JOIN modules.regra_avaliacao ON regra_avaliacao.id = rasa.regra_avaliacao_id
            LEFT JOIN modules.tabela_arredondamento_valor ON (tabela_arredondamento_valor.tabela_arredondamento_id = regra_avaliacao.tabela_arredondamento_id
                                                               AND tabela_arredondamento_valor.nome = nccm.media_arredondada)
            WHERE instituicao.cod_instituicao = $P{instituicao}
              AND matricula.ano = $P{ano}
              AND escola.cod_escola = $P{escola}
              AND curso.cod_curso = $P{curso}
              AND serie.cod_serie = $P{serie}
              AND turma.cod_turma = $P{turma}
              AND cod_situacao = $P{situacao}
              AND (CASE WHEN $P{areas_conhecimento} = '0' THEN TRUE ELSE componente_curricular.area_conhecimento_id IN ($P!{areas_conhecimento}) END)
              AND matricula_turma.sequencial = (SELECT max(mt.sequencial)
                                                FROM pmieducar.matricula_turma mt
                                                WHERE mt.ref_cod_matricula = matricula.cod_matricula
                                                  AND mt.ref_cod_turma = turma.cod_turma
                                                  AND (mt.ativo = 1 OR (
                                                    mt.transferido OR
                                                    mt.remanejado OR
                                                    mt.reclassificado OR
                                                    mt.abandono OR
                                                    mt.falecido
                                                  )))
              AND NOT EXISTS (SELECT *
                                FROM pmieducar.matricula_turma mt
                               INNER JOIN pmieducar.matricula m ON (m.cod_matricula = mt.ref_cod_matricula)
                               WHERE mt.ref_cod_turma = matricula_turma.ref_cod_turma
                                 AND mt.ref_cod_matricula <> matricula_turma.ref_cod_matricula
                                 AND m.ref_cod_aluno = matricula.ref_cod_aluno
                                 AND mt.data_enturmacao > matricula_turma.data_enturmacao
                                 AND m.ativo = 1)
            ORDER BY sequencial_fechamento,
                     nm_social_aluno,
                     nm_aluno,
                     situacao,
                     nm_componente_curricular,
                     media,
                     componente_curricular.id
SQL;
    }
}
