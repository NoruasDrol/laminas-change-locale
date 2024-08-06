<?php
namespace Language\View\Helper\Factory;
/*
 * 
 */
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Language\View\Helper\LanguageViewHelper;
use Laminas\Http\PhpEnvironment\Request;
use Laminas\Mvc\I18n\Translator;
use Laminas\Session\SessionManager;
/**
 * Description of LanguageViewHelperFactory
 *
 * @author Herdle
 */
class LanguageViewHelperFactory implements FactoryInterface{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): object {
        $request = $container->get(Request::class);
        $config = $container->get('config');
        $translator = $container->get(Translator::class);
        $sessionManager = $container->get(SessionManager::class);
        return new LanguageViewHelper($request,$translator,$sessionManager,$config);
    }
}
