SET search_path = relatorio, pmieducar, public, pg_catalog;

--
-- Name: get_ddd_escola(integer); Type: FUNCTION; Schema: relatorio; Owner: postgres
--

CREATE OR REPLACE FUNCTION get_ddd_escola(integer) RETURNS numeric
    LANGUAGE sql
    AS $_$
SELECT COALESCE(
                  (SELECT min(fone_pessoa.ddd)
                   FROM cadastro.fone_pessoa, cadastro.juridica
                   WHERE juridica.idpes = fone_pessoa.idpes
                     AND juridica.idpes =
                       (SELECT idpes
                        FROM cadastro.pessoa
                        INNER JOIN pmieducar.escola ON escola.ref_idpes = pessoa.idpes
                        WHERE cod_escola = $1)),
                  (SELECT min(ddd_telefone)
                   FROM pmieducar.escola_complemento
                   WHERE ref_cod_escola = $1)); $_$;


--
-- Name: get_mae_aluno(integer); Type: FUNCTION; Schema: relatorio; Owner: postgres
--

CREATE OR REPLACE FUNCTION get_mae_aluno(integer) RETURNS character varying
    LANGUAGE sql
    AS $_$
SELECT coalesce(
                  (SELECT nome
                   FROM cadastro.pessoa
                   WHERE idpes = fisica.idpes_mae), (aluno.nm_mae))
FROM pmieducar.aluno
INNER JOIN cadastro.fisica ON fisica.idpes = aluno.ref_idpes
WHERE aluno.ativo = 1
  AND aluno.cod_aluno = $1; $_$;


--
-- Name: situacao_matricula; Type: TABLE; Schema: relatorio; Owner: postgres
--

DROP VIEW IF EXISTS view_situacao;
DROP TABLE IF EXISTS situacao_matricula;
CREATE TABLE situacao_matricula (
    cod_situacao integer NOT NULL,
    descricao character varying(50) NOT NULL
);


--
-- Name: view_situacao; Type: VIEW; Schema: relatorio; Owner: ieducar
--

CREATE OR REPLACE VIEW view_situacao AS
    SELECT matricula.cod_matricula,
        situacao_matricula.cod_situacao,
        matricula_turma.ref_cod_turma AS cod_turma,
        matricula_turma.sequencial,
        ( SELECT
              CASE
              WHEN matricula_turma.remanejado THEN 'Remanejado'::character varying
              WHEN matricula_turma.transferido THEN 'Transferido'::character varying
              WHEN matricula_turma.reclassificado THEN 'Reclassificado'::character varying
              WHEN matricula_turma.abandono THEN 'Abandono'::character varying
              WHEN (matricula.aprovado = 1) THEN 'Aprovado'::character varying
              WHEN (matricula.aprovado = 12) THEN 'Ap. Depen.'::character varying
              WHEN (matricula.aprovado = 13) THEN 'Ap. Cons.'::character varying
              WHEN (matricula.aprovado = 2) THEN 'Reprovado'::character varying
              WHEN (matricula.aprovado = 3) THEN 'Cursando'::character varying
              WHEN (matricula.aprovado = 4) THEN 'Transferido'::character varying
              WHEN (matricula.aprovado = 5) THEN 'Reclassificado'::character varying
              WHEN (matricula.aprovado = 6) THEN 'Abandono'::character varying
              WHEN (matricula.aprovado = 14) THEN 'Rp. Faltas'::character varying
              WHEN (matricula.aprovado = 15) THEN 'Falecido'::character varying
              ELSE 'Recl'::character varying
              END AS "case") AS texto_situacao,
        ( SELECT
              CASE
              WHEN matricula_turma.remanejado THEN 'Rem'::character varying
              WHEN matricula_turma.transferido THEN 'Trs'::character varying
              WHEN matricula_turma.reclassificado THEN 'Recl'::character varying
              WHEN matricula_turma.abandono THEN 'Aba'::character varying
              WHEN (matricula.aprovado = 1) THEN 'Apr'::character varying
              WHEN (matricula.aprovado = 12) THEN 'ApDp'::character varying
              WHEN (matricula.aprovado = 13) THEN 'ApCo'::character varying
              WHEN (matricula.aprovado = 2) THEN 'Rep'::character varying
              WHEN (matricula.aprovado = 3) THEN 'Cur'::character varying
              WHEN (matricula.aprovado = 4) THEN 'Trs'::character varying
              WHEN (matricula.aprovado = 5) THEN 'Recl'::character varying
              WHEN (matricula.aprovado = 6) THEN 'Aba'::character varying
              WHEN (matricula.aprovado = 14) THEN 'RpFt'::character varying
              WHEN (matricula.aprovado = 15) THEN 'Fal'::character varying
              ELSE 'Recl'::character varying
              END AS "case") AS texto_situacao_simplificado
    FROM situacao_matricula,
        (((pmieducar.matricula
            JOIN pmieducar.escola ON ((escola.cod_escola = matricula.ref_ref_cod_escola)))
            JOIN pmieducar.instituicao ON ((instituicao.cod_instituicao = escola.ref_cod_instituicao)))
            LEFT JOIN pmieducar.matricula_turma ON ((matricula_turma.ref_cod_matricula = matricula.cod_matricula)))
    WHERE ((matricula.ativo = 1) AND
           CASE
           WHEN (instituicao.data_base_remanejamento IS NULL) THEN (COALESCE(matricula_turma.remanejado, false) = false)
           ELSE true
           END AND
           CASE
           WHEN (matricula.aprovado = 4) THEN ((matricula_turma.ativo = 1) OR matricula_turma.transferido OR matricula_turma.reclassificado OR matricula_turma.remanejado OR (matricula_turma.sequencial = ( SELECT max(mt.sequencial) AS max
                                                                                                                                                                                                             FROM pmieducar.matricula_turma mt
                                                                                                                                                                                                             WHERE (mt.ref_cod_matricula = matricula.cod_matricula))))
           WHEN (matricula.aprovado = 6) THEN ((matricula_turma.ativo = 1) OR matricula_turma.abandono)
           WHEN (matricula.aprovado = 15) THEN ((matricula_turma.ativo = 1) OR matricula_turma.falecido)
           WHEN (matricula.aprovado = 5) THEN ((matricula_turma.ativo = 1) OR matricula_turma.reclassificado)
           ELSE ((matricula_turma.ativo = 1) OR matricula_turma.transferido OR matricula_turma.reclassificado OR matricula_turma.abandono OR matricula_turma.remanejado OR (matricula_turma.falecido AND (matricula_turma.sequencial < ( SELECT max(mt.sequencial) AS max
                                                                                                                                                                                                                                         FROM pmieducar.matricula_turma mt
                                                                                                                                                                                                                                         WHERE (mt.ref_cod_matricula = matricula.cod_matricula)))))
           END AND
           CASE
           WHEN (situacao_matricula.cod_situacao = 10) THEN (matricula.aprovado = ANY (ARRAY[1, 2, 3, 4, 5, 6, 12, 13, 14, 15]))
           WHEN (situacao_matricula.cod_situacao = 9) THEN ((matricula.aprovado = ANY (ARRAY[1, 2, 3, 5, 12, 13, 14])) AND ((NOT matricula_turma.reclassificado) OR (matricula_turma.reclassificado IS NULL)) AND ((NOT matricula_turma.abandono) OR (matricula_turma.abandono IS NULL)) AND ((NOT matricula_turma.remanejado) OR (matricula_turma.remanejado IS NULL)) AND ((NOT matricula_turma.transferido) OR (matricula_turma.transferido IS NULL)) AND ((NOT matricula_turma.falecido) OR (matricula_turma.falecido IS NULL)))
           WHEN (situacao_matricula.cod_situacao = 2) THEN ((matricula.aprovado = ANY (ARRAY[2, 14])) AND ((NOT matricula_turma.reclassificado) OR (matricula_turma.reclassificado IS NULL)) AND ((NOT matricula_turma.abandono) OR (matricula_turma.abandono IS NULL)) AND ((NOT matricula_turma.remanejado) OR (matricula_turma.remanejado IS NULL)) AND ((NOT matricula_turma.transferido) OR (matricula_turma.transferido IS NULL)) AND ((NOT matricula_turma.falecido) OR (matricula_turma.falecido IS NULL)))
           WHEN (situacao_matricula.cod_situacao = 1) THEN ((matricula.aprovado = ANY (ARRAY[1, 12, 13])) AND ((NOT matricula_turma.reclassificado) OR (matricula_turma.reclassificado IS NULL)) AND ((NOT matricula_turma.abandono) OR (matricula_turma.abandono IS NULL)) AND ((NOT matricula_turma.remanejado) OR (matricula_turma.remanejado IS NULL)) AND ((NOT matricula_turma.transferido) OR (matricula_turma.transferido IS NULL)) AND ((NOT matricula_turma.falecido) OR (matricula_turma.falecido IS NULL)))
           WHEN (situacao_matricula.cod_situacao = ANY (ARRAY[3, 12, 13])) THEN ((matricula.aprovado = situacao_matricula.cod_situacao) AND ((NOT matricula_turma.reclassificado) OR (matricula_turma.reclassificado IS NULL)) AND ((NOT matricula_turma.abandono) OR (matricula_turma.abandono IS NULL)) AND ((NOT matricula_turma.remanejado) OR (matricula_turma.remanejado IS NULL)) AND ((NOT matricula_turma.transferido) OR (matricula_turma.transferido IS NULL)) AND ((NOT matricula_turma.falecido) OR (matricula_turma.falecido IS NULL)))
           ELSE (matricula.aprovado = situacao_matricula.cod_situacao)
           END);


--
-- Name: get_max_sequencial_matricula(integer); Type: FUNCTION; Schema: relatorio; Owner: ieducar
--

CREATE OR REPLACE FUNCTION get_max_sequencial_matricula(integer) RETURNS integer
    LANGUAGE sql
    AS $_$
                            SELECT MAX(matricula_turma.sequencial)
                              FROM matricula_turma
                             INNER JOIN pmieducar.matricula ON (matricula.cod_matricula = matricula_turma.ref_cod_matricula)
                             INNER JOIN relatorio.view_situacao ON (view_situacao.cod_matricula = matricula.cod_matricula
                                                                AND view_situacao.cod_turma = matricula_turma.ref_cod_turma
                                                                AND view_situacao.sequencial = matricula_turma.sequencial)
                             WHERE ref_cod_matricula = $1;
                        $_$;


--
-- Name: get_media_geral_turma(integer, integer); Type: FUNCTION; Schema: relatorio; Owner: ieducar
--

CREATE OR REPLACE FUNCTION get_media_geral_turma(turma_i integer, componente_i integer) RETURNS numeric
    LANGUAGE plpgsql
    AS $$
                        BEGIN
                        RETURN (SELECT avg(nota_componente_curricular.nota)
                                    FROM modules.nota_componente_curricular,
                                        modules.nota_aluno,
                                        pmieducar.matricula m,
                                        pmieducar.matricula_turma mt
                                    WHERE nota_componente_curricular.nota_aluno_id = nota_aluno.id
                                    AND nota_componente_curricular.componente_curricular_id = componente_i
                                    AND nota_aluno.matricula_id = m.cod_matricula
                                    AND m.cod_matricula = mt.ref_cod_matricula
                                    AND mt.ativo = 1
                                    AND m.ativo = 1
                                    AND mt.ref_cod_turma = turma_i);
                        END; $$;


--
-- Name: get_media_recuperacao_semestral(integer, integer); Type: FUNCTION; Schema: relatorio; Owner: postgres
--

CREATE OR REPLACE FUNCTION get_media_recuperacao_semestral(matricula integer, componente integer) RETURNS numeric
    LANGUAGE plpgsql
    AS $$
                                              DECLARE
                                                 nota_etapa1 numeric;
                                                 nota_etapa2 numeric;
                                                 nota_etapa3 numeric;
                                                 nota_etapa4 numeric;
                                                 nota_recuperacao1 numeric;
                                                 nota_recuperacao2 numeric;
                                                 nota_exame_final numeric;
                                                 media_semestre1 numeric;
                                                 media_semestre2 numeric;

                                                 resultado numeric;
                                                 media_final numeric;

                                                 nota_aluno integer;
                                              BEGIN

                                                 nota_aluno := (SELECT id FROM modules.nota_aluno WHERE matricula_id = matricula);

                                                 nota_etapa1 := (SELECT nota FROM modules.nota_componente_curricular WHERE nota_aluno_id = nota_aluno AND componente_curricular_id = componente AND etapa = '1');
                                                 nota_etapa2 := (SELECT nota FROM modules.nota_componente_curricular WHERE nota_aluno_id = nota_aluno AND componente_curricular_id = componente AND etapa = '2');
                                                 nota_etapa3 := (SELECT nota FROM modules.nota_componente_curricular WHERE nota_aluno_id = nota_aluno AND componente_curricular_id = componente AND etapa = '3');
                                                 nota_etapa4 := (SELECT nota FROM modules.nota_componente_curricular WHERE nota_aluno_id = nota_aluno AND componente_curricular_id = componente AND etapa = '4');

                                                 nota_recuperacao1 := (SELECT nota_recuperacao_especifica::numeric FROM modules.nota_componente_curricular WHERE nota_aluno_id = nota_aluno AND componente_curricular_id = componente AND etapa = '2');
                                                 nota_recuperacao2 := (SELECT nota_recuperacao_especifica::numeric FROM modules.nota_componente_curricular WHERE nota_aluno_id = nota_aluno AND componente_curricular_id = componente AND etapa = '4');

                                                 nota_exame_final := (SELECT nota FROM modules.nota_componente_curricular WHERE nota_aluno_id = nota_aluno AND componente_curricular_id = componente AND etapa = 'Rc');
                                                 media_final := (SELECT media FROM modules.nota_componente_curricular_media WHERE nota_aluno_id = nota_aluno AND componente_curricular_id = componente);

                                                 IF nota_etapa2 > 0 THEN
                                                    media_semestre1 := (nota_etapa1 + nota_etapa2) / 2;
                                                 ELSE
                                                    media_semestre1 := nota_etapa1;
                                                 END IF;

                                                 IF nota_etapa4 > 0 THEN
                                                    media_semestre2 := (nota_etapa3 + nota_etapa4) / 2;
                                                 ELSE
                                                    media_semestre2 := nota_etapa3;
                                                 END IF;

                                                 IF nota_recuperacao1 >= media_semestre1 THEN
                                                    media_semestre1 := (media_semestre1 + nota_recuperacao1) / 2;
                                                 END IF;

                                                 IF nota_recuperacao2 >= media_semestre2 THEN
                                                    media_semestre2 := (media_semestre2 + nota_recuperacao2) / 2;
                                                 END IF;

                                                 IF nota_exame_final > 0 THEN
                                                    resultado := media_final;
                                                 ELSEIF media_semestre2 > 0 THEN
                                                    resultado := (media_semestre1 + media_semestre2) / 2;
                                                 ELSE
                                                    resultado := media_semestre1;
                                                 END IF;

                                                 RETURN trunc(resultado,1);

                                              END; $$;


--
-- Name: get_media_turma(integer, integer, integer); Type: FUNCTION; Schema: relatorio; Owner: postgres
--

CREATE OR REPLACE FUNCTION get_media_turma(turma_i integer, componente_i integer, etapa_i integer) RETURNS numeric
    LANGUAGE plpgsql
    AS $$
                      BEGIN
                      RETURN (SELECT avg(nota_componente_curricular.nota)
                                  FROM modules.nota_componente_curricular,
                                      modules.nota_aluno,
                                      pmieducar.matricula m,
                                      pmieducar.matricula_turma mt
                                  WHERE nota_componente_curricular.nota_aluno_id = nota_aluno.id
                                  AND nota_componente_curricular.componente_curricular_id = componente_i
                                  AND nota_aluno.matricula_id = m.cod_matricula
                                  AND m.cod_matricula = mt.ref_cod_matricula
                                  AND mt.ativo = 1
                                  AND m.ativo = 1
                                  AND mt.ref_cod_turma = turma_i
                                  AND nota_componente_curricular.etapa = etapa_i::varchar);
                      END; $$;


--
-- Name: get_nacionalidade(numeric); Type: FUNCTION; Schema: relatorio; Owner: ieducar
--

CREATE OR REPLACE FUNCTION get_nacionalidade(nacionalidade_id numeric) RETURNS character varying
    LANGUAGE plpgsql
    AS $$ BEGIN RETURN
                                                (SELECT CASE
                                                            WHEN nacionalidade_id = 1 THEN 'Brasileira'
                                                            WHEN nacionalidade_id = 2 THEN 'Naturalizado Brasileiro'
                                                            ELSE 'Estrangeiro'
                                                        END); END; $$;


--
-- Name: get_nome_modulo(integer); Type: FUNCTION; Schema: relatorio; Owner: postgres
--

CREATE OR REPLACE FUNCTION get_nome_modulo(integer) RETURNS character varying
    LANGUAGE sql
    AS $_$
SELECT MIN(modulo.nm_tipo)
FROM pmieducar.turma
INNER JOIN pmieducar.curso ON (curso.cod_curso = turma.ref_cod_curso)
LEFT JOIN pmieducar.ano_letivo_modulo ON (ano_letivo_modulo.ref_ano = turma.ano
                                          AND ano_letivo_modulo.ref_ref_cod_escola = turma.ref_ref_cod_escola
                                          AND curso.padrao_ano_escolar = 1)
LEFT JOIN pmieducar.turma_modulo ON (turma_modulo.ref_cod_turma = turma.cod_turma
                                     AND curso.padrao_ano_escolar = 0)
INNER JOIN pmieducar.modulo ON (CASE
                                    WHEN curso.padrao_ano_escolar = 1 THEN modulo.cod_modulo = ano_letivo_modulo.ref_cod_modulo
                                    ELSE modulo.cod_modulo = turma_modulo.ref_cod_modulo
                                END)
WHERE turma.cod_turma = $1;$_$;


--
-- Name: get_nota_exame(integer, integer); Type: FUNCTION; Schema: relatorio; Owner: ieducar
--

CREATE OR REPLACE FUNCTION get_nota_exame(integer, integer) RETURNS character varying
    LANGUAGE sql
    AS $_$
                        (SELECT CASE WHEN nota_componente_curricular.nota_arredondada = '10' THEN '10,0' WHEN char_length(nota_componente_curricular.nota_arredondada) = 1 THEN replace(nota_componente_curricular.nota_arredondada,'.',',') || ',0' ELSE replace(nota_componente_curricular.nota_arredondada,'.',',') END
                         FROM modules.nota_componente_curricular, modules.nota_aluno
                         WHERE nota_componente_curricular.componente_curricular_id = $1
                           AND nota_componente_curricular.etapa = 'Rc'
                           AND nota_aluno.id = nota_componente_curricular.nota_aluno_id
                           AND nota_aluno.matricula_id = $2); $_$;


--
-- Name: get_pai_aluno(integer); Type: FUNCTION; Schema: relatorio; Owner: postgres
--

CREATE OR REPLACE FUNCTION get_pai_aluno(integer) RETURNS character varying
    LANGUAGE sql
    AS $_$
SELECT coalesce(
                  (SELECT nome
                   FROM cadastro.pessoa
                   WHERE idpes = fisica.idpes_pai), (aluno.nm_pai))
FROM pmieducar.aluno
INNER JOIN cadastro.fisica ON fisica.idpes = aluno.ref_idpes
WHERE aluno.ativo = 1
  AND aluno.cod_aluno = $1; $_$;


--
-- Name: get_qtde_alunos(integer, integer); Type: FUNCTION; Schema: relatorio; Owner: postgres
--

CREATE OR REPLACE FUNCTION get_qtde_alunos(integer, integer) RETURNS bigint
    LANGUAGE sql
    AS $_$
SELECT COUNT(*)
FROM pmieducar.matricula
WHERE matricula.ativo = 1
  AND (CASE WHEN 0 = $1 THEN TRUE ELSE matricula.ano = $1 END)
  AND (CASE WHEN 0 = $2 THEN TRUE ELSE matricula.ref_ref_cod_escola = $2 END); $_$;


--
-- Name: get_qtde_alunos_situacao(integer, integer, character, integer, integer, integer); Type: FUNCTION; Schema: relatorio; Owner: postgres
--

CREATE OR REPLACE FUNCTION get_qtde_alunos_situacao(integer, integer, character, integer, integer, integer) RETURNS integer
    LANGUAGE sql
    AS $_$
		    SELECT COUNT(*)::integer AS situacao
		      FROM relatorio.view_situacao
        INNER JOIN pmieducar.matricula 	    ON (relatorio.view_situacao.cod_matricula = pmieducar.matricula.cod_matricula)
	    INNER JOIN pmieducar.aluno     	    ON (pmieducar.matricula.ref_cod_aluno = pmieducar.aluno.cod_aluno)
	    INNER JOIN cadastro.fisica     	    ON (pmieducar.aluno.ref_idpes = cadastro.fisica.idpes)
	    INNER JOIN cadastro.pessoa     	    ON (cadastro.fisica.idpes = cadastro.pessoa.idpes)
	     LEFT JOIN cadastro.endereco_pessoa ON (cadastro.endereco_pessoa.idpes = cadastro.pessoa.idpes)
	     LEFT JOIN public.bairro	        ON (public.bairro.idbai = cadastro.endereco_pessoa.idbai)
             WHERE (CASE WHEN $3 = 'M' THEN cadastro.fisica.sexo = 'M'
		                 WHEN $3 = 'F' THEN cadastro.fisica.sexo = 'F'
		                 WHEN $3 = 'A' THEN cadastro.fisica.sexo IN ('M', 'F')
	                END) AND
	                ((($4 > 0
			           AND (SELECT substring(age(CURRENT_DATE, fisica.data_nasc)::char,1,2)
				              FROM cadastro.pessoa,
				                   cadastro.fisica
				             WHERE aluno.ref_idpes = fisica.idpes AND
				                   fisica.idpes = pessoa.idpes)::integer >= $4) AND
				            ($5 > 0 AND
				                (SELECT substring(age(CURRENT_DATE, fisica.data_nasc)::char,1,2)
					               FROM cadastro.pessoa,
					                    cadastro.fisica
					              WHERE aluno.ref_idpes = fisica.idpes AND
					                    fisica.idpes = pessoa.idpes)::integer <= $5)) OR
					              ($4 = 0)  AND
					              ($5 = 0))
		       AND (CASE WHEN $6 = 0 THEN TRUE ELSE public.bairro.idbai = $6 END)
		       AND relatorio.view_situacao.cod_turma = $1 AND
		           relatorio.view_situacao.cod_situacao = $2;
	$_$;


--
-- Name: get_qtde_alunos_situacao(integer, integer, integer, integer, integer, integer, integer, integer, character, integer, integer); Type: FUNCTION; Schema: relatorio; Owner: postgres
--

CREATE OR REPLACE FUNCTION get_qtde_alunos_situacao(ano integer, instituicao integer, escola integer, curso integer, serie integer, turma integer, situacao integer, bairro integer, sexo character, idadeini integer, idadefim integer) RETURNS integer
    LANGUAGE plpgsql
    AS $$

DECLARE

BEGIN

	RETURN (SELECT COUNT(*) AS qtde_situacao
	  FROM pmieducar.instituicao
    INNER JOIN pmieducar.escola            ON (escola.ref_cod_instituicao = instituicao.cod_instituicao)
    INNER JOIN pmieducar.escola_ano_letivo ON (escola_ano_letivo.ref_cod_escola = escola.cod_escola)
    INNER JOIN pmieducar.matricula         ON (matricula.ref_ref_cod_escola = escola.cod_escola)
    INNER JOIN pmieducar.aluno             ON (matricula.ref_cod_aluno = aluno.cod_aluno)
    INNER JOIN pmieducar.matricula_turma   ON (matricula.cod_matricula = matricula_turma.ref_cod_matricula)
    INNER JOIN pmieducar.turma             ON (turma.cod_turma = matricula_turma.ref_cod_turma)
    INNER JOIN pmieducar.serie             ON (turma.ref_ref_cod_serie = serie.cod_serie)
    INNER JOIN pmieducar.curso             ON (serie.ref_cod_curso = curso.cod_curso)
    INNER JOIN cadastro.pessoa             ON (pessoa.idpes = aluno.ref_idpes)
    INNER JOIN cadastro.fisica             ON (fisica.idpes = pessoa.idpes)
    LEFT  JOIN cadastro.endereco_pessoa    ON (endereco_pessoa.idpes = pessoa.idpes)
    LEFT  JOIN public.bairro               ON (endereco_pessoa.idbai = bairro.idbai)
    LEFT  JOIN public.logradouro           ON (logradouro.idlog = endereco_pessoa.idlog)
    LEFT  JOIN cadastro.fone_pessoa        ON (fone_pessoa.idpes = pessoa.idpes
                                    AND fone_pessoa.tipo =
                                      (SELECT COALESCE(MIN(fone_pessoa_aux.tipo),1)
                                       FROM cadastro.fone_pessoa AS fone_pessoa_aux
                                       WHERE fone_pessoa_aux.fone <> 0
                                         AND fone_pessoa_aux.idpes = pessoa.idpes))
    LEFT  JOIN cadastro.documento        ON (documento.idpes = pessoa.idpes)
    LEFT  JOIN cadastro.orgao_emissor_rg ON (orgao_emissor_rg.idorg_rg = documento.idorg_exp_rg)
    INNER JOIN relatorio.view_situacao   ON (view_situacao.cod_matricula = matricula.cod_matricula
                                       AND view_situacao.cod_turma = turma.cod_turma
                                       AND view_situacao.cod_situacao = situacao
                                       AND matricula_turma.sequencial = view_situacao.sequencial)
     LEFT JOIN cadastro.pessoa pessoa_mae             ON (pessoa_mae.idpes = fisica.idpes_mae)
     LEFT JOIN cadastro.juridica                      ON (juridica.idpes = escola.ref_idpes)
     LEFT JOIN endereco_pessoa endereco_pessoa_escola ON (endereco_pessoa_escola.idpes = escola.ref_idpes)
     LEFT JOIN public.bairro bairro_escola            ON (endereco_pessoa.idbai = bairro_escola.idbai)
     LEFT JOIN public.logradouro logradouro_escola    ON (logradouro_escola.idlog = endereco_pessoa.idlog)
     LEFT JOIN public.municipio                       ON (municipio.idmun = bairro_escola.idmun)
     LEFT JOIN cadastro.pessoa pessoa_escola          ON (pessoa_escola.idpes = escola.ref_idpes)
         WHERE aluno.ativo = 1       			 AND
               matricula.ativo = 1   			 AND
               turma.ativo = 1       			 AND
               serie.ativo = 1       			 AND
               curso.ativo = 1       			 AND
               instituicao.ativo = 1                     AND
               escola.ativo = 1                          AND
               matricula.ano = escola_ano_letivo.ano     AND
               instituicao.cod_instituicao = instituicao AND
               escola.cod_escola = escola 		 AND
               turma.ano = ano 				 AND
     (SELECT CASE WHEN curso = 0 THEN TRUE ELSE curso.cod_curso = curso END)
 AND (SELECT CASE WHEN serie = 0 THEN TRUE ELSE serie.cod_serie = serie END)
 AND (SELECT CASE WHEN turma = 0 THEN TRUE ELSE turma.cod_turma = turma END)
 AND (SELECT CASE WHEN bairro = 0 THEN TRUE ELSE bairro.idbai = bairro END)
 AND (SELECT CASE WHEN sexo = 'A' THEN TRUE ELSE (CASE WHEN sexo = 'M' THEN fisica.sexo = 'M' ELSE fisica.sexo = 'F' END) END)
 AND (((idadeIni > 0
         AND
           (SELECT substring(age(CURRENT_DATE, fisica.data_nasc),1,2)
            FROM cadastro.pessoa,
                 cadastro.fisica
            WHERE aluno.ref_idpes = fisica.idpes
              AND fisica.idpes = pessoa.idpes)::integer >= idadeIni)
        AND (idadeFim > 0
             AND
               (SELECT substring(age(CURRENT_DATE, fisica.data_nasc),1,2)
                FROM cadastro.pessoa,
                     cadastro.fisica
                WHERE aluno.ref_idpes = fisica.idpes
                  AND fisica.idpes = pessoa.idpes)::integer <= idadeFim))
       OR (idadeIni = 0)
       AND (idadeFim = 0)));

END;

$$;


--
-- Name: get_qtde_etapa_disciplina_dispensada_matricula(integer, integer); Type: FUNCTION; Schema: relatorio; Owner: ieducar
--

CREATE OR REPLACE FUNCTION get_qtde_etapa_disciplina_dispensada_matricula(integer, integer) RETURNS integer
    LANGUAGE sql
    AS $_$
                            SELECT DISTINCT count(dispensa_etapa.etapa)::integer AS qtde_dispensa_etapa
                              FROM pmieducar.dispensa_disciplina
                             INNER JOIN pmieducar.dispensa_etapa ON (dispensa_etapa.ref_cod_dispensa = dispensa_disciplina.cod_dispensa)
                             WHERE dispensa_disciplina.ativo =1
                               AND ref_cod_matricula = $1
                               AND ref_cod_disciplina = $2;
                        $_$;


--
-- Name: get_qtde_modulo(integer); Type: FUNCTION; Schema: relatorio; Owner: postgres
--

CREATE OR REPLACE FUNCTION get_qtde_modulo(integer) RETURNS integer
    LANGUAGE sql
    AS $_$
SELECT COUNT(modulo.nm_tipo)::integer AS qtde
FROM pmieducar.turma
INNER JOIN pmieducar.curso ON (curso.cod_curso = turma.ref_cod_curso)
LEFT JOIN pmieducar.ano_letivo_modulo ON (ano_letivo_modulo.ref_ano = turma.ano
                                          AND ano_letivo_modulo.ref_ref_cod_escola = turma.ref_ref_cod_escola
                                          AND curso.padrao_ano_escolar = 1)
LEFT JOIN pmieducar.turma_modulo ON (turma_modulo.ref_cod_turma = turma.cod_turma
                                     AND curso.padrao_ano_escolar = 0)
INNER JOIN pmieducar.modulo ON (CASE
                                    WHEN curso.padrao_ano_escolar = 1 THEN modulo.cod_modulo = ano_letivo_modulo.ref_cod_modulo
                                    ELSE modulo.cod_modulo = turma_modulo.ref_cod_modulo
                                END)
WHERE turma.cod_turma = $1;$_$;


--
-- Name: get_situacao_componente(numeric); Type: FUNCTION; Schema: relatorio; Owner: ieducar
--

CREATE OR REPLACE FUNCTION get_situacao_componente(cod_situacao numeric) RETURNS character varying
    LANGUAGE plpgsql
    AS $$
                DECLARE
                  texto_situacao varchar := '';
                BEGIN
                  texto_situacao := (CASE
                    WHEN cod_situacao = 1 THEN 'Aprovado'
                    WHEN cod_situacao = 2 THEN 'Retido'
                    WHEN cod_situacao = 3 THEN 'Cursando'
                    WHEN cod_situacao = 4 THEN 'Transferido'
                    WHEN cod_situacao = 5 THEN 'Reclassificado'
                    WHEN cod_situacao = 6 THEN 'Abandono'
                    WHEN cod_situacao = 7 THEN 'Em exame'
                    WHEN cod_situacao = 8 THEN 'Aprovado após exame'
                    WHEN cod_situacao = 9 THEN 'Retido por falta'
                    WHEN cod_situacao = 10 THEN 'Aprovado sem exame'
                    WHEN cod_situacao = 11 THEN 'Pré-matrícula'
                    WHEN cod_situacao = 12 THEN 'Aprovado com dependência'
                    WHEN cod_situacao = 13 THEN 'Aprovado pelo conselho'
                    ELSE '' END);
                  RETURN texto_situacao;
                END;
                $$;


--
-- Name: get_situacao_historico(integer); Type: FUNCTION; Schema: relatorio; Owner: ieducar
--

CREATE OR REPLACE FUNCTION get_situacao_historico(situacao integer) RETURNS character varying
    LANGUAGE sql
    AS $$
                        SELECT CASE
                                WHEN situacao = 1 THEN 'Aprovado'::character varying
                                WHEN situacao = 2 THEN 'Reprovado'::character varying
                                WHEN situacao = 3 THEN 'Cursando'::character varying
                                WHEN situacao = 4 THEN 'Transferido'::character varying
                                WHEN situacao = 5 THEN 'Reclassificado'::character varying
                                WHEN situacao = 6 THEN 'Abandono'::character varying
                                WHEN situacao = 12 THEN 'Ap. Depen.'::character varying
                                WHEN situacao = 13 THEN 'Aprovado conselho'::character varying
                                WHEN situacao = 14 THEN 'Reprovado por faltas'::character varying
                                ELSE ''::character varying
                            END AS situacao; $$;


--
-- Name: get_situacao_historico_abreviado(integer); Type: FUNCTION; Schema: relatorio; Owner: postgres
--

CREATE OR REPLACE FUNCTION get_situacao_historico_abreviado(integer) RETURNS character varying
    LANGUAGE sql
    AS $_$
SELECT CASE
           WHEN $1 = 1 THEN 'Apr'::character varying
           WHEN $1 = 2 THEN 'Rep'::character varying
           WHEN $1 = 3 THEN 'Cur'::character varying
           WHEN $1 = 4 THEN 'Trs'::character varying
           WHEN $1 = 5 THEN 'Recl'::character varying
           WHEN $1 = 6 THEN 'Aba'::character varying
           WHEN $1 = 12 THEN 'ApDp'::character varying
           WHEN $1 = 13 THEN 'ApCo'::character varying
           WHEN $1 = 14 THEN 'RpFt'::character varying
           ELSE ''::character varying
       END AS situacao; $_$;


--
-- Name: get_telefone_escola(integer); Type: FUNCTION; Schema: relatorio; Owner: postgres
--

CREATE OR REPLACE FUNCTION get_telefone_escola(integer) RETURNS character varying
    LANGUAGE sql
    AS $_$
SELECT COALESCE(
                  (SELECT min(to_char(fone_pessoa.fone, '99999-9999'))
                   FROM cadastro.fone_pessoa, cadastro.juridica
                   WHERE juridica.idpes = fone_pessoa.idpes
                     AND juridica.idpes =
                       (SELECT idpes
                        FROM cadastro.pessoa
                        INNER JOIN pmieducar.escola ON escola.ref_idpes = pessoa.idpes
                        WHERE cod_escola = $1)),
                  (SELECT min(to_char(telefone, '99999-9999'))
                   FROM pmieducar.escola_complemento
                   WHERE escola_complemento.ref_cod_escola = $1)); $_$;


--
-- Name: get_total_falta_componente(integer, integer); Type: FUNCTION; Schema: relatorio; Owner: ieducar
--

CREATE OR REPLACE FUNCTION get_total_falta_componente(matricula_i integer, componente_i integer) RETURNS numeric
    LANGUAGE plpgsql
    AS $$
                        BEGIN
                        RETURN (SELECT sum(falta_componente_curricular.quantidade)
                                  FROM modules.falta_componente_curricular,
                                       modules.falta_aluno
                                 WHERE falta_componente_curricular.falta_aluno_id = falta_aluno.id AND
                                       falta_aluno.matricula_id = matricula_i AND
                                       falta_componente_curricular.etapa in ('1','2','3','4') AND
                                       falta_componente_curricular.componente_curricular_id = componente_i AND
                                       falta_aluno.tipo_falta = 2);
                        END; $$;


--
-- Name: get_total_faltas(integer); Type: FUNCTION; Schema: relatorio; Owner: ieducar
--

CREATE OR REPLACE FUNCTION get_total_faltas(matricula_i integer) RETURNS numeric
    LANGUAGE plpgsql
    AS $$
                        BEGIN
                        RETURN (SELECT sum(falta_geral.quantidade)
                                FROM modules.falta_geral,
                                        modules.falta_aluno
                                WHERE falta_geral.falta_aluno_id = falta_aluno.id AND
                                        falta_aluno.matricula_id = matricula_i AND
                                        falta_aluno.tipo_falta = 1 AND
                                        falta_geral.etapa in ('1','2','3','4'));
                        END; $$;


--
-- Name: get_total_geral_falta_componente(integer); Type: FUNCTION; Schema: relatorio; Owner: ieducar
--

CREATE OR REPLACE FUNCTION get_total_geral_falta_componente(matricula_i integer) RETURNS numeric
    LANGUAGE plpgsql
    AS $$
                        BEGIN
                        RETURN (SELECT sum(falta_componente_curricular.quantidade)
                                FROM modules.falta_componente_curricular,
                                        modules.falta_aluno
                                WHERE falta_componente_curricular.falta_aluno_id = falta_aluno.id AND
                                        falta_aluno.matricula_id = matricula_i AND
                                        falta_componente_curricular.etapa in ('1','2','3','4') AND
                                falta_aluno.tipo_falta = 2);
                        END; $$;


--
-- Name: get_ultima_matricula_turma(integer, integer, integer); Type: FUNCTION; Schema: relatorio; Owner: ieducar
--

CREATE OR REPLACE FUNCTION get_ultima_matricula_turma(integer, integer, integer) RETURNS boolean
    LANGUAGE plpgsql
    AS $_$
                            DECLARE
                                max_matricula integer;
                                cod_aluno integer;
                            BEGIN
                                cod_aluno := (SELECT ref_cod_aluno FROM pmieducar.matricula WHERE cod_matricula = $1);
                            max_matricula := (SELECT max(matricula.cod_matricula)
                                                FROM pmieducar.matricula
                                               INNER JOIN pmieducar.matricula_turma ON (matricula_turma.ref_cod_matricula = matricula.cod_matricula)
                                               WHERE ref_cod_aluno = cod_aluno
                                                 AND ref_cod_turma = $2
                                                 AND matricula.aprovado = $3);
                            IF max_matricula = $1 THEN
                                RETURN true;
                            END IF;
                            RETURN FALSE;
                            END;
                        $_$;


--
-- Name: get_ultima_observacao_historico(integer); Type: FUNCTION; Schema: relatorio; Owner: postgres
--

CREATE OR REPLACE FUNCTION get_ultima_observacao_historico(integer) RETURNS character varying
    LANGUAGE sql
    AS $_$
SELECT (replace(textcat_all(observacao),'<br>',E'\n'))
FROM pmieducar.historico_escolar she
WHERE she.ativo = 1
  AND she.ref_cod_aluno = $1
  AND she.sequencial =
    (SELECT max(s_he.sequencial)
     FROM pmieducar.historico_escolar s_he
     WHERE s_he.ref_cod_instituicao = she.ref_cod_instituicao
       AND substring(s_he.nm_serie,1,1) = substring(she.nm_serie,1,1)
       AND substring(s_he.nm_curso,1,1) = substring(she.nm_curso,1,1)
       AND s_he.ref_cod_aluno = she.ref_cod_aluno
       AND s_he.ativo = 1); $_$;


--
-- Name: get_valor_campo_auditoria(character varying, character varying, character varying); Type: FUNCTION; Schema: relatorio; Owner: postgres
--

CREATE OR REPLACE FUNCTION get_valor_campo_auditoria(character varying, character varying, character varying) RETURNS character varying
    LANGUAGE sql
    AS $_$
SELECT CASE
           WHEN $2 = '' THEN substr($3, strpos($3, $1||':')+char_length($1)+1, ((strpos($3, '}')) - (strpos($3, $1)+char_length($1)+1)))
           ELSE substr($3, strpos($3, $1||':')+char_length($1)+1, ((strpos($3, $2||':')-1) - (strpos($3, $1)+char_length($1)+1)))
       END AS nome_instituicao;$_$;


--
-- Name: historico_carga_horaria_componente(character varying, character varying, integer); Type: FUNCTION; Schema: relatorio; Owner: ieducar
--

CREATE OR REPLACE FUNCTION historico_carga_horaria_componente(nome_componente character varying, nome_serie character varying, escola_id integer) RETURNS numeric
    LANGUAGE plpgsql
    AS $$ BEGIN RETURN
                        (SELECT to_number(ccae.carga_horaria::varchar,'999')
                         FROM modules.componente_curricular_ano_escolar ccae
                         INNER JOIN modules.componente_curricular cc ON (fcn_upper(relatorio.get_texto_sem_caracter_especial(cc.nome)) = fcn_upper(relatorio.get_texto_sem_caracter_especial(nome_componente))
                                                                         AND cc.id = ccae.componente_curricular_id)
                         INNER JOIN pmieducar.serie s ON (fcn_upper(relatorio.get_texto_sem_caracter_especial(s.nm_serie)) = fcn_upper(relatorio.get_texto_sem_caracter_especial(nome_serie))
                                                          AND s.cod_serie = ccae.ano_escolar_id)
                         LEFT JOIN pmieducar.escola_serie es ON (es.ref_cod_escola = escola_id
                                                                 AND es.ref_cod_serie = s.cod_serie)
                         ORDER BY ref_cod_escola LIMIT 1); END; $$;


--
-- Name: prioridade_historico(numeric); Type: FUNCTION; Schema: relatorio; Owner: ieducar
--

CREATE OR REPLACE FUNCTION prioridade_historico(situacao numeric) RETURNS numeric
    LANGUAGE plpgsql
    AS $$
                        DECLARE
                            prioridade NUMERIC := 0;
                        BEGIN
                            prioridade := (CASE
                                WHEN situacao = 1  THEN 1
                                WHEN situacao = 12 THEN 1
                                WHEN situacao = 13 THEN 1
                                WHEN situacao = 2  THEN 2
                                WHEN situacao = 14 THEN 2
                                WHEN situacao = 3  THEN 3
                                WHEN situacao = 4  THEN 4
                                WHEN situacao = 6  THEN 4
                                ELSE 5 END);
                            RETURN prioridade;
                        END;
                        $$;

--
-- Name: exibe_aluno_conforme_parametro_alunos_diferenciados(integer, integer); Type: FUNCTION; Schema: relatorio; Owner: ieducar
--

CREATE OR REPLACE FUNCTION relatorio.exibe_aluno_conforme_parametro_alunos_diferenciados(
    codigo_aluno integer,
    alunos_diferenciados integer)
  RETURNS boolean AS
$BODY$
    DECLARE
    possui_deficiencia boolean;
    BEGIN

    possui_deficiencia := EXISTS
(SELECT 1
       FROM cadastro.fisica_deficiencia fd
       JOIN pmieducar.aluno a ON fd.ref_idpes = a.ref_idpes
       JOIN cadastro.deficiencia d ON d.cod_deficiencia = fd.ref_cod_deficiencia
      WHERE a.cod_aluno = codigo_aluno
AND d.desconsidera_regra_diferenciada = false
      LIMIT 1
    );

    CASE alunos_diferenciados
        WHEN 1 THEN RETURN possui_deficiencia = false;
        WHEN 2 THEN RETURN possui_deficiencia = true;
        ELSE RETURN true;
    END CASE;

    END; $BODY$
LANGUAGE plpgsql VOLATILE
COST 100;

--
-- Name: view_auditoria; Type: VIEW; Schema: relatorio; Owner: postgres
--

CREATE OR REPLACE VIEW view_auditoria AS
 SELECT (substr((auditoria.usuario)::text, 0, strpos((auditoria.usuario)::text, '-'::text)))::integer AS usuario_id,
    substr((auditoria.usuario)::text, (strpos((auditoria.usuario)::text, '-'::text) + 2)) AS usuario_matricula,
    auditoria.operacao,
    auditoria.rotina,
    auditoria.data_hora,
        CASE
            WHEN (auditoria.operacao = 1) THEN get_valor_campo_auditoria('instituicao'::character varying, 'instituicao_id'::character varying, (auditoria.valor_novo)::character varying)
            ELSE get_valor_campo_auditoria('instituicao'::character varying, 'instituicao_id'::character varying, (auditoria.valor_antigo)::character varying)
        END AS instituicao,
    (
        CASE
            WHEN (auditoria.operacao = 1) THEN get_valor_campo_auditoria('instituicao_id'::character varying, 'escola'::character varying, (auditoria.valor_novo)::character varying)
            ELSE get_valor_campo_auditoria('instituicao_id'::character varying, 'escola'::character varying, (auditoria.valor_antigo)::character varying)
        END)::integer AS instituicao_id,
        CASE
            WHEN (auditoria.operacao = 1) THEN get_valor_campo_auditoria('escola'::character varying, 'escola_id'::character varying, (auditoria.valor_novo)::character varying)
            ELSE get_valor_campo_auditoria('escola'::character varying, 'escola_id'::character varying, (auditoria.valor_antigo)::character varying)
        END AS escola,
    (
        CASE
            WHEN (auditoria.operacao = 1) THEN get_valor_campo_auditoria('escola_id'::character varying, 'curso'::character varying, (auditoria.valor_novo)::character varying)
            ELSE get_valor_campo_auditoria('escola_id'::character varying, 'curso'::character varying, (auditoria.valor_antigo)::character varying)
        END)::integer AS escola_id,
        CASE
            WHEN (auditoria.operacao = 1) THEN get_valor_campo_auditoria('curso'::character varying, 'curso_id'::character varying, (auditoria.valor_novo)::character varying)
            ELSE get_valor_campo_auditoria('curso'::character varying, 'curso_id'::character varying, (auditoria.valor_antigo)::character varying)
        END AS curso,
    (
        CASE
            WHEN (auditoria.operacao = 1) THEN get_valor_campo_auditoria('curso_id'::character varying, 'serie'::character varying, (auditoria.valor_novo)::character varying)
            ELSE get_valor_campo_auditoria('curso_id'::character varying, 'serie'::character varying, (auditoria.valor_antigo)::character varying)
        END)::integer AS curso_id,
        CASE
            WHEN (auditoria.operacao = 1) THEN get_valor_campo_auditoria('serie'::character varying, 'serie_id'::character varying, (auditoria.valor_novo)::character varying)
            ELSE get_valor_campo_auditoria('serie'::character varying, 'serie_id'::character varying, (auditoria.valor_antigo)::character varying)
        END AS serie,
    (
        CASE
            WHEN (auditoria.operacao = 1) THEN get_valor_campo_auditoria('serie_id'::character varying, 'turma'::character varying, (auditoria.valor_novo)::character varying)
            ELSE get_valor_campo_auditoria('serie_id'::character varying, 'turma'::character varying, (auditoria.valor_antigo)::character varying)
        END)::integer AS serie_id,
        CASE
            WHEN (auditoria.operacao = 1) THEN get_valor_campo_auditoria('turma'::character varying, 'turma_id'::character varying, (auditoria.valor_novo)::character varying)
            ELSE get_valor_campo_auditoria('turma'::character varying, 'turma_id'::character varying, (auditoria.valor_antigo)::character varying)
        END AS turma,
    (
        CASE
            WHEN (auditoria.operacao = 1) THEN get_valor_campo_auditoria('turma_id'::character varying, 'aluno'::character varying, (auditoria.valor_novo)::character varying)
            ELSE get_valor_campo_auditoria('turma_id'::character varying, 'aluno'::character varying, (auditoria.valor_antigo)::character varying)
        END)::integer AS turma_id,
        CASE
            WHEN (auditoria.operacao = 1) THEN get_valor_campo_auditoria('aluno'::character varying, 'aluno_id'::character varying, (auditoria.valor_novo)::character varying)
            ELSE get_valor_campo_auditoria('aluno'::character varying, 'aluno_id'::character varying, (auditoria.valor_antigo)::character varying)
        END AS aluno,
    (
        CASE
            WHEN (auditoria.operacao = 1) THEN get_valor_campo_auditoria('aluno_id'::character varying, 'nota'::character varying, (auditoria.valor_novo)::character varying)
            ELSE get_valor_campo_auditoria('aluno_id'::character varying, 'nota'::character varying, (auditoria.valor_antigo)::character varying)
        END)::integer AS aluno_id,
        CASE
            WHEN (auditoria.operacao = 1) THEN get_valor_campo_auditoria('etapa'::character varying, 'componenteCurricular'::character varying, (auditoria.valor_novo)::character varying)
            ELSE get_valor_campo_auditoria('etapa'::character varying, 'componenteCurricular'::character varying, (auditoria.valor_antigo)::character varying)
        END AS etapa,
        CASE
            WHEN (auditoria.operacao = 1) THEN get_valor_campo_auditoria('componenteCurricular'::character varying, ''::character varying, (auditoria.valor_novo)::character varying)
            ELSE get_valor_campo_auditoria('componenteCurricular'::character varying, ''::character varying, (auditoria.valor_antigo)::character varying)
        END AS componente_curricular,
    get_valor_campo_auditoria('nota'::character varying, 'etapa'::character varying, (auditoria.valor_antigo)::character varying) AS nota_antiga,
    get_valor_campo_auditoria('nota'::character varying, 'etapa'::character varying, (auditoria.valor_novo)::character varying) AS nota_nova
   FROM modules.auditoria;



--
-- Name: view_dados_historico_posicionamento; Type: VIEW; Schema: relatorio; Owner: ieducar
--

CREATE OR REPLACE VIEW view_dados_historico_posicionamento AS
    SELECT he.ref_cod_aluno,
        public.fcn_upper(hd.nm_disciplina) AS nm_disciplina,
        ( SELECT COALESCE(min(hdd2.ordenamento), 9999) AS "coalesce"
          FROM (pmieducar.historico_escolar hee2
              JOIN pmieducar.historico_disciplinas hdd2 ON (((hdd2.ref_ref_cod_aluno = hee2.ref_cod_aluno) AND (hdd2.ref_sequencial = hee2.sequencial))))
          WHERE ((hee2.ref_cod_aluno = he.ref_cod_aluno) AND (public.fcn_upper((hdd2.nm_disciplina)::text) = public.fcn_upper(hd.nm_disciplina)))) AS order_componente,
        he1.historico_grade_curso_id AS grade_curso1,
        he2.historico_grade_curso_id AS grade_curso2,
        he3.historico_grade_curso_id AS grade_curso3,
        he4.historico_grade_curso_id AS grade_curso4,
        he5.historico_grade_curso_id AS grade_curso5,
        he6.historico_grade_curso_id AS grade_curso6,
        he7.historico_grade_curso_id AS grade_curso7,
        he8.historico_grade_curso_id AS grade_curso8,
        he9.historico_grade_curso_id AS grade_curso9,
        he1.ano AS ano1,
        he2.ano AS ano2,
        he3.ano AS ano3,
        he4.ano AS ano4,
        he5.ano AS ano5,
        he6.ano AS ano6,
        he7.ano AS ano7,
        he8.ano AS ano8,
        he9.ano AS ano9,
        he1.escola AS escola1,
        he2.escola AS escola2,
        he3.escola AS escola3,
        he4.escola AS escola4,
        he5.escola AS escola5,
        he6.escola AS escola6,
        he7.escola AS escola7,
        he8.escola AS escola8,
        he9.escola AS escola9,
        he1.escola_cidade AS escola_cidade1,
        he2.escola_cidade AS escola_cidade2,
        he3.escola_cidade AS escola_cidade3,
        he4.escola_cidade AS escola_cidade4,
        he5.escola_cidade AS escola_cidade5,
        he6.escola_cidade AS escola_cidade6,
        he7.escola_cidade AS escola_cidade7,
        he8.escola_cidade AS escola_cidade8,
        he9.escola_cidade AS escola_cidade9,
        he1.escola_uf AS escola_uf1,
        he2.escola_uf AS escola_uf2,
        he3.escola_uf AS escola_uf3,
        he4.escola_uf AS escola_uf4,
        he5.escola_uf AS escola_uf5,
        he6.escola_uf AS escola_uf6,
        he7.escola_uf AS escola_uf7,
        he8.escola_uf AS escola_uf8,
        he9.escola_uf AS escola_uf9,
        he1.nm_serie AS nm_serie1,
        he2.nm_serie AS nm_serie2,
        he3.nm_serie AS nm_serie3,
        he4.nm_serie AS nm_serie4,
        he5.nm_serie AS nm_serie5,
        he6.nm_serie AS nm_serie6,
        he7.nm_serie AS nm_serie7,
        he8.nm_serie AS nm_serie8,
        he9.nm_serie AS nm_serie9,
        he1.carga_horaria AS ch1,
        he2.carga_horaria AS ch2,
        he3.carga_horaria AS ch3,
        he4.carga_horaria AS ch4,
        he5.carga_horaria AS ch5,
        he6.carga_horaria AS ch6,
        he7.carga_horaria AS ch7,
        he8.carga_horaria AS ch8,
        he9.carga_horaria AS ch9,
        he1.frequencia AS freq1,
        he2.frequencia AS freq2,
        he3.frequencia AS freq3,
        he4.frequencia AS freq4,
        he5.frequencia AS freq5,
        he6.frequencia AS freq6,
        he7.frequencia AS freq7,
        he8.frequencia AS freq8,
        he9.frequencia AS freq9,
        he1.observacao AS obs1,
        he2.observacao AS obs2,
        he3.observacao AS obs3,
        he4.observacao AS obs4,
        he5.observacao AS obs5,
        he6.observacao AS obs6,
        he7.observacao AS obs7,
        he8.observacao AS obs8,
        he9.observacao AS obs9,
        hd1.nota AS nota1,
        hd2.nota AS nota2,
        hd3.nota AS nota3,
        hd4.nota AS nota4,
        hd5.nota AS nota5,
        hd6.nota AS nota6,
        hd7.nota AS nota7,
        hd8.nota AS nota8,
        hd9.nota AS nota9,
        hd1.carga_horaria_disciplina AS chd1,
        hd2.carga_horaria_disciplina AS chd2,
        hd3.carga_horaria_disciplina AS chd3,
        hd4.carga_horaria_disciplina AS chd4,
        hd5.carga_horaria_disciplina AS chd5,
        hd6.carga_horaria_disciplina AS chd6,
        hd7.carga_horaria_disciplina AS chd7,
        hd8.carga_horaria_disciplina AS chd8,
        hd9.carga_horaria_disciplina AS chd9,
        hd1.faltas AS faltas1,
        hd2.faltas AS faltas2,
        hd3.faltas AS faltas3,
        hd4.faltas AS faltas4,
        hd5.faltas AS faltas5,
        hd6.faltas AS faltas6,
        hd7.faltas AS faltas7,
        hd8.faltas AS faltas8,
        hd9.faltas AS faltas9,
        CASE
        WHEN (he1.aceleracao = 1) THEN (
            CASE
            WHEN (he1.aprovado = 1) THEN 'Apro'::text
            WHEN (he1.aprovado = 12) THEN 'AprDep'::text
            WHEN (he1.aprovado = 13) THEN 'AprCo'::text
            WHEN (he1.aprovado = 2) THEN 'Repr'::text
            WHEN (he1.aprovado = 3) THEN 'Curs'::text
            WHEN (he1.aprovado = 4) THEN 'Tran'::text
            WHEN (he1.aprovado = 5) THEN 'Recl'::text
            WHEN (he1.aprovado = 6) THEN 'Aban'::text
            WHEN (he1.aprovado = 14) THEN 'RpFt'::text
            WHEN (he1.aprovado = 15) THEN 'Fal'::text
            ELSE ''::text
            END || ' AC'::text)
        ELSE
            CASE
            WHEN (he1.aprovado = 1) THEN 'Apro'::text
            WHEN (he1.aprovado = 12) THEN 'AprDep'::text
            WHEN (he1.aprovado = 13) THEN 'AprCo'::text
            WHEN (he1.aprovado = 2) THEN 'Repr'::text
            WHEN (he1.aprovado = 3) THEN 'Curs'::text
            WHEN (he1.aprovado = 4) THEN 'Tran'::text
            WHEN (he1.aprovado = 5) THEN 'Recl'::text
            WHEN (he1.aprovado = 6) THEN 'Aban'::text
            WHEN (he1.aprovado = 14) THEN 'RpFt'::text
            WHEN (he1.aprovado = 15) THEN 'Fal'::text
            ELSE ''::text
            END
        END AS status1,
        CASE
        WHEN (he2.aceleracao = 1) THEN (
            CASE
            WHEN (he2.aprovado = 1) THEN 'Apro'::text
            WHEN (he2.aprovado = 12) THEN 'AprDep'::text
            WHEN (he2.aprovado = 13) THEN 'AprCo'::text
            WHEN (he2.aprovado = 2) THEN 'Repr'::text
            WHEN (he2.aprovado = 3) THEN 'Curs'::text
            WHEN (he2.aprovado = 4) THEN 'Tran'::text
            WHEN (he2.aprovado = 5) THEN 'Recl'::text
            WHEN (he2.aprovado = 6) THEN 'Aban'::text
            WHEN (he2.aprovado = 14) THEN 'RpFt'::text
            WHEN (he2.aprovado = 15) THEN 'Fal'::text
            ELSE ''::text
            END || ' AC'::text)
        ELSE
            CASE
            WHEN (he2.aprovado = 1) THEN 'Apro'::text
            WHEN (he2.aprovado = 12) THEN 'AprDep'::text
            WHEN (he2.aprovado = 13) THEN 'AprCo'::text
            WHEN (he2.aprovado = 2) THEN 'Repr'::text
            WHEN (he2.aprovado = 3) THEN 'Curs'::text
            WHEN (he2.aprovado = 4) THEN 'Tran'::text
            WHEN (he2.aprovado = 5) THEN 'Recl'::text
            WHEN (he2.aprovado = 6) THEN 'Aban'::text
            WHEN (he2.aprovado = 14) THEN 'RpFt'::text
            WHEN (he2.aprovado = 15) THEN 'Fal'::text
            ELSE ''::text
            END
        END AS status2,
        CASE
        WHEN (he3.aceleracao = 1) THEN (
            CASE
            WHEN (he3.aprovado = 1) THEN 'Apro'::text
            WHEN (he3.aprovado = 12) THEN 'AprDep'::text
            WHEN (he3.aprovado = 13) THEN 'AprCo'::text
            WHEN (he3.aprovado = 2) THEN 'Repr'::text
            WHEN (he3.aprovado = 3) THEN 'Curs'::text
            WHEN (he3.aprovado = 4) THEN 'Tran'::text
            WHEN (he3.aprovado = 5) THEN 'Recl'::text
            WHEN (he3.aprovado = 6) THEN 'Aban'::text
            WHEN (he3.aprovado = 14) THEN 'RpFt'::text
            WHEN (he3.aprovado = 15) THEN 'Fal'::text
            ELSE ''::text
            END || ' AC'::text)
        ELSE
            CASE
            WHEN (he3.aprovado = 1) THEN 'Apro'::text
            WHEN (he3.aprovado = 12) THEN 'AprDep'::text
            WHEN (he3.aprovado = 13) THEN 'AprCo'::text
            WHEN (he3.aprovado = 2) THEN 'Repr'::text
            WHEN (he3.aprovado = 3) THEN 'Curs'::text
            WHEN (he3.aprovado = 4) THEN 'Tran'::text
            WHEN (he3.aprovado = 5) THEN 'Recl'::text
            WHEN (he3.aprovado = 6) THEN 'Aban'::text
            WHEN (he3.aprovado = 14) THEN 'RpFt'::text
            WHEN (he3.aprovado = 15) THEN 'Fal'::text
            ELSE ''::text
            END
        END AS status3,
        CASE
        WHEN (he4.aceleracao = 1) THEN (
            CASE
            WHEN (he4.aprovado = 1) THEN 'Apro'::text
            WHEN (he4.aprovado = 12) THEN 'AprDep'::text
            WHEN (he4.aprovado = 13) THEN 'AprCo'::text
            WHEN (he4.aprovado = 2) THEN 'Repr'::text
            WHEN (he4.aprovado = 3) THEN 'Curs'::text
            WHEN (he4.aprovado = 4) THEN 'Tran'::text
            WHEN (he4.aprovado = 5) THEN 'Recl'::text
            WHEN (he4.aprovado = 6) THEN 'Aban'::text
            WHEN (he4.aprovado = 14) THEN 'RpFt'::text
            WHEN (he4.aprovado = 15) THEN 'Fal'::text
            ELSE ''::text
            END || ' AC'::text)
        ELSE
            CASE
            WHEN (he4.aprovado = 1) THEN 'Apro'::text
            WHEN (he4.aprovado = 12) THEN 'AprDep'::text
            WHEN (he4.aprovado = 13) THEN 'AprCo'::text
            WHEN (he4.aprovado = 2) THEN 'Repr'::text
            WHEN (he4.aprovado = 3) THEN 'Curs'::text
            WHEN (he4.aprovado = 4) THEN 'Tran'::text
            WHEN (he4.aprovado = 5) THEN 'Recl'::text
            WHEN (he4.aprovado = 6) THEN 'Aban'::text
            WHEN (he4.aprovado = 14) THEN 'RpFt'::text
            WHEN (he4.aprovado = 15) THEN 'Fal'::text
            ELSE ''::text
            END
        END AS status4,
        CASE
        WHEN (he5.aceleracao = 1) THEN (
            CASE
            WHEN (he5.aprovado = 1) THEN 'Apro'::text
            WHEN (he5.aprovado = 12) THEN 'AprDep'::text
            WHEN (he5.aprovado = 13) THEN 'AprCo'::text
            WHEN (he5.aprovado = 2) THEN 'Repr'::text
            WHEN (he5.aprovado = 3) THEN 'Curs'::text
            WHEN (he5.aprovado = 4) THEN 'Tran'::text
            WHEN (he5.aprovado = 5) THEN 'Recl'::text
            WHEN (he5.aprovado = 6) THEN 'Aban'::text
            WHEN (he5.aprovado = 14) THEN 'RpFt'::text
            WHEN (he5.aprovado = 15) THEN 'Fal'::text
            ELSE ''::text
            END || ' AC'::text)
        ELSE
            CASE
            WHEN (he5.aprovado = 1) THEN 'Apro'::text
            WHEN (he5.aprovado = 12) THEN 'AprDep'::text
            WHEN (he5.aprovado = 13) THEN 'AprCo'::text
            WHEN (he5.aprovado = 2) THEN 'Repr'::text
            WHEN (he5.aprovado = 3) THEN 'Curs'::text
            WHEN (he5.aprovado = 4) THEN 'Tran'::text
            WHEN (he5.aprovado = 5) THEN 'Recl'::text
            WHEN (he5.aprovado = 6) THEN 'Aban'::text
            WHEN (he5.aprovado = 14) THEN 'RpFt'::text
            WHEN (he5.aprovado = 15) THEN 'Fal'::text
            ELSE ''::text
            END
        END AS status5,
        CASE
        WHEN (he6.aceleracao = 1) THEN (
            CASE
            WHEN (he6.aprovado = 1) THEN 'Apro'::text
            WHEN (he6.aprovado = 12) THEN 'AprDep'::text
            WHEN (he6.aprovado = 13) THEN 'AprCo'::text
            WHEN (he6.aprovado = 2) THEN 'Repr'::text
            WHEN (he6.aprovado = 3) THEN 'Curs'::text
            WHEN (he6.aprovado = 4) THEN 'Tran'::text
            WHEN (he6.aprovado = 5) THEN 'Recl'::text
            WHEN (he6.aprovado = 6) THEN 'Aban'::text
            WHEN (he6.aprovado = 14) THEN 'RpFt'::text
            WHEN (he6.aprovado = 15) THEN 'Fal'::text
            ELSE ''::text
            END || ' AC'::text)
        ELSE
            CASE
            WHEN (he6.aprovado = 1) THEN 'Apro'::text
            WHEN (he6.aprovado = 12) THEN 'AprDep'::text
            WHEN (he6.aprovado = 13) THEN 'AprCo'::text
            WHEN (he6.aprovado = 2) THEN 'Repr'::text
            WHEN (he6.aprovado = 3) THEN 'Curs'::text
            WHEN (he6.aprovado = 4) THEN 'Tran'::text
            WHEN (he6.aprovado = 5) THEN 'Recl'::text
            WHEN (he6.aprovado = 6) THEN 'Aban'::text
            WHEN (he6.aprovado = 14) THEN 'RpFt'::text
            WHEN (he6.aprovado = 15) THEN 'Fal'::text
            ELSE ''::text
            END
        END AS status6,
        CASE
        WHEN (he7.aceleracao = 1) THEN (
            CASE
            WHEN (he7.aprovado = 1) THEN 'Apro'::text
            WHEN (he7.aprovado = 12) THEN 'AprDep'::text
            WHEN (he7.aprovado = 13) THEN 'AprCo'::text
            WHEN (he7.aprovado = 2) THEN 'Repr'::text
            WHEN (he7.aprovado = 3) THEN 'Curs'::text
            WHEN (he7.aprovado = 4) THEN 'Tran'::text
            WHEN (he7.aprovado = 5) THEN 'Recl'::text
            WHEN (he7.aprovado = 6) THEN 'Aban'::text
            WHEN (he7.aprovado = 14) THEN 'RpFt'::text
            WHEN (he7.aprovado = 15) THEN 'Fal'::text
            ELSE ''::text
            END || ' AC'::text)
        ELSE
            CASE
            WHEN (he7.aprovado = 1) THEN 'Apro'::text
            WHEN (he7.aprovado = 12) THEN 'AprDep'::text
            WHEN (he7.aprovado = 13) THEN 'AprCo'::text
            WHEN (he7.aprovado = 2) THEN 'Repr'::text
            WHEN (he7.aprovado = 3) THEN 'Curs'::text
            WHEN (he7.aprovado = 4) THEN 'Tran'::text
            WHEN (he7.aprovado = 5) THEN 'Recl'::text
            WHEN (he7.aprovado = 6) THEN 'Aban'::text
            WHEN (he7.aprovado = 14) THEN 'RpFt'::text
            WHEN (he7.aprovado = 15) THEN 'Fal'::text
            ELSE ''::text
            END
        END AS status7,
        CASE
        WHEN (he8.aceleracao = 1) THEN (
            CASE
            WHEN (he8.aprovado = 1) THEN 'Apro'::text
            WHEN (he8.aprovado = 12) THEN 'AprDep'::text
            WHEN (he8.aprovado = 13) THEN 'AprCo'::text
            WHEN (he8.aprovado = 2) THEN 'Repr'::text
            WHEN (he8.aprovado = 3) THEN 'Curs'::text
            WHEN (he8.aprovado = 4) THEN 'Tran'::text
            WHEN (he8.aprovado = 5) THEN 'Recl'::text
            WHEN (he8.aprovado = 6) THEN 'Aban'::text
            WHEN (he8.aprovado = 14) THEN 'RpFt'::text
            WHEN (he8.aprovado = 15) THEN 'Fal'::text
            ELSE ''::text
            END || ' AC'::text)
        ELSE
            CASE
            WHEN (he8.aprovado = 1) THEN 'Apro'::text
            WHEN (he8.aprovado = 12) THEN 'AprDep'::text
            WHEN (he8.aprovado = 13) THEN 'AprCo'::text
            WHEN (he8.aprovado = 2) THEN 'Repr'::text
            WHEN (he8.aprovado = 3) THEN 'Curs'::text
            WHEN (he8.aprovado = 4) THEN 'Tran'::text
            WHEN (he8.aprovado = 5) THEN 'Recl'::text
            WHEN (he8.aprovado = 6) THEN 'Aban'::text
            WHEN (he8.aprovado = 14) THEN 'RpFt'::text
            WHEN (he8.aprovado = 15) THEN 'Fal'::text
            ELSE ''::text
            END
        END AS status8,
        CASE
        WHEN (he9.aceleracao = 1) THEN (
            CASE
            WHEN (he9.aprovado = 1) THEN 'Apro'::text
            WHEN (he9.aprovado = 12) THEN 'AprDep'::text
            WHEN (he9.aprovado = 13) THEN 'AprCo'::text
            WHEN (he9.aprovado = 2) THEN 'Repr'::text
            WHEN (he9.aprovado = 3) THEN 'Curs'::text
            WHEN (he9.aprovado = 4) THEN 'Tran'::text
            WHEN (he9.aprovado = 5) THEN 'Recl'::text
            WHEN (he9.aprovado = 6) THEN 'Aban'::text
            WHEN (he9.aprovado = 14) THEN 'RpFt'::text
            WHEN (he9.aprovado = 15) THEN 'Fal'::text
            ELSE ''::text
            END || ' AC'::text)
        ELSE
            CASE
            WHEN (he9.aprovado = 1) THEN 'Apro'::text
            WHEN (he9.aprovado = 12) THEN 'AprDep'::text
            WHEN (he9.aprovado = 13) THEN 'AprCo'::text
            WHEN (he9.aprovado = 2) THEN 'Repr'::text
            WHEN (he9.aprovado = 3) THEN 'Curs'::text
            WHEN (he9.aprovado = 4) THEN 'Tran'::text
            WHEN (he9.aprovado = 5) THEN 'Recl'::text
            WHEN (he9.aprovado = 6) THEN 'Aban'::text
            WHEN (he9.aprovado = 14) THEN 'RpFt'::text
            WHEN (he9.aprovado = 15) THEN 'Fal'::text
            ELSE ''::text
            END
        END AS status9
    FROM (((((((((((((((((((( SELECT hd_1.sequencial,
                                  hd_1.ref_ref_cod_aluno,
                                  hd_1.ref_sequencial,
                                  public.fcn_upper((hd_1.nm_disciplina)::text) AS nm_disciplina,
                                  hd_1.nota,
                                  hd_1.faltas,
                                  hd_1.ordenamento
                              FROM pmieducar.historico_disciplinas hd_1) hd
        JOIN pmieducar.historico_escolar he ON (((hd.ref_ref_cod_aluno = he.ref_cod_aluno) AND (hd.ref_sequencial = he.sequencial))))
        LEFT JOIN pmieducar.historico_escolar he1 ON (((he1.ref_cod_aluno = he.ref_cod_aluno) AND (he1.posicao = 1) AND (he1.ativo = 1) AND (he1.sequencial = ( SELECT hee.sequencial
                                                                                                                                                                FROM pmieducar.historico_escolar hee
                                                                                                                                                                WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND (hee.posicao = 1) AND (hee.ativo = 1))
                                                                                                                                                                ORDER BY hee.ano DESC, hee.aprovado
                                                                                                                                                                LIMIT 1)))))
        LEFT JOIN pmieducar.historico_escolar he2 ON (((he2.ref_cod_aluno = he.ref_cod_aluno) AND (he2.posicao = 2) AND (he2.ativo = 1) AND (he2.sequencial = ( SELECT hee.sequencial
                                                                                                                                                                FROM pmieducar.historico_escolar hee
                                                                                                                                                                WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND (hee.posicao = 2) AND (hee.ativo = 1))
                                                                                                                                                                ORDER BY hee.ano DESC, hee.aprovado
                                                                                                                                                                LIMIT 1)))))
        LEFT JOIN pmieducar.historico_escolar he3 ON (((he3.ref_cod_aluno = he.ref_cod_aluno) AND (he3.posicao = 3) AND (he3.ativo = 1) AND (he3.sequencial = ( SELECT hee.sequencial
                                                                                                                                                                FROM pmieducar.historico_escolar hee
                                                                                                                                                                WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND (hee.posicao = 3) AND (hee.ativo = 1))
                                                                                                                                                                ORDER BY hee.ano DESC, hee.aprovado
                                                                                                                                                                LIMIT 1)))))
        LEFT JOIN pmieducar.historico_escolar he4 ON (((he4.ref_cod_aluno = he.ref_cod_aluno) AND (he4.posicao = 4) AND (he4.ativo = 1) AND (he4.sequencial = ( SELECT hee.sequencial
                                                                                                                                                                FROM pmieducar.historico_escolar hee
                                                                                                                                                                WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND (hee.posicao = 4) AND (hee.ativo = 1))
                                                                                                                                                                ORDER BY hee.ano DESC, hee.aprovado
                                                                                                                                                                LIMIT 1)))))
        LEFT JOIN pmieducar.historico_escolar he5 ON (((he5.ref_cod_aluno = he.ref_cod_aluno) AND (he5.posicao = 5) AND (he5.ativo = 1) AND (he5.sequencial = ( SELECT hee.sequencial
                                                                                                                                                                FROM pmieducar.historico_escolar hee
                                                                                                                                                                WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND (hee.posicao = 5) AND (hee.ativo = 1))
                                                                                                                                                                ORDER BY hee.ano DESC, hee.aprovado
                                                                                                                                                                LIMIT 1)))))
        LEFT JOIN pmieducar.historico_escolar he6 ON (((he6.ref_cod_aluno = he.ref_cod_aluno) AND (he6.posicao = 6) AND (he6.ativo = 1) AND (he6.sequencial = ( SELECT hee.sequencial
                                                                                                                                                                FROM pmieducar.historico_escolar hee
                                                                                                                                                                WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND (hee.posicao = 6) AND (hee.ativo = 1))
                                                                                                                                                                ORDER BY hee.ano DESC, hee.aprovado
                                                                                                                                                                LIMIT 1)))))
        LEFT JOIN pmieducar.historico_escolar he7 ON (((he7.ref_cod_aluno = he.ref_cod_aluno) AND (he7.posicao = 7) AND (he7.ativo = 1) AND (he7.sequencial = ( SELECT hee.sequencial
                                                                                                                                                                FROM pmieducar.historico_escolar hee
                                                                                                                                                                WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND (hee.posicao = 7) AND (hee.ativo = 1))
                                                                                                                                                                ORDER BY hee.ano DESC, hee.aprovado
                                                                                                                                                                LIMIT 1)))))
        LEFT JOIN pmieducar.historico_escolar he8 ON (((he8.ref_cod_aluno = he.ref_cod_aluno) AND (he8.posicao = 8) AND (he8.ativo = 1) AND (he8.sequencial = ( SELECT hee.sequencial
                                                                                                                                                                FROM pmieducar.historico_escolar hee
                                                                                                                                                                WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND (hee.posicao = 8) AND (hee.ativo = 1))
                                                                                                                                                                ORDER BY hee.ano DESC, hee.aprovado
                                                                                                                                                                LIMIT 1)))))
        LEFT JOIN pmieducar.historico_escolar he9 ON (((he9.ref_cod_aluno = he.ref_cod_aluno) AND (he9.posicao = 9) AND (he9.ativo = 1) AND (he9.sequencial = ( SELECT hee.sequencial
                                                                                                                                                                FROM pmieducar.historico_escolar hee
                                                                                                                                                                WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND (hee.posicao = 9) AND (hee.ativo = 1))
                                                                                                                                                                ORDER BY hee.ano DESC, hee.aprovado
                                                                                                                                                                LIMIT 1)))))
        LEFT JOIN pmieducar.historico_disciplinas hd1 ON (((hd1.ref_ref_cod_aluno = hd.ref_ref_cod_aluno) AND (public.fcn_upper((hd1.nm_disciplina)::text) = public.fcn_upper(hd.nm_disciplina)) AND (hd1.ref_sequencial = he1.sequencial))))
        LEFT JOIN pmieducar.historico_disciplinas hd2 ON (((hd2.ref_ref_cod_aluno = hd.ref_ref_cod_aluno) AND (public.fcn_upper((hd2.nm_disciplina)::text) = public.fcn_upper(hd.nm_disciplina)) AND (hd2.ref_sequencial = he2.sequencial))))
        LEFT JOIN pmieducar.historico_disciplinas hd3 ON (((hd3.ref_ref_cod_aluno = hd.ref_ref_cod_aluno) AND (public.fcn_upper((hd3.nm_disciplina)::text) = public.fcn_upper(hd.nm_disciplina)) AND (hd3.ref_sequencial = he3.sequencial))))
        LEFT JOIN pmieducar.historico_disciplinas hd4 ON (((hd4.ref_ref_cod_aluno = hd.ref_ref_cod_aluno) AND (public.fcn_upper((hd4.nm_disciplina)::text) = public.fcn_upper(hd.nm_disciplina)) AND (hd4.ref_sequencial = he4.sequencial))))
        LEFT JOIN pmieducar.historico_disciplinas hd5 ON (((hd5.ref_ref_cod_aluno = hd.ref_ref_cod_aluno) AND (public.fcn_upper((hd5.nm_disciplina)::text) = public.fcn_upper(hd.nm_disciplina)) AND (hd5.ref_sequencial = he5.sequencial))))
        LEFT JOIN pmieducar.historico_disciplinas hd6 ON (((hd6.ref_ref_cod_aluno = hd.ref_ref_cod_aluno) AND (public.fcn_upper((hd6.nm_disciplina)::text) = public.fcn_upper(hd.nm_disciplina)) AND (hd6.ref_sequencial = he6.sequencial))))
        LEFT JOIN pmieducar.historico_disciplinas hd7 ON (((hd7.ref_ref_cod_aluno = hd.ref_ref_cod_aluno) AND (public.fcn_upper((hd7.nm_disciplina)::text) = public.fcn_upper(hd.nm_disciplina)) AND (hd7.ref_sequencial = he7.sequencial))))
        LEFT JOIN pmieducar.historico_disciplinas hd8 ON (((hd8.ref_ref_cod_aluno = hd.ref_ref_cod_aluno) AND (public.fcn_upper((hd8.nm_disciplina)::text) = public.fcn_upper(hd.nm_disciplina)) AND (hd8.ref_sequencial = he8.sequencial))))
        LEFT JOIN pmieducar.historico_disciplinas hd9 ON (((hd9.ref_ref_cod_aluno = hd.ref_ref_cod_aluno) AND (public.fcn_upper((hd9.nm_disciplina)::text) = public.fcn_upper(hd.nm_disciplina)) AND (hd9.ref_sequencial = he9.sequencial))))
    WHERE ((he.ativo = 1) AND (he.posicao IS NOT NULL))
    GROUP BY he.ref_cod_aluno, hd.nm_disciplina, he1.historico_grade_curso_id, he2.historico_grade_curso_id, he3.historico_grade_curso_id, he4.historico_grade_curso_id, he5.historico_grade_curso_id, he6.historico_grade_curso_id, he7.historico_grade_curso_id, he8.historico_grade_curso_id, he9.historico_grade_curso_id, he1.ano, he2.ano, he3.ano, he4.ano, he5.ano, he6.ano, he7.ano, he8.ano, he9.ano, he1.escola, he2.escola, he3.escola, he4.escola, he5.escola, he6.escola, he7.escola, he8.escola, he9.escola, he1.escola_cidade, he2.escola_cidade, he3.escola_cidade, he4.escola_cidade, he5.escola_cidade, he6.escola_cidade, he7.escola_cidade, he8.escola_cidade, he9.escola_cidade, he1.escola_uf, he2.escola_uf, he3.escola_uf, he4.escola_uf, he5.escola_uf, he6.escola_uf, he7.escola_uf, he8.escola_uf, he9.escola_uf, he1.nm_serie, he2.nm_serie, he3.nm_serie, he4.nm_serie, he5.nm_serie, he6.nm_serie, he7.nm_serie, he8.nm_serie, he9.nm_serie, he1.carga_horaria, he2.carga_horaria, he3.carga_horaria, he4.carga_horaria, he5.carga_horaria, he6.carga_horaria, he7.carga_horaria, he8.carga_horaria, he9.carga_horaria, he1.frequencia, he2.frequencia, he3.frequencia, he4.frequencia, he5.frequencia, he6.frequencia, he7.frequencia, he8.frequencia, he9.frequencia, he1.observacao, he2.observacao, he3.observacao, he4.observacao, he5.observacao, he6.observacao, he7.observacao, he8.observacao, he9.observacao, hd1.nota, hd2.nota, hd3.nota, hd4.nota, hd5.nota, hd6.nota, hd7.nota, hd8.nota, hd9.nota, hd1.carga_horaria_disciplina, hd2.carga_horaria_disciplina, hd3.carga_horaria_disciplina, hd4.carga_horaria_disciplina, hd5.carga_horaria_disciplina, hd6.carga_horaria_disciplina, hd7.carga_horaria_disciplina, hd8.carga_horaria_disciplina, hd9.carga_horaria_disciplina, hd1.faltas, hd2.faltas, hd3.faltas, hd4.faltas, hd5.faltas, hd6.faltas, hd7.faltas, hd8.faltas, hd9.faltas, he1.aceleracao, he2.aceleracao, he3.aceleracao, he4.aceleracao, he5.aceleracao, he6.aceleracao, he7.aceleracao, he8.aceleracao, he9.aceleracao, he1.aprovado, he2.aprovado, he3.aprovado, he4.aprovado, he5.aprovado, he6.aprovado, he7.aprovado, he8.aprovado, he9.aprovado;


--
-- Name: view_dados_modulo; Type: VIEW; Schema: relatorio; Owner: ieducar
--

CREATE OR REPLACE VIEW view_dados_modulo AS
    ( SELECT turma.cod_turma,
          alm.sequencial,
          alm.ref_cod_modulo,
          alm.data_inicio,
          alm.data_fim,
          COALESCE(alm.dias_letivos, (0)::numeric) AS dias_letivos
      FROM (((((((pmieducar.instituicao
          JOIN pmieducar.escola ON ((escola.ref_cod_instituicao = instituicao.cod_instituicao)))
          JOIN pmieducar.escola_curso ON (((escola_curso.ativo = 1) AND (escola_curso.ref_cod_escola = escola.cod_escola))))
          JOIN pmieducar.curso ON (((curso.cod_curso = escola_curso.ref_cod_curso) AND (curso.ativo = 1))))
          JOIN pmieducar.escola_serie ON (((escola_serie.ativo = 1) AND (escola_serie.ref_cod_escola = escola.cod_escola))))
          JOIN pmieducar.serie ON (((serie.cod_serie = escola_serie.ref_cod_serie) AND (serie.ativo = 1))))
          JOIN pmieducar.turma ON (((turma.ref_ref_cod_escola = escola.cod_escola) AND (turma.ref_cod_curso = escola_curso.ref_cod_curso) AND (turma.ref_ref_cod_serie = escola_serie.ref_cod_serie) AND (turma.ativo = 1))))
          JOIN pmieducar.ano_letivo_modulo alm ON (((alm.ref_ano = turma.ano) AND (alm.ref_ref_cod_escola = escola.cod_escola))))
      WHERE (curso.padrao_ano_escolar = 1)
      ORDER BY turma.nm_turma, turma.cod_turma, alm.sequencial)
    UNION ALL
    ( SELECT turma.cod_turma,
          tm.sequencial,
          tm.ref_cod_modulo,
          tm.data_inicio,
          tm.data_fim,
          COALESCE(tm.dias_letivos, 0) AS dias_letivos
      FROM (((((((pmieducar.instituicao
          JOIN pmieducar.escola ON ((escola.ref_cod_instituicao = instituicao.cod_instituicao)))
          JOIN pmieducar.escola_curso ON (((escola_curso.ativo = 1) AND (escola_curso.ref_cod_escola = escola.cod_escola))))
          JOIN pmieducar.curso ON (((curso.cod_curso = escola_curso.ref_cod_curso) AND (curso.ativo = 1))))
          JOIN pmieducar.escola_serie ON (((escola_serie.ativo = 1) AND (escola_serie.ref_cod_escola = escola.cod_escola))))
          JOIN pmieducar.serie ON (((serie.cod_serie = escola_serie.ref_cod_serie) AND (serie.ativo = 1))))
          JOIN pmieducar.turma ON (((turma.ref_ref_cod_escola = escola.cod_escola) AND (turma.ref_cod_curso = escola_curso.ref_cod_curso) AND (turma.ref_ref_cod_serie = escola_serie.ref_cod_serie) AND (turma.ativo = 1))))
          JOIN pmieducar.turma_modulo tm ON ((tm.ref_cod_turma = turma.cod_turma)))
      WHERE (curso.padrao_ano_escolar = 0)
      ORDER BY turma.nm_turma, turma.cod_turma, tm.sequencial);


--
-- Name: view_historico_9anos; Type: VIEW; Schema: relatorio; Owner: ieducar
--

CREATE OR REPLACE VIEW view_historico_9anos AS
    SELECT historico.cod_aluno,
        historico.disciplina,
        historico.nota_1serie,
        historico.nota_2serie,
        historico.nota_3serie,
        historico.nota_4serie,
        historico.nota_5serie,
        historico.nota_6serie,
        historico.nota_7serie,
        historico.nota_8serie,
        historico.nota_9serie,
        historico.ano_1serie,
        historico.escola_1serie,
        historico.escola_cidade_1serie,
        historico.escola_uf_1serie,
        historico.ano_2serie,
        historico.escola_2serie,
        historico.escola_cidade_2serie,
        historico.escola_uf_2serie,
        historico.ano_3serie,
        historico.escola_3serie,
        historico.escola_cidade_3serie,
        historico.escola_uf_3serie,
        historico.ano_4serie,
        historico.escola_4serie,
        historico.escola_cidade_4serie,
        historico.escola_uf_4serie,
        historico.ano_5serie,
        historico.escola_5serie,
        historico.escola_cidade_5serie,
        historico.escola_uf_5serie,
        historico.ano_6serie,
        historico.escola_6serie,
        historico.escola_cidade_6serie,
        historico.escola_uf_6serie,
        historico.ano_7serie,
        historico.escola_7serie,
        historico.escola_cidade_7serie,
        historico.escola_uf_7serie,
        historico.ano_8serie,
        historico.escola_8serie,
        historico.escola_cidade_8serie,
        historico.escola_uf_8serie,
        historico.ano_9serie,
        historico.escola_9serie,
        historico.escola_cidade_9serie,
        historico.escola_uf_9serie,
        historico.transferido1,
        historico.transferido2,
        historico.transferido3,
        historico.transferido4,
        historico.transferido5,
        historico.transferido6,
        historico.transferido7,
        historico.transferido8,
        historico.transferido9,
        historico.carga_horaria1,
        historico.carga_horaria2,
        historico.carga_horaria3,
        historico.carga_horaria4,
        historico.carga_horaria5,
        historico.carga_horaria6,
        historico.carga_horaria7,
        historico.carga_horaria8,
        historico.carga_horaria9,
        historico.observacao_all,
        historico.matricula_transferido,
        historico.carga_horaria_disciplina1,
        historico.carga_horaria_disciplina2,
        historico.carga_horaria_disciplina3,
        historico.carga_horaria_disciplina4,
        historico.carga_horaria_disciplina5,
        historico.carga_horaria_disciplina6,
        historico.carga_horaria_disciplina7,
        historico.carga_horaria_disciplina8,
        historico.carga_horaria_disciplina9,
        historico.disciplina_dependencia1,
        historico.disciplina_dependencia2,
        historico.disciplina_dependencia3,
        historico.disciplina_dependencia4,
        historico.disciplina_dependencia5,
        historico.disciplina_dependencia6,
        historico.disciplina_dependencia7,
        historico.disciplina_dependencia8,
        historico.disciplina_dependencia9,
        historico.ch_componente_1,
        historico.ch_componente_2,
        historico.ch_componente_3,
        historico.ch_componente_4,
        historico.ch_componente_5,
        historico.ch_componente_6,
        historico.ch_componente_7,
        historico.ch_componente_8,
        historico.ch_componente_9,
        historico.frequencia1,
        historico.frequencia2,
        historico.frequencia3,
        historico.frequencia4,
        historico.frequencia5,
        historico.frequencia6,
        historico.frequencia7,
        historico.frequencia8,
        historico.frequencia9,
        max(historico.status_serie1) AS status_serie1,
        max(historico.status_serie2) AS status_serie2,
        max(historico.status_serie3) AS status_serie3,
        max(historico.status_serie4) AS status_serie4,
        max(historico.status_serie5) AS status_serie5,
        max(historico.status_serie6) AS status_serie6,
        max(historico.status_serie7) AS status_serie7,
        max(historico.status_serie8) AS status_serie8,
        max(historico.status_serie9) AS status_serie9
    FROM ( SELECT historico_disciplinas.ref_ref_cod_aluno AS cod_aluno,
                  get_texto_sem_caracter_especial((upper(btrim(historico_disciplinas.nm_disciplina)))::character varying) AS disciplina,
                  ( SELECT
                        CASE
                        WHEN (("substring"(btrim((hd.nota)::text), 1, 1) <> (0)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (1)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (2)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (3)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (4)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (5)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (6)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (7)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (8)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (9)::text)) THEN replace((hd.nota)::text, '.'::text, ','::text)
                        WHEN ((to_number(btrim((hd.nota)::text), '999'::text) > (10)::numeric) AND (to_number(btrim((hd.nota)::text), '999'::text) <= (20)::numeric)) THEN replace(btrim((hd.nota)::text), '.'::text, ','::text)
                        ELSE replace("substring"(btrim((hd.nota)::text), 1, 4), '.'::text, ','::text)
                        END AS replace
                    FROM (pmieducar.historico_disciplinas hd
                        JOIN pmieducar.historico_escolar historico_escolar_1 ON (((historico_escolar_1.ref_cod_aluno = hd.ref_ref_cod_aluno) AND (historico_escolar_1.sequencial = hd.ref_sequencial))))
                    WHERE ((hd.ref_ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND ((get_texto_sem_caracter_especial((upper(btrim((hd.nm_disciplina)::text)))::character varying))::text = (get_texto_sem_caracter_especial((upper(btrim(historico_disciplinas.nm_disciplina)))::character varying))::text) AND (historico_escolar_1.ativo = 1) AND (historico_escolar_1.extra_curricular = 0) AND ("substring"((historico_escolar_1.nm_serie)::text, 1, 1) = (1)::text) AND (historico_escolar_1.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              FROM pmieducar.historico_escolar hee
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              WHERE ((hee.ref_cod_aluno = historico_escolar_1.ref_cod_aluno) AND ("substring"((historico_escolar_1.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0) AND (COALESCE(hee.dependencia, false) = false)))))
                    LIMIT 1) AS nota_1serie,
                  ( SELECT
                        CASE
                        WHEN (("substring"(btrim((hd.nota)::text), 1, 1) <> (0)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (1)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (2)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (3)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (4)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (5)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (6)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (7)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (8)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (9)::text)) THEN replace((hd.nota)::text, '.'::text, ','::text)
                        WHEN ((to_number(btrim((hd.nota)::text), '999'::text) > (10)::numeric) AND (to_number(btrim((hd.nota)::text), '999'::text) <= (20)::numeric)) THEN replace(btrim((hd.nota)::text), '.'::text, ','::text)
                        ELSE replace("substring"(btrim((hd.nota)::text), 1, 4), '.'::text, ','::text)
                        END AS replace
                    FROM (pmieducar.historico_disciplinas hd
                        JOIN pmieducar.historico_escolar historico_escolar_1 ON (((historico_escolar_1.ref_cod_aluno = hd.ref_ref_cod_aluno) AND (historico_escolar_1.sequencial = hd.ref_sequencial))))
                    WHERE ((hd.ref_ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND ((get_texto_sem_caracter_especial((upper(btrim((hd.nm_disciplina)::text)))::character varying))::text = (get_texto_sem_caracter_especial((upper(btrim(historico_disciplinas.nm_disciplina)))::character varying))::text) AND (historico_escolar_1.ativo = 1) AND (historico_escolar_1.extra_curricular = 0) AND ("substring"((historico_escolar_1.nm_serie)::text, 1, 1) = (2)::text) AND (historico_escolar_1.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              FROM pmieducar.historico_escolar hee
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              WHERE ((hee.ref_cod_aluno = historico_escolar_1.ref_cod_aluno) AND ("substring"((historico_escolar_1.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0) AND (COALESCE(hee.dependencia, false) = false)))))
                    LIMIT 1) AS nota_2serie,
                  ( SELECT
                        CASE
                        WHEN (("substring"(btrim((hd.nota)::text), 1, 1) <> (0)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (1)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (2)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (3)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (4)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (5)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (6)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (7)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (8)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (9)::text)) THEN replace((hd.nota)::text, '.'::text, ','::text)
                        WHEN ((to_number(btrim((hd.nota)::text), '999'::text) > (10)::numeric) AND (to_number(btrim((hd.nota)::text), '999'::text) <= (20)::numeric)) THEN replace(btrim((hd.nota)::text), '.'::text, ','::text)
                        ELSE replace("substring"(btrim((hd.nota)::text), 1, 4), '.'::text, ','::text)
                        END AS replace
                    FROM (pmieducar.historico_disciplinas hd
                        JOIN pmieducar.historico_escolar historico_escolar_1 ON (((historico_escolar_1.ref_cod_aluno = hd.ref_ref_cod_aluno) AND (historico_escolar_1.sequencial = hd.ref_sequencial))))
                    WHERE ((hd.ref_ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND ((get_texto_sem_caracter_especial((upper(btrim((hd.nm_disciplina)::text)))::character varying))::text = (get_texto_sem_caracter_especial((upper(btrim(historico_disciplinas.nm_disciplina)))::character varying))::text) AND (historico_escolar_1.ativo = 1) AND (historico_escolar_1.extra_curricular = 0) AND ("substring"((historico_escolar_1.nm_serie)::text, 1, 1) = (3)::text) AND (historico_escolar_1.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              FROM pmieducar.historico_escolar hee
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              WHERE ((hee.ref_cod_aluno = historico_escolar_1.ref_cod_aluno) AND ("substring"((historico_escolar_1.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0) AND (COALESCE(hee.dependencia, false) = false)))))
                    LIMIT 1) AS nota_3serie,
                  ( SELECT
                        CASE
                        WHEN (("substring"(btrim((hd.nota)::text), 1, 1) <> (0)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (1)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (2)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (3)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (4)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (5)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (6)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (7)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (8)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (9)::text)) THEN replace((hd.nota)::text, '.'::text, ','::text)
                        WHEN ((to_number(btrim((hd.nota)::text), '999'::text) > (10)::numeric) AND (to_number(btrim((hd.nota)::text), '999'::text) <= (20)::numeric)) THEN replace(btrim((hd.nota)::text), '.'::text, ','::text)
                        ELSE replace("substring"(btrim((hd.nota)::text), 1, 4), '.'::text, ','::text)
                        END AS replace
                    FROM (pmieducar.historico_disciplinas hd
                        JOIN pmieducar.historico_escolar historico_escolar_1 ON (((historico_escolar_1.ref_cod_aluno = hd.ref_ref_cod_aluno) AND (historico_escolar_1.sequencial = hd.ref_sequencial))))
                    WHERE ((hd.ref_ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND ((get_texto_sem_caracter_especial((upper(btrim((hd.nm_disciplina)::text)))::character varying))::text = (get_texto_sem_caracter_especial((upper(btrim(historico_disciplinas.nm_disciplina)))::character varying))::text) AND (historico_escolar_1.ativo = 1) AND (historico_escolar_1.extra_curricular = 0) AND ("substring"((historico_escolar_1.nm_serie)::text, 1, 1) = (4)::text) AND (historico_escolar_1.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              FROM pmieducar.historico_escolar hee
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              WHERE ((hee.ref_cod_aluno = historico_escolar_1.ref_cod_aluno) AND ("substring"((historico_escolar_1.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0) AND (COALESCE(hee.dependencia, false) = false)))))
                    LIMIT 1) AS nota_4serie,
                  ( SELECT
                        CASE
                        WHEN (("substring"(btrim((hd.nota)::text), 1, 1) <> (0)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (1)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (2)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (3)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (4)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (5)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (6)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (7)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (8)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (9)::text)) THEN replace((hd.nota)::text, '.'::text, ','::text)
                        WHEN ((to_number(btrim((hd.nota)::text), '999'::text) > (10)::numeric) AND (to_number(btrim((hd.nota)::text), '999'::text) <= (20)::numeric)) THEN replace(btrim((hd.nota)::text), '.'::text, ','::text)
                        ELSE replace("substring"(btrim((hd.nota)::text), 1, 4), '.'::text, ','::text)
                        END AS replace
                    FROM (pmieducar.historico_disciplinas hd
                        JOIN pmieducar.historico_escolar historico_escolar_1 ON (((historico_escolar_1.ref_cod_aluno = hd.ref_ref_cod_aluno) AND (historico_escolar_1.sequencial = hd.ref_sequencial))))
                    WHERE ((hd.ref_ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND ((get_texto_sem_caracter_especial((upper(btrim((hd.nm_disciplina)::text)))::character varying))::text = (get_texto_sem_caracter_especial((upper(btrim(historico_disciplinas.nm_disciplina)))::character varying))::text) AND (historico_escolar_1.ativo = 1) AND (historico_escolar_1.extra_curricular = 0) AND ("substring"((historico_escolar_1.nm_serie)::text, 1, 1) = (5)::text) AND (historico_escolar_1.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              FROM pmieducar.historico_escolar hee
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              WHERE ((hee.ref_cod_aluno = historico_escolar_1.ref_cod_aluno) AND ("substring"((historico_escolar_1.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0) AND (COALESCE(hee.dependencia, false) = false)))))
                    LIMIT 1) AS nota_5serie,
                  ( SELECT
                        CASE
                        WHEN (("substring"(btrim((hd.nota)::text), 1, 1) <> (0)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (1)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (2)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (3)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (4)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (5)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (6)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (7)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (8)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (9)::text)) THEN replace((hd.nota)::text, '.'::text, ','::text)
                        WHEN ((to_number(btrim((hd.nota)::text), '999'::text) > (10)::numeric) AND (to_number(btrim((hd.nota)::text), '999'::text) <= (20)::numeric)) THEN replace(btrim((hd.nota)::text), '.'::text, ','::text)
                        ELSE replace("substring"(btrim((hd.nota)::text), 1, 4), '.'::text, ','::text)
                        END AS replace
                    FROM (pmieducar.historico_disciplinas hd
                        JOIN pmieducar.historico_escolar historico_escolar_1 ON (((historico_escolar_1.ref_cod_aluno = hd.ref_ref_cod_aluno) AND (historico_escolar_1.sequencial = hd.ref_sequencial))))
                    WHERE ((hd.ref_ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND ((get_texto_sem_caracter_especial((upper(btrim((hd.nm_disciplina)::text)))::character varying))::text = (get_texto_sem_caracter_especial((upper(btrim(historico_disciplinas.nm_disciplina)))::character varying))::text) AND (historico_escolar_1.ativo = 1) AND (historico_escolar_1.extra_curricular = 0) AND ("substring"((historico_escolar_1.nm_serie)::text, 1, 1) = (6)::text) AND (historico_escolar_1.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              FROM pmieducar.historico_escolar hee
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              WHERE ((hee.ref_cod_aluno = historico_escolar_1.ref_cod_aluno) AND ("substring"((historico_escolar_1.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0) AND (COALESCE(hee.dependencia, false) = false)))))
                    LIMIT 1) AS nota_6serie,
                  ( SELECT
                        CASE
                        WHEN (("substring"(btrim((hd.nota)::text), 1, 1) <> (0)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (1)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (2)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (3)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (4)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (5)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (6)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (7)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (8)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (9)::text)) THEN replace((hd.nota)::text, '.'::text, ','::text)
                        WHEN ((to_number(btrim((hd.nota)::text), '999'::text) > (10)::numeric) AND (to_number(btrim((hd.nota)::text), '999'::text) <= (20)::numeric)) THEN replace(btrim((hd.nota)::text), '.'::text, ','::text)
                        ELSE replace("substring"(btrim((hd.nota)::text), 1, 4), '.'::text, ','::text)
                        END AS replace
                    FROM (pmieducar.historico_disciplinas hd
                        JOIN pmieducar.historico_escolar historico_escolar_1 ON (((historico_escolar_1.ref_cod_aluno = hd.ref_ref_cod_aluno) AND (historico_escolar_1.sequencial = hd.ref_sequencial))))
                    WHERE ((hd.ref_ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND ((get_texto_sem_caracter_especial((upper(btrim((hd.nm_disciplina)::text)))::character varying))::text = (get_texto_sem_caracter_especial((upper(btrim(historico_disciplinas.nm_disciplina)))::character varying))::text) AND (historico_escolar_1.ativo = 1) AND (historico_escolar_1.extra_curricular = 0) AND ("substring"((historico_escolar_1.nm_serie)::text, 1, 1) = (7)::text) AND (historico_escolar_1.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              FROM pmieducar.historico_escolar hee
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              WHERE ((hee.ref_cod_aluno = historico_escolar_1.ref_cod_aluno) AND ("substring"((historico_escolar_1.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0) AND (COALESCE(hee.dependencia, false) = false)))))
                    LIMIT 1) AS nota_7serie,
                  ( SELECT
                        CASE
                        WHEN (("substring"(btrim((hd.nota)::text), 1, 1) <> (0)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (1)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (2)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (3)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (4)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (5)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (6)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (7)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (8)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (9)::text)) THEN replace((hd.nota)::text, '.'::text, ','::text)
                        WHEN ((to_number(btrim((hd.nota)::text), '999'::text) > (10)::numeric) AND (to_number(btrim((hd.nota)::text), '999'::text) <= (20)::numeric)) THEN replace(btrim((hd.nota)::text), '.'::text, ','::text)
                        ELSE replace("substring"(btrim((hd.nota)::text), 1, 4), '.'::text, ','::text)
                        END AS replace
                    FROM (pmieducar.historico_disciplinas hd
                        JOIN pmieducar.historico_escolar historico_escolar_1 ON (((historico_escolar_1.ref_cod_aluno = hd.ref_ref_cod_aluno) AND (historico_escolar_1.sequencial = hd.ref_sequencial))))
                    WHERE ((hd.ref_ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND ((get_texto_sem_caracter_especial((upper(btrim((hd.nm_disciplina)::text)))::character varying))::text = (get_texto_sem_caracter_especial((upper(btrim(historico_disciplinas.nm_disciplina)))::character varying))::text) AND (historico_escolar_1.ativo = 1) AND (historico_escolar_1.extra_curricular = 0) AND ("substring"((historico_escolar_1.nm_serie)::text, 1, 1) = (8)::text) AND (historico_escolar_1.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              FROM pmieducar.historico_escolar hee
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              WHERE ((hee.ref_cod_aluno = historico_escolar_1.ref_cod_aluno) AND ("substring"((historico_escolar_1.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0) AND (COALESCE(hee.dependencia, false) = false)))))
                    LIMIT 1) AS nota_8serie,
                  ( SELECT
                        CASE
                        WHEN (("substring"(btrim((hd.nota)::text), 1, 1) <> (0)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (1)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (2)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (3)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (4)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (5)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (6)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (7)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (8)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (9)::text)) THEN replace((hd.nota)::text, '.'::text, ','::text)
                        WHEN ((to_number(btrim((hd.nota)::text), '999'::text) > (10)::numeric) AND (to_number(btrim((hd.nota)::text), '999'::text) <= (20)::numeric)) THEN replace(btrim((hd.nota)::text), '.'::text, ','::text)
                        ELSE replace("substring"(btrim((hd.nota)::text), 1, 4), '.'::text, ','::text)
                        END AS replace
                    FROM (pmieducar.historico_disciplinas hd
                        JOIN pmieducar.historico_escolar historico_escolar_1 ON (((historico_escolar_1.ref_cod_aluno = hd.ref_ref_cod_aluno) AND (historico_escolar_1.sequencial = hd.ref_sequencial))))
                    WHERE ((hd.ref_ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND ((get_texto_sem_caracter_especial((upper(btrim((hd.nm_disciplina)::text)))::character varying))::text = (get_texto_sem_caracter_especial((upper(btrim(historico_disciplinas.nm_disciplina)))::character varying))::text) AND (historico_escolar_1.ativo = 1) AND (historico_escolar_1.extra_curricular = 0) AND ("substring"((historico_escolar_1.nm_serie)::text, 1, 1) = (9)::text) AND (historico_escolar_1.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              FROM pmieducar.historico_escolar hee
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              WHERE ((hee.ref_cod_aluno = historico_escolar_1.ref_cod_aluno) AND ("substring"((historico_escolar_1.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0) AND (COALESCE(hee.dependencia, false) = false)))))
                    LIMIT 1) AS nota_9serie,
                  ( SELECT he.ano
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (1)::text))
                    LIMIT 1) AS ano_1serie,
                  ( SELECT he.escola
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (1)::text))
                    LIMIT 1) AS escola_1serie,
                  ( SELECT he.escola_cidade
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (1)::text))
                    LIMIT 1) AS escola_cidade_1serie,
                  ( SELECT he.escola_uf
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (1)::text))
                    LIMIT 1) AS escola_uf_1serie,
                  ( SELECT he.ano
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (2)::text))
                    LIMIT 1) AS ano_2serie,
                  ( SELECT he.escola
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (2)::text))
                    LIMIT 1) AS escola_2serie,
                  ( SELECT he.escola_cidade
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (2)::text))
                    LIMIT 1) AS escola_cidade_2serie,
                  ( SELECT he.escola_uf
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (2)::text))
                    LIMIT 1) AS escola_uf_2serie,
                  ( SELECT he.ano
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (3)::text))
                    LIMIT 1) AS ano_3serie,
                  ( SELECT he.escola
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (3)::text))
                    LIMIT 1) AS escola_3serie,
                  ( SELECT he.escola_cidade
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (3)::text))
                    LIMIT 1) AS escola_cidade_3serie,
                  ( SELECT he.escola_uf
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (3)::text))
                    LIMIT 1) AS escola_uf_3serie,
                  ( SELECT he.ano
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (4)::text))
                    LIMIT 1) AS ano_4serie,
                  ( SELECT he.escola
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (4)::text))
                    LIMIT 1) AS escola_4serie,
                  ( SELECT he.escola_cidade
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (4)::text))
                    LIMIT 1) AS escola_cidade_4serie,
                  ( SELECT he.escola_uf
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (4)::text))
                    LIMIT 1) AS escola_uf_4serie,
                  ( SELECT he.ano
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (5)::text))
                    LIMIT 1) AS ano_5serie,
                  ( SELECT he.escola
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (5)::text))
                    LIMIT 1) AS escola_5serie,
                  ( SELECT he.escola_cidade
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (5)::text))
                    LIMIT 1) AS escola_cidade_5serie,
                  ( SELECT he.escola_uf
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (5)::text))
                    LIMIT 1) AS escola_uf_5serie,
                  ( SELECT he.ano
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (6)::text))
                    LIMIT 1) AS ano_6serie,
                  ( SELECT he.escola
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (6)::text))
                    LIMIT 1) AS escola_6serie,
                  ( SELECT he.escola_cidade
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (6)::text))
                    LIMIT 1) AS escola_cidade_6serie,
                  ( SELECT he.escola_uf
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (6)::text))
                    LIMIT 1) AS escola_uf_6serie,
                  ( SELECT he.ano
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (7)::text))
                    LIMIT 1) AS ano_7serie,
                  ( SELECT he.escola
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (7)::text))
                    LIMIT 1) AS escola_7serie,
                  ( SELECT he.escola_cidade
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (7)::text))
                    LIMIT 1) AS escola_cidade_7serie,
                  ( SELECT he.escola_uf
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (7)::text))
                    LIMIT 1) AS escola_uf_7serie,
                  ( SELECT he.ano
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (8)::text))
                    LIMIT 1) AS ano_8serie,
                  ( SELECT he.escola
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (8)::text))
                    LIMIT 1) AS escola_8serie,
                  ( SELECT he.escola_cidade
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (8)::text))
                    LIMIT 1) AS escola_cidade_8serie,
                  ( SELECT he.escola_uf
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (8)::text))
                    LIMIT 1) AS escola_uf_8serie,
                  ( SELECT he.ano
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (9)::text))
                    LIMIT 1) AS ano_9serie,
                  ( SELECT he.escola
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (9)::text))
                    LIMIT 1) AS escola_9serie,
                  ( SELECT he.escola_cidade
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (9)::text))
                    LIMIT 1) AS escola_cidade_9serie,
                  ( SELECT he.escola_uf
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (9)::text))
                    LIMIT 1) AS escola_uf_9serie,
                  ( SELECT DISTINCT (he.aprovado = 4)
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (1)::text))
                    ORDER BY (he.aprovado = 4)
                    LIMIT 1) AS transferido1,
                  ( SELECT DISTINCT (he.aprovado = 4)
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (2)::text))
                    ORDER BY (he.aprovado = 4)
                    LIMIT 1) AS transferido2,
                  ( SELECT DISTINCT (he.aprovado = 4)
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (3)::text))
                    ORDER BY (he.aprovado = 4)
                    LIMIT 1) AS transferido3,
                  ( SELECT DISTINCT (he.aprovado = 4)
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (4)::text))
                    ORDER BY (he.aprovado = 4)
                    LIMIT 1) AS transferido4,
                  ( SELECT DISTINCT (he.aprovado = 4)
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (5)::text))
                    ORDER BY (he.aprovado = 4)
                    LIMIT 1) AS transferido5,
                  ( SELECT DISTINCT (he.aprovado = 4)
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (6)::text))
                    ORDER BY (he.aprovado = 4)
                    LIMIT 1) AS transferido6,
                  ( SELECT DISTINCT (he.aprovado = 4)
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 11) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                   FROM pmieducar.historico_escolar hee
                                                                                                                                   WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (7)::text))
                    ORDER BY (he.aprovado = 4)
                    LIMIT 1) AS transferido7,
                  ( SELECT DISTINCT (he.aprovado = 4)
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (8)::text))
                    ORDER BY (he.aprovado = 4)
                    LIMIT 1) AS transferido8,
                  ( SELECT DISTINCT (he.aprovado = 4)
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (9)::text))
                    ORDER BY (he.aprovado = 4)
                    LIMIT 1) AS transferido9,
                  ( SELECT hd.dependencia
                    FROM pmieducar.historico_disciplinas hd
                    WHERE ((hd.ref_ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (hd.ref_sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                       FROM pmieducar.historico_escolar hee
                                                                                                                       WHERE ((hee.ref_cod_aluno = hd.ref_ref_cod_aluno) AND ((get_texto_sem_caracter_especial((upper(btrim((hd.nm_disciplina)::text)))::character varying))::text = (get_texto_sem_caracter_especial((upper(btrim(historico_disciplinas.nm_disciplina)))::character varying))::text) AND ("substring"((hee.nm_serie)::text, 1, 1) = (1)::text) AND (hee.ativo = 1) AND (hee.extra_curricular = 0) AND (COALESCE(hee.dependencia, false) = false)))))
                    LIMIT 1) AS disciplina_dependencia1,
                  ( SELECT hd.dependencia
                    FROM pmieducar.historico_disciplinas hd
                    WHERE ((hd.ref_ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (hd.ref_sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                       FROM pmieducar.historico_escolar hee
                                                                                                                       WHERE ((hee.ref_cod_aluno = hd.ref_ref_cod_aluno) AND ((get_texto_sem_caracter_especial((upper(btrim((hd.nm_disciplina)::text)))::character varying))::text = (get_texto_sem_caracter_especial((upper(btrim(historico_disciplinas.nm_disciplina)))::character varying))::text) AND ("substring"((hee.nm_serie)::text, 1, 1) = (2)::text) AND (hee.ativo = 1) AND (hee.extra_curricular = 0) AND (COALESCE(hee.dependencia, false) = false)))))
                    LIMIT 1) AS disciplina_dependencia2,
                  ( SELECT hd.dependencia
                    FROM pmieducar.historico_disciplinas hd
                    WHERE ((hd.ref_ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (hd.ref_sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                       FROM pmieducar.historico_escolar hee
                                                                                                                       WHERE ((hee.ref_cod_aluno = hd.ref_ref_cod_aluno) AND ((get_texto_sem_caracter_especial((upper(btrim((hd.nm_disciplina)::text)))::character varying))::text = (get_texto_sem_caracter_especial((upper(btrim(historico_disciplinas.nm_disciplina)))::character varying))::text) AND ("substring"((hee.nm_serie)::text, 1, 1) = (3)::text) AND (hee.ativo = 1) AND (hee.extra_curricular = 0) AND (COALESCE(hee.dependencia, false) = false)))))
                    LIMIT 1) AS disciplina_dependencia3,
                  ( SELECT hd.dependencia
                    FROM pmieducar.historico_disciplinas hd
                    WHERE ((hd.ref_ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (hd.ref_sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                       FROM pmieducar.historico_escolar hee
                                                                                                                       WHERE ((hee.ref_cod_aluno = hd.ref_ref_cod_aluno) AND ((get_texto_sem_caracter_especial((upper(btrim((hd.nm_disciplina)::text)))::character varying))::text = (get_texto_sem_caracter_especial((upper(btrim(historico_disciplinas.nm_disciplina)))::character varying))::text) AND ("substring"((hee.nm_serie)::text, 1, 1) = (4)::text) AND (hee.ativo = 1) AND (hee.extra_curricular = 0) AND (COALESCE(hee.dependencia, false) = false)))))
                    LIMIT 1) AS disciplina_dependencia4,
                  ( SELECT hd.dependencia
                    FROM pmieducar.historico_disciplinas hd
                    WHERE ((hd.ref_ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (hd.ref_sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                       FROM pmieducar.historico_escolar hee
                                                                                                                       WHERE ((hee.ref_cod_aluno = hd.ref_ref_cod_aluno) AND ((get_texto_sem_caracter_especial((upper(btrim((hd.nm_disciplina)::text)))::character varying))::text = (get_texto_sem_caracter_especial((upper(btrim(historico_disciplinas.nm_disciplina)))::character varying))::text) AND ("substring"((hee.nm_serie)::text, 1, 1) = (5)::text) AND (hee.ativo = 1) AND (hee.extra_curricular = 0) AND (COALESCE(hee.dependencia, false) = false)))))
                    LIMIT 1) AS disciplina_dependencia5,
                  ( SELECT hd.dependencia
                    FROM pmieducar.historico_disciplinas hd
                    WHERE ((hd.ref_ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (hd.ref_sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                       FROM pmieducar.historico_escolar hee
                                                                                                                       WHERE ((hee.ref_cod_aluno = hd.ref_ref_cod_aluno) AND ((get_texto_sem_caracter_especial((upper(btrim((hd.nm_disciplina)::text)))::character varying))::text = (get_texto_sem_caracter_especial((upper(btrim(historico_disciplinas.nm_disciplina)))::character varying))::text) AND ("substring"((hee.nm_serie)::text, 1, 1) = (6)::text) AND (hee.ativo = 1) AND (hee.extra_curricular = 0) AND (COALESCE(hee.dependencia, false) = false)))))
                    LIMIT 1) AS disciplina_dependencia6,
                  ( SELECT hd.dependencia
                    FROM pmieducar.historico_disciplinas hd
                    WHERE ((hd.ref_ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (hd.ref_sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                       FROM pmieducar.historico_escolar hee
                                                                                                                       WHERE ((hee.ref_cod_aluno = hd.ref_ref_cod_aluno) AND ((get_texto_sem_caracter_especial((upper(btrim((hd.nm_disciplina)::text)))::character varying))::text = (get_texto_sem_caracter_especial((upper(btrim(historico_disciplinas.nm_disciplina)))::character varying))::text) AND ("substring"((hee.nm_serie)::text, 1, 1) = (7)::text) AND (hee.ativo = 1) AND (hee.extra_curricular = 0) AND (COALESCE(hee.dependencia, false) = false)))))
                    LIMIT 1) AS disciplina_dependencia7,
                  ( SELECT hd.dependencia
                    FROM pmieducar.historico_disciplinas hd
                    WHERE ((hd.ref_ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (hd.ref_sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                       FROM pmieducar.historico_escolar hee
                                                                                                                       WHERE ((hee.ref_cod_aluno = hd.ref_ref_cod_aluno) AND ((get_texto_sem_caracter_especial((upper(btrim((hd.nm_disciplina)::text)))::character varying))::text = (get_texto_sem_caracter_especial((upper(btrim(historico_disciplinas.nm_disciplina)))::character varying))::text) AND ("substring"((hee.nm_serie)::text, 1, 1) = (8)::text) AND (hee.ativo = 1) AND (hee.extra_curricular = 0) AND (COALESCE(hee.dependencia, false) = false)))))
                    LIMIT 1) AS disciplina_dependencia8,
                  ( SELECT hd.dependencia
                    FROM pmieducar.historico_disciplinas hd
                    WHERE ((hd.ref_ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (hd.ref_sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                       FROM pmieducar.historico_escolar hee
                                                                                                                       WHERE ((hee.ref_cod_aluno = hd.ref_ref_cod_aluno) AND ((get_texto_sem_caracter_especial((upper(btrim((hd.nm_disciplina)::text)))::character varying))::text = (get_texto_sem_caracter_especial((upper(btrim(historico_disciplinas.nm_disciplina)))::character varying))::text) AND ("substring"((hee.nm_serie)::text, 1, 1) = (9)::text) AND (hee.ativo = 1) AND (hee.extra_curricular = 0) AND (COALESCE(hee.dependencia, false) = false)))))
                    LIMIT 1) AS disciplina_dependencia9,
                  ( SELECT hd.carga_horaria_disciplina
                    FROM pmieducar.historico_disciplinas hd
                    WHERE ((hd.ref_ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (hd.ref_sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                       FROM pmieducar.historico_escolar hee
                                                                                                                       WHERE ((hee.ref_cod_aluno = hd.ref_ref_cod_aluno) AND (public.fcn_upper((hd.nm_disciplina)::text) = public.fcn_upper(historico_disciplinas.nm_disciplina)) AND ("substring"((hee.nm_serie)::text, 1, 1) = (1)::text)))))
                    LIMIT 1) AS carga_horaria_disciplina1,
                  ( SELECT hd.carga_horaria_disciplina
                    FROM pmieducar.historico_disciplinas hd
                    WHERE ((hd.ref_ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (hd.ref_sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                       FROM pmieducar.historico_escolar hee
                                                                                                                       WHERE ((hee.ref_cod_aluno = hd.ref_ref_cod_aluno) AND (public.fcn_upper((hd.nm_disciplina)::text) = public.fcn_upper(historico_disciplinas.nm_disciplina)) AND ("substring"((hee.nm_serie)::text, 1, 1) = (2)::text)))))
                    LIMIT 1) AS carga_horaria_disciplina2,
                  ( SELECT hd.carga_horaria_disciplina
                    FROM pmieducar.historico_disciplinas hd
                    WHERE ((hd.ref_ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (hd.ref_sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                       FROM pmieducar.historico_escolar hee
                                                                                                                       WHERE ((hee.ref_cod_aluno = hd.ref_ref_cod_aluno) AND (public.fcn_upper((hd.nm_disciplina)::text) = public.fcn_upper(historico_disciplinas.nm_disciplina)) AND ("substring"((hee.nm_serie)::text, 1, 1) = (3)::text)))))
                    LIMIT 1) AS carga_horaria_disciplina3,
                  ( SELECT hd.carga_horaria_disciplina
                    FROM pmieducar.historico_disciplinas hd
                    WHERE ((hd.ref_ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (hd.ref_sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                       FROM pmieducar.historico_escolar hee
                                                                                                                       WHERE ((hee.ref_cod_aluno = hd.ref_ref_cod_aluno) AND (public.fcn_upper((hd.nm_disciplina)::text) = public.fcn_upper(historico_disciplinas.nm_disciplina)) AND ("substring"((hee.nm_serie)::text, 1, 1) = (4)::text)))))
                    LIMIT 1) AS carga_horaria_disciplina4,
                  ( SELECT hd.carga_horaria_disciplina
                    FROM pmieducar.historico_disciplinas hd
                    WHERE ((hd.ref_ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (hd.ref_sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                       FROM pmieducar.historico_escolar hee
                                                                                                                       WHERE ((hee.ref_cod_aluno = hd.ref_ref_cod_aluno) AND (public.fcn_upper((hd.nm_disciplina)::text) = public.fcn_upper(historico_disciplinas.nm_disciplina)) AND ("substring"((hee.nm_serie)::text, 1, 1) = (5)::text)))))
                    LIMIT 1) AS carga_horaria_disciplina5,
                  ( SELECT hd.carga_horaria_disciplina
                    FROM pmieducar.historico_disciplinas hd
                    WHERE ((hd.ref_ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (hd.ref_sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                       FROM pmieducar.historico_escolar hee
                                                                                                                       WHERE ((hee.ref_cod_aluno = hd.ref_ref_cod_aluno) AND (public.fcn_upper((hd.nm_disciplina)::text) = public.fcn_upper(historico_disciplinas.nm_disciplina)) AND ("substring"((hee.nm_serie)::text, 1, 1) = (6)::text)))))
                    LIMIT 1) AS carga_horaria_disciplina6,
                  ( SELECT hd.carga_horaria_disciplina
                    FROM pmieducar.historico_disciplinas hd
                    WHERE ((hd.ref_ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (hd.ref_sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                       FROM pmieducar.historico_escolar hee
                                                                                                                       WHERE ((hee.ref_cod_aluno = hd.ref_ref_cod_aluno) AND (public.fcn_upper((hd.nm_disciplina)::text) = public.fcn_upper(historico_disciplinas.nm_disciplina)) AND ("substring"((hee.nm_serie)::text, 1, 1) = (7)::text)))))
                    LIMIT 1) AS carga_horaria_disciplina7,
                  ( SELECT hd.carga_horaria_disciplina
                    FROM pmieducar.historico_disciplinas hd
                    WHERE ((hd.ref_ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (hd.ref_sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                       FROM pmieducar.historico_escolar hee
                                                                                                                       WHERE ((hee.ref_cod_aluno = hd.ref_ref_cod_aluno) AND (public.fcn_upper((hd.nm_disciplina)::text) = public.fcn_upper(historico_disciplinas.nm_disciplina)) AND ("substring"((hee.nm_serie)::text, 1, 1) = (8)::text)))))
                    LIMIT 1) AS carga_horaria_disciplina8,
                  ( SELECT hd.carga_horaria_disciplina
                    FROM pmieducar.historico_disciplinas hd
                    WHERE ((hd.ref_ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (hd.ref_sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                       FROM pmieducar.historico_escolar hee
                                                                                                                       WHERE ((hee.ref_cod_aluno = hd.ref_ref_cod_aluno) AND (public.fcn_upper((hd.nm_disciplina)::text) = public.fcn_upper(historico_disciplinas.nm_disciplina)) AND ("substring"((hee.nm_serie)::text, 1, 1) = (9)::text)))))
                    LIMIT 1) AS carga_horaria_disciplina9,
                  ( SELECT he.carga_horaria
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (1)::text))
                    LIMIT 1) AS carga_horaria1,
                  ( SELECT he.carga_horaria
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (2)::text))
                    LIMIT 1) AS carga_horaria2,
                  ( SELECT he.carga_horaria
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (3)::text))
                    LIMIT 1) AS carga_horaria3,
                  ( SELECT he.carga_horaria
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (4)::text))
                    LIMIT 1) AS carga_horaria4,
                  ( SELECT he.carga_horaria
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (5)::text))
                    LIMIT 1) AS carga_horaria5,
                  ( SELECT he.carga_horaria
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (6)::text))
                    LIMIT 1) AS carga_horaria6,
                  ( SELECT he.carga_horaria
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (7)::text))
                    LIMIT 1) AS carga_horaria7,
                  ( SELECT he.carga_horaria
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (8)::text))
                    LIMIT 1) AS carga_horaria8,
                  ( SELECT he.carga_horaria
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (9)::text))
                    LIMIT 1) AS carga_horaria9,
                  ( SELECT public.textcat_all(phe.observacao) AS textcat_all
                    FROM pmieducar.historico_escolar phe
                    WHERE ((phe.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (phe.ativo = 1) AND (phe.extra_curricular = 0) AND (phe.sequencial = ( SELECT max(he.sequencial) AS max
                                                                                                                                                                    FROM pmieducar.historico_escolar he
                                                                                                                                                                    WHERE ((he.ref_cod_instituicao = phe.ref_cod_instituicao) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((phe.nm_serie)::text, 1, 1)) AND (he.ref_cod_aluno = phe.ref_cod_aluno) AND (he.ativo = 1)))))) AS observacao_all,
                  ( SELECT m.cod_matricula
                    FROM pmieducar.matricula m
                    WHERE ((m.ano = ( SELECT historico_escolar_1.ano
                                      FROM pmieducar.historico_escolar historico_escolar_1
                                      WHERE ((historico_escolar_1.aprovado = 4) AND (historico_escolar_1.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (historico_escolar_1.ativo = 1) AND (historico_escolar_1.extra_curricular = 0))
                                      ORDER BY historico_escolar_1.ano DESC, historico_escolar_1.sequencial DESC
                                      LIMIT 1)) AND (( SELECT historico_escolar_1.sequencial
                                                       FROM pmieducar.historico_escolar historico_escolar_1
                                                       WHERE ((historico_escolar_1.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (historico_escolar_1.ativo = 1) AND (historico_escolar_1.extra_curricular = 0))
                                                       ORDER BY historico_escolar_1.ano DESC, historico_escolar_1.sequencial DESC
                                                       LIMIT 1) = ( SELECT historico_escolar_1.sequencial
                                                                    FROM pmieducar.historico_escolar historico_escolar_1
                                                                    WHERE ((historico_escolar_1.aprovado = 4) AND (historico_escolar_1.ativo = 1) AND (historico_escolar_1.extra_curricular = 0) AND (historico_escolar_1.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno))
                                                                    ORDER BY historico_escolar_1.ano DESC, historico_escolar_1.sequencial DESC
                                                                    LIMIT 1)) AND (m.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (m.ativo = 1) AND (m.aprovado = 4))
                    ORDER BY m.cod_matricula DESC
                    LIMIT 1) AS matricula_transferido,
                  ( SELECT historico_carga_horaria_componente((historico_disciplinas.nm_disciplina)::character varying, he.nm_serie, he.ref_cod_escola) AS historico_carga_horaria_componente
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (1)::text))
                    LIMIT 1) AS ch_componente_1,
                  ( SELECT historico_carga_horaria_componente((historico_disciplinas.nm_disciplina)::character varying, he.nm_serie, he.ref_cod_escola) AS historico_carga_horaria_componente
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (2)::text))
                    LIMIT 1) AS ch_componente_2,
                  ( SELECT historico_carga_horaria_componente((historico_disciplinas.nm_disciplina)::character varying, he.nm_serie, he.ref_cod_escola) AS historico_carga_horaria_componente
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (3)::text))
                    LIMIT 1) AS ch_componente_3,
                  ( SELECT historico_carga_horaria_componente((historico_disciplinas.nm_disciplina)::character varying, he.nm_serie, he.ref_cod_escola) AS historico_carga_horaria_componente
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (4)::text))
                    LIMIT 1) AS ch_componente_4,
                  ( SELECT historico_carga_horaria_componente((historico_disciplinas.nm_disciplina)::character varying, he.nm_serie, he.ref_cod_escola) AS historico_carga_horaria_componente
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (5)::text))
                    LIMIT 1) AS ch_componente_5,
                  ( SELECT historico_carga_horaria_componente((historico_disciplinas.nm_disciplina)::character varying, he.nm_serie, he.ref_cod_escola) AS historico_carga_horaria_componente
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (6)::text))
                    LIMIT 1) AS ch_componente_6,
                  ( SELECT historico_carga_horaria_componente((historico_disciplinas.nm_disciplina)::character varying, he.nm_serie, he.ref_cod_escola) AS historico_carga_horaria_componente
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (7)::text))
                    LIMIT 1) AS ch_componente_7,
                  ( SELECT historico_carga_horaria_componente((historico_disciplinas.nm_disciplina)::character varying, he.nm_serie, he.ref_cod_escola) AS historico_carga_horaria_componente
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (8)::text))
                    LIMIT 1) AS ch_componente_8,
                  ( SELECT historico_carga_horaria_componente((historico_disciplinas.nm_disciplina)::character varying, he.nm_serie, he.ref_cod_escola) AS historico_carga_horaria_componente
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (9)::text))
                    LIMIT 1) AS ch_componente_9,
                  ( SELECT he.frequencia
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (1)::text))
                    LIMIT 1) AS frequencia1,
                  ( SELECT he.frequencia
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (2)::text))
                    LIMIT 1) AS frequencia2,
                  ( SELECT he.frequencia
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (3)::text))
                    LIMIT 1) AS frequencia3,
                  ( SELECT he.frequencia
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (4)::text))
                    LIMIT 1) AS frequencia4,
                  ( SELECT he.frequencia
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (5)::text))
                    LIMIT 1) AS frequencia5,
                  ( SELECT he.frequencia
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (6)::text))
                    LIMIT 1) AS frequencia6,
                  ( SELECT he.frequencia
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (7)::text))
                    LIMIT 1) AS frequencia7,
                  ( SELECT he.frequencia
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (8)::text))
                    LIMIT 1) AS frequencia8,
                  ( SELECT he.frequencia
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (9)::text))
                    LIMIT 1) AS frequencia9,
                  ( SELECT DISTINCT
                        CASE
                        WHEN (he.aceleracao = 1) THEN (
                            CASE
                            WHEN (he.aprovado = 1) THEN 'Apro'::text
                            WHEN (he.aprovado = 12) THEN 'AprDep'::text
                            WHEN (he.aprovado = 13) THEN 'AprCo'::text
                            WHEN (he.aprovado = 2) THEN 'Repr'::text
                            WHEN (he.aprovado = 3) THEN 'Curs'::text
                            WHEN (he.aprovado = 4) THEN 'Tran'::text
                            WHEN (he.aprovado = 5) THEN 'Recl'::text
                            WHEN (he.aprovado = 6) THEN 'Aban'::text
                            WHEN (he.aprovado = 14) THEN 'RpFt'::text
                            WHEN (he.aprovado = 15) THEN 'Fal'::text
                            ELSE ''::text
                            END || ' AC'::text)
                        ELSE
                            CASE
                            WHEN (he.aprovado = 1) THEN 'Apro'::text
                            WHEN (he.aprovado = 12) THEN 'AprDep'::text
                            WHEN (he.aprovado = 13) THEN 'AprCo'::text
                            WHEN (he.aprovado = 2) THEN 'Repr'::text
                            WHEN (he.aprovado = 3) THEN 'Curs'::text
                            WHEN (he.aprovado = 4) THEN 'Tran'::text
                            WHEN (he.aprovado = 5) THEN 'Recl'::text
                            WHEN (he.aprovado = 6) THEN 'Aban'::text
                            WHEN (he.aprovado = 14) THEN 'RpFt'::text
                            WHEN (he.aprovado = 15) THEN 'Fal'::text
                            ELSE ''::text
                            END
                        END AS "case"
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1)))) AND ("substring"((he.nm_serie)::text, 1, 1) = '1'::text))) AS status_serie1,
                  ( SELECT DISTINCT
                        CASE
                        WHEN (he.aceleracao = 1) THEN (
                            CASE
                            WHEN (he.aprovado = 1) THEN 'Apro'::text
                            WHEN (he.aprovado = 12) THEN 'AprDep'::text
                            WHEN (he.aprovado = 13) THEN 'AprCo'::text
                            WHEN (he.aprovado = 2) THEN 'Repr'::text
                            WHEN (he.aprovado = 3) THEN 'Curs'::text
                            WHEN (he.aprovado = 4) THEN 'Tran'::text
                            WHEN (he.aprovado = 5) THEN 'Recl'::text
                            WHEN (he.aprovado = 6) THEN 'Aban'::text
                            WHEN (he.aprovado = 14) THEN 'RpFt'::text
                            WHEN (he.aprovado = 15) THEN 'Fal'::text
                            ELSE ''::text
                            END || ' AC'::text)
                        ELSE
                            CASE
                            WHEN (he.aprovado = 1) THEN 'Apro'::text
                            WHEN (he.aprovado = 12) THEN 'AprDep'::text
                            WHEN (he.aprovado = 13) THEN 'AprCo'::text
                            WHEN (he.aprovado = 2) THEN 'Repr'::text
                            WHEN (he.aprovado = 3) THEN 'Curs'::text
                            WHEN (he.aprovado = 4) THEN 'Tran'::text
                            WHEN (he.aprovado = 5) THEN 'Recl'::text
                            WHEN (he.aprovado = 6) THEN 'Aban'::text
                            WHEN (he.aprovado = 14) THEN 'RpFt'::text
                            WHEN (he.aprovado = 15) THEN 'Fal'::text
                            ELSE ''::text
                            END
                        END AS "case"
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1)))) AND ("substring"((he.nm_serie)::text, 1, 1) = '2'::text))) AS status_serie2,
                  ( SELECT DISTINCT
                        CASE
                        WHEN (he.aceleracao = 1) THEN (
                            CASE
                            WHEN (he.aprovado = 1) THEN 'Apro'::text
                            WHEN (he.aprovado = 12) THEN 'AprDep'::text
                            WHEN (he.aprovado = 13) THEN 'AprCo'::text
                            WHEN (he.aprovado = 2) THEN 'Repr'::text
                            WHEN (he.aprovado = 3) THEN 'Curs'::text
                            WHEN (he.aprovado = 4) THEN 'Tran'::text
                            WHEN (he.aprovado = 5) THEN 'Recl'::text
                            WHEN (he.aprovado = 6) THEN 'Aban'::text
                            WHEN (he.aprovado = 14) THEN 'RpFt'::text
                            WHEN (he.aprovado = 15) THEN 'Fal'::text
                            ELSE ''::text
                            END || ' AC'::text)
                        ELSE
                            CASE
                            WHEN (he.aprovado = 1) THEN 'Apro'::text
                            WHEN (he.aprovado = 12) THEN 'AprDep'::text
                            WHEN (he.aprovado = 13) THEN 'AprCo'::text
                            WHEN (he.aprovado = 2) THEN 'Repr'::text
                            WHEN (he.aprovado = 3) THEN 'Curs'::text
                            WHEN (he.aprovado = 4) THEN 'Tran'::text
                            WHEN (he.aprovado = 5) THEN 'Recl'::text
                            WHEN (he.aprovado = 6) THEN 'Aban'::text
                            WHEN (he.aprovado = 14) THEN 'RpFt'::text
                            WHEN (he.aprovado = 15) THEN 'Fal'::text
                            ELSE ''::text
                            END
                        END AS "case"
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1)))) AND ("substring"((he.nm_serie)::text, 1, 1) = '3'::text))) AS status_serie3,
                  ( SELECT DISTINCT
                        CASE
                        WHEN (he.aceleracao = 1) THEN (
                            CASE
                            WHEN (he.aprovado = 1) THEN 'Apro'::text
                            WHEN (he.aprovado = 12) THEN 'AprDep'::text
                            WHEN (he.aprovado = 13) THEN 'AprCo'::text
                            WHEN (he.aprovado = 2) THEN 'Repr'::text
                            WHEN (he.aprovado = 3) THEN 'Curs'::text
                            WHEN (he.aprovado = 4) THEN 'Tran'::text
                            WHEN (he.aprovado = 5) THEN 'Recl'::text
                            WHEN (he.aprovado = 6) THEN 'Aban'::text
                            WHEN (he.aprovado = 14) THEN 'RpFt'::text
                            WHEN (he.aprovado = 15) THEN 'Fal'::text
                            ELSE ''::text
                            END || ' AC'::text)
                        ELSE
                            CASE
                            WHEN (he.aprovado = 1) THEN 'Apro'::text
                            WHEN (he.aprovado = 12) THEN 'AprDep'::text
                            WHEN (he.aprovado = 13) THEN 'AprCo'::text
                            WHEN (he.aprovado = 2) THEN 'Repr'::text
                            WHEN (he.aprovado = 3) THEN 'Curs'::text
                            WHEN (he.aprovado = 4) THEN 'Tran'::text
                            WHEN (he.aprovado = 5) THEN 'Recl'::text
                            WHEN (he.aprovado = 6) THEN 'Aban'::text
                            WHEN (he.aprovado = 14) THEN 'RpFt'::text
                            WHEN (he.aprovado = 15) THEN 'Fal'::text
                            ELSE ''::text
                            END
                        END AS "case"
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1)))) AND ("substring"((he.nm_serie)::text, 1, 1) = '4'::text))) AS status_serie4,
                  ( SELECT DISTINCT
                        CASE
                        WHEN (he.aceleracao = 1) THEN (
                            CASE
                            WHEN (he.aprovado = 1) THEN 'Apro'::text
                            WHEN (he.aprovado = 12) THEN 'AprDep'::text
                            WHEN (he.aprovado = 13) THEN 'AprCo'::text
                            WHEN (he.aprovado = 2) THEN 'Repr'::text
                            WHEN (he.aprovado = 3) THEN 'Curs'::text
                            WHEN (he.aprovado = 4) THEN 'Tran'::text
                            WHEN (he.aprovado = 5) THEN 'Recl'::text
                            WHEN (he.aprovado = 6) THEN 'Aban'::text
                            WHEN (he.aprovado = 14) THEN 'RpFt'::text
                            WHEN (he.aprovado = 15) THEN 'Fal'::text
                            ELSE ''::text
                            END || ' AC'::text)
                        ELSE
                            CASE
                            WHEN (he.aprovado = 1) THEN 'Apro'::text
                            WHEN (he.aprovado = 12) THEN 'AprDep'::text
                            WHEN (he.aprovado = 13) THEN 'AprCo'::text
                            WHEN (he.aprovado = 2) THEN 'Repr'::text
                            WHEN (he.aprovado = 3) THEN 'Curs'::text
                            WHEN (he.aprovado = 4) THEN 'Tran'::text
                            WHEN (he.aprovado = 5) THEN 'Recl'::text
                            WHEN (he.aprovado = 6) THEN 'Aban'::text
                            WHEN (he.aprovado = 14) THEN 'RpFt'::text
                            WHEN (he.aprovado = 15) THEN 'Fal'::text
                            ELSE ''::text
                            END
                        END AS "case"
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1)))) AND ("substring"((he.nm_serie)::text, 1, 1) = '5'::text))) AS status_serie5,
                  ( SELECT DISTINCT
                        CASE
                        WHEN (he.aceleracao = 1) THEN (
                            CASE
                            WHEN (he.aprovado = 1) THEN 'Apro'::text
                            WHEN (he.aprovado = 12) THEN 'AprDep'::text
                            WHEN (he.aprovado = 13) THEN 'AprCo'::text
                            WHEN (he.aprovado = 2) THEN 'Repr'::text
                            WHEN (he.aprovado = 3) THEN 'Curs'::text
                            WHEN (he.aprovado = 4) THEN 'Tran'::text
                            WHEN (he.aprovado = 5) THEN 'Recl'::text
                            WHEN (he.aprovado = 6) THEN 'Aban'::text
                            WHEN (he.aprovado = 14) THEN 'RpFt'::text
                            WHEN (he.aprovado = 15) THEN 'Fal'::text
                            ELSE ''::text
                            END || ' AC'::text)
                        ELSE
                            CASE
                            WHEN (he.aprovado = 1) THEN 'Apro'::text
                            WHEN (he.aprovado = 12) THEN 'AprDep'::text
                            WHEN (he.aprovado = 13) THEN 'AprCo'::text
                            WHEN (he.aprovado = 2) THEN 'Repr'::text
                            WHEN (he.aprovado = 3) THEN 'Curs'::text
                            WHEN (he.aprovado = 4) THEN 'Tran'::text
                            WHEN (he.aprovado = 5) THEN 'Recl'::text
                            WHEN (he.aprovado = 6) THEN 'Aban'::text
                            WHEN (he.aprovado = 14) THEN 'RpFt'::text
                            WHEN (he.aprovado = 15) THEN 'Fal'::text
                            ELSE ''::text
                            END
                        END AS "case"
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1)))) AND ("substring"((he.nm_serie)::text, 1, 1) = '6'::text))) AS status_serie6,
                  ( SELECT DISTINCT
                        CASE
                        WHEN (he.aceleracao = 1) THEN (
                            CASE
                            WHEN (he.aprovado = 1) THEN 'Apro'::text
                            WHEN (he.aprovado = 12) THEN 'AprDep'::text
                            WHEN (he.aprovado = 13) THEN 'AprCo'::text
                            WHEN (he.aprovado = 2) THEN 'Repr'::text
                            WHEN (he.aprovado = 3) THEN 'Curs'::text
                            WHEN (he.aprovado = 4) THEN 'Tran'::text
                            WHEN (he.aprovado = 5) THEN 'Recl'::text
                            WHEN (he.aprovado = 6) THEN 'Aban'::text
                            WHEN (he.aprovado = 14) THEN 'RpFt'::text
                            WHEN (he.aprovado = 15) THEN 'Fal'::text
                            ELSE ''::text
                            END || ' AC'::text)
                        ELSE
                            CASE
                            WHEN (he.aprovado = 1) THEN 'Apro'::text
                            WHEN (he.aprovado = 12) THEN 'AprDep'::text
                            WHEN (he.aprovado = 13) THEN 'AprCo'::text
                            WHEN (he.aprovado = 2) THEN 'Repr'::text
                            WHEN (he.aprovado = 3) THEN 'Curs'::text
                            WHEN (he.aprovado = 4) THEN 'Tran'::text
                            WHEN (he.aprovado = 5) THEN 'Recl'::text
                            WHEN (he.aprovado = 6) THEN 'Aban'::text
                            WHEN (he.aprovado = 14) THEN 'RpFt'::text
                            WHEN (he.aprovado = 15) THEN 'Fal'::text
                            ELSE ''::text
                            END
                        END AS "case"
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1)))) AND ("substring"((he.nm_serie)::text, 1, 1) = '7'::text))) AS status_serie7,
                  ( SELECT DISTINCT
                        CASE
                        WHEN (he.aceleracao = 1) THEN (
                            CASE
                            WHEN (he.aprovado = 1) THEN 'Apro'::text
                            WHEN (he.aprovado = 12) THEN 'AprDep'::text
                            WHEN (he.aprovado = 13) THEN 'AprCo'::text
                            WHEN (he.aprovado = 2) THEN 'Repr'::text
                            WHEN (he.aprovado = 3) THEN 'Curs'::text
                            WHEN (he.aprovado = 4) THEN 'Tran'::text
                            WHEN (he.aprovado = 5) THEN 'Recl'::text
                            WHEN (he.aprovado = 6) THEN 'Aban'::text
                            WHEN (he.aprovado = 14) THEN 'RpFt'::text
                            WHEN (he.aprovado = 15) THEN 'Fal'::text
                            ELSE ''::text
                            END || ' AC'::text)
                        ELSE
                            CASE
                            WHEN (he.aprovado = 1) THEN 'Apro'::text
                            WHEN (he.aprovado = 12) THEN 'AprDep'::text
                            WHEN (he.aprovado = 13) THEN 'AprCo'::text
                            WHEN (he.aprovado = 2) THEN 'Repr'::text
                            WHEN (he.aprovado = 3) THEN 'Curs'::text
                            WHEN (he.aprovado = 4) THEN 'Tran'::text
                            WHEN (he.aprovado = 5) THEN 'Recl'::text
                            WHEN (he.aprovado = 6) THEN 'Aban'::text
                            WHEN (he.aprovado = 14) THEN 'RpFt'::text
                            WHEN (he.aprovado = 15) THEN 'Fal'::text
                            ELSE ''::text
                            END
                        END AS "case"
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1)))) AND ("substring"((he.nm_serie)::text, 1, 1) = '8'::text))) AS status_serie8,
                  ( SELECT DISTINCT
                        CASE
                        WHEN (he.aceleracao = 1) THEN (
                            CASE
                            WHEN (he.aprovado = 1) THEN 'Apro'::text
                            WHEN (he.aprovado = 12) THEN 'AprDep'::text
                            WHEN (he.aprovado = 13) THEN 'AprCo'::text
                            WHEN (he.aprovado = 2) THEN 'Repr'::text
                            WHEN (he.aprovado = 3) THEN 'Curs'::text
                            WHEN (he.aprovado = 4) THEN 'Tran'::text
                            WHEN (he.aprovado = 5) THEN 'Recl'::text
                            WHEN (he.aprovado = 6) THEN 'Aban'::text
                            WHEN (he.aprovado = 14) THEN 'RpFt'::text
                            WHEN (he.aprovado = 15) THEN 'Fal'::text
                            ELSE ''::text
                            END || ' AC'::text)
                        ELSE
                            CASE
                            WHEN (he.aprovado = 1) THEN 'Apro'::text
                            WHEN (he.aprovado = 12) THEN 'AprDep'::text
                            WHEN (he.aprovado = 13) THEN 'AprCo'::text
                            WHEN (he.aprovado = 2) THEN 'Repr'::text
                            WHEN (he.aprovado = 3) THEN 'Curs'::text
                            WHEN (he.aprovado = 4) THEN 'Tran'::text
                            WHEN (he.aprovado = 5) THEN 'Recl'::text
                            WHEN (he.aprovado = 6) THEN 'Aban'::text
                            WHEN (he.aprovado = 14) THEN 'RpFt'::text
                            WHEN (he.aprovado = 15) THEN 'Fal'::text
                            ELSE ''::text
                            END
                        END AS "case"
                    FROM pmieducar.historico_escolar he
                    WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                  FROM pmieducar.historico_escolar hee
                                                                                                                                  WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1)))) AND ("substring"((he.nm_serie)::text, 1, 1) = '9'::text))) AS status_serie9
           FROM (( SELECT historico_disciplinas_1.sequencial,
                       historico_disciplinas_1.ref_ref_cod_aluno,
                       historico_disciplinas_1.ref_sequencial,
                       btrim((get_texto_sem_caracter_especial(historico_disciplinas_1.nm_disciplina))::text) AS nm_disciplina,
                       historico_disciplinas_1.nota,
                       historico_disciplinas_1.faltas,
                       historico_disciplinas_1.import
                   FROM pmieducar.historico_disciplinas historico_disciplinas_1) historico_disciplinas
               JOIN pmieducar.historico_escolar ON (((historico_escolar.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (historico_escolar.sequencial = historico_disciplinas.ref_sequencial))))
           WHERE ((historico_escolar.extra_curricular = 0) AND (historico_escolar.ativo = 1))
           GROUP BY historico_disciplinas.nm_disciplina, historico_disciplinas.ref_ref_cod_aluno, historico_escolar.ref_cod_aluno, historico_escolar.sequencial
           ORDER BY historico_disciplinas.nm_disciplina) historico
    GROUP BY historico.disciplina, historico.cod_aluno, historico.nota_1serie, historico.nota_2serie, historico.nota_3serie, historico.nota_4serie, historico.nota_5serie, historico.nota_6serie, historico.nota_7serie, historico.nota_8serie, historico.nota_9serie, historico.ano_1serie, historico.ano_2serie, historico.ano_3serie, historico.ano_4serie, historico.ano_5serie, historico.ano_6serie, historico.ano_7serie, historico.ano_8serie, historico.ano_9serie, historico.escola_1serie, historico.escola_2serie, historico.escola_3serie, historico.escola_4serie, historico.escola_5serie, historico.escola_6serie, historico.escola_7serie, historico.escola_8serie, historico.escola_9serie, historico.escola_cidade_1serie, historico.escola_cidade_2serie, historico.escola_cidade_3serie, historico.escola_cidade_4serie, historico.escola_cidade_5serie, historico.escola_cidade_6serie, historico.escola_cidade_7serie, historico.escola_cidade_8serie, historico.escola_cidade_9serie, historico.escola_uf_1serie, historico.escola_uf_2serie, historico.escola_uf_3serie, historico.escola_uf_4serie, historico.escola_uf_5serie, historico.escola_uf_6serie, historico.escola_uf_7serie, historico.escola_uf_8serie, historico.escola_uf_9serie, historico.transferido1, historico.transferido2, historico.transferido3, historico.transferido4, historico.transferido5, historico.transferido6, historico.transferido7, historico.transferido8, historico.transferido9, historico.carga_horaria_disciplina1, historico.carga_horaria_disciplina2, historico.carga_horaria_disciplina3, historico.carga_horaria_disciplina4, historico.carga_horaria_disciplina5, historico.carga_horaria_disciplina6, historico.carga_horaria_disciplina7, historico.carga_horaria_disciplina8, historico.carga_horaria_disciplina9, historico.carga_horaria1, historico.carga_horaria2, historico.carga_horaria3, historico.carga_horaria4, historico.carga_horaria5, historico.carga_horaria6, historico.carga_horaria7, historico.carga_horaria8, historico.carga_horaria9, historico.disciplina_dependencia1, historico.disciplina_dependencia2, historico.disciplina_dependencia3, historico.disciplina_dependencia4, historico.disciplina_dependencia5, historico.disciplina_dependencia6, historico.disciplina_dependencia7, historico.disciplina_dependencia8, historico.disciplina_dependencia9, historico.ch_componente_1, historico.ch_componente_2, historico.ch_componente_3, historico.ch_componente_4, historico.ch_componente_5, historico.ch_componente_6, historico.ch_componente_7, historico.ch_componente_8, historico.ch_componente_9, historico.observacao_all, historico.matricula_transferido, historico.frequencia1, historico.frequencia2, historico.frequencia3, historico.frequencia4, historico.frequencia5, historico.frequencia6, historico.frequencia7, historico.frequencia8, historico.frequencia9;


--
-- Name: view_historico_9anos_extra_curricular; Type: VIEW; Schema: relatorio; Owner: postgres
--

CREATE OR REPLACE VIEW view_historico_9anos_extra_curricular AS
    SELECT historico_disciplinas.ref_ref_cod_aluno AS cod_aluno,
           public.fcn_upper((historico_disciplinas.nm_disciplina)::text) AS disciplina,
           ( SELECT
                 CASE
                 WHEN (("substring"(btrim((hd.nota)::text), 1, 1) <> (0)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (1)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (2)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (3)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (4)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (5)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (6)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (7)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (8)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (9)::text)) THEN replace((hd.nota)::text, '.'::text, ','::text)
                 WHEN ((to_number(btrim((hd.nota)::text), '999'::text) > (10)::numeric) AND (to_number(btrim((hd.nota)::text), '999'::text) <= (20)::numeric)) THEN replace(btrim((hd.nota)::text), '.'::text, ','::text)
                 ELSE replace("substring"(btrim((hd.nota)::text), 1, 4), '.'::text, ','::text)
                 END AS replace
             FROM (pmieducar.historico_disciplinas hd
                 JOIN pmieducar.historico_escolar historico_escolar_1 ON (((historico_escolar_1.ref_cod_aluno = hd.ref_ref_cod_aluno) AND (historico_escolar_1.sequencial = hd.ref_sequencial))))
             WHERE ((hd.ref_ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (public.fcn_upper((hd.nm_disciplina)::text) = public.fcn_upper((hd.nm_disciplina)::text)) AND (historico_escolar_1.ativo = 1) AND (historico_escolar_1.extra_curricular = 1) AND (historico_escolar_1.ano = ( SELECT he.ano
                                                                                                                                                                                                                                                                                                       FROM pmieducar.historico_escolar he
                                                                                                                                                                                                                                                                                                       WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                                                                                                                                                                                                                                                                                                     FROM pmieducar.historico_escolar hee
                                                                                                                                                                                                                                                                                                                                                                                                                     WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (1)::text))
                                                                                                                                                                                                                                                                                                       LIMIT 1)))
             LIMIT 1) AS nota_1serie,
           ( SELECT
                 CASE
                 WHEN (("substring"(btrim((hd.nota)::text), 1, 1) <> (0)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (1)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (2)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (3)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (4)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (5)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (6)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (7)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (8)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (9)::text)) THEN replace((hd.nota)::text, '.'::text, ','::text)
                 WHEN ((to_number(btrim((hd.nota)::text), '999'::text) > (10)::numeric) AND (to_number(btrim((hd.nota)::text), '999'::text) <= (20)::numeric)) THEN replace(btrim((hd.nota)::text), '.'::text, ','::text)
                 ELSE replace("substring"(btrim((hd.nota)::text), 1, 4), '.'::text, ','::text)
                 END AS replace
             FROM (pmieducar.historico_disciplinas hd
                 JOIN pmieducar.historico_escolar historico_escolar_1 ON (((historico_escolar_1.ref_cod_aluno = hd.ref_ref_cod_aluno) AND (historico_escolar_1.sequencial = hd.ref_sequencial))))
             WHERE ((hd.ref_ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (public.fcn_upper((hd.nm_disciplina)::text) = public.fcn_upper((hd.nm_disciplina)::text)) AND (historico_escolar_1.ativo = 1) AND (historico_escolar_1.extra_curricular = 1) AND (historico_escolar_1.ano = ( SELECT he.ano
                                                                                                                                                                                                                                                                                                       FROM pmieducar.historico_escolar he
                                                                                                                                                                                                                                                                                                       WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                                                                                                                                                                                                                                                                                                     FROM pmieducar.historico_escolar hee
                                                                                                                                                                                                                                                                                                                                                                                                                     WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (2)::text))
                                                                                                                                                                                                                                                                                                       LIMIT 1)))
             LIMIT 1) AS nota_2serie,
           ( SELECT
                 CASE
                 WHEN (("substring"(btrim((hd.nota)::text), 1, 1) <> (0)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (1)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (2)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (3)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (4)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (5)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (6)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (7)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (8)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (9)::text)) THEN replace((hd.nota)::text, '.'::text, ','::text)
                 WHEN ((to_number(btrim((hd.nota)::text), '999'::text) > (10)::numeric) AND (to_number(btrim((hd.nota)::text), '999'::text) <= (20)::numeric)) THEN replace(btrim((hd.nota)::text), '.'::text, ','::text)
                 ELSE replace("substring"(btrim((hd.nota)::text), 1, 4), '.'::text, ','::text)
                 END AS replace
             FROM (pmieducar.historico_disciplinas hd
                 JOIN pmieducar.historico_escolar historico_escolar_1 ON (((historico_escolar_1.ref_cod_aluno = hd.ref_ref_cod_aluno) AND (historico_escolar_1.sequencial = hd.ref_sequencial))))
             WHERE ((hd.ref_ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (public.fcn_upper((hd.nm_disciplina)::text) = public.fcn_upper((hd.nm_disciplina)::text)) AND (historico_escolar_1.ativo = 1) AND (historico_escolar_1.extra_curricular = 1) AND (historico_escolar_1.ano = ( SELECT he.ano
                                                                                                                                                                                                                                                                                                       FROM pmieducar.historico_escolar he
                                                                                                                                                                                                                                                                                                       WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                                                                                                                                                                                                                                                                                                     FROM pmieducar.historico_escolar hee
                                                                                                                                                                                                                                                                                                                                                                                                                     WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (3)::text))
                                                                                                                                                                                                                                                                                                       LIMIT 1)))
             LIMIT 1) AS nota_3serie,
           ( SELECT
                 CASE
                 WHEN (("substring"(btrim((hd.nota)::text), 1, 1) <> (0)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (1)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (2)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (3)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (4)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (5)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (6)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (7)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (8)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (9)::text)) THEN replace((hd.nota)::text, '.'::text, ','::text)
                 WHEN ((to_number(btrim((hd.nota)::text), '999'::text) > (10)::numeric) AND (to_number(btrim((hd.nota)::text), '999'::text) <= (20)::numeric)) THEN replace(btrim((hd.nota)::text), '.'::text, ','::text)
                 ELSE replace("substring"(btrim((hd.nota)::text), 1, 4), '.'::text, ','::text)
                 END AS replace
             FROM (pmieducar.historico_disciplinas hd
                 JOIN pmieducar.historico_escolar historico_escolar_1 ON (((historico_escolar_1.ref_cod_aluno = hd.ref_ref_cod_aluno) AND (historico_escolar_1.sequencial = hd.ref_sequencial))))
             WHERE ((hd.ref_ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (public.fcn_upper((hd.nm_disciplina)::text) = public.fcn_upper((hd.nm_disciplina)::text)) AND (historico_escolar_1.ativo = 1) AND (historico_escolar_1.extra_curricular = 1) AND (historico_escolar_1.ano = ( SELECT he.ano
                                                                                                                                                                                                                                                                                                       FROM pmieducar.historico_escolar he
                                                                                                                                                                                                                                                                                                       WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                                                                                                                                                                                                                                                                                                     FROM pmieducar.historico_escolar hee
                                                                                                                                                                                                                                                                                                                                                                                                                     WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (4)::text))
                                                                                                                                                                                                                                                                                                       LIMIT 1)))
             LIMIT 1) AS nota_4serie,
           ( SELECT
                 CASE
                 WHEN (("substring"(btrim((hd.nota)::text), 1, 1) <> (0)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (1)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (2)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (3)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (4)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (5)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (6)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (7)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (8)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (9)::text)) THEN replace((hd.nota)::text, '.'::text, ','::text)
                 WHEN ((to_number(btrim((hd.nota)::text), '999'::text) > (10)::numeric) AND (to_number(btrim((hd.nota)::text), '999'::text) <= (20)::numeric)) THEN replace(btrim((hd.nota)::text), '.'::text, ','::text)
                 ELSE replace("substring"(btrim((hd.nota)::text), 1, 4), '.'::text, ','::text)
                 END AS replace
             FROM (pmieducar.historico_disciplinas hd
                 JOIN pmieducar.historico_escolar historico_escolar_1 ON (((historico_escolar_1.ref_cod_aluno = hd.ref_ref_cod_aluno) AND (historico_escolar_1.sequencial = hd.ref_sequencial))))
             WHERE ((hd.ref_ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (public.fcn_upper((hd.nm_disciplina)::text) = public.fcn_upper((hd.nm_disciplina)::text)) AND (historico_escolar_1.ativo = 1) AND (historico_escolar_1.extra_curricular = 1) AND (historico_escolar_1.ano = ( SELECT he.ano
                                                                                                                                                                                                                                                                                                       FROM pmieducar.historico_escolar he
                                                                                                                                                                                                                                                                                                       WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                                                                                                                                                                                                                                                                                                     FROM pmieducar.historico_escolar hee
                                                                                                                                                                                                                                                                                                                                                                                                                     WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (5)::text))
                                                                                                                                                                                                                                                                                                       LIMIT 1)))
             LIMIT 1) AS nota_5serie,
           ( SELECT
                 CASE
                 WHEN (("substring"(btrim((hd.nota)::text), 1, 1) <> (0)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (1)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (2)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (3)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (4)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (5)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (6)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (7)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (8)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (9)::text)) THEN replace((hd.nota)::text, '.'::text, ','::text)
                 WHEN ((to_number(btrim((hd.nota)::text), '999'::text) > (10)::numeric) AND (to_number(btrim((hd.nota)::text), '999'::text) <= (20)::numeric)) THEN replace(btrim((hd.nota)::text), '.'::text, ','::text)
                 ELSE replace("substring"(btrim((hd.nota)::text), 1, 4), '.'::text, ','::text)
                 END AS replace
             FROM (pmieducar.historico_disciplinas hd
                 JOIN pmieducar.historico_escolar historico_escolar_1 ON (((historico_escolar_1.ref_cod_aluno = hd.ref_ref_cod_aluno) AND (historico_escolar_1.sequencial = hd.ref_sequencial))))
             WHERE ((hd.ref_ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (public.fcn_upper((hd.nm_disciplina)::text) = public.fcn_upper((hd.nm_disciplina)::text)) AND (historico_escolar_1.ativo = 1) AND (historico_escolar_1.extra_curricular = 1) AND (historico_escolar_1.ano = ( SELECT he.ano
                                                                                                                                                                                                                                                                                                       FROM pmieducar.historico_escolar he
                                                                                                                                                                                                                                                                                                       WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                                                                                                                                                                                                                                                                                                     FROM pmieducar.historico_escolar hee
                                                                                                                                                                                                                                                                                                                                                                                                                     WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (6)::text))
                                                                                                                                                                                                                                                                                                       LIMIT 1)))
             LIMIT 1) AS nota_6serie,
           ( SELECT
                 CASE
                 WHEN (("substring"(btrim((hd.nota)::text), 1, 1) <> (0)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (1)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (2)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (3)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (4)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (5)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (6)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (7)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (8)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (9)::text)) THEN replace((hd.nota)::text, '.'::text, ','::text)
                 WHEN ((to_number(btrim((hd.nota)::text), '999'::text) > (10)::numeric) AND (to_number(btrim((hd.nota)::text), '999'::text) <= (20)::numeric)) THEN replace(btrim((hd.nota)::text), '.'::text, ','::text)
                 ELSE replace("substring"(btrim((hd.nota)::text), 1, 4), '.'::text, ','::text)
                 END AS replace
             FROM (pmieducar.historico_disciplinas hd
                 JOIN pmieducar.historico_escolar historico_escolar_1 ON (((historico_escolar_1.ref_cod_aluno = hd.ref_ref_cod_aluno) AND (historico_escolar_1.sequencial = hd.ref_sequencial))))
             WHERE ((hd.ref_ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (public.fcn_upper((hd.nm_disciplina)::text) = public.fcn_upper((hd.nm_disciplina)::text)) AND (historico_escolar_1.ativo = 1) AND (historico_escolar_1.extra_curricular = 1) AND (historico_escolar_1.ano = ( SELECT he.ano
                                                                                                                                                                                                                                                                                                       FROM pmieducar.historico_escolar he
                                                                                                                                                                                                                                                                                                       WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                                                                                                                                                                                                                                                                                                     FROM pmieducar.historico_escolar hee
                                                                                                                                                                                                                                                                                                                                                                                                                     WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (7)::text))
                                                                                                                                                                                                                                                                                                       LIMIT 1)))
             LIMIT 1) AS nota_7serie,
           ( SELECT
                 CASE
                 WHEN (("substring"(btrim((hd.nota)::text), 1, 1) <> (0)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (1)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (2)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (3)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (4)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (5)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (6)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (7)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (8)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (9)::text)) THEN replace((hd.nota)::text, '.'::text, ','::text)
                 WHEN ((to_number(btrim((hd.nota)::text), '999'::text) > (10)::numeric) AND (to_number(btrim((hd.nota)::text), '999'::text) <= (20)::numeric)) THEN replace(btrim((hd.nota)::text), '.'::text, ','::text)
                 ELSE replace("substring"(btrim((hd.nota)::text), 1, 4), '.'::text, ','::text)
                 END AS replace
             FROM (pmieducar.historico_disciplinas hd
                 JOIN pmieducar.historico_escolar historico_escolar_1 ON (((historico_escolar_1.ref_cod_aluno = hd.ref_ref_cod_aluno) AND (historico_escolar_1.sequencial = hd.ref_sequencial))))
             WHERE ((hd.ref_ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (public.fcn_upper((hd.nm_disciplina)::text) = public.fcn_upper((hd.nm_disciplina)::text)) AND (historico_escolar_1.ativo = 1) AND (historico_escolar_1.extra_curricular = 1) AND (historico_escolar_1.ano = ( SELECT he.ano
                                                                                                                                                                                                                                                                                                       FROM pmieducar.historico_escolar he
                                                                                                                                                                                                                                                                                                       WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                                                                                                                                                                                                                                                                                                     FROM pmieducar.historico_escolar hee
                                                                                                                                                                                                                                                                                                                                                                                                                     WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (8)::text))
                                                                                                                                                                                                                                                                                                       LIMIT 1)))
             LIMIT 1) AS nota_8serie,
           ( SELECT
                 CASE
                 WHEN (("substring"(btrim((hd.nota)::text), 1, 1) <> (0)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (1)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (2)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (3)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (4)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (5)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (6)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (7)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (8)::text) AND ("substring"(btrim((hd.nota)::text), 1, 1) <> (9)::text)) THEN replace((hd.nota)::text, '.'::text, ','::text)
                 WHEN ((to_number(btrim((hd.nota)::text), '999'::text) > (10)::numeric) AND (to_number(btrim((hd.nota)::text), '999'::text) <= (20)::numeric)) THEN replace(btrim((hd.nota)::text), '.'::text, ','::text)
                 ELSE replace("substring"(btrim((hd.nota)::text), 1, 4), '.'::text, ','::text)
                 END AS replace
             FROM (pmieducar.historico_disciplinas hd
                 JOIN pmieducar.historico_escolar historico_escolar_1 ON (((historico_escolar_1.ref_cod_aluno = hd.ref_ref_cod_aluno) AND (historico_escolar_1.sequencial = hd.ref_sequencial))))
             WHERE ((hd.ref_ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (public.fcn_upper((hd.nm_disciplina)::text) = public.fcn_upper((hd.nm_disciplina)::text)) AND (historico_escolar_1.ativo = 1) AND (historico_escolar_1.extra_curricular = 1) AND (historico_escolar_1.ano = ( SELECT he.ano
                                                                                                                                                                                                                                                                                                       FROM pmieducar.historico_escolar he
                                                                                                                                                                                                                                                                                                       WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                                                                                                                                                                                                                                                                                                     FROM pmieducar.historico_escolar hee
                                                                                                                                                                                                                                                                                                                                                                                                                     WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (9)::text))
                                                                                                                                                                                                                                                                                                       LIMIT 1)))
             LIMIT 1) AS nota_9serie,
           ( SELECT he.ano
             FROM pmieducar.historico_escolar he
             WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                           FROM pmieducar.historico_escolar hee
                                                                                                                           WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (1)::text))
             LIMIT 1) AS ano_1serie,
           ( SELECT he.ano
             FROM pmieducar.historico_escolar he
             WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                           FROM pmieducar.historico_escolar hee
                                                                                                                           WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (2)::text))
             LIMIT 1) AS ano_2serie,
           ( SELECT he.ano
             FROM pmieducar.historico_escolar he
             WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                           FROM pmieducar.historico_escolar hee
                                                                                                                           WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (3)::text))
             LIMIT 1) AS ano_3serie,
           ( SELECT he.ano
             FROM pmieducar.historico_escolar he
             WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                           FROM pmieducar.historico_escolar hee
                                                                                                                           WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (4)::text))
             LIMIT 1) AS ano_4serie,
           ( SELECT he.ano
             FROM pmieducar.historico_escolar he
             WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                           FROM pmieducar.historico_escolar hee
                                                                                                                           WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (5)::text))
             LIMIT 1) AS ano_5serie,
           ( SELECT he.ano
             FROM pmieducar.historico_escolar he
             WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                           FROM pmieducar.historico_escolar hee
                                                                                                                           WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (6)::text))
             LIMIT 1) AS ano_6serie,
           ( SELECT he.ano
             FROM pmieducar.historico_escolar he
             WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                           FROM pmieducar.historico_escolar hee
                                                                                                                           WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (7)::text))
             LIMIT 1) AS ano_7serie,
           ( SELECT he.ano
             FROM pmieducar.historico_escolar he
             WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                           FROM pmieducar.historico_escolar hee
                                                                                                                           WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (8)::text))
             LIMIT 1) AS ano_8serie,
           ( SELECT he.ano
             FROM pmieducar.historico_escolar he
             WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                           FROM pmieducar.historico_escolar hee
                                                                                                                           WHERE ((hee.ref_cod_aluno = he.ref_cod_aluno) AND ("substring"((he.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he.nm_serie)::text, 1, 1) = (9)::text))
             LIMIT 1) AS ano_9serie,
           ( SELECT DISTINCT (he.aprovado = 4)
             FROM pmieducar.historico_escolar he
             WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.extra_curricular = 1) AND (he.ano = ( SELECT he_1.ano
                                                                                                                                                  FROM pmieducar.historico_escolar he_1
                                                                                                                                                  WHERE ((he_1.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he_1.ativo = 1) AND (he_1.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                                                                                                                                                      FROM pmieducar.historico_escolar hee
                                                                                                                                                                                                                                                                      WHERE ((hee.ref_cod_aluno = he_1.ref_cod_aluno) AND ("substring"((he_1.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he_1.nm_serie)::text, 1, 1) = (1)::text))
                                                                                                                                                  LIMIT 1)))
             ORDER BY (he.aprovado = 4)
             LIMIT 1) AS transferido1,
           ( SELECT DISTINCT (he.aprovado = 4)
             FROM pmieducar.historico_escolar he
             WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.extra_curricular = 1) AND (he.ano = ( SELECT he_1.ano
                                                                                                                                                  FROM pmieducar.historico_escolar he_1
                                                                                                                                                  WHERE ((he_1.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he_1.ativo = 1) AND (he_1.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                                                                                                                                                      FROM pmieducar.historico_escolar hee
                                                                                                                                                                                                                                                                      WHERE ((hee.ref_cod_aluno = he_1.ref_cod_aluno) AND ("substring"((he_1.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he_1.nm_serie)::text, 1, 1) = (2)::text))
                                                                                                                                                  LIMIT 1)))
             ORDER BY (he.aprovado = 4)
             LIMIT 1) AS transferido2,
           ( SELECT DISTINCT (he.aprovado = 4)
             FROM pmieducar.historico_escolar he
             WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.extra_curricular = 1) AND (he.ano = ( SELECT he_1.ano
                                                                                                                                                  FROM pmieducar.historico_escolar he_1
                                                                                                                                                  WHERE ((he_1.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he_1.ativo = 1) AND (he_1.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                                                                                                                                                      FROM pmieducar.historico_escolar hee
                                                                                                                                                                                                                                                                      WHERE ((hee.ref_cod_aluno = he_1.ref_cod_aluno) AND ("substring"((he_1.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he_1.nm_serie)::text, 1, 1) = (3)::text))
                                                                                                                                                  LIMIT 1)))
             ORDER BY (he.aprovado = 4)
             LIMIT 1) AS transferido3,
           ( SELECT DISTINCT (he.aprovado = 4)
             FROM pmieducar.historico_escolar he
             WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.extra_curricular = 1) AND (he.ano = ( SELECT he_1.ano
                                                                                                                                                  FROM pmieducar.historico_escolar he_1
                                                                                                                                                  WHERE ((he_1.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he_1.ativo = 1) AND (he_1.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                                                                                                                                                      FROM pmieducar.historico_escolar hee
                                                                                                                                                                                                                                                                      WHERE ((hee.ref_cod_aluno = he_1.ref_cod_aluno) AND ("substring"((he_1.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he_1.nm_serie)::text, 1, 1) = (4)::text))
                                                                                                                                                  LIMIT 1)))
             ORDER BY (he.aprovado = 4)
             LIMIT 1) AS transferido4,
           ( SELECT DISTINCT (he.aprovado = 4)
             FROM pmieducar.historico_escolar he
             WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.extra_curricular = 1) AND (he.ano = ( SELECT he_1.ano
                                                                                                                                                  FROM pmieducar.historico_escolar he_1
                                                                                                                                                  WHERE ((he_1.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he_1.ativo = 1) AND (he_1.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                                                                                                                                                      FROM pmieducar.historico_escolar hee
                                                                                                                                                                                                                                                                      WHERE ((hee.ref_cod_aluno = he_1.ref_cod_aluno) AND ("substring"((he_1.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he_1.nm_serie)::text, 1, 1) = (5)::text))
                                                                                                                                                  LIMIT 1)))
             ORDER BY (he.aprovado = 4)
             LIMIT 1) AS transferido5,
           ( SELECT DISTINCT (he.aprovado = 4)
             FROM pmieducar.historico_escolar he
             WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.extra_curricular = 1) AND (he.ano = ( SELECT he_1.ano
                                                                                                                                                  FROM pmieducar.historico_escolar he_1
                                                                                                                                                  WHERE ((he_1.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he_1.ativo = 1) AND (he_1.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                                                                                                                                                      FROM pmieducar.historico_escolar hee
                                                                                                                                                                                                                                                                      WHERE ((hee.ref_cod_aluno = he_1.ref_cod_aluno) AND ("substring"((he_1.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he_1.nm_serie)::text, 1, 1) = (6)::text))
                                                                                                                                                  LIMIT 1)))
             ORDER BY (he.aprovado = 4)
             LIMIT 1) AS transferido6,
           ( SELECT DISTINCT (he.aprovado = 4)
             FROM pmieducar.historico_escolar he
             WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.extra_curricular = 1) AND (he.ano = ( SELECT he_1.ano
                                                                                                                                                  FROM pmieducar.historico_escolar he_1
                                                                                                                                                  WHERE ((he_1.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he_1.ativo = 1) AND (he_1.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                                                                                                                                                      FROM pmieducar.historico_escolar hee
                                                                                                                                                                                                                                                                      WHERE ((hee.ref_cod_aluno = he_1.ref_cod_aluno) AND ("substring"((he_1.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he_1.nm_serie)::text, 1, 1) = (7)::text))
                                                                                                                                                  LIMIT 1)))
             ORDER BY (he.aprovado = 4)
             LIMIT 1) AS transferido7,
           ( SELECT DISTINCT (he.aprovado = 4)
             FROM pmieducar.historico_escolar he
             WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.extra_curricular = 1) AND (he.ano = ( SELECT he_1.ano
                                                                                                                                                  FROM pmieducar.historico_escolar he_1
                                                                                                                                                  WHERE ((he_1.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he_1.ativo = 1) AND (he_1.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                                                                                                                                                      FROM pmieducar.historico_escolar hee
                                                                                                                                                                                                                                                                      WHERE ((hee.ref_cod_aluno = he_1.ref_cod_aluno) AND ("substring"((he_1.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he_1.nm_serie)::text, 1, 1) = (8)::text))
                                                                                                                                                  LIMIT 1)))
             ORDER BY (he.aprovado = 4)
             LIMIT 1) AS transferido8,
           ( SELECT DISTINCT (he.aprovado = 4)
             FROM pmieducar.historico_escolar he
             WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.extra_curricular = 1) AND (he.ano = ( SELECT he_1.ano
                                                                                                                                                  FROM pmieducar.historico_escolar he_1
                                                                                                                                                  WHERE ((he_1.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he_1.ativo = 1) AND (he_1.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                                                                                                                                                      FROM pmieducar.historico_escolar hee
                                                                                                                                                                                                                                                                      WHERE ((hee.ref_cod_aluno = he_1.ref_cod_aluno) AND ("substring"((he_1.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he_1.nm_serie)::text, 1, 1) = (9)::text))
                                                                                                                                                  LIMIT 1)))
             ORDER BY (he.aprovado = 4)
             LIMIT 1) AS transferido9,
           ( SELECT he.carga_horaria
             FROM pmieducar.historico_escolar he
             WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.extra_curricular = 1) AND (he.ano = ( SELECT he_1.ano
                                                                                                                                                  FROM pmieducar.historico_escolar he_1
                                                                                                                                                  WHERE ((he_1.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he_1.ativo = 1) AND (he_1.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                                                                                                                                                      FROM pmieducar.historico_escolar hee
                                                                                                                                                                                                                                                                      WHERE ((hee.ref_cod_aluno = he_1.ref_cod_aluno) AND ("substring"((he_1.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he_1.nm_serie)::text, 1, 1) = (1)::text))
                                                                                                                                                  LIMIT 1)))
             LIMIT 1) AS carga_horaria1,
           ( SELECT he.carga_horaria
             FROM pmieducar.historico_escolar he
             WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.extra_curricular = 1) AND (he.ano = ( SELECT he_1.ano
                                                                                                                                                  FROM pmieducar.historico_escolar he_1
                                                                                                                                                  WHERE ((he_1.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he_1.ativo = 1) AND (he_1.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                                                                                                                                                      FROM pmieducar.historico_escolar hee
                                                                                                                                                                                                                                                                      WHERE ((hee.ref_cod_aluno = he_1.ref_cod_aluno) AND ("substring"((he_1.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he_1.nm_serie)::text, 1, 1) = (2)::text))
                                                                                                                                                  LIMIT 1)))
             LIMIT 1) AS carga_horaria2,
           ( SELECT he.carga_horaria
             FROM pmieducar.historico_escolar he
             WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.extra_curricular = 1) AND (he.ano = ( SELECT he_1.ano
                                                                                                                                                  FROM pmieducar.historico_escolar he_1
                                                                                                                                                  WHERE ((he_1.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he_1.ativo = 1) AND (he_1.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                                                                                                                                                      FROM pmieducar.historico_escolar hee
                                                                                                                                                                                                                                                                      WHERE ((hee.ref_cod_aluno = he_1.ref_cod_aluno) AND ("substring"((he_1.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he_1.nm_serie)::text, 1, 1) = (3)::text))
                                                                                                                                                  LIMIT 1)))
             LIMIT 1) AS carga_horaria3,
           ( SELECT he.carga_horaria
             FROM pmieducar.historico_escolar he
             WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.extra_curricular = 1) AND (he.ano = ( SELECT he_1.ano
                                                                                                                                                  FROM pmieducar.historico_escolar he_1
                                                                                                                                                  WHERE ((he_1.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he_1.ativo = 1) AND (he_1.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                                                                                                                                                      FROM pmieducar.historico_escolar hee
                                                                                                                                                                                                                                                                      WHERE ((hee.ref_cod_aluno = he_1.ref_cod_aluno) AND ("substring"((he_1.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he_1.nm_serie)::text, 1, 1) = (4)::text))
                                                                                                                                                  LIMIT 1)))
             LIMIT 1) AS carga_horaria4,
           ( SELECT he.carga_horaria
             FROM pmieducar.historico_escolar he
             WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.extra_curricular = 1) AND (he.ano = ( SELECT he_1.ano
                                                                                                                                                  FROM pmieducar.historico_escolar he_1
                                                                                                                                                  WHERE ((he_1.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he_1.ativo = 1) AND (he_1.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                                                                                                                                                      FROM pmieducar.historico_escolar hee
                                                                                                                                                                                                                                                                      WHERE ((hee.ref_cod_aluno = he_1.ref_cod_aluno) AND ("substring"((he_1.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he_1.nm_serie)::text, 1, 1) = (5)::text))
                                                                                                                                                  LIMIT 1)))
             LIMIT 1) AS carga_horaria5,
           ( SELECT he.carga_horaria
             FROM pmieducar.historico_escolar he
             WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.extra_curricular = 1) AND (he.ano = ( SELECT he_1.ano
                                                                                                                                                  FROM pmieducar.historico_escolar he_1
                                                                                                                                                  WHERE ((he_1.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he_1.ativo = 1) AND (he_1.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                                                                                                                                                      FROM pmieducar.historico_escolar hee
                                                                                                                                                                                                                                                                      WHERE ((hee.ref_cod_aluno = he_1.ref_cod_aluno) AND ("substring"((he_1.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he_1.nm_serie)::text, 1, 1) = (6)::text))
                                                                                                                                                  LIMIT 1)))
             LIMIT 1) AS carga_horaria6,
           ( SELECT he.carga_horaria
             FROM pmieducar.historico_escolar he
             WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.extra_curricular = 1) AND (he.ano = ( SELECT he_1.ano
                                                                                                                                                  FROM pmieducar.historico_escolar he_1
                                                                                                                                                  WHERE ((he_1.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he_1.ativo = 1) AND (he_1.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                                                                                                                                                      FROM pmieducar.historico_escolar hee
                                                                                                                                                                                                                                                                      WHERE ((hee.ref_cod_aluno = he_1.ref_cod_aluno) AND ("substring"((he_1.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he_1.nm_serie)::text, 1, 1) = (7)::text))
                                                                                                                                                  LIMIT 1)))
             LIMIT 1) AS carga_horaria7,
           ( SELECT he.carga_horaria
             FROM pmieducar.historico_escolar he
             WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.extra_curricular = 1) AND (he.ano = ( SELECT he_1.ano
                                                                                                                                                  FROM pmieducar.historico_escolar he_1
                                                                                                                                                  WHERE ((he_1.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he_1.ativo = 1) AND (he_1.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                                                                                                                                                      FROM pmieducar.historico_escolar hee
                                                                                                                                                                                                                                                                      WHERE ((hee.ref_cod_aluno = he_1.ref_cod_aluno) AND ("substring"((he_1.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he_1.nm_serie)::text, 1, 1) = (8)::text))
                                                                                                                                                  LIMIT 1)))
             LIMIT 1) AS carga_horaria8,
           ( SELECT he.carga_horaria
             FROM pmieducar.historico_escolar he
             WHERE ((he.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he.ativo = 1) AND (he.extra_curricular = 1) AND (he.ano = ( SELECT he_1.ano
                                                                                                                                                  FROM pmieducar.historico_escolar he_1
                                                                                                                                                  WHERE ((he_1.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (he_1.ativo = 1) AND (he_1.sequencial = ( SELECT max(hee.sequencial) AS max
                                                                                                                                                                                                                                                                      FROM pmieducar.historico_escolar hee
                                                                                                                                                                                                                                                                      WHERE ((hee.ref_cod_aluno = he_1.ref_cod_aluno) AND ("substring"((he_1.nm_serie)::text, 1, 1) = "substring"((hee.nm_serie)::text, 1, 1)) AND (hee.ativo = 1) AND (hee.extra_curricular = 0)))) AND ("substring"((he_1.nm_serie)::text, 1, 1) = (9)::text))
                                                                                                                                                  LIMIT 1)))
             LIMIT 1) AS carga_horaria9
    FROM (pmieducar.historico_disciplinas
        JOIN pmieducar.historico_escolar ON (((historico_escolar.ref_cod_aluno = historico_disciplinas.ref_ref_cod_aluno) AND (historico_escolar.sequencial = historico_disciplinas.ref_sequencial))))
    WHERE ((historico_escolar.extra_curricular = 1) AND (historico_escolar.ativo = 1))
    GROUP BY (public.fcn_upper((historico_disciplinas.nm_disciplina)::text)), historico_disciplinas.ref_ref_cod_aluno
    ORDER BY (public.fcn_upper((historico_disciplinas.nm_disciplina)::text));


--
-- Name: view_modulo; Type: VIEW; Schema: relatorio; Owner: postgres
--

CREATE OR REPLACE VIEW view_modulo AS
    SELECT DISTINCT turma.cod_turma,
        modulo_curso.cod_modulo AS cod_modulo_curso,
        modulo_turma.cod_modulo AS cod_modulo_turma,
        CASE
        WHEN ((curso.padrao_ano_escolar = 0) AND (modulo_turma.cod_modulo IS NOT NULL)) THEN modulo_turma.nm_tipo
        ELSE modulo_curso.nm_tipo
        END AS nome,
        CASE
        WHEN ((curso.padrao_ano_escolar = 0) AND (modulo_turma.cod_modulo IS NOT NULL)) THEN turma_modulo.sequencial
        ELSE ano_letivo_modulo.sequencial
        END AS sequencial
    FROM (((((pmieducar.turma
        JOIN pmieducar.curso ON ((curso.cod_curso = turma.ref_cod_curso)))
        LEFT JOIN pmieducar.ano_letivo_modulo ON (((ano_letivo_modulo.ref_ano = turma.ano) AND (ano_letivo_modulo.ref_ref_cod_escola = turma.ref_ref_cod_escola))))
        LEFT JOIN pmieducar.turma_modulo ON ((turma_modulo.ref_cod_turma = turma.cod_turma)))
        LEFT JOIN pmieducar.modulo modulo_curso ON ((modulo_curso.cod_modulo = ano_letivo_modulo.ref_cod_modulo)))
        LEFT JOIN pmieducar.modulo modulo_turma ON ((modulo_turma.cod_modulo = turma_modulo.ref_cod_modulo)))
    ORDER BY turma.cod_turma, modulo_curso.cod_modulo, modulo_turma.cod_modulo,
        CASE
        WHEN ((curso.padrao_ano_escolar = 0) AND (modulo_turma.cod_modulo IS NOT NULL)) THEN modulo_turma.nm_tipo
        ELSE modulo_curso.nm_tipo
        END,
        CASE
        WHEN ((curso.padrao_ano_escolar = 0) AND (modulo_turma.cod_modulo IS NOT NULL)) THEN turma_modulo.sequencial
        ELSE ano_letivo_modulo.sequencial
        END;


--
-- Data for Name: situacao_matricula; Type: TABLE DATA; Schema: relatorio; Owner: postgres
--

INSERT INTO situacao_matricula (cod_situacao, descricao) VALUES (1, 'Aprovado');
INSERT INTO situacao_matricula (cod_situacao, descricao) VALUES (2, 'Reprovado');
INSERT INTO situacao_matricula (cod_situacao, descricao) VALUES (15, 'Falecido');
INSERT INTO situacao_matricula (cod_situacao, descricao) VALUES (4, 'Transferido');
INSERT INTO situacao_matricula (cod_situacao, descricao) VALUES (6, 'Abandono');
INSERT INTO situacao_matricula (cod_situacao, descricao) VALUES (13, 'Aprovado pelo conselho');
INSERT INTO situacao_matricula (cod_situacao, descricao) VALUES (9, 'Exceto Transferidos/Abandono');
INSERT INTO situacao_matricula (cod_situacao, descricao) VALUES (10, 'Todas');
INSERT INTO situacao_matricula (cod_situacao, descricao) VALUES (14, 'Reprovado por faltas');
INSERT INTO situacao_matricula (cod_situacao, descricao) VALUES (12, 'Ap. Depen.');
INSERT INTO situacao_matricula (cod_situacao, descricao) VALUES (5, 'Reclassificado');
INSERT INTO situacao_matricula (cod_situacao, descricao) VALUES (3, 'Cursando');

--
-- Name: situacao_matricula_pkey; Type: CONSTRAINT; Schema: relatorio; Owner: postgres
--

ALTER TABLE ONLY situacao_matricula
    ADD CONSTRAINT situacao_matricula_pkey PRIMARY KEY (cod_situacao);
