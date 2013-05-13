<?php
use FMB\Core\Core;
use FMB\Plugins\PluginEngine;
use FMB\Plugins\DBPlugin;

Core::loadFile('src/plugins/DBPlugin.class.php');

class MonsterDB extends DBPlugin
{

    public function init()
    {
        global $fmbConf;
        
        // TODO : Check PEAR & MDB2
        if (!include_once('MDB2.php')) {
            $mdb2Path = $fmbConf['monsterdb']['mdb2_path'];
            if (false === Core::loadFile('plugins/MonsterDB/'.$mdb2Path)) {
                return false;
            }
        }

        $dbproto = $fmbConf['monsterdb']['protocol'];
        $dbuser  = $fmbConf['monsterdb']['user'];
        $dbpass  = $fmbConf['monsterdb']['password'];
        $dbhost  = $fmbConf['monsterdb']['host'];
        $dbname  = $fmbConf['monsterdb']['db_name'];
        $dsn = $dbproto.'://'.$dbuser.':'.$dbpass.'@'.$dbhost.'/'.$dbname;
        $this->_db = MDB2::factory($dsn);

        if (false === $this->_db) {
            return false;
        }

        $this->_db->setFetchMode(MDB2_FETCHMODE_ASSOC);
        return true;
    }

    // DBPlugin interface methods.----------------------------------------------
    public function query($query, $values, $type)
    {
        $mem = PluginEngine::getCachingPlugin();
        $cache = false;
        $key = "";
        if (null != $mem) {
            if (0 == strncasecmp($query, "SELECT", 6)) {
                $cache = true;
                $key = md5($query.implode(" ",$values));
                $tmp = $mem->get($key);
                if (null != $tmp) {
                    $this->_result = $tmp;
                    return true;
                }
            } else {
                $mem->flushdb();
            }
        }
        MonsterDB::$sqlQueries++;
        switch ($type)
        {
            // Get all records
            case DBPlugin::SQL_QUERY_ALL :
            {
                if (sizeof($values) > 0) {
                    $st = $this->_db->prepare($query, null, MDB2_PREPARE_RESULT);

                    if (MDB2::isError($st)) {
                        $this->_error = $st->getMessage();
                        return false;
                    } else {
                        $this->_result = $st->execute($values)->fetchAll();
                        $st->free();
                    }
                } else {
                    $this->_result = $this->_db->queryAll($query);
                }
            } break;

            // Get only the first record
            case DBPlugin::SQL_QUERY_FIRST :
            {
                if (sizeof($values) > 0) {
                    $st = $this->_db->prepare($query, null, MDB2_PREPARE_RESULT);

                    if (MDB2::isError($st)) {
                        $this->_error = $st->getMessage();
                        return false;
                    } else {
                        $this->_result = $st->execute($values)->fetchRow();
                        $st->free();
                    }
                } else {
                    $this->_result = $this->_db->queryRow($query);
                }
            } break;

            // Get no record
            case DBPlugin::SQL_QUERY_MANIP :
            default :
            {
                if (sizeof($values) > 0) {
                    $st = $this->_db->prepare($query, null, MDB2_PREPARE_MANIP);

                    if (MDB2::isError($st)) {
                        $this->_error = $st->getMessage();
                        return false;
                    } else {
                        $st->execute($values);
                        $st->free();
                    }
                } else {
                    $this->_db->query($query);
                }
            } break;
        }

        if (MDB2::isError($this->_result)) {
            $this->_error = $this->_result->getMessage();
            return false;
        }

        if ($cache) {
            $mem->set($key, $this->_result, "db", 300);
        }

        return true;
    }

    public function getSQLSearchString($queryableCols, $searchString)
    {
        // FIXME if we aren't using postgres, disable search.
        $plaintext = true;
        $documentsList = preg_replace('/\s*,\s*/', '||', $queryableCols);
        
        if (strpos($searchString, '|') !== false) {
            $searchString = ereg_replace('\|\|','|', $searchString);
            $plaintext = false;
        }
        if (strpos($searchString, '&') !== false) {
            $searchString = ereg_replace('\&\&','&', $searchString);
            $plaintext = false;
        }

        $searchQuery = ($plaintext) ? 'plainto_tsquery' : 'to_tsquery';
        $searchQuery .= '(\''.$searchString.'\')';

         return 'AND to_tsvector('.$documentsList.') ' .
                '@@ '.$searchQuery.' ';
    }

    public function getSQLIntervalString($startEpoch, $endEpoch)
    {
        /* Todo : specific to Postgresql */
        $periodString = 'BETWEEN to_timestamp('.$startEpoch.')';
        $periodString .= ' AND to_timestamp('.$endEpoch.')';
        return $periodString;
    }

    public function getSQLExtractString($what, $column)
    {
        return 'EXTRACT('.$what.' FROM '.$column.')';
    }

    public function getBooleanValueFromSQL($SQLBoolean)
    {
        return ('t' == $SQLBoolean);
    }

    /**
     * Retrieve last SQL query result.
     * @return Last SQL query result or <b>NULL</b> if error.
     */
    public function getSQLResult()
    {
        if (MDB2::isError($this->_result)) {
            return NULL;
        }
        return $this->_result;
    }

    public function getSQLError(){
        ;
    }

    /**
     * Retrieve overall SQL queries count.
     * @return Total SQL queries count.
     */
    public function getSQLQueriesCount()
    {
        return MonsterDB::$sqlQueries;
    }

    public function destroy()
    {
        if(MDB2::isConnection($this->_db)) {
            $this->_db->disconnect();
        }
    }

}
?>
