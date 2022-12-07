<?php

class QueryFinalMapByDisciplineModel2 extends QueryBridge
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
                       regra_avaliacao.qtd_casas_decimais AS casas_decimais,
                       relatorio.get_qtde_modulo(turma.cod_turma) AS qtd_modulos,
                       relatorio.get_situacao_componente(nota_componente_curricular_media.situacao) AS situacao_disciplina,

                       (CASE
                          WHEN ncc1.nota_arredondada IS NULL THEN
                            NULL
                          WHEN ISNUMERIC(ncc1.nota_arredondada) THEN
                            TRUNC(ncc1.nota_arredondada::numeric, regra_avaliacao.qtd_casas_decimais)::text
                          ELSE
                            ncc1.nota_arredondada
                         END) AS nota1,

                       (CASE
                          WHEN ncc2.nota_arredondada IS NULL THEN
                            NULL
                          WHEN ISNUMERIC(ncc2.nota_arredondada) THEN
                            TRUNC(ncc2.nota_arredondada::numeric, regra_avaliacao.qtd_casas_decimais)::text
                          ELSE
                            ncc2.nota_arredondada
                         END) AS nota2,

                       (CASE
                          WHEN ncc3.nota_arredondada IS NULL THEN
                            NULL
                          WHEN ISNUMERIC(ncc3.nota_arredondada) THEN
                            TRUNC(ncc3.nota_arredondada::numeric, regra_avaliacao.qtd_casas_decimais)::text
                          ELSE
                            ncc3.nota_arredondada
                         END) AS nota3,

                       (CASE
                          WHEN ncc4.nota_arredondada IS NULL THEN
                            NULL
                          WHEN ISNUMERIC(ncc4.nota_arredondada) THEN
                            TRUNC(ncc4.nota_arredondada::numeric, regra_avaliacao.qtd_casas_decimais)::text
                          ELSE
                            ncc4.nota_arredondada
                         END) AS nota4,
                CASE WHEN ISNUMERIC(nota_componente_curricular_media.media_arredondada) THEN
                                replace(trunc(nota_componente_curricular_media.media_arredondada::numeric, COALESCE(
                                        regra_avaliacao.qtd_casas_decimais

                                    , 1))::varchar, '.', ',')
                            ELSE
                                nota_componente_curricular_media.media_arredondada
                           END AS media_final,
                       CASE
                            WHEN ncc4.nota is not null THEN
                                ROUND((ncc1.nota + ncc2.nota + ncc3.nota + ncc4.nota) / 4, regra_avaliacao.qtd_casas_decimais)::varchar
                            WHEN ncc3.nota is not null THEN
                                ROUND((ncc1.nota + ncc2.nota + ncc3.nota) / 3, regra_avaliacao.qtd_casas_decimais)::varchar
                            WHEN ncc2.nota is not null THEN
                                ROUND((ncc1.nota + ncc2.nota) / 2, regra_avaliacao.qtd_casas_decimais)::varchar
                            ELSE
                                ROUND(ncc1.nota, regra_avaliacao.qtd_casas_decimais)::varchar
                        END AS media_anual,
                       nccm.media_arredondada AS media,
                       nota_exame.nota_arredondada AS nota_exame,
                       fg1.quantidade AS falta_geral1,
                       fg2.quantidade AS falta_geral2,
                       fg3.quantidade AS falta_geral3,
                       fg4.quantidade AS falta_geral4,
                       fcc1.quantidade AS falta_componente1,
                       fcc2.quantidade AS falta_componente2,
                       fcc3.quantidade AS falta_componente3,
                       fcc4.quantidade AS falta_componente4,
                       modules.frequencia_da_matricula(matricula.cod_matricula) AS frequencia_componente,
                       modules.frequencia_por_componente(matricula.cod_matricula, vcc.id, turma.cod_turma) AS frequencia,
                       falta_aluno.tipo_falta,
                       matricula_turma.sequencial_fechamento,
                       regra_avaliacao.tipo_nota
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
                                                                              AND ccae.ano_escolar_id = serie.cod_serie)
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
                LEFT JOIN modules.nota_componente_curricular ncc4 ON (ncc4.nota_aluno_id = nota_aluno.id
                                                                      AND ncc4.componente_curricular_id = vcc.id
                                                                      AND ncc4.etapa = '4')
                LEFT JOIN modules.nota_componente_curricular_media nccm ON (nccm.nota_aluno_id = nota_aluno.id
                                                                            AND nccm.componente_curricular_id = vcc.id)
                LEFT JOIN modules.nota_componente_curricular nota_exame ON (nota_exame.nota_aluno_id = nota_aluno.id
                                                                            AND nota_exame.componente_curricular_id = vcc.id
                                                                            AND nota_exame.etapa = 'Rc')
                LEFT JOIN modules.nota_componente_curricular_media ON (nota_componente_curricular_media.componente_curricular_id = vcc.id
                                                                                                        AND nota_componente_curricular_media.nota_aluno_id = nota_aluno.id)
                LEFT JOIN modules.falta_aluno ON (falta_aluno.matricula_id = matricula.cod_matricula)
                LEFT JOIN modules.falta_geral fg1 ON (fg1.falta_aluno_id = falta_aluno.id
                                                 AND fg1.etapa = '1')
                LEFT JOIN modules.falta_geral fg2 ON (fg2.falta_aluno_id = falta_aluno.id
                                                 AND fg2.etapa = '2')
                LEFT JOIN modules.falta_geral fg3 ON (fg3.falta_aluno_id = falta_aluno.id
                                                 AND fg3.etapa = '3')
                LEFT JOIN modules.falta_geral fg4 ON (fg4.falta_aluno_id = falta_aluno.id
                                                 AND fg4.etapa = '4')
                LEFT JOIN modules.falta_componente_curricular fcc1 ON (fcc1.falta_aluno_id = falta_aluno.id
                                                                  AND fcc1.componente_curricular_id = vcc.id
                                                                  AND fcc1.etapa = '1')
                LEFT JOIN modules.falta_componente_curricular fcc2 ON (fcc2.falta_aluno_id = falta_aluno.id
                                                                  AND fcc2.componente_curricular_id = vcc.id
                                                                  AND fcc2.etapa = '2')
                LEFT JOIN modules.falta_componente_curricular fcc3 ON (fcc3.falta_aluno_id = falta_aluno.id
                                                                  AND fcc3.componente_curricular_id = vcc.id
                                                                  AND fcc3.etapa = '3')
                LEFT JOIN modules.falta_componente_curricular fcc4 ON (fcc4.falta_aluno_id = falta_aluno.id
                                                                  AND fcc4.componente_curricular_id = vcc.id
                                                                  AND fcc4.etapa = '4')
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
