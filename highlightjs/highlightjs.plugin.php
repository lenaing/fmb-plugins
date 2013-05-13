<?php
use FMB\Core\Core;
use FMB\Plugins\PluginEngine;
use FMB\Plugins\Plugin;

Core::loadFile('src/plugins/Plugin.class.php');

class highlightjs extends Plugin
{

    public $theme;

    public $path;

    private $registered = false;

    public function init()
    {
        global $fmbConf;

        if (isset($fmbConf['highlightjs']['theme'])) {
            $this->theme = $fmbConf['highlightjs']['theme'];
        } else {
            $this->theme = 'default';
        }

        $this->path = $this->getPluginUrl('highlightjs');

        $fmbPluginEngine = PluginEngine::getInstance();
        $fmbPluginEngine->setHook('extend', 'highlightjs', 'extend');
        return true;
    }

    function extend($params)
    {
        if ($this->registered)
            return;
        $this->registered = true;
        $tpl = $params[0];
        $tmp = <<<EOL
        <script type="text/javascript" src="{$this->path}highlight.js/highlight.pack.js"></script>
        <script type="text/javascript">
            hljs.initHighlightingOnLoad();
        </script>
        <link media="screen,projection,handheld" href="{$this->path}highlight.js/styles/{$this->theme}.css" type="text/css" rel="stylesheet" />
EOL;
        $tpl->smarty->append('extHTMLHeader', $tmp);
    }
}
?>
