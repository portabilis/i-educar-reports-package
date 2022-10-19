<?php

trait MonthlyMovementTrait
{
    /**
     * @inheritdoc
     */
    protected function query()
    {
        $ano = $this->args['ano'];
        $instituicao = $this->args['instituicao'];
        $escola = $this->args['escola'];
        $curso = $this->args['curso'];
        $serie = $this->args['serie'];
        $turma = $this->args['turma'];
        $data_inicial = $this->args['data_inicial'];
        $data_final = $this->args['data_final'];
        $modalidade = $this->args['modalidade'];
        $tipos_atendimento = $this->args['tipos_atendimento'];
        $modalidade_eja = $this->args['modalidade_eja'];
        $filtrar_datas_calendario = $this->args['filtrar_datas_calendario'];
        $data_inicial_calendario = $this->args['data_inicial_calendario'];
        $data_final_calendario = $this->args['data_final_calendario'];

        return "
            select
                cod_escola,
                relatorio.get_nome_escola(cod_escola) as nm_escola,
                cod_serie,
                nm_serie,
                cod_turma,
                nm_turma,
                turno,
                sum(case when masculino and matricula_ativa and sem_dependencia and entrou_antes_inicio and saiu_depois_inicio then 1 else 0 end) as mat_ini_m,
                sum(case when feminino and matricula_ativa and sem_dependencia and entrou_antes_inicio and saiu_depois_inicio then 1 else 0 end) as mat_ini_f,
                sum(case when matricula_ativa and sem_dependencia and entrou_antes_inicio and saiu_depois_inicio then 1 else 0 end) as mat_ini,
                sum(case when masculino and transferido and saiu_durante then 1 else 0 end) as mat_transf_m,
                sum(case when feminino and transferido and saiu_durante then 1 else 0 end) as mat_transf_f,
                sum(case when masculino and abandono and enturmacao_abandono and saiu_durante then 1 else 0 end) as mat_aband_m,
                sum(case when feminino and abandono and enturmacao_abandono and saiu_durante then 1 else 0 end) as mat_aband_f,
                sum(case when masculino and matricula_ativa and sequencial = 1 and entrada_reclassificado = false and entrou_durante then 1 else 0 end) as mat_admit_m,
                sum(case when feminino and matricula_ativa and sequencial = 1 and entrada_reclassificado = false and entrou_durante then 1 else 0 end) as mat_admit_f,
                sum(case when masculino and falecido and saiu_durante then 1 else 0 end) as mat_falecido_m,
                sum(case when feminino and falecido and saiu_durante then 1 else 0 end) as mat_falecido_f,
                sum(case when masculino and reclassificado and saiu_durante then 1 else 0 end) as mat_reclassificados_m,
                sum(case when feminino and reclassificado and saiu_durante then 1 else 0 end) as mat_reclassificados_f,
                sum(case when masculino and matricula_ativa and entrada_reclassificado and entrou_durante then 1 else 0 end) as mat_reclassificadose_m,
                sum(case when feminino and matricula_ativa and entrada_reclassificado and entrou_durante then 1 else 0 end) as mat_reclassificadose_f,
                sum(case when masculino and matricula_ativa and entrou_durante and sequencial > 1 then 1 else 0 end) as mat_trocae_m,
                sum(case when feminino and matricula_ativa and entrou_durante and sequencial > 1 then 1 else 0 end) as mat_trocae_f,
                sum(case when masculino and matricula_ativa and enturmacao_inativa and saiu_durante and sequencial < maior_sequencial then 1 else 0 end) as mat_trocas_m,
                sum(case when feminino and matricula_ativa and enturmacao_inativa and saiu_durante and sequencial < maior_sequencial then 1 else 0 end) as mat_trocas_f,
                sum(case when masculino and matricula_ativa and sem_dependencia and entrou_antes_fim and saiu_depois_fim then 1 else 0 end) as mat_fim_m,
                sum(case when feminino and matricula_ativa and sem_dependencia and entrou_antes_fim and saiu_depois_fim then 1 else 0 end) as mat_fim_f,
                sum(case when matricula_ativa and sem_dependencia and entrou_antes_fim and saiu_depois_fim then 1 else 0 end) as mat_fim
            from (
                select
                    ie.school_id as cod_escola,
                    ie.grade_id  as cod_serie,
                    serie.nm_serie,
                    ie.classroom_id as cod_turma,
                    turma.nm_turma,
                    turno.nome as turno,
                    ie.sequential as sequencial,
                    sexo = 'F' as feminino,
                    sexo = 'M' as masculino,
                    ie.registration_active as matricula_ativa,
                    ie.registration_transferred transferido,
                    ie.registration_reclassified as reclassificado,
                    ie.registration_abandoned as abandono,
                    ie.registration_deceased as falecido,
                    ie.registration_was_reclassified as entrada_reclassificado,
                    ie.enrollment_active = false as enturmacao_inativa,
                    ie.enrollment_transferred as enturmacao_transferida,
                    ie.enrollment_abandoned as enturmacao_abandono,
                    ie.dependence = false as sem_dependencia,
                    ie.start_date < date('{$data_inicial}') as entrou_antes_inicio,
                    ie.start_date <= date('{$data_final}') as entrou_antes_fim,
                    ie.start_date between date('{$data_inicial}') and date('{$data_final}') as entrou_durante,
                    ie.end_date is null or ie.end_date >= date('{$data_inicial}') as saiu_depois_inicio,
                    ie.end_date is null or ie.end_date > date('{$data_final}') as saiu_depois_fim,
                    ie.end_date between date('{$data_inicial}') and date('{$data_final}') as saiu_durante,
                    ie.last_sequential as maior_sequencial
                from public.info_enrollment ie
                inner join pmieducar.matricula_turma enturmacao on true
                    and enturmacao.id = ie.enrollment_id
                inner join pmieducar.matricula matricula on true
                    and matricula.cod_matricula = ie.registration_id
                inner join pmieducar.turma turma on true
                    and turma.cod_turma = ie.classroom_id
                inner join pmieducar.serie serie on true
                    and serie.cod_serie = ie.grade_id
                inner join pmieducar.turma_turno turno on true
                    and turno.id = turma.turma_turno_id
                inner join pmieducar.aluno aluno on true
                    and aluno.cod_aluno = matricula.ref_cod_aluno
                inner join cadastro.fisica pessoa on true
                    and pessoa.idpes = aluno.ref_idpes
                inner join pmieducar.escola escola on true
                    and escola.cod_escola = matricula.ref_ref_cod_escola
                inner join pmieducar.curso on true
                    and curso.cod_curso = turma.ref_cod_curso
                where true
		            and escola.ref_cod_instituicao = {$instituicao}
                    and (
                        case when {$escola} = 0 then
                            true
                        else
                            matricula.ref_ref_cod_escola = {$escola}
                        end
                    )
                    and matricula.ano = {$ano}
                    and matricula.ativo = 1
		            and turma.ativo = 1
                    and
                    (
                        case when {$curso} = 0 then
                            true
                        else
                            serie.ref_cod_curso = {$curso}
                        end
                    )
                    and
                    (
                        case when {$turma} = 0 then
                            true
                        else
                            turma.cod_turma = {$turma}
                        end
                    )
                    and
                    (
                        case when {$serie} = 0 then
                            true
                        else
                            serie.cod_serie = {$serie}
                        end
                    )
                    and case when '{$modalidade}' != '' AND {$modalidade_eja} = 0 then curso.modalidade_curso != 3 else true end
                    and (
                        case
                            when '{$modalidade}' = '' then true
                            else (
                                (
                                    coalesce(turma.tipo_atendimento, 0) IN ({$tipos_atendimento})
                                )
                                or (
                                    curso.modalidade_curso IN ({$modalidade_eja})
                                    and case
                                        when {$filtrar_datas_calendario} then
                                            (SELECT min(data_inicio) FROM pmieducar.turma_modulo WHERE turma_modulo.ref_cod_turma = turma.cod_turma LIMIT 1)::VARCHAR IN ('{$data_inicial_calendario}')
                                            AND (SELECT max(data_fim) FROM pmieducar.turma_modulo WHERE turma_modulo.ref_cod_turma = turma.cod_turma LIMIT 1)::VARCHAR IN ('{$data_final_calendario}')
                                        else true
                                    end
                                )
                            )
                        end
                    )
            ) as matriculas
            group by
                cod_escola,
                cod_serie,
                nm_serie,
                cod_turma,
                nm_turma,
                turno
            order by
                nm_escola,
                nm_turma;
        ";
    }
}
