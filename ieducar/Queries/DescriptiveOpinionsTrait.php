<?php

trait DescriptiveOpinionsTrait
{
    /**
     * @inheritdoc
     */
    protected function query()
    {
        $matricula = $this->args['matricula'];
        $serie = $this->args['serie'];
        $turma = $this->args['turma'];
        $instituicao = $this->args['instituicao'];
        $escola = $this->args['escola'];
        $ano = $this->args['ano'];
        $curso = $this->args['curso'];
        $situacao_matricula = $this->args['situacao_matricula'];
        $etapa = $this->args['etapa'];
        $alunos_diferenciados = $this->args['alunos_diferenciados'];

        return <<<SQL
            SELECT escola_ano_letivo.ano AS ano,
                   public.fcn_upper(instituicao.nm_responsavel) AS nome_responsavel,
                   relatorio.get_nome_escola(escola.cod_escola) AS nm_escola,
                   public.fcn_upper(instituicao.nm_instituicao) AS nome_instituicao,
                   view_dados_escola.logradouro AS logradouro,
                   view_dados_escola.telefone_ddd AS fone_ddd,
                   view_dados_escola.telefone AS fone,
                   view_dados_escola.email AS email,
                   to_char(CURRENT_DATE,'dd/mm/yyyy') AS data_atual,
                   to_char(CURRENT_TIMESTAMP, 'HH24:MI:SS') AS hora_atual,
                   matricula.cod_matricula AS matricula,
                   componente_curricular.nome AS nome_disciplina,
                   curso.nm_curso AS nome_curso,
                   serie.nm_serie AS nome_serie,
                   turma.nm_turma AS nome_turma,
                   turma_turno.nome AS periodo,
                   view_situacao.texto_situacao AS situacao,
                   COALESCE(NULLIF(fisica.nome_social, ''), pessoa.nome) AS nome_aluno,
                   (CASE WHEN NULLIF(fisica.nome_social, '') IS NOT NULL THEN pessoa.nome ELSE NULL END) AS nome_aluno_registro,
                   fisica.data_nasc AS dt_nasc,
                  regexp_replace(parecer_componente_curricular.parecer, '(( ){2,}|\t+)', ' ', 'g') AS parecer,
                   view_modulo.nome AS modulo_etapa,
                   modules.frequencia_da_matricula(matricula.cod_matricula)  AS frequencia,
                   matricula_turma.sequencial || 'º' AS seq_etapa,
                   curso.hora_falta * 100 AS curso_hora_falta,
                   COALESCE(componente_curricular_turma.carga_horaria, componente_curricular_ano_escolar.carga_horaria)::int AS carga_horaria_componente,
                   serie.carga_horaria AS carga_horaria_serie,

                 (SELECT sum(falta_geral.quantidade)
                  FROM modules.falta_geral,
                       modules.falta_aluno
                  WHERE falta_geral.falta_aluno_id = falta_aluno.id
                    AND falta_aluno.matricula_id = matricula.cod_matricula
                    AND falta_geral.etapa = '$etapa'
                    AND falta_aluno.tipo_falta = 1) AS total_faltas_et1,

                 (SELECT sum(falta_componente_curricular.quantidade)
                  FROM modules.falta_componente_curricular,
                       modules.falta_aluno
                  WHERE falta_componente_curricular.falta_aluno_id = falta_aluno.id
                    AND falta_aluno.matricula_id = matricula.cod_matricula
                    AND falta_componente_curricular.etapa = '$etapa'
                    AND falta_componente_curricular.componente_curricular_id = componente_curricular.id
                    AND falta_aluno.tipo_falta = 2) AS faltas_componente_et1,

                 (SELECT sum(falta_componente_curricular.quantidade)
                  FROM modules.falta_componente_curricular,
                       modules.falta_aluno
                  WHERE falta_componente_curricular.falta_aluno_id = falta_aluno.id
                    AND falta_aluno.matricula_id = matricula.cod_matricula
                    AND falta_componente_curricular.etapa = '$etapa'
                    AND falta_aluno.tipo_falta = 2) AS total_geral_faltas_componente,

               (SELECT sum(falta_componente_curricular.quantidade)
                  FROM modules.falta_componente_curricular,
                       modules.falta_aluno
                  WHERE falta_componente_curricular.falta_aluno_id = falta_aluno.id
                    AND falta_aluno.matricula_id = matricula.cod_matricula
                    AND falta_componente_curricular.componente_curricular_id = componente_curricular.id
                    AND falta_componente_curricular.etapa = '$etapa'
                    AND falta_aluno.tipo_falta = 2) AS total_faltas_componente,

               (SELECT replace(textcat_all(tabela_arredondamento_valor.nome || ' - ' || tabela_arredondamento_valor.descricao), '<br>', '/')
                  FROM modules.tabela_arredondamento_valor
            INNER JOIN pmieducar.serie ON serie.cod_serie = matricula.ref_ref_cod_serie
            JOIN modules.regra_avaliacao_serie_ano rasa on(serie.cod_serie = rasa.serie_id AND matricula.ano = rasa.ano_letivo)
            JOIN modules.regra_avaliacao on(rasa.regra_avaliacao_id = regra_avaliacao.id AND tabela_arredondamento_valor.tabela_arredondamento_id = regra_avaliacao.tabela_arredondamento_id)
            ) AS rotulo_descricao

              FROM pmieducar.instituicao
             INNER JOIN pmieducar.escola ON (escola.ref_cod_instituicao = instituicao.cod_instituicao)
             INNER JOIN pmieducar.escola_ano_letivo ON (escola_ano_letivo.ref_cod_escola = escola.cod_escola)
             INNER JOIN relatorio.view_dados_escola ON (view_dados_escola.cod_escola = escola.cod_escola)
             INNER JOIN pmieducar.escola_curso ON (pmieducar.escola_curso.ref_cod_escola = pmieducar.escola.cod_escola
                                                     AND pmieducar.escola_curso.ativo = 1)
             INNER JOIN pmieducar.curso ON (escola_curso.ref_cod_curso = curso.cod_curso)
             INNER JOIN pmieducar.escola_serie ON (escola_serie.ref_cod_escola = escola.cod_escola
                                                     AND escola_serie.ativo = 1)
             INNER JOIN pmieducar.serie ON (serie.cod_serie = escola_serie.ref_cod_serie
                                              AND serie.ref_cod_curso = escola_curso.ref_cod_curso)
             INNER JOIN pmieducar.turma ON (turma.ref_ref_cod_escola = escola.cod_escola
                                             AND turma.ref_ref_cod_serie = serie.cod_serie
                                             AND turma.ref_cod_curso = escola_curso.ref_cod_curso)
             INNER JOIN pmieducar.matricula_turma ON (matricula_turma.ref_cod_turma = turma.cod_turma)
             INNER JOIN pmieducar.matricula ON (matricula.ref_ref_cod_escola = escola.cod_escola
                                                AND matricula.cod_matricula = matricula_turma.ref_cod_matricula
                                AND matricula.ano = escola_ano_letivo.ano)
             INNER JOIN pmieducar.turma_turno ON (turma_turno.id = turma.turma_turno_id)
             INNER JOIN relatorio.view_situacao ON (view_situacao.cod_matricula = matricula.cod_matricula
                                AND view_situacao.cod_turma = turma.cod_turma
                                        AND view_situacao.cod_situacao = $situacao_matricula
                                        AND matricula_turma.sequencial = view_situacao.sequencial)
              LEFT JOIN modules.parecer_aluno ON (parecer_aluno.matricula_id = matricula.cod_matricula)
              LEFT JOIN modules.parecer_componente_curricular ON (parecer_componente_curricular.parecer_aluno_id = parecer_aluno.id)
              LEFT JOIN modules.componente_curricular ON (componente_curricular.id = parecer_componente_curricular.componente_curricular_id)
              LEFT JOIN modules.componente_curricular_ano_escolar ON (componente_curricular_ano_escolar.componente_curricular_id = componente_curricular.id
                                        AND componente_curricular_ano_escolar.ano_escolar_id = matricula.ref_ref_cod_serie
                          AND matricula.ano = any(componente_curricular_ano_escolar.anos_letivos)
                          )
             INNER JOIN pmieducar.aluno ON (aluno.cod_aluno = matricula.ref_cod_aluno)
             INNER JOIN cadastro.pessoa ON (pessoa.idpes = aluno.ref_idpes)
             INNER JOIN cadastro.fisica ON (fisica.idpes = aluno.ref_idpes)
             LEFT JOIN relatorio.view_modulo ON (view_modulo.cod_turma = turma.cod_turma
                                 AND view_modulo.sequencial::varchar = parecer_componente_curricular.etapa)
              LEFT JOIN modules.componente_curricular_turma ON (componente_curricular_turma.componente_curricular_id = componente_curricular_ano_escolar.componente_curricular_id
                                                                AND componente_curricular_turma.turma_id = turma.cod_turma)
            WHERE instituicao.cod_instituicao = $instituicao
               AND escola.cod_escola = $escola
               AND escola_ano_letivo.ano = $ano
               AND curso.cod_curso = $curso
               AND serie.cod_serie = $serie
               AND turma.cod_turma = $turma
               AND parecer_componente_curricular.etapa = '$etapa'
               AND (CASE WHEN $matricula = 0 THEN TRUE ELSE matricula.cod_matricula = $matricula END)
               AND relatorio.exibe_aluno_conforme_parametro_alunos_diferenciados(cod_aluno,  $alunos_diferenciados)
               AND matricula_turma.sequencial = (
                    SELECT max(sequencial)
                    FROM pmieducar.matricula_turma mt
                    WHERE mt.ref_cod_turma = matricula_turma.ref_cod_turma
                    AND mt.ref_cod_matricula = matricula.cod_matricula
                )
             ORDER BY sequencial_fechamento, relatorio.get_texto_sem_caracter_especial(pessoa.nome), ordenamento, nome_disciplina
SQL;
    }
}
