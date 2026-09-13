<?php

declare(strict_types=1);

namespace App\Support;

use App\Core\App;
use App\Core\Database;

/**
 * Fluxo de caixa da empresa: categorias, formas de pagamento e resumos.
 *
 * Os lançamentos (contas a pagar e a receber) ficam em `financial_entries` e
 * aparecem também no calendário, junto com a agenda de manutenção da frota.
 */
final class Financeiro
{
    public const CATEGORIAS_DESPESA = [
        'combustivel' => ['label' => 'Combustível', 'icon' => 'bi-fuel-pump'],
        'manutencao' => ['label' => 'Manutenção de veículos', 'icon' => 'bi-tools'],
        'pecas' => ['label' => 'Peças e materiais', 'icon' => 'bi-nut'],
        'pneus' => ['label' => 'Pneus', 'icon' => 'bi-circle'],
        'salarios' => ['label' => 'Salários', 'icon' => 'bi-people'],
        'encargos' => ['label' => 'Encargos e benefícios', 'icon' => 'bi-shield-check'],
        'impostos' => ['label' => 'Impostos e taxas', 'icon' => 'bi-bank'],
        'aluguel' => ['label' => 'Aluguel', 'icon' => 'bi-house-door'],
        'utilidades' => ['label' => 'Água, energia e internet', 'icon' => 'bi-lightning-charge'],
        'licencas' => ['label' => 'Licenças e outorgas', 'icon' => 'bi-file-earmark-check'],
        'terceiros' => ['label' => 'Serviços de terceiros', 'icon' => 'bi-briefcase'],
        'transporte' => ['label' => 'Frete e transporte', 'icon' => 'bi-truck'],
        'marketing' => ['label' => 'Marketing e publicidade', 'icon' => 'bi-megaphone'],
        'seguros' => ['label' => 'Seguros', 'icon' => 'bi-shield'],
        'bancos' => ['label' => 'Tarifas bancárias e juros', 'icon' => 'bi-credit-card'],
        'escritorio' => ['label' => 'Despesas administrativas', 'icon' => 'bi-clipboard'],
        'outros' => ['label' => 'Outras despesas', 'icon' => 'bi-three-dots'],
    ];

    public const CATEGORIAS_RECEITA = [
        'perfuracao' => ['label' => 'Perfuração de poços', 'icon' => 'bi-water'],
        'limpeza' => ['label' => 'Limpeza de poços', 'icon' => 'bi-droplet'],
        'manutencao_poco' => ['label' => 'Manutenção de poços', 'icon' => 'bi-tools'],
        'outorga' => ['label' => 'Outorga e licenciamento', 'icon' => 'bi-file-earmark-text'],
        'laudos' => ['label' => 'Laudos e análises', 'icon' => 'bi-clipboard-data'],
        'materiais' => ['label' => 'Venda de materiais', 'icon' => 'bi-box-seam'],
        'locacao' => ['label' => 'Locação de equipamentos', 'icon' => 'bi-truck-front'],
        'projetos' => ['label' => 'Projetos e consultoria', 'icon' => 'bi-rulers'],
        'outras_receitas' => ['label' => 'Outras receitas', 'icon' => 'bi-three-dots'],
    ];

    public const FORMAS_PAGAMENTO = [
        'pix' => 'PIX',
        'dinheiro' => 'Dinheiro',
        'boleto' => 'Boleto',
        'transferencia' => 'Transferência',
        'cartao_credito' => 'Cartão de crédito',
        'cartao_debito' => 'Cartão de débito',
        'cheque' => 'Cheque',
        'debito_automatico' => 'Débito automático',
    ];

    public const RECORRENCIAS = [
        'none' => 'Não repete',
        'weekly' => 'Semanal',
        'monthly' => 'Mensal',
        'quarterly' => 'Trimestral',
        'yearly' => 'Anual',
    ];

    public static function categorias(string $kind): array
    {
        return $kind === 'income' ? self::CATEGORIAS_RECEITA : self::CATEGORIAS_DESPESA;
    }

    public static function categoriaLabel(?string $kind, ?string $categoria): string
    {
        $mapa = self::categorias((string) $kind);

        return $mapa[$categoria]['label'] ?? ($mapa['outros']['label'] ?? 'Outros');
    }

    public static function categoriaIcone(?string $kind, ?string $categoria): string
    {
        $mapa = self::categorias((string) $kind);

        return $mapa[$categoria]['icon'] ?? 'bi-three-dots';
    }

    public static function formaPagamento(?string $chave): string
    {
        return self::FORMAS_PAGAMENTO[(string) $chave] ?? '—';
    }

    public static function recorrencia(?string $chave): string
    {
        return self::RECORRENCIAS[(string) $chave] ?? 'Não repete';
    }

    /**
     * Resumo do período: a receber, a pagar, realizado e saldo.
     *
     * @return array<string,float|int>
     */
    public static function resumoPeriodo(string $inicio, string $fim, ?string $kind = null): array
    {
        $params = [$inicio, $fim];
        $filtroKind = '';

        if ($kind === 'income' || $kind === 'expense') {
            $filtroKind = ' AND kind = ?';
            $params[] = $kind;
        }

        $row = self::db()->fetch(
            "SELECT
                COALESCE(SUM(status = 'pending' AND kind = 'income'), 0) AS a_receber_qtd,
                COALESCE(SUM(CASE WHEN status = 'pending' AND kind = 'income' THEN amount ELSE 0 END), 0) AS a_receber,
                COALESCE(SUM(status = 'pending' AND kind = 'expense'), 0) AS a_pagar_qtd,
                COALESCE(SUM(CASE WHEN status = 'pending' AND kind = 'expense' THEN amount ELSE 0 END), 0) AS a_pagar,
                COALESCE(SUM(CASE WHEN status = 'paid' AND kind = 'income' THEN COALESCE(paid_amount, amount) ELSE 0 END), 0) AS recebido,
                COALESCE(SUM(CASE WHEN status = 'paid' AND kind = 'expense' THEN COALESCE(paid_amount, amount) ELSE 0 END), 0) AS pago,
                COALESCE(SUM(status = 'pending' AND kind = 'expense' AND due_date < CURDATE()), 0) AS atrasadas_qtd,
                COALESCE(SUM(CASE WHEN status = 'pending' AND kind = 'expense' AND due_date < CURDATE() THEN amount ELSE 0 END), 0) AS atrasadas
             FROM financial_entries
             WHERE due_date BETWEEN ? AND ? AND status <> 'cancelled'{$filtroKind}",
            $params
        ) ?? [];

        $aReceber = (float) ($row['a_receber'] ?? 0);
        $aPagar = (float) ($row['a_pagar'] ?? 0);
        $recebido = (float) ($row['recebido'] ?? 0);
        $pago = (float) ($row['pago'] ?? 0);

        return [
            'a_receber' => $aReceber,
            'a_receber_qtd' => (int) ($row['a_receber_qtd'] ?? 0),
            'a_pagar' => $aPagar,
            'a_pagar_qtd' => (int) ($row['a_pagar_qtd'] ?? 0),
            'recebido' => $recebido,
            'pago' => $pago,
            'atrasadas' => (float) ($row['atrasadas'] ?? 0),
            'atrasadas_qtd' => (int) ($row['atrasadas_qtd'] ?? 0),
            'previsto' => $recebido + $aReceber - $pago - $aPagar,
            'realizado' => $recebido - $pago,
        ];
    }

    /**
     * Fluxo mensal de um ano (para gráficos e relatórios).
     *
     * @return array<int,array<string,mixed>>
     */
    public static function fluxoMensal(int $ano): array
    {
        $linhas = self::db()->fetchAll(
            "SELECT MONTH(due_date) AS mes,
                    COALESCE(SUM(CASE WHEN kind = 'income' THEN amount ELSE 0 END), 0) AS entradas,
                    COALESCE(SUM(CASE WHEN kind = 'expense' THEN amount ELSE 0 END), 0) AS saidas,
                    COALESCE(SUM(CASE WHEN kind = 'income' AND status = 'paid' THEN amount ELSE 0 END), 0) AS entradas_pagas,
                    COALESCE(SUM(CASE WHEN kind = 'expense' AND status = 'paid' THEN amount ELSE 0 END), 0) AS saidas_pagas
             FROM financial_entries
             WHERE YEAR(due_date) = ? AND status <> 'cancelled'
             GROUP BY MONTH(due_date)
             ORDER BY mes",
            [$ano]
        );

        $porMes = [];
        foreach ($linhas as $linha) {
            $porMes[(int) $linha['mes']] = $linha;
        }

        $meses = [];

        for ($m = 1; $m <= 12; $m++) {
            $linha = $porMes[$m] ?? [];

            $meses[] = [
                'mes' => $m,
                'label' => self::nomeMes($m),
                'entradas' => (float) ($linha['entradas'] ?? 0),
                'saidas' => (float) ($linha['saidas'] ?? 0),
                'saldo' => (float) ($linha['entradas'] ?? 0) - (float) ($linha['saidas'] ?? 0),
                'entradas_pagas' => (float) ($linha['entradas_pagas'] ?? 0),
                'saidas_pagas' => (float) ($linha['saidas_pagas'] ?? 0),
            ];
        }

        return $meses;
    }

    /**
     * Lista de lançamentos com filtros.
     *
     * @param  array<string,mixed> $filtros inicio, fim, kind, status, categoria, vehicle_id, busca, ordenar
     * @return array<int,array<string,mixed>>
     */
    public static function listar(array $filtros = [], int $limite = 500): array
    {
        [$where, $params] = self::filtros($filtros);

        $ordenar = match ((string) ($filtros['ordenar'] ?? '')) {
            'valor' => 'amount DESC',
            'cadastro' => 'created_at DESC',
            default => 'due_date ASC, id ASC',
        };

        return self::db()->fetchAll(
            "SELECT fe.*, v.plate, v.brand, v.model, v.category AS vehicle_category, v.equipment_type
             FROM financial_entries fe
             LEFT JOIN vehicles v ON v.id = fe.vehicle_id
             {$where}
             ORDER BY {$ordenar}
             LIMIT " . max(1, min(2000, $limite)),
            $params
        );
    }

    /**
     * Agrupa os lançamentos por dia do mês (usado no calendário).
     *
     * @return array<int,array<int,array<string,mixed>>>
     */
    public static function porDia(string $inicio, string $fim): array
    {
        $linhas = self::db()->fetchAll(
            "SELECT fe.*, v.plate, v.brand, v.model, v.category AS vehicle_category, v.equipment_type
             FROM financial_entries fe
             LEFT JOIN vehicles v ON v.id = fe.vehicle_id
             WHERE fe.due_date BETWEEN ? AND ? AND fe.status <> 'cancelled'
             ORDER BY fe.due_date, fe.id",
            [$inicio, $fim]
        );

        $porDia = [];

        foreach ($linhas as $linha) {
            $porDia[(int) substr((string) $linha['due_date'], 8, 2)][] = $linha;
        }

        return $porDia;
    }

    /**
     * Gera o próximo vencimento de um lançamento recorrente.
     */
    public static function proximoVencimento(string $data, string $recorrencia): ?string
    {
        $mapa = [
            'weekly' => '+1 week',
            'monthly' => '+1 month',
            'quarterly' => '+3 months',
            'yearly' => '+1 year',
        ];

        if (!isset($mapa[$recorrencia])) {
            return null;
        }

        $timestamp = strtotime($data . ' ' . $mapa[$recorrencia]);

        return $timestamp !== false ? date('Y-m-d', $timestamp) : null;
    }

    public static function nomeMes(int $mes): string
    {
        $meses = [
            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
            5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
            9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro',
        ];

        return $meses[$mes] ?? '';
    }

    /**
     * @param  array<string,mixed> $filtros
     * @return array{0:string,1:array<int,mixed>}
     */
    public static function filtros(array $filtros): array
    {
        $where = [];
        $params = [];

        $inicio = trim((string) ($filtros['inicio'] ?? ''));
        $fim = trim((string) ($filtros['fim'] ?? ''));

        if ($inicio !== '' && strtotime($inicio)) {
            $where[] = 'due_date >= ?';
            $params[] = date('Y-m-d', strtotime($inicio));
        }

        if ($fim !== '' && strtotime($fim)) {
            $where[] = 'due_date <= ?';
            $params[] = date('Y-m-d', strtotime($fim));
        }

        $kind = (string) ($filtros['kind'] ?? '');
        if ($kind === 'income' || $kind === 'expense') {
            $where[] = 'fe.kind = ?';
            $params[] = $kind;
        }

        $status = (string) ($filtros['status'] ?? '');
        if (in_array($status, ['pending', 'paid', 'cancelled', 'late'], true)) {
            if ($status === 'late') {
                $where[] = "fe.status = 'pending' AND fe.due_date < CURDATE()";
            } else {
                $where[] = 'fe.status = ?';
                $params[] = $status;
            }
        }

        $categoria = (string) ($filtros['categoria'] ?? '');
        if ($categoria !== '') {
            $where[] = 'fe.category = ?';
            $params[] = $categoria;
        }

        $veiculo = (int) ($filtros['vehicle_id'] ?? 0);
        if ($veiculo > 0) {
            $where[] = 'fe.vehicle_id = ?';
            $params[] = $veiculo;
        }

        $busca = trim((string) ($filtros['busca'] ?? ''));
        if ($busca !== '') {
            $where[] = '(fe.description LIKE ? OR fe.party LIKE ? OR fe.document LIKE ?)';
            $like = '%' . $busca . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        return [$where === [] ? '' : 'WHERE ' . implode(' AND ', $where), $params];
    }

    private static function db(): Database
    {
        return App::getInstance()->getDb();
    }
}
