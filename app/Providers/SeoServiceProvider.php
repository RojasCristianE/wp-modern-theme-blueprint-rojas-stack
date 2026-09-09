<?php

declare(strict_types=1);

namespace RojasStack\Providers;

use Spatie\SchemaOrg\Schema;
use Timber\Timber;

/**
 * SeoServiceProvider
 * 
 * Orquesta la generación de JSON-LD estructurado para SEO.
 * Utiliza los metadatos de Carbon Fields y datos de WordPress.
 */
class SeoServiceProvider
{
    /**
     * Config del módulo 'seo' (app/Config/features.php).
     *
     * @var array<string, mixed>
     */
    private array $config;

    /**
     * @param array<string, mixed> $config
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    public function register(): void
    {
        add_action('wp_head', [$this, 'render_schemas'], 5);
    }

    public function render_schemas(): void
    {
        echo "\n<!-- Rojas Stack SEO Engine (Spatie Schema-org) -->\n";
        
        $this->render_organization_schema();

        if (is_single()) {
            $this->render_article_schema();
        }

        echo "<!-- End Rojas Stack SEO Engine -->\n";
    }

    /**
     * Genera el esquema de la Organización basado en Theme Options.
     */
    private function render_organization_schema(): void
    {
        // Prioridad: features.php → Carbon Fields → bloginfo
        $company_name = $this->config['organization_name'] ?? null;
        $company_name = (is_string($company_name) && $company_name !== '')
            ? $company_name
            : rs_string(carbon_get_theme_option('rs_company_name') ?: get_bloginfo('name'));
        $logo_url     = carbon_get_theme_option('rs_logo');
        $social_links = carbon_get_theme_option('rs_social_links') ?: [];

        /** @var array<int, array<string, mixed>> $social_links */
        $social_urls = array_map(
            static fn (array $item): string => rs_string($item['url'] ?? ''),
            $social_links
        );

        $schema = Schema::organization()
            ->name($company_name)
            ->url(get_bloginfo('url'))
            ->logo(is_string($logo_url) && $logo_url !== '' ? $logo_url : '')
            ->sameAs(array_values(array_filter($social_urls)));

        echo $schema->toScript();
    }

    /**
     * Genera el esquema de Artículo para posts individuales.
     */
    private function render_article_schema(): void
    {
        $post = Timber::get_post();
        if (!$post) return;

        $author = $post->author();
        $thumb  = $post->thumbnail();

        $schema = Schema::article()
            ->headline($post->title())
            ->datePublished(new \DateTimeImmutable($post->date('c')))
            ->dateModified(new \DateTimeImmutable($post->modified_date('c')))
            ->author(Schema::person()->name($author ? $author->name() : ''))
            ->image($thumb instanceof \Timber\Image ? $thumb->src() : '');

        echo $schema->toScript();
    }
}
