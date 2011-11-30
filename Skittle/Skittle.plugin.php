<?php
use FMB\Core\Core;
use FMB\Plugins\PluginEngine;
use FMB\Plugins\TemplatePlugin;

Core::loadFile('src/plugins/TemplatePlugin.class.php');

class Skittle extends TemplatePlugin
{
    public $smarty;

    public function init()
    {
        global $fmbConf;

        if (false === Core::loadFile('plugins/Skittle/Smarty-3.0.7/libs/Smarty.class.php')) {
            return false;
        }
        
        $this->smarty = new Smarty();
        $this->smarty->template_dir = $fmbConf['skittle']['templates_dir'];
        $this->smarty->compile_dir = $fmbConf['skittle']['compile_dir'];
        $this->smarty->cache_dir = $fmbConf['skittle']['cache_dir'];
        return true;
    }

    public function assign($varname, $var=null) {
        return $this->smarty->assign($varname, $var);
    }

    public function display($template, $cache_id = null, $compile_id = null)
    {
        return $this->fetch($template, $cache_id, $compile_id, true);
    }

    public function fetch($template, $cache_id = null, $compile_id = null, $display = false)
    {
        return $this->smarty->fetch($template, $cache_id, $compile_id, null, $display);
    }
}
?>
