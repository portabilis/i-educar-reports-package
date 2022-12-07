<?php

class QueryClassBoardMap extends QueryBridge
{
    /**
     * @inheritdoc
     */
    protected function query()
    {
        return <<<'SQL'
                    SELECT matricula.cod_matricula AS matricula,
                        aluno.cod_aluno AS cod_aluno,
                        relatorio.get_texto_sem_caracter_especial(pessoa.nome) AS nm_aluno,
                        view_situacao.texto_situacao AS situacao,
                        view_situacao.texto_situacao_simplificado AS situacao_simplificado,
                        trim(to_char(modules.frequencia_da_matricula(matricula.cod_matricula),'99999999999D99')) AS frequencia_geral,
                        CASE
                           WHEN isnumeric(nota_componente_curricular_etapa1.nota_arredondada) = false THEN nota_componente_curricular_etapa1.nota_arredondada
                           ELSE replace(TRUNC(nota_componente_curricular_etapa1.nota_arredondada::numeric, qtd_casas_decimais)::text,'.',',')
                        END AS nota1,
                        CASE
                           WHEN isnumeric(nota_componente_curricular_etapa2.nota_arredondada) = false THEN nota_componente_curricular_etapa2.nota_arredondada
                           ELSE replace(TRUNC(nota_componente_curricular_etapa2.nota_arredondada::numeric, qtd_casas_decimais)::text,'.',',')
                        END AS nota2,
                        CASE
                           WHEN isnumeric(nota_componente_curricular_etapa3.nota_arredondada) = false THEN nota_componente_curricular_etapa3.nota_arredondada
                           ELSE replace(TRUNC(nota_componente_curricular_etapa3.nota_arredondada::numeric, qtd_casas_decimais)::text,'.',',')
                        END AS nota3,
                        CASE
                           WHEN isnumeric(nota_componente_curricular_etapa4.nota_arredondada) = false THEN nota_componente_curricular_etapa4.nota_arredondada
                           ELSE replace(TRUNC(nota_componente_curricular_etapa4.nota_arredondada::numeric, qtd_casas_decimais)::text,'.',',')
                        END AS nota4,
                        (SELECT COALESCE(
                                           (SELECT SUM(quantidade)
                                            FROM modules.falta_componente_curricular, modules.falta_aluno
                                            WHERE falta_componente_curricular.falta_aluno_id = falta_aluno.id
                                              AND falta_aluno.matricula_id = matricula.cod_matricula
                                              AND CASE WHEN $P{etapa} = 0 THEN TRUE ELSE falta_componente_curricular.etapa = ($P{etapa})::varchar END
                                              AND falta_aluno.tipo_falta = 2),
                                           (SELECT SUM(quantidade)
                                            FROM modules.falta_geral, modules.falta_aluno
                                            WHERE falta_geral.falta_aluno_id = falta_aluno.id
                                              AND falta_aluno.matricula_id = matricula.cod_matricula
                                              AND CASE WHEN $P{etapa} = 0 THEN TRUE ELSE falta_geral.etapa = ($P{etapa})::varchar END
                                              AND falta_aluno.tipo_falta = 1),
                                           0)) AS total_faltas,
                        (SELECT COALESCE(
                                         (SELECT SUM(quantidade)
                                          FROM modules.falta_componente_curricular, modules.falta_aluno
                                          WHERE falta_componente_curricular.falta_aluno_id = falta_aluno.id
                                            AND falta_componente_curricular.componente_curricular_id = view_componente_curricular.id
                                            AND falta_aluno.matricula_id = matricula.cod_matricula
                                            AND falta_componente_curricular.etapa = '1'
                                            AND falta_aluno.tipo_falta = 2),
                                         (SELECT SUM(quantidade)
                                          FROM modules.falta_geral, modules.falta_aluno
                                          WHERE falta_geral.falta_aluno_id = falta_aluno.id
                                            AND falta_aluno.matricula_id = matricula.cod_matricula
                                            AND falta_geral.etapa = '1'
                                            AND falta_aluno.tipo_falta = 1))) AS falta1,
                        (SELECT COALESCE(
                                         (SELECT SUM(quantidade)
                                          FROM modules.falta_componente_curricular, modules.falta_aluno
                                          WHERE falta_componente_curricular.falta_aluno_id = falta_aluno.id
                                            AND falta_componente_curricular.componente_curricular_id = view_componente_curricular.id
                                            AND falta_aluno.matricula_id = matricula.cod_matricula
                                            AND falta_componente_curricular.etapa = '2'
                                            AND falta_aluno.tipo_falta = 2),
                                         (SELECT SUM(quantidade)
                                          FROM modules.falta_geral, modules.falta_aluno
                                          WHERE falta_geral.falta_aluno_id = falta_aluno.id
                                            AND falta_aluno.matricula_id = matricula.cod_matricula
                                            AND falta_geral.etapa = '2'
                                            AND falta_aluno.tipo_falta = 1))) AS falta2,

                        (SELECT COALESCE(
                                         (SELECT SUM(quantidade)
                                          FROM modules.falta_componente_curricular, modules.falta_aluno
                                          WHERE falta_componente_curricular.falta_aluno_id = falta_aluno.id
                                            AND falta_componente_curricular.componente_curricular_id = view_componente_curricular.id
                                            AND falta_aluno.matricula_id = matricula.cod_matricula
                                            AND falta_componente_curricular.etapa = '3'
                                            AND falta_aluno.tipo_falta = 2),
                                         (SELECT SUM(quantidade)
                                          FROM modules.falta_geral, modules.falta_aluno
                                          WHERE falta_geral.falta_aluno_id = falta_aluno.id
                                            AND falta_aluno.matricula_id = matricula.cod_matricula
                                            AND falta_geral.etapa = '3'
                                            AND falta_aluno.tipo_falta = 1))) AS falta3,

                            (SELECT COALESCE(
                                             (SELECT SUM(quantidade)
                                              FROM modules.falta_componente_curricular, modules.falta_aluno
                                              WHERE falta_componente_curricular.falta_aluno_id = falta_aluno.id
                                                AND falta_componente_curricular.componente_curricular_id = view_componente_curricular.id
                                                AND falta_aluno.matricula_id = matricula.cod_matricula
                                                AND falta_componente_curricular.etapa = '4'
                                                AND falta_aluno.tipo_falta = 2),
                                             (SELECT SUM(quantidade)
                                              FROM modules.falta_geral, modules.falta_aluno
                                              WHERE falta_geral.falta_aluno_id = falta_aluno.id
                                                AND falta_aluno.matricula_id = matricula.cod_matricula
                                                AND falta_geral.etapa = '4'
                                                AND falta_aluno.tipo_falta = 1))) AS falta4,
                            view_componente_curricular.id AS id_disciplina,
                            view_componente_curricular.ordenamento AS componente_order,
                            view_componente_curricular.abreviatura AS nm_componente_curricular,
                            matricula.ano AS ano,
                            curso.nm_curso AS nome_curso,
                            serie.nm_serie AS nome_serie,
                            turma.nm_turma AS nome_turma,
                            relatorio.get_qtde_modulo(turma.cod_turma) AS qtde_etapa,
                            relatorio.get_nome_modulo(turma.cod_turma) AS nome_etapa,
                            turma_turno.nome AS periodo,
                            matricula_turma.sequencial_fechamento,
                            matricula_turma.sequencial
                 FROM pmieducar.instituicao
                INNER JOIN pmieducar.escola ON (escola.ref_cod_instituicao = instituicao.cod_instituicao)
                INNER JOIN pmieducar.escola_curso ON (escola_curso.ref_cod_escola = escola.cod_escola)
                INNER JOIN pmieducar.curso ON (curso.cod_curso = escola_curso.ref_cod_curso)
                INNER JOIN pmieducar.escola_serie ON (escola_serie.ref_cod_escola = escola.cod_escola)
                INNER JOIN pmieducar.serie ON (serie.cod_serie = escola_serie.ref_cod_serie)
                INNER JOIN pmieducar.turma ON (turma.ref_ref_cod_escola = escola.cod_escola AND turma.ativo = 1)
                INNER JOIN pmieducar.turma_turno ON (turma_turno.id = turma.turma_turno_id)
                INNER JOIN pmieducar.matricula_turma ON (matricula_turma.ref_cod_turma = turma.cod_turma)
                INNER JOIN pmieducar.matricula ON (matricula.cod_matricula = matricula_turma.ref_cod_matricula
                                                       AND matricula.ref_cod_curso = curso.cod_curso
                                                       AND matricula.ref_ref_cod_serie = serie.cod_serie)
                INNER JOIN relatorio.view_situacao ON (view_situacao.cod_matricula = matricula.cod_matricula
                                                       AND view_situacao.cod_turma = matricula_turma.ref_cod_turma
                                                       AND view_situacao.sequencial = matricula_turma.sequencial
                                                       AND view_situacao.cod_situacao = $P{situacao})
                INNER JOIN pmieducar.aluno ON (aluno.cod_aluno = matricula.ref_cod_aluno)
                INNER JOIN cadastro.pessoa ON (pessoa.idpes = aluno.ref_idpes)
                INNER JOIN relatorio.view_componente_curricular ON (view_componente_curricular.cod_turma = turma.cod_turma
                  AND view_componente_curricular.cod_serie = serie.cod_serie)
                INNER JOIN pmieducar.escola_serie_disciplina ON (escola_serie_disciplina.ref_cod_disciplina = view_componente_curricular.id
                                                                  AND escola_serie_disciplina.ref_ref_cod_serie = serie.cod_serie
                                                                  AND escola_serie_disciplina.ref_ref_cod_escola = escola.cod_escola
                                                                  AND turma.ano = ANY(escola_serie_disciplina.anos_letivos)
                                                                  AND escola_serie_disciplina.ativo = 1)

                INNER JOIN modules.regra_avaliacao_serie_ano rasa ON (rasa.serie_id = serie.cod_serie and rasa.ano_letivo = turma.ano)
                INNER JOIN modules.regra_avaliacao ON (regra_avaliacao.id = rasa.regra_avaliacao_id)
                LEFT JOIN modules.nota_aluno ON (nota_aluno.matricula_id = matricula.cod_matricula)
                LEFT JOIN modules.nota_componente_curricular AS nota_componente_curricular_etapa1 ON (nota_componente_curricular_etapa1.nota_aluno_id = nota_aluno.id
                                                                                                      AND nota_componente_curricular_etapa1.componente_curricular_id = view_componente_curricular.id
                                                                                                      AND nota_componente_curricular_etapa1.etapa = '1')
                LEFT JOIN modules.nota_componente_curricular AS nota_componente_curricular_etapa2 ON (nota_componente_curricular_etapa2.nota_aluno_id = nota_aluno.id
                                                                                                      AND nota_componente_curricular_etapa2.componente_curricular_id = view_componente_curricular.id
                                                                                                      AND nota_componente_curricular_etapa2.etapa = '2')
                LEFT JOIN modules.nota_componente_curricular AS nota_componente_curricular_etapa3 ON (nota_componente_curricular_etapa3.nota_aluno_id = nota_aluno.id
                                                                                                      AND nota_componente_curricular_etapa3.componente_curricular_id = view_componente_curricular.id
                                                                                                      AND nota_componente_curricular_etapa3.etapa = '3')
                LEFT JOIN modules.nota_componente_curricular AS nota_componente_curricular_etapa4 ON (nota_componente_curricular_etapa4.nota_aluno_id = nota_aluno.id
                                                                                                      AND nota_componente_curricular_etapa4.componente_curricular_id = view_componente_curricular.id
                                                                                                      AND nota_componente_curricular_etapa4.etapa = '4')
                WHERE instituicao.cod_instituicao = $P{instituicao}
                  AND matricula.ano = $P{ano}
                  AND escola.cod_escola = $P{escola}
                  AND curso.cod_curso = $P{curso}
                  AND serie.cod_serie = $P{serie}
                  AND turma.cod_turma = $P{turma}
                  AND (CASE WHEN escola_serie_disciplina.etapas_especificas = 1 AND $P{etapa} <> '0' THEN
                              $P{etapa}::varchar = ANY (string_to_array(escola_serie_disciplina.etapas_utilizadas,',')::varchar[]) ELSE TRUE END)
                  AND (CASE WHEN $P{dependencia} = 1 THEN matricula.dependencia = TRUE WHEN $P{dependencia} = 2 THEN matricula.dependencia = FALSE ELSE TRUE END)
                ORDER BY sequencial_fechamento,
                         nm_aluno,
                         cod_aluno,
                         matricula,
                         componente_order,
                         nm_componente_curricular
SQL;
    }
}
