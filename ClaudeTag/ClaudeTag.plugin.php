<?php
use FMB\Core\Core;
use FMB\Plugins\PluginEngine;
use FMB\Plugins\Plugin;
use FMB\Plugins\DBPlugin;

Core::loadFile('src/plugins/Plugin.class.php');

class ClaudeTag extends Plugin
{

    public $nbTags;

    public $sizeMin;

    public $sizeMax;

    private $_uri;

    private $_url;

    private $_registered = false;

    public function init()
    {
        global $fmbConf;

        if (isset($fmbConf['ClaudeTag']['nbtags'])) {
            $this->nbTags = $fmbConf['ClaudeTag']['nbtags'];
        } else {
            $this->nbTags = 30;
        }
        if (isset($fmbConf['ClaudeTag']['sizemax'])) {
            $this->sizeMax = $fmbConf['ClaudeTag']['sizemax'];
        } else {
            $this->sizeMax = 20;
        }
        if (isset($fmbConf['ClaudeTag']['sizemin'])) {
            $this->sizeMin = $fmbConf['ClaudeTag']['sizemin'];
        } else {
            $this->sizeMin = 10;
        }

        $this->_uri = $this->getPluginUri('ClaudeTag');
        $this->_url = $this->getPluginUrl('ClaudeTag');

        $fmbPluginEngine = PluginEngine::getInstance();
        $fmbPluginEngine->setHook('extend', 'ClaudeTag', 'extend');
        return true;
    }

    function extend($params)
    {
        if ($this->_registered)
            return;
        $this->_registered = true;
        $tpl = $params[0];
        $db = $params[1];
        $tpl->assign('ext_tags_with_size', $this->getTagsList($db));
        $tmp = $tpl->fetch($this->_uri.'ClaudeTag.tpl');
        $tpl->smarty->append('extMenu', $tmp);
        $tmp = <<<EOL
        <link rel="stylesheet" type="text/css" href="{$this->_url}ClaudeTag.css" />
EOL;
        $tpl->smarty->append('extHTMLHeader', $tmp);
    }

    private function getTagsList($db) {
        $query = "SELECT COUNT(t.tag_id) AS cpt,t.tag_title AS title,t.tag_id AS id,0 AS size ".
                 "FROM fmb_blog_tags_rel AS r,fmb_blog_tags AS t ".
                 "WHERE t.tag_id = r.tag_id ".
                 "GROUP BY t.tag_title,t.tag_id ".
                 "LIMIT ".$this->nbTags;

        $tags = 
            $db->query(
                $query,
                array(),
                DBPlugin::SQL_QUERY_ALL
            )   
            ? $db->getSQLResult()
            : array();

        $query2 = "SELECT max(cpt) AS max,min(cpt) AS min ".
                  "FROM (".
                      "SELECT count(t.tag_id) AS cpt ".
                      "FROM fmb_blog_tags_rel AS r,fmb_blog_tags AS t ".
                      "WHERE t.tag_id = r.tag_id ".
                      "GROUP BY t.tag_title,t.tag_id".
                  ") AS range";

        $range =
            $db->query(
                $query2,
                array(),
                DBPlugin::SQL_QUERY_ALL
            )   
            ? $db->getSQLResult()
            :array();

        $max = $range[0]["max"];
        $min = $range[0]["min"];
        $diff = ($max - $min);
        $med = $diff / 2;
        $nb = count($tags);

        $max_size = $this->sizeMax;
        $min_size = $this->sizeMin;

        for ($i=0; $i < $nb; $i++) {
            if ($tags[$i]["cpt"] == $max) {
                $tags[$i]["size"] = $max_size;
            } else if ($tags[$i]["cpt"] == $min) {
                $tags[$i]["size"] = $min_size;
/*
            } else if ($tags[$i]["cpt"] < $med) {
                $tags[$i]["size"] = $min_size + ($med * (1 + $tags[$i]["cpt"] / 100));
            } else {
                $tags[$i]["size"] = $min_size + ($max_size - $min_size) / $med + ($med * (1 + $tags[$i]["cpt"] / 100));
            }
*/
            } else {
                $tags[$i]["size"] = $min_size + ($tags[$i]["cpt"] / $nb * ($max_size - $min_size));
            }
        }

        return $tags;
    }
}
?>
