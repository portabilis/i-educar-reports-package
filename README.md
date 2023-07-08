# i-Educar Relatórios

Módulo de relatórios para o [i-Educar](https://github.com/portabilis/i-educar).

## Dependências

Os relatórios utilizam a biblioteca [JasperReports](https://community.jaspersoft.com/project/jasperreports-library)
desenvolvida em Java para renderizar os arquivos em PDF.

Para intermediar a conexão entre PHP e Java é utilizada a biblioteca [JasperStarter](http://jasperstarter.cenote.de/).

- PHP ter permissão para executar as funções `exec` e `passthru` no servidor.
- [OpenJDK](https://openjdk.java.net/) 8 instalado no servidor.

## Instalação

> Para usuários Docker, executar os comandos `# (Docker)` ao invés da linha seguinte.

Clone este repositório a partir da raiz do i-Educar:

```bash
git clone git@github.com:portabilis/i-educar-reports-package.git packages/portabilis/i-educar-reports-package
```

Instale o pacote:

```bash
# (Docker) docker-compose exec php composer plug-and-play
composer plug-and-play
```

Execute as migrações:

```bash
# (Docker) docker-compose exec php artisan community:reports:install
php artisan community:reports:install
```

## Perguntas frequentes (FAQ)

Algumas perguntas aparecem recorrentemente. Olhe primeiro por aqui:
[FAQ](https://github.com/portabilis/i-educar-website/blob/master/docs/faq.md).

---

Powered by [Portábilis](https://portabilis.com.br/).
