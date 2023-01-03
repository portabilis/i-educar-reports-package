<?php

class QueryDefaultSchoolHistory extends QueryBridge
{
    /**
     * @inheritdoc
     */
    protected function query()
    {
        return <<<'SQL'
            SELECT
                relatorio.get_nome_escola(vde.cod_escola) AS nm_escola,
                vde.municipio,
                e.ato_autorizativo,
                he.ref_cod_aluno AS cod_aluno,
                p.nome as nome_aluno,
                a.aluno_estado_id AS cod_ra,
                d.rg AS cod_rg,
                CASE WHEN tipo_cert_civil = 91
                    THEN 'Termo: ' || coalesce(d.num_termo::text, '') ||
                    ' Livro: ' || coalesce(d.num_livro, '') ||
                    ' Folha: ' || coalesce(d.num_folha::text, '')
                    ELSE d.certidao_nascimento
                END AS registro_nascimento,
                f.nome_social as nome_social_aluno,
                eca.cod_aluno_inep AS cod_inep,
                c.name || '/' || s.abbreviation AS cidade_nascimento_uf,
                s.abbreviation AS uf_nascimento,
                c.name AS cidade_nascimento,
                f.data_nasc AS data_nasc,
                relatorio.get_pai_aluno(he.ref_cod_aluno) AS nome_do_pai,
                relatorio.get_mae_aluno(he.ref_cod_aluno) AS nome_da_mae,
                he.sequencial,
                he.ano,
                he.carga_horaria,
                he.dias_letivos,
                he.escola,
                he.escola_cidade,
                he.escola_uf,
                he.aprovado,
                (SELECT historico_escolar.aprovado
                        FROM pmieducar.historico_escolar
                        JOIN pmieducar.historico_grade_curso hgc ON hgc.id = historico_escolar.historico_grade_curso_id AND hgc.id = 3
                        WHERE historico_escolar.ativo = 1
                            AND coalesce(historico_escolar.dependencia, false) = false
                            AND historico_escolar.extra_curricular = 0
                            AND historico_escolar.ref_cod_aluno = he.ref_cod_aluno
                            AND historico_escolar.aprovado IN (1, 2, 3, 14)
                        ORDER BY ano desc,(CASE WHEN historico_escolar.aprovado = 3 THEN 1 WHEN historico_escolar.aprovado IN (2, 14) THEN 2 WHEN historico_escolar.aprovado = 1 THEN 3 END), substring(nm_curso, 1, 1) desc
                        LIMIT 1) as aprovado_eja,
                he.faltas_globalizadas,
                he.nm_serie,
                he.frequencia,
                he.registro,
                he.livro,
                he.folha,
                he.historico_grade_curso_id,
                COALESCE(
                    (SELECT nm_curso
                        FROM pmieducar.historico_escolar
                        JOIN pmieducar.historico_grade_curso hgc ON hgc.id = historico_escolar.historico_grade_curso_id AND hgc.id = 3
                        WHERE historico_escolar.ativo = 1
                            AND coalesce(historico_escolar.dependencia, false) = false
                            AND historico_escolar.extra_curricular = 0
                            AND historico_escolar.ref_cod_aluno = he.ref_cod_aluno
                            AND historico_escolar.aprovado IN (1, 2, 3, 14)
                        ORDER BY ano desc,(CASE WHEN historico_escolar.aprovado = 3 THEN 1 WHEN historico_escolar.aprovado IN (2, 14) THEN 2 WHEN historico_escolar.aprovado = 1 THEN 3 END),
                        ano desc, substring(nm_curso, 1, 1) desc
                        LIMIT 1),
                    (SELECT nm_curso
                        FROM pmieducar.historico_escolar
                        WHERE true
                            AND historico_escolar.ativo = 1
                            AND coalesce(historico_escolar.dependencia, false) = false
                            AND historico_escolar.extra_curricular = 0
                            AND historico_escolar.ref_cod_aluno = he.ref_cod_aluno
                            AND isnumeric(substring(historico_escolar.nm_serie::text, 1, 1))
                            AND he.extra_curricular = 0
                        ORDER BY historico_escolar.nm_serie DESC
                        LIMIT 1)
                    ) AS nome_curso,
                he.dependencia,
                upper(unaccent(hd.nm_disciplina)) AS nm_disciplina,
                hd.nota,
                hd.faltas,
                hd.carga_horaria_disciplina,
                hd.dependencia AS disciplina_dependencia,
                he.observacao,
                (
                    SELECT max(coalesce(ato_poder_publico,''))
                    FROM pmieducar.curso
                ) AS ato_poder_publico,
                to_char(CURRENT_DATE,'dd/mm/yyyy') as data_atual,
                public.data_para_extenso(CURRENT_DATE) AS data_atual_extenso
            FROM pmieducar.historico_escolar he
            JOIN pmieducar.historico_disciplinas hd ON true
                AND hd.ref_ref_cod_aluno = he.ref_cod_aluno
                AND hd.ref_sequencial = he.sequencial
            JOIN pmieducar.escola e ON e.cod_escola = $P{escola}
            JOIN relatorio.view_dados_escola vde ON vde.cod_escola = $P{escola}
            JOIN pmieducar.aluno a ON a.cod_aluno = he.ref_cod_aluno
            JOIN cadastro.pessoa p ON p.idpes = a.ref_idpes
            JOIN cadastro.fisica f ON f.idpes = a.ref_idpes
            LEFT JOIN cadastro.documento d ON (d.idpes = a.ref_idpes)
            LEFT JOIN public.cities c ON c.id = f.idmun_nascimento
            LEFT JOIN public.states s ON s.id = c.state_id
            LEFT JOIN modules.educacenso_cod_aluno eca ON (eca.cod_aluno = a.cod_aluno)
            WHERE true
            AND CASE WHEN $P{turma} > 0 THEN he.ref_cod_aluno IN ($P!{alunos}) ELSE he.ref_cod_aluno = $P{aluno} END
            AND CASE WHEN $P{grade_curso_eja} = 3 THEN he.historico_grade_curso_id = 3 ELSE he.historico_grade_curso_id <> 3 END
            AND he.ativo = 1
            AND coalesce(he.dependencia, false) = false
            AND he.extra_curricular = 0
            ORDER BY
                nome_aluno,
                he.nm_serie,
                he.ano,
                relatorio.prioridade_historico(he.aprovado) DESC,
                hd.ordenamento,
                hd.nm_disciplina;
SQL;
    }
}
