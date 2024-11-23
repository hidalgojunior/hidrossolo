# Hidrossolo CMS

Bem-vindo ao Hidrossolo CMS, um sistema de gerenciamento de conteúdo personalizado para empresas de perfuração de poços artesianos.

## Requisitos
* PHP 7.4 ou superior
* MySQL 5.7 ou superior
* Git

## Instalação
* Clone o repositório:

git clone https://github.com/hidalgojunior/hidrossolo.git

cd hidrossolo


* Execute o script de instalação:

php scripts/install.php

* Siga as instruções na tela para configurar o banco de dados.
* Após a instalação, inicie o servidor PHP embutido para desenvolvimento:

php -S localhost:8000 -t public

* Acesse o CMS em seu navegador: http://localhost:8000

# Estrutura do Projeto

public/: Arquivos acessíveis publicamente

src/: Código-fonte da aplicação

config/: Arquivos de configuração

controllers/: Controladores da aplicação

models/: Modelos de dados

views/: Templates e arquivos de visualização

scripts/: Scripts de utilidade, incluindo o instalador

# Contribuindo
Contribuições são bem-vindas! Por favor, leia nossas diretrizes de contribuição antes de enviar pull requests.

# Suporte
Se você encontrar algum problema ou tiver dúvidas, por favor, abra uma issue no GitHub.

# Licença
Este projeto está licenciado sob a MIT License.