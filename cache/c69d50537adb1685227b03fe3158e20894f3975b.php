<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Controle de Manutenções</h4>
    <a href="/admin/manutencoes/novo<?php echo e($veiculoId ? '?veiculo_id=' . $veiculoId : ''); ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>Nova Manutenção
    </a>
</div>

<!-- Filtro por veículo -->
<div class="mb-3">
    <select class="form-select d-inline-block w-auto" onchange="location = this.value ? '?veiculo_id=' + this.value : '?'">
        <option value="">Todos os veículos</option>
        <?php $__currentLoopData = $veiculos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($v['id']); ?>" <?php echo e($veiculoId == $v['id'] ? 'selected' : ''); ?>>
                <?php echo e($v['plate']); ?> - <?php echo e($v['brand']); ?> <?php echo e($v['model']); ?>

            </option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>
</div>

<div class="card">
    <div class="card-body p-0">
        <?php if(empty($manutencoes)): ?>
            <p class="text-muted text-center py-5">Nenhuma manutenção registrada.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Veículo</th>
                            <th>Tipo</th>
                            <th>Data</th>
                            <th>KM</th>
                            <th>Oficina</th>
                            <th>Valor</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $manutencoes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><strong><?php echo e($m['plate']); ?></strong><br><small><?php echo e($m['brand']); ?> <?php echo e($m['model']); ?></small></td>
                            <td>
                                <?php if($m['type'] === 'preventive'): ?>
                                    <span class="badge bg-info">Preventiva</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">Corretiva</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo e(date('d/m/Y', strtotime($m['maintenance_date']))); ?></td>
                            <td><?php echo e(number_format($m['km_at_maintenance'] ?? 0, 0, ',', '.')); ?> km</td>
                            <td><?php echo e($m['workshop'] ?: '—'); ?></td>
                            <td>R$ <?php echo e(number_format($m['cost'], 2, ',', '.')); ?></td>
                            <td class="text-end">
                                <a href="/admin/manutencoes/editar/<?php echo e($m['id']); ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                                <form action="/admin/manutencoes/excluir/<?php echo e($m['id']); ?>" method="POST" class="d-inline" onsubmit="return confirm('Excluir esta manutenção?')">
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button class="btn btn-sm btn-outline-danger">Excluir</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/views/admin/manutencoes/index.blade.php ENDPATH**/ ?>