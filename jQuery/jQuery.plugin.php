<?php
use FMB\Core\Core;
use FMB\Plugins\PluginEngine;
use FMB\Plugins\Plugin;

Core::loadFile('src/plugins/Plugin.class.php');

class jQuery extends Plugin
{

    private $path;
    private $_registered = false;

    public function init()
    {
        $this->path = $this->getPluginUrl('jQuery');

        $fmbPluginEngine = PluginEngine::getInstance();
        $fmbPluginEngine->setHook('extend', 'jQuery', 'extend');
        return true;
    }

    function extend($params)
    {
        if ($this->_registered)
            return;
        $this->_registered = true;
        $tpl = $params[0];
        $tmp = <<<EOL
        <script type="text/javascript" src="{$this->path}res/jquery-2.0.0.min.js"></script>
EOL;
        $tpl->smarty->append('extHTMLHeader', $tmp);
    }
}
?>
