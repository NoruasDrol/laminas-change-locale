<?php
declare(strict_types=1);

namespace Language\Controller;
/*
 * 
 */

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Mvc\MvcEvent;
use Laminas\Stdlib\ArrayObject;
use Laminas\Session\SessionManager;

/**
 * Description of LanguageController
 *
 * @author Herdle
 */
class LanguageController extends AbstractActionController{
    
    /**
     * @var ArrayObject $languages
     */
    protected $languages;
    
    /**
     * @var String $stdLang
     */
    private $stdLang = "de";
    
    /**
     *
     * @var string $usedLocale
     */
    private $usedLocale;
    
    /**
     * Eigentlisch schon wieder falsch weil der scann nach den sprachen erfolgt in
     * $this->scannForLanguageFiles()... setzen der Locale mit beachtung des session status.
     * wird vor dem zustimmen zu cookies eine sprache ausgewählt wird diese in einem locale cookie
     * gespeichert, wurde den cookies zugestimmt wird der locale cookie wieder entsorgt und der wert in dem
     * session cookie gespeichert. wenn ich rausfinde wie genau ich die session anspreche ^^
     * @param MvcEvent $event
     */
    public function doLanguageScann (MvcEvent $event){
        $application = $event->getApplication();
        $serviceManager = $application->getServiceManager();
        $config = $serviceManager->get('config');
        $translatorConfig = $config['translator'];
        $pattern = $translatorConfig['translation_file_patterns'];
        if (!$this->languages){
            $this->scannForLanguageFiles($pattern);
        }
        $request = $application->getRequest();
        $cookies = $request->getCookie();
        $session = $serviceManager->get(SessionManager::class);
        if ($request->getPost()->language){
            $this->processLanguageFromForm($request->getPost()->language);
        }else if ($session->sessionExists() AND $session->isValid()){
            $this->processLanguageFromSession($config['session_config'],$cookies,$serviceManager);
        }else if(isset($cookies->locale)){
            $this->processLanguageFromCookie($cookies->locale);
        }else{
            \Locale::setDefault($this->stdLang);
            setlocale(LC_ALL, \Locale::getDefault());
            $this->usedLocale = \Locale::getDefault();
        }
        if ($this->usedLocale){
            if ($session->sessionExists() AND $session->isValid()){
                $sessionContainer = $serviceManager->get(\Laminas\Session\Container::class);
                $sessionContainer->locale = $this->usedLocale;
                if (isset($cookies->locale)){
                    setcookie('locale', '', time() -10);
                }
            }else{
                setcookie('locale', $this->usedLocale, time() + 3600, "/");
            }
        }
    }
    
    /**
     * die auf der Seite ausgewählte sprache als standard-locale setzen
     * existiert die ausgewählte sprache durch z.B.manipulation des Formular vom user,
     * wird die Standard-Sprache gesetzt.
     * @param type $languageFromForm die Nutzerauswahl (lowercased ISO 3166 ALPHA-2)
     */
    private function processLanguageFromForm($languageFromForm){
        #formular zur Sprachauswahl wurde verwendet die neue locale setzen
        if (array_search($languageFromForm, $this->languages) == false){
            \Locale::setDefault($this->stdLang);
            setlocale(LC_ALL, \Locale::getDefault());
            $this->usedLocale = \Locale::getDefault();
        }else{
            \Locale::setDefault($languageFromForm);
            setlocale(LC_ALL, \Locale::getDefault());
            $this->usedLocale = \Locale::getDefault();
        }
    }
    
    /**
     * Setzen der Sprache aufgrund eines Session eintrag.
     * Auch hier wird auf gegebene Sprachdateien geprüft und bei missmatch die stdLocale verwendet
     * @param type $config das array session_config aus der globalen konfig
     * @param type $cookies die Cookies des request
     */
    private function processLanguageFromSession($config,$cookies,$serviceManager){
        $sessionName = $config['name'];
        if (isset($cookies->$sessionName)){
            $sessionContainer = $serviceManager->get(\Laminas\Session\Container::class);
            if (!isset($sessionContainer->locale) OR array_search($sessionContainer->locale, $this->languages) == false){
                \Locale::setDefault($this->stdLang);
                setlocale(LC_ALL, \Locale::getDefault());
                $this->usedLocale = \Locale::getDefault();
            }else{
                \Locale::setDefault($sessionContainer->locale);
                setlocale(LC_ALL, \Locale::getDefault());
                $this->usedLocale = \Locale::getDefault();
                if (isset($cookies->locale)){
                    setcookie('locale', '', time() -10);
                }
            }
        }else{
            \Locale::setDefault($this->stdLang);
            setlocale(LC_ALL, \Locale::getDefault());
            $this->usedLocale = \Locale::getDefault();
        }
    }
    
    /**
     * Setzen der Sprache aus einem Cookie erfolgt nur wenn es keinen Cookie mit dem Sessionnamen gibt
     * @param type $locale der Inhalt des cookie->locale
     */
    private function processLanguageFromCookie($locale){
        if (array_search($locale, $this->languages) == false){
            \Locale::setDefault($this->stdLang);
            setlocale(LC_ALL, \Locale::getDefault());
            $this->usedLocale = \Locale::getDefault();
        }else{
            \Locale::setDefault($locale);
            setlocale(LC_ALL, \Locale::getDefault());
            $this->usedLocale = \Locale::getDefault();
        }
    }
    
    /**
     * Die aus der configuration des translator gezogenen translation_file_patterns durchgehen um alle übersetzungsdateien
     * durchzugehen. diese liegen im format de.mo/en.mo/etz.mo vor und wir erruieren hier die verfügbaren sprachen
     * und speichern diese in $this->languages
     * @param array $filePattern Die aus der configuration des translator gezogenen translation_file_patterns
     * @return array|null $this->languages
     */
    public function scannForLanguageFiles($filePattern){
        $languages = [];
        foreach($filePattern as $pattern){
            if (strpos($pattern['pattern'], 'Laminas_Validate') !== false){
                continue;
            }
            $fileDir = scandir($pattern['base_dir']);
            foreach ($fileDir as $file) {
                if ($file === '.' OR $file === '..'){
                    continue;
                }
                if (strlen($file) > 3){
                    $languages[substr($file, 0, -3)] = substr($file, 0, -3);
                }
            }
        }
        if (!empty($languages)){
            $this->languages = array_unique($languages);
        }
    }
    
    public function setStandardLocale(string $locale){
        $this->stdLang = $locale;
    }
    
    public function getStandardLocale(){
        return $this->stdLang;
    }
}
