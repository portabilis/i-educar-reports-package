<?php

use iEducar\Reports\JsonDataSource;

class StudentDisciplinaryOccurrenceReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        return 'student-disciplinary-occurrence';
    }

    /**
     * @inheritdoc
     */
    public function requiredArgs()
    {
        $this->addRequiredArg('ano');
        $this->addRequiredArg('instituicao');
        $this->addRequiredArg('escola');
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
        $aluno = $this->args['aluno'] ?: 0;
        $instituicao = $this->args['instituicao'] ?: 0;
        $escola = $this->args['escola'] ?: 0;
        $curso = $this->args['curso'] ?: 0;
        $serie = $this->args['serie'] ?: 0;
        $turma = $this->args['turma'] ?: 0;
        $ano = $this->args['ano'] ?: 0;

        return "
        SELECT public.fcn_upper(nm_instituicao) AS nome_instituicao,
       public.fcn_upper(instituicao.nm_responsavel) AS nm_responsavel,
       instituicao.cidade AS cidade_instituicao,

       public.fcn_upper(ref_sigla_uf) AS uf_instituicao,

       to_char(CURRENT_DATE,'dd/mm/yyyy') AS data_atual,

       to_char(current_timestamp, 'HH24:MI:SS') AS hora_atual,

  (SELECT pessoa.nome
          FROM cadastro.pessoa,
               cadastro.juridica
         WHERE escola.ref_idpes = juridica.idpes AND
               juridica.idpes = pessoa.idpes) as nm_escola,

  (SELECT serie.nm_serie
     FROM pmieducar.serie
    WHERE serie.cod_serie = matricula.ref_ref_cod_serie) as nm_serie,

  (SELECT curso.nm_curso
     FROM pmieducar.curso
    WHERE curso.cod_curso = matricula.ref_cod_curso) as nm_curso,

  (SELECT turma.nm_turma
     FROM pmieducar.turma
    WHERE turma.cod_turma = matricula_turma.ref_cod_turma
          LIMIT 1) as nm_turma,

  aluno.cod_aluno as cod_aluno,

  (SELECT translate(pessoa.nome,'åáàãâäéèêëíìîïóòõôöúùüûçÿýñÅÁÀÃÂÄÉÈÊËÍÌÎÏÓÒÕÔÖÚÙÛÜÇÝÑ', 'aaaaaaeeeeiiiiooooouuuucyynAAAAAAEEEEIIIIOOOOOUUUUCYN')
          FROM cadastro.pessoa,
               cadastro.fisica
         WHERE aluno.ref_idpes = fisica.idpes AND
               fisica.idpes = pessoa.idpes) as nm_aluno,
matricula_turma.sequencial_fechamento as seque_fecha,

  (SELECT tod.nm_tipo
     FROM pmieducar.tipo_ocorrencia_disciplinar tod
    WHERE tod.cod_tipo_ocorrencia_disciplinar = mod.ref_cod_tipo_ocorrencia_disciplinar) as tipo_ocorrencia,

       to_char(mod.data_cadastro,'dd/mm/yyyy') AS data_ocorrencia,

       to_char(mod.data_cadastro, 'HH24:MI:SS') AS hora_ocorrencia,

  mod.observacao

  FROM pmieducar.matricula_ocorrencia_disciplinar mod,
       pmieducar.matricula,
       pmieducar.aluno,
       pmieducar.escola,
       pmieducar.instituicao,
       pmieducar.matricula_turma

 WHERE mod.ref_cod_matricula = matricula.cod_matricula and
       matricula_turma.ref_cod_matricula = matricula.cod_matricula and
       matricula.ref_cod_aluno = aluno.cod_aluno AND
       ({$aluno} = aluno.cod_aluno OR {$aluno} = 0) AND
       matricula.ativo = 1 AND
       aluno.ativo = 1 AND
       mod.ativo = 1 AND
       instituicao.cod_instituicao = {$instituicao} AND
       instituicao.cod_instituicao = escola.ref_cod_instituicao AND
       matricula.ref_ref_cod_escola = escola.cod_escola AND
       escola.cod_escola = {$escola} AND
       ({$curso} = matricula.ref_cod_curso OR {$curso} = 0) AND
       ({$serie} = matricula.ref_ref_cod_serie OR {$serie} = 0) AND
       ({$turma} = matricula_turma.ref_cod_turma OR {$turma} = 0) AND
       matricula.ano = {$ano} AND
       matricula_turma.ativo = 1
ORDER BY nm_escola, seque_fecha, nm_aluno, data_ocorrencia
        ";
    }
}
