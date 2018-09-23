<?php

use iEducar\Reports\JsonDataSource;

require_once 'lib/Portabilis/Report/ReportCore.php';
require_once 'App/Model/IedFinder.php';

class SchoolTransportationUsersReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        return 'school-transportation-users';
    }

    /**
     * @inheritdoc
     */
    public function requiredArgs()
    {
        $this->addRequiredArg('ano');
        $this->addRequiredArg('instituicao');
    }

    /**
     * Retorna o SQL para buscar os dados do relatório principal.
     *
     * @return string
     *
     * @throws Exception
     */
    public function getSqlMainReport()
    {
        $ano = $this->args['ano'] ?: date('Y');
        $instituicao = $this->args['instituicao'] ?: 0;
        $escola = $this->args['escola'] ?: 0;
        $curso = $this->args['curso'] ?: 0;
        $serie = $this->args['serie'] ?: 0;
        $turma = $this->args['turma'] ?: 0;
        $periodo = $this->args['periodo'] ?: 0;
        $zona_localizacao = $this->args['zona_localizacao'] ?: 0;

        return "
SELECT public.fcn_upper(nm_instituicao) AS nome_instituicao,
       public.fcn_upper(nm_responsavel) AS nome_responsavel,
       transporte_aluno.responsavel,
       educacenso_cod_aluno.cod_aluno_inep AS codigo_inep,
       fcn_upper(pessoa.nome) AS nome_aluno,
       to_char(fisica.data_nasc, 'dd/MM/yyyy') AS data_nasc,
       (CASE
            WHEN fisica.sexo = 'M' THEN 'Masculino'
            ELSE 'Feminino'
        END) AS sexo,
       fcn_upper(bairro.nome) AS bairro,
       relatorio.get_nome_escola(escola.cod_escola) AS nm_escola,
       COALESCE(p.nome, 'Não especificada') AS nome_empresa,
       fcn_upper(descricao) AS descricao,
       tercerizado,
       fcn_upper(coalesce(nome_destino.nome, nome_destino_2.nome)) AS nome_destino,
       turma_turno.nome AS periodo
FROM pmieducar.instituicao
INNER JOIN pmieducar.escola ON (instituicao.cod_instituicao = escola.ref_cod_instituicao)
INNER JOIN pmieducar.matricula ON (escola.cod_escola = matricula.ref_ref_cod_escola)
INNER JOIN pmieducar.curso ON (matricula.ref_cod_curso = curso.cod_curso)
INNER JOIN pmieducar.serie ON (matricula.ref_ref_cod_serie = serie.cod_serie)
INNER JOIN pmieducar.matricula_turma ON (matricula.cod_matricula = matricula_turma.ref_cod_matricula)
INNER JOIN pmieducar.turma ON (matricula_turma.ref_cod_turma = turma.cod_turma)
LEFT JOIN pmieducar.turma_turno ON (turma_turno.id = turma.turma_turno_id)
INNER JOIN pmieducar.aluno ON (matricula.ref_cod_aluno = aluno.cod_aluno)
INNER JOIN cadastro.pessoa ON (aluno.ref_idpes = pessoa.idpes)
LEFT JOIN modules.educacenso_cod_aluno ON (educacenso_cod_aluno.cod_aluno = aluno.cod_aluno)
INNER JOIN cadastro.fisica ON (pessoa.idpes = fisica.idpes)
LEFT JOIN cadastro.endereco_pessoa ON (pessoa.idpes = endereco_pessoa.idpes)
LEFT JOIN public.bairro ON (endereco_pessoa.idbai = bairro.idbai)
INNER JOIN modules.transporte_aluno ON (aluno.cod_aluno = transporte_aluno.aluno_id)
LEFT JOIN modules.pessoa_transporte
  ON (pessoa.idpes = pessoa_transporte.ref_idpes
    AND EXISTS (SELECT 1 FROM modules.rota_transporte_escolar where rota_transporte_escolar.cod_rota_transporte_escolar = pessoa_transporte.ref_cod_rota_transporte_escolar AND rota_transporte_escolar.ano = {$ano})
    )
LEFT JOIN modules.rota_transporte_escolar ON (rota_transporte_escolar.cod_rota_transporte_escolar = pessoa_transporte.ref_cod_rota_transporte_escolar AND rota_transporte_escolar.ano = {$ano})
LEFT JOIN modules.empresa_transporte_escolar ON (empresa_transporte_escolar.cod_empresa_transporte_escolar = rota_transporte_escolar.ref_cod_empresa_transporte_escolar)
LEFT JOIN cadastro.pessoa AS p ON (p.idpes = empresa_transporte_escolar.ref_idpes)
LEFT JOIN cadastro.pessoa AS nome_destino ON (pessoa_transporte.ref_idpes_destino = nome_destino.idpes)
LEFT JOIN cadastro.pessoa AS nome_destino_2 ON (rota_transporte_escolar.ref_idpes_destino = nome_destino_2.idpes)
WHERE matricula.ativo = 1
  AND matricula_turma.ativo = 1
  AND matricula.ultima_matricula > 0 --Tras apenas os diferentes de 0, pois na tabela 'transporte_aluno' o campo 'responsavel'
 --armazena a informação de utilização de transporte ou não.
 --Se for 0, não utiliza, se for 2 utiliza municipal e se for 1 utiliza estadual

  AND transporte_aluno.responsavel <> 0
  AND matricula.ano = {$ano}
  AND instituicao.cod_instituicao = {$instituicao}
  AND (CASE WHEN {$escola} = 0 THEN TRUE ELSE escola.cod_escola = {$escola} END)
  AND (CASE WHEN {$curso} = 0 THEN TRUE ELSE curso.cod_curso = {$curso} END)
  AND (CASE WHEN {$serie} = 0 THEN TRUE ELSE serie.cod_serie = {$serie} END)
  AND (CASE WHEN {$turma} = 0 THEN TRUE ELSE turma.cod_turma = {$turma} END)
  AND (CASE WHEN {$periodo} = 0 THEN TRUE ELSE turma.turma_turno_id = {$periodo} END)
  AND (CASE WHEN {$zona_localizacao} = 0 THEN TRUE ELSE coalesce(
                                                                    (SELECT bairro.zona_localizacao
                                                                     FROM public.bairro
                                                                     INNER JOIN cadastro.endereco_pessoa ON (endereco_pessoa.idbai = bairro.idbai)
                                                                     WHERE endereco_pessoa.idpes = pessoa.idpes),
                                                                    (SELECT endereco_externo.zona_localizacao
                                                                     FROM cadastro.endereco_externo
                                                                     WHERE endereco_externo.idpes = pessoa.idpes)) = {$zona_localizacao} END)
ORDER BY nome_empresa,
         nome_aluno ASC
        ";
    }
}
