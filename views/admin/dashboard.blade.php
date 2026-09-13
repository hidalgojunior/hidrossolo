@extends('layouts.admin')

@section('content')
<h4 class="mb-4">Dashboard</h4>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-envelope"></i></div>
            <div>
                <div class="stat-value">{{ $contatosNovos }}</div>
                <div class="stat-label">Mensagens Novas</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="bi bi-file-earmark-text"></i></div>
            <div>
                <div class="stat-value">{{ $contratosAtivos }}</div>
                <div class="stat-label">Contratos Ativos</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="bi bi-truck"></i></div>
            <div>
                <div class="stat-value">{{ $totalVeiculos }}</div>
                <div class="stat-label">Veículos Ativos</div>
            </div>
        </div>
    </div>
</div>

@php
$meses = ['01'=>'Jan','02'=>'Fev','03'=>'Mar','04'=>'Abr','05'=>'Mai','06'=>'Jun','07'=>'Jul','08'=>'Ago','09'=>'Set','10'=>'Out','11'=>'Nov','12'=>'Dez'];
$labelsM = []; $dataM = [];
foreach ($chartManutencao as $m) { $labelsM[] = $meses[substr($m['mes'],5,2)] ?? $m['mes']; $dataM[] = (int)$m['total']; }
$labelsC = []; $dataC = [];
foreach ($chartCombustivel as $c) { $labelsC[] = $meses[substr($c['mes'],5,2)] ?? $c['mes']; $dataC[] = (int)$c['total']; }
@endphp

<!-- Gráficos -->
@if (!empty($dataM) || !empty($dataC))
<div class="row g-4 mb-4" id="chartsSection">
    <div class="col-lg-6">
        <div class="card"><div class="card-header bg-white d-flex justify-content-between"><h5 class="mb-0">📊 Manutenções (R$)</h5><small class="text-muted">últimos 6 meses</small></div>
        <div class="card-body"><canvas id="chartManutencao" height="200"></canvas></div></div>
    </div>
    <div class="col-lg-6">
        <div class="card"><div class="card-header bg-white d-flex justify-content-between"><h5 class="mb-0">⛽ Combustível (R$)</h5><small class="text-muted">últimos 6 meses</small></div>
        <div class="card-body"><canvas id="chartCombustivel" height="200"></canvas></div></div>
    </div>
</div>
@endif

<!-- Relatórios Rápidos -->
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card"><div class="card-header bg-white d-flex justify-content-between align-items-center"><h5 class="mb-0">📋 Relatórios</h5></div>
        <div class="card-body">
            <div class="d-flex flex-wrap gap-2">
                <a href="/admin/contratos?export=csv" class="btn btn-sm btn-outline-success"><i class="bi bi-file-earmark-spreadsheet me-1"></i>Contratos (CSV)</a>
                <a href="/admin/frota?export=csv" class="btn btn-sm btn-outline-success"><i class="bi bi-file-earmark-spreadsheet me-1"></i>Frota (CSV)</a>
                <a href="/admin/contatos?export=csv" class="btn btn-sm btn-outline-success"><i class="bi bi-file-earmark-spreadsheet me-1"></i>Mensagens (CSV)</a>
                <a href="/admin/manutencoes?export=csv" class="btn btn-sm btn-outline-success"><i class="bi bi-file-earmark-spreadsheet me-1"></i>Manutenções (CSV)</a>
                <a href="/admin/abastecimentos?export=csv" class="btn btn-sm btn-outline-success"><i class="bi bi-file-earmark-spreadsheet me-1"></i>Abastecimentos (CSV)</a>
            </div>
        </div></div>
    </div>
</div>

<div class="row g-4">
    <!-- Últimos Contatos -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Últimas Mensagens</h5>
                <a href="/admin/contatos" class="btn btn-sm btn-outline-primary">Ver Todas</a>
            </div>
            <div class="card-body p-0">
                @if (empty($ultimosContatos))
                    <p class="text-muted text-center py-4">Nenhuma mensagem recebida.</p>
                @else
                    <div class="list-group list-group-flush">
                        @foreach ($ultimosContatos as $contato)
                        <a href="/admin/contatos/{{ $contato['id'] }}" class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between">
                                <strong>{{ $contato['name'] }}</strong>
                                <small class="text-muted">{{ date('d/m/Y H:i', strtotime($contato['created_at'])) }}</small>
                            </div>
                            <small class="text-muted">{{ Str::limit($contato['message'], 80) }}</small>
                            @if ($contato['status'] === 'new')
                                <span class="badge bg-danger ms-2">Nova</span>
                            @endif
                        </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Contratos Próximos do Vencimento -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Contratos Vencendo</h5>
                <a href="/admin/contratos" class="btn btn-sm btn-outline-primary">Ver Todos</a>
            </div>
            <div class="card-body p-0">
                @if (empty($contratosVencendo))
                    <p class="text-muted text-center py-4">Nenhum contrato próximo do vencimento.</p>
                @else
                    <div class="list-group list-group-flush">
                        @foreach ($contratosVencendo as $c)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong>{{ $c['contract_number'] }}</strong> - {{ $c['client_name'] }}
                                </div>
                                <span class="badge bg-warning text-dark">Vence {{ date('d/m/Y', strtotime($c['end_date'])) }}</span>
                            </div>
                            <small class="text-muted">R$ {{ number_format($c['value'], 2, ',', '.') }}</small>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@if (!empty($dataM) || !empty($dataC))
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const colors = ['#1e40af','#3b82f6','#06b6d4','#f59e0b','#ef4444','#10b981'];
@if (!empty($dataM))
new Chart(document.getElementById('chartManutencao'), {
    type: 'bar',
    data: { labels: @json($labelsM), datasets: [{ label: 'Manutenções (R$)', data: @json($dataM), backgroundColor: colors[0], borderRadius: 6 }] },
    options: { responsive: true, plugins: { legend: { display: false } } }
});
@endif
@if (!empty($dataC))
new Chart(document.getElementById('chartCombustivel'), {
    type: 'line',
    data: { labels: @json($labelsC), datasets: [{ label: 'Combustível (R$)', data: @json($dataC), borderColor: colors[4], backgroundColor: colors[4]+'20', fill: true, tension: 0.3 }] },
    options: { responsive: true, plugins: { legend: { display: false } } }
});
@endif
</script>
@endif
@endsection
