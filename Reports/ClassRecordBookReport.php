<?php

use iEducar\Reports\JsonDataSource;

require_once 'lib/Portabilis/Report/ReportCore.php';
require_once 'App/Model/IedFinder.php';

class ClassRecordBookReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    public function templateName()
    {
        return 'class-record-book';
    }

    public function requiredArgs()
    {
        $this->addRequiredArg('ano');
        $this->addRequiredArg('instituicao');
        $this->addRequiredArg('escola');
        $this->addRequiredArg('curso');
        $this->addRequiredArg('serie');
    }

    public function getJsonData()
    {
        $queryMainReport = $this->getSqlMainReport();
        $queryHeaderReport = $this->getSqlHeaderReport();
        $datasets = $this->getJsonDataFromDatasets();

        return array_merge([
            'main' => Portabilis_Utils_Database::fetchPreparedQuery($queryMainReport),
            'header' => Portabilis_Utils_Database::fetchPreparedQuery($queryHeaderReport),
        ], $datasets);
    }

    public function getJsonDataFromDatasets()
    {
        $queriesDatasets = $this->getSqlsForDatasets();
        $jsonData = [];
        foreach ($queriesDatasets as $name => $query) {
            $jsonData[$name] = Portabilis_Utils_Database::fetchPreparedQuery($query);
        }
        return $jsonData;
    }

    public function getSqlMainReport()
    {
        return "SELECT 1;";
    }

    public function getSqlsForDatasets()
    {
        $ano = $this->args['ano'] ?: 0;
        $instituicao = $this->args['instituicao'] ?: 0;
        $escola = $this->args['escola'] ?: 0;
        $curso = $this->args['curso'] ?: 0;
        $serie = $this->args['serie'] ?: 0;
        $turma = $this->args['turma'] ?: 0;
        $situacao = $this->args['situacao'] ?: 0;
        $linha = $this->args['linha'] ?: 0;
        $buscar_disciplina = $this->args['buscar_disciplina'] ?: false;
        $ref_cod_componente_curricular = $this->args['ref_cod_componente_curricular'] ?: 0;
        $disciplina = $this->args['disciplina'] ?: '';
        $buscar_professor = $this->args['buscar_professor'] ?: false;
        $servidor_id = $this->args['servidor_id'] ?: 0;
        $professor = $this->args['professor'] ?: '';

        $classRecordCoverSql = "
            SELECT public.fcn_upper(nm_instituicao) AS nome_instituicao,
                public.fcn_upper(nm_responsavel) AS nome_responsavel,
                instituicao.cidade AS cidade_instituicao,
                public.fcn_upper(ref_sigla_uf) AS uf_instituicao,
                escola.cod_escola AS cod_escola,
                escola_ano_letivo.ano,
                curso.nm_curso,
                serie.nm_serie,
                turma.nm_turma,
                to_char(CURRENT_DATE,'dd/mm/yyyy') AS data_atual,
                to_char(current_timestamp, 'HH24:MI:SS') AS hora_atual,
                instituicao.ref_sigla_uf as uf,
                instituicao.cidade,
            
                (SELECT CASE
                    WHEN {$buscar_professor} AND {$servidor_id} <> 0 THEN
                        (SELECT nome FROM cadastro.pessoa WHERE idpes = {$servidor_id} )
                    ELSE '{$professor}'
                END) AS professor,
            
                (SELECT p.nome FROM cadastro.pessoa p WHERE escola.ref_idpes_gestor = p.idpes) as diretor,
                turma_turno.nome AS periodo,
                view_dados_escola.nome AS nm_escola,
                view_dados_escola.logradouro AS logradouro,
                view_dados_escola.bairro AS bairro,
                view_dados_escola.municipio AS municipio,
                view_dados_escola.numero AS numero,
                view_dados_escola.uf_municipio AS uf_municipio,
                view_dados_escola.telefone_ddd AS fone_ddd,
                view_dados_escola.celular_ddd AS cel_ddd,
                view_dados_escola.celular AS cel,
                view_dados_escola.cep AS cep,
                view_dados_escola.telefone AS fone,
                view_dados_escola.email AS email,
                relatorio.get_nome_modulo(turma.cod_turma) as nome_modulo
            FROM pmieducar.instituicao
            INNER JOIN pmieducar.escola ON (escola.ref_cod_instituicao = instituicao.cod_instituicao)
            INNER JOIN relatorio.view_dados_escola ON (view_dados_escola.cod_escola = escola.cod_escola)
            INNER JOIN pmieducar.escola_ano_letivo ON (escola_ano_letivo.ref_cod_escola = escola.cod_escola)
            INNER JOIN pmieducar.escola_curso ON (escola_curso.ref_cod_escola = escola.cod_escola AND escola_curso.ativo = 1)
            INNER JOIN pmieducar.escola_serie ON (escola_serie.ref_cod_escola = escola.cod_escola AND escola_serie.ativo = 1)
            INNER JOIN pmieducar.curso ON (curso.cod_curso = escola_curso.ref_cod_curso AND curso.ativo = 1)
            INNER JOIN pmieducar.serie ON (serie.cod_serie = escola_serie.ref_cod_serie AND serie.ativo = 1)
            INNER JOIN pmieducar.turma ON (turma.ref_ref_cod_escola = escola.cod_escola
                AND turma.ref_cod_curso = curso.cod_curso
                AND turma.ref_ref_cod_serie = serie.cod_serie
                AND turma.ano = escola_ano_letivo.ano
                AND turma.ativo = 1)
            LEFT JOIN pmieducar.turma_turno ON (turma_turno.id = turma.turma_turno_id)
            WHERE instituicao.cod_instituicao = {$instituicao}
            AND escola_ano_letivo.ano = {$ano}
            AND (CASE WHEN {$escola} = 0 THEN TRUE ELSE escola.cod_escola = {$escola} END)
            AND (CASE WHEN {$curso} = 0 THEN TRUE ELSE curso.cod_curso = {$curso} END)
            AND (CASE WHEN {$serie} = 0 THEN TRUE ELSE serie.cod_serie = {$serie} END)
            AND (CASE WHEN {$turma} = 0 THEN TRUE ELSE turma.cod_turma = {$turma} END);
        ";

        $studentList = "
            (SELECT public.fcn_upper(instituicao.nm_instituicao) AS nome_instituicao,
                relatorio.get_nome_escola(escola.cod_escola) AS nm_escola,
                escola_ano_letivo.ano,
                curso.cod_curso,
                curso.nm_curso,
                serie.nm_serie,
                turma.cod_turma,
                turma.nm_turma,
                turma_turno.nome AS periodo,
                aluno.cod_aluno AS cod_aluno,
                public.fcn_upper(pessoa.nome) AS nome_aluno,
                matricula_turma.sequencial_fechamento AS sequencial_fechamento,
                relatorio.get_nome_modulo(turma.cod_turma) AS nome_modulo,

                (SELECT CASE
                    WHEN {$buscar_disciplina} AND {$ref_cod_componente_curricular} <> 0 THEN
                        (SELECT nome FROM modules.componente_curricular WHERE id = {$ref_cod_componente_curricular})
                    ELSE '{$disciplina}'
                END) AS disciplina,

                (SELECT CASE
                    WHEN {$buscar_professor} AND {$servidor_id} <> 0 THEN
                        (SELECT nome FROM cadastro.pessoa WHERE idpes = {$servidor_id})
                    ELSE '{$professor}'
                END) AS professor

            FROM pmieducar.instituicao
            INNER JOIN pmieducar.escola ON escola.ref_cod_instituicao = instituicao.cod_instituicao
            INNER JOIN pmieducar.escola_ano_letivo ON pmieducar.escola_ano_letivo.ref_cod_escola = escola.cod_escola
            INNER JOIN pmieducar.escola_curso ON escola_curso.ref_cod_escola = escola.cod_escola
                AND escola_curso.ativo = 1
            INNER JOIN pmieducar.escola_serie ON escola_serie.ref_cod_escola = escola.cod_escola
                AND escola_serie.ativo = 1
            INNER JOIN pmieducar.serie ON serie.cod_serie = escola_serie.ref_cod_serie
                AND serie.ref_cod_curso = escola_curso.ref_cod_curso
            INNER JOIN pmieducar.turma ON turma.ref_ref_cod_escola = escola.cod_escola
                AND turma.ref_ref_cod_serie = serie.cod_serie
                AND turma.ref_cod_curso = escola_curso.ref_cod_curso
                AND turma.ativo = 1
            LEFT JOIN pmieducar.turma_turno ON turma_turno.id = turma.turma_turno_id
            INNER JOIN pmieducar.curso ON escola_curso.ref_cod_escola = escola.cod_escola
                AND turma.ref_cod_curso = curso.cod_curso
            INNER JOIN pmieducar.matricula_turma ON matricula_turma.ref_cod_turma = turma.cod_turma
            INNER JOIN pmieducar.matricula ON matricula.cod_matricula = matricula_turma.ref_cod_matricula
            INNER JOIN relatorio.view_situacao ON view_situacao.cod_matricula = matricula.cod_matricula
                AND view_situacao.cod_turma = matricula_turma.ref_cod_turma
                AND view_situacao.sequencial = matricula_turma.sequencial
            INNER JOIN pmieducar.aluno ON aluno.cod_aluno = matricula.ref_cod_aluno
            INNER JOIN cadastro.pessoa ON pessoa.idpes = aluno.ref_idpes
            WHERE instituicao.cod_instituicao = {$instituicao}
                AND escola.cod_escola = {$escola}
                AND escola_ano_letivo.ano = {$ano}
                AND turma.ano = escola_ano_letivo.ano
                AND escola_curso.ref_cod_curso = {$curso}
                AND serie.cod_serie = {$serie}
                AND view_situacao.cod_situacao = {$situacao}
                AND (CASE WHEN {$turma} = 0 THEN TRUE ELSE turma.cod_turma = {$turma} END)
            UNION ALL
            
            SELECT null, null, null, null, null, null, {$turma}, null, null, null, null, null, null, null, null
            FROM generate_series(1,{$linha})
            
            ) ORDER BY cod_turma, sequencial_fechamento, nome_aluno;
        ";

        $studentListCrosstabSql = "
            (SELECT public.fcn_upper(instituicao.nm_instituicao) AS nome_instituicao,
                relatorio.get_nome_escola(escola.cod_escola) AS nm_escola,
                escola_ano_letivo.ano,
                curso.cod_curso,
                curso.nm_curso,
                serie.nm_serie,
                turma.nm_turma,
                turma.cod_turma,
                turma.nm_turma,
                turma_turno.nome AS periodo,
                view_componente_curricular.id AS id_disciplina,
                view_componente_curricular.nome AS nome_disciplina,
                aluno.cod_aluno AS cod_aluno,
                public.fcn_upper(pessoa.nome) AS nome_aluno,
                matricula_turma.sequencial_fechamento AS sequencial_fechamento,
                relatorio.get_nome_modulo(turma.cod_turma) AS nome_modulo,
                (SELECT CASE
                    WHEN {$buscar_disciplina} AND {$ref_cod_componente_curricular} <> 0 THEN
                        (SELECT nome FROM modules.componente_curricular WHERE id = {$ref_cod_componente_curricular})
                    ELSE '{$disciplina}'
                END) AS disciplina,
                (SELECT CASE
                    WHEN {$buscar_professor} AND {$servidor_id} <> 0 THEN
                        (SELECT nome
                        FROM cadastro.pessoa
                        WHERE idpes = {$servidor_id})
                    ELSE '{$professor}'
                END) AS professor
            FROM pmieducar.instituicao
            INNER JOIN pmieducar.escola ON escola.ref_cod_instituicao = instituicao.cod_instituicao
            INNER JOIN pmieducar.escola_ano_letivo ON pmieducar.escola_ano_letivo.ref_cod_escola = escola.cod_escola
            INNER JOIN pmieducar.escola_curso ON escola_curso.ref_cod_escola = escola.cod_escola
                AND escola_curso.ativo = 1
            INNER JOIN pmieducar.escola_serie ON escola_serie.ref_cod_escola = escola.cod_escola
                AND escola_serie.ativo = 1
            INNER JOIN pmieducar.serie ON serie.cod_serie = escola_serie.ref_cod_serie
                AND serie.ref_cod_curso = escola_curso.ref_cod_curso
            INNER JOIN pmieducar.turma ON turma.ref_ref_cod_escola = escola.cod_escola
                AND turma.ref_ref_cod_serie = serie.cod_serie
                AND turma.ref_cod_curso = escola_curso.ref_cod_curso
                AND turma.ativo = 1
            LEFT JOIN pmieducar.turma_turno ON turma_turno.id = turma.turma_turno_id
            INNER JOIN pmieducar.curso ON escola_curso.ref_cod_escola = escola.cod_escola
                AND turma.ref_cod_curso = curso.cod_curso
            INNER JOIN pmieducar.matricula_turma ON matricula_turma.ref_cod_turma = turma.cod_turma
            INNER JOIN pmieducar.matricula ON matricula.cod_matricula = matricula_turma.ref_cod_matricula
            INNER JOIN relatorio.view_situacao ON view_situacao.cod_matricula = matricula.cod_matricula
                AND view_situacao.cod_turma = matricula_turma.ref_cod_turma
                AND view_situacao.sequencial = matricula_turma.sequencial
            INNER JOIN relatorio.view_componente_curricular ON view_componente_curricular.cod_turma = turma.cod_turma
            INNER JOIN relatorio.view_modulo ON view_modulo.cod_turma = matricula_turma.ref_cod_turma
            INNER JOIN pmieducar.aluno ON aluno.cod_aluno = matricula.ref_cod_aluno
            INNER JOIN cadastro.pessoa ON pessoa.idpes = aluno.ref_idpes
            WHERE instituicao.cod_instituicao = {$instituicao}
                AND escola.cod_escola = {$escola}
                AND escola_ano_letivo.ano = {$ano}
                AND turma.ano = escola_ano_letivo.ano
                AND escola_curso.ref_cod_curso = {$curso}
                AND serie.cod_serie = {$serie}
                AND view_situacao.cod_situacao = {$situacao}
                AND (CASE WHEN {$turma} = 0 THEN TRUE ELSE turma.cod_turma = {$turma} END)

            UNION ALL

            SELECT
                public.fcn_upper(instituicao.nm_instituicao) AS nome_instituicao,
                relatorio.get_nome_escola(escola.cod_escola) AS nm_escola,
                escola_ano_letivo.ano,
                curso.cod_curso,
                curso.nm_curso,
                serie.nm_serie,
                turma.nm_turma,
                turma.cod_turma,
                turma.nm_turma,
                turma_turno.nome AS periodo,
                view_componente_curricular.id AS id_disciplina,
                view_componente_curricular.nome AS nome_disciplina,
                generate_series as cod_aluno,
                null AS nome_aluno,
                null,
                relatorio.get_nome_modulo(turma.cod_turma) AS nome_modulo,
                null,
                null
            FROM
                generate_series(99999999999999,(99999999999998+{$linha})),
                pmieducar.instituicao
            INNER JOIN pmieducar.escola ON escola.ref_cod_instituicao = instituicao.cod_instituicao
            INNER JOIN pmieducar.escola_ano_letivo ON pmieducar.escola_ano_letivo.ref_cod_escola = escola.cod_escola
            INNER JOIN pmieducar.escola_curso ON escola_curso.ref_cod_escola = escola.cod_escola
                AND escola_curso.ativo = 1
            INNER JOIN pmieducar.escola_serie ON escola_serie.ref_cod_escola = escola.cod_escola
                AND escola_serie.ativo = 1
            INNER JOIN pmieducar.serie ON serie.cod_serie = escola_serie.ref_cod_serie
                AND serie.ref_cod_curso = escola_curso.ref_cod_curso
            INNER JOIN pmieducar.turma ON turma.ref_ref_cod_escola = escola.cod_escola
                AND turma.ref_ref_cod_serie = serie.cod_serie
                AND turma.ref_cod_curso = escola_curso.ref_cod_curso
                AND turma.ativo = 1
            LEFT JOIN pmieducar.turma_turno ON turma_turno.id = turma.turma_turno_id
            INNER JOIN pmieducar.curso ON escola_curso.ref_cod_escola = escola.cod_escola
                AND turma.ref_cod_curso = curso.cod_curso
            INNER JOIN pmieducar.matricula_turma ON matricula_turma.ref_cod_turma = turma.cod_turma
            INNER JOIN pmieducar.matricula ON matricula.cod_matricula = matricula_turma.ref_cod_matricula
            INNER JOIN relatorio.view_situacao ON view_situacao.cod_matricula = matricula.cod_matricula
                AND view_situacao.cod_turma = matricula_turma.ref_cod_turma
                AND view_situacao.sequencial = matricula_turma.sequencial
            INNER JOIN relatorio.view_componente_curricular ON view_componente_curricular.cod_turma = turma.cod_turma
            INNER JOIN relatorio.view_modulo ON view_modulo.cod_turma = matricula_turma.ref_cod_turma
            INNER JOIN pmieducar.aluno ON aluno.cod_aluno = matricula.ref_cod_aluno
            INNER JOIN cadastro.pessoa ON pessoa.idpes = aluno.ref_idpes
            WHERE instituicao.cod_instituicao = {$instituicao}
                AND escola.cod_escola =  {$escola}
                AND escola_ano_letivo.ano =  {$ano}
                AND turma.ano = escola_ano_letivo.ano
                AND escola_curso.ref_cod_curso =  {$curso}
                AND serie.cod_serie =  {$serie}
                AND view_situacao.cod_situacao =  {$situacao}
                AND (CASE WHEN {$turma} = 0 THEN TRUE ELSE turma.cod_turma = {$turma} END))
            ORDER BY cod_turma, id_disciplina , sequencial_fechamento, nome_aluno;
        ";

        return [
            'class_record_cover' => $classRecordCoverSql,
            'student_list' => $studentList,
            'student_list_crosstab' => $studentListCrosstabSql
        ];
    }
}
