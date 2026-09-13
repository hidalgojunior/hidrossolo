<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\BaseController;

class MenusController extends BaseController
{
    public function index(): void
    {
        $db = $this->db();

        $menus = $db->fetchAll("SELECT * FROM menus ORDER BY name");

        echo $this->view('admin.menus.index', [
            'title' => 'Gerenciar Menus',
            'menus' => $menus,
            'config' => $this->config('company'),
        ]);
    }

    public function store(): void
    {
        $data = $this->validate([
            'name' => 'required|max:100',
            'location' => 'required|max:100',
        ]);

        $this->db()->insert('menus', [
            'name' => $data['name'],
            'location' => $data['location'],
        ]);

        $_SESSION['flash_success'] = 'Menu criado com sucesso!';
        $this->redirect('/admin/menus');
    }

    /**
     * Itens de um menu.
     *
     * Atenção: o Router injeta os parâmetros da rota por nome (PHP 8), então o
     * parâmetro precisa se chamar exatamente como o curinga da rota (`{id}`).
     */
    public function items(string $id): void
    {
        $db = $this->db();

        $menu = $db->fetch("SELECT * FROM menus WHERE id = ?", [$id]);
        if (!$menu) {
            $_SESSION['flash_error'] = 'Menu não encontrado.';
            $this->redirect('/admin/menus');
        }

        $items = $db->fetchAll(
            "SELECT * FROM menu_items WHERE menu_id = ? ORDER BY sort_order",
            [$id]
        );

        // Buscar páginas para sugestão de links
        $pages = $db->fetchAll("SELECT id, title, slug FROM pages WHERE status = 'published' ORDER BY title");
        $servicos = $db->fetchAll("SELECT id, title, slug FROM services WHERE active = 1 ORDER BY title");

        echo $this->view('admin.menus.items', [
            'title' => 'Itens do Menu: ' . $menu['name'],
            'menu' => $menu,
            'items' => $items,
            'pages' => $pages,
            'servicos' => $servicos,
            'config' => $this->config('company'),
        ]);
    }

    public function storeItem(string $id): void
    {
        $data = $this->validate([
            'title' => 'required|max:255',
            'url' => 'required|max:500',
        ]);

        $this->db()->insert('menu_items', [
            'menu_id' => (int)$id,
            'parent_id' => !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null,
            'title' => $data['title'],
            'url' => $data['url'],
            'target' => $_POST['target'] ?? '_self',
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
        ]);

        $_SESSION['flash_success'] = 'Item adicionado ao menu!';
        $this->redirect('/admin/menus/' . $id . '/itens');
    }

    public function updateItem(string $id, string $itemId): void
    {
        $db = $this->db();

        $item = $db->fetch("SELECT * FROM menu_items WHERE id = ? AND menu_id = ?", [(int)$itemId, (int)$id]);
        if (!$item) {
            $_SESSION['flash_error'] = 'Item não encontrado.';
            $this->redirect('/admin/menus/' . $id . '/itens');
        }

        $parentId = trim((string) ($_POST['parent_id'] ?? ''));

        $db->update('menu_items', [
            'title' => $_POST['title'] ?? $item['title'],
            'url' => $_POST['url'] ?? $item['url'],
            'parent_id' => $parentId !== '' && $parentId !== '0' ? (int) $parentId : null,
            'target' => $_POST['target'] ?? $item['target'],
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
        ], 'id = ?', [(int)$itemId]);

        $_SESSION['flash_success'] = 'Item atualizado!';
        $this->redirect('/admin/menus/' . $id . '/itens');
    }

    public function deleteItem(string $id, string $itemId): void
    {
        $db = $this->db();
        $db->delete('menu_items', 'id = ? AND menu_id = ?', [(int)$itemId, (int)$id]);
        $_SESSION['flash_success'] = 'Item removido!';
        $this->redirect('/admin/menus/' . $id . '/itens');
    }

    public function reorder(string $id): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $order = $input['order'] ?? [];
        $db = $this->db();
        foreach ($order as $index => $itemId) {
            $db->update('menu_items', ['sort_order' => $index], 'id = ? AND menu_id = ?', [(int)$itemId, (int)$id]);
        }
        $this->json(['success' => true]);
    }
}
