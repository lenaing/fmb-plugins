<?php
use FMB\Core\Core;
use FMB\Plugins\PluginEngine;
use FMB\Plugins\CachingPlugin;

Core::loadFile('src/plugins/CachingPlugin.class.php');

class APCache extends CachingPlugin
{

    private $_dbkeys;

    private $_cachefile;

    public function init()
    {
        $this->_cachefile = $this->getPluginUri('APCache').'dbkeys.cache.php';
        return true;
    }

    /**
     * Returns the cached value.
     * @param string $key Key of the cached value we need.
     * @return The cached value or NULL.
     */
    public function get($key) {
        $tmp = apc_fetch($key);
        if (false === $tmp) {
            return null;
        }
        return $tmp;
    }

    /**
     * Replace a value in the cache.
     * @param string $key Key of the value.
     * @param string $data Data to store.
     * @return bool True if the value has been replaced
     */
    public function replace($key,$data, $expire = 0) {
        if (apc_exists($key)) {
            return false;
        }
        apc_delete($key);
        apc_add($key, $data, $expire);
        return true;
    }

    /**
     * Insert data in cache.
     * @param string $key The key that will be associated with the item.
     * @param $data The variable to store. Strings and integers are stored as is, other types are stored serialized.
     * @param int $expire Expiration time of the item.
     */
    public function set($key,$data,$from = null, $expire = 0) {
        if (!$this->replace($key, $data, $expire)) {
            if (!apc_add($key, $data, $expire)) {
                return;
            }
        }
        if ($from === "db") {
            $this->loadKeys();
            $this->_dbkeys[] = $key;
            $this->storeKeys();
        }
    }

    public function delete($key) {
        return apc_delete($key);
    }

    public function flush() {
        return null;
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
        $this->_dbkeys = $GLOBALS['dbkeys'];
    }

    private function storeKeys() {
        file_put_contents($this->_cachefile, "<?php\n\$GLOBALS['dbkeys']=".var_export($this->_dbkeys,true).";\n?>");
    }
}
?>
