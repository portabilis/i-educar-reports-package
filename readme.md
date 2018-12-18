# Relatórios do i-Educar

## Instalação

Para adicionar os relatórios ao i-Educar, faça o clone deste repositório em 
`ieducar/modules/Reports`:

```bash
git clone https://github.com/portabilis/i-educar-reports-package.git ieducar/modules/Reports
```

Logo após, instale e rode as migrations:

```bash
docker-compose exec php artisan reports:install
docker-compose exec php artisan migrate
```
