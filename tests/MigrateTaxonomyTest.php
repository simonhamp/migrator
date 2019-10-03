<?php

namespace Tests;

use Tests\TestCase;
use Statamic\Migrator\YAML;
use Tests\Console\Foundation\InteractsWithConsole;

class MigrateTaxonomyTest extends TestCase
{
    protected function path($append = null)
    {
        return collect([base_path('content/taxonomies'), $append])->filter()->implode('/');
    }

    /** @test */
    function it_can_migrate_a_taxonomy()
    {
        $this->assertFileNotExists($this->path('tags'));
        $this->assertFileNotExists($this->path('tags.yaml'));

        $this->artisan('statamic:migrate:taxonomy', ['handle' => 'tags']);

        $this->assertFileExists($this->path('tags.yaml'));
        $this->assertCount(2, $this->files->files($this->path('tags')));
    }

    /** @test */
    function it_migrates_yaml_config()
    {
        $this->artisan('statamic:migrate:taxonomy', ['handle' => 'tags']);

        $expected = [
            'title' => 'Tags',
            'blueprints' => [
                'tag',
            ],
            'route' => '/blog/tags/{slug}',
        ];

        $this->assertParsedYamlEquals($expected, $this->path('tags.yaml'));
    }

    /** @test */
    function it_migrates_without_a_route()
    {
        $this->files->delete($this->sitePath('settings/routes.yaml'));

        $this->artisan('statamic:migrate:taxonomy', ['handle' => 'tags']);

        $this->assertParsedYamlNotHasKey('route', $this->path('tags.yaml'));
    }

    /** @test */
    function it_migrates_term_content_as_document_content()
    {
        $this->artisan('statamic:migrate:taxonomy', ['handle' => 'tags']);

        $expected = <<<EOT
---
title: spring
---
Spring has sprung!
EOT;

        $this->assertEquals($expected, $this->files->get($this->path('tags/spring.yaml')));
    }
}