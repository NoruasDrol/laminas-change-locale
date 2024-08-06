<?php

declare(strict_types=1);

namespace Language;
/**
 * Aufgrund von 
 * A 404 error occurred
 * Page not found.
 * The requested URL could not be matched by routing.
 * beim umschalten der Sprache, lassen wir dass mit dem URL-Übersetzen
 * erstmal sein. https://discourse.laminas.dev/t/translated-urls-receive-a-404-error-page-not-found-when-the-language-is-switched/3745
 */
use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\Http\PhpEnvironment\Request;

return [
    'controllers' => [
        'factories' => [
            Controller\LanguageController::class => InvokableFactory::class,
        ],
    ],
    'service_manager' => [
        'aliases' => [
            'translator' => \Laminas\Mvc\I18n\Translator::class,
        ],
        'factories' => [
            Request::class => InvokableFactory::class,
        ],
    ],
    'view_helpers' => [
        'invokables' => [
            'language' => View\Helper\LanguageViewHelper::class
        ],
        'factories' => [
            View\Helper\LanguageViewHelper::class => View\Helper\Factory\LanguageViewHelperFactory::class,
        ],
    ],
    'translator' => [
        'translation_files' => [
            [
                'type' => 'phparray',
                'filename' => __DIR__ .  '/../locale/de.mo',
                'locale' => 'de',
            ],
        ],
        'translation_file_patterns' => [
            [
                'type'     => 'phparray',
                'base_dir' =>  __DIR__ .  '/../locale',
                'pattern'  => '%s.mo',
            ],
            [
                'type'     => \Laminas\I18n\Translator\Loader\PhpArray::class,
                'base_dir' => \Laminas\I18n\Translator\Resources::getBasePath(),
                'pattern'  => \Laminas\I18n\Translator\Resources::getPatternForValidator(),
            ],
        ],
    ],
];