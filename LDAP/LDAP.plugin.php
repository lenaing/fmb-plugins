<?php
use FMB\Core\Core;
use FMB\Plugins\PluginEngine;
use FMB\Plugins\LoginPlugin;

Core::loadFile('src/plugins/LoginPlugin.class.php');

class LDAP extends LoginPlugin
{
    private $_host;
    private $_connect_string;

    public function init()
    {
        global $fmbConf;

        $this->_host = $fmbConf['ldap']['host'] ? $fmbConf['ldap']['host'] : 'localhost';
        $this->_connect_string = $fmbConf['ldap']['connect_string'] ? $fmbConf['ldap']['connect_string'] : null;

        if (null === $this->_connect_string) {
            return false;
        }
        
        $fmbPluginEngine = PluginEngine::getInstance();
        $fmbPluginEngine->setHook('login', 'LDAP', 'login');
        return true;
    }

    function login($params)
    {
        return $this->checkLogin($params[0], $params[1]);
    }

    public function checkLogin($user, $password) {
        if (!function_exists('ldap_connect')) {
            return false;
        }

        $string = str_replace('%u', $user, $this->_connect_string);
        $ldap = ldap_connect($this->_host);
        if (false === $ldap) {
            return false;
        }
        if (Core::isDebugging()) {
            Core::debug("Connected to LDAP");
        }

        ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
        $ret = ldap_bind($ldap, $string, $password);

        if (Core::isDebugging()) {
            Core::debug("bind: %d", array($ret));
        }

        ldap_unbind($ldap);

        return $ret;
    }
}
?>
