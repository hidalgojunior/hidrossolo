<?php
/**
 * Campo reutilizável de mídia.
 *
 * Uso em qualquer view do admin:
 *   <?= $view->partial('admin.components.media-field', [
 *       'name'  => 'featured_image',
 *       'label' => 'Imagem de Destaque',
 *       'value' => $servico['featured_image'] ?? '',
 *       'help'  => 'Escolha um arquivo da biblioteca ou envie um novo.',
 *   ]) ?>
 *
 * @var string      $name
 * @var string      $label
 * @var string|null $value
 * @var string|null $help
 * @var string|null $type   image|file
 */
$name = $name ?? 'media';
$label = $label ?? 'Mídia';
$value = $value ?? '';
$help = $help ?? null;
$type = $type ?? 'image';
$fieldId = 'mf-' . preg_replace('/[^a-zA-Z0-9_-]/', '-', $name);
?>
<div class="media-field" data-media-field data-media-type="<?= e($type) ?>">
    <label class="form-label" for="<?= e($fieldId) ?>"><?= e($label) ?></label>

    <div class="media-field-body">
        <div class="media-field-preview" data-media-preview>
            <i class="bi bi-image"></i>
        </div>

        <div class="media-field-main">
            <input type="text" class="form-control form-control-sm" name="<?= e($name) ?>" id="<?= e($fieldId) ?>"
                   value="<?= e($value) ?>" placeholder="/assets/uploads/..." data-media-input autocomplete="off">

            <div class="media-field-actions">
                <button type="button" class="btn btn-outline-primary btn-sm" data-media-open>
                    <i class="bi bi-images me-1"></i> Biblioteca
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-media-upload>
                    <i class="bi bi-cloud-upload me-1"></i> Enviar
                </button>
                <button type="button" class="btn btn-outline-danger btn-sm" data-media-clear title="Limpar">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <?php if ($help) { ?>
                <small class="text-muted d-block mt-1"><?= e($help) ?></small>
            <?php } ?>
        </div>
    </div>
</div>
