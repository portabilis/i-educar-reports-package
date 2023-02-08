CREATE OR REPLACE FUNCTION relatorio.retorna_situacao_matricula_componente(cod_situacao_matricula numeric, cod_situacao_componente numeric) RETURNS character varying
    LANGUAGE plpgsql
AS $$
DECLARE
    texto_situacao varchar := '';
BEGIN

    IF cod_situacao_matricula IN (4,5,6,15) THEN
        texto_situacao := (CASE
                               WHEN (cod_situacao_matricula = 4) THEN 'Transferido'
                               WHEN (cod_situacao_matricula = 5) THEN 'Reclassificado'
                               WHEN (cod_situacao_matricula = 6) THEN 'Abandono'
                               WHEN (cod_situacao_matricula = 15) THEN 'Falecido'
            END);
        RETURN texto_situacao;
    END IF;

    IF cod_situacao_componente IS NULL THEN
        RETURN '';
    END IF;

    RETURN (SELECT relatorio.get_situacao_componente(cod_situacao_componente));
END;
$$;
