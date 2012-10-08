<?php
use FMB\Core\Core;
use FMB\Plugins\PluginEngine;
use FMB\Plugins\Plugin;

Core::loadFile('src/plugins/Plugin.class.php');

/**
 *  Basic Securimage CAPTCHA plugin
 *  Needs php5-gd
 */
class FMB_Securimage extends Plugin
{
    private $securimage;

    public function init()
    {
        global $fmbConf;

        if (false === Core::loadFile('plugins/FMB_Securimage/securimage/securimage.php')) {
            Core::debug("Failed to find Securimage library.");
            return false;
        }

        $this->securimage = new Securimage();
        $fmbPluginEngine = PluginEngine::getInstance();
        $fmbPluginEngine->setHook('getCaptchaLabel', 'FMB_Securimage', 'getCaptchaLabel');
        $fmbPluginEngine->setHook('getCaptchaInput', 'FMB_Securimage', 'getCaptchaInput');
        $fmbPluginEngine->setHook('checkCaptcha', 'FMB_Securimage', 'checkCaptcha');
        return true;
    }

    public function getCaptchaInput() {
        global $fmbConf;
        return "<img id='captcha' src='".$fmbConf['url']."plugins/FMB_Securimage/securimage/securimage_show.php' alt='Captcha Image'/><br/>Please enter your answer : <input type='text' name='captcha_code' size='10' maxlength='6' />";
    }

    public function getCaptchaLabel() {
        return "Captcha :<br/>";
    }

    public function checkCaptcha() {
        return ($this->securimage->check($_POST['captcha_code']));
    }

}
?>
