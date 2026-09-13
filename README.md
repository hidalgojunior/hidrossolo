Segue um README.md inicial consolidando os requisitos funcionais e técnicos do projeto.

# Hidrossolo Poços Artesianos - Portal Institucional e Sistema de Gestão

## Visão Geral
Desenvolver um portal institucional moderno para a Hidrossolo Poços Artesianos, integrado a um sistema administrativo interno para gerenciamento de conteúdo, frota, contratos e manutenção.

O sistema deverá ser responsivo, Mobile First, seguro, escalável e preparado para expansão futura, utilizando tecnologias modernas e ambiente containerizado com Docker.

---

# Dados da Empresa
**Razão Social:** Hidrossolo Poços Artesianos

**Endereço:**
R. Assad Haddad, 584
Parque das Indústrias
Marília - SP
CEP: 17519-700

---

# Objetivos do Projeto

## Objetivos Institucionais

- Apresentar a empresa ao mercado.
- Exibir serviços prestados.
- Facilitar o contato com clientes.
- Melhorar a presença digital.
- Gerar solicitações de orçamento.

## Objetivos Administrativos

- Centralizar informações da empresa.
- Controlar frota e equipamentos.
- Gerenciar contratos.
- Controlar gastos de manutenção.
- Possuir auditoria das operações realizadas pelos usuários.

---

# Arquitetura Tecnológica

## Backend

- PHP 8.3+ (PHP puro — sem frameworks)
- Micro-framework MVC próprio (`app/Core`)
- Motor de views próprio em PHP puro (`App\Core\View`)
- API Ready

## Frontend

- Templates PHP puro (sem Blade)
- CSS próprio (`assets/css/app.css`) — sem Bootstrap/Tailwind
- JavaScript vanilla (`assets/js/ui.js`) — sem framework de UI
- Fonte **Tahoma** (com fallback de sistema)
- Mobile First / Progressive Enhancement

## Banco de Dados

- MySQL 8

## Containers

- Docker
- Docker Compose

## Infraestrutura

- Nginx
- Redis (cache e sessões)
- PHP-FPM

---

# Site Institucional

## Página Inicial

### Banner Principal

- Imagem institucional
- Texto de destaque
- Chamada para ação

### Apresentação

- Resumo da empresa
- Diferenciais

### Serviços em Destaque

### Depoimentos

### CTA para orçamento

### Rodapé

- Endereço
- Telefones
- E-mail
- Redes sociais

### Botão Flutuante

- WhatsApp fixo no canto inferior direito

---

## Página Empresa

### História

### Missão

### Visão

### Valores

### Estrutura Operacional

### Certificações

---

## Página Serviços
Cadastro dinâmico de serviços:

- Perfuração de Poços Artesianos
- Licenciamento
- Outorgas
- Limpeza de Poços
- Teste de Vazão
- Instalação de Bombas
- Manutenção Preventiva
- Manutenção Corretiva

Cada serviço deverá possuir:

- Título
- Descrição
- Galeria
- SEO próprio
- URL amigável

---

## Página Contato

### Informações

- Endereço
- Telefone
- WhatsApp
- E-mail

### Horário de Funcionamento

### Formulário de Contato
Campos:

- Nome
- Telefone
- E-mail
- Mensagem

### Google Maps

---

# CMS Próprio
Sistema inspirado na experiência de uso do WordPress, porém desenvolvido integralmente em PHP puro (MVC próprio, sem frameworks).

## Gestão de Conteúdo

### Home

- Editar banners
- Editar textos
- Editar destaques

### Empresa

- História
- Missão
- Visão
- Valores

### Serviços

- Cadastrar
- Editar
- Excluir
- Ativar/Inativar

### Contato

- Dados institucionais
- Horários
- Redes sociais

---

# Sistema de Páginas Dinâmicas
Permitir criação de páginas sem programação.

Exemplos:

- Sustentabilidade
- Projetos
- Trabalhe Conosco
- Parceiros

Campos:

- Título
- Slug
- Conteúdo
- SEO
- Imagens

---

# Sistema de Menus
Permitir:

- Criar menus
- Editar menus
- Reordenar itens
- Criar submenus

---

# Biblioteca de Mídias

- Upload de imagens
- Organização por categorias
- Compressão automática
- Conversão para WEBP
- Exclusão segura

---

# Área Administrativa

## Dashboard
Indicadores:

- Visitantes
- Mensagens recebidas
- Contratos ativos
- Veículos cadastrados
- Manutenções pendentes

---

# Controle de Frota

## Cadastro de Veículos
Campos:

- Placa
- Marca
- Modelo
- Ano
- Renavam
- Chassi
- Combustível
- Quilometragem Atual
- Situação

---

## Controle de Manutenções
Campos:

- Veículo
- Tipo de manutenção
- Oficina
- Data
- Quilometragem
- Valor
- Observações
- Anexos

Tipos:

- Preventiva
- Corretiva

---

## Controle de Abastecimento
Campos:

- Veículo
- Data
- Litros
- Valor
- Quilometragem

---

## Relatórios

- Custo por veículo
- Histórico de manutenção
- Consumo médio
- Custos mensais

---

# Gestão de Contratos

## Cadastro de Contratos
Campos:

- Número
- Cliente
- Responsável
- Data de início
- Data de término
- Valor
- Situação

---

## Controle de Vigência
Alertas automáticos para:

- Contratos próximos do vencimento
- Contratos vencidos

---

## Gestão Documental
Anexar:

- PDF
- Imagens
- Planilhas

---

# Sistema de Usuários
Perfis:

## Administrador
Controle total

## Gestor
Controle operacional

## Editor
Gerenciamento de conteúdo

## Operador
Consulta limitada

---

# Segurança

## Autenticação

- Autenticação própria com sessões PHP
- Bcrypt
- Recuperação de senha

## Proteções

- CSRF
- XSS
- SQL Injection
- Rate Limiting
- Session Security

## Auditoria
Registrar:

- Logins
- Logouts
- Inclusões
- Alterações
- Exclusões

---

# Onboarding
Ao primeiro acesso:

## Passo 1
Cadastro da empresa

## Passo 2
Configuração dos contatos

## Passo 3
Configuração do WhatsApp

## Passo 4
Cadastro dos primeiros usuários

## Passo 5
Cadastro dos primeiros serviços

## Passo 6
Finalização e publicação

---

# SEO

## Configurações Gerais

- Título do site
- Meta Description
- Keywords

## Configurações por Página

- SEO individual
- Open Graph
- URL amigável

## Arquivos

- Sitemap XML
- Robots.txt

---

# LGPD

- Política de Privacidade
- Política de Cookies
- Termos de Uso
- Consentimento de Cookies

---

# Estrutura Inicial de Containers

```
services:

  nginx:
  app:
  mysql:
  phpmyadmin:
  redis:
```

---

# Banco de Dados (Módulos Principais)

```
users
roles
permissions

pages
page_contents

services
service_images

menus
menu_items

media_library

contacts

site_settings

vehicles
vehicle_maintenance
vehicle_fuel

contracts
contract_files

notifications

audit_logs
```

---

# Objetivo Final
Entregar uma plataforma corporativa unificada que combine:

- Site institucional profissional
- CMS próprio
- Controle de frota
- Controle de manutenção
- Gestão de contratos
- Gestão documental
- Segurança corporativa
- Ambiente Docker para desenvolvimento e homologação
- Estrutura preparada para crescimento futuro
