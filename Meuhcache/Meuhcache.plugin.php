<?php
use FMB\Core\Core;
use FMB\Plugins\PluginEngine;
use FMB\Plugins\CachingPlugin;

Core::loadFile('src/plugins/CachingPlugin.class.php');

class Meuhcache extends CachingPlugin
{

    public $mem;

    private $_dbkeys;

    private $_cachefile;

    public function init()
    {
        global $fmbConf;
        
        $host = $fmbConf['meuhcache']['server'];
        $port  = $fmbConf['meuhcache']['port'];

        if (!isset($host)) {
            $host = "localhost";
        }
        if (!isset($port)) {
            $port = 11211;
        }

        $this->mem = new Memcache();

        $this->_cachefile = $this->getPluginUri('Meuhcache').'dbkeys.cache.php';
        
        return $this->mem->connect($host, $port);
    }

    /**
     * Returns the cached value.
     * @param string $key Key of the cached value we need.
     * @return The cached value or NULL.
     */
    public function get($key) {
        $tmp = $this->mem->get($key);
        if (false === $tmp) {
            return null;
        }
        return $tmp;
    }

    /**
     * Insert data in cache.
     * @param string $key The key that will be associated with the item.
     * @param $data The variable to store. Strings and integers are stored as is, other types are stored serialized.
     * @param int $expire Expiration time of the item.
     */
    public function set($key,$data,$from = null, $expire = 0) {
        if (!$this->mem->replace($key, $data, 0, $expire)) {
            if (!$this->mem->set($key, $data, 0, $expire)) {
                return;
            }
        }
        if ($from === "db") {
            $this->loadKeys();
            $this->_dbkeys[] = $key;
            $this->storeKeys();
        }
    }

    public function flush() {
        return $this->mem->flush();
    }

    public function flushdb() {
        $this->loadKeys();
        foreach ($this->_dbkeys as $key => $value) {
            $this->mem->delete($value);
        }
        $this->_dbkeys = array();
        $this->storeKeys();
    }

    private function loadKeys() {
        if (!is_file($this->_cachefile))
        {   
            file_put_contents($this->_cachefile,"<?php\n\$GLOBALS['dbkeys']=array();\n?>");
            chmod($this->_cachefile,0705);
        }   
        require $this->_cachefile;
        $this->_dbkeys=$GLOBALS['dbkeys'];
    }

    private function storeKeys() {
        file_put_contents($this->_cachefile, "<?php\n\$GLOBALS['dbkeys']=".var_export($this->_dbkeys,true).";\n?>");
    }
}
?>
