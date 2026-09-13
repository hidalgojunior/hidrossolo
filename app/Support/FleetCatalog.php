<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Catálogo de tipos de serviço de manutenção e combustíveis.
 */
final class FleetCatalog
{
    /**
     * Categorias de serviço de manutenção.
     *
     * @return array<string,array{label:string,icon:string}>
     */
    public static function servicos(): array
    {
        return [
            'revision' => ['label' => 'Revisão geral', 'icon' => 'bi-clipboard-check'],
            'oil' => ['label' => 'Troca de óleo e filtros', 'icon' => 'bi-droplet-half'],
            'tires' => ['label' => 'Troca / reparo de pneus', 'icon' => 'bi-circle-square'],
            'brakes' => ['label' => 'Freios', 'icon' => 'bi-exclamation-octagon'],
            'suspension' => ['label' => 'Suspensão', 'icon' => 'bi-sliders'],
            'electrical' => ['label' => 'Elétrica', 'icon' => 'bi-lightning-charge'],
            'engine' => ['label' => 'Motor', 'icon' => 'bi-gear-wide-connected'],
            'hydraulic' => ['label' => 'Hidráulica / bomba', 'icon' => 'bi-water'],
            'bodywork' => ['label' => 'Funilaria / lataria', 'icon' => 'bi-hammer'],
            'other' => ['label' => 'Outros', 'icon' => 'bi-tools'],
        ];
    }

    public static function servicoLabel(?string $chave): string
    {
        if ($chave === null || $chave === '') {
            return '—';
        }

        return self::servicos()[$chave]['label'] ?? $chave;
    }

    public static function servicoIcon(?string $chave): string
    {
        if ($chave === null || $chave === '') {
            return 'bi-tools';
        }

        return self::servicos()[$chave]['icon'] ?? 'bi-tools';
    }

    /**
     * Sugestões de itens para agilizar o lançamento (inclui pneus).
     *
     * @return array<string,string[]>
     */
    public static function sugestoesItens(): array
    {
        return [
            'tires' => ['Pneu dianteiro esquerdo', 'Pneu dianteiro direito', 'Pneu traseiro esquerdo',
                'Pneu traseiro direito', 'Pneu estepe', 'Balanceamento', 'Alinhamento', 'Válvula', 'Reparo de furo'],
            'oil' => ['Óleo do motor', 'Filtro de óleo', 'Filtro de ar', 'Filtro de combustível', 'Filtro de cabine'],
            'brakes' => ['Pastilha de freio', 'Disco de freio', 'Lona de freio', 'Fluido de freio'],
            'revision' => ['Revisão preventiva', 'Verificação geral', 'Troca de correia'],
            'electrical' => ['Bateria', 'Alternador', 'Lâmpada', 'Farol'],
            'hydraulic' => ['Bomba submersa', 'Cabo de aço', 'Vedação'],
        ];
    }

    /**
     * Tipos de combustível.
     *
     * @return array<string,string>
     */
    public static function combustiveis(): array
    {
        return [
            'diesel' => 'Diesel',
            'gasoline' => 'Gasolina',
            'ethanol' => 'Etanol',
            'flex' => 'Flex',
            'electric' => 'Elétrico',
        ];
    }
}
