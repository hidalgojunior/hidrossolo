<?php $__env->startSection('content'); ?>
<section class="section" style="min-height:60vh">
    <div class="container text-center py-5">
        <i class="bi bi-check-circle display-1 text-success"></i>
        <h1 class="mt-3">Obrigado!</h1>
        <p class="lead">Recebemos sua mensagem. Nossa equipe entrará em contato em breve.</p>
        <div class="mt-4">
            <a href="/" class="btn btn-primary btn-lg">Voltar ao Início</a>
            <a href="/servicos" class="btn btn-outline-primary btn-lg ms-2">Nossos Serviços</a>
        </div>
    </div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/views/pages/obrigado.blade.php ENDPATH**/ ?>