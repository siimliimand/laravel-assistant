<?php

namespace App\Helpers;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\MarkdownConverter;

class Markdown
{
    protected static ?MarkdownConverter $converter = null;

    /**
     * Get or create the Markdown converter instance.
     */
    protected static function getConverter(): MarkdownConverter
    {
        if (static::$converter === null) {
            $environment = new Environment([
                'html_input' => 'escape',
                'allow_unsafe_links' => false,
                'max_nesting_level' => 100,
            ]);

            $environment->addExtension(new CommonMarkCoreExtension);
            $environment->addExtension(new GithubFlavoredMarkdownExtension);

            static::$converter = new MarkdownConverter($environment);
        }

        return static::$converter;
    }

    /**
     * Convert markdown to HTML.
     */
    public static function convert(string $markdown): string
    {
        return static::getConverter()->convert($markdown)->getContent();
    }

    /**
     * Convert markdown to HTML with custom configuration.
     */
    public static function convertWithConfig(string $markdown, array $config): string
    {
        $environment = new Environment(array_merge([
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
            'max_nesting_level' => 100,
        ], $config));

        $environment->addExtension(new CommonMarkCoreExtension);
        $environment->addExtension(new GithubFlavoredMarkdownExtension);

        $converter = new MarkdownConverter($environment);

        return $converter->convert($markdown)->getContent();
    }
}
