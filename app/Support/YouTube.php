<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Utilitários para vídeos hospedados no YouTube.
 *
 * O arquivo do vídeo continua no YouTube (não ocupa espaço nem banda do
 * servidor): aqui guardamos apenas o ID e montamos a URL de exibição, o
 * endereço do player embutido e a miniatura.
 *
 * Formatos aceitos ao colar o link:
 *   - youtube.com/watch?v=ID   (vídeo normal)
 *   - youtu.be/ID              (link curto)
 *   - youtube.com/shorts/ID    (Shorts)
 *   - youtube.com/embed/ID     (código de incorporação)
 *   - youtube.com/live/ID      (transmissão já encerrada)
 *   - youtube.com/v/ID         (formato antigo)
 *   - o próprio ID (11 caracteres)
 */
final class YouTube
{
    /** Tamanho fixo do ID de vídeo do YouTube. */
    private const ID = '[A-Za-z0-9_-]{11}';

    /** @var list<string> */
    private const PADROES = [
        '~youtu\.be/([A-Za-z0-9_-]{11})~i',
        '~youtube(?:-nocookie)?\.com/watch\?(?:[^&\s]*&)*v=([A-Za-z0-9_-]{11})~i',
        '~youtube(?:-nocookie)?\.com/(?:shorts|embed|live|v)/([A-Za-z0-9_-]{11})~i',
    ];

    /**
     * Extrai o ID do vídeo a partir de um link colado pelo usuário.
     * Retorna null quando o link não é de um vídeo do YouTube.
     */
    public static function parse(string $url): ?string
    {
        $url = trim($url);

        if ($url === '') {
            return null;
        }

        // O usuário pode ter colado só o ID.
        if (preg_match('/^' . self::ID . '$/', $url) === 1) {
            return $url;
        }

        foreach (self::PADROES as $padrao) {
            if (preg_match($padrao, $url, $encontrado) === 1) {
                return $encontrado[1];
            }
        }

        return null;
    }

    /** URL pública do vídeo (usada como caminho da mídia na biblioteca). */
    public static function watchUrl(string $id): string
    {
        return 'https://www.youtube.com/watch?v=' . $id;
    }

    /**
     * URL do player embutido. Usa o domínio "nocookie", que só grava cookies
     * depois que o visitante dá o play — mais adequado à LGPD.
     */
    public static function embedUrl(string $id): string
    {
        return 'https://www.youtube-nocookie.com/embed/' . $id
            . '?rel=0&modestbranding=1&playsinline=1';
    }

    /** Miniatura oficial do vídeo (capa 480x360). */
    public static function thumbnail(string $id): string
    {
        return 'https://i.ytimg.com/vi/' . $id . '/hqdefault.jpg';
    }
}
