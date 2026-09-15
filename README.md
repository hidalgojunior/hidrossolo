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

**Telefone fixo:** (14) 3413-7789  
**WhatsApp:** (14) 99789-4727  
**E-mail:** hidrossolo@hidrossolopocos.com.br  
**Horário:** Seg a Sex: 08h às 17h

> Todos esses dados são cadastrados em **Admin → CMS → Contato** e existe uma
> **fonte única**: o que for salvo ali aparece automaticamente na página de
> contato, no rodapé de todas as páginas, nas páginas legais, no JSON-LD (SEO)
> e no cabeçalho dos relatórios em PDF.

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
- Telefone fixo
- WhatsApp
- E-mail

São os mesmos dados do rodapé (fonte única). Telefone, e-mail e endereço são
**clicáveis**: o telefone abre a discagem (`tel:`), o e-mail abre o cliente de
correio (`mailto:`) e o endereço abre a rota no mapa (`Google Maps`) — útil
para quem acessa pelo celular.

### Horário de Funcionamento

### Redes sociais

Ícones exibidos no rodapé de todas as páginas, conforme o que estiver
cadastrado no CMS.

### Formulário de Contato
Campos:

- Nome
- Telefone
- E-mail
- Mensagem

### Google Maps

O mapa embutido é montado a partir do **endereço cadastrado no CMS** (ao lado
do formulário), e também há o link de rota mencionado acima em
[Informações](#informações).

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

- Dados institucionais (endereço, telefone fixo, WhatsApp, e-mail, horário)
- Horários de funcionamento
- Logo da marca (usada no site e nos relatórios em PDF)
- Título e texto do formulário
- **Redes sociais** — Instagram, Facebook, YouTube, LinkedIn, TikTok e X
  (Twitter), além de **Outras redes** livres, uma por linha no formato
  `Nome | endereço`. Rede deixada em branco não aparece no site.

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

## Vídeos do YouTube

Vídeos entram na biblioteca **por link do YouTube**, sem hospedar o arquivo:\nao colar o endereço do vídeo, o sistema guarda a URL, busca a capa
automaticamente e passa a exibir um player no visualizador. Assim vídeos
maiores (ou até transmissões já encerradas) ficam disponíveis sem consumir
espaço nem banda da hospedagem.

Formatos aceitos ao colar: `youtube.com/watch?v=…`, `youtu.be/…`,
`youtube.com/shorts/…` (Shorts), `/embed/…`, `/live/…`, `/v/…` e o ID puro.
Links de outros serviços são recusados, e o mesmo vídeo não pode ser
cadastrado duas vezes.

Há um filtro **Vídeos** na biblioteca. O recurso **não exige alteração no
banco**: o tipo fica no `mime_type` (`video/youtube`) e o ID é lido da própria
URL.

> O upload direto de arquivo de vídeo **não** é permitido (decisão do
> projeto): os arquivos enviados continuam limitados a 10MB e aos formatos de
> imagem/documento.

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

# Páginas Legais (LGPD)

Páginas públicas de conformidade, com layout moderno (índice lateral fixo, tabelas,
cards de direitos e versão de impressão):

| Rota | Página |
|---|---|
| `/politica-de-privacidade` (alias `/politica-privacidade`) | Política de Privacidade — 18 cláusulas: controlador, dados coletados, bases legais (art. 7º), compartilhamento, retenção, segurança, direitos do titular, cookies, foro |
| `/politica-de-cookies` | Política de Cookies — tipos, tabela dos cookies realmente usados e revogação de consentimento |
| `/termos-de-uso` | Termos de Uso — regras de utilização, orçamentos, propriedade intelectual, alerta anti-phishing |
| `/lgpd` | Central LGPD — princípios, governança (ROPA/auditoria) e **formulário de solicitação do titular** com protocolo |

- Rotas em `routes/legal.php`, carregado **antes** de `routes/web.php` (o catch-all `/{slug}` capturaria as URLs).
- Controller: `App\Controllers\LegalController` · CSS: `assets/css/legal.css`
- **Solicitações do titular** ficam na tabela `lgpd_requests` (com protocolo, IP e user-agent) e
  podem ser analisadas/respondidas em **`/admin/lgpd`**, com alerta de prazo (>14 dias) e atualização de status.
- Formulário protegido por CSRF e por *honeypot* anti-robô.

> Os textos são um **modelo de conformidade** alinhado à LGPD (Lei nº 13.709/2018) e devem ser
> revisados pelo jurídico antes da publicação oficial, incluindo CNPJ e designação formal do encarregado (DPO).

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

# Solicitações de orçamento

## Formulário público (`/orcamento`)

Campos: nome, e-mail, telefone, tipo de serviço, endereço do serviço,
**data desejada** e descrição do que precisa. A solicitação entra no sistema
com a situação inicial **Nova** e o visitante é levado para a página de
agradecimento.

## Central de orçamentos (`/admin/orcamentos`)

- Lista das solicitações com filtro por situação
- Detalhe com todos os dados enviados
- Situações: **Nova**, **Em análise**, **Respondida**, **Aprovada** e
  **Recusada** — abrir uma solicitação ainda nova já a move para *Em análise*
- Botões para **responder por e-mail** e **chamar no WhatsApp** direto para o
  contato do cliente
- Exclusão da solicitação

> A tabela `orcamentos` é criada automaticamente na primeira solicitação, para
a funcionar também em hospedagem sem acesso a terminal.

---

# Gestão operacional e financeira

## Padrões de data e moeda

- **Datas sempre em `dd/mm/aaaa`** na tela e `aaaa-mm-dd` no banco. Os campos
  usam máscara própria (`data-date-br`, em `assets/js/ui.js`) em vez do
  `<input type="date">` nativo, que é exibido no formato do idioma do
  navegador — em navegador configurado em inglês aparece `mm/dd/yyyy`.
- **Fuso horário:** `America/Sao_Paulo` (GMT-3), definido na inicialização da
  aplicação.
- **Valores:** moeda brasileira — `R$ 1.234,56` na tela e `R$ #,##0.00` nos
  arquivos exportados.

## Agenda & compromissos (`/admin/agenda`)

Calendário mensal único que reúne, no mesmo dia:

- **manutenções** da frota (revisão, inspeção, outros compromissos);
- **contas a pagar** e **contas a receber** da empresa.

Assim é possível enxergar de uma vez o que precisa ser pago, quando, e o que
está programado para a frota. A tela traz os indicadores do mês
(a receber, a pagar, saldo previsto e contas em atraso), as listas de
**contas a vencer em 30 dias** e **contas em atraso**, e permite criar tanto um
compromisso de manutenção quanto um lançamento financeiro direto no calendário
(basta clicar no dia).

Avisos automáticos são gerados **30, 15 e 7 dias** antes de cada compromisso.

## Fluxo de caixa (`/admin/financeiro`)

Contas a pagar e a receber com categorias por tipo de lançamento:

- **Saídas**: combustível, manutenção, peças, pneus, salários, encargos,
  impostos, aluguel, utilidades, licenças/outorgas, terceiros, frete,
  marketing, seguros, tarifas bancárias, administrativas e outras.
- **Entradas**: perfuração, limpeza, manutenção de poço, outorga e
  licenciamento, laudos, materiais, locação, projetos e outras receitas.

Recursos:

- baixa (pagamento/recebimento) com data e forma de pagamento;
- repetição **semanal, mensal, trimestral ou anual**, com geração automática do
  próximo lançamento ao dar baixa;
- vínculo opcional com um veículo/equipamento (custo por ativo);
- filtros por mês, tipo, situação, ativo e busca livre;
- gráfico de entradas × saídas do ano e resumo por categoria.

## Notificações (`/admin/notificacoes`)

Central acessível pelo sino da barra superior. Reúne os avisos de manutenção,
alertas de contas a vencer e em atraso e eventos de segurança. Permite abrir o
registro relacionado (marca como lida), marcar/desmarcar como lida, limpar as
já lidas e reprocessar os avisos de manutenção pendentes.

## Exportação em PDF e Excel (XLSX)

Praticamente todas as telas operacionais têm **Exportar PDF** e **Exportar
Excel**. Não é CSV: as planilhas são `.xlsx` reais, com cabeçalho colorido
congelado, filtro automático, largura de colunas, bordas, linhas zebradas,
linha de totais e formatos nativos de moeda (`R$ #,##0.00`) e data
(`dd/mm/yyyy`) — abre direto no Excel, LibreOffice e Google Planilhas.

Os PDFs são gerados em A4 (paisagem ou retrato) com cabeçalho trazendo a
**logo cadastrada no CMS** (Admin → CMS → Contato), cartões de resumo, tabela
formatada e rodapé com numeração de páginas.

Telas com exportação: **agenda**, **fluxo de caixa** (3 abas no Excel),
**frota & equipamentos**, **relatórios de consumo** (2 abas),
**manutenções** e **abastecimentos**.

Os filtros aplicados na tela são respeitados no arquivo exportado.

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

---

**Desenvolvido por Arnaldo Martins Hidalgo Junior** — hidalgojunior@gmail.com
