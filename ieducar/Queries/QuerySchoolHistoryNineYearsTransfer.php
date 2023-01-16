<?php

class QuerySchoolHistoryNineYearsTransfer extends QueryBridge
{
    /**
     * @inheritdoc
     */
    protected function query()
    {
        return <<<'SQL'
                    SELECT matricula.cod_matricula AS matricula,
                           serie.nm_serie AS nome_serie,
                           to_char(matricula.data_cancel,'dd/mm/yyyy') AS data_transferencia,
                           public.fcn_upper(pessoa.nome) AS nome_aluno,
                           serie.etapa_curso AS etapa_curso,
                           to_char(CURRENT_DATE,'dd/mm/yyyy') AS data_atual,

                      (SELECT CASE
                                  WHEN nota_componente_curricular.nota_arredondada = '10' THEN '10,0'
                                  WHEN char_length(nota_componente_curricular.nota_arredondada) = 1 THEN replace(nota_componente_curricular.nota_arredondada,'.',',') || ',0'
                                  ELSE replace(nota_componente_curricular.nota_arredondada,'.',',')
                              END
                       FROM modules.nota_componente_curricular,
                            modules.nota_aluno
                       WHERE nota_componente_curricular.nota_aluno_id = nota_aluno.id
                         AND nota_componente_curricular.componente_curricular_id = view_componente_curricular.id
                         AND nota_aluno.matricula_id = matricula.cod_matricula
                         AND nota_componente_curricular.etapa = '1') AS nota1,

                      (SELECT CASE
                                  WHEN nota_componente_curricular.nota_arredondada = '10' THEN '10,0'
                                  WHEN char_length(nota_componente_curricular.nota_arredondada) = 1 THEN replace(nota_componente_curricular.nota_arredondada,'.',',') || ',0'
                                  ELSE replace(nota_componente_curricular.nota_arredondada,'.',',')
                              END
                       FROM modules.nota_componente_curricular,
                            modules.nota_aluno
                       WHERE nota_componente_curricular.nota_aluno_id = nota_aluno.id
                         AND nota_componente_curricular.componente_curricular_id = view_componente_curricular.id
                         AND nota_aluno.matricula_id = matricula.cod_matricula
                         AND nota_componente_curricular.etapa = '2') AS nota2,

                      (SELECT CASE
                                  WHEN nota_componente_curricular.nota_arredondada = '10' THEN '10,0'
                                  WHEN char_length(nota_componente_curricular.nota_arredondada) = 1 THEN replace(nota_componente_curricular.nota_arredondada,'.',',') || ',0'
                                  ELSE replace(nota_componente_curricular.nota_arredondada,'.',',')
                              END
                       FROM modules.nota_componente_curricular,
                            modules.nota_aluno
                       WHERE nota_componente_curricular.nota_aluno_id = nota_aluno.id
                         AND nota_componente_curricular.componente_curricular_id = view_componente_curricular.id
                         AND nota_aluno.matricula_id = matricula.cod_matricula
                         AND nota_componente_curricular.etapa = '3') AS nota3,

                      (SELECT CASE
                                  WHEN nota_componente_curricular.nota_arredondada = '10' THEN '10,0'
                                  WHEN char_length(nota_componente_curricular.nota_arredondada) = 1 THEN replace(nota_componente_curricular.nota_arredondada,'.',',') || ',0'
                                  ELSE replace(nota_componente_curricular.nota_arredondada,'.',',')
                              END
                       FROM modules.nota_componente_curricular,
                            modules.nota_aluno
                       WHERE nota_componente_curricular.nota_aluno_id = nota_aluno.id
                         AND nota_componente_curricular.componente_curricular_id = view_componente_curricular.id
                         AND nota_aluno.matricula_id = matricula.cod_matricula
                         AND nota_componente_curricular.etapa = '4') AS nota4,

                      (SELECT dias_letivos
                       FROM pmieducar.ano_letivo_modulo
                       WHERE ref_ref_cod_escola = matricula.ref_ref_cod_escola
                         AND ref_ano = matricula.ano
                         AND sequencial = 1) AS dias_letivos_1sementre,

                      (SELECT dias_letivos
                       FROM pmieducar.ano_letivo_modulo
                       WHERE ref_ref_cod_escola = matricula.ref_ref_cod_escola
                         AND ref_ano = matricula.ano
                         AND sequencial = 2) AS dias_letivos_2sementre,

                      (SELECT dias_letivos
                       FROM pmieducar.ano_letivo_modulo
                       WHERE ref_ref_cod_escola = matricula.ref_ref_cod_escola
                         AND ref_ano = matricula.ano
                         AND sequencial = 3) AS dias_letivos_3sementre,

                      (SELECT dias_letivos
                       FROM pmieducar.ano_letivo_modulo
                       WHERE ref_ref_cod_escola = matricula.ref_ref_cod_escola
                         AND ref_ano = matricula.ano
                         AND sequencial = 4) AS dias_letivos_4sementre,

                      (SELECT sum(falta_geral.quantidade)
                       FROM modules.falta_geral,
                            modules.falta_aluno
                       WHERE falta_geral.falta_aluno_id = falta_aluno.id
                         AND falta_aluno.matricula_id = matricula.cod_matricula
                         AND falta_geral.etapa IN ('1',
                                                   '2',
                                                   '3',
                                                   '4')) AS total_faltas,

                      (SELECT sum(falta_componente_curricular.quantidade)
                       FROM modules.falta_componente_curricular,
                            modules.falta_aluno
                       WHERE falta_componente_curricular.falta_aluno_id = falta_aluno.id
                         AND falta_aluno.matricula_id = matricula.cod_matricula
                         AND falta_componente_curricular.componente_curricular_id = view_componente_curricular.id
                         AND falta_componente_curricular.etapa IN ('1',
                                                                   '2',
                                                                   '3',
                                                                   '4')
                         AND falta_aluno.tipo_falta = 2) AS total_faltas_componente,
                           view_componente_curricular.nome AS nome_disciplina,

                      (SELECT ' CONCLUIU O '|| fcn_upper(he.nm_serie) ||' DO ENSINO FUNDAMENTAL DE NOVE ANOS NO ANO LETIVO DE '|| he.ano
                       FROM pmieducar.historico_escolar he
                       WHERE he.ref_cod_aluno = aluno.cod_aluno
                         AND ativo = 1
                         AND he.extra_curricular = 0
                         AND aprovado IN (1,
                                          12,
                                          13) ORDER BY he.ano DESC LIMIT 1) AS nome_serie_aux,

                      (SELECT ' ESTÁ APTO(A) A PROSSEGUIR SEUS ESTUDOS NO ' || fcn_upper(he.nm_serie) ||' DO ENSINO FUNDAMENTAL DE NOVE ANOS NO ANO LETIVO DE '|| he.ano
                       FROM pmieducar.historico_escolar he
                       WHERE he.ref_cod_aluno = aluno.cod_aluno
                         AND he.ativo = 1
                         AND he.aprovado = 4
                         AND he.extra_curricular = 0
                         AND
                           (SELECT count(*)
                            FROM pmieducar.historico_escolar he
                            WHERE he.ref_cod_aluno = aluno.cod_aluno
                              AND he.ativo = 1) = '1') AS nome_serie_aux_primeiro_ano,
                    aluno.cod_aluno AS cod_aluno
                    FROM pmieducar.aluno
                    LEFT JOIN pmieducar.matricula ON (matricula.cod_matricula IN ($P!{matriculas_transferido}))
                    LEFT JOIN pmieducar.serie ON (serie.cod_serie = matricula.ref_ref_cod_serie)
                    LEFT JOIN cadastro.pessoa ON (pessoa.idpes = aluno.ref_idpes)
                    LEFT JOIN pmieducar.matricula_turma ON (matricula_turma.ref_cod_matricula = matricula.cod_matricula
                                                   AND matricula_turma.transferido = TRUE)
                    LEFT JOIN relatorio.view_componente_curricular ON (true
                        AND view_componente_curricular.cod_turma = matricula_turma.ref_cod_turma
                        AND view_componente_curricular.cod_serie = serie.cod_serie)
                    WHERE CASE WHEN $P{turma} > 0 THEN aluno.cod_aluno IN ($P!{alunos}) ELSE aluno.cod_aluno = $P{aluno} END
SQL;
    }
}
