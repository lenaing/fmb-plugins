<?php
use FMB\Core\Core;
use FMB\Plugins\PluginEngine;
use FMB\Plugins\Plugin;

Core::loadFile('src/plugins/Plugin.class.php');

class ReadMore extends Plugin
{

    private $imgURL;
    private $playerURL;

    public function init()
    {
        global $fmbConf;
        
        $fmbPluginEngine = PluginEngine::getInstance();
        $fmbPluginEngine->setHook('format', 'ReadMore', 'readmore');
        return true;
    }

    function readmore($params)
    {
        if (!isset($_GET['page']) && !isset($_GET['id'])) {
            if (($p = strpos($params[0], '[more]')) !== false) {
                return substr($params[0], 0, $p);
            }
        }
        return str_replace('[more]', '', $params[0]);
    }
}
?>
