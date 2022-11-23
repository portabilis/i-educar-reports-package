<?php

trait SchoolingCertificateTrait
{
    /**
     * @inheritdoc
     */
    protected function query()
    {
        $instituicao = $this->args['instituicao'];
        $ano = $this->args['ano'];
        $matricula = $this->args['matricula'];

        return <<<SQL
                    SELECT
                        matricula.cod_matricula AS matricula,
                        escola.cod_escola,
                        matricula.ano,
                        instituicao.altera_atestado_para_declaracao,
                        upper(fisica.nome_social) AS nome_social,
                        upper(pessoa.nome) AS nome,
                        COALESCE(relatorio.get_pai_aluno(aluno.cod_aluno), '') AS nm_pai,
                        COALESCE(relatorio.get_mae_aluno(aluno.cod_aluno), '') AS nm_mae,
                        to_char(fisica.data_nasc, 'dd/mm/yyyy') AS data_nasc,
                        COALESCE(municipio.nome || ' - ' || sigla_uf, 'Não informado') as municipio_uf_nascimento,
                        nm_serie,
                        upper(curso.sgl_curso) AS sigla_curso,
                        upper(curso.nm_curso) AS nm_curso,
                        upper(instituicao.cidade) AS cidade,
                        public.data_para_extenso(CURRENT_DATE) as data_atual,
                        educacenso_cod_aluno.cod_aluno_inep AS cod_inep,
                        aluno.aluno_estado_id,
                        upper(secretario.nome) AS secretario,
                        upper(diretor.nome) AS diretor,
                        turma_turno.nome AS periodo
                        FROM pmieducar.instituicao
                    INNER JOIN pmieducar.escola ON (escola.ref_cod_instituicao = instituicao.cod_instituicao)
                    INNER JOIN pmieducar.escola_ano_letivo ON (pmieducar.escola_ano_letivo.ref_cod_escola = pmieducar.escola.cod_escola)
                    INNER JOIN pmieducar.escola_curso ON (escola_curso.ativo = 1
                        AND escola_curso.ref_cod_escola = escola.cod_escola)
                    INNER JOIN pmieducar.curso ON (curso.cod_curso = escola_curso.ref_cod_curso
                        AND curso.ativo = 1)
                    INNER JOIN pmieducar.escola_serie ON (escola_serie.ativo = 1
                        AND escola_serie.ref_cod_escola = escola.cod_escola)
                    INNER JOIN pmieducar.serie ON (serie.cod_serie = escola_serie.ref_cod_serie
                        AND serie.ativo = 1)
                    INNER JOIN pmieducar.matricula ON (matricula.ativo = 1
                        AND matricula.ref_ref_cod_serie = serie.cod_serie
                        AND matricula.ref_cod_curso = curso.cod_curso
                        AND matricula.ref_ref_cod_escola = escola.cod_escola)
                    INNER JOIN pmieducar.matricula_turma ON (matricula.cod_matricula = matricula_turma.ref_cod_matricula
                        AND matricula_turma.ativo = 1)
                    INNER JOIN pmieducar.turma ON (turma.cod_turma = matricula_turma.ref_cod_turma
                        AND turma.ativo = 1)
                    INNER JOIN pmieducar.turma_turno ON (turma_turno.id = turma.turma_turno_id)
                    INNER JOIN pmieducar.aluno ON (aluno.cod_aluno = matricula.ref_cod_aluno)
                    INNER JOIN cadastro.pessoa ON (pessoa.idpes = aluno.ref_idpes)
                    INNER JOIN cadastro.fisica ON (fisica.idpes = aluno.ref_idpes)
                    LEFT JOIN public.municipio ON (municipio.idmun = fisica.idmun_nascimento)
                    LEFT JOIN modules.educacenso_cod_aluno ON (educacenso_cod_aluno.cod_aluno = aluno.cod_aluno)
                    LEFT JOIN cadastro.pessoa secretario ON (secretario.idpes = escola.ref_idpes_secretario_escolar)
                    LEFT JOIN cadastro.pessoa diretor ON (diretor.idpes = escola.ref_idpes_gestor)
                        WHERE instituicao.cod_instituicao = {$instituicao}
                        AND escola_ano_letivo.ano = {$ano}
                        AND matricula.cod_matricula = {$matricula}
SQL;
    }
}
