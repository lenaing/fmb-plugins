<?php
use FMB\Core\Core;
use FMB\Plugins\PluginEngine;
use FMB\Plugins\Plugin;

Core::loadFile('src/plugins/Plugin.class.php');

class TeX extends Plugin
{
    private $_uri;

    private $_url;

    public $type;
    public $size;

    public function init()
    {
        global $fmbConf;

        $this->type = isset($fmbConf['TeX']['type']) ? $fmbConf['TeX']['type'] : 'png';
        $this->size = isset($fmbConf['TeX']['size']) ? $fmbConf['TeX']['size'] : 16;

        $this->_uri = $this->getPluginUri('TeX');
        $this->_url = $this->getPluginUrl('TeX');

        $fmbPluginEngine = PluginEngine::getInstance();
        $fmbPluginEngine->setHook('format', 'TeX', 'format');
        return true;
    }

    function format($params)
    {
        $mem = PluginEngine::getCachingPlugin();
        if (null != $mem) {
            $key = md5('TeX'.print_r($params,true));
            $res = $mem->get($key);
            if (null == $res) {
                $res = $this->process($params[0]);
                $mem->set($key, $res);
            }   
            return $res;
        }

        return $this->process($params[0]);
    }

    private function process($text) {
        include_once($this->_uri.'lib/class.latexrender.php');

        preg_match_all("#\[tex\](.*?)\[/tex\]#si", $text, $tex_matches);

        $latex = new LatexRender($this->_uri."/pictures", $this->_url."/pictures", $this->_uri."/tmp");

        $latex->_cachefiles = 1;
        $latex->_image_format = $this->type;
        $latex->_font_size = $this->size;

        $popupwindow = 0;

        for ($i=0; $i < count($tex_matches[0]); $i++) {
            $pos = strpos($text, $tex_matches[0][$i]);
            $latex_formula = $tex_matches[1][$i];

            $url = $latex->getFormulaURL($latex_formula);
            $alt_latex_formula = htmlentities($latex_formula, ENT_QUOTES);
            $alt_latex_formula = str_replace("\r","&#13;",$alt_latex_formula);
            $alt_latex_formula = str_replace("\n","&#10;",$alt_latex_formula);

            if ($popupwindow==1) {
                $ahref = "<a href=\"javascript:void(0)\">";
                $slasha = "</a>";
                $ht=100;
                if (strlen($latex_formula)>100) ($ht=250);
                $texpopup = "onclick=\"newWindow=window.open('$latexrender_path_http/latexcode.php?code=".urlencode($latex_formula)."',";
                $texpopup .= "'latexCode','toolbar=no,location=no,scrollbars=yes,";
                $texpopup .= "resizable=yes,status=no,width=375,height=".$ht.",left=200,top=100');\"";
            } else {
                $texpopup="";
                $ahref = "";
                $slasha = "";
            }

            if ($url != false) {
                $text = substr_replace($text, $ahref."<img src='".$url."' title='".$alt_latex_formula."' alt='".$alt_latex_formula."' border=0 align=absmiddle ".$texpopup.">".$slasha,$pos,strlen($tex_matches[0][$i]));
            }
        }

        return $text;
    }
}
?>
