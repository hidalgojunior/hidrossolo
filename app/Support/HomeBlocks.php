<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Catálogo dos blocos da Home.
 *
 * Cada bloco tem um partial em `views/pages/home-blocks/{chave}.php` e um
 * registro em `page_blocks` (ordem + exibição), gerenciável em
 * Admin → Home → Blocos da Home.
 */
final class HomeBlocks
{
    /**
     * Ordem padrão (mesma ordem histórica da página).
     *
     * @var array<int,string>
     */
    public const PADRAO = [
        'hero',
        'estatisticas',
        'servicos',
        'diferenciais',
        'como_trabalhamos',
        'secoes',
        'depoimentos',
        'parceiros',
        'cta',
    ];

    /**
     * Rótulos e observações exibidos no painel.
     *
     * @return array<string,array{label:string,icon:string,hint:string}>
     */
    public static function catalogo(): array
    {
        return [
            'hero' => [
                'label' => 'Hero (banner principal)',
                'icon' => 'bi-stars',
                'hint' => 'Título, subtítulo e botão principal do topo da página.',
            ],
            'estatisticas' => [
                'label' => 'Estatísticas',
                'icon' => 'bi-graph-up',
                'hint' => 'Faixa com números da empresa (anos de experiência, poços perfurados...).',
            ],
            'servicos' => [
                'label' => 'Serviços em destaque',
                'icon' => 'bi-droplet',
                'hint' => 'Cards dos serviços marcados como destaque em Serviços.',
            ],
            'diferenciais' => [
                'label' => 'Diferenciais',
                'icon' => 'bi-patch-check',
                'hint' => 'Aparece quando existem diferenciais cadastrados.',
            ],
            'como_trabalhamos' => [
                'label' => 'Como trabalhamos',
                'icon' => 'bi-diagram-3',
                'hint' => 'Etapas do processo de trabalho.',
            ],
            'secoes' => [
                'label' => 'Seções livres (CMS)',
                'icon' => 'bi-layout-text-window-reverse',
                'hint' => 'Posição das seções que você cria em “Seções da Home”.',
            ],
            'depoimentos' => [
                'label' => 'Depoimentos',
                'icon' => 'bi-chat-quote',
                'hint' => 'Aparece quando existem depoimentos cadastrados.',
            ],
            'parceiros' => [
                'label' => 'Parceiros e divulgações',
                'icon' => 'bi-badge-ad',
                'hint' => 'Aparece quando existem banners/divulgações cadastrados.',
            ],
            'cta' => [
                'label' => 'Chamada final (CTA)',
                'icon' => 'bi-send',
                'hint' => 'Bloco de contato no fim da página.',
            ],
        ];
    }

    public static function label(string $block): string
    {
        return self::catalogo()[$block]['label'] ?? ucfirst(str_replace('_', ' ', $block));
    }

    public static function icone(string $block): string
    {
        return self::catalogo()[$block]['icon'] ?? 'bi-square';
    }

    public static function dica(string $block): string
    {
        return self::catalogo()[$block]['hint'] ?? '';
    }

    /**
     * @return array<int,array{block:string,enabled:int}>
     */
    public static function padrao(): array
    {
        return array_map(
            static fn(string $block): array => ['block' => $block, 'enabled' => 1],
            self::PADRAO
        );
    }
}
