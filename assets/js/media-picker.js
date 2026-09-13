/* =========================================================================
   Hidrossolo — Seletor de Mídia (JavaScript puro)
   Permite escolher/enviar arquivos da biblioteca e inseri-los em qualquer
   campo do site.
   ========================================================================= */
(function () {
    'use strict';

    var IMAGE_RE = /\.(jpe?g|png|gif|webp|svg|avif|bmp)(\?.*)?$/i;

    function csrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function el(tag, className, html) {
        var node = document.createElement(tag);
        if (className) node.className = className;
        if (html !== undefined) node.innerHTML = html;
        return node;
    }

    function escapeHtml(value) {
        return String(value === null || value === undefined ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    /* ---------------------------------------------------------------------
     * Feedback (toast simples)
     * ------------------------------------------------------------------- */
    function toast(message, type) {
        var wrap = document.getElementById('mediaToastWrap');

        if (!wrap) {
            wrap = el('div', 'media-toast-wrap');
            wrap.id = 'mediaToastWrap';
            document.body.appendChild(wrap);
        }

        var node = el('div', 'media-toast media-toast-' + (type || 'success'), escapeHtml(message));
        wrap.appendChild(node);

        window.setTimeout(function () {
            node.style.opacity = '0';
            window.setTimeout(function () { node.remove(); }, 250);
        }, 3200);
    }

    /* ---------------------------------------------------------------------
     * Seletor
     * ------------------------------------------------------------------- */
    function MediaPicker(root) {
        this.root = root;
        this.grid = root.querySelector('[data-media-grid]');
        this.empty = root.querySelector('[data-media-empty]');
        this.pagination = root.querySelector('[data-media-pagination]');
        this.dropzone = root.querySelector('[data-media-dropzone]');
        this.fileInput = root.querySelector('[data-media-file-input]');
        this.progress = root.querySelector('[data-media-progress]');
        this.progressBar = root.querySelector('[data-media-progress-bar]');
        this.progressLabel = root.querySelector('[data-media-progress-label]');
        this.selectionLabel = root.querySelector('[data-media-selection]');
        this.confirmBtn = root.querySelector('[data-media-confirm]');
        this.searchInput = root.querySelector('[data-media-search]');
        this.typeSelect = root.querySelector('[data-media-type]');
        this.categorySelect = root.querySelector('[data-media-category]');

        this.state = { q: '', type: 'all', categoria: 'all', page: 1 };
        this.items = [];
        this.selected = null;
        this.target = null;
        this.onSelect = null;
        this.uploadCategory = 'imagens';

        this.bindEvents();
    }

    MediaPicker.prototype.bindEvents = function () {
        var self = this;

        // Busca (com atraso)
        if (this.searchInput) {
            var timer = null;
            this.searchInput.addEventListener('input', function () {
                window.clearTimeout(timer);
                timer = window.setTimeout(function () {
                    self.state.q = self.searchInput.value.trim();
                    self.state.page = 1;
                    self.load();
                }, 320);
            });
        }

        if (this.typeSelect) {
            this.typeSelect.addEventListener('change', function () {
                self.state.type = self.typeSelect.value;
                self.state.page = 1;
                self.load();
            });
        }

        if (this.categorySelect) {
            this.categorySelect.addEventListener('change', function () {
                self.state.categoria = self.categorySelect.value;
                self.state.page = 1;
                self.load();
            });
        }

        // Upload por botão
        var uploadBtn = this.root.querySelector('[data-media-upload-btn]');
        if (uploadBtn && this.fileInput) {
            uploadBtn.addEventListener('click', function () { self.fileInput.click(); });
        }

        if (this.fileInput) {
            this.fileInput.addEventListener('change', function () {
                if (self.fileInput.files.length) {
                    self.upload(self.fileInput.files, self.uploadCategory);
                }
            });
        }

        // Upload por arrastar e soltar
        if (this.dropzone) {
            this.dropzone.addEventListener('click', function () {
                if (self.fileInput) self.fileInput.click();
            });

            ['dragenter', 'dragover'].forEach(function (evt) {
                self.dropzone.addEventListener(evt, function (event) {
                    event.preventDefault();
                    self.dropzone.classList.add('is-dragover');
                });
            });

            ['dragleave', 'drop'].forEach(function (evt) {
                self.dropzone.addEventListener(evt, function (event) {
                    event.preventDefault();
                    self.dropzone.classList.remove('is-dragover');
                });
            });

            this.dropzone.addEventListener('drop', function (event) {
                if (event.dataTransfer && event.dataTransfer.files.length) {
                    self.upload(event.dataTransfer.files, self.uploadCategory);
                }
            });
        }

        // Confirmar seleção
        if (this.confirmBtn) {
            this.confirmBtn.addEventListener('click', function () { self.applySelection(); });
        }

        // Selecionar com duplo clique
        this.grid.addEventListener('dblclick', function (event) {
            var card = event.target.closest('[data-media-id]');
            if (!card) return;
            self.select(card.getAttribute('data-media-id'));
            self.applySelection();
        });
    };

    MediaPicker.prototype.open = function (options) {
        options = options || {};

        this.target = options.target || null;
        this.onSelect = options.onSelect || null;
        this.uploadCategory = options.category || 'imagens';
        this.state.type = options.type || 'all';
        this.state.categoria = 'all';
        this.state.q = '';
        this.state.page = 1;
        this.selected = null;

        if (this.searchInput) this.searchInput.value = '';
        if (this.typeSelect) this.typeSelect.value = this.state.type;
        if (this.categorySelect) this.categorySelect.value = 'all';
        if (this.selectionLabel) this.selectionLabel.textContent = 'Nenhum arquivo selecionado';
        if (this.confirmBtn) this.confirmBtn.disabled = true;

        this.root.classList.add('show');
        this.root.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';

        this.load();
    };

    MediaPicker.prototype.close = function () {
        this.root.classList.remove('show');
        this.root.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    };

    MediaPicker.prototype.load = function () {
        var self = this;
        var params = new URLSearchParams({
            q: this.state.q,
            type: this.state.type,
            categoria: this.state.categoria,
            page: String(this.state.page)
        });

        fetch('/admin/midia/list?' + params.toString(), {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin'
        })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                self.items = data.items || [];
                self.render();
                self.renderPagination(data.page || 1, data.pages || 1);
                self.renderCategories(data.categories || []);
            })
            .catch(function () {
                toast('Não foi possível carregar a biblioteca.', 'error');
            });
    };

    MediaPicker.prototype.renderCategories = function (categories) {
        if (!this.categorySelect) return;

        var current = this.state.categoria;
        var html = '<option value="all">Todas as categorias</option>';

        categories.forEach(function (cat) {
            html += '<option value="' + escapeHtml(cat.name) + '">'
                + escapeHtml(cat.name) + ' (' + cat.count + ')</option>';
        });

        this.categorySelect.innerHTML = html;
        this.categorySelect.value = current;
    };

    MediaPicker.prototype.render = function () {
        var self = this;

        this.grid.innerHTML = '';

        if (!this.items.length) {
            if (this.empty) this.empty.hidden = false;
            return;
        }

        if (this.empty) this.empty.hidden = true;

        this.items.forEach(function (item) {
            var card = el('div', 'media-card');
            card.setAttribute('data-media-id', String(item.id));
            card.setAttribute('title', item.name);

            var thumb = item.is_image
                ? '<img src="' + escapeHtml(item.thumb) + '" alt="' + escapeHtml(item.name) + '" loading="lazy">'
                : '<i class="bi ' + escapeHtml(item.icon) + '"></i>';

            card.innerHTML =
                '<div class="media-card-thumb">' + thumb
                + '<span class="media-card-badge">' + escapeHtml(item.category) + '</span>'
                + '<span class="media-card-check"><i class="bi bi-check-lg"></i></span>'
                + '</div>'
                + '<div class="media-card-info">'
                + '<span class="media-card-name">' + escapeHtml(item.name) + '</span>'
                + '<span class="media-card-meta"><span>' + escapeHtml(item.size_human) + '</span></span>'
                + '</div>';

            card.addEventListener('click', function () {
                self.select(String(item.id));
            });

            self.grid.appendChild(card);
        });
    };

    MediaPicker.prototype.renderPagination = function (page, pages) {
        var self = this;
        this.pagination.innerHTML = '';

        if (pages <= 1) return;

        var add = function (label, targetPage, active, disabled) {
            var button = el('button', active ? 'is-active' : '', label);
            button.type = 'button';
            if (disabled) button.disabled = true;
            button.addEventListener('click', function () {
                self.state.page = targetPage;
                self.load();
                self.grid.scrollTop = 0;
            });
            self.pagination.appendChild(button);
        };

        add('&laquo;', page - 1, false, page <= 1);

        var start = Math.max(1, page - 2);
        var end = Math.min(pages, start + 4);

        for (var i = start; i <= end; i++) {
            add(String(i), i, i === page, false);
        }

        add('&raquo;', page + 1, false, page >= pages);
    };

    MediaPicker.prototype.select = function (id) {
        var card = this.grid.querySelector('[data-media-id="' + id + '"]');
        var item = this.items.filter(function (i) { return String(i.id) === String(id); })[0];

        this.grid.querySelectorAll('.media-card.is-selected').forEach(function (node) {
            node.classList.remove('is-selected');
        });

        if (card) card.classList.add('is-selected');

        this.selected = item || null;

        if (this.confirmBtn) this.confirmBtn.disabled = !item;

        if (this.selectionLabel) {
            this.selectionLabel.textContent = item ? item.name : 'Nenhum arquivo selecionado';
        }
    };

    MediaPicker.prototype.applySelection = function () {
        if (!this.selected) return;

        var value = this.selected.url;

        if (this.target) {
            this.target.value = value;
            this.target.dispatchEvent(new Event('input', { bubbles: true }));
            this.target.dispatchEvent(new Event('change', { bubbles: true }));
        }

        if (typeof this.onSelect === 'function') {
            this.onSelect(this.selected);
        }

        this.close();
    };

    /* ---------------------------------------------------------------------
     * Upload
     * ------------------------------------------------------------------- */
    MediaPicker.prototype.upload = function (files, category) {
        var self = this;
        var formData = new FormData();

        Array.prototype.forEach.call(files, function (file) {
            formData.append('files[]', file);
        });

        formData.append('category', category || 'general');
        formData.append('_csrf_token', csrfToken());

        var xhr = new XMLHttpRequest();
        xhr.open('POST', '/admin/midia/upload?json=1', true);
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('X-CSRF-Token', csrfToken());

        if (this.progress) this.progress.hidden = false;

        xhr.upload.addEventListener('progress', function (event) {
            if (!event.lengthComputable || !self.progressBar) return;
            var percent = Math.round((event.loaded / event.total) * 100);
            self.progressBar.style.width = percent + '%';
            if (self.progressLabel) self.progressLabel.textContent = 'Enviando... ' + percent + '%';
        });

        xhr.addEventListener('load', function () {
            if (self.progress) self.progress.hidden = true;
            if (self.progressBar) self.progressBar.style.width = '0%';
            if (self.fileInput) self.fileInput.value = '';

            var data = null;
            try { data = JSON.parse(xhr.responseText); } catch (e) { data = null; }

            if (!data || !data.success) {
                toast((data && data.message) || 'Falha ao enviar o arquivo.', 'error');
                return;
            }

            toast(data.message || 'Upload concluído.', 'success');

            self.state.page = 1;
            self.load().valueOf();

            // Seleciona automaticamente o último arquivo enviado
            if (data.items && data.items.length) {
                var last = data.items[data.items.length - 1];
                window.setTimeout(function () { self.select(String(last.id)); }, 400);
            }
        });

        xhr.addEventListener('error', function () {
            if (self.progress) self.progress.hidden = true;
            toast('Erro de conexão durante o envio.', 'error');
        });

        xhr.send(formData);
    };

    /* ---------------------------------------------------------------------
     * Campos de mídia ([data-media-field])
     * ------------------------------------------------------------------- */
    function previewOf(input) {
        var field = input.closest('[data-media-field]');
        if (!field) return;

        var preview = field.querySelector('[data-media-preview]');
        if (!preview) return;

        var value = (input.value || '').trim();

        if (value && IMAGE_RE.test(value)) {
            preview.innerHTML = '<img src="' + escapeHtml(value) + '" alt="">';
        } else if (value) {
            preview.innerHTML = '<i class="bi bi-file-earmark"></i>';
        } else {
            preview.innerHTML = '<i class="bi bi-image"></i>';
        }
    }

    function quickUpload(input) {
        var field = input.closest('[data-media-field]');
        var type = field ? field.getAttribute('data-media-type') : 'image';

        var chooser = document.createElement('input');
        chooser.type = 'file';
        chooser.multiple = false;
        chooser.accept = type === 'file' ? '*' : 'image/*,.pdf,.doc,.docx';

        chooser.addEventListener('change', function () {
            if (!chooser.files.length) return;

            var formData = new FormData();
            formData.append('files[]', chooser.files[0]);
            formData.append('category', type === 'file' ? 'documentos' : 'imagens');
            formData.append('_csrf_token', csrfToken());

            var xhr = new XMLHttpRequest();
            xhr.open('POST', '/admin/midia/upload?json=1', true);
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.setRequestHeader('X-CSRF-Token', csrfToken());

            xhr.addEventListener('load', function () {
                var data = null;
                try { data = JSON.parse(xhr.responseText); } catch (e) { data = null; }

                if (!data || !data.success || !data.items || !data.items.length) {
                    toast((data && data.message) || 'Falha ao enviar o arquivo.', 'error');
                    return;
                }

                input.value = data.items[0].url;
                input.dispatchEvent(new Event('input', { bubbles: true }));
                input.dispatchEvent(new Event('change', { bubbles: true }));
                toast('Arquivo enviado e vinculado.', 'success');
            });

            xhr.addEventListener('error', function () {
                toast('Erro de conexão durante o envio.', 'error');
            });

            xhr.send(formData);
        });

        chooser.click();
    }

    /* ---------------------------------------------------------------------
     * Inicialização
     * ------------------------------------------------------------------- */
    function bindMediaField(field, picker) {
        var input = field.querySelector('[data-media-input]');
        if (!input) return;

        previewOf(input);
        input.addEventListener('input', function () { previewOf(input); });
        input.addEventListener('change', function () { previewOf(input); });

        var openBtn = field.querySelector('[data-media-open]');
        if (openBtn) {
            openBtn.addEventListener('click', function () {
                var type = field.getAttribute('data-media-type') || 'image';
                if (!picker) {
                    toast('Seletor de mídia não disponível.', 'error');
                    return;
                }
                picker.open({ target: input, type: type === 'file' ? 'document' : 'image' });
            });
        }

        var uploadBtn = field.querySelector('[data-media-upload]');
        if (uploadBtn) {
            uploadBtn.addEventListener('click', function () { quickUpload(input); });
        }

        var clearBtn = field.querySelector('[data-media-clear]');
        if (clearBtn) {
            clearBtn.addEventListener('click', function () {
                input.value = '';
                input.dispatchEvent(new Event('input', { bubbles: true }));
                input.dispatchEvent(new Event('change', { bubbles: true }));
            });
        }
    }

    function bindMediaFields(scope, picker) {
        (scope || document).querySelectorAll('[data-media-field]').forEach(function (field) {
            if (field.getAttribute('data-media-bound') === '1') return;
            field.setAttribute('data-media-bound', '1');
            bindMediaField(field, picker);
        });
    }

    function init() {
        var pickerRoot = document.querySelector('[data-media-picker]');
        var picker = pickerRoot ? new MediaPicker(pickerRoot) : null;

        window.MediaPicker = {
            open: function (options) {
                if (picker) picker.open(options);
            },
            close: function () {
                if (picker) picker.close();
            },
            initFields: function (scope) {
                bindMediaFields(scope || document, picker);
            },
            toast: toast
        };

        bindMediaFields(document, picker);

        // Fechar clicando no fundo
        if (pickerRoot) {
            pickerRoot.addEventListener('click', function (event) {
                if (event.target === pickerRoot) picker.close();
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
