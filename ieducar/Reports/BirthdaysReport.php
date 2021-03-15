<?php

use iEducar\Reports\JsonDataSource;

class BirthdaysReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        return 'birthdays';
    }

    /**
     * @inheritdoc
     */
    public function requiredArgs()
    {
        $this->addRequiredArg('ano');
        $this->addRequiredArg('instituicao');
        $this->addRequiredArg('situacao');
    }

    /**
     * Retorna o SQL para buscar os dados do relatório principal.
     *
     * @return string
     */
    public function getSqlMainReport()
    {
        return "

            SELECT
                matricula_turma.sequencial_fechamento AS sequencial_fechamento,
                aluno.cod_aluno AS cod_aluno,
                aluno.aluno_estado_id AS serie_ciasc,
                educacenso_cod_aluno.cod_aluno_inep AS inep,
                fcn_upper(pessoa.nome) AS nome_aluno,
                (
                    CASE WHEN fisica.sexo = 'M' THEN 'Mas' ELSE 'Fem' END
                ) AS sexo,
                to_char(fisica.data_nasc,'dd/mm/yyyy') AS data_nasc,
                nis_pis_pasep,
                curso.nm_curso AS nome_curso,
                turma.nm_turma AS nome_turma,
                serie.nm_serie AS nome_serie,
                turma.cod_turma AS cod_turma,
                escola.cod_escola AS cod_escola,
                juridica.fantasia AS nm_escola,
                turma_turno.nome AS periodo,
                (
                    SELECT
                        infra_predio.nm_predio
                    FROM
                        pmieducar.infra_predio_comodo,
                        pmieducar.infra_comodo_funcao,
                        pmieducar.infra_predio
                    WHERE TRUE
                        AND infra_comodo_funcao.cod_infra_comodo_funcao = infra_predio_comodo.ref_cod_infra_comodo_funcao
                        AND infra_comodo_funcao.ref_cod_escola = escola.cod_escola
                        AND infra_predio.cod_infra_predio = infra_predio_comodo.ref_cod_infra_predio
                        AND infra_predio.ref_cod_escola = escola.cod_escola
                        AND infra_predio.ativo = 1
                        AND infra_predio_comodo.cod_infra_predio_comodo = turma.ref_cod_infra_predio_comodo
                ) AS predio,
                (
                    SELECT nm_comodo
                    FROM
                        pmieducar.infra_predio_comodo,
                        pmieducar.infra_comodo_funcao,
                        pmieducar.infra_predio
                    WHERE TRUE
                        AND infra_comodo_funcao.cod_infra_comodo_funcao = infra_predio_comodo.ref_cod_infra_comodo_funcao
                        AND infra_comodo_funcao.ref_cod_escola = escola.cod_escola
                        AND infra_predio.cod_infra_predio = infra_predio_comodo.ref_cod_infra_predio
                        AND infra_predio.ref_cod_escola = escola.cod_escola
                        AND infra_predio.ativo = 1
                        AND infra_predio_comodo.cod_infra_predio_comodo = turma.ref_cod_infra_predio_comodo
                ) AS sala,
                view_situacao.texto_situacao AS situacao,
                matricula.dependencia
            FROM
                pmieducar.instituicao
            INNER JOIN pmieducar.escola ON TRUE
                AND escola.ref_cod_instituicao = instituicao.cod_instituicao
            INNER JOIN pmieducar.escola_ano_letivo ON TRUE
                AND pmieducar.escola_ano_letivo.ref_cod_escola = pmieducar.escola.cod_escola
            INNER JOIN pmieducar.escola_curso ON TRUE
                AND escola_curso.ativo = 1
                AND escola_curso.ref_cod_escola = escola.cod_escola
            INNER JOIN pmieducar.curso ON TRUE
                AND curso.cod_curso = escola_curso.ref_cod_curso
                AND curso.ativo = 1
            INNER JOIN pmieducar.escola_serie ON TRUE
                AND escola_serie.ativo = 1
                AND escola_serie.ref_cod_escola = escola.cod_escola
            INNER JOIN pmieducar.serie ON TRUE
                AND serie.cod_serie = escola_serie.ref_cod_serie
                AND serie.ativo = 1
            INNER JOIN pmieducar.turma ON TRUE
                AND turma.ref_ref_cod_escola = escola.cod_escola
                AND turma.ref_cod_curso = escola_curso.ref_cod_curso
                AND turma.ref_ref_cod_serie = escola_serie.ref_cod_serie
                AND turma.ativo = 1
            INNER JOIN pmieducar.matricula_turma ON TRUE
                AND matricula_turma.ref_cod_turma = turma.cod_turma
            INNER JOIN pmieducar.matricula ON TRUE
                AND matricula.cod_matricula = matricula_turma.ref_cod_matricula
                AND matricula.ativo = 1
            INNER JOIN relatorio.view_situacao ON TRUE
                AND view_situacao.cod_matricula = matricula.cod_matricula
                AND view_situacao.cod_turma = turma.cod_turma
                AND view_situacao.cod_situacao = '{$this->args['situacao']}'
                AND matricula_turma.sequencial = view_situacao.sequencial
            LEFT JOIN pmieducar.turma_turno ON TRUE
                AND turma_turno.id = turma.turma_turno_id
                AND turma.cod_turma = matricula_turma.ref_cod_turma
            INNER JOIN pmieducar.aluno ON TRUE
                AND pmieducar.matricula.ref_cod_aluno = pmieducar.aluno.cod_aluno
            INNER JOIN cadastro.fisica ON TRUE
                AND cadastro.fisica.idpes = pmieducar.aluno.ref_idpes
            INNER JOIN cadastro.pessoa ON TRUE
                AND cadastro.pessoa.idpes = cadastro.fisica.idpes
            LEFT JOIN cadastro.juridica ON TRUE
                AND juridica.idpes = escola.ref_idpes
            LEFT JOIN cadastro.documento ON TRUE
                AND documento.idpes = fisica.idpes
            LEFT JOIN modules.educacenso_cod_aluno ON TRUE
                AND educacenso_cod_aluno.cod_aluno = aluno.cod_aluno
            WHERE TRUE
                AND EXTRACT(MONTH FROM fisica.data_nasc::timestamp) = '{$this->args['mes']}'
                AND pmieducar.instituicao.cod_instituicao = '{$this->args['instituicao']}'
                AND pmieducar.escola_ano_letivo.ano = '{$this->args['ano']}'
                AND pmieducar.matricula.ano = pmieducar.escola_ano_letivo.ano
                AND
                (
                    SELECT CASE WHEN '{$this->args['escola']}' = 0 THEN
                        TRUE
                    ELSE
                        escola.cod_escola = '{$this->args['escola']}'
                    END
                )
                AND
                (
                    SELECT CASE WHEN '{$this->args['curso']}' = 0 THEN
                        TRUE
                    ELSE
                        pmieducar.escola_curso.ref_cod_curso = '{$this->args['curso']}'
                    END
                )
                AND
                (
                    SELECT CASE WHEN '{$this->args['serie']}' = 0 THEN
                        TRUE
                    ELSE
                        pmieducar.serie.cod_serie = '{$this->args['serie']}'
                    END
                )
                AND
                (
                    SELECT CASE WHEN '{$this->args['turma']}' = 0 THEN
                        TRUE
                    ELSE
                        pmieducar.turma.cod_turma = '{$this->args['turma']}'
                    END
                )
            ORDER BY
                nm_escola,
                nome_curso,
                nome_serie,
                nome_turma,
                cod_turma,
                sequencial_fechamento,
                nome_aluno

        ";
    }
}
