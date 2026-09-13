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

    public function items(string $menuId): void
    {
        $db = $this->db();

        $menu = $db->fetch("SELECT * FROM menus WHERE id = ?", [$menuId]);
        if (!$menu) {
            $_SESSION['flash_error'] = 'Menu não encontrado.';
            $this->redirect('/admin/menus');
        }

        $items = $db->fetchAll(
            "SELECT * FROM menu_items WHERE menu_id = ? ORDER BY sort_order",
            [$menuId]
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

    public function storeItem(string $menuId): void
    {
        $data = $this->validate([
            'title' => 'required|max:255',
            'url' => 'required|max:500',
        ]);

        $this->db()->insert('menu_items', [
            'menu_id' => (int)$menuId,
            'parent_id' => !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null,
            'title' => $data['title'],
            'url' => $data['url'],
            'target' => $_POST['target'] ?? '_self',
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
        ]);

        $_SESSION['flash_success'] = 'Item adicionado ao menu!';
        $this->redirect('/admin/menus/' . $menuId . '/itens');
    }

    public function updateItem(string $menuId, string $itemId): void
    {
        $db = $this->db();

        $item = $db->fetch("SELECT * FROM menu_items WHERE id = ? AND menu_id = ?", [(int)$itemId, (int)$menuId]);
        if (!$item) {
            $_SESSION['flash_error'] = 'Item não encontrado.';
            $this->redirect('/admin/menus/' . $menuId . '/itens');
        }

        $db->update('menu_items', [
            'title' => $_POST['title'] ?? $item['title'],
            'url' => $_POST['url'] ?? $item['url'],
            'parent_id' => $_POST['parent_id'] !== '' ? (int)$_POST['parent_id'] : null,
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
        ], 'id = ?', [(int)$itemId]);

        $_SESSION['flash_success'] = 'Item atualizado!';
        $this->redirect('/admin/menus/' . $menuId . '/itens');
    }

    public function deleteItem(string $menuId, string $itemId): void
    {
        $db = $this->db();
        $db->delete('menu_items', 'id = ? AND menu_id = ?', [(int)$itemId, (int)$menuId]);
        $_SESSION['flash_success'] = 'Item removido!';
        $this->redirect('/admin/menus/' . $menuId . '/itens');
    }

    public function reorder(string $menuId): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $order = $input['order'] ?? [];
        $db = $this->db();
        foreach ($order as $index => $id) {
            $db->update('menu_items', ['sort_order' => $index], 'id = ? AND menu_id = ?', [(int)$id, (int)$menuId]);
        }
        $this->json(['success' => true]);
    }
}
