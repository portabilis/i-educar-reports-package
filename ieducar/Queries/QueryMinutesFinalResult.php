<?php

class QueryMinutesFinalResult extends QueryBridge
{
    /**
     * @inheritdoc
     */
    protected function query()
    {
        return <<<'SQL'
                SELECT
                       instituicao.cod_instituicao,
                       instituicao.nm_instituicao,
                       instituicao.cidade as cidade_instituicao,
                       instituicao.nm_responsavel,
                       escola.cod_escola,
                       relatorio.get_nome_escola(escola.cod_escola) as nm_escola,
                       curso.nm_curso,
                       serie.nm_serie,
                       serie.cod_serie,
                       turma.nm_turma,
                       turma_turno.nome as turno_turma,
                       escola_ano_letivo.ano as ano,
                       aluno.cod_aluno,
                       matricula.cod_matricula,
                       matricula_turma.sequencial_fechamento,
                       upper(pessoa.nome) as aluno,
                       to_char(fisica.data_nasc, 'dd/mm/yyyy') as data_nasc,
                       componente_curricular_ano_escolar.carga_horaria::int AS carga_horaria_componente,
                       cc.nome as componente_curricular,
                       cc.id as componente_curricular_id,
                       nota_componente_curricular_media.media_arredondada as nota,
                       nota_componente_curricular_media.media as media,
                       falta_componente.quantidade AS faltas_componente,
                       matricula.aprovado,
                       matricula.dependencia,
                       view_situacao.texto_situacao_simplificado as situacao,
                       relatorio.get_total_faltas(matricula.cod_matricula)::int as faltas_gerais,
                       ROUND(modules.frequencia_da_matricula(matricula.cod_matricula)::numeric, 1)as frequencia_geral,
                       public.data_para_extenso(
                        COALESCE(
                        (
                            SELECT max(data_fim)
                            FROM pmieducar.turma_modulo
                            where ref_cod_turma = matricula_turma.ref_cod_turma
                        ), (
                            select max(data_fim)
                            from pmieducar.ano_letivo_modulo
                            where ref_ref_cod_escola = escola.cod_escola
                            and ref_ano = turma.ano
                        ), now()
                        )::date

                        ) as data_extenso,
                       (select count(1)
                          from pmieducar.matricula_turma mt
                         where mt.ref_cod_turma = matricula_turma.ref_cod_turma
                           and mt.remanejado is true) as remanejado
                FROM pmieducar.instituicao
                INNER JOIN pmieducar.escola ON pmieducar.escola.ref_cod_instituicao = pmieducar.instituicao.cod_instituicao
                INNER JOIN pmieducar.escola_ano_letivo ON pmieducar.escola_ano_letivo.ref_cod_escola = pmieducar.escola.cod_escola
                INNER JOIN pmieducar.escola_curso ON escola_curso.ativo = 1
                                                  AND escola_curso.ref_cod_escola = escola.cod_escola
                INNER JOIN pmieducar.curso ON curso.cod_curso = escola_curso.ref_cod_curso
                                              AND curso.ativo = 1
                INNER JOIN pmieducar.escola_serie ON escola_serie.ativo = 1
                                    AND escola_serie.ref_cod_escola = escola.cod_escola
                INNER JOIN pmieducar.serie ON serie.cod_serie = escola_serie.ref_cod_serie
                                             AND serie.ativo = 1
                INNER JOIN pmieducar.turma ON turma.ref_ref_cod_escola = escola.cod_escola
                                             AND turma.ativo = 1
                INNER JOIN relatorio.view_componente_curricular ON view_componente_curricular.cod_turma = turma.cod_turma
                    AND view_componente_curricular.cod_serie = serie.cod_serie
                INNER JOIN pmieducar.turma_turno ON turma_turno .id = turma.turma_turno_id
                INNER JOIN pmieducar.matricula_turma ON matricula_turma.ref_cod_turma = turma.cod_turma
                INNER JOIN pmieducar.matricula ON matricula.cod_matricula = matricula_turma.ref_cod_matricula
                    AND matricula.ref_ref_cod_serie = serie.cod_serie
                    AND matricula.ref_cod_curso = curso.cod_curso
                INNER JOIN relatorio.view_situacao ON view_situacao.cod_matricula = matricula.cod_matricula
                                          AND view_situacao.cod_turma = matricula_turma.ref_cod_turma
                                      AND view_situacao.sequencial = matricula_turma.sequencial --Garante que irá trazer a enturmação correta
                INNER JOIN pmieducar.aluno ON pmieducar.matricula.ref_cod_aluno = pmieducar.aluno.cod_aluno
                INNER JOIN cadastro.pessoa ON aluno.ref_idpes = cadastro.pessoa.idpes
                INNER JOIN cadastro.fisica ON pessoa.idpes = fisica.idpes
                LEFT JOIN modules.nota_aluno na ON na.matricula_id = matricula.cod_matricula
                LEFT JOIN modules.nota_componente_curricular_media ON nota_componente_curricular_media.nota_aluno_id = na.id
                                                                   AND nota_componente_curricular_media.componente_curricular_id = view_componente_curricular.id
                LEFT JOIN modules.componente_curricular_ano_escolar ON componente_curricular_ano_escolar.ano_escolar_id = serie.cod_serie
                                                                        AND componente_curricular_ano_escolar.componente_curricular_id = view_componente_curricular.id
                                                                        AND escola_ano_letivo.ano = any(componente_curricular_ano_escolar.anos_letivos)
                LEFT JOIN modules.falta_aluno ON falta_aluno.matricula_id = matricula.cod_matricula
                LEFT JOIN modules.falta_componente_curricular falta_componente ON falta_componente.falta_aluno_id = falta_aluno.id
                                                                                    AND falta_componente.componente_curricular_id = view_componente_curricular.id
                LEFT JOIN modules.componente_curricular cc on cc.id = view_componente_curricular.id
                WHERE instituicao.cod_instituicao = $P{instituicao}
                   AND pmieducar.escola_ano_letivo.ano = $P{ano}
                   AND pmieducar.matricula.ano = pmieducar.escola_ano_letivo.ano
                   AND escola.cod_escola = $P{escola}
                   AND curso.cod_curso = $P{curso}
                   AND serie.cod_serie = $P{serie}
                   AND turma.cod_turma = $P{turma}
                   AND view_situacao.cod_situacao = $P{situacao}
                   AND CASE WHEN $P!{filtro_areas_conhecimento} THEN true ELSE cc.area_conhecimento_id IN ($P!{areas_conhecimento}) END
                ORDER BY nm_escola,
                          curso.nm_curso,
                          serie.nm_serie,
                          turma.nm_turma,
                          turma_turno.nome,
                          matricula_turma.sequencial_fechamento,
                          pessoa.nome,
                          cc.nome
SQL;
    }
}
