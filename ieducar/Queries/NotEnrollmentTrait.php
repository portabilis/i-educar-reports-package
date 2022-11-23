<?php

trait NotEnrollmentTrait
{
    /**
     * @inheritdoc
     */
    protected function query()
    {
        $instituicao = $this->args['instituicao'];
        $escola = $this->args['escola'];
        $curso = $this->args['curso'];
        $serie = $this->args['serie'];
        $ano = $this->args['ano'];

        return <<<SQL
                    SELECT
                        aluno.cod_aluno AS cod_aluno,
                        educacenso_cod_aluno.cod_aluno_inep AS cod_aluno_inep,
                        relatorio.get_texto_sem_caracter_especial(pessoa.nome) AS nome_aluno,
                        relatorio.get_texto_sem_caracter_especial(fisica.nome_social) AS nome_social,
                        to_char(fisica.data_nasc,'dd/mm/yyyy') AS data_nasc,
                        CASE fisica.sexo
                            WHEN 'M' THEN 'Masculino'
                            WHEN 'F' THEN 'Feminino'
                            ELSE ''
                        END AS sexo,
                        matricula.cod_matricula AS cod_matricula,
                        relatorio.get_nome_escola(escola.cod_escola) AS nm_escola,
                        curso.nm_curso AS curso,
                        serie.nm_serie AS serie,
                        to_char(CURRENT_DATE,'dd/mm/yyyy') AS data_atual,
                        to_char(current_timestamp, 'HH24:MI:SS') AS hora_atual
                    FROM pmieducar.instituicao
                    INNER JOIN pmieducar.escola ON (escola.ref_cod_instituicao = instituicao.cod_instituicao)
                    INNER JOIN pmieducar.escola_ano_letivo ON (escola_ano_letivo.ref_cod_escola = escola.cod_escola)
                    INNER JOIN pmieducar.escola_curso ON (escola_curso.ref_cod_escola = escola.cod_escola AND escola_curso.ativo = 1)
                    INNER JOIN pmieducar.escola_serie ON (escola_serie.ref_cod_escola = escola.cod_escola AND escola_serie.ativo = 1)
                    INNER JOIN pmieducar.curso ON (curso.cod_curso = escola_curso.ref_cod_curso AND curso.ativo = 1)
                    INNER JOIN pmieducar.serie ON (serie.cod_serie = escola_serie.ref_cod_serie AND serie.ativo = 1)
                    INNER JOIN pmieducar.matricula ON (matricula.ref_ref_cod_escola = escola.cod_escola
                        AND matricula.ref_cod_curso = curso.cod_curso
                        AND matricula.ref_ref_cod_serie = serie.cod_serie
                        AND matricula.ano = escola_ano_letivo.ano)
                    INNER JOIN pmieducar.aluno ON (aluno.cod_aluno = matricula.ref_cod_aluno)
                    INNER JOIN cadastro.pessoa ON (pessoa.idpes = aluno.ref_idpes)
                    INNER JOIN cadastro.fisica ON (fisica.idpes = aluno.ref_idpes)
                    LEFT JOIN modules.educacenso_cod_aluno ON (educacenso_cod_aluno.cod_aluno = aluno.cod_aluno)
                    WHERE true
                        AND instituicao.cod_instituicao = {$instituicao}
                        AND ({$escola} = 0 OR {$escola} = escola.cod_escola)
                        AND ({$curso} = 0 OR {$curso} = curso.cod_curso)
                        AND ({$serie} = 0 OR {$serie} = serie.cod_serie)
                        AND matricula.aprovado = 3
                        AND matricula.ativo = 1
                        AND aluno.ativo = 1
                        AND matricula.ano = {$ano}
                        AND NOT EXISTS (
                            SELECT 1 FROM pmieducar.matricula_turma
                            WHERE matricula.cod_matricula = matricula_turma.ref_cod_matricula
                                AND matricula_turma.ativo = 1
                        )
                    ORDER BY nm_escola, coalesce(fisica.nome_social, pessoa.nome)
SQL;
    }
}
