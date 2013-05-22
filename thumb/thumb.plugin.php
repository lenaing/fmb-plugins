<?php
/*
Plugin Name: Thumbnails
Info: This plugin is adapted from flatpress
Description: Thumbnail plugin. Part of the standard distribution ;) If this is loaded scale parameter of images will create a scaled version of your img
Author: NoWhereMan real_nowhereman at user dot sf dot net
Version: 1.0
Author URI: http://www.nowhereland.it/
*/ 

use FMB\Core\Core;
use FMB\Plugins\PluginEngine;
use FMB\Plugins\Plugin;

Core::loadFile('src/plugins/Plugin.class.php');

class thumb extends Plugin
{

    private $thumbs;
    private $mode;

    public function init()
    {   
        global $fmbConf;

        if (!function_exists('imagegd2')) {
            return false;
        }

        $this->thumbs = isset($fmbConf['thumb']['path']) ? $fmbConf['thumb']['path'] : $this->getPluginUri('thumb').'thumbs';
        $this->mode = isset($fmbConf['thumb']['mode']) ? $fmbConf['thumb']['mode'] : 0640;

        $fmbPluginEngine = PluginEngine::getInstance();
        return true;
    }   

    /**
     *
     * plugin_thumb_create
     *
     * creates a thumbnail and caches the thumbnail in IMAGES_DIR/.thumb
     *
     * @param string $fpath string with filepath
     * @param array $infos infos from getimagesize($fpath) function
     * @param int $new_width
     * @param int $new_height
     *
     * @return array array(string $thumbpath, int $thumbwidth, int $thumbheight)
     *
     */

    private function plugin_thumb_create($fpath, $infos, $new_width, $new_height) {

        if (!file_exists($fpath)) {
            return array();
        }

        if (!($new_width && $new_height)) {
//            trigger_error("Size can't be 0 but got width=$new_width height=$new_height\n", E_USER_WARNING);
            return;
        }
        
        $thumbname = basename($fpath);
        $thumbdir = $this->thumbs;
        $thumbpath = $thumbdir .'/'. $thumbname;

        
        if (file_exists($thumbpath)) {
            $oldthumbinfo = getimagesize($thumbpath);
            if ($new_width==$oldthumbinfo[0]) {
                // already scaled
                return array($thumbpath, $new_width, $new_height);
            }
        }

        @mkdir($thumbdir, 0770, true);


        // we support only jpeg's, png's and gif's
        
        switch($infos[2]) {
            case 1: $image = imagecreatefromgif($fpath); break;
            case 2: $image = imagecreatefromjpeg ($fpath); break;
            case 3: $image = imagecreatefrompng($fpath);
        }
        
        
        //$image = imagecreatefromgd2 ($fpath);
        
        // create empty scaled and copy(resized) the picture
        
        
        $scaled = imagecreatetruecolor($new_width, $new_height);
        /*
         * If gif or png preserve the alpha channel
         *
         * Added by Piero VDFN
         * Kudos to http://www.php.net/manual/en/function.imagecopyresampled.php#104028
         */
        if($infos[2]==1 || $infos[2]==3) {
            imagecolortransparent($scaled, imagecolorallocatealpha($scaled, 0, 0, 0, 127));
            imagealphablending($scaled, false);
            imagesavealpha($scaled, true);
            $output=$infos[2]==3 ? 'png' : 'gif';
        } else {
            $output='jpg';
        }

        imagecopyresampled($scaled, $image, 0, 0, 0, 0, $new_width, $new_height, $infos[0], $infos[1]);

        if($output=='png') {
            $res = imagepng($scaled, $thumbpath);
        } elseif($output=='gif') {
            $res = imagegif($scaled, $thumbpath);
        } else {
            $res = imagejpeg($scaled, $thumbpath);
        }

        @chmod($thumbpath, $this->mode);
        return array($thumbpath, $new_width, $new_height);
        
    }

    public function do_thumb($actualpath, $props, $newsize){
        list($width, $height) = $newsize;
        if ($thumb = $this->plugin_thumb_create($actualpath, $props, $width, $height))
            $thumb = $thumb[0];
        return $thumb;
    }
}
?>
