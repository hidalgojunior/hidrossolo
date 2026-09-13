# 🗄️ Hidrossolo - Diagrama do Banco de Dados (DFD)

```
┌──────────────────────────────────────────────────────────────────────┐
│                        HIDROSSOLO DATABASE                           │
│                        Schema: utf8mb4                                │
└──────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────┐
│ 1. USUÁRIOS E PERMISSÕES                                            │
├─────────────────────────────────────────────────────────────────────┤
│                                                                      │
│  ┌──────────┐         ┌──────────┐         ┌───────────────┐        │
│  │  roles   │ 1───N   │  users   │ N───N   │ permissions   │        │
│  │──────────│────────▶│──────────│◀────────│───────────────│        │
│  │ id (PK)  │         │ id (PK)  │         │ id (PK)        │        │
│  │ name     │         │ name     │         │ name           │        │
│  │ desc     │         │ email ✉  │         │ description    │        │
│  └──────────┘         │ password │         └───────────────┘        │
│                       │ role_id FK│                                  │
│                       │ avatar   │                                   │
│                       │ active   │                                   │
│                       │ last_login│                                  │
│                       └────┬─────┘                                  │
│                            │ 1                                       │
│                            │                                         │
│              ┌─────────────┼─────────────┬──────────────┐           │
│              │             │             │              │           │
│              ▼             ▼             ▼              ▼           │
│         ┌─────────┐  ┌─────────┐  ┌───────────┐  ┌──────────┐      │
│         │  pages  │  │  posts  │  │  media_   │  │ audit_   │      │
│         │created_by│ │author_id│  │  library  │  │  logs    │      │
│         └─────────┘  └─────────┘  │uploaded_by│  │ user_id  │      │
│                                   └───────────┘  └──────────┘      │
└─────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────┐
│ 2. CMS - PÁGINAS E CONTEÚDO                                         │
├─────────────────────────────────────────────────────────────────────┤
│                                                                      │
│  ┌──────────┐         ┌────────────────┐                            │
│  │  pages   │ 1───N   │ page_contents  │                            │
│  │──────────│────────▶│────────────────│                            │
│  │ id (PK)  │         │ id (PK)        │                            │
│  │ title    │         │ page_id (FK)   │                            │
│  │ slug ✉   │         │ section        │ ← hero, banners, cta,      │
│  │ content  │         │ title          │   depoimentos, diferenciais │
│  │ excerpt  │         │ subtitle       │   missao, visao, valores    │
│  │ meta_*   │         │ content        │                            │
│  │ status   │         │ image          │                            │
│  │ featured │         │ link_url       │                            │
│  │ image    │         │ sort_order     │                            │
│  └──────────┘         └────────────────┘                            │
│                                                                      │
│  🎯 Seções da Home: hero, servicos_section, cta, banners,           │
│     depoimentos, diferenciais                                        │
│  🏢 Seções da Empresa: missao, visao, valores                        │
└─────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────┐
│ 3. SERVIÇOS                                                         │
├─────────────────────────────────────────────────────────────────────┤
│                                                                      │
│  ┌──────────┐         ┌────────────────┐                            │
│  │ services │ 1───N   │ service_images │                            │
│  │──────────│────────▶│────────────────│                            │
│  │ id (PK)  │         │ id (PK)        │                            │
│  │ title    │         │ service_id(FK) │                            │
│  │ slug ✉   │         │ image_path     │                            │
│  │ desc     │         │ alt_text       │                            │
│  │ content  │         │ sort_order     │                            │
│  │ icon     │         └────────────────┘                            │
│  │ featured │                                                       │
│  │ image    │                                                       │
│  │ active   │                                                       │
│  │ highlight│ ← destaque na home                                    │
│  └──────────┘                                                       │
└─────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────┐
│ 4. MENUS                                                            │
├─────────────────────────────────────────────────────────────────────┤
│                                                                      │
│  ┌──────────┐         ┌──────────────┐                              │
│  │  menus   │ 1───N   │  menu_items  │                              │
│  │──────────│────────▶│──────────────│                              │
│  │ id (PK)  │         │ id (PK)      │                              │
│  │ name     │         │ menu_id (FK) │                              │
│  │ location │         │ parent_id(FK)│ ← auto-ref (submenu)         │
│  └──────────┘         │ title        │                              │
│                       │ url          │                              │
│                       │ target       │                              │
│                       │ icon         │                              │
│                       │ sort_order   │                              │
│                       │ active       │                              │
│                       └──────────────┘                              │
└─────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────┐
│ 5. FROTA (VEÍCULOS)                                                 │
├─────────────────────────────────────────────────────────────────────┤
│                                                                      │
│                       ┌──────────────┐                               │
│                       │  vehicles    │                               │
│                       │──────────────│                               │
│                       │ id (PK)      │                               │
│                       │ plate ✉      │                               │
│                       │ brand        │                               │
│                       │ model        │                               │
│                       │ year         │                               │
│                       │ fuel_type    │                               │
│                       │ current_km   │                               │
│                       │ status       │ ← active|maintenance|inactive │
│                       └──┬───────┬──┘                               │
│                          │ 1     │ 1                                 │
│                          │       │                                   │
│              ┌───────────┘       └──────────┐                        │
│              ▼                              ▼                        │
│  ┌────────────────────┐      ┌────────────────────┐                 │
│  │ vehicle_maintenance│      │   vehicle_fuel     │                 │
│  │────────────────────│      │────────────────────│                 │
│  │ id (PK)            │      │ id (PK)            │                 │
│  │ vehicle_id (FK)    │      │ vehicle_id (FK)    │                 │
│  │ type               │      │ fuel_date          │                 │
│  │ maintenance_date   │      │ liters             │                 │
│  │ km_at_maintenance  │      │ cost               │                 │
│  │ cost               │      │ km_at_refuel       │                 │
│  │ description        │      └────────────────────┘                 │
│  └────────────────────┘                                             │
└─────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────┐
│ 6. CONTRATOS                                                        │
├─────────────────────────────────────────────────────────────────────┤
│                                                                      │
│  ┌───────────┐        ┌────────────────┐                             │
│  │ contracts │ 1───N  │ contract_files │                             │
│  │───────────│───────▶│────────────────│                             │
│  │ id (PK)   │        │ id (PK)        │                             │
│  │ number ✉  │        │ contract_id(FK)│                             │
│  │ client    │        │ filename       │                             │
│  │ start_date│        │ original_name  │                             │
│  │ end_date  │        │ file_path      │                             │
│  │ value     │        │ mime_type      │                             │
│  │ status    │        │ file_size      │                             │
│  └───────────┘        └────────────────┘                             │
└─────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────┐
│ 7. SUPORTE                                                          │
├─────────────────────────────────────────────────────────────────────┤
│                                                                      │
│  ┌───────────────┐    ┌───────────────────┐    ┌──────────────┐     │
│  │ site_settings │    │  notifications    │    │  audit_logs  │     │
│  │───────────────│    │───────────────────│    │──────────────│     │
│  │ id (PK)       │    │ id (PK)           │    │ id (PK)      │     │
│  │ key ✉         │    │ user_id (FK)      │    │ user_id (FK) │     │
│  │ value         │    │ title             │    │ action       │     │
│  │ group         │    │ message           │    │ entity_type  │     │
│  │ type          │    │ type              │    │ entity_id    │     │
│  └───────────────┘    │ link              │    │ old_values   │     │
│                       │ read_at           │    │ new_values   │     │
│  ┌───────────────┐    └───────────────────┘    │ ip_address   │     │
│  │   contacts    │                              └──────────────┘     │
│  │───────────────│                                                    │
│  │ id (PK)       │    ┌───────────────────┐                          │
│  │ name          │    │  media_library    │                          │
│  │ email         │    │───────────────────│                          │
│  │ phone         │    │ id (PK)           │                          │
│  │ message       │    │ filename          │                          │
│  │ status        │    │ original_name     │                          │
│  │ ip_address    │    │ mime_type         │                          │
│  └───────────────┘    │ file_path         │                          │
│                       │ thumbnail_path    │                          │
│  ┌───────────────┐    │ category          │                          │
│  │    posts      │    │ uploaded_by (FK)  │                          │
│  │───────────────│    └───────────────────┘                          │
│  │ id (PK)       │                                                    │
│  │ title         │                                                    │
│  │ slug ✉        │                                                    │
│  │ content       │                                                    │
│  │ excerpt       │                                                    │
│  │ author_id(FK) │                                                    │
│  │ status        │                                                    │
│  │ published_at  │                                                    │
│  └───────────────┘                                                    │
└─────────────────────────────────────────────────────────────────────┘

## 📊 Resumo das Tabelas

| # | Tabela | Registros | Função |
|---|--------|-----------|--------|
| 1 | `roles` | 5 | Perfis de usuário |
| 2 | `users` | 2 | Usuários do sistema |
| 3 | `permissions` | 0 | Permissões RBAC |
| 4 | `site_settings` | ~15 | Configurações dinâmicas |
| 5 | `contacts` | 0 | Formulário de contato |
| 6 | `pages` | 4 | Páginas CMS |
| 7 | `page_contents` | ~15 | Seções das páginas |
| 8 | `services` | 0 | Serviços oferecidos |
| 9 | `service_images` | 0 | Imagens dos serviços |
| 10 | `menus` | 2 | Menus de navegação |
| 11 | `menu_items` | 10 | Itens dos menus |
| 12 | `media_library` | 0 | Uploads de mídia |
| 13 | `posts` | 0 | Blog posts |
| 14 | `vehicles` | 0 | Frota de veículos |
| 15 | `vehicle_maintenance` | 0 | Manutenções |
| 16 | `vehicle_fuel` | 0 | Abastecimentos |
| 17 | `contracts` | 0 | Contratos |
| 18 | `contract_files` | 0 | Anexos de contratos |
| 19 | `notifications` | 0 | Notificações |
| 20 | `audit_logs` | 0 | Logs de auditoria |

## 🔗 Principais Relacionamentos

```
users ──▶ pages (created_by)
users ──▶ posts (author_id)
users ──▶ media_library (uploaded_by)
users ──▶ audit_logs (user_id)
users ──▶ notifications (user_id)

pages ──▶ page_contents (page_id) CASCADE
services ──▶ service_images (service_id) CASCADE
menus ──▶ menu_items (menu_id) CASCADE
menu_items ──▶ menu_items (parent_id) SET NULL
vehicles ──▶ vehicle_maintenance (vehicle_id) CASCADE
vehicles ──▶ vehicle_fuel (vehicle_id) CASCADE
contracts ──▶ contract_files (contract_id) CASCADE
```
