<?php
use FMB\Core\Core;
use FMB\Plugins\PluginEngine;
use FMB\Plugins\Plugin;

Core::loadFile('src/plugins/Plugin.class.php');

class nl2br extends Plugin
{

    public function init()
    {
        $fmbPluginEngine = PluginEngine::getInstance();
        $fmbPluginEngine->setHook('format', 'nl2br', 'format');
        return true;
    }

    function format($params)
    {

        $mem = PluginEngine::getCachingPlugin();
        if (null != $mem) {
            $key = md5('nl2br'.print_r($params,true));
            $res = $mem->get($key);
            if (null == $res) {
                $res = $this->extnl2br($params[0]);
                $mem->set($key, $res);
            }
            return $res;
        }

        return $this->extnl2br($params[0]);
    }

    function extnl2br($text, $pattern = "\n\n")
    {
        // Some <pre> tag, start formatting consequently.
        $lines = explode($pattern, $text);
        $res = '';
        $pre = '';
        $isPreFound = false;

        foreach($lines as $line) {

            if(strpos($line, '<pre>') !== false) {
                $isPreFound = true;
            }
            
            if(strpos($line, '</pre>') !== false) {
                
                $isPreFound = false;
                $res .= $pre;
                $pre = "";
            }

            if ($isPreFound) {    
                $pre .= $line.$pattern;
            } else {
                $res .= $line.'<br />';
            }
        }

        if ($pattern === "\n") {
            return $res;
        }

        return $this->extnl2br($res, "\n");
    }
}
?>
