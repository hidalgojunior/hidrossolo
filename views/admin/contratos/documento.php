<?php
/**
 * Documento do contrato — pronto para impressão (sem o chrome do painel).
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($title) ?> - Hidrossolo</title>
    <style>
        :root { --ink: #111827; --muted: #6b7280; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Tahoma, Verdana, "Segoe UI", Geneva, sans-serif;
            font-size: 12pt;
            line-height: 1.65;
            color: var(--ink);
            background: #e5e7eb;
        }
        .toolbar {
            position: sticky; top: 0; z-index: 10;
            display: flex; flex-wrap: wrap; gap: .5rem; align-items: center;
            padding: .75rem 1rem;
            background: #0f172a; color: #fff;
            box-shadow: 0 2px 12px rgba(2,6,23,.3);
        }
        .toolbar strong { font-size: .95rem; }
        .toolbar .spacer { margin-left: auto; }
        .toolbar a, .toolbar button {
            font-family: inherit; font-size: .82rem; font-weight: 600;
            padding: .45rem .9rem; border-radius: 8px; cursor: pointer;
            border: 1px solid rgba(255,255,255,.35); background: transparent; color: #fff;
            text-decoration: none;
        }
        .toolbar .primary { background: #dc2626; border-color: #dc2626; }
        .sheet {
            max-width: 820px;
            margin: 1.5rem auto 3rem;
            padding: 3rem 3.25rem;
            background: #fff;
            box-shadow: 0 10px 40px rgba(2,6,23,.18);
            border-radius: 4px;
        }
        .sheet h2 { font-size: 14pt; text-align: center; margin: 0 0 1.4rem; }
        .sheet h3 { font-size: 12pt; margin: 1.4rem 0 .5rem; }
        .sheet p { margin: 0 0 .8rem; text-align: justify; }
        .doc-footer { margin-top: 2.5rem; padding-top: 1rem; border-top: 1px solid #e5e7eb; color: var(--muted); font-size: .8rem; text-align: center; }

        @media (max-width: 640px) {
            .sheet { padding: 1.5rem 1.25rem; margin: 1rem .6rem 2rem; font-size: 11.5pt; }
        }

        @media print {
            body { background: #fff; }
            .toolbar { display: none !important; }
            .sheet { box-shadow: none; margin: 0; padding: 0; max-width: none; border-radius: 0; }
            @page { size: A4; margin: 2cm 2.2cm; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <strong><i>Contrato <?= e($contrato['contract_number']) ?></i></strong>
        <span class="spacer"></span>
        <a href="/admin/contratos/editar/<?= e($contrato['id']) ?>">Editar</a>
        <button type="button" onclick="window.print()">Imprimir</button>
        <a class="primary" href="/admin/contratos/pdf/<?= e($contrato['id']) ?>">Baixar PDF</a>
        <a href="/admin/contratos">Voltar</a>
    </div>

    <div class="sheet">
        <?= $contrato['content'] ?>

        <div class="doc-footer">
            <?= e($config['name'] ?? 'Hidrossolo Poços Artesianos') ?>
            <?php if (!empty($contrato['generated_at'])) { ?>
                · Documento gerado em <?= e(date('d/m/Y H:i', strtotime($contrato['generated_at']))) ?>
            <?php } ?>
        </div>
    </div>
</body>
</html>
