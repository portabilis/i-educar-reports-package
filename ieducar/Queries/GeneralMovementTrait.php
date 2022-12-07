<?php

trait GeneralMovementTrait
{
    /**
     * @inheritdoc
     */
    protected function query()
    {
        $ano = $this->args['ano'];
        $instituicao = $this->args['instituicao'];
        $cursos = $this->args['curso'];
        $dataInicial = $this->args['data_inicial'];
        $dataFinal = $this->args['data_final'];

        return <<<SQL
            SELECT
                escola,
                ciclo,
                aee,
                localizacao,
                SUM(CASE WHEN ed_infantil AND matricula_ativa AND integral AND entrou_antes_inicio AND saiu_depois_inicio THEN 1 ELSE 0 END) AS ed_inf_int,
                SUM(CASE WHEN ed_infantil AND matricula_ativa AND integral = 'f' AND entrou_antes_inicio AND saiu_depois_inicio THEN 1 ELSE 0 END) AS ed_inf_parc,
                SUM(CASE WHEN ano1 AND matricula_ativa AND entrou_antes_inicio AND saiu_depois_inicio THEN 1 ELSE 0 END) AS ano_1,
                SUM(CASE WHEN ano2 AND matricula_ativa AND entrou_antes_inicio AND saiu_depois_inicio THEN 1 ELSE 0 END) AS ano_2,
                SUM(CASE WHEN ano3 AND matricula_ativa AND entrou_antes_inicio AND saiu_depois_inicio THEN 1 ELSE 0 END) AS ano_3,
                SUM(CASE WHEN ano4 AND matricula_ativa AND entrou_antes_inicio AND saiu_depois_inicio THEN 1 ELSE 0 END) AS ano_4,
                SUM(CASE WHEN ano5 AND matricula_ativa AND entrou_antes_inicio AND saiu_depois_inicio THEN 1 ELSE 0 END) AS ano_5,
                SUM(CASE WHEN ano6 AND matricula_ativa AND entrou_antes_inicio AND saiu_depois_inicio THEN 1 ELSE 0 END) AS ano_6,
                SUM(CASE WHEN ano7 AND matricula_ativa AND entrou_antes_inicio AND saiu_depois_inicio THEN 1 ELSE 0 END) AS ano_7,
                SUM(CASE WHEN ano8 AND matricula_ativa AND entrou_antes_inicio AND saiu_depois_inicio THEN 1 ELSE 0 END) AS ano_8,
                SUM(CASE WHEN ano9 AND matricula_ativa AND entrou_antes_inicio AND saiu_depois_inicio THEN 1 ELSE 0 END) AS ano_9,
                SUM(CASE WHEN matricula_ativa AND entrou_antes_fim AND saiu_depois_fim THEN 1 ELSE 0 END) AS total_series,
                SUM(CASE WHEN matricula_ativa AND primeira_enturmacao AND entrou_durante THEN 1 ELSE 0 END) AS admitidos,
                SUM(CASE WHEN abandono AND saiu_durante THEN 1 ELSE 0 END) AS aband,
                SUM(CASE WHEN transferido AND saiu_durante THEN 1 ELSE 0 END) AS transf,
                SUM(CASE WHEN reclassificado AND saiu_durante THEN 1 ELSE 0 END) AS recla,
                SUM(CASE WHEN falecido AND saiu_durante THEN 1 ELSE 0 END) AS obito,
                SUM(CASE WHEN matricula_ativa AND enturmacao_inativa AND saiu_durante AND sequencial < maior_sequencial THEN 1 ELSE 0 END) AS rem
            FROM (
                SELECT
                    e.cod_escola,
                    (CASE WHEN COALESCE(e.fundamental_ciclo,0) = 0 THEN '' ELSE '**' END) AS ciclo,
                    (CASE WHEN COALESCE(e.atendimento_aee,0) <= 0 THEN '' ELSE '*' END) AS aee,
                    (CASE WHEN e.zona_localizacao = 2 THEN 'Rural' ELSE 'Urbana' END) AS localizacao,
                    j.fantasia AS escola,
                    coluna = 0 AS ed_infantil,
                    coluna = 1 AS ano1,
                    coluna = 2 AS ano2,
                    coluna = 3 AS ano3,
                    coluna = 4 AS ano4,
                    coluna = 5 AS ano5,
                    coluna = 6 AS ano6,
                    coluna = 7 AS ano7,
                    coluna = 8 AS ano8,
                    coluna = 9 AS ano9,
                    tt.id = 4 AS integral,
                    ie.sequential = 1 AS primeira_enturmacao,
                    ie.sequential AS sequencial,
                    ie.last_sequential AS maior_sequencial,
                    ie.registration_active as matricula_ativa,
                    ie.registration_abandoned AND ie.enrollment_abandoned AS abandono,
                    ie.registration_transferred AND ie.enrollment_transferred AS transferido,
                    ie.registration_reclassified AS reclassificado,
                    ie.registration_deceased AND ie.enrollment_deceased AS falecido,
                    ie.enrollment_active = false AS enturmacao_inativa,
                    ie.start_date < date('$dataInicial') AS entrou_antes_inicio,
                    ie.start_date <= date('$dataFinal') AS entrou_antes_fim,
                    ie.start_date between date('$dataInicial') and date('$dataFinal') as entrou_durante,
                    ie.end_date is null or ie.end_date >= date('$dataInicial') AS saiu_depois_inicio,
                    ie.end_date is null or ie.end_date > date('$dataFinal') AS saiu_depois_fim,
                    ie.end_date between date('$dataInicial') and date('$dataFinal') AS saiu_durante
                FROM pmieducar.instituicao i
                INNER JOIN pmieducar.escola e ON (e.ref_cod_instituicao = i.cod_instituicao)
                INNER JOIN pmieducar.escola_ano_letivo eal ON (eal.ref_cod_escola = e.cod_escola)
                INNER JOIN cadastro.juridica j ON (j.idpes = e.ref_idpes)
                INNER JOIN pmieducar.matricula m ON (m.ref_ref_cod_escola = e.cod_escola
                    AND m.ano = eal.ano)
                INNER JOIN pmieducar.matricula_turma mt ON (mt.ref_cod_matricula = m.cod_matricula)
                INNER JOIN pmieducar.turma t ON (t.cod_turma = mt.ref_cod_turma)
                INNER JOIN pmieducar.turma_turno tt ON (tt.id = t.turma_turno_id)
                INNER JOIN pmieducar.aluno a ON (a.cod_aluno = m.ref_cod_aluno)
                INNER JOIN pmieducar.serie s ON (s.cod_serie = m.ref_ref_cod_serie)
                INNER JOIN modules.config_movimento_geral cmg ON (cmg.ref_cod_serie = s.cod_serie)
                INNER JOIN public.info_enrollment ie ON ie.enrollment_id = mt.id
                WHERE true
                    AND i.cod_instituicao = $instituicao
                    AND e.ativo = 1
                    AND m.ano = $ano
                    AND m.dependencia NOT IN (TRUE)
                    AND (CASE WHEN coalesce($cursos) = 0 THEN TRUE ELSE s.ref_cod_curso in ($cursos) END)
                    AND t.ativo = 1
                    AND m.ativo = 1
            ) AS dados_movimentacao
            GROUP BY
                cod_escola,
                escola,
                ciclo,
                aee,
                localizacao
            ORDER BY escola;
SQL;
    }
}
