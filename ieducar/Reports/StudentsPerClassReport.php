<?php

use iEducar\Reports\JsonDataSource;

class StudentsPerClassReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        return 'students-per-class';
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
     * TODO #refatorar
     *
     * @return string
     */
    public function getSqlMainReport()
    {
        return "
            SELECT
                mt.sequencial_fechamento AS sequencial_fechamento,
                coalesce((case when d.rg ='0' then null else 'RG: ' || d.rg end),'RG: Não informado') as rg,
				coalesce((case when f.cpf =0 then null else 'CPF: ' || replace(to_char(f.cpf, '000:000:000-00'), ':', '.') end)::text,'CPF: Não informado') as cpf,
                upper(ma.nome) AS nome_mae,
				upper(pa.nome) AS nome_pai,
                a.cod_aluno AS cod_aluno,
                a.aluno_estado_id AS serie_ciasc,
                eca.cod_aluno_inep AS inep,
                fcn_upper(p.nome) AS nome_aluno,
                fcn_upper(f.nome_social) AS nome_social,
                t.sgl_turma AS prodesp_gdae,
                (CASE WHEN f.sexo = 'M' THEN 'Mas' WHEN f.sexo = 'F' THEN 'Fem' END) AS sexo,
                to_char(f.data_nasc,'dd/mm/yyyy') AS data_nasc,
                nis_pis_pasep,
                c.nm_curso AS nome_curso,
                t.nm_turma AS nome_turma,
                s.nm_serie AS nome_serie,
                s.cod_serie AS cod_serie,
                t.cod_turma AS cod_turma,
                tt.nome AS periodo,
                e.cod_escola AS cod_escola,
                j.fantasia AS nm_escola,
                ct.name AS naturalidade,
                st.abbreviation AS uf_naturalidade,
                vsr.texto_situacao AS situacao,
                m.dependencia,
                CASE m.modalidade_ensino
                    WHEN 0 THEN 'Semipresencial'::varchar
                    WHEN 1 THEN 'EAD'::varchar
                    WHEN 2 THEN 'Off-line'::varchar
                    ELSE 'Presencial'::varchar
                END AS modalidade_ensino,
                m.modalidade_ensino AS cod_modalidade_ensino
            FROM pmieducar.matricula m
            JOIN pmieducar.matricula_turma mt ON mt.ref_cod_matricula = m.cod_matricula
            JOIN relatorio.view_situacao_relatorios vsr ON true
                AND vsr.cod_matricula = m.cod_matricula
                AND vsr.cod_turma = mt.ref_cod_turma
                AND vsr.sequencial = mt.sequencial
                AND vsr.cod_situacao = {$this->args['situacao']}
            JOIN pmieducar.turma t ON true
                AND t.cod_turma = mt.ref_cod_turma
                AND t.ativo = 1
            JOIN pmieducar.turma_turno tt ON tt.id = t.turma_turno_id
            JOIN pmieducar.serie s ON true
                AND s.cod_serie = m.ref_ref_cod_serie
                AND s.ativo = 1
            JOIN pmieducar.curso c ON true
                AND c.cod_curso = m.ref_cod_curso
                AND c.ativo = 1
            JOIN pmieducar.escola e ON true
                AND e.cod_escola = m.ref_ref_cod_escola
                AND e.ativo = 1
            JOIN pmieducar.instituicao i ON true
                AND i.cod_instituicao = e.ref_cod_instituicao
                AND i.ativo = 1
            JOIN pmieducar.aluno a ON true
                AND a.cod_aluno = m.ref_cod_aluno
                AND a.ativo = 1
            LEFT JOIN modules.educacenso_cod_aluno eca ON eca.cod_aluno = a.cod_aluno
            JOIN cadastro.pessoa p ON p.idpes = a.ref_idpes
            JOIN cadastro.fisica f ON f.idpes = a.ref_idpes
			LEFT JOIN cadastro.pessoa ma ON ma.idpes = f.idpes_mae
			LEFT JOIN cadastro.pessoa pa ON pa.idpes = f.idpes_pai
            LEFT JOIN public.cities ct ON ct.id = f.idmun_nascimento::integer
            LEFT JOIN public.states st ON st.id = ct.state_id
            LEFT JOIN cadastro.juridica j ON (j.idpes = e.ref_idpes)
            LEFT JOIN cadastro.pessoa pj ON (pj.idpes = j.idpes)
            LEFT JOIN cadastro.documento d ON (d.idpes = f.idpes)
            WHERE true
                AND i.cod_instituicao = {$this->args['instituicao']}
                AND m.ano = {$this->args['ano']}
                AND CASE WHEN {$this->args['escola']} = 0 THEN TRUE ELSE e.cod_escola = {$this->args['escola']} END
                AND CASE WHEN {$this->args['curso']} = 0 THEN TRUE ELSE c.cod_curso = {$this->args['curso']} END
                AND CASE WHEN {$this->args['serie']} = 0 THEN TRUE ELSE s.cod_serie = {$this->args['serie']} END
                AND CASE WHEN {$this->args['turma']}= 0 THEN TRUE ELSE t.cod_turma = {$this->args['turma']} END
                AND (CASE WHEN '{$this->args['data_inicial']}' = '' THEN true ELSE  m.data_matricula::text >= '{$this->args['data_inicial']}' END)
                AND (CASE WHEN '{$this->args['data_final']}' = '' THEN true ELSE  m.data_matricula::text <= '{$this->args['data_final']}' END)
                AND (
                    CASE
                        WHEN {$this->args['dependencia']} = 1 THEN m.dependencia = TRUE
                        WHEN {$this->args['dependencia']} = 2 THEN m.dependencia = FALSE
                        ELSE true
                    END
                )
            ORDER BY
                j.fantasia,
				c.nm_curso,
				s.nm_serie,
				t.nm_turma,
				t.cod_turma,
				(CASE WHEN m.dependencia THEN 1 ELSE 0 END),
				mt.sequencial_fechamento,
				(COALESCE(f.nome_social, p.nome));
        ";
    }
}
