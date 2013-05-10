<?php
use FMB\Core\Core;
use FMB\Plugins\PluginEngine;
use FMB\Plugins\Plugin;

Core::loadFile('src/plugins/Plugin.class.php');

class NiceURL extends Plugin
{

    private $registered = false;

    public function init()
    {
        global $fmbConf;

        $fmbPluginEngine = PluginEngine::getInstance();
        $fmbPluginEngine->setHook('extend', 'NiceURL', 'extend');
        return true;
    }

    function extend($params)
    {
        if ($this->registered)
            return;
        $this->registered = true;
        $tpl = $params[0];
        $tpl->smarty->registerPlugin('modifier','niceurl', 'NiceURL::escapeurl');
        $tpl->assign('extNiceURL', true);
    }

    /** 
     * Escape the given string to add to URL.
     * @param title Title to escape.
     * @param size Maximum size (optional, default 150).
     * @return Escaped string.
     */
    public static function escapeurl($title, $size = 150) {
        $result = strtolower ($title);
        /*  
        foreach (STOP_WORDS as $word) {
            $result = preg_replace ("/\b".$word."\b/g", "", $result);
        }
        /*$result = str_replace (STOP_WORDS, "", $result);*/
        $search = explode(",","ç,æ,œ,á,é,í,ó,ú,à,è,ì,ò,ù,ä,ë,ï,ö,ü,ÿ,â,ê,î,ô,û,å,e,i,ø,u");
        $replace = explode(",","c,ae,oe,a,e,i,o,u,a,e,i,o,u,a,e,i,o,u,y,a,e,i,o,u,a,e,i,o,u");
        $result = str_replace($search, $replace, $result);
        $chars = array (' ', '.', ',', ';', '/', '\\', '!', '-', '?', '<', '>', '(', ')', '{', '}', '[', ']', '€', '$', '£', '%', '+', '°');
        $result = str_replace ('\'', '', $result);
        $result = str_replace ($chars, "_", $result);
        $pattern = array ('/_+/', '/_$/');
        $replacement = array ('_', '');
        $result = preg_replace ($pattern , $replacement, $result);
        return substr ($result, 0, $size);
    }   
}
?>
