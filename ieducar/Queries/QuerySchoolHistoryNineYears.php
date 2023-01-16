<?php

class QuerySchoolHistoryNineYears extends QueryBridge
{
    /**
     * @inheritdoc
     */
    protected function query()
    {
        return <<<'SQL'
              SELECT aluno.cod_aluno AS cod_aluno,
                   public.fcn_upper(pessoa.nome) AS nome_aluno,
                   documento.rg AS rg,
                   to_char(fisica.data_nasc,'DD/MM/YYYY') AS data_nasc,
                   aluno.aluno_estado_id AS ra,
                   (SELECT municipio.nome || ' - ' || municipio.sigla_uf
                      FROM public.municipio
                     WHERE municipio.idmun = fisica.idmun_nascimento) AS cidade_nascimento_uf,
                   relatorio.get_nacionalidade(fisica.nacionalidade) AS nacionalidade,
                   documento.num_termo AS num_termo,
                   documento.num_livro AS num_livro,
                   documento.num_folha AS num_folha,
                   documento.certidao_nascimento AS certidao_nascimento,
                   view_historico_9anos.ano_1serie AS ano_1serie,
                   view_historico_9anos.ano_2serie AS ano_2serie,
                   view_historico_9anos.ano_3serie AS ano_3serie,
                   view_historico_9anos.ano_4serie AS ano_4serie,
                   view_historico_9anos.ano_5serie AS ano_5serie,
                   view_historico_9anos.ano_6serie AS ano_6serie,
                   view_historico_9anos.ano_7serie AS ano_7serie,
                   view_historico_9anos.ano_8serie AS ano_8serie,
                   view_historico_9anos.ano_9serie AS ano_9serie,
                   max_anos.max_ano_1serie,
                   max_anos.max_ano_2serie,
                   max_anos.max_ano_3serie,
                   max_anos.max_ano_4serie,
                   max_anos.max_ano_5serie,
                   max_anos.max_ano_6serie,
                   max_anos.max_ano_7serie,
                   max_anos.max_ano_8serie,
                   max_anos.max_ano_9serie,
                   view_historico_9anos.transferido1 AS transferido1,
                   view_historico_9anos.transferido2 AS transferido2,
                   view_historico_9anos.transferido3 AS transferido3,
                   view_historico_9anos.transferido4 AS transferido4,
                   view_historico_9anos.transferido5 AS transferido5,
                   view_historico_9anos.transferido6 AS transferido6,
                   view_historico_9anos.transferido7 AS transferido7,
                   view_historico_9anos.transferido8 AS transferido8,
                   view_historico_9anos.transferido9 AS transferido9,
                   view_historico_9anos.disciplina AS nm_disciplina,
                   view_historico_9anos.nota_1serie AS nota_1serie,
                   view_historico_9anos.nota_2serie AS nota_2serie,
                   view_historico_9anos.nota_3serie AS nota_3serie,
                   view_historico_9anos.nota_4serie AS nota_4serie,
                   view_historico_9anos.nota_5serie AS nota_5serie,
                   view_historico_9anos.nota_6serie AS nota_6serie,
                   view_historico_9anos.nota_7serie AS nota_7serie,
                   view_historico_9anos.nota_8serie AS nota_8serie,
                   view_historico_9anos.nota_9serie AS nota_9serie,
                   coalesce (view_historico_9anos.carga_horaria1::integer, view_historico_9anos.carga_horaria_disciplina1::integer) AS carga_horaria1,
                   coalesce (view_historico_9anos.carga_horaria2::integer, view_historico_9anos.carga_horaria_disciplina2::integer) AS carga_horaria2,
                   coalesce (view_historico_9anos.carga_horaria3::integer, view_historico_9anos.carga_horaria_disciplina3::integer) AS carga_horaria3,
                   coalesce (view_historico_9anos.carga_horaria4::integer, view_historico_9anos.carga_horaria_disciplina4::integer) AS carga_horaria4,
                   coalesce (view_historico_9anos.carga_horaria5::integer, view_historico_9anos.carga_horaria_disciplina5::integer) AS carga_horaria5,
                   coalesce (view_historico_9anos.carga_horaria6::integer, view_historico_9anos.carga_horaria_disciplina6::integer) AS carga_horaria6,
                   coalesce (view_historico_9anos.carga_horaria7::integer, view_historico_9anos.carga_horaria_disciplina7::integer) AS carga_horaria7,
                   coalesce (view_historico_9anos.carga_horaria8::integer, view_historico_9anos.carga_horaria_disciplina8::integer) AS carga_horaria8,
                   coalesce (view_historico_9anos.carga_horaria9::integer, view_historico_9anos.carga_horaria_disciplina9::integer) AS carga_horaria9,
                   view_historico_9anos.observacao_all AS observacao_all,
                   view_historico_9anos.escola_1serie AS escola_1serie,
                   view_historico_9anos.escola_2serie AS escola_2serie,
                   view_historico_9anos.escola_3serie AS escola_3serie,
                   view_historico_9anos.escola_4serie AS escola_4serie,
                   view_historico_9anos.escola_5serie AS escola_5serie,
                   view_historico_9anos.escola_6serie AS escola_6serie,
                   view_historico_9anos.escola_7serie AS escola_7serie,
                   view_historico_9anos.escola_8serie AS escola_8serie,
                   view_historico_9anos.escola_9serie AS escola_9serie,
                   view_historico_9anos.escola_cidade_1serie AS escola_cidade_1serie,
                   view_historico_9anos.escola_cidade_2serie AS escola_cidade_2serie,
                   view_historico_9anos.escola_cidade_3serie AS escola_cidade_3serie,
                   view_historico_9anos.escola_cidade_4serie AS escola_cidade_4serie,
                   view_historico_9anos.escola_cidade_5serie AS escola_cidade_5serie,
                   view_historico_9anos.escola_cidade_6serie AS escola_cidade_6serie,
                   view_historico_9anos.escola_cidade_7serie AS escola_cidade_7serie,
                   view_historico_9anos.escola_cidade_8serie AS escola_cidade_8serie,
                   view_historico_9anos.escola_cidade_9serie AS escola_cidade_9serie,
                   view_historico_9anos.escola_uf_1serie AS escola_uf_1serie,
                   view_historico_9anos.escola_uf_2serie AS escola_uf_2serie,
                   view_historico_9anos.escola_uf_3serie AS escola_uf_3serie,
                   view_historico_9anos.escola_uf_4serie AS escola_uf_4serie,
                   view_historico_9anos.escola_uf_5serie AS escola_uf_5serie,
                   view_historico_9anos.escola_uf_6serie AS escola_uf_6serie,
                   view_historico_9anos.escola_uf_7serie AS escola_uf_7serie,
                   view_historico_9anos.escola_uf_8serie AS escola_uf_8serie,
                   view_historico_9anos.escola_uf_9serie AS escola_uf_9serie,
                   (
                        SELECT m.cod_matricula
                        FROM pmieducar.matricula m
                        JOIN pmieducar.matricula_turma mt ON mt.ref_cod_matricula = m.cod_matricula
                        JOIN pmieducar.turma t ON t.cod_turma = mt.ref_cod_turma
                        WHERE m.ano = (
                            (
                                SELECT max(he.ano) AS max
                                FROM pmieducar.historico_escolar he
                                WHERE he.ref_cod_aluno = aluno.cod_aluno
                                    AND he.ativo = 1
                                    AND he.extra_curricular = 0
                                    AND COALESCE(he.dependencia, false) = false
                                    AND isnumeric("substring"(he.nm_serie::text, 1, 1))
                            )
                        )
                        AND m.ref_cod_aluno = aluno.cod_aluno
                        AND m.ativo = 1
                        AND m.aprovado = 4
                        AND mt.transferido = true
                        AND coalesce(t.tipo_atendimento, 0) != 4 /* Atividade complementar */
                        ORDER BY m.cod_matricula DESC
                        LIMIT 1
                    ) AS matricula_transferido,
                   (SELECT ordenamento
                        FROM modules.componente_curricular
                        WHERE (fcn_upper(relatorio.get_texto_sem_caracter_especial(nome)) = fcn_upper(relatorio.get_texto_sem_caracter_especial(disciplina)))
                        ORDER BY id DESC LIMIT 1) AS ordenamento,
                   (SELECT
                        ano
                        FROM pmieducar.matricula
                        WHERE ref_cod_aluno = aluno.cod_aluno
                        AND ativo = 1
                        ORDER BY cod_matricula DESC
                        LIMIT 1) as matricula_atual_ano,
                   (SELECT
                        aprovado
                        FROM pmieducar.matricula
                        WHERE ref_cod_aluno = aluno.cod_aluno
                        AND ativo = 1
                        ORDER BY cod_matricula DESC
                        LIMIT 1) as situacao_atual,
                   (SELECT
                        ano
                        FROM pmieducar.matricula
                        WHERE ref_cod_aluno = aluno.cod_aluno
                        AND aprovado = 4
                        AND ativo = 1
                        ORDER BY cod_matricula DESC
                        LIMIT 1) as ano_transferido
            FROM relatorio.view_historico_9anos
            INNER JOIN pmieducar.aluno ON (aluno.cod_aluno = view_historico_9anos.cod_aluno)
            INNER JOIN cadastro.pessoa ON (pessoa.idpes = aluno.ref_idpes)
            LEFT JOIN cadastro.documento ON (documento.idpes = pessoa.idpes)
            INNER JOIN cadastro.fisica ON (fisica.idpes = pessoa.idpes)
            JOIN LATERAL(SELECT cod_aluno,
                                MAX(vh9.ano_1serie) AS max_ano_1serie,
                                MAX(vh9.ano_2serie) AS max_ano_2serie,
                                MAX(vh9.ano_3serie) AS max_ano_3serie,
                                MAX(vh9.ano_4serie) AS max_ano_4serie,
                                MAX(vh9.ano_5serie) AS max_ano_5serie,
                                MAX(vh9.ano_6serie) AS max_ano_6serie,
                                MAX(vh9.ano_7serie) AS max_ano_7serie,
                                MAX(vh9.ano_8serie) AS max_ano_8serie,
                                MAX(vh9.ano_9serie) AS max_ano_9serie
                           FROM relatorio.view_historico_9anos vh9
                          GROUP BY cod_aluno) max_anos ON max_anos.cod_aluno = view_historico_9anos.cod_aluno
            AND CASE WHEN $P{turma} > 0 THEN aluno.cod_aluno IN ($P!{alunos}) ELSE aluno.cod_aluno = $P{aluno} END
            ORDER BY nome_aluno, cod_aluno, ordenamento;
SQL;
    }
}
