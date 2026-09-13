<?php
$view->layout('layouts.main');

/* ---------------------------------------------------------------------------
 | Conteúdo de fallback (usado quando o CMS ainda não tem registros)
 * ------------------------------------------------------------------------- */
$servicosFallback = [
    ['title' => 'Perfuração de Poços Artesianos', 'slug' => 'perfuracao-de-pocos', 'icon' => 'droplet', 'description' => 'Perfuração com equipamentos modernos e estudo geológico prévio, garantindo vazão e qualidade da água.'],
    ['title' => 'Limpeza e Reabilitação', 'slug' => 'limpeza-de-pocos', 'icon' => 'tools', 'description' => 'Remoção de sedimentos e incrustações para recuperar a vazão original do seu poço.'],
    ['title' => 'Licenciamento e Outorga', 'slug' => 'licenciamento-e-outorga', 'icon' => 'file-earmark-text', 'description' => 'Assessoria completa junto aos órgãos ambientais (DAEE, CETESB) para regularização do poço.'],
    ['title' => 'Instalação de Bombas', 'slug' => 'instalacao-de-bombas', 'icon' => 'gear', 'description' => 'Dimensionamento e instalação de bombas submersas, quadros elétricos e automação.'],
    ['title' => 'Manutenção Preventiva', 'slug' => 'manutencao', 'icon' => 'wrench', 'description' => 'Planos de manutenção que evitam paradas e prolongam a vida útil do sistema de captação.'],
    ['title' => 'Análise e Potabilidade', 'slug' => 'analise-da-agua', 'icon' => 'clipboard', 'description' => 'Coleta e análise laboratorial da água, com laudo técnico e recomendações de tratamento.'],
];

$diferenciaisFallback = [
    ['title' => 'Equipe Especializada', 'content' => 'Técnicos e geólogos experientes em captação de água subterrânea.'],
    ['title' => 'Equipamentos Modernos', 'content' => 'Maquinário próprio de perfuração, com manutenção e tecnologia atualizadas.'],
    ['title' => 'Responsabilidade Ambiental', 'content' => 'Atuação em conformidade com as normas técnicas e ambientais vigentes.'],
];

$stats = [
    ['value' => '+20', 'label' => 'Anos de experiência', 'icon' => 'calendar'],
    ['value' => '+1.500', 'label' => 'Poços perfurados', 'icon' => 'droplet'],
    ['value' => '100%', 'label' => 'Regularização ambiental', 'icon' => 'shield-check'],
    ['value' => '+50', 'label' => 'Municípios atendidos', 'icon' => 'geo-alt'],
];

$servicos = !empty($servicos) ? $servicos : $servicosFallback;
$diferenciais = !empty($diferenciais) ? $diferenciais : $diferenciaisFallback;
$heroTitle = !empty($hero['title']) ? $hero['title'] : 'Soluções completas em poços artesianos';
$heroSubtitle = !empty($hero['subtitle']) ? $hero['subtitle'] : 'Perfuração, licenciamento, limpeza e manutenção com equipe especializada em Marília e região.';
?>

<?php $view->section('content'); ?>


<?php
/*
 | Blocos da Home: ordem e visibilidade são definidos no CMS
 | (Admin → Home → Blocos da Home). Cada bloco é um partial em
 | views/pages/home-blocks/ carregado na ordem configurada.
 */
foreach (($blocos ?? []) as $bloco) {
    if (empty($bloco['enabled'])) {
        continue;
    }

    $partial = __DIR__ . '/home-blocks/' . basename((string) $bloco['block']) . '.php';

    if (is_file($partial)) {
        require $partial;
    }
}
?>
<?php $view->endSection(); ?>
