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

# Biblioteca de Mídias

Todos os arquivos enviados pelo painel ficam centralizados em `/admin/midia` e podem ser
reutilizados em qualquer parte do site.

## Fluxo

1. Envie um ou vários arquivos em **Biblioteca de Mídias** (arrastar e soltar ou selecionar).
2. Os arquivos são gravados em `assets/uploads/AAAA/MM/` e registrados na tabela `media_library`.
3. Nos formulários, use o **campo de mídia** (botões *Biblioteca* / *Enviar*) para escolher a imagem.
4. Também é possível copiar o caminho gerado (ex.: `/assets/uploads/2026/09/foto.jpg`) e usá-lo em qualquer campo.

## Recursos

- Upload múltiplo com arrastar e soltar e barra de progresso
- Miniaturas WebP geradas automaticamente
- Busca por nome/descrição e filtros por tipo e categoria
- Edição de nome, texto alternativo (acessibilidade/SEO) e categoria
- **Escanear servidor**: registra imagens já existentes no projeto
- Exclusão segura: só remove arquivos de `assets/uploads`; imagens do tema são apenas desvinculadas

## Reutilização no site

O campo de mídia é um componente reutilizável:

```php
<?= $view->partial('admin.components.media-field', [
    'name'  => 'featured_image',
    'label' => 'Imagem de Destaque',
    'value' => $servico['featured_image'] ?? '',
]) ?>
```

O seletor (modal) é carregado globalmente pelo layout do admin, então funciona em
qualquer formulário — inclusive em linhas criadas dinamicamente por JavaScript
(basta chamar `MediaPicker.initFields(container)`).

## Permissões

O PHP roda como `www-data` (uid 82) no container Alpine. A pasta de uploads precisa
ser gravável por ele — o `start.sh` já ajusta isso automaticamente:

```bash
mkdir -p assets/uploads
chown -R 82:82 assets/uploads && chmod -R 775 assets/uploads
```

O nginx também define `client_max_body_size 64M` para acomodar arquivos de até 10MB.

# Área do Motorista

Papel dedicado (`motorista`) com acesso **exclusivo** a `/motorista`, onde só é possível
lançar abastecimentos e manutenções. O motorista escolhe o veículo/equipamento no momento
do lançamento; ele não acessa nenhuma outra tela do painel.

- Rota: `/motorista` (layout próprio, mobile-first)
- Middleware: `App\Middleware\MotoristaMiddleware`
- Ao fazer login, o motorista é redirecionado automaticamente para a área dele
- Tentativas de acessar `/admin` são devolvidas para `/motorista`
- Cada lançamento grava `user_id`, alimenta a auditoria e atualiza o odômetro/horímetro do ativo

# Frota & Equipamentos

Veículos e equipamentos (geradores, compressores...) compartilham a tabela `vehicles`,
diferenciados pela coluna `category`.

- `/admin/frota` — cadastro e listagem com filtro por tipo
- `/admin/frota/relatorios` — consumo de combustível e custos de manutenção por ativo,
  com filtro de período, litros, R$/km, km/L, ranking de maior custo e gráficos
- O dashboard inicial traz um resumo do mês (combustível, manutenção, ativos) e os maiores custos

# Contratos: modelos com variáveis

1. Em **Modelos de contrato** (`/admin/contratos/modelos`) escreva o texto com coringas
   como `{{cliente}}`, `{{documento}}`, `{{valor}}`, `{{data_inicio}}`.
2. Ao criar um contrato (`/admin/contratos/novo`), escolha o modelo: os campos das
   variáveis aparecem automaticamente para preencher.
3. As variáveis de cliente, número, valor e datas são preenchidas sozinhas a partir do cadastro.
4. Gere o documento em `/admin/contratos/documento/{id}` (pronto para imprimir) ou baixe
   o PDF em `/admin/contratos/pdf/{id}` (gerado no servidor com **dompdf**).

Variáveis automáticas: `cliente`, `contratante`, `numero_contrato`, `responsavel`,
`valor`, `data_inicio`, `data_fim`, `data_hoje`, `cidade`, `empresa`, `endereco_empresa`,
`telefone_empresa`, `email_empresa`.

# Segurança e Governança

Implementado em `app/Core/Security.php` (carregado no bootstrap da aplicação):

- **Headers**: CSP restritiva, `X-Frame-Options`, `X-Content-Type-Options`,
  `Referrer-Policy`, `Permissions-Policy`, COOP e HSTS (quando HTTPS)
- **Sessão**: cookies HttpOnly + SameSite=Lax, `use_strict_mode`, ID de 48 caracteres,
  rotação de ID no login, expiração por inatividade (2h) e limite absoluto (12h)
- **Força bruta**: máx. 5 falhas por e-mail e 12 por IP em 15 min → bloqueio de 15 min
- **Política de senha**: 10+ caracteres, com letra e número, sem dados do e-mail e sem senhas comuns
- **Auditoria**: login, login falho, bloqueio, logout, troca de senha, criação/edição de
  veículos, equipamentos, contratos, modelos e lançamentos do motorista
- **Uploads**: nomes aleatórios, validação de extensão/tamanho e verificação de scripts
  executáveis na pasta pública (relatório em `/admin/seguranca`)
- **Painel de segurança** (`/admin/seguranca`): mostra o estado real das proteções,
  falhas de login nas últimas 24h, IPs suspeitos e contas com senha antiga

> Em produção, habilite HTTPS para o HSTS entrar em ação e mantenha o
> `client_max_body_size` do nginx coerente com o limite de upload.

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
