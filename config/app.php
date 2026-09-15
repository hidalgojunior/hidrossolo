<?php

/**
 * Configuração da aplicação
 */
return [
    'name' => $_ENV['APP_NAME'] ?? 'Hidrossolo Poços Artesianos',
    'url' => $_ENV['APP_URL'] ?? 'http://localhost:8080',
    'env' => $_ENV['APP_ENV'] ?? 'production',
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),

    // Configurações da empresa
    'company' => [
        'name' => 'Hidrossolo Poços Artesianos',
        'address' => 'R. Assad Haddad, 584 - Parque das Indústrias',
        'city' => 'Marília',
        'state' => 'SP',
        'zip' => '17519-700',
        'phone' => '(14) 3413-2437',
        'whatsapp' => '(14) 99123-4567',
        'email' => 'hidrossolo@hidrossolopocos.com.br',
    ],

    // SEO padrão
    'seo' => [
        'title' => 'Hidrossolo Poços Artesianos - Perfuração, Limpeza e Manutenção',
        'description' => 'Especialistas em perfuração de poços artesianos, licenciamento, outorgas, limpeza e manutenção. Atendimento em Marília e região.',
        'keywords' => 'poços artesianos, perfuração, limpeza de poços, outorga, licenciamento, Marília',
    ],
];
