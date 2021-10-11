# Relatórios do i-Educar

Os relatórios utilizam a biblioteca [JasperReports](https://community.jaspersoft.com/project/jasperreports-library)
desenvolvida em Java para renderizar os arquivos em PDF.

Para intermediar a conexão entre PHP e Java é utilizada a biblioteca [JasperStarter](http://jasperstarter.cenote.de/).

## Dependências

- PHP ter permissão para executar as funções `exec` e `passthru` no servidor.
- [OpenJDK](https://openjdk.java.net/) 8 instalado no servidor.

## Instalação

Para adicionar o pacote de relatórios execute estes comandos na raiz do projeto i-Educar:

```bash
git clone https://github.com/portabilis/i-educar-reports-package.git packages/portabilis/i-educar-reports-package

# (Docker) docker-compose exec php composer plug-and-play:update
composer plug-and-play:update

# (Docker) docker-compose exec php artisan community:reports:link
php artisan community:reports:link

# (Docker) docker-compose exec php artisan reports:install
php artisan reports:install
```

## Perguntas frequentes (FAQ)

Algumas perguntas aparecem recorrentemente. Olhe primeiro por aqui:
[FAQ](https://github.com/portabilis/i-educar-website/blob/master/docs/faq.md).

---

Powered by [Portabilis Tecnologia](http://www.portabilis.com.br/).
