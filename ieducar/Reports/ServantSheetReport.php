<?php

use iEducar\Reports\JsonDataSource;

class ServantSheetReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        return $this->args['branco'] == 1 ? 'servant-sheet-blank' : 'servant-sheet';
    }

    /**
     * @inheritdoc
     */
    public function requiredArgs()
    {
        $this->addRequiredArg('instituicao');
        $this->addRequiredArg('escola');
        $this->addRequiredArg('ano');
    }

    /**
     * @inheritdoc
     */
    public function getJsonData()
    {
        $queryMainReport = $this->getSqlMainReport();
        $queryHeaderReport = $this->getSqlHeaderReport();

        $servants = Portabilis_Utils_Database::fetchPreparedQuery($queryMainReport);

        $instituition = $this->args['instituicao'] ?: 0;
        $ids = [];

        foreach ($servants as $servant) {
            $ids[] = $servant['cod_servidor'];
        }

        return [
            'main' => $servants,
            'header' => Portabilis_Utils_Database::fetchPreparedQuery($queryHeaderReport),
            'professional_data' => $this->getServantDataProfessional($ids, $this->args['ano'], $instituition),
            'formations' => $this->getServantFormations($ids, $instituition),
        ];
    }

    /**
     * Retorna o SQL para buscar os dados do relatório principal.
     *
     * @return string
     */
    public function getSqlMainReport()
    {
        return $this->args['branco'] == 1
            ? $this->getSqlBlankReport()
            : $this->getSqlReport();
    }

    /**
     * Retorna o SQL para o relatório emitido em branco.
     *
     * @return string
     */
    private function getSqlBlankReport()
    {
        $instituicao = $this->args['instituicao'] ?: 0;
        $escola = $this->args['escola'] ?: 0;

        return "

            SELECT
                pmieducar.instituicao.nm_instituicao as \"nome_instituicao\",
                cadastro.juridica.fantasia as \"nm_escola\",
                to_char(CURRENT_DATE,'dd/mm/yyyy') AS data_atual,
                to_char(current_timestamp, 'HH24:MI:SS') AS hora_atual
            FROM
                pmieducar.instituicao
            INNER JOIN pmieducar.escola ON TRUE
                AND escola.ref_cod_instituicao = instituicao.cod_instituicao
            INNER JOIN cadastro.juridica ON TRUE
                AND juridica.idpes = escola.ref_idpes
            WHERE TRUE
                AND instituicao.cod_instituicao = {$instituicao}
                AND cod_escola = {$escola}
            GROUP BY
                nome_instituicao, nm_escola
            ORDER BY
                nm_escola

        ";
    }

    /**
     * Retorna SQL para o relatório emitido com dados.
     *
     * @return string
     */
    private function getSqlReport()
    {
        $ano = $this->args[''] ?: 0;
        $instituicao = $this->args['ano'] ?: 0;
        $escola = $this->args['instituicao'] ?: 0;
        $servidor = $this->args['servidor'] ?: 0;

        return "

            SELECT DISTINCT
                servidor.cod_servidor,
                translate(upper(municipio_nasceu.nome),'áéíóúýàèìòùãõâêîôûäëïöüç','ÁÉÍÓÚÝÀÈÌÒÙÃÕÂÊÎÔÛÄËÏÖÜÇ') AS cidade_nasceu,
                translate(upper(municipio_nasceu.sigla_uf),'áéíóúýàèìòùãõâêîôûäëïöüç','ÁÉÍÓÚÝÀÈÌÒÙÃÕÂÊÎÔÛÄËÏÖÜÇ') AS estado_nasceu,
                translate(upper(bairro.nome),'áéíóúýàèìòùãõâêîôûäëïöüç','ÁÉÍÓÚÝÀÈÌÒÙÃÕÂÊÎÔÛÄËÏÖÜÇ') AS bairro_servidor,
                translate(upper(pais_nasceu.nome),'áéíóúýàèìòùãõâêîôûäëïöüç','ÁÉÍÓÚÝÀÈÌÒÙÃÕÂÊÎÔÛÄËÏÖÜÇ') AS pais_nasceu,
                translate(upper(municipio_mora.sigla_uf),'áéíóúýàèìòùãõâêîôûäëïöüç','ÁÉÍÓÚÝÀÈÌÒÙÃÕÂÊÎÔÛÄËÏÖÜÇ') AS estado_casa_servidor,
                translate(upper(municipio_mora.nome),'áéíóúýàèìòùãõâêîôûäëïöüç','ÁÉÍÓÚÝÀÈÌÒÙÃÕÂÊÎÔÛÄËÏÖÜÇ') AS municipio_casa_servidor,
                translate(upper(logradouro.nome),'áéíóúýàèìòùãõâêîôûäëïöüç','ÁÉÍÓÚÝÀÈÌÒÙÃÕÂÊÎÔÛÄËÏÖÜÇ') AS nm_logradouro,
                translate(upper(logradouro.idtlog::varchar),'áéíóúýàèìòùãõâêîôûäëïöüç','ÁÉÍÓÚÝÀÈÌÒÙÃÕÂÊÎÔÛÄËÏÖÜÇ') AS tipo_logradouro,
                translate(upper(endereco_pessoa.complemento),'áéíóúýàèìòùãõâêîôûäëïöüç','ÁÉÍÓÚÝÀÈÌÒÙÃÕÂÊÎÔÛÄËÏÖÜÇ') AS complemento,
                endereco_pessoa.cep AS cep,
                endereco_pessoa.numero AS numero_casa,
                translate(upper(pessoa.nome),'áéíóúýàèìòùãõâêîôûäëïöüç','ÁÉÍÓÚÝÀÈÌÒÙÃÕÂÊÎÔÛÄËÏÖÜÇ') AS nm_servidor,
                translate(upper(pessoa.email),'áéíóúýàèìòùãõâêîôûäëïöüç','ÁÉÍÓÚÝÀÈÌÒÙÃÕÂÊÎÔÛÄËÏÖÜÇ') AS email,
                translate(upper(estado_civil.descricao),'áéíóúýàèìòùãõâêîôûäëïöüç','ÁÉÍÓÚÝÀÈÌÒÙÃÕÂÊÎÔÛÄËÏÖÜÇ') AS estado_civil,
                translate(upper(religions.name),'áéíóúýàèìòùãõâêîôûäëïöüç','ÁÉÍÓÚÝÀÈÌÒÙÃÕÂÊÎÔÛÄËÏÖÜÇ') AS religiao,
                translate(upper(documento.sigla_uf_exp_rg),'áéíóúýàèìòùãõâêîôûäëïöüç','ÁÉÍÓÚÝÀÈÌÒÙÃÕÂÊÎÔÛÄËÏÖÜÇ') AS sigla_uf_exp_rg,
                translate(upper(documento.sigla_uf_cert_civil),'áéíóúýàèìòùãõâêîôûäëïöüç','ÁÉÍÓÚÝÀÈÌÒÙÃÕÂÊÎÔÛÄËÏÖÜÇ') AS sigla_uf_cert_civil,
                translate(upper(orgao_emissor_rg.sigla),'áéíóúýàèìòùãõâêîôûäëïöüç','ÁÉÍÓÚÝÀÈÌÒÙÃÕÂÊÎÔÛÄËÏÖÜÇ') AS orgao_exp,
                translate(upper(pessoa_pai.nome),'áéíóúýàèìòùãõâêîôûäëïöüç','ÁÉÍÓÚÝÀÈÌÒÙÃÕÂÊÎÔÛÄËÏÖÜÇ') AS nome_pai,
                translate(upper(pessoa_mae.nome),'áéíóúýàèìòùãõâêîôûäëïöüç','ÁÉÍÓÚÝÀÈÌÒÙÃÕÂÊÎÔÛÄËÏÖÜÇ') AS nome_mae,
                fisica.sexo AS sexo,
                to_char(fisica.data_nasc,'dd/mm/yyyy') AS data_nasc,
                lpad(cast(fisica.cpf AS varchar),11,'0') AS cpf,
                fisica.nis_pis_pasep AS nis_pis_pasep,
                documento.rg AS rg,
                to_char(documento.data_exp_rg,'dd/mm/yyyy') AS data_exp_rg,
                documento.num_termo AS num_termo,
                documento.num_livro AS num_livro,
                documento.num_folha AS num_folha,
                to_char(documento.data_emissao_cert_civil,'dd/mm/yyyy') AS data_emissao_cert_civil,
                documento.num_tit_eleitor AS num_tit_eleitor,
                documento.zona_tit_eleitor AS zona_tit_eleitor,
                documento.secao_tit_eleitor AS secao_tit_eleitor,
                documento.cartorio_cert_civil_inep AS cartorio_cert_civil_inep,
                documento.cartorio_cert_civil AS cartorio_cert_civil,
                documento.certidao_nascimento AS certidao_nascimento,
                documento.certidao_casamento AS certidao_casamento,
                documento.tipo_cert_civil AS int_tipo_cert_civil,
                documento.certidao_nascimento AS eh_certidao_nascimento,
                documento.certidao_casamento AS eh_certidao_casamento,
                (
                    CASE WHEN telefone.fone IS NOT NULL
                        AND telefone_add.fone IS NOT NULL
                        AND telefone_fax.fone IS NOT NULL
                    THEN
                        '(' || telefone.ddd || ') ' || telefone.fone || ' - ' || '('|| telefone_add.ddd ||') '|| telefone_add.fone || ' - '|| 'FAX: (' || telefone_fax.ddd ||') '|| telefone_fax.fone ||'.'
                    WHEN telefone.fone IS NOT NULL
                        AND telefone_add.fone IS NULL
                        AND telefone_fax.fone IS NULL
                    THEN '(' || telefone.ddd || ') ' || telefone.fone
                    WHEN telefone.fone IS NULL
                        AND telefone_add.fone IS NOT NULL
                        AND telefone_fax.fone IS NULL
                    THEN
                        '(' || telefone_add.ddd || ') ' || telefone_add.fone
                    WHEN telefone.fone IS NULL
                        AND telefone_add.fone IS NULL
                        AND telefone_fax.fone IS NOT NULL
                    THEN
                        '(' || telefone_fax.ddd || ') ' || telefone_fax.fone
                    WHEN telefone.fone IS NOT NULL
                        AND telefone_add.fone IS NOT NULL
                        AND telefone_fax.fone IS NULL
                    THEN
                        '(' || telefone.ddd || ') ' || telefone.fone || ' - ' || '('|| telefone_add.ddd ||') '|| telefone_add.fone || '.'
                    WHEN telefone.fone IS NOT NULL
                        AND telefone_add.fone IS NULL
                        AND telefone_fax.fone IS NOT NULL
                    THEN
                        '(' || telefone.ddd || ') ' || telefone.fone || ' - ' || 'FAX: (' || telefone_fax.ddd ||') '|| telefone_fax.fone || '.'
                    WHEN telefone.fone IS NULL
                        AND telefone_add.fone IS NOT NULL
                        AND telefone_fax.fone IS NOT NULL
                    THEN
                        '('|| telefone_add.ddd ||') '|| telefone_add.fone || ' - '|| 'FAX: (' || telefone_fax.ddd ||') '|| telefone_fax.fone || '.'
                    ELSE ''
                 END
                ) AS telefones,
                celular.ddd AS celular_ddd,
                celular.fone AS celular_fone,
                fisica_foto.caminho,
                fisica.data_admissao AS dt_adimissao
            FROM
                cadastro.pessoa pessoa
            INNER JOIN cadastro.fisica ON TRUE
                AND pessoa.idpes = fisica.idpes
            INNER JOIN pmieducar.servidor ON TRUE
                AND pessoa.idpes = servidor.cod_servidor
            LEFT JOIN cadastro.estado_civil ON TRUE
                AND estado_civil.ideciv = fisica.ideciv
            LEFT JOIN pmieducar.religions ON TRUE
                AND fisica.ref_cod_religiao = religions.id
            LEFT JOIN cadastro.documento ON TRUE
                AND pessoa.idpes = documento.idpes
            LEFT JOIN cadastro.endereco_pessoa ON TRUE
                AND pessoa.idpes = endereco_pessoa.idpes
            LEFT JOIN public.logradouro ON TRUE
                AND logradouro.idlog = endereco_pessoa.idlog
            LEFT JOIN public.municipio municipio_mora ON TRUE
                AND logradouro.idmun = municipio_mora.idmun
            LEFT JOIN public.bairro ON TRUE
                AND bairro.idbai = endereco_pessoa.idbai
            LEFT JOIN cadastro.orgao_emissor_rg ON TRUE
                AND orgao_emissor_rg.idorg_rg = documento.idorg_exp_rg
            LEFT JOIN cadastro.fone_pessoa telefone ON TRUE
                AND telefone.idpes = pessoa.idpes
                AND telefone.tipo = 1
            LEFT JOIN cadastro.fone_pessoa telefone_add ON TRUE
                AND telefone_add.idpes = pessoa.idpes
                AND telefone_add.tipo = 2
            LEFT JOIN cadastro.fone_pessoa celular ON TRUE
                AND celular.idpes = pessoa.idpes
                AND celular.tipo = 3
            LEFT JOIN cadastro.fone_pessoa telefone_fax ON TRUE
                AND telefone_fax.idpes = pessoa.idpes
                AND telefone_fax.tipo = 4
            LEFT JOIN public.municipio municipio_nasceu ON TRUE
                AND municipio_nasceu.idmun = fisica.idmun_nascimento
            LEFT JOIN public.uf ON TRUE
                AND municipio_nasceu.sigla_uf = uf.sigla_uf
            LEFT JOIN public.pais pais_nasceu ON TRUE
                AND pais_nasceu.idpais = uf.idpais
            LEFT JOIN cadastro.pessoa pessoa_mae ON TRUE
                AND pessoa_mae.idpes = fisica.idpes_mae
            LEFT JOIN cadastro.pessoa pessoa_pai ON TRUE
                AND pessoa_pai.idpes = fisica.idpes_pai
            LEFT JOIN pmieducar.servidor_alocacao ON TRUE
                AND servidor_alocacao.ref_cod_servidor = servidor.cod_servidor
            LEFT JOIN cadastro.fisica_foto ON TRUE
                AND fisica_foto.idpes = pessoa.idpes
            WHERE TRUE
                -- AND servidor_alocacao.ano = {$ano}
                -- AND servidor.ref_cod_instituicao = {$instituicao}
                -- AND servidor_alocacao.ref_cod_escola = {$escola}
                AND
                (
                    SELECT CASE WHEN {$servidor} = 0 THEN
                        TRUE
                    ELSE
                        servidor.cod_servidor = {$servidor}
                    END
                )
        ";
    }

    /**
     * Retorna os dados e formação dos servidores com IDs.
     *
     * @param array $servidoresIds IDs dos servidores
     * @param int   $instituicao
     *
     * @return array
     *
     * @throws Exception
     */
    private function getServantFormations($servidoresIds, $instituicao)
    {
        if (empty($servidoresIds)) {
            return [];
        }

        $servidoresIds = implode(', ', $servidoresIds);

        $sql = "

            SELECT DISTINCT
                servidor.cod_servidor,
                translate(upper(pessoa.nome),'áéíóúýàèìòùãõâêîôûäëïöüç','ÁÉÍÓÚÝÀÈÌÒÙÃÕÂÊÎÔÛÄËÏÖÜÇ') AS nm_servidor,
                translate(upper(escolaridade.descricao),'áéíóúýàèìòùãõâêîôûäëïöüç','ÁÉÍÓÚÝÀÈÌÒÙÃÕÂÊÎÔÛÄËÏÖÜÇ') AS escolaridade,
                translate(upper(faculdade_um.nome),'áéíóúýàèìòùãõâêîôûäëïöüç','ÁÉÍÓÚÝÀÈÌÒÙÃÕÂÊÎÔÛÄËÏÖÜÇ') AS faculdade_um,
                translate(upper(faculdade_dois.nome),'áéíóúýàèìòùãõâêîôûäëïöüç','ÁÉÍÓÚÝÀÈÌÒÙÃÕÂÊÎÔÛÄËÏÖÜÇ') AS faculdade_dois,
                translate(upper(faculdade_tres.nome),'áéíóúýàèìòùãõâêîôûäëïöüç','ÁÉÍÓÚÝÀÈÌÒÙÃÕÂÊÎÔÛÄËÏÖÜÇ') AS faculdade_tres,
                translate(upper(curso_um.nome),'áéíóúýàèìòùãõâêîôûäëïöüç','ÁÉÍÓÚÝÀÈÌÒÙÃÕÂÊÎÔÛÄËÏÖÜÇ') AS curso_um,
                translate(upper(curso_dois.nome),'áéíóúýàèìòùãõâêîôûäëïöüç','ÁÉÍÓÚÝÀÈÌÒÙÃÕÂÊÎÔÛÄËÏÖÜÇ') AS curso_dois,
                translate(upper(curso_tres.nome),'áéíóúýàèìòùãõâêîôûäëïöüç','ÁÉÍÓÚÝÀÈÌÒÙÃÕÂÊÎÔÛÄËÏÖÜÇ') AS curso_tres,
                servidor.ano_inicio_curso_superior_1,
                servidor.ano_conclusao_curso_superior_1,
                servidor.situacao_curso_superior_1,
                servidor.ano_inicio_curso_superior_2,
                servidor.ano_conclusao_curso_superior_2,
                servidor.situacao_curso_superior_2,
                servidor.ano_inicio_curso_superior_3,
                servidor.ano_conclusao_curso_superior_3,
                servidor.situacao_curso_superior_3,
                (ARRAY[1] <@ servidor.curso_formacao_continuada)::INTEGER AS curso_creche,
                (ARRAY[2] <@ servidor.curso_formacao_continuada)::INTEGER AS curso_pre_escola,
                (ARRAY[3] <@ servidor.curso_formacao_continuada)::INTEGER AS curso_anos_iniciais,
                (ARRAY[4] <@ servidor.curso_formacao_continuada)::INTEGER AS curso_anos_finais,
                (ARRAY[5] <@ servidor.curso_formacao_continuada)::INTEGER AS curso_ensino_medio,
                (ARRAY[6] <@ servidor.curso_formacao_continuada)::INTEGER AS curso_eja,
                (ARRAY[7] <@ servidor.curso_formacao_continuada)::INTEGER AS curso_educacao_especial,
                (ARRAY[8] <@ servidor.curso_formacao_continuada)::INTEGER AS curso_educacao_indigena,
                (ARRAY[10] <@ servidor.curso_formacao_continuada)::INTEGER AS curso_educacao_ambiental,
                (ARRAY[11] <@ servidor.curso_formacao_continuada)::INTEGER AS curso_educacao_direitos_humanos,
                (ARRAY[9] <@ servidor.curso_formacao_continuada)::INTEGER AS curso_educacao_campo,
                (ARRAY[12] <@ servidor.curso_formacao_continuada)::INTEGER AS curso_genero_diversidade_sexual,
                (ARRAY[14] <@ servidor.curso_formacao_continuada)::INTEGER AS curso_relacoes_etnicorraciais,
                (ARRAY[13] <@ servidor.curso_formacao_continuada)::INTEGER AS curso_direito_crianca_adolescente,
                translate(upper(
                    (SELECT 'Especialização:  ' ||
                    (SELECT CASE WHEN ARRAY[1] <@ servidor.curso_formacao_continuada THEN 'Específico para Creche (0 a 3 anos), ' ELSE '' END) ||
                    (SELECT CASE WHEN ARRAY[2] <@ servidor.curso_formacao_continuada THEN 'Específico para Pré-Escola (4 e 5 anos), ' ELSE '' END) ||
                    (SELECT CASE WHEN ARRAY[3] <@ servidor.curso_formacao_continuada THEN 'Específico para anos iniciais do ensino fundamental, ' ELSE '' END) ||
                    (SELECT CASE WHEN ARRAY[4] <@ servidor.curso_formacao_continuada THEN 'Específico para anos finais do ensino fundamental, ' ELSE '' END) ||
                    (SELECT CASE WHEN ARRAY[5] <@ servidor.curso_formacao_continuada THEN 'Específico para ensino médio, ' ELSE '' END) ||
                    (SELECT CASE WHEN ARRAY[6] <@ servidor.curso_formacao_continuada THEN 'Específico para educação de jovens e adultos, ' ELSE '' END) ||
                    (SELECT CASE WHEN ARRAY[7] <@ servidor.curso_formacao_continuada THEN 'Específico para educação especial, ' ELSE '' END) ||
                    (SELECT CASE WHEN ARRAY[8] <@ servidor.curso_formacao_continuada THEN 'Específico para educação indígena, ' ELSE '' END) ||
                    (SELECT CASE WHEN ARRAY[10] <@ servidor.curso_formacao_continuada THEN 'Específico para educação ambiental, ' ELSE '' END) ||
                    (SELECT CASE WHEN ARRAY[11] <@ servidor.curso_formacao_continuada THEN 'Específico para educação em direitos humanos, ' ELSE '' END) ||
                    (SELECT CASE WHEN ARRAY[9] <@ servidor.curso_formacao_continuada THEN 'Específico para educação do campo, ' ELSE '' END) ||
                    (SELECT CASE WHEN ARRAY[12] <@ servidor.curso_formacao_continuada THEN 'Gênero e diversidade sexual, ' ELSE '' END) ||
                    (SELECT CASE WHEN ARRAY[14] <@ servidor.curso_formacao_continuada THEN 'Educação para as relações etnicorraciais e História e cultura Afro-Brasileira e Africana, ' ELSE '' END) ||
                    (SELECT CASE WHEN ARRAY[13] <@ servidor.curso_formacao_continuada THEN 'Direito das crianças e adolescentes, ' ELSE '' END))),'áéíóúýàèìòùãõâêîôûäëïöüç','ÁÉÍÓÚÝÀÈÌÒÙÃÕÂÊÎÔÛÄËÏÖÜÇ'
                ) AS especializacao,
                (ARRAY[1] <@ servidor.pos_graduacao)::INTEGER AS pos_especializacao,
                (ARRAY[2] <@ servidor.pos_graduacao)::INTEGER AS pos_mestrado,
                (ARRAY[3] <@ servidor.pos_graduacao)::INTEGER AS pos_doutorado,
                (ARRAY[4] <@ servidor.pos_graduacao)::INTEGER AS pos_nenhuma
            FROM
                cadastro.pessoa pessoa
            INNER JOIN cadastro.fisica ON TRUE
                AND pessoa.idpes = fisica.idpes
            INNER JOIN pmieducar.servidor ON TRUE
                AND pessoa.idpes = servidor.cod_servidor
            LEFT JOIN cadastro.escolaridade ON TRUE
                AND escolaridade.idesco = servidor.ref_idesco
            LEFT JOIN modules.educacenso_ies faculdade_um ON
                TRUE AND faculdade_um.id = servidor.instituicao_curso_superior_1
            LEFT JOIN modules.educacenso_ies faculdade_dois ON
                TRUE AND faculdade_dois.id = servidor.instituicao_curso_superior_2
            LEFT JOIN modules.educacenso_ies faculdade_tres ON
                TRUE AND faculdade_tres.id = servidor.instituicao_curso_superior_3
            LEFT JOIN pmieducar.servidor_alocacao ON TRUE
                AND servidor_alocacao.ref_cod_servidor = servidor.cod_servidor
            LEFT JOIN modules.educacenso_curso_superior curso_um ON
                TRUE AND curso_um.id = servidor.codigo_curso_superior_1
            LEFT JOIN modules.educacenso_curso_superior curso_dois ON
                TRUE AND curso_dois.id = servidor.codigo_curso_superior_2
            LEFT JOIN modules.educacenso_curso_superior curso_tres ON
                TRUE AND curso_tres.id = servidor.codigo_curso_superior_3
            WHERE TRUE
                AND servidor.ref_cod_instituicao = {$instituicao}
                AND servidor.cod_servidor IN ({$servidoresIds})

        ";

        return Portabilis_Utils_Database::fetchPreparedQuery($sql);
    }

    /**
     * Retorna os dados profissionais dos servidores.
     *
     * @param array $servidoresIds IDs dos servidores
     * @param int   $ano
     * @param int   $instituicao
     *
     * @return array
     *
     * @throws Exception
     */
    private function getServantDataProfessional($servidoresIds, $ano, $instituicao)
    {
        if (empty($servidoresIds)) {
            return [];
        }

        $servidoresIds = implode(', ', $servidoresIds);

        $sql = "

            SELECT DISTINCT
                servidor.cod_servidor,
                translate(upper(pessoa.nome),'áéíóúýàèìòùãõâêîôûäëïöüç','ÁÉÍÓÚÝÀÈÌÒÙÃÕÂÊÎÔÛÄËÏÖÜÇ') AS nm_servidor,
                translate(upper(turma_turno.nome),'áéíóúýàèìòùãõâêîôûäëïöüç','ÁÉÍÓÚÝÀÈÌÒÙÃÕÂÊÎÔÛÄËÏÖÜÇ') AS turno,
                translate(upper(funcionario_vinculo.nm_vinculo),'áéíóúýàèìòùãõâêîôûäëïöüç','ÁÉÍÓÚÝÀÈÌÒÙÃÕÂÊÎÔÛÄËÏÖÜÇ') AS nm_vinculo,
                translate(upper(funcao.nm_funcao),'áéíóúýàèìòùãõâêîôûäëïöüç','ÁÉÍÓÚÝÀÈÌÒÙÃÕÂÊÎÔÛÄËÏÖÜÇ') AS nm_funcao,
                turma_turno.id,
                substring(servidor_alocacao.carga_horaria::varchar,1,5) AS carga_horaria,
                funcao.professor,
                fisica.data_admissao AS dt_admissao,
                translate(upper((
                    SELECT replace(textcat_all(curso.nm_curso), ' <br>', ',')
                    FROM pmieducar.curso
                    WHERE cod_curso in (
                        SELECT DISTINCT ref_cod_curso
                        FROM pmieducar.servidor_curso_ministra
                        WHERE curso.cod_curso = servidor_curso_ministra.ref_cod_curso
                        AND servidor_curso_ministra.ref_cod_servidor = servidor.cod_servidor
                    ))),'áéíóúýàèìòùãõâêîôûäëïöüç','ÁÉÍÓÚÝÀÈÌÒÙÃÕÂÊÎÔÛÄËÏÖÜÇ'
                ) AS curso,
                juridica.fantasia
            FROM
                cadastro.pessoa pessoa
            INNER JOIN cadastro.fisica ON TRUE
                AND pessoa.idpes = fisica.idpes
            INNER JOIN pmieducar.servidor ON TRUE
                AND pessoa.idpes = servidor.cod_servidor
            LEFT JOIN cadastro.documento ON TRUE
                AND pessoa.idpes = documento.idpes
            LEFT JOIN cadastro.endereco_pessoa ON TRUE
                AND pessoa.idpes = endereco_pessoa.idpes
            LEFT JOIN pmieducar.servidor_alocacao ON TRUE
                AND servidor_alocacao.ref_cod_servidor = servidor.cod_servidor
            LEFT JOIN pmieducar.turma_turno ON TRUE
                AND turma_turno.id = servidor_alocacao.periodo
            LEFT JOIN portal.funcionario_vinculo ON TRUE
                AND funcionario_vinculo.cod_funcionario_vinculo = servidor_alocacao.ref_cod_funcionario_vinculo
            LEFT JOIN pmieducar.servidor_funcao ON TRUE
                AND servidor_funcao.cod_servidor_funcao = servidor_alocacao.ref_cod_servidor_funcao
            LEFT JOIN pmieducar.funcao ON TRUE
                AND servidor_funcao.ref_cod_funcao = funcao.cod_funcao
            LEFT JOIN pmieducar.servidor_disciplina ON TRUE
                AND servidor_disciplina.ref_cod_servidor = servidor.cod_servidor
                AND servidor_disciplina.ref_ref_cod_instituicao = servidor_funcao.ref_cod_funcao
            LEFT JOIN pmieducar.curso ON TRUE
                AND curso.cod_curso = servidor_disciplina.ref_cod_curso
            LEFT JOIN pmieducar.escola ON TRUE
                AND escola.cod_escola = servidor_alocacao.ref_cod_escola
            LEFT JOIN cadastro.juridica ON TRUE
                AND escola.ref_idpes = juridica.idpes
            WHERE TRUE
                AND servidor_alocacao.ano = {$ano}
                AND servidor.ref_cod_instituicao = {$instituicao}
                AND servidor.cod_servidor IN ({$servidoresIds})
                AND servidor_alocacao.ativo = 1
                AND fisica.ativo = 1

        ";

        return Portabilis_Utils_Database::fetchPreparedQuery($sql);
    }
}
