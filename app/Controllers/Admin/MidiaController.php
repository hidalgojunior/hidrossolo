<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\BaseController;

class MidiaController extends BaseController
{
    public function index(): void
    {
        $db = $this->db();

        $categoria = $_GET['categoria'] ?? 'all';
        $pagina = (int)($_GET['page'] ?? 1);
        $perPage = 20;
        $offset = ($pagina - 1) * $perPage;

        $where = $categoria !== 'all' ? "WHERE category = ?" : "";
        $params = $categoria !== 'all' ? [$categoria] : [];

        $midias = $db->fetchAll(
            "SELECT * FROM media_library {$where} ORDER BY created_at DESC LIMIT ? OFFSET ?",
            array_merge($params, [$perPage, $offset])
        );

        $total = $db->fetch(
            "SELECT COUNT(*) as total FROM media_library {$where}",
            $params
        );

        $categorias = $db->fetchAll(
            "SELECT DISTINCT category FROM media_library ORDER BY category"
        );

        echo $this->view('admin.midia.index', [
            'title' => 'Biblioteca de Mídias',
            'midias' => $midias,
            'categorias' => $categorias,
            'categoria' => $categoria,
            'total' => $total['total'] ?? 0,
            'pagina' => $pagina,
            'perPage' => $perPage,
            'config' => $this->config('company'),
        ]);
    }

    public function upload(): void
    {
        if (empty($_FILES['file'])) {
            $_SESSION['flash_error'] = 'Nenhum arquivo enviado.';
            $this->redirect('/admin/midia');
        }

        $file = $_FILES['file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx', 'xls', 'xlsx'];

        if (!in_array($ext, $allowed)) {
            $_SESSION['flash_error'] = 'Tipo de arquivo não permitido.';
            $this->redirect('/admin/midia');
        }

        if ($file['size'] > 10 * 1024 * 1024) {
            $_SESSION['flash_error'] = 'Arquivo muito grande (máximo 10MB).';
            $this->redirect('/admin/midia');
        }

        $uploadDir = dirname(__DIR__, 3) . '/public/assets/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = uniqid() . '.' . $ext;
        $filepath = $uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            $_SESSION['flash_error'] = 'Erro ao salvar arquivo.';
            $this->redirect('/admin/midia');
        }

        // Converter para WebP se for imagem
        $thumbnailPath = null;
        if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
            $thumbnailPath = $this->convertToWebP($filepath, $ext, $uploadDir);
        }

        $this->db()->insert('media_library', [
            'filename' => $filename,
            'original_name' => $file['name'],
            'mime_type' => $file['type'],
            'file_size' => $file['size'],
            'file_path' => '/assets/uploads/' . $filename,
            'thumbnail_path' => $thumbnailPath ? '/assets/uploads/' . $thumbnailPath : null,
            'category' => $_POST['category'] ?? 'general',
            'uploaded_by' => $_SESSION['user_id'] ?? null,
        ]);

        $_SESSION['flash_success'] = 'Arquivo enviado com sucesso!';
        $this->redirect('/admin/midia');
    }
    public function scan(): void
    {
        $db = $this->db();
        $baseDir = dirname(__DIR__, 3) . '/public/assets/';
        $count = 0;

        // Diretórios a escanear
        $dirs = ['images/', 'uploads/'];
        $imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];

        foreach ($dirs as $dir) {
            $fullPath = $baseDir . $dir;
            if (!is_dir($fullPath)) continue;

            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($fullPath, \RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($files as $file) {
                if (!$file->isFile()) continue;
                $ext = strtolower($file->getExtension());
                if (!in_array($ext, $imageExts)) continue;

                $relativePath = '/assets/' . $dir . str_replace($fullPath, '', $file->getPathname());
                $relativePath = str_replace('//', '/', $relativePath);

                // Verificar se já existe
                $exists = $db->fetch("SELECT id FROM media_library WHERE file_path = ?", [$relativePath]);
                if ($exists) continue;

                $db->insert('media_library', [
                    'filename' => $file->getFilename(),
                    'original_name' => $file->getFilename(),
                    'mime_type' => mime_content_type($file->getPathname()),
                    'file_size' => $file->getSize(),
                    'file_path' => $relativePath,
                    'category' => str_contains($dir, 'uploads') ? 'uploads' : 'imagens',
                ]);
                $count++;
            }
        }

        $_SESSION['flash_success'] = "{$count} imagens encontradas e registradas na galeria!";
        $this->redirect('/admin/midia');
    }

    public function delete(string $id): void
    {
        $db = $this->db();
        $midia = $db->fetch("SELECT * FROM media_library WHERE id = ?", [(int)$id]);

        if (!$midia) {
            $_SESSION['flash_error'] = 'Mídia não encontrada.';
            $this->redirect('/admin/midia');
        }

        // Remover arquivo físico
        $filePath = dirname(__DIR__, 3) . '/public' . $midia['file_path'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        if ($midia['thumbnail_path']) {
            $thumbPath = dirname(__DIR__, 3) . '/public' . $midia['thumbnail_path'];
            if (file_exists($thumbPath)) unlink($thumbPath);
        }

        // Remover do banco
        $db->delete('media_library', 'id = ?', [(int)$id]);

        $_SESSION['flash_success'] = 'Mídia removida com sucesso!';
        $this->redirect('/admin/midia');
    }
    private function convertToWebP(string $sourcePath, string $ext, string $uploadDir): ?string
    {
        try {
            $image = match ($ext) {
                'jpeg', 'jpg' => imagecreatefromjpeg($sourcePath),
                'png' => imagecreatefrompng($sourcePath),
                default => null,
            };

            if (!$image) return null;

            $webpName = pathinfo($sourcePath, PATHINFO_FILENAME) . '.webp';
            $webpPath = $uploadDir . $webpName;

            imagewebp($image, $webpPath, 80);
            imagedestroy($image);

            return $webpName;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
