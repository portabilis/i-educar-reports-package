<?php

use iEducar\Reports\JsonDataSource;

class StudentSheetReport extends Portabilis_Report_ReportCore
{
    use JsonDataSource;

    /**
     * @inheritdoc
     */
    public function templateName()
    {
        return 'student-sheet';
    }

    /**
     * @inheritdoc
     */
    public function requiredArgs()
    {
        $this->addRequiredArg('instituicao');
        $this->addRequiredArg('escola');
        $this->addRequiredArg('modelo');
    }

    /**
     * Retorna o SQL para buscar os dados do relatório principal.
     *
     * @return string
     */
    public function getSqlMainReport()
    {
        $escola = $this->args['escola'] ?: 0;
        $instituicao = $this->args['instituicao'] ?: 0;
        $matricula = $this->args['matricula'] ?: 0;
        $curso = $this->args['curso'] ?: 0;
        $serie = $this->args['serie'] ?: 0;
        $turma = $this->args['turma'] ?: 0;
        $ano = $this->args['ano'] ?: 0;

        return "
SELECT (cod_aluno), public.fcn_upper(nm_instituicao) AS nome_instituicao,
                    public.fcn_upper(nm_responsavel) AS nome_secretaria,
                    instituicao.cidade AS cidade_instituicao,
                    public.fcn_upper(ref_sigla_uf) AS uf_instituicao,
                    to_char(CURRENT_DATE,'dd/mm/yyyy') AS data_atual,
                    to_char(CURRENT_TIMESTAMP, 'HH24:MI:SS') AS hora_atual,
                    pessoa.nome AS aluno,
                    fcn_upper(COALESCE(relatorio.get_pai_aluno(aluno.cod_aluno), 'NAO INFORMADO')) AS nm_pai,
                    fcn_upper(COALESCE(relatorio.get_mae_aluno(aluno.cod_aluno), 'NAO INFORMADO')) AS nm_mae,
                    fisica.sexo,
                    to_char(fisica.data_nasc,'dd/mm/yyyy') AS data_nasc,
                    religions.name AS religiao,
                    relatorio.get_nacionalidade(fisica.nacionalidade) AS nacionalidade,
                    (CASE
                         WHEN aluno.analfabeto = 0 THEN 'Sim'
                         WHEN aluno.analfabeto = 1 THEN 'Não'
                         ELSE ''
                     END) AS alfabetizado,

  (SELECT municipio.nome
   FROM public.municipio
   WHERE municipio.idmun = fisica.idmun_nascimento) AS municipio_nasc,

  (SELECT municipio.sigla_uf
   FROM public.municipio
   WHERE municipio.idmun = fisica.idmun_nascimento) AS municipio_uf,

  (SELECT pais.nome
   FROM public.pais
   WHERE pais.idpais = fisica.idpais_estrangeiro) AS pais_origem,

  (SELECT estado_civil.descricao
   FROM cadastro.estado_civil
   WHERE estado_civil.ideciv = fisica.ideciv) AS estado_civil,

  (SELECT ps.nome
   FROM cadastro.pessoa ps
   WHERE ps.idpes = fisica.idpes_mae) AS nm_mae,

  (SELECT ps.nome
   FROM cadastro.pessoa ps
   WHERE ps.idpes = fisica.idpes_pai) AS nm_pai,

  (SELECT public.formata_cpf(fs.cpf)
   FROM cadastro.fisica fs
   WHERE fs.idpes = fisica.idpes_mae) AS cpf_mae,

  (SELECT public.formata_cpf(fs.cpf)
   FROM cadastro.fisica fs
   WHERE fs.idpes = fisica.idpes_pai) AS cpf_pai,

  (SELECT textcat_all(aluno_beneficio.nm_beneficio)
   FROM pmieducar.aluno_beneficio,
        pmieducar.aluno_aluno_beneficio
   WHERE pmieducar.aluno_aluno_beneficio.aluno_id = aluno.cod_aluno
     AND pmieducar.aluno_beneficio.cod_aluno_beneficio = pmieducar.aluno_aluno_beneficio.aluno_beneficio_id) AS beneficio,

  (SELECT COALESCE(
                     (SELECT endereco_pessoa.numero
                      FROM cadastro.endereco_pessoa
                      WHERE endereco_pessoa.idpes = pessoa.idpes),
                     (SELECT endereco_externo.numero
                      FROM cadastro.endereco_externo
                      WHERE endereco_externo.idpes = aluno.ref_idpes))) AS numero,

  (SELECT COALESCE(
                     (SELECT endereco_pessoa.letra
                      FROM cadastro.endereco_pessoa
                      WHERE endereco_pessoa.idpes = pessoa.idpes),
                     (SELECT endereco_externo.letra
                      FROM cadastro.endereco_externo
                      WHERE endereco_externo.idpes = aluno.ref_idpes))) AS letra,

  (SELECT COALESCE(
                     (SELECT endereco_pessoa.bloco
                      FROM cadastro.endereco_pessoa
                      WHERE endereco_pessoa.idpes = pessoa.idpes),
                     (SELECT endereco_externo.bloco
                      FROM cadastro.endereco_externo
                      WHERE endereco_externo.idpes = aluno.ref_idpes))) AS bloco,

  (SELECT COALESCE(
                     (SELECT endereco_pessoa.andar
                      FROM cadastro.endereco_pessoa
                      WHERE endereco_pessoa.idpes = pessoa.idpes),
                     (SELECT endereco_externo.andar
                      FROM cadastro.endereco_externo
                      WHERE endereco_externo.idpes = aluno.ref_idpes))) AS andar,

  (SELECT COALESCE(
                     (SELECT endereco_pessoa.apartamento
                      FROM cadastro.endereco_pessoa
                      WHERE endereco_pessoa.idpes = pessoa.idpes),
                     (SELECT endereco_externo.apartamento
                      FROM cadastro.endereco_externo
                      WHERE endereco_externo.idpes = aluno.ref_idpes))) AS apartamento,

  (SELECT COALESCE(
                     (SELECT endereco_pessoa.complemento
                      FROM cadastro.endereco_pessoa
                      WHERE endereco_pessoa.idpes = pessoa.idpes),
                     (SELECT endereco_externo.complemento
                      FROM cadastro.endereco_externo
                      WHERE endereco_externo.idpes = aluno.ref_idpes))) AS complemento,

  (SELECT COALESCE(
                     (SELECT endereco_pessoa.cep
                      FROM cadastro.endereco_pessoa
                      WHERE endereco_pessoa.idpes = pessoa.idpes),
                     (SELECT endereco_externo.cep
                      FROM cadastro.endereco_externo
                      WHERE endereco_externo.idpes = aluno.ref_idpes))) AS cep,

  (SELECT COALESCE(
                     (SELECT logradouro.nome
                      FROM public.logradouro, cadastro.endereco_pessoa
                      WHERE logradouro.idlog = endereco_pessoa.idlog
                        AND endereco_pessoa.idpes = pessoa.idpes),
                     (SELECT endereco_externo.logradouro
                      FROM cadastro.endereco_externo
                      WHERE endereco_externo.idpes = aluno.ref_idpes))) AS logradouro,

  (SELECT COALESCE(
                     (SELECT municipio.nome
                      FROM public.municipio, public.logradouro, cadastro.endereco_pessoa
                      WHERE municipio.idmun = logradouro.idmun
                        AND logradouro.idlog = endereco_pessoa.idlog
                        AND endereco_pessoa.idpes = pessoa.idpes),
                     (SELECT endereco_externo.cidade
                      FROM cadastro.endereco_externo
                      WHERE endereco_externo.idpes = aluno.ref_idpes))) AS municipio,

  (SELECT COALESCE(
                     (SELECT municipio.sigla_uf
                      FROM public.municipio, public.logradouro, cadastro.endereco_pessoa
                      WHERE municipio.idmun = logradouro.idmun
                        AND logradouro.idlog = endereco_pessoa.idlog
                        AND endereco_pessoa.idpes = pessoa.idpes),
                     (SELECT endereco_externo.sigla_uf
                      FROM cadastro.endereco_externo
                      WHERE endereco_externo.idpes = aluno.ref_idpes))) AS sigla_uf,

  (SELECT COALESCE(
                     (SELECT min(bairro.nome)
                      FROM public.bairro, public.municipio, public.logradouro, cadastro.endereco_pessoa
                      WHERE bairro.idmun = municipio.idmun
                        AND municipio.idmun = logradouro.idmun
                        AND logradouro.idlog = endereco_pessoa.idlog
                        AND endereco_pessoa.idpes = aluno.ref_idpes
                        AND endereco_pessoa.idbai = bairro.idbai),
                     (SELECT endereco_externo.bairro
                      FROM cadastro.endereco_externo
                      WHERE endereco_externo.idpes = aluno.ref_idpes))) AS bairro,
                    pessoa.email,

  (SELECT fone_pessoa.fone
   FROM cadastro.fone_pessoa
   WHERE fone_pessoa.idpes = fisica.idpes
     AND fone_pessoa.tipo = 1) AS fone,

  (SELECT fone_pessoa.ddd
   FROM cadastro.fone_pessoa
   WHERE fone_pessoa.idpes = fisica.idpes
     AND fone_pessoa.tipo = 1) AS ddd,

  (SELECT fone_pessoa.fone
   FROM cadastro.fone_pessoa
   WHERE fone_pessoa.idpes = fisica.idpes
     AND fone_pessoa.tipo = 2) AS fone2,

  (SELECT fone_pessoa.ddd
   FROM cadastro.fone_pessoa
   WHERE fone_pessoa.idpes = fisica.idpes
     AND fone_pessoa.tipo = 2) AS ddd2,

  (SELECT fone_pessoa.fone
   FROM cadastro.fone_pessoa
   WHERE fone_pessoa.idpes = fisica.idpes
     AND fone_pessoa.tipo = 3) AS fone3,

  (SELECT fone_pessoa.ddd
   FROM cadastro.fone_pessoa
   WHERE fone_pessoa.idpes = fisica.idpes
     AND fone_pessoa.tipo = 3) AS ddd3,

  (SELECT fone_pessoa.fone
   FROM cadastro.fone_pessoa
   WHERE fone_pessoa.idpes = fisica.idpes
     AND fone_pessoa.tipo = 2) AS fone_aluno2,

  (SELECT fone_pessoa.fone
   FROM cadastro.fone_pessoa
   WHERE fone_pessoa.idpes = fisica.idpes
     AND fone_pessoa.tipo = 3) AS fone_aluno3,

  (SELECT min(deficiencia.nm_deficiencia)
   FROM cadastro.deficiencia,
        cadastro.fisica_deficiencia
   WHERE deficiencia.cod_deficiencia = fisica_deficiencia.ref_cod_deficiencia
     AND fisica_deficiencia.ref_idpes = fisica.idpes) AS deficiencia,

  (SELECT public.formata_cpf(fs.cpf)
   FROM cadastro.fisica fs
   WHERE fs.idpes = fisica.idpes) AS cpf,

  (SELECT fs.nis_pis_pasep
   FROM cadastro.fisica fs
   WHERE fs.idpes = fisica.idpes) AS pis,

  (SELECT documento.rg
   FROM cadastro.documento
   WHERE documento.idpes = fisica.idpes) AS rg,

  (SELECT documento.data_exp_rg
   FROM cadastro.documento
   WHERE documento.idpes = fisica.idpes) AS data_exp_rg,

  (SELECT documento.sigla_uf_exp_rg
   FROM cadastro.documento
   WHERE documento.idpes = fisica.idpes) AS sigla_uf_exp_rg,

  (SELECT orgao_emissor_rg.sigla
   FROM cadastro.orgao_emissor_rg,
        cadastro.documento
   WHERE orgao_emissor_rg.idorg_rg = documento.idorg_exp_rg
     AND documento.idpes = fisica.idpes) AS orgao_emissor_rg,

  (SELECT documento.num_tit_eleitor
   FROM cadastro.documento
   WHERE documento.idpes = fisica.idpes) AS num_tit_eleitor,

  (SELECT documento.zona_tit_eleitor
   FROM cadastro.documento
   WHERE documento.idpes = fisica.idpes) AS zona_tit_eleitor,

  (SELECT documento.secao_tit_eleitor
   FROM cadastro.documento
   WHERE documento.idpes = fisica.idpes) AS secao_tit_eleitor,

  (SELECT documento.tipo_cert_civil
   FROM cadastro.documento
   WHERE documento.idpes = fisica.idpes) AS tipo_cert_civil,

  (SELECT documento.sigla_uf_cert_civil
   FROM cadastro.documento
   WHERE documento.idpes = fisica.idpes) AS sigla_uf_cert_civil,

  (SELECT to_char(documento.data_emissao_cert_civil, 'dd/mm/yyyy')
   FROM cadastro.documento
   WHERE documento.idpes = fisica.idpes) AS data_emissao_cert_civil,

  (SELECT documento.num_termo
   FROM cadastro.documento
   WHERE documento.idpes = fisica.idpes) AS num_termo,

  (SELECT documento.num_livro
   FROM cadastro.documento
   WHERE documento.idpes = fisica.idpes) AS num_livro,

  (SELECT documento.num_folha
   FROM cadastro.documento
   WHERE documento.idpes = fisica.idpes) AS num_folha,

  (SELECT documento.cartorio_cert_civil
   FROM cadastro.documento
   WHERE documento.idpes = fisica.idpes) AS cartorio_cert_civil,

  (SELECT documento.certidao_nascimento
   FROM cadastro.documento
   WHERE documento.idpes = fisica.idpes) AS certidao_nascimento,

  (SELECT ps.nome
   FROM cadastro.pessoa ps
   WHERE ps.idpes = fisica.idpes_responsavel) AS nome_responsavel,

  (SELECT fs.ocupacao
   FROM cadastro.fisica fs
   WHERE fs.idpes = fisica.idpes_responsavel) AS ocupacao_responsavel,

  (SELECT fs.cpf
   FROM cadastro.fisica fs
   WHERE fs.idpes = fisica.idpes_responsavel) AS cpf_responsavel,

  (SELECT fs.sexo
   FROM cadastro.fisica fs
   WHERE fs.idpes = fisica.idpes_responsavel) AS sexo_responsavel,

  (SELECT min(fone_pessoa.ddd)
   FROM cadastro.fone_pessoa
   WHERE fone_pessoa.idpes = fisica.idpes_responsavel) AS ddd_responsavel,

  (SELECT min(fone_pessoa.fone)
   FROM cadastro.fone_pessoa
   WHERE fone_pessoa.idpes = fisica.idpes_responsavel) AS fone_responsavel,

  (SELECT pessoa.email
   FROM cadastro.pessoa
   WHERE pessoa.idpes = fisica.idpes_responsavel) AS email_responsavel,

  (SELECT COALESCE(
                     (SELECT endereco_pessoa.numero
                      FROM cadastro.endereco_pessoa
                      WHERE endereco_pessoa.idpes = fisica.idpes_responsavel),
                     (SELECT endereco_externo.numero
                      FROM cadastro.endereco_externo
                      WHERE endereco_externo.idpes = fisica.idpes_responsavel))) AS numero_responsavel,

  (SELECT COALESCE(
                     (SELECT endereco_pessoa.letra
                      FROM cadastro.endereco_pessoa
                      WHERE endereco_pessoa.idpes = fisica.idpes_responsavel),
                     (SELECT endereco_externo.letra
                      FROM cadastro.endereco_externo
                      WHERE endereco_externo.idpes = fisica.idpes_responsavel))) AS letra_responsavel,

  (SELECT COALESCE(
                     (SELECT endereco_pessoa.bloco
                      FROM cadastro.endereco_pessoa
                      WHERE endereco_pessoa.idpes = fisica.idpes_responsavel),
                     (SELECT endereco_externo.bloco
                      FROM cadastro.endereco_externo
                      WHERE endereco_externo.idpes = fisica.idpes_responsavel))) AS bloco_responsavel,

  (SELECT COALESCE(
                     (SELECT endereco_pessoa.andar
                      FROM cadastro.endereco_pessoa
                      WHERE endereco_pessoa.idpes = fisica.idpes_responsavel),
                     (SELECT endereco_externo.andar
                      FROM cadastro.endereco_externo
                      WHERE endereco_externo.idpes = fisica.idpes_responsavel))) AS andar_responsavel,

  (SELECT COALESCE(
                     (SELECT endereco_pessoa.apartamento
                      FROM cadastro.endereco_pessoa
                      WHERE endereco_pessoa.idpes = fisica.idpes_responsavel),
                     (SELECT endereco_externo.apartamento
                      FROM cadastro.endereco_externo
                      WHERE endereco_externo.idpes = fisica.idpes_responsavel))) AS apartamento_responsavel,

  (SELECT COALESCE(
                     (SELECT endereco_pessoa.cep
                      FROM cadastro.endereco_pessoa
                      WHERE endereco_pessoa.idpes = fisica.idpes_responsavel),
                     (SELECT endereco_externo.cep
                      FROM cadastro.endereco_externo
                      WHERE endereco_externo.idpes = fisica.idpes_responsavel))) AS cep_responsavel,

  (SELECT COALESCE(
                     (SELECT logradouro.nome
                      FROM public.logradouro, cadastro.endereco_pessoa
                      WHERE logradouro.idlog = endereco_pessoa.idlog
                        AND endereco_pessoa.idpes = fisica.idpes_responsavel),
                     (SELECT endereco_externo.logradouro
                      FROM cadastro.endereco_externo
                      WHERE endereco_externo.idpes = fisica.idpes_responsavel))) AS logradouro_responsavel,

  (SELECT COALESCE(
                     (SELECT municipio.nome
                      FROM public.municipio, public.logradouro, cadastro.endereco_pessoa
                      WHERE municipio.idmun = logradouro.idmun
                        AND logradouro.idlog = endereco_pessoa.idlog
                        AND endereco_pessoa.idpes = fisica.idpes_responsavel),
                     (SELECT endereco_externo.cidade
                      FROM cadastro.endereco_externo
                      WHERE endereco_externo.idpes = fisica.idpes_responsavel))) AS municipio_responsavel,

  (SELECT COALESCE(
                     (SELECT municipio.sigla_uf
                      FROM public.municipio, public.logradouro, cadastro.endereco_pessoa
                      WHERE municipio.idmun = logradouro.idmun
                        AND logradouro.idlog = endereco_pessoa.idlog
                        AND endereco_pessoa.idpes = fisica.idpes_responsavel),
                     (SELECT endereco_externo.sigla_uf
                      FROM cadastro.endereco_externo
                      WHERE endereco_externo.idpes = fisica.idpes_responsavel))) AS sigla_uf_responsavel,

  (SELECT COALESCE(
                     (SELECT bairro.nome
                      FROM public.bairro, public.municipio, public.logradouro, cadastro.endereco_pessoa
                      WHERE bairro.idmun = municipio.idmun
                        AND municipio.idmun = logradouro.idmun
                        AND logradouro.idlog = endereco_pessoa.idlog
                        AND endereco_pessoa.idpes = fisica.idpes_responsavel
                        AND endereco_pessoa.idbai = bairro.idbai),
                     (SELECT endereco_externo.bairro
                      FROM cadastro.endereco_externo
                      WHERE endereco_externo.idpes = fisica.idpes_responsavel))) AS bairro_responsavel,

  (SELECT caminho
   FROM cadastro.fisica_foto
   WHERE idpes = aluno.ref_idpes
       AND SUBSTR(caminho, 1,27) = 'http://apps-ieducar-images.') AS foto,

  (SELECT 1
   FROM cadastro.fisica_foto
   WHERE idpes = aluno.ref_idpes
       AND SUBSTR(caminho, 1,27) = 'http://apps-ieducar-images.') AS existe_foto,

  (SELECT fisica.sus
   FROM cadastro.fisica
   WHERE idpes = aluno.ref_idpes) AS codigo_sus,
                    matricula.matricula_transferencia AS transferencia_matricula,

  (SELECT cod_aluno_inep
   FROM modules.educacenso_cod_aluno
   WHERE educacenso_cod_aluno.cod_aluno = aluno.cod_aluno) AS cod_inep,

  (SELECT max(sequencial_fechamento)
   FROM pmieducar.matricula_turma
   WHERE matricula_turma.ref_cod_matricula =
       (SELECT cod_matricula
        FROM pmieducar.matricula
        WHERE matricula.ref_cod_aluno = cod_aluno
          AND cod_matricula =
            (SELECT cod_matricula
             FROM pmieducar.matricula
             WHERE matricula.ref_cod_aluno = cod_aluno
             ORDER BY ano DESC LIMIT 1
             OFFSET 1))) AS seque_fecha,

  (SELECT ano
   FROM pmieducar.matricula
   WHERE matricula.ref_cod_aluno = cod_aluno
     AND matricula.aprovado = 1
   ORDER BY ano DESC LIMIT 1) AS ultima_matricula_ano,

  (SELECT max(nm_serie)
   FROM pmieducar.serie
   INNER JOIN pmieducar.matricula m ON (m.ref_ref_cod_serie = serie.cod_serie)
   INNER JOIN pmieducar.matricula_turma mt ON (mt.ref_cod_matricula = m.cod_matricula)
   INNER JOIN pmieducar.turma t ON (mt.ref_cod_turma = t.cod_turma)
   WHERE m.ref_cod_aluno = aluno.cod_aluno
     AND m.aprovado = 1
   GROUP BY m.ano
   ORDER BY m.ano DESC LIMIT 1) AS ultima_matricula_serie,

  (SELECT max(nm_curso)
   FROM pmieducar.curso
   INNER JOIN pmieducar.matricula m ON (m.ref_cod_curso = curso.cod_curso)
   INNER JOIN pmieducar.matricula_turma mt ON (mt.ref_cod_matricula = m.cod_matricula)
   INNER JOIN pmieducar.turma t ON (mt.ref_cod_turma = t.cod_turma)
   INNER JOIN pmieducar.turma_tipo tipo ON (tt.id = t.turma_turno_id)
   WHERE m.ref_cod_aluno = aluno.cod_aluno
     AND tipo.nm_tipo = turma_tipo.nm_tipo
     AND m.ref_cod_aluno = aluno.cod_aluno
     AND m.aprovado = 1
   GROUP BY m.ano
   ORDER BY m.ano DESC LIMIT 1) AS ultima_matricula_curso,
                    initcap(lower(
                                    (SELECT relatorio.get_nome_escola(escola.cod_escola)
                                     FROM pmieducar.escola
                                     INNER JOIN pmieducar.matricula m ON (m.ref_ref_cod_escola = escola.cod_escola)
                                     INNER JOIN pmieducar.matricula_turma mt ON (mt.ref_cod_matricula = m.cod_matricula)
                                     INNER JOIN pmieducar.turma t ON (mt.ref_cod_turma = t.cod_turma)
                                     INNER JOIN pmieducar.turma_tipo tipo ON (tt.id = t.turma_turno_id)
                                     WHERE m.ref_cod_aluno = aluno.cod_aluno
                                       AND tipo.nm_tipo = turma_tipo.nm_tipo
                                       AND m.ref_cod_aluno = aluno.cod_aluno
                                       AND m.aprovado = 1
                                     ORDER BY m.ano DESC LIMIT 1))) AS ultima_matricula_escola,

  (SELECT max(tt.nome)
   FROM pmieducar.turma_turno tt
   INNER JOIN pmieducar.turma t ON (tt.id = t.turma_turno_id)
   INNER JOIN pmieducar.turma_tipo tipo ON (t.ref_cod_turma_tipo = tipo.cod_turma_tipo)
   INNER JOIN pmieducar.matricula_turma mt ON (mt.ref_cod_turma = t.cod_turma)
   INNER JOIN pmieducar.matricula m ON (mt.ref_cod_matricula = m.cod_matricula)
   WHERE m.ref_cod_aluno = aluno.cod_aluno
     AND tipo.nm_tipo = turma_tipo.nm_tipo
     AND m.ref_cod_aluno = aluno.cod_aluno
     AND m.aprovado = 1
   GROUP BY m.ano,
            mt.sequencial
   ORDER BY m.ano,
            mt.sequencial DESC LIMIT 1) AS ultima_matricula_turno,
                    escola_ano_letivo.ano AS atual_matricula_ano,
                    serie.nm_serie AS atual_matricula_serie,
                    curso.nm_curso AS atual_matricula_curso,
                    relatorio.get_nome_escola(escola.cod_escola) AS atual_matricula_escola,
                    tt.nome AS atual_matricula_turno,
                    aluno.autorizado_um,
                    aluno.autorizado_dois,
                    aluno.autorizado_tres,
                    aluno.autorizado_quatro,
                    aluno.autorizado_cinco,
                    raca.nm_raca AS cor_raca,

  (SELECT to_char(pessoa_pai.data_nasc, 'dd/MM/yyyy')
   FROM cadastro.fisica AS pessoa_pai
   WHERE pessoa_pai.idpes = fisica.idpes_pai) AS data_nasc_pai,

  (SELECT doc_pai.rg
   FROM cadastro.fisica AS pessoa_pai,
        cadastro.documento AS doc_pai
   WHERE pessoa_pai.idpes = fisica.idpes_pai
     AND pessoa_pai.idpes = doc_pai.idpes) AS rg_pai,

  (SELECT to_char(pessoa_mae.data_nasc, 'dd/MM/yyyy')
   FROM cadastro.fisica AS pessoa_mae
   WHERE pessoa_mae.idpes = fisica.idpes_mae) AS data_nasc_mae,

  (SELECT doc_mae.rg
   FROM cadastro.fisica AS pessoa_mae,
        cadastro.documento AS doc_mae
   WHERE pessoa_mae.idpes = fisica.idpes_mae
     AND pessoa_mae.idpes = doc_mae.idpes) AS rg_mae,
                    coalesce(
                               (SELECT fone_pai.ddd
                                FROM cadastro.fisica AS pessoa_pai, cadastro.fone_pessoa AS fone_pai
                                WHERE pessoa_pai.idpes = fisica.idpes_pai
                                  AND pessoa_pai.idpes = fone_pai.idpes
                                  AND fone_pai.tipo = 1),
                               (SELECT fone_pai.ddd
                                FROM cadastro.fisica AS pessoa_pai, cadastro.fone_pessoa AS fone_pai
                                WHERE pessoa_pai.idpes = fisica.idpes_pai
                                  AND pessoa_pai.idpes = fone_pai.idpes
                                  AND fone_pai.tipo = 3),
                               (SELECT fone_pai.ddd
                                FROM cadastro.fisica AS pessoa_pai, cadastro.fone_pessoa AS fone_pai
                                WHERE pessoa_pai.idpes = fisica.idpes_pai
                                  AND pessoa_pai.idpes = fone_pai.idpes
                                  AND fone_pai.tipo = 2)) AS ddd_pai,
                    coalesce(
                               (SELECT to_char(fone_pai.fone, '9999999-9999')
                                FROM cadastro.fisica AS pessoa_pai, cadastro.fone_pessoa AS fone_pai
                                WHERE pessoa_pai.idpes = fisica.idpes_pai
                                  AND pessoa_pai.idpes = fone_pai.idpes
                                  AND fone_pai.tipo = 1),
                               (SELECT to_char(fone_pai.fone, '9999999-9999')
                                FROM cadastro.fisica AS pessoa_pai, cadastro.fone_pessoa AS fone_pai
                                WHERE pessoa_pai.idpes = fisica.idpes_pai
                                  AND pessoa_pai.idpes = fone_pai.idpes
                                  AND fone_pai.tipo = 3),
                               (SELECT to_char(fone_pai.fone, '9999999-9999')
                                FROM cadastro.fisica AS pessoa_pai, cadastro.fone_pessoa AS fone_pai
                                WHERE pessoa_pai.idpes = fisica.idpes_pai
                                  AND pessoa_pai.idpes = fone_pai.idpes
                                  AND fone_pai.tipo = 2)) AS telefone_pai,
                    coalesce(
                               (SELECT fone_mae.ddd
                                FROM cadastro.fisica AS pessoa_mae, cadastro.fone_pessoa AS fone_mae
                                WHERE pessoa_mae.idpes = fisica.idpes_mae
                                  AND pessoa_mae.idpes = fone_mae.idpes
                                  AND fone_mae.tipo = 1),
                               (SELECT fone_mae.ddd
                                FROM cadastro.fisica AS pessoa_mae, cadastro.fone_pessoa AS fone_mae
                                WHERE pessoa_mae.idpes = fisica.idpes_mae
                                  AND pessoa_mae.idpes = fone_mae.idpes
                                  AND fone_mae.tipo = 3),
                               (SELECT fone_mae.ddd
                                FROM cadastro.fisica AS pessoa_mae, cadastro.fone_pessoa AS fone_mae
                                WHERE pessoa_mae.idpes = fisica.idpes_mae
                                  AND pessoa_mae.idpes = fone_mae.idpes
                                  AND fone_mae.tipo = 2)) AS ddd_mae,
                    coalesce(
                               (SELECT to_char(fone_mae.fone, '9999999-9999')
                                FROM cadastro.fisica AS pessoa_mae, cadastro.fone_pessoa AS fone_mae
                                WHERE pessoa_mae.idpes = fisica.idpes_mae
                                  AND pessoa_mae.idpes = fone_mae.idpes
                                  AND fone_mae.tipo = 1),
                               (SELECT to_char(fone_mae.fone, '9999999-9999')
                                FROM cadastro.fisica AS pessoa_mae, cadastro.fone_pessoa AS fone_mae
                                WHERE pessoa_mae.idpes = fisica.idpes_mae
                                  AND pessoa_mae.idpes = fone_mae.idpes
                                  AND fone_mae.tipo = 3),
                               (SELECT to_char(fone_mae.fone, '9999999-9999')
                                FROM cadastro.fisica AS pessoa_mae, cadastro.fone_pessoa AS fone_mae
                                WHERE pessoa_mae.idpes = fisica.idpes_mae
                                  AND pessoa_mae.idpes = fone_mae.idpes
                                  AND fone_mae.tipo = 2)) AS telefone_mae,

  (SELECT pessoa_pai.ocupacao
   FROM cadastro.fisica AS pessoa_pai
   WHERE pessoa_pai.idpes = fisica.idpes_pai) AS profissao_pai,

  (SELECT pessoa_mae.ocupacao
   FROM cadastro.fisica AS pessoa_mae
   WHERE pessoa_mae.idpes = fisica.idpes_mae) AS profissao_mae,
                    (CASE
                         WHEN transporte_aluno.responsavel = 0 THEN 'NÃO UTILIZA'
                         ELSE 'UTILIZA'
                     END) AS transporte_aluno,

  (SELECT to_char(COALESCE(data_matricula, data_cadastro), 'dd/MM/yyyy')
   FROM pmieducar.matricula
   WHERE cod_matricula = {$matricula}
     AND matricula.ativo = 1
     AND matricula.cod_matricula =
       (SELECT max(cod_matricula)
        FROM pmieducar.matricula AS m
        WHERE m.cod_matricula = {$matricula}
          AND m.ativo = 1)) AS data_matricula
FROM pmieducar.instituicao
INNER JOIN pmieducar.escola ON (escola.ref_cod_instituicao = instituicao.cod_instituicao)
INNER JOIN pmieducar.escola_ano_letivo ON (escola_ano_letivo.ref_cod_escola = escola.cod_escola)
INNER JOIN pmieducar.escola_curso ON (escola_curso.ativo = 1
                                      AND escola_curso.ref_cod_escola = escola.cod_escola)
INNER JOIN pmieducar.curso ON (curso.cod_curso = escola_curso.ref_cod_curso
                               AND curso.ativo = 1)
INNER JOIN pmieducar.escola_serie ON (escola_serie.ativo = 1
                                      AND escola_serie.ref_cod_escola = escola.cod_escola)
INNER JOIN pmieducar.serie ON (serie.cod_serie = escola_serie.ref_cod_serie
                               AND serie.ativo = 1)
INNER JOIN pmieducar.turma ON (turma.ref_ref_cod_escola = escola.cod_escola
                               AND turma.ref_cod_curso = escola_curso.ref_cod_curso
                               AND turma.ref_ref_cod_serie = escola_serie.ref_cod_serie
                               AND turma.ativo = 1)
INNER JOIN pmieducar.turma_turno tt ON (tt.id = turma.turma_turno_id)
INNER JOIN pmieducar.matricula_turma ON (matricula_turma.ref_cod_turma = turma.cod_turma
                                         AND matricula_turma.ativo = 1)
INNER JOIN pmieducar.matricula ON (matricula.cod_matricula = matricula_turma.ref_cod_matricula
                                   AND matricula.ativo = 1)
INNER JOIN pmieducar.aluno ON (aluno.cod_aluno = matricula.ref_cod_aluno)
INNER JOIN cadastro.pessoa ON (pessoa.idpes = aluno.ref_idpes)
LEFT JOIN cadastro.fisica ON (fisica.idpes = pessoa.idpes)
LEFT JOIN modules.transporte_aluno ON (aluno.cod_aluno = transporte_aluno.aluno_id)
LEFT JOIN pmieducar.religions ON (religions.id = fisica.ref_cod_religiao)
LEFT JOIN cadastro.fisica_raca ON (pessoa.idpes = fisica_raca.ref_idpes)
LEFT JOIN cadastro.raca ON (fisica_raca.ref_cod_raca = raca.cod_raca)
INNER JOIN pmieducar.turma_tipo ON (turma.ref_cod_turma_tipo = turma_tipo.cod_turma_tipo)
WHERE instituicao.cod_instituicao = {$instituicao}
  AND escola.cod_escola = {$escola}
  AND curso.cod_curso = {$curso}
  AND serie.cod_serie = {$serie}
  AND turma.cod_turma = {$turma}
  AND escola_ano_letivo.ano = {$ano}
  AND (CASE WHEN {$matricula} = 0 THEN TRUE ELSE matricula.cod_matricula = {$matricula} END)
ORDER BY seque_fecha,
         aluno
        ";
    }
}
