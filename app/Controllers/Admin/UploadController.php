<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\BaseController;

class UploadController extends BaseController
{
    /**
     * Upload de imagem via editor HTML (Summernote/TinyMCE style)
     */
    public function editorUpload(): void
    {
        if (empty($_FILES['file'])) {
            $this->json(['error' => 'Nenhum arquivo enviado.'], 400);
        }

        $file = $_FILES['file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];

        if (!in_array($ext, $allowed)) {
            $this->json(['error' => 'Tipo de arquivo não permitido. Use JPG, PNG, GIF, WebP ou SVG.'], 400);
        }

        if ($file['size'] > 10 * 1024 * 1024) {
            $this->json(['error' => 'Arquivo muito grande (máximo 10MB).'], 400);
        }

        // Criar diretório se não existir
        $uploadDir = dirname(__DIR__, 3) . '/public/assets/uploads/editor/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Organizar por ano/mês
        $subDir = date('Y/m');
        $fullDir = $uploadDir . $subDir;
        if (!is_dir($fullDir)) {
            mkdir($fullDir, 0755, true);
        }

        $filename = uniqid('img_') . '.' . $ext;
        $filepath = $fullDir . '/' . $filename;
        $relativePath = '/assets/uploads/editor/' . $subDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            $this->json(['error' => 'Erro ao salvar arquivo.'], 500);
        }

        // Registrar na biblioteca de mídias
        try {
            $db = $this->db();
            $db->insert('media_library', [
                'filename' => $subDir . '/' . $filename,
                'original_name' => $file['name'],
                'mime_type' => $file['type'],
                'file_size' => $file['size'],
                'file_path' => $relativePath,
                'category' => 'editor',
                'uploaded_by' => $_SESSION['user_id'] ?? null,
            ]);
        } catch (\Throwable $e) {
            // Não falhar o upload por causa da biblioteca
        }

        // Retornar URL para o editor (formato Summernote)
        $this->json(['url' => $relativePath]);
    }
}
