<?php
use FMB\Core\Core;
use FMB\Plugins\PluginEngine;
use FMB\Plugins\Plugin;

# Install PSR-0-compatible class autoloader
spl_autoload_register(function($class){
    require 'lib/'.preg_replace('{\\\\|_(?!.*\\\\)}', DIRECTORY_SEPARATOR, ltrim($class, '\\')).'.php';
});

use \Michelf\MarkdownExtra;

Core::loadFile('src/plugins/Plugin.class.php');

class Markdown extends Plugin
{

    public function init()
    {
        $fmbPluginEngine = PluginEngine::getInstance();
        $fmbPluginEngine->setHook('format', 'Markdown', 'format');
        return true;
    }

    function format($params)
    {
        $mem = PluginEngine::getCachingPlugin();
        if (null != $mem) {
            $key = md5('Markdown'.print_r($params,true));
            $res = $mem->get($key);
            if (null == $res) {
                $res = MarkdownExtra::defaultTransform($params[0]);
                $mem->set($key, $res);
            }   
            return $res;
        }
        return MarkdownExtra::defaultTransform($params[0]);
    }
}
?>
