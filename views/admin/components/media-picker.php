<?php
/**
 * Seletor de mídia reutilizável (modal).
 * Incluído uma única vez no layout do admin; qualquer campo pode abri-lo.
 */
$pickerCategories = [
    'general' => 'Geral',
    'imagens' => 'Imagens',
    'banners' => 'Banners',
    'servicos' => 'Serviços',
    'blog' => 'Blog',
    'empresa' => 'Empresa',
    'equipe' => 'Equipe',
    'documentos' => 'Documentos',
];
?>
<div class="modal media-picker" id="mediaPickerModal" data-media-picker aria-hidden="true">
    <div class="modal-dialog media-picker-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-images me-2"></i>Biblioteca de Mídias</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <div class="modal-body">
                <div class="media-toolbar">
                    <div class="media-toolbar-search">
                        <i class="bi bi-search"></i>
                        <input type="search" class="form-control form-control-sm" placeholder="Buscar por nome ou descrição..."
                               data-media-search aria-label="Buscar mídia">
                    </div>

                    <select class="form-select form-select-sm" data-media-type aria-label="Tipo">
                        <option value="all">Todos os tipos</option>
                        <option value="image">Imagens</option>
                        <option value="document">Documentos</option>
                    </select>

                    <select class="form-select form-select-sm" data-media-category aria-label="Categoria">
                        <option value="all">Todas as categorias</option>
                        <?php foreach ($pickerCategories as $slug => $label) { ?>
                            <option value="<?= e($slug) ?>"><?= e($label) ?></option>
                        <?php } ?>
                    </select>

                    <button type="button" class="btn btn-primary btn-sm" data-media-upload-btn>
                        <i class="bi bi-cloud-upload me-1"></i> Enviar arquivos
                    </button>
                    <input type="file" multiple hidden data-media-file-input
                           accept="image/*,.pdf,.doc,.docx,.xls,.xlsx">
                </div>

                <div class="media-dropzone" data-media-dropzone>
                    <i class="bi bi-cloud-arrow-up"></i>
                    <span>Arraste arquivos aqui ou <strong>clique para enviar</strong></span>
                    <small>JPG, PNG, GIF, WebP, SVG, PDF, DOC, XLS — até 10MB cada</small>
                </div>

                <div class="media-upload-progress" data-media-progress hidden>
                    <div class="media-upload-progress-bar"><span data-media-progress-bar></span></div>
                    <small class="text-muted" data-media-progress-label>Enviando...</small>
                </div>

                <div class="media-grid" data-media-grid></div>

                <div class="media-empty text-center text-muted py-5" data-media-empty hidden>
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                    Nenhum arquivo encontrado.
                </div>

                <div class="media-pagination" data-media-pagination></div>
            </div>

            <div class="modal-footer">
                <span class="me-auto text-muted small text-truncate" data-media-selection>Nenhum arquivo selecionado</span>
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary btn-sm" data-media-confirm disabled>
                    <i class="bi bi-check-lg me-1"></i> Usar este arquivo
                </button>
            </div>
        </div>
    </div>
</div>
