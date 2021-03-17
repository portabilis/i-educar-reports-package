<?php

use iEducar\Reports\JsonDataSource;

class StudentsEntranceAndAllocationReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        return 'students-entrance-and-allocation';
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
     */
    public function getSqlMainReport()
    {
        $ano = $this->args['ano'] ?: 0;
        $instituicao = $this->args['instituicao'] ?: 0;
        $escola = $this->args['escola'] ?: 0;
        $curso = $this->args['curso'] ?: 0;
        $serie = $this->args['serie'] ?: 0;
        $turma = $this->args['turma'] ?: 0;
        $situacao = $this->args['situacao'] ?: 0;

        $data_inicial = ' AND TRUE ';
        $data_final = ' AND TRUE ';

        if ($this->args['data_inicial']) {
            $data_inicial = " AND matricula.data_matricula >= '{$data_inicial}'::date ";
        }

        if ($this->args['data_final']) {
            $data_inicial = " AND matricula.data_matricula <= '{$data_final}'::date ";
        }

        return "
SELECT matricula.ano,
           (SELECT relatorio.get_nome_escola(matricula.ref_ref_cod_escola)) AS nome_escola,
           curso.nm_curso,
           serie.nm_serie,
           aluno.cod_aluno,
           pessoa.nome AS nome_aluno,
           turma.cod_turma,
           turma.nm_turma,
           fisica.sexo,
           CASE
             WHEN matricula_turma.remanejado THEN 'Rem'::character varying
             WHEN matricula_turma.transferido THEN 'Trs'::character varying
             WHEN matricula_turma.reclassificado THEN 'Recl'::character varying
             WHEN matricula_turma.abandono THEN 'Aba'::character varying
             WHEN matricula.aprovado = 1 THEN 'Apr'::character varying
             WHEN matricula.aprovado = 12 THEN 'Ap. Depen.'::character varying
             WHEN matricula.aprovado = 13 THEN 'Ap. Cons.'::character varying
             WHEN matricula.aprovado = 2 THEN 'Rep'::character varying
             WHEN matricula.aprovado = 3 THEN 'Cur'::character varying
             WHEN matricula.aprovado = 4 THEN 'Trs'::character varying
             WHEN matricula.aprovado = 5 THEN 'Recl'::character varying
             WHEN matricula.aprovado = 6 THEN 'Aba'::character varying
             WHEN matricula.aprovado = 14 THEN 'Rp. Faltas'::character varying
             WHEN matricula.aprovado = 15 THEN 'Fal'::character varying
             ELSE 'Recl'::character varying
           END AS situacao,
           to_char(fisica.data_nasc, 'dd/mm/yyyy') AS data_nascimento,
           to_char(coalesce(matricula.data_matricula, matricula.data_cadastro), 'dd/mm/yyyy') AS data_matricula,
           to_char(matricula_turma.data_enturmacao, 'dd/mm/yyyy') AS data_enturmacao,
           COALESCE(to_char(matricula.data_cancel, 'dd/mm/yyyy'), to_char(matricula.data_exclusao, 'dd/mm/yyyy')) AS dt_saida_matricula,
           to_char(matricula_turma.data_exclusao, 'dd/mm/yyyy') AS dt_saida_turma
      FROM relatorio.situacao_matricula, pmieducar.instituicao
INNER JOIN pmieducar.escola ON (instituicao.cod_instituicao = escola.ref_cod_instituicao)
INNER JOIN pmieducar.matricula ON (escola.cod_escola = matricula.ref_ref_cod_escola)
INNER JOIN pmieducar.aluno ON (matricula.ref_cod_aluno = aluno.cod_aluno)
INNER JOIN cadastro.pessoa ON (aluno.ref_idpes = pessoa.idpes)
INNER JOIN cadastro.fisica ON (pessoa.idpes = fisica.idpes)
INNER JOIN pmieducar.matricula_turma ON (matricula.cod_matricula = matricula_turma.ref_cod_matricula)
INNER JOIN pmieducar.curso ON (matricula.ref_cod_curso = curso.cod_curso)
INNER JOIN pmieducar.serie ON (matricula.ref_ref_cod_serie = serie.cod_serie)
INNER JOIN pmieducar.turma on (matricula_turma.ref_cod_turma = turma.cod_turma)
     WHERE matricula.ativo = 1
             AND aluno.ativo = 1
       AND matricula.ano = {$ano}
       AND instituicao.cod_instituicao = {$instituicao}
       AND (CASE WHEN {$escola} = 0 THEN TRUE ELSE matricula.ref_ref_cod_escola = {$escola} END)
       AND (CASE WHEN {$curso} = 0 THEN TRUE ELSE matricula.ref_cod_curso = {$curso} END)
       AND (CASE WHEN {$serie} = 0 THEN TRUE ELSE matricula.ref_ref_cod_serie = {$serie} END)
       AND (CASE WHEN {$turma} = 0 THEN TRUE ELSE matricula_turma.ref_cod_turma = {$turma} END)
       {$data_inicial}
       {$data_final}
       AND situacao_matricula.cod_situacao = {$situacao}
AND CASE
	   WHEN matricula.aprovado = 4 THEN matricula_turma.ativo = 1 OR matricula_turma.transferido OR matricula_turma.reclassificado OR matricula_turma.remanejado OR matricula_turma.sequencial = ((
	     SELECT max(mt.sequencial) AS max
  	       FROM pmieducar.matricula_turma mt
	      WHERE mt.ref_cod_matricula = matricula.cod_matricula))
	   WHEN matricula.aprovado = 6 THEN matricula_turma.ativo = 1 OR matricula_turma.abandono
	   WHEN matricula.aprovado = 15 THEN matricula_turma.ativo = 1 OR matricula_turma.falecido
	   WHEN matricula.aprovado = 5 THEN matricula_turma.ativo = 1 OR matricula_turma.reclassificado
	   ELSE matricula_turma.ativo = 1 OR matricula_turma.transferido OR matricula_turma.reclassificado OR matricula_turma.abandono OR matricula_turma.remanejado OR matricula_turma.falecido AND matricula_turma.sequencial < ((
   	     SELECT max(mt.sequencial) AS max
  	       FROM pmieducar.matricula_turma mt
	      WHERE mt.ref_cod_matricula = matricula.cod_matricula))
           END
      AND CASE
	    WHEN situacao_matricula.cod_situacao = 10 THEN matricula.aprovado = any (array[1, 2, 3, 4, 5, 6, 12, 13, 14, 15])
	    WHEN situacao_matricula.cod_situacao = 9 THEN (matricula.aprovado = any (array[1, 2, 3, 5, 12, 13, 14])) AND (not matricula_turma.reclassificado OR matricula_turma.reclassificado is null) AND (not matricula_turma.abandono OR matricula_turma.abandono is null) AND (not matricula_turma.remanejado OR matricula_turma.remanejado is null) AND (not matricula_turma.transferido OR matricula_turma.transferido is null) AND (not matricula_turma.falecido OR matricula_turma.falecido is null)
	    WHEN situacao_matricula.cod_situacao = 2 THEN (matricula.aprovado = any (array[2, 14])) AND (not matricula_turma.reclassificado OR matricula_turma.reclassificado is null) AND (not matricula_turma.abandono OR matricula_turma.abandono is null) AND (not matricula_turma.remanejado OR matricula_turma.remanejado is null) AND (not matricula_turma.transferido OR matricula_turma.transferido is null) AND (not matricula_turma.falecido OR matricula_turma.falecido is null)
	    WHEN situacao_matricula.cod_situacao = 1 THEN (matricula.aprovado = any (array[1, 12, 13])) AND (not matricula_turma.reclassificado OR matricula_turma.reclassificado is null) AND (not matricula_turma.abandono OR matricula_turma.abandono is null) AND (not matricula_turma.remanejado OR matricula_turma.remanejado is null) AND (not matricula_turma.transferido OR matricula_turma.transferido is null) AND (not matricula_turma.falecido OR matricula_turma.falecido is null)
	    WHEN situacao_matricula.cod_situacao = any (array[3, 12, 13]) THEN matricula.aprovado = situacao_matricula.cod_situacao AND (not matricula_turma.reclassificado OR matricula_turma.reclassificado is null) AND (not matricula_turma.abandono OR matricula_turma.abandono is null) AND (not matricula_turma.remanejado OR matricula_turma.remanejado is null) AND (not matricula_turma.transferido OR matricula_turma.transferido is null) AND (not matricula_turma.falecido OR matricula_turma.falecido is null)
	    ELSE matricula.aprovado = situacao_matricula.cod_situacao
          END
ORDER BY nome_escola, nm_curso, nm_serie, nome_aluno
        ";
    }
}
