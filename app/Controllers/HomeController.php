<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\Service;
use App\Models\Page;
use App\Models\PageContent;

class HomeController extends BaseController
{
    public function index(): void
    {
        // Serviços em destaque
        $servicosDestaque = Service::highlighted(6);

        // Página Home
        $homePage = Page::findBySlug('home');

        // Conteúdos da home page
        $depoimentos = [];
        $banners = [];
        $hero = null;
        $servicosSection = null;
        $ctaSection = null;
        $diferenciais = [];

        if ($homePage) {
            $banners = PageContent::findAllBySection($homePage->id, 'banners');
            $hero = PageContent::findByPageAndSection($homePage->id, 'hero');
            $servicosSection = PageContent::findByPageAndSection($homePage->id, 'servicos_section');
            $depoimentos = PageContent::findAllBySection($homePage->id, 'depoimentos');
            $ctaSection = PageContent::findByPageAndSection($homePage->id, 'cta');
            $diferenciais = PageContent::findAllBySection($homePage->id, 'diferenciais');
        }

        echo $this->view('pages.home', [
            'title' => 'Hidrossolo Poços Artesianos',
            'banners' => array_map(fn($b) => $b->toArray(), $banners),
            'hero' => $hero ? $hero->toArray() : null,
            'servicos' => array_map(fn($s) => $s->toArray(), $servicosDestaque),
            'servicosSectionTitle' => $servicosSection?->title ?? null,
            'servicosSectionSub' => $servicosSection?->subtitle ?? null,
            'depoimentos' => array_map(fn($d) => $d->toArray(), $depoimentos),
            'diferenciais' => array_map(fn($d) => $d->toArray(), $diferenciais),
            'ctaSection' => $ctaSection ? $ctaSection->toArray() : null,
            'config' => $this->config('company'),
            'seo' => $this->config('seo'),
        ]);
    }
}
