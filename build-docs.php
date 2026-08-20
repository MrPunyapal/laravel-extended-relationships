<?php

declare(strict_types=1);

require __DIR__.'/vendor/autoload.php';

use Docsmith\Docsmith;

$output = __DIR__.'/docs';

Docsmith::make()
    ->source(__DIR__.'/md')
    ->output($output)
    ->title('Laravel Extended Relationships')
    ->description('Additional, more efficient relationship methods for Laravel Eloquent models.')
    ->siteUrl('https://mrpunyapal.github.io/laravel-extended-relationships')
    ->repositoryUrl('https://github.com/mrpunyapal/laravel-extended-relationships')
    ->editBranch('main')
    ->editPrefix('md')
    ->navigationOrder([
        'index.md',
        'installation.md',
        'usage.md',
        'belongs-to-many-keys.md',
        'has-many-keys.md',
        'has-many-array-column.md',
        'belongs-to-array-column.md',
    ])
    ->ogGeneratedPerPage()
    ->build();

// The sitemap <lastmod> is derived from file mtimes, which differ between
// local builds and CI checkouts. Strip it so the generated site is
// deterministic and CI never produces a docs commit loop.
$sitemap = $output.'/sitemap.xml';

if (is_file($sitemap)) {
    $normalized = (string) preg_replace(
        '/\s*<lastmod>[^<]*<\/lastmod>/',
        '',
        (string) file_get_contents($sitemap),
    );

    if ($normalized !== '') {
        file_put_contents($sitemap, $normalized);
    }
}