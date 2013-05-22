<?php
use FMB\Core\Core;
use FMB\Plugins\PluginEngine;
use FMB\Plugins\Plugin;

Core::loadFile('src/plugins/Plugin.class.php');

class SlimFast extends Plugin
{

    private $path;
    private $_registered = false;

    public function init()
    {
        global $fmbConf;

        $this->path = $this->getPluginUrl('SlimFast');

        $fmbPluginEngine = PluginEngine::getInstance();
        $fmbPluginEngine->setHook('extend', 'SlimFast', 'extend');
        return true;
    }

    function extend($params)
    {
        if ($this->_registered)
            return;
        $this->_registered = true;
        $tpl = $params[0];
        $tmp = <<<EOL
        <script type="text/javascript" src="{$this->path}res/lytebox.js"></script>
        <link rel="stylesheet" href="{$this->path}res/lytebox.css" type="text/css" media="screen" />
EOL;
        $tpl->smarty->append('extHTMLHeader', $tmp);
    }

    public static function popup($rel, $alt, $title) {
        $mtitle = '';
        $desc = '';
        $ret = ' class="lytebox" data-lyte-options="autoResize:true';
        if (isset($rel)) {
            $group = ' group:'.$rel;
        } else {
            $group = ' group:default';
        }
        if (isset($title)) {
            $mtitle = ' data-title="'.$title.'"';
        }
        if (isset($alt)) {
            $mdesc = ' data-description="'.$alt.'"';
        }
        return $ret.$group.'"'.$mtitle.$mdesc;
    }
}
?>
