<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\BaseController;
use App\Support\YouTube;

/**
 * Biblioteca de Mídias.
 *
 * Todos os arquivos enviados ficam em /assets/uploads/AAAA/MM e são
 * registrados na tabela media_library, podendo depois ser reutilizados em
 * qualquer parte do site (serviços, páginas do CMS, blog, logos etc.).
 */
class MidiaController extends BaseController
{
    private const PER_PAGE = 24;
    private const MAX_SIZE = 10 * 1024 * 1024; // 10MB
    private const ALLOWED_EXT = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'pdf', 'doc', 'docx', 'xls', 'xlsx'];
    private const IMAGE_EXT = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];

    /** Marcador de mídia hospedada fora do servidor (vídeo do YouTube). */
    public const MIME_YOUTUBE = 'video/youtube';

    /** Categorias sugeridas para organizar a biblioteca. */
    public const CATEGORIES = [
        'general' => 'Geral',
        'imagens' => 'Imagens',
        'banners' => 'Banners',
        'servicos' => 'Serviços',
        'blog' => 'Blog',
        'empresa' => 'Empresa',
        'equipe' => 'Equipe',
        'documentos' => 'Documentos',
        'editor' => 'Editor',
        'videos' => 'Vídeos',
    ];

    /* =====================================================================
     | Listagem (admin)
     * =================================================================== */

    public function index(): void
    {
        $db = $this->db();

        [$where, $params, $q, $type, $category] = $this->filters();
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $offset = ($page - 1) * self::PER_PAGE;

        $rows = $db->fetchAll(
            "SELECT * FROM media_library {$where} ORDER BY created_at DESC, id DESC LIMIT ? OFFSET ?",
            array_merge($params, [self::PER_PAGE, $offset])
        );

        $total = (int) ($db->fetch("SELECT COUNT(*) AS c FROM media_library {$where}", $params)['c'] ?? 0);

        $cats = $db->fetchAll("SELECT category, COUNT(*) AS c FROM media_library GROUP BY category ORDER BY category");
        $categorias = array_map(
            static fn(array $c): array => ['name' => $c['category'] ?: 'general', 'count' => (int) $c['c']],
            $cats
        );

        echo $this->view('admin.midia.index', [
            'title' => 'Biblioteca de Mídias',
            'midias' => array_map(fn(array $m): array => $this->mapRow($m), $rows),
            'categorias' => $categorias,
            'catLabels' => self::CATEGORIES,
            'categoria' => $category,
            'q' => $q,
            'type' => $type,
            'total' => $total,
            'pagina' => $page,
            'perPage' => self::PER_PAGE,
            'config' => $this->config('company'),
        ]);
    }

    /* =====================================================================
     | API JSON (usada pelo seletor de mídia)
     * =================================================================== */

    public function list(): void
    {
        $db = $this->db();

        [$where, $params] = $this->filters();
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = min(60, max(6, (int) ($_GET['per_page'] ?? 24)));
        $offset = ($page - 1) * $perPage;

        $total = (int) ($db->fetch("SELECT COUNT(*) AS c FROM media_library {$where}", $params)['c'] ?? 0);

        $rows = $db->fetchAll(
            "SELECT * FROM media_library {$where} ORDER BY created_at DESC, id DESC LIMIT ? OFFSET ?",
            array_merge($params, [$perPage, $offset])
        );

        $cats = $db->fetchAll("SELECT category, COUNT(*) AS c FROM media_library GROUP BY category ORDER BY category");

        $this->json([
            'success' => true,
            'items' => array_map(fn(array $m): array => $this->mapRow($m), $rows),
            'total' => $total,
            'page' => $page,
            'pages' => (int) ceil($total / max(1, $perPage)),
            'categories' => array_map(
                static fn(array $c): array => ['name' => $c['category'] ?: 'general', 'count' => (int) $c['c']],
                $cats
            ),
        ]);
    }

    /* =====================================================================
     | Upload
     * =================================================================== */

    public function upload(): void
    {
        $wantsJson = $this->wantsJson();
        $category = $this->sanitizeCategory($_POST['category'] ?? 'general');

        $files = $this->normalizeFiles($_FILES['files'] ?? $_FILES['file'] ?? null);

        if ($files === []) {
            $this->respond($wantsJson, false, 'Nenhum arquivo foi enviado.');
        }

        $created = [];
        $errors = [];

        foreach ($files as $file) {
            $result = $this->storeFile($file, $category);

            if ($result['ok']) {
                $created[] = $result['item'];
            } else {
                $errors[] = $result['error'];
            }
        }

        if ($created === []) {
            $this->respond($wantsJson, false, implode(' ', $errors), ['errors' => $errors]);
        }

        $message = count($created) === 1
            ? 'Arquivo enviado com sucesso!'
            : count($created) . ' arquivos enviados com sucesso!';

        if ($errors !== []) {
            $message .= ' (' . count($errors) . ' ignorado(s): ' . implode(' ', array_slice($errors, 0, 2)) . ')';
        }

        $this->respond($wantsJson, true, $message, ['items' => $created]);
    }

    /* =====================================================================
     | Vídeo do YouTube (sem hospedar o arquivo no servidor)
     * =================================================================== */

    /**
     * Cadastra um vídeo que já está no YouTube.
     *
     * O vídeo não é baixado nem copiado e nada é hospedado no servidor:
     * guardamos a URL do vídeo em `file_path`, a capa em `thumbnail_path` e
     * marcamos o tipo como `video/youtube`. Vídeos maiores (ou até
     * transmissões já encerradas) passam a ficar disponíveis na biblioteca
     * sem consumir espaço nem banda da hospedagem.
     *
     * Não é preciso alterar o banco: o tipo fica no `mime_type` e o ID do
     * vídeo é extraído da própria URL quando a mídia é lida.
     */
    public function youtube(): void
    {
        $db = $this->db();
        $wantsJson = $this->wantsJson();

        $videoId = YouTube::parse((string) ($_POST['youtube_url'] ?? ''));

        if ($videoId === null) {
            $this->respond(
                $wantsJson,
                false,
                'Link do YouTube inválido. Cole a URL do vídeo (youtube.com/watch?v=…, youtu.be/… ou /shorts/…).'
            );
        }

        $watchUrl = YouTube::watchUrl($videoId);

        if ($db->fetch('SELECT id FROM media_library WHERE file_path = ?', [$watchUrl])) {
            $this->respond($wantsJson, false, 'Este vídeo já está na biblioteca.');
        }

        $titulo = trim((string) ($_POST['title'] ?? ''));
        $alt = trim((string) ($_POST['alt_text'] ?? ''));
        $nome = $titulo !== '' ? $titulo : 'Vídeo do YouTube ' . $videoId;

        $id = $db->insert('media_library', [
            'filename' => 'youtube-' . $videoId,
            'original_name' => mb_substr($nome, 0, 255),
            'mime_type' => self::MIME_YOUTUBE,
            'file_size' => 0,
            'file_path' => $watchUrl,
            'thumbnail_path' => YouTube::thumbnail($videoId),
            'alt_text' => mb_substr($alt !== '' ? $alt : $nome, 0, 255),
            'category' => $this->sanitizeCategory($_POST['category'] ?? 'videos'),
            'uploaded_by' => $_SESSION['user_id'] ?? null,
        ]);

        $this->respond($wantsJson, true, 'Vídeo do YouTube adicionado à biblioteca!', [
            'item' => $this->mapRow($db->fetch('SELECT * FROM media_library WHERE id = ?', [$id]) ?? []),
        ]);
    }

    /* =====================================================================
     | Atualizar metadados de uma mídia
     * =================================================================== */

    public function update(string $id): void
    {
        $db = $this->db();
        $wantsJson = $this->wantsJson();
        $midia = $db->fetch('SELECT * FROM media_library WHERE id = ?', [(int) $id]);

        if (!$midia) {
            $this->respond($wantsJson, false, 'Mídia não encontrada.');
        }

        $data = [];

        if (array_key_exists('alt_text', $_POST)) {
            $alt = trim((string) $_POST['alt_text']);
            $data['alt_text'] = $alt === '' ? null : mb_substr($alt, 0, 255);
        }

        if (array_key_exists('category', $_POST)) {
            $data['category'] = $this->sanitizeCategory($_POST['category']);
        }

        if (!empty($_POST['original_name'])) {
            $data['original_name'] = mb_substr(trim((string) $_POST['original_name']), 0, 255);
        }

        if ($data !== []) {
            $db->update('media_library', $data, 'id = ?', [(int) $id]);
        }

        $this->respond($wantsJson, true, 'Mídia atualizada com sucesso.', [
            'item' => $this->mapRow($db->fetch('SELECT * FROM media_library WHERE id = ?', [(int) $id]) ?? $midia),
        ]);
    }

    /* =====================================================================
     | Excluir
     * =================================================================== */

    public function delete(string $id): void
    {
        $db = $this->db();
        $wantsJson = $this->wantsJson();
        $midia = $db->fetch('SELECT * FROM media_library WHERE id = ?', [(int) $id]);

        if (!$midia) {
            $this->respond($wantsJson, false, 'Mídia não encontrada.');
        }

        // Segurança: só remove arquivos físicos que estejam na pasta de uploads.
        // Imagens do projeto (tema, logos) são apenas desvinculadas da biblioteca.
        $relative = $this->relativeFromWebPath((string) ($midia['file_path'] ?? ''));

        if ($relative !== null && str_starts_with($relative, 'uploads/')) {
            $this->unlinkAsset($relative);
        }

        if (!empty($midia['thumbnail_path'])) {
            $thumbRelative = $this->relativeFromWebPath((string) $midia['thumbnail_path']);
            if ($thumbRelative !== null && str_starts_with($thumbRelative, 'uploads/')) {
                $this->unlinkAsset($thumbRelative);
            }
        }

        $db->delete('media_library', 'id = ?', [(int) $id]);

        $this->respond($wantsJson, true, 'Mídia removida com sucesso.');
    }

    /* =====================================================================
     | Escanear arquivos já existentes no servidor
     * =================================================================== */

    public function scan(): void
    {
        $db = $this->db();
        $assetsDir = $this->assetsDir();
        $scanned = 0;

        foreach (['uploads', 'images'] as $dir) {
            $fullPath = $assetsDir . '/' . $dir;

            if (!is_dir($fullPath)) {
                continue;
            }

            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($fullPath, \RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($files as $file) {
                if (!$file->isFile()) {
                    continue;
                }

                $ext = strtolower($file->getExtension());

                if (!in_array($ext, self::IMAGE_EXT, true)) {
                    continue;
                }

                $relative = $dir . '/' . str_replace('\\', '/', substr($file->getPathname(), strlen($fullPath) + 1));
                $webPath = '/assets/' . ltrim(str_replace('//', '/', $relative), '/');

                if ($db->fetch('SELECT id FROM media_library WHERE file_path = ?', [$webPath])) {
                    continue;
                }

                $db->insert('media_library', [
                    'filename' => $file->getFilename(),
                    'original_name' => $file->getFilename(),
                    'mime_type' => $this->mimeOf($file->getPathname()) ?: 'image/' . $ext,
                    'file_size' => (int) $file->getSize(),
                    'file_path' => $webPath,
                    'category' => $dir === 'uploads' ? 'imagens' : 'general',
                ]);

                $scanned++;
            }
        }

        $_SESSION['flash_success'] = $scanned > 0
            ? "{$scanned} imagem(ns) encontrada(s) e registrada(s) na biblioteca!"
            : 'Nenhuma imagem nova encontrada.';

        $this->redirect('/admin/midia');
    }

    /* =====================================================================
     | Internos
     * =================================================================== */

    private function assetsDir(): string
    {
        return dirname(__DIR__, 3) . '/assets';
    }

    /**
     * @return array{0:string,1:array,2:string,3:string,4:string}
     */
    private function filters(): array
    {
        $q = trim((string) ($_GET['q'] ?? ''));
        $type = (string) ($_GET['type'] ?? 'all');
        $category = (string) ($_GET['categoria'] ?? 'all');

        if (!in_array($type, ['all', 'image', 'video', 'document'], true)) {
            $type = 'all';
        }

        $where = [];
        $params = [];

        if ($q !== '') {
            $where[] = '(original_name LIKE ? OR alt_text LIKE ? OR file_path LIKE ?)';
            $like = '%' . $q . '%';
            array_push($params, $like, $like, $like);
        }

        if ($type === 'image') {
            $where[] = "mime_type LIKE 'image/%'";
        } elseif ($type === 'video') {
            $where[] = "mime_type LIKE 'video/%'";
        } elseif ($type === 'document') {
            $where[] = "mime_type NOT LIKE 'image/%' AND mime_type NOT LIKE 'video/%'";
        }

        if ($category !== 'all' && $category !== '') {
            $where[] = 'category = ?';
            $params[] = $category;
        }

        $sql = $where === [] ? '' : 'WHERE ' . implode(' AND ', $where);

        return [$sql, $params, $q, $type, $category];
    }

    private function mapRow(array $m): array
    {
        $mime = (string) ($m['mime_type'] ?? '');
        $isImage = str_starts_with($mime, 'image/');
        $isVideo = str_starts_with($mime, 'video/');
        $size = (int) ($m['file_size'] ?? 0);

        // Vídeo do YouTube: o ID vem da própria URL gravada em file_path.
        $externalId = $isVideo ? (YouTube::parse((string) ($m['file_path'] ?? '')) ?? '') : '';

        return [
            'id' => (int) ($m['id'] ?? 0),
            'url' => (string) ($m['file_path'] ?? ''),
            'thumb' => (string) (!empty($m['thumbnail_path']) ? $m['thumbnail_path'] : ($m['file_path'] ?? '')),
            'name' => (string) ($m['original_name'] ?? ''),
            'filename' => (string) ($m['filename'] ?? ''),
            'mime_type' => $mime,
            'is_image' => $isImage,
            'is_video' => $isVideo,
            'external_id' => $externalId,
            'embed_url' => $externalId !== '' ? YouTube::embedUrl($externalId) : '',
            'size' => $size,
            'size_human' => self::humanSize($size),
            'category' => (string) (!empty($m['category']) ? $m['category'] : 'general'),
            'alt_text' => (string) ($m['alt_text'] ?? ''),
            'icon' => $this->iconFor($mime, (string) ($m['original_name'] ?? '')),
            'created_at' => (string) ($m['created_at'] ?? ''),
        ];
    }

    private function iconFor(string $mime, string $name): string
    {
        if ($mime === self::MIME_YOUTUBE) {
            return 'bi-youtube';
        }
        if (str_starts_with($mime, 'video/')) {
            return 'bi-camera-video';
        }
        if (str_starts_with($mime, 'image/')) {
            return 'bi-image';
        }
        if (str_contains($mime, 'pdf') || str_ends_with(strtolower($name), '.pdf')) {
            return 'bi-file-earmark-pdf';
        }
        if (preg_match('/\.(docx?|odt)$/i', $name)) {
            return 'bi-file-earmark-word';
        }
        if (preg_match('/\.(xlsx?|csv|ods)$/i', $name)) {
            return 'bi-file-earmark-spreadsheet';
        }
        if (preg_match('/\.(zip|rar|7z)$/i', $name)) {
            return 'bi-file-earmark-zip';
        }
        return 'bi-file-earmark';
    }

    private static function humanSize(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1, ',', '.') . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 0, ',', '.') . ' KB';
        }
        return $bytes . ' B';
    }

    /**
     * Normaliza $_FILES (single ou multiple) para uma lista simples.
     */
    private function normalizeFiles(?array $files): array
    {
        if ($files === null || !isset($files['name'])) {
            return [];
        }

        if (!is_array($files['name'])) {
            return [$files];
        }

        $list = [];

        foreach ($files['name'] as $i => $name) {
            $list[] = [
                'name' => $name,
                'type' => $files['type'][$i] ?? '',
                'tmp_name' => $files['tmp_name'][$i] ?? '',
                'error' => $files['error'][$i] ?? UPLOAD_ERR_NO_FILE,
                'size' => $files['size'][$i] ?? 0,
            ];
        }

        return $list;
    }

    /**
     * @return array{ok:bool,item?:array,error?:string}
     */
    private function storeFile(array $file, string $category): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'error' => "Falha no envio de \"{$file['name']}\"."];
        }

        $ext = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, self::ALLOWED_EXT, true)) {
            return ['ok' => false, 'error' => "Tipo de arquivo não permitido ({$ext})."];
        }

        if ((int) $file['size'] > self::MAX_SIZE) {
            return ['ok' => false, 'error' => "\"{$file['name']}\" excede 10MB."];
        }

        $subDir = date('Y/m');
        $targetDir = $this->assetsDir() . '/uploads/' . $subDir;

        if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
            return ['ok' => false, 'error' => 'Não foi possível criar a pasta de uploads.'];
        }

        $baseName = $this->slugify(pathinfo((string) $file['name'], PATHINFO_FILENAME));
        $filename = $baseName . '-' . substr(bin2hex(random_bytes(4)), 0, 8) . '.' . $ext;
        $targetPath = $targetDir . '/' . $filename;

        if (!move_uploaded_file((string) $file['tmp_name'], $targetPath)) {
            return ['ok' => false, 'error' => "Erro ao salvar \"{$file['name']}\"."];
        }

        @chmod($targetPath, 0644);

        $thumbnailFile = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)
            ? $this->makeThumbnail($targetPath, pathinfo($filename, PATHINFO_FILENAME), $targetDir)
            : null;

        $mime = $this->mimeOf($targetPath) ?: ($file['type'] ?: 'application/octet-stream');

        $id = $this->db()->insert('media_library', [
            'filename' => $filename,
            'original_name' => mb_substr((string) $file['name'], 0, 255),
            'mime_type' => $mime,
            'file_size' => (int) filesize($targetPath),
            'file_path' => '/assets/uploads/' . $subDir . '/' . $filename,
            'thumbnail_path' => $thumbnailFile ? '/assets/uploads/' . $subDir . '/' . $thumbnailFile : null,
            'alt_text' => $this->guessAlt($baseName),
            'category' => $category,
            'uploaded_by' => $_SESSION['user_id'] ?? null,
        ]);

        $row = $this->db()->fetch('SELECT * FROM media_library WHERE id = ?', [$id]);

        return ['ok' => true, 'item' => $this->mapRow($row ?? [
            'id' => $id,
            'file_path' => '/assets/uploads/' . $subDir . '/' . $filename,
            'original_name' => $file['name'],
            'mime_type' => $mime,
            'file_size' => 0,
            'category' => $category,
        ])];
    }

    /**
     * Gera miniatura (máx. 480px) em WebP. Retorna o nome do arquivo ou null.
     */
    private function makeThumbnail(string $sourcePath, string $baseName, string $targetDir): ?string
    {
        if (!function_exists('imagecreatefromstring')) {
            return null;
        }

        $data = @file_get_contents($sourcePath);

        if ($data === false) {
            return null;
        }

        $image = @imagecreatefromstring($data);

        if (!$image) {
            return null;
        }

        try {
            $width = imagesx($image);
            $height = imagesy($image);
            $max = 480;

            if ($width > $max || $height > $max) {
                $ratio = min($max / $width, $max / $height);
                $newWidth = max(1, (int) round($width * $ratio));
                $newHeight = max(1, (int) round($height * $ratio));

                $thumb = imagecreatetruecolor($newWidth, $newHeight);

                // Preserva transparência em PNG/GIF/WebP
                imagealphablending($thumb, false);
                imagesavealpha($thumb, true);
                $transparent = imagecolorallocatealpha($thumb, 255, 255, 255, 127);
                imagefilledrectangle($thumb, 0, 0, $newWidth, $newHeight, $transparent);

                imagecopyresampled($thumb, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagedestroy($image);
                $image = $thumb;
            }

            $thumbName = $baseName . '-thumb.webp';

            if (@imagewebp($image, $targetDir . '/' . $thumbName, 82)) {
                return $thumbName;
            }

            $thumbName = $baseName . '-thumb.jpg';

            if (@imagejpeg($image, $targetDir . '/' . $thumbName, 82)) {
                return $thumbName;
            }

            return null;
        } catch (\Throwable) {
            return null;
        } finally {
            if ($image instanceof \GdImage) {
                imagedestroy($image);
            }
        }
    }

    private function guessAlt(string $baseName): string
    {
        $alt = trim(str_replace(['-', '_'], ' ', $baseName));
        return $alt === '' ? '' : mb_convert_case($alt, MB_CASE_TITLE, 'UTF-8');
    }

    private function slugify(string $value): string
    {
        $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
        $value = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $value) ?? '');
        $value = trim($value, '-');

        return $value === '' ? 'arquivo' : mb_substr($value, 0, 60);
    }

    private function mimeOf(string $path): ?string
    {
        if (!function_exists('mime_content_type')) {
            return null;
        }

        $mime = @mime_content_type($path);

        return is_string($mime) ? $mime : null;
    }

    private function sanitizeCategory(mixed $category): string
    {
        $category = strtolower(trim((string) $category));
        $category = preg_replace('/[^a-z0-9_-]/', '', $category) ?? '';

        return $category === '' ? 'general' : mb_substr($category, 0, 100);
    }

    /**
     * Converte "/assets/uploads/2026/09/x.jpg" em "uploads/2026/09/x.jpg".
     */
    private function relativeFromWebPath(string $webPath): ?string
    {
        if ($webPath === '' || !str_starts_with($webPath, '/assets/')) {
            return null;
        }

        return ltrim(substr($webPath, strlen('/assets/')), '/');
    }

    private function unlinkAsset(string $relative): void
    {
        $path = $this->assetsDir() . '/' . $relative;

        if (is_file($path)) {
            @unlink($path);
        }
    }

    private function wantsJson(): bool
    {
        if (isset($_GET['json'])) {
            return true;
        }

        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';

        return str_contains($accept, 'application/json')
            || strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';
    }

    private function respond(bool $json, bool $ok, string $message, array $extra = []): void
    {
        if ($json) {
            $this->json(array_merge(['success' => $ok, 'message' => $message], $extra), $ok ? 200 : 422);
        }

        $_SESSION[$ok ? 'flash_success' : 'flash_error'] = $message;
        $this->redirect('/admin/midia');
    }
}
