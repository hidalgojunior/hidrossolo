<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\BaseController;

class NewsletterController extends BaseController
{
    public function subscribe(): void
    {
        $email = trim($_POST['email'] ?? '');
        $name = trim($_POST['name'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash_error'] = 'E-mail inválido.';
            $this->back();
        }

        $db = $this->db();
        $exists = $db->fetch("SELECT id FROM newsletter WHERE email = ?", [$email]);

        if ($exists) {
            $_SESSION['flash_success'] = 'Você já está inscrito!';
        } else {
            $db->insert('newsletter', ['email' => $email, 'name' => $name ?: null]);
            $_SESSION['flash_success'] = 'Inscrição realizada com sucesso!';
        }

        $this->back();
    }
}
