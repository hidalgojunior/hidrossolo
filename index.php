<?php

/**
 * Hidrossolo Poços Artesianos - Entrypoint
 *
 * Em produção, o nginx aponta diretamente para public/index.php.
 * Este arquivo serve como fallback para desenvolvimento local com php -S.
 */

$uri = $_SERVER['REQUEST_URI'] ?? '/';
$publicFile = __DIR__ . '/public' . parse_url($uri, PHP_URL_PATH);

// Servir arquivos estáticos do public/
if ($uri !== '/' && file_exists($publicFile) && is_file($publicFile)) {
    return false;
}

// Encaminhar para o entrypoint principal
require __DIR__ . '/public/index.php';
