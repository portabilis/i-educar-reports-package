<?php

class QueryStudentCard extends QueryBridge
{
    /**
     * @inheritdoc
     */
    protected function query()
    {
        return <<<'SQL'
            SELECT
                instituicao.nm_instituicao AS nm_instituicao,
                instituicao.nm_responsavel AS nm_responsavel,
                relatorio.get_nome_escola(escola.cod_escola) AS nm_escola,
                curso.nm_curso AS nome_curso,
                turma.nm_turma AS nome_turma,
                serie.nm_serie AS nome_serie,
                aluno.cod_aluno AS cod_aluno,
                aluno.aluno_estado_id AS aluno_estado_id,
                matricula.ano AS ano_letivo,
                educacenso_cod_aluno.cod_aluno_inep AS inep,
                pessoa.nome AS nome_aluno,
                to_char(fisica.data_nasc,'dd/mm/yyyy') AS data_nasc,
                fone_pessoa.fone AS fone,
                fone_pessoa.ddd AS fone_ddd,
                documento.rg AS rg,
                fisica_foto.caminho AS foto,
                CASE WHEN fisica_foto.caminho IS NULL THEN 0 ELSE 1 END AS existe_foto,
                CASE $P{cor_de_fundo}
                    WHEN 1 THEN 'yellow'
                    WHEN 2 THEN 'blue'
                    WHEN 3 THEN 'orange'
                    WHEN 4 THEN 'purple'
                    WHEN 5 THEN 'green'
                    WHEN 6 THEN 'red'
                    ELSE 'blue'
                END AS cor_fundo
            FROM pmieducar.instituicao
            INNER JOIN pmieducar.escola ON (escola.ref_cod_instituicao = instituicao.cod_instituicao)
            INNER JOIN pmieducar.escola_ano_letivo ON (escola_ano_letivo.ref_cod_escola = escola.cod_escola)
            INNER JOIN pmieducar.escola_curso ON (escola_curso.ref_cod_escola = escola.cod_escola AND escola_curso.ativo = 1)
            INNER JOIN pmieducar.escola_serie ON (escola_serie.ref_cod_escola = escola.cod_escola AND escola_serie.ativo = 1)
            INNER JOIN pmieducar.curso ON (curso.cod_curso = escola_curso.ref_cod_curso AND curso.ativo = 1)
            INNER JOIN pmieducar.serie ON (serie.cod_serie = escola_serie.ref_cod_serie AND serie.ativo = 1)
            INNER JOIN pmieducar.turma ON (turma.ref_ref_cod_escola = escola.cod_escola
                AND turma.ref_cod_curso = curso.cod_curso
                AND turma.ano = escola_ano_letivo.ano
                AND turma.ativo = 1)
            INNER JOIN pmieducar.matricula_turma ON (matricula_turma.ref_cod_turma = turma.cod_turma)
            INNER JOIN pmieducar.matricula ON (matricula.cod_matricula = matricula_turma.ref_cod_matricula
                AND matricula.ref_ref_cod_escola = escola.cod_escola
                AND matricula.ref_cod_curso = curso.cod_curso
                AND matricula.ref_ref_cod_serie = serie.cod_serie
                AND matricula.ano = escola_ano_letivo.ano)

            INNER JOIN relatorio.view_situacao ON (view_situacao.cod_matricula = matricula.cod_matricula
                AND view_situacao.cod_turma = turma.cod_turma
                AND view_situacao.sequencial = matricula_turma.sequencial)
            INNER JOIN pmieducar.aluno ON (matricula.ref_cod_aluno = aluno.cod_aluno)
            INNER JOIN cadastro.pessoa ON (pessoa.idpes = aluno.ref_idpes)
            INNER JOIN cadastro.fisica ON (fisica.idpes = aluno.ref_idpes)
            LEFT JOIN cadastro.fisica_foto ON fisica_foto.idpes = aluno.ref_idpes
            LEFT JOIN cadastro.documento ON (documento.idpes = aluno.ref_idpes)
            LEFT JOIN modules.educacenso_cod_aluno ON (educacenso_cod_aluno.cod_aluno = aluno.cod_aluno)
            LEFT JOIN LATERAL (
                SELECT
                    idpes,
                    fone,
                    ddd
                FROM cadastro.fone_pessoa
                WHERE fone_pessoa.idpes = escola.ref_idpes
                ORDER BY tipo
                LIMIT 1
            ) fone_pessoa ON (true)
            WHERE true
                AND instituicao.cod_instituicao = $P{instituicao}
                AND escola_ano_letivo.ano = $P{ano}
                AND escola.cod_escola = $P{escola}
                AND curso.cod_curso = $P{curso}
                AND serie.cod_serie = $P{serie}
                AND turma.cod_turma = $P{turma}
                AND ($P{matricula} = 0 OR matricula.cod_matricula = $P{matricula})
                AND (CASE WHEN $P{situacao_matricula} = 16 THEN EXISTS (
                    SELECT 1
                    FROM modules.nota_componente_curricular_media nccm
                          JOIN modules.nota_aluno ON (nota_aluno.matricula_id = matricula.cod_matricula)
                    WHERE nccm.nota_aluno_id = nota_aluno.id
                    AND nccm.situacao = 8
                ) AND view_situacao.cod_situacao IN (1, 12, 13)
                ELSE view_situacao.cod_situacao = $P{situacao_matricula}
                END)
            ORDER BY sequencial_fechamento;
SQL;
    }
}
