<?php

class QueryFinalMapByDisciplineModel1 extends QueryBridge
{
    /**
     * @inheritdoc
     */
    protected function query()
    {
        return <<<'SQL'
                SELECT public.fcn_upper(nm_instituicao) AS nome_instituicao,
                       public.fcn_upper(nm_responsavel) AS nome_responsavel,
                       public.fcn_upper(relatorio.get_nome_escola(escola.cod_escola)) AS nm_escola,
                       vde.logradouro,
                       vde.email,
                       vde.telefone AS fone,
                       to_char(CURRENT_DATE,'dd/mm/yyyy') AS data_atual,
                       to_char(CURRENT_TIMESTAMP, 'HH24:MI:SS') AS hora_atual,
                       public.fcn_upper(curso.nm_curso) AS nome_curso,
                       curso.cod_curso AS codigo_curso,
                       curso.hora_falta * 100 AS curso_hora_falta,
                       public.fcn_upper(serie.nm_serie) AS nome_serie,
                       serie.carga_horaria AS carga_horaria_serie,
                       public.fcn_upper(turma.nm_turma) AS nome_turma,
                       public.fcn_upper(turma_turno.nome) AS periodo,
                       public.fcn_upper(pessoa.nome) AS nome_aluno,
                       matricula.cod_matricula AS matricula,
                       to_char(fisica.data_nasc,'dd/mm/yyyy') AS data_nasc,
                       public.fcn_upper(relatorio.get_pai_aluno(aluno.cod_aluno)) AS nome_pai,
                       public.fcn_upper(relatorio.get_mae_aluno(aluno.cod_aluno)) AS nome_mae,
                       vs.texto_situacao_simplificado AS situacao,
                       vs.texto_situacao AS sit,
                       vs.cod_situacao,
                       vcc.id AS cod_disciplina,
                       public.fcn_upper(vcc.nome) AS nome_disciplina,
                       relatorio.get_situacao_componente(nota_componente_curricular_media.situacao) AS situacao_disciplina,
                       (
                            SELECT SUM(componente_curricular_ano_escolar.carga_horaria::int)
                            FROM modules.componente_curricular_ano_escolar
                            INNER JOIN relatorio.view_componente_curricular ON (true
                                AND componente_curricular_ano_escolar.componente_curricular_id = view_componente_curricular.id
                                AND view_componente_curricular.cod_turma = turma.cod_turma
                                AND view_componente_curricular.cod_serie = serie.cod_serie
                            )
                            WHERE componente_curricular_ano_escolar.ano_escolar_id = serie.cod_serie
                            AND turma.ano = ANY(componente_curricular_ano_escolar.anos_letivos)
                       ) AS total_carga_horaria,
                       ccae.carga_horaria::int AS carga_horaria_componente,

                        (CASE
                          WHEN ncc1.nota_arredondada IS NULL THEN
                            NULL
                          WHEN ISNUMERIC(ncc1.nota_arredondada) THEN
                            TRUNC(ncc1.nota_original::numeric, regra_avaliacao.qtd_casas_decimais)::text
                          ELSE
                            ncc1.nota_arredondada
                         END) AS nota1,

                       (CASE
                          WHEN ncc2.nota_arredondada IS NULL THEN
                            NULL
                          WHEN ISNUMERIC(ncc2.nota_arredondada) THEN
                            TRUNC(ncc2.nota_original::numeric, regra_avaliacao.qtd_casas_decimais)::text
                          ELSE
                            ncc2.nota_arredondada
                         END) AS nota2,

                       (CASE
                          WHEN ncc3.nota_arredondada IS NULL THEN
                            NULL
                          WHEN ISNUMERIC(ncc3.nota_arredondada) THEN
                            TRUNC(ncc3.nota_original::numeric, regra_avaliacao.qtd_casas_decimais)::text
                          ELSE
                            ncc3.nota_arredondada
                         END) AS nota3,

                       (CASE
                          WHEN ncc1.nota_recuperacao IS NULL THEN
                              NULL
                          ELSE
                              TRUNC(ncc1.nota_recuperacao::numeric, regra_avaliacao.qtd_casas_decimais)::text
                       END) AS nota_rec1,
                       (CASE
                          WHEN ncc2.nota_recuperacao IS NULL THEN
                              NULL
                          ELSE
                              TRUNC(ncc2.nota_recuperacao::numeric, regra_avaliacao.qtd_casas_decimais)::text
                       END) AS nota_rec2,
                       (CASE
                          WHEN ncc3.nota_recuperacao IS NULL THEN
                              NULL
                          ELSE
                              TRUNC(ncc3.nota_recuperacao::numeric, regra_avaliacao.qtd_casas_decimais)::text
                       END) AS nota_rec3,

                        CASE WHEN ISNUMERIC(nota_componente_curricular_media.media_arredondada) THEN
                              replace(trunc(nota_componente_curricular_media.media_arredondada::numeric, COALESCE(
                                  regra_avaliacao.qtd_casas_decimais

                                  , 1))::varchar, '.', ',')
                            ELSE
                              nota_componente_curricular_media.media_arredondada
                         END AS media_final,
                       relatorio.media_anual_modelo1(matricula.cod_matricula, vcc.id) AS media_anual,
                       nccm.media_arredondada AS media,
                       nota_exame.nota_arredondada AS nota_exame,
                       (CASE
                            WHEN falta_aluno.tipo_falta = 1 THEN
                                   (SELECT SUM(quantidade)
                                    FROM modules.falta_geral
                                    WHERE falta_geral.falta_aluno_id = falta_aluno.id)
                            ELSE
                                   (SELECT SUM(quantidade)
                                    FROM modules.falta_componente_curricular
                                    WHERE falta_componente_curricular.falta_aluno_id = falta_aluno.id
                                      AND falta_componente_curricular.componente_curricular_id = vcc.id)
                        END) AS total_faltas_componente,
                       (CASE
                            WHEN
                                   (SELECT SUM(dias_letivos)
                                    FROM relatorio.view_dados_modulo vdm
                                    WHERE vdm.cod_turma = $P{turma}) > 0 THEN
                                   (SELECT SUM(dias_letivos)
                                    FROM relatorio.view_dados_modulo vdm
                                    WHERE vdm.cod_turma = turma.cod_turma)
                            ELSE serie.dias_letivos
                        END)AS dias_letivos,
                       (CASE
                            WHEN falta_aluno.tipo_falta = 1 THEN
                                   trim(to_char(modules.frequencia_da_matricula(matricula.cod_matricula),'99999999999D99'))
                            ELSE
                                   modules.frequencia_por_componente(matricula.cod_matricula, vcc.id, turma.cod_turma)
                        END) AS frequencia,

                       (CASE
                            WHEN ncc1.nota_original::numeric >= COALESCE(ncc1.nota_recuperacao::numeric, 0) THEN
                                TRUNC((ncc1.nota_original::numeric * 3), regra_avaliacao.qtd_casas_decimais)
                            WHEN COALESCE(ncc1.nota_recuperacao::numeric, 0) > ncc1.nota_original::numeric THEN
                                TRUNC((ncc1.nota_recuperacao::numeric * 3), regra_avaliacao.qtd_casas_decimais)
                            ELSE 0
                        END)::varchar AS res_1x3,
                       (CASE
                            WHEN ncc2.nota_original::numeric >= COALESCE(ncc2.nota_recuperacao::numeric, 0) THEN
                                TRUNC((ncc2.nota_original::numeric * 3), regra_avaliacao.qtd_casas_decimais)
                            WHEN COALESCE(ncc2.nota_recuperacao::numeric, 0) > ncc2.nota_original::numeric THEN
                                TRUNC((ncc2.nota_recuperacao::numeric * 3), regra_avaliacao.qtd_casas_decimais)
                            ELSE 0
                        END)::varchar AS res_2x3,
                       (CASE
                            WHEN ncc3.nota_original::numeric >= COALESCE(ncc3.nota_recuperacao::numeric, 0) THEN
                                TRUNC((ncc3.nota_original::numeric * 4), regra_avaliacao.qtd_casas_decimais)
                            WHEN COALESCE(ncc3.nota_recuperacao::numeric, 0) > ncc3.nota_original::numeric THEN
                                TRUNC((ncc3.nota_recuperacao::numeric * 4), regra_avaliacao.qtd_casas_decimais)
                            ELSE 0
                        END)::varchar AS res_3x4,
                       falta_aluno.tipo_falta,
                        matricula_turma.sequencial_fechamento
                FROM pmieducar.instituicao
                INNER JOIN pmieducar.escola ON (escola.ref_cod_instituicao = instituicao.cod_instituicao)
                INNER JOIN pmieducar.escola_curso ON (escola_curso.ativo = 1
                                                      AND escola_curso.ref_cod_escola = escola.cod_escola)
                INNER JOIN relatorio.view_dados_escola vde ON (vde.cod_escola = escola.cod_escola)
                INNER JOIN pmieducar.curso ON (curso.cod_curso = escola_curso.ref_cod_curso
                                               AND curso.ativo = 1)
                INNER JOIN pmieducar.escola_serie ON (escola_serie.ativo = 1
                                                      AND escola_serie.ref_cod_escola = escola.cod_escola)
                INNER JOIN pmieducar.serie ON (serie.cod_serie = escola_serie.ref_cod_serie
                                               AND serie.ativo = 1)
                INNER JOIN pmieducar.turma ON (turma.ref_ref_cod_escola = escola.cod_escola
                                               AND turma.ativo = 1)
                INNER JOIN pmieducar.turma_turno ON (turma_turno.id = turma.turma_turno_id)
                INNER JOIN relatorio.view_componente_curricular vcc ON (vcc.cod_turma = turma.cod_turma
                  AND vcc.cod_serie = serie.cod_serie)
                INNER JOIN modules.componente_curricular_ano_escolar ccae ON (ccae.componente_curricular_id = vcc.id
                                                                              AND ccae.ano_escolar_id = serie.cod_serie
                                                                              AND turma.ano = ANY(ccae.anos_letivos))
                INNER JOIN pmieducar.matricula_turma ON (matricula_turma.ref_cod_turma = turma.cod_turma)
                INNER JOIN pmieducar.matricula ON (matricula.cod_matricula = matricula_turma.ref_cod_matricula
                                                   AND matricula.ref_ref_cod_escola = escola.cod_escola
                                                   AND matricula.ref_cod_curso = curso.cod_curso
                                                   AND matricula.ref_ref_cod_serie = serie.cod_serie)
                INNER JOIN relatorio.view_situacao vs ON (vs.cod_matricula = matricula.cod_matricula
                                                          AND vs.cod_turma = matricula_turma.ref_cod_turma
                                                          AND vs.sequencial = matricula_turma.sequencial
                                                          AND vs.cod_situacao = $P{situacao})
                INNER JOIN pmieducar.aluno ON (aluno.cod_aluno = matricula.ref_cod_aluno)
                INNER JOIN cadastro.pessoa ON (pessoa.idpes = aluno.ref_idpes)
                INNER JOIN cadastro.fisica ON (fisica.idpes = aluno.ref_idpes)
                INNER JOIN modules.regra_avaliacao_serie_ano ON (regra_avaliacao_serie_ano.serie_id = serie.cod_serie
                                                                   AND regra_avaliacao_serie_ano.ano_letivo = turma.ano)
                INNER JOIN modules.regra_avaliacao ON (regra_avaliacao.id = regra_avaliacao_serie_ano.regra_avaliacao_id)
                LEFT JOIN modules.nota_aluno ON (nota_aluno.matricula_id = matricula.cod_matricula)
                LEFT JOIN modules.nota_componente_curricular ncc1 ON (ncc1.nota_aluno_id = nota_aluno.id
                                                                      AND ncc1.componente_curricular_id = vcc.id
                                                                      AND ncc1.etapa = '1')
                LEFT JOIN modules.nota_componente_curricular ncc2 ON (ncc2.nota_aluno_id = nota_aluno.id
                                                                      AND ncc2.componente_curricular_id = vcc.id
                                                                      AND ncc2.etapa = '2')
                LEFT JOIN modules.nota_componente_curricular ncc3 ON (ncc3.nota_aluno_id = nota_aluno.id
                                                                      AND ncc3.componente_curricular_id = vcc.id
                                                                      AND ncc3.etapa = '3')
                LEFT JOIN modules.nota_componente_curricular_media nccm ON (nccm.nota_aluno_id = nota_aluno.id
                                                                            AND nccm.componente_curricular_id = vcc.id)
                LEFT JOIN modules.nota_componente_curricular nota_exame ON (nota_exame.nota_aluno_id = nota_aluno.id
                                                                            AND nota_exame.componente_curricular_id = vcc.id
                                                                            AND nota_exame.etapa = 'Rc')
                LEFT JOIN modules.nota_componente_curricular_media ON (nota_componente_curricular_media.componente_curricular_id = vcc.id
                                                                                                        AND nota_componente_curricular_media.nota_aluno_id = nota_aluno.id)
                LEFT JOIN modules.falta_aluno ON (falta_aluno.matricula_id = matricula.cod_matricula)
                WHERE instituicao.cod_instituicao = $P{instituicao}
                  AND turma.ano = $P{ano}
                  AND escola.cod_escola = $P{escola}
                  AND curso.cod_curso = $P{curso}
                  AND serie.cod_serie = $P{serie}
                  AND turma.cod_turma = $P{turma}
                  AND CASE WHEN $P{disciplina} = 0 THEN TRUE ELSE vcc.id = $P{disciplina} END
                ORDER BY vcc.ordenamento,
                                  nome_disciplina,
                                  matricula_turma.sequencial_fechamento,
                                  relatorio.get_texto_sem_caracter_especial(pessoa.nome)
SQL;
    }
}
