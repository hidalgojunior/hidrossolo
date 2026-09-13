<?php

declare(strict_types=1);

namespace App\Core;

require_once __DIR__ . '/helpers.php';

/**
 * Motor de views em PHP puro.
 *
 * Sem Blade, sem Laravel, sem dependências externas. Os templates são arquivos
 * .php comuns e usam o objeto $view para herança de layout, seções e parciais.
 *
 * Convenções nos templates:
 *   <?php $view->layout('layouts/main'); ?>
 *   <?php $view->section('content'); ?> ... <?php $view->endSection(); ?>
 *   <?= $view->getSection('content') ?>        // dentro do layout
 *   <?= $view->partial('components.header') ?> // inclui outra view
 *   <?= e($variavel) ?>                        // saída escapada
 */
final class View
{
    private static string $basePath = '';
    private static array $shared = [];

    private string $name;
    private array $data;
    private array $sections = [];
    private array $sectionStack = [];
    private ?string $layout = null;
    private array $layoutData = [];

    private function __construct(string $name, array $data)
    {
        $this->name = $name;
        $this->data = $data;
    }

    /* ---------------------------------------------------------------------
     | Configuração
     * ------------------------------------------------------------------- */

    public static function setBasePath(string $path): void
    {
        self::$basePath = rtrim($path, '/');
    }

    public static function getBasePath(): string
    {
        return self::$basePath;
    }

    public static function share(string $key, mixed $value): void
    {
        self::$shared[$key] = $value;
    }

    public static function shareMany(array $values): void
    {
        self::$shared = array_merge(self::$shared, $values);
    }

    public static function getShared(string $key, mixed $default = null): mixed
    {
        return self::$shared[$key] ?? $default;
    }

    public static function exists(string $name): bool
    {
        return is_file(self::resolvePath($name));
    }

    private static function resolvePath(string $name): string
    {
        $relative = str_replace(['.', '\\'], '/', $name);

        return self::$basePath . '/' . $relative . '.php';
    }

    /* ---------------------------------------------------------------------
     | Renderização
     * ------------------------------------------------------------------- */

    /**
     * Renderiza uma view e retorna o HTML resultante.
     */
    public static function render(string $name, array $data = []): string
    {
        $view = new self($name, array_merge(self::$shared, $data));

        return $view->evaluate();
    }

    private function evaluate(): string
    {
        $file = self::resolvePath($this->name);

        if (!is_file($file)) {
            throw new \RuntimeException("View não encontrada: {$this->name} ({$file})");
        }

        $body = $this->includeFile($file, $this->data);

        // Se o template não declarou seções, todo o corpo vira a seção "content".
        if (!isset($this->sections['content'])) {
            $this->sections['content'] = $body;
        }

        if ($this->layout === null) {
            return $body;
        }

        $layoutFile = self::resolvePath($this->layout);

        if (!is_file($layoutFile)) {
            throw new \RuntimeException("Layout não encontrado: {$this->layout}");
        }

        $layoutData = array_merge(
            $this->data,
            $this->layoutData,
            ['content' => $this->sections['content'] ?? $body]
        );

        return $this->includeFile($layoutFile, $layoutData);
    }

    /**
     * Inclui um arquivo de template isolando o escopo e capturando a saída.
     */
    private function includeFile(string $file, array $data): string
    {
        $view = $this;

        // Torna as variáveis acessíveis diretamente no template.
        extract($data, EXTR_SKIP);

        ob_start();

        try {
            include $file;
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }

        return (string) ob_get_clean();
    }

    /* ---------------------------------------------------------------------
     | API usada dentro dos templates
     * ------------------------------------------------------------------- */

    /**
     * Declara o layout que envolve esta view.
     */
    public function layout(string $name, array $data = []): void
    {
        $this->layout = $name;
        $this->layoutData = $data;
    }

    /**
     * Inicia a captura de uma seção nomeada.
     */
    public function section(string $name): void
    {
        $this->sectionStack[] = $name;
        ob_start();
    }

    /**
     * Finaliza a captura da seção atual.
     */
    public function endSection(): void
    {
        $name = array_pop($this->sectionStack);

        if ($name === null) {
            throw new \RuntimeException('endSection() chamado sem section() correspondente.');
        }

        $this->sections[$name] = (string) ob_get_clean();
    }

    /**
     * Retorna o conteúdo já renderizado de uma seção.
     */
    public function getSection(string $name, string $default = ''): string
    {
        return $this->sections[$name] ?? $default;
    }

    public function hasSection(string $name): bool
    {
        return isset($this->sections[$name]) && $this->sections[$name] !== '';
    }

    /**
     * Renderiza uma view parcial no ponto de chamada.
     */
    public function partial(string $name, array $data = []): string
    {
        return self::render($name, array_merge($this->data, $data));
    }
}
