<?php
namespace Language\View\Helper;
/*
 * 
 */
use Laminas\View\Helper\AbstractHelper;
use Laminas\Http\PhpEnvironment\Request;
use Laminas\Mvc\I18n\Translator;
use Laminas\Session\SessionManager;
use Laminas\Session\Container;
/**
 * Description of Language
 *
 * @author Herdle
 */
class LanguageViewHelper extends AbstractHelper{
    
    /**
     * existing Languages
     * @var array $languages
     */
    protected $languages;
    
    /**
     * the active/choosen Language
     * @var string $selected
     */
    protected $selected;
    
    /**
     * über Factory geladene konfiguration des translator
     * @var array $translatorConfig
     */
    protected $config;
    
    protected $request;
    
    protected $translator;
    
    protected $sessionManager;
    
    public function __construct(Request $request, Translator $translator, SessionManager $sessionManager, array $config) {
        $this->config = $config;
        $this->request = $request;
        $this->translator = $translator;
        $this->sessionManager = $sessionManager;
    }
    
    public function __invoke() {
        $translatorConfig = $this->config['translator'];
        $pattern = $translatorConfig['translation_file_patterns'];
        if (!$this->languages){
            $languages = $this->generateLanguageProperty($pattern);
        }else{
            $languages = $this->languages;
        }
        if (!$this->selected){
            $this->checkSelectedLocale();
        }
        $layout = $this->getView()->getEngine()->layout();
        $layout->locale = $this->selected;
        return $this;
    }
    private function generateLanguageProperty($filedir){
        if ($this->languages){
            return $this->languages;
        }
        $languages = [];
        foreach($filedir as $pattern){
            if (strpos($pattern['pattern'], 'Laminas_')){
                continue;
            }
            $filename = scandir($pattern['base_dir']);
            foreach ($filename as $file) {
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
        return $this->languages;
    }
    
    public function getHtmlOutput(){
        $string = '<form method="post" id="selectLocale">'."\n";
        $string .= '                    <div style="color: white;">'."\n";
        $string .= '                        <label>'.$this->translator->translate('Language choice').'<br>'."\n";
        $string .= '                            <select name="language" id="language" onchange="setLanguage()">'."\n";
        $this->checkSelectedLocale();
        foreach ($this->languages as $value) {
            if ($this->selected === $value){
                $string .= '                                <option value="'.$value.'" selected>'.$value.'</option>'."\n";
            }else{
                 $string .= '                               <option value="'.$value.'">'.$value.'</option>'."\n";
            }
        }
        $string .= '                            </select>'."\n";
        $string .= '                        </label>'."\n";
        $string .= '                    </div>'."\n";
        $string .= '                </form>'."\n";
        $java = 'function setLanguage() {'."\n";
        $java .= '                          var form = document.getElementById("selectLocale");'."\n";
        $java .= '                          form.submit();'."\n";
        $java .= '                      };'."\n";
        $string .= '                <script>'."\n";
        $string .= '                    '.$java;
        $string .= '                </script>'."\n";
        return $string;
    }
    
    private function checkSelectedLocale(){
        if (isset($this->request->getPost()->language)){
            $this->selected = $this->request->getPost()->language;
            return;
        }
        if (isset($this->config['session_config'])){
            $sessionConfig = $this->config['session_config'];
            $sessionName = $sessionConfig['name'];
            if ($this->sessionManager->sessionExists() AND $this->sessionManager->isValid()){
                $sessContainer = new Container($sessionName,$this->sessionManager);
                if ($sessContainer->locale){
                    $this->selected = $sessContainer->locale;
                    return;
                }
            }
        }
        $cookies = $this->request->getCookie();
        if (isset($cookies->locale)){
            $this->selected = $cookies->locale;
            return;
        }
        $this->selected = substr(\Locale::getDefault(), 0, 2);
    }
    /**
     * Ein Array mit bekannten Sprachen (lowercased ISO 3166 ALPHA-2)
     * @param array $languages
     */
    public function setLanguages(array $languages=[]){
        $this->languages = $languages;
    }
    
    
    public function setActiveLanguage(string $language){
        $this->selected = $language;
    }
}
