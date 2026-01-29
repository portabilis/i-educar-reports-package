# Guia de Remoção do Pacote i-educar-reports-package

## Estrutura de Arquivos

| Arquivo | Função |
|---------|--------|
| `database/migrations/2025_01_29_000000_uninstall_reports_package.php` | Migration principal |
| `database/sqls/uninstall/uninstall_reports_package.sql` | SQL executado pela migration |

## O que será removido

- 58 migrations do pacote
- 1 View: `relatorio.view_historico_eja`
- 2 Functions do pacote: `relatorio.media_anual_modelo1`, `relatorio.retorna_situacao_matricula_componente`
- 10 Functions legadas: `formata_nome`, `get_media_recuperacao_semestral`, `get_qtde_alunos_situacao`, etc.
- 55+ menus e suas permissões

## O que NÃO será removido

- `relatorio.view_dados_escola` - **Gerenciada pelo i-Educar Core** (migrations em `database/migrations/report/`)