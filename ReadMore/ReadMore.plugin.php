<?php
use FMB\Core\Core;
use FMB\Plugins\PluginEngine;
use FMB\Plugins\Plugin;

Core::loadFile('src/plugins/Plugin.class.php');

class ReadMore extends Plugin
{

    public function init()
    {
        $fmbPluginEngine = PluginEngine::getInstance();
        $fmbPluginEngine->setHook('format', 'ReadMore', 'format');
        return true;
    }

    function format($params)
    {
        if ((!isset($_GET['page']) && !isset($_GET['id'])) || (isset($_GET['page']) && $_GET['page'] != "post")) {
            if (($p = strpos($params[0], '[more]')) !== false) {
                return substr($params[0], 0, $p);
            }
        }
        return str_replace('[more]', '', $params[0]);
    }
}
?>
