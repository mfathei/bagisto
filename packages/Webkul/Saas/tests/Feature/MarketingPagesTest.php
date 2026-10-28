<?php

// ==== Public Pages ====

it('renders every public marketing page', function (string $path) {
    $this->get($path)->assertOk();
})->with([
    '/',
    '/features',
    '/solutions',
    '/pricing',
    '/resources',
    '/about',
    '/contact',
    '/privacy',
    '/terms',
    '/refund-policy',
    '/merchant-login',
]);

it('switches the marketing site to a right-to-left locale', function () {
    $this->get('/locale/ar')
        ->assertRedirect()
        ->assertSessionHas('saas_marketing_locale', 'ar');

    $this->get('/')
        ->assertOk()
        ->assertSee('dir="rtl"', false)
        ->assertSee('lang="ar"', false);
});

it('rejects a locale that is not translated', function () {
    $this->get('/locale/xx')->assertNotFound();
});

// ==== System Files ====

it('serves a sitemap and robots file for the platform host', function () {
    $this->get('/sitemap.xml')
        ->assertOk()
        ->assertHeader('Content-Type', 'application/xml; charset=UTF-8');

    $this->get('/robots.txt')
        ->assertOk()
        ->assertSee('Sitemap:');
});
