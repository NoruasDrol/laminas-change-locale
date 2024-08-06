<?php

declare(strict_types=1);

namespace Language;

use Laminas\Mvc\MvcEvent;
use Laminas\Validator\AbstractValidator;

class Module
{
    public function getConfig(): array{
        /** @var array $config */
        $config = include __DIR__ . '/../config/module.config.php';
        return $config;
    }
    
    public function onBootstrap(MvcEvent $event){
        $application = $event->getApplication();
        $eventManager = $application->getEventManager();
        #$eventManager->attach(MvcEvent::EVENT_DISPATCH, [$this, 'onDispatch'], 100);
        $languagecontroller = new Controller\LanguageController();
        $eventManager->attach(MvcEvent::EVENT_ROUTE, [$languagecontroller, 'doLanguageScann'], 100);
        $serviceManager = $application->getServiceManager();
        $translator = $serviceManager->get('translator');
        AbstractValidator::setDefaultTranslator($translator);
    }
}
