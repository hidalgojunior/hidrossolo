<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\BaseController;

class SEOController extends BaseController
{
    public function index(): void
    {
        $db = $this->db();

        // Configurações globais de SEO
        $settings = $db->fetchAll("SELECT * FROM site_settings WHERE `group` = 'seo'");
        $settingsMap = [];
        foreach ($settings as $s) {
            $settingsMap[$s['key']] = $s['value'];
        }

        echo $this->view('admin.seo.index', [
            'title' => 'Configurações de SEO',
            'settings' => $settingsMap,
            'config' => $this->config('company'),
        ]);
    }

    public function update(): void
    {
        $db = $this->db();
        $fields = [
            'site_title', 'meta_description', 'meta_keywords',
            'og_title', 'og_description', 'og_image',
            'google_analytics', 'google_verification',
        ];

        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                // Upsert: atualiza ou insere
                $existing = $db->fetch("SELECT id FROM site_settings WHERE `key` = ?", [$field]);
                if ($existing) {
                    $db->update('site_settings', ['value' => $_POST[$field]], '`key` = ?', [$field]);
                } else {
                    $db->insert('site_settings', [
                        'key' => $field,
                        'value' => $_POST[$field],
                        'group' => 'seo',
                    ]);
                }
            }
        }

        $_SESSION['flash_success'] = 'Configurações de SEO atualizadas!';
        $this->redirect('/admin/seo');
    }
}
