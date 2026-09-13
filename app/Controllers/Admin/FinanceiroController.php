<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\BaseController;
use App\Core\Security;
use App\Support\Financeiro;
use App\Support\PdfReport;
use App\Support\XlsxWriter;

/**
 * Fluxo de caixa: contas a pagar e a receber da empresa.
 */
class FinanceiroController extends BaseController
{
    public function index(): void
    {
        $filtros = $this->filtros();

        $lancamentos = Financeiro::listar($filtros, 800);
        $resumo = Financeiro::resumoPeriodo($filtros['inicio'], $filtros['fim']);

        // Resumo por categoria no período
        $porCategoria = $this->db()->fetchAll(
            "SELECT kind, category,
                    COUNT(*) AS qtd,
                    COALESCE(SUM(amount), 0) AS total
             FROM financial_entries
             WHERE status <> 'cancelled' AND due_date BETWEEN ? AND ?
             GROUP BY kind, category
             ORDER BY kind, total DESC",
            [$filtros['inicio'], $filtros['fim']]
        );

        echo $this->view('admin.financeiro.index', [
            'title' => 'Fluxo de Caixa',
            'lancamentos' => $lancamentos,
            'resumo' => $resumo,
            'porCategoria' => $porCategoria,
            'filtros' => $filtros,
            'categoriasDespesa' => Financeiro::CATEGORIAS_DESPESA,
            'categoriasReceita' => Financeiro::CATEGORIAS_RECEITA,
            'formasPagamento' => Financeiro::FORMAS_PAGAMENTO,
            'veiculos' => $this->ativos(),
            'fluxoMensal' => Financeiro::fluxoMensal((int) date('Y', strtotime($filtros['inicio']))),
            'config' => $this->config('company'),
        ]);
    }

    public function create(): void
    {
        echo $this->view('admin.financeiro.form', [
            'title' => 'Novo Lançamento',
            'lancamento' => null,
            'categoriasDespesa' => Financeiro::CATEGORIAS_DESPESA,
            'categoriasReceita' => Financeiro::CATEGORIAS_RECEITA,
            'formasPagamento' => Financeiro::FORMAS_PAGAMENTO,
            'recorrencias' => Financeiro::RECORRENCIAS,
            'veiculos' => $this->ativos(),
            'dataSugerida' => $_GET['vencimento'] ?? date('Y-m-d'),
            'config' => $this->config('company'),
        ]);
    }

    public function store(): void
    {
        $dados = $this->validar();

        if (is_string($dados)) {
            $_SESSION['flash_error'] = $dados;
            $_SESSION['old_input'] = $_POST;
            $this->redirect('/admin/financeiro/novo');
        }

        $id = $this->db()->insert('financial_entries', $dados + [
            'status' => 'pending',
            'created_by' => (int) ($_SESSION['user_id'] ?? 0),
        ]);

        Security::audit('finance_created', 'financial_entries', $id, [
            'kind' => $dados['kind'],
            'amount' => $dados['amount'],
            'due_date' => $dados['due_date'],
        ]);

        if (!empty($_POST['criar_proximo']) && $dados['recurrence'] !== 'none') {
            $this->criarProximo((int) $id, $dados);
        }

        unset($_SESSION['old_input']);
        $_SESSION['flash_success'] = 'Lançamento registrado no fluxo de caixa!';
        $this->redirect('/admin/financeiro?mes=' . substr((string) $dados['due_date'], 0, 7));
    }

    public function edit(string $id): void
    {
        $lancamento = $this->buscar((int) $id);

        echo $this->view('admin.financeiro.form', [
            'title' => 'Editar Lançamento',
            'lancamento' => $lancamento,
            'categoriasDespesa' => Financeiro::CATEGORIAS_DESPESA,
            'categoriasReceita' => Financeiro::CATEGORIAS_RECEITA,
            'formasPagamento' => Financeiro::FORMAS_PAGAMENTO,
            'recorrencias' => Financeiro::RECORRENCIAS,
            'veiculos' => $this->ativos(),
            'dataSugerida' => $lancamento['due_date'],
            'config' => $this->config('company'),
        ]);
    }

    public function update(string $id): void
    {
        $this->buscar((int) $id);

        $dados = $this->validar();

        if (is_string($dados)) {
            $_SESSION['flash_error'] = $dados;
            $this->redirect('/admin/financeiro/editar/' . (int) $id);
        }

        $this->db()->update('financial_entries', $dados, 'id = ?', [(int) $id]);

        Security::audit('finance_updated', 'financial_entries', (int) $id, ['kind' => $dados['kind']]);

        $_SESSION['flash_success'] = 'Lançamento atualizado!';
        $this->redirect('/admin/financeiro?mes=' . substr((string) $dados['due_date'], 0, 7));
    }

    /**
     * Baixa (pagamento/recebimento) ou estorno de um lançamento.
     */
    public function status(string $id): void
    {
        $lancamento = $this->buscar((int) $id);
        $acao = (string) ($_POST['acao'] ?? 'baixar');

        $dados = match ($acao) {
            'baixar' => [
                'status' => 'paid',
                'paid_at' => trim((string) ($_POST['paid_at'] ?? '')) !== ''
                    ? date('Y-m-d', (int) strtotime((string) $_POST['paid_at']))
                    : date('Y-m-d'),
                'payment_method' => ((string) ($_POST['payment_method'] ?? '')) ?: null,
            ],
            'cancelar' => ['status' => 'cancelled'],
            'reabrir' => ['status' => 'pending', 'paid_at' => null],
            default => ['status' => 'pending'],
        };

        $this->db()->update('financial_entries', $dados, 'id = ?', [(int) $id]);

        Security::audit('finance_' . $acao, 'financial_entries', (int) $id, ['status' => $dados['status']]);

        // Ao concretizar uma despesa vinculada, replica para o próximo ciclo
        if ($acao === 'baixar' && !empty($_POST['gerar_proximo']) && $lancamento['recurrence'] !== 'none') {
            $this->criarProximo((int) $id, $lancamento);
        }

        $_SESSION['flash_success'] = $acao === 'baixar'
            ? 'Lançamento baixado com sucesso!'
            : 'Lançamento atualizado.';

        $this->redirect($_POST['redirect'] ?? '/admin/financeiro');
    }

    public function delete(string $id): void
    {
        $this->db()->delete('financial_entries', 'id = ?', [(int) $id]);
        Security::audit('finance_deleted', 'financial_entries', (int) $id);

        $_SESSION['flash_success'] = 'Lançamento excluído.';
        $this->redirect('/admin/financeiro');
    }

    /**
     * Exporta o fluxo de caixa filtrado em PDF.
     */
    public function pdf(): void
    {
        $filtros = $this->filtros();
        $lancamentos = Financeiro::listar($filtros, 2000);
        $resumo = Financeiro::resumoPeriodo($filtros['inicio'], $filtros['fim']);

        Security::audit('finance_export_pdf', 'financial_entries', null, ['periodo' => $filtros['inicio'] . '..' . $filtros['fim']]);

        PdfReport::download('fluxo-de-caixa-' . $filtros['inicio'] . '-a-' . $filtros['fim'], [
            'title' => 'Fluxo de caixa',
            'subtitle' => 'Período de ' . date('d/m/Y', (int) strtotime($filtros['inicio']))
                . ' a ' . date('d/m/Y', (int) strtotime($filtros['fim']))
                . ($filtros['status'] !== '' ? ' · filtro: ' . $this->statusLabel($filtros['status']) : ''),
            'company' => $this->config('company'),
            'columns' => self::colunas(),
            'rows' => array_map([$this, 'linhaExport'], $lancamentos),
            'totals' => [
                'amount' => (float) array_sum(array_map(
                    static fn(array $l): float => $l['kind'] === 'expense' ? (float) $l['amount'] : 0.0,
                    $lancamentos
                )),
            ],
            'totals_label' => 'TOTAL A PAGAR (despesas do período)',
            'summary' => [
                ['label' => 'A receber', 'value' => $this->moeda($resumo['a_receber']), 'tone' => 'in', 'hint' => $resumo['a_receber_qtd'] . ' lançamento(s)'],
                ['label' => 'A pagar', 'value' => $this->moeda($resumo['a_pagar']), 'tone' => 'out', 'hint' => $resumo['a_pagar_qtd'] . ' lançamento(s)'],
                ['label' => 'Recebido', 'value' => $this->moeda($resumo['recebido']), 'tone' => 'neutral'],
                ['label' => 'Pago', 'value' => $this->moeda($resumo['pago']), 'tone' => 'neutral'],
                ['label' => 'Saldo realizado', 'value' => $this->moeda($resumo['realizado']), 'tone' => $resumo['realizado'] >= 0 ? 'in' : 'out'],
                ['label' => 'Saldo previsto', 'value' => $this->moeda($resumo['previsto']), 'tone' => $resumo['previsto'] >= 0 ? 'in' : 'out'],
                ['label' => 'Em atraso', 'value' => $this->moeda($resumo['atrasadas']), 'tone' => 'warn', 'hint' => $resumo['atrasadas_qtd'] . ' lançamento(s)'],
            ],
            'notes' => 'Valores previstos consideram os lançamentos pendentes com vencimento no período. '
                . 'Entradas somam receitas; saídas somam despesas.',
        ]);
    }

    /**
     * Exporta o fluxo de caixa filtrado em planilha XLSX formatada.
     */
    public function xlsx(): void
    {
        $filtros = $this->filtros();
        $lancamentos = Financeiro::listar($filtros, 2000);
        $resumo = Financeiro::resumoPeriodo($filtros['inicio'], $filtros['fim']);

        Security::audit('finance_export_xlsx', 'financial_entries', null, ['periodo' => $filtros['inicio'] . '..' . $filtros['fim']]);

        $xlsx = new XlsxWriter('Hidrossolo');

        $xlsx->addSheet('Lançamentos', self::colunas(), array_map([$this, 'linhaExport'], $lancamentos), [
            'title' => 'Fluxo de caixa — lançamentos',
            'subtitle' => 'Período de ' . date('d/m/Y', (int) strtotime($filtros['inicio']))
                . ' a ' . date('d/m/Y', (int) strtotime($filtros['fim'])),
            'totals' => [
                'amount' => (float) array_sum(array_map(
                    static fn(array $l): float => $l['kind'] === 'expense' ? (float) $l['amount'] : 0.0,
                    $lancamentos
                )),
            ],
            'totals_label' => 'TOTAL A PAGAR',
        ]);

        // Aba de resumo por categoria
        $categorias = [];
        foreach ($lancamentos as $lancamento) {
            $chave = $lancamento['kind'] . '|' . $lancamento['category'];
            $categorias[$chave] ??= [
                'kind' => $lancamento['kind'] === 'income' ? 'Entrada' : 'Saída',
                'categoria' => Financeiro::categoriaLabel($lancamento['kind'], $lancamento['category']),
                'itens' => 0,
                'total' => 0.0,
            ];
            $categorias[$chave]['itens']++;
            $categorias[$chave]['total'] += (float) $lancamento['amount'];
        }

        usort($categorias, static fn(array $a, array $b): int => $b['total'] <=> $a['total']);

        $xlsx->addSheet('Resumo por categoria', [
            ['label' => 'Tipo', 'key' => 'kind', 'width' => 12, 'type' => 'center'],
            ['label' => 'Categoria', 'key' => 'categoria', 'width' => 30],
            ['label' => 'Lançamentos', 'key' => 'itens', 'width' => 14, 'type' => 'center'],
            ['label' => 'Total', 'key' => 'total', 'width' => 16, 'type' => 'money'],
        ], array_values($categorias), [
            'title' => 'Resumo por categoria',
            'subtitle' => 'Período de ' . date('d/m/Y', (int) strtotime($filtros['inicio']))
                . ' a ' . date('d/m/Y', (int) strtotime($filtros['fim'])),
        ]);

        // Aba de indicadores
        $xlsx->addSheet('Indicadores', [
            ['label' => 'Indicador', 'key' => 'indicador', 'width' => 28],
            ['label' => 'Valor', 'key' => 'valor', 'width' => 18, 'type' => 'text'],
            ['label' => 'Lançamentos', 'key' => 'qtd', 'width' => 14, 'type' => 'center'],
        ], [
            ['indicador' => 'A receber (pendente)', 'valor' => $this->moeda($resumo['a_receber']), 'qtd' => $resumo['a_receber_qtd']],
            ['indicador' => 'A pagar (pendente)', 'valor' => $this->moeda($resumo['a_pagar']), 'qtd' => $resumo['a_pagar_qtd']],
            ['indicador' => 'Recebido', 'valor' => $this->moeda($resumo['recebido']), 'qtd' => ''],
            ['indicador' => 'Pago', 'valor' => $this->moeda($resumo['pago']), 'qtd' => ''],
            ['indicador' => 'Saldo realizado', 'valor' => $this->moeda($resumo['realizado']), 'qtd' => ''],
            ['indicador' => 'Saldo previsto', 'valor' => $this->moeda($resumo['previsto']), 'qtd' => ''],
            ['indicador' => 'Em atraso', 'valor' => $this->moeda($resumo['atrasadas']), 'qtd' => $resumo['atrasadas_qtd']],
        ], [
            'title' => 'Indicadores do período',
            'zebra' => false,
        ]);

        $xlsx->download('fluxo-de-caixa-' . $filtros['inicio'] . '-a-' . $filtros['fim'] . '.xlsx');
    }

    /* ===================================================================== */

    /**
     * @return array{label:string,key:string,width:int,type:string}[]
     */
    public static function colunas(): array
    {
        return [
            ['label' => 'Vencimento', 'key' => 'due_date', 'width' => 12, 'type' => 'date'],
            ['label' => 'Tipo', 'key' => 'tipo', 'width' => 10, 'type' => 'center'],
            ['label' => 'Descrição', 'key' => 'description', 'width' => 42],
            ['label' => 'Categoria', 'key' => 'categoria', 'width' => 26],
            ['label' => 'Fornecedor / Cliente', 'key' => 'party', 'width' => 26],
            ['label' => 'Ativo', 'key' => 'ativo', 'width' => 24],
            ['label' => 'Valor (R$)', 'key' => 'amount', 'width' => 16, 'type' => 'money'],
            ['label' => 'Situação', 'key' => 'situacao', 'width' => 14, 'type' => 'center'],
            ['label' => 'Pago / recebido em', 'key' => 'paid_at', 'width' => 16, 'type' => 'date'],
            ['label' => 'Forma', 'key' => 'forma', 'width' => 16],
            ['label' => 'Documento', 'key' => 'document', 'width' => 16],
        ];
    }

    /**
     * @param  array<string,mixed> $lancamento
     * @return array<string,mixed>
     */
    public function linhaExport(array $lancamento): array
    {
        return [
            'due_date' => (string) $lancamento['due_date'],
            'tipo' => $lancamento['kind'] === 'income' ? 'Entrada' : 'Saída',
            'description' => (string) $lancamento['description'],
            'categoria' => Financeiro::categoriaLabel($lancamento['kind'], $lancamento['category']),
            'party' => (string) ($lancamento['party'] ?? ''),
            'ativo' => $this->ativoLabel($lancamento),
            'amount' => (float) $lancamento['amount'],
            'situacao' => $this->statusLabel((string) $lancamento['status']),
            'paid_at' => (string) ($lancamento['paid_at'] ?? ''),
            'forma' => Financeiro::formaPagamento($lancamento['payment_method'] ?? null),
            'document' => (string) ($lancamento['document'] ?? ''),
        ];
    }

    /**
     * @param array<string,mixed> $lancamento
     */
    private function ativoLabel(array $lancamento): string
    {
        if (empty($lancamento['plate']) && empty($lancamento['model'])) {
            return '';
        }

        return asset_label($lancamento);
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'paid' => 'Baixado',
            'cancelled' => 'Cancelado',
            'late' => 'Em atraso',
            default => 'Pendente',
        };
    }

    private function moeda(float $valor): string
    {
        return 'R$ ' . number_format($valor, 2, ',', '.');
    }

    /**
     * @return array<string,mixed>
     */
    private function filtros(): array
    {
        $mes = preg_replace('/[^0-9\-]/', '', (string) ($_GET['mes'] ?? ''));

        if (is_string($mes) && preg_match('/^\d{4}-\d{2}$/', $mes)) {
            $inicio = $mes . '-01';
            $fim = date('Y-m-t', (int) strtotime($inicio));
        } else {
            $inicio = (string) ($_GET['inicio'] ?? date('Y-m-01'));
            $fim = (string) ($_GET['fim'] ?? date('Y-m-t'));

            $inicio = strtotime($inicio) ? date('Y-m-d', (int) strtotime($inicio)) : date('Y-m-01');
            $fim = strtotime($fim) ? date('Y-m-d', (int) strtotime($fim)) : date('Y-m-t');
        }

        return [
            'inicio' => $inicio,
            'fim' => $fim,
            'mes' => substr($inicio, 0, 7),
            'kind' => in_array(($_GET['kind'] ?? ''), ['income', 'expense'], true) ? (string) $_GET['kind'] : '',
            'status' => in_array(($_GET['status'] ?? ''), ['pending', 'paid', 'cancelled', 'late'], true) ? (string) $_GET['status'] : '',
            'categoria' => (string) ($_GET['categoria'] ?? ''),
            'vehicle_id' => (int) ($_GET['vehicle_id'] ?? 0),
            'busca' => trim((string) ($_GET['busca'] ?? '')),
            'ordenar' => (string) ($_GET['ordenar'] ?? 'vencimento'),
        ];
    }

    /**
     * Valida e normaliza os dados do formulário.
     *
     * @return array<string,mixed>|string
     */
    private function validar(): array|string
    {
        $kind = (string) ($_POST['kind'] ?? 'expense');
        $descricao = trim((string) ($_POST['description'] ?? ''));
        $categoria = (string) ($_POST['category'] ?? 'outros');
        $valor = $this->decimal($_POST['amount'] ?? null);
        $vencimento = trim((string) ($_POST['due_date'] ?? ''));

        $erros = [];

        if (!in_array($kind, ['income', 'expense'], true)) {
            $kind = 'expense';
        }

        if ($descricao === '') {
            $erros[] = 'Descreva o lançamento.';
        }

        if (!array_key_exists($categoria, Financeiro::categorias($kind))) {
            $categoria = $kind === 'income' ? 'outras_receitas' : 'outros';
        }

        if ($valor === null || $valor <= 0) {
            $erros[] = 'Informe um valor maior que zero.';
        }

        if ($vencimento === '' || !strtotime($vencimento)) {
            $erros[] = 'Informe uma data de vencimento válida.';
        }

        if ($erros !== []) {
            return implode(' ', $erros);
        }

        $recorrencia = (string) ($_POST['recurrence'] ?? 'none');
        if (!array_key_exists($recorrencia, Financeiro::RECORRENCIAS)) {
            $recorrencia = 'none';
        }

        $forma = (string) ($_POST['payment_method'] ?? '');
        if ($forma !== '' && !array_key_exists($forma, Financeiro::FORMAS_PAGAMENTO)) {
            $forma = '';
        }

        $pago = trim((string) ($_POST['paid_at'] ?? ''));

        return [
            'kind' => $kind,
            'category' => $categoria,
            'description' => mb_substr($descricao, 0, 255),
            'amount' => $valor,
            'due_date' => date('Y-m-d', (int) strtotime($vencimento)),
            'paid_at' => $pago !== '' && strtotime($pago) ? date('Y-m-d', (int) strtotime($pago)) : null,
            'paid_amount' => $this->decimal($_POST['paid_amount'] ?? null),
            'payment_method' => $forma !== '' ? $forma : null,
            'party' => mb_substr(trim((string) ($_POST['party'] ?? '')), 0, 160) ?: null,
            'document' => mb_substr(trim((string) ($_POST['document'] ?? '')), 0, 60) ?: null,
            'vehicle_id' => ((int) ($_POST['vehicle_id'] ?? 0)) > 0 ? (int) $_POST['vehicle_id'] : null,
            'recurrence' => $recorrencia,
            'notes' => trim((string) ($_POST['notes'] ?? '')) ?: null,
            'status' => !empty($_POST['paid_at']) && strtotime((string) $_POST['paid_at']) ? 'paid' : 'pending',
        ];
    }

    /**
     * @param array<string,mixed> $dados
     */
    private function criarProximo(int $id, array $dados): void
    {
        $proximo = Financeiro::proximoVencimento((string) $dados['due_date'], (string) $dados['recurrence']);

        if ($proximo === null) {
            return;
        }

        $this->db()->insert('financial_entries', [
            'kind' => $dados['kind'],
            'category' => $dados['category'],
            'description' => $dados['description'],
            'amount' => $dados['amount'],
            'due_date' => $proximo,
            'status' => 'pending',
            'party' => $dados['party'] ?? null,
            'document' => $dados['document'] ?? null,
            'vehicle_id' => $dados['vehicle_id'] ?? null,
            'recurrence' => $dados['recurrence'],
            'notes' => $dados['notes'] ?? null,
            'created_by' => (int) ($_SESSION['user_id'] ?? 0),
        ]);

        Security::audit('finance_recurrence_created', 'financial_entries', $id, ['proximo' => $proximo]);
    }

    /**
     * @return array<string,mixed>
     */
    private function buscar(int $id): array
    {
        $lancamento = $this->db()->fetch('SELECT * FROM financial_entries WHERE id = ?', [$id]);

        if (!$lancamento) {
            $_SESSION['flash_error'] = 'Lançamento não encontrado.';
            $this->redirect('/admin/financeiro');
        }

        return $lancamento;
    }

    private function decimal(mixed $valor): ?float
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        $normalizado = str_replace(['.', ','], ['', '.'], (string) $valor);

        return is_numeric($normalizado) ? round((float) $normalizado, 2) : null;
    }

    private function ativos(): array
    {
        return $this->db()->fetchAll(
            "SELECT id, plate, brand, model, category, equipment_type
             FROM vehicles WHERE status <> 'inactive'
             ORDER BY category, COALESCE(plate, equipment_type), brand"
        );
    }
}
