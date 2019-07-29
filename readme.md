# Relatórios do i-Educar

Os relatórios utilizam a biblioteca [JasperReports](https://community.jaspersoft.com/project/jasperreports-library), 
desenvolvida em Java para renderizar os arquivos em PDF.

Para intermediar a conexão entre PHP e Java, é utilizada a biblioteca [JasperStarter](http://jasperstarter.cenote.de/).

## Dependências

- É necessário que o PHP tenha permissão para executar as funções `exec` e `passthru`.
- Ter o [OpenJDK](https://openjdk.java.net/) instalado no servidor.

## Instalação

Para adicionar os relatórios ao i-Educar clone este repositório em 
`ieducar/modules/Reports` e faça a instalação:

```bash
# Execute este comando na raiz do projeto i-educar
git clone https://github.com/portabilis/i-educar-reports-package.git ieducar/modules/Reports

# (Docker) docker-compose exec php artisan reports:install
php artisan reports:install
```

## Perguntas frequentes (FAQ)

Algumas perguntas aparecem recorrentemente. Olhe primeiro por aqui: 
[FAQ](https://github.com/portabilis/i-educar-website/blob/master/docs/faq.md).

---

Powered by [Portabilis Tecnologia](http://www.portabilis.com.br/).
