<?php
use FMB\Core\Core;
use FMB\Plugins\PluginEngine;
use FMB\Plugins\Plugin;

Core::loadFile('src/plugins/Plugin.class.php');

/**
 * 
 *  Needs php5-gd
 */

class Recaptelechat extends Plugin
{
    private $publicKey;
    private $privateKey;

    public function init()
    {
        global $fmbConf;
        $error = false;

        if (empty($fmbConf['recaptelechat'])) {
            Core::debug("You should configure Recaptelechat!");
            $error = true;
        } else {
            if (empty($fmbConf['recaptelechat']['libPath'])) {
                Core::debug("Recaptelechat miss the Recaptcha library path!");
                $error = true;
            }
            if (empty($fmbConf['recaptelechat']['publicKey'])) {
                Core::debug("Recaptelechat miss your public key!");
                $error = true;
            }
            if (empty($fmbConf['recaptelechat']['privateKey'])) {
                Core::debug("Recaptelechat miss your private key!");
                $error = true;
            }
        }

        if ($error) {
            return false;
        }

        $this->publicKey = $fmbConf['recaptelechat']['publicKey'];
        $this->privateKey = $fmbConf['recaptelechat']['privateKey'];
        $path = $fmbConf['recaptelechat']['libPath'];

        if (false === Core::loadFile('plugins/Recaptelechat/'.$path.'recaptchalib.php')) {
            return false;
        }

        // Setup hooks
        $fmbPluginEngine = PluginEngine::getInstance();
        $fmbPluginEngine->setHook('getCaptchaLabel', 'Recaptelechat', 'getCaptchaLabel');
        $fmbPluginEngine->setHook('getCaptchaInput', 'Recaptelechat', 'getCaptchaInput');
        $fmbPluginEngine->setHook('checkCaptcha', 'Recaptelechat', 'checkCaptcha');
        return true;
    }

    public function getCaptchaInput() {
        return recaptcha_get_html($this->publicKey);
    }

    public function getCaptchaLabel() {
        return "Captcha :<br/>";
    }

    public function checkCaptcha() {
        $resp = recaptcha_check_answer($this->privateKey,
                                $_SERVER["REMOTE_ADDR"],
                                $_POST["recaptcha_challenge_field"],
                                $_POST["recaptcha_response_field"]);
        return ($resp->is_valid);
    }


}
?>
