<?php
/*
Plugin Name: Daddy
Version: 1.5
Description: Plugin based on the flatpress bbcode plugin
Author: Hydra, NoWhereMan
Author URI: http://flatpress.sf.net
*/

use FMB\Core\Core;
use FMB\Plugins\PluginEngine;
use FMB\Plugins\Plugin;

Core::loadFile('src/plugins/Plugin.class.php');

class Daddy extends Plugin
{
    private $bbcode;

    public function init()
    {   
        global $fmbConf;

        if (false === Core::loadFile('plugins/Daddy/inc/stringparser_bbcode.class.php')) {
            return false;
        }
        $fmbPluginEngine = PluginEngine::getInstance();
        $fmbPluginEngine->setHook('format', 'Daddy', 'format');
        $this->daddy_setup();
        return true;
    }

    private function daddy_setup() { 
        $this->bbcode = new StringParser_BBCode();
        // If you set it to false the case-sensitive will be ignored for all codes
        $this->bbcode->setGlobalCaseSensitive(false);
        $this->bbcode->setMixedAttributeTypes(true);
        $BBCODE_TAGS_SIMPLE = array(
            'b' => 'strong',
            'i' => 'em',
            'quote' => 'blockquote',
            'blockquote',
            'strong', 
            'em', 
            'ins',
            'del',
            'hr','h2','h3','h4','h5','h6'
            // u for underlined: see below
        );
        foreach ($BBCODE_TAGS_SIMPLE as $key => $val) {
            if (!is_numeric($key)) {
                $bbtag = $key;
                $htmltag = $val;
            } else {
                $htmltag = $bbtag = $val;
            }
            $this->bbcode->addCode (
                $bbtag,
                'simple_replace', 
                null, 
                array(
                    'start_tag' => "<$htmltag>",
                    'end_tag' => "</$htmltag>"
                ),
                'inline',
                array(
                    'listitem', 'block', 'inline', 'link'
                ),
                array()
            );
            $this->bbcode->setCodeFlag($bbtag, 'closetag', BBCODE_CLOSETAG_MUSTEXIST);
        }
        
        /* other tags */
        $this->bbcode->addCode(
            'u', 
            'simple_replace', 
            null, 
            array(
                'start_tag' => '<span style="text-decoration: underline">', 
                'end_tag' => '</span>'
            ),
            'inline',
            array(
                'listitem', 'block', 'inline', 'link'),
            array()
        );
        $this->bbcode->addCode(
            'color',
            'callback_replace',
            'Daddy::do_daddy_color',
            array(
                'usecontent_param' => array('default')
            ),
            'inline', 
            array(
                'listitem', 'block', 'inline', 'link'
            ), 
            array()
        );
        $this->bbcode->setCodeFlag('color', 'closetag', BBCODE_CLOSETAG_MUSTEXIST);
        $this->bbcode->addCode(
            'size',
            'callback_replace',
            'Daddy::do_daddy_size',
            array(
                'usecontent_param' => array('default')
            ),
            'inline', 
            array(
                'listitem', 'block', 'inline', 'link'
            ),
            array()
        );
        $this->bbcode->setCodeFlag('color', 'closetag', BBCODE_CLOSETAG_MUSTEXIST);
        $this->bbcode->addCode(
            'code', 
            'usecontent', 
            'Daddy::do_daddy_code', 
            array(),
            'inline', 
            array(
                'listitem', 'block', 'inline', 'link'
            ),
            array()
        );
        $this->bbcode->setCodeFlag('code', 'closetag', BBCODE_CLOSETAG_MUSTEXIST);
        $this->bbcode->addCode(
            'html', 
            'usecontent', 
            'Daddy::do_daddy_html', 
            array(),
            'inline', 
            array(
                'listitem', 'block', 'inline', 'link'
            ),
            array()
        );
        $this->bbcode->setCodeFlag('html', 'closetag', BBCODE_CLOSETAG_MUSTEXIST);
        $this->bbcode->addCode(
            'url', 
            'callback_replace', 
            'Daddy::do_daddy_url', 
            array(
                'usecontent_param' => array('default', 'new')
            ),
            'link',
            array(
                'listitem', 'block', 'inline'
            ),
            array('link')
        );
        $this->bbcode->addCode(
            'img', 
            'callback_replace', 
            'Daddy::do_daddy_img', 
            array(
                'usecontent_param' => array(
                    'default', 'float', 'alt', 'popup', 'width', 'height', 'title', 'rel', 'cache'
                )
            ),
            'image',
            array(
                'listitem', 'block', 'inline', 'link'
            ),
            array());
        $this->bbcode->setCodeFlag('img', 'closetag', BBCODE_CLOSETAG_OPTIONAL);
        $this->bbcode->addCode(
            'video', 
            'callback_replace_single', 
            'Daddy::do_daddy_video', 
            array(
                'usecontent_param' => array(
                    'default', 'float', 'width', 'height'
                )
            ),
            'image',
            array(
                'listitem', 'block', 'inline', 'link'
            ),
            array()
        );
        $this->bbcode->setCodeFlag('video', 'closetag', 'BBCODE_CLOSETAG_FORBIDDEN');
        $this->bbcode->addCode (
            'list',
            'callback_replace',
            'Daddy::do_daddy_list',
            array(
                'start_tag' => '<ul>',
                'end_tag' => '</ul>'
            ),
            'list',
            array(
                'block', 'listitem'
            ),
            array()
        );
        $this->bbcode->setCodeFlag('list', 'closetag', BBCODE_CLOSETAG_MUSTEXIST);
        $this->bbcode->setCodeFlag('list', 'paragraph_type', BBCODE_PARAGRAPH_BLOCK_ELEMENT);
        $this->bbcode->setCodeFlag('list', 'opentag.before.newline', BBCODE_NEWLINE_DROP);
        $this->bbcode->setCodeFlag('list', 'closetag.before.newline', BBCODE_NEWLINE_DROP);
        $this->bbcode->addCode(
            '*',
            'simple_replace',
            null,
            array(
                'start_tag' => '<li>',
                'end_tag' => '</li>'
            ),
            'listitem',
            array('list'),
            array()
        );
        $this->bbcode->setCodeFlag('*', 'closetag', BBCODE_CLOSETAG_OPTIONAL);
        $this->bbcode->setCodeFlag('*', 'paragraphs', false);
        $this->bbcode->addCode(
            'align',
            'callback_replace',
            'Daddy::do_daddy_align',
            array(
                'usecontent_param' => array('default')
            ),
            'block',
            array(
                'listitem', 'block', 'inline', 'link'
            ),
            array()
        );
    }

    /**
     * Adds a Toolbar to admin panels write entry.
     *
     * @global $_FP_SMARTY
     */
    private function daddy_toolbar() {
        $tpl = PluginEngine::getTemplatePlugin();
        // get all available images
        $images_dir = isset($fmbConf['daddy']['images_dir']) ? $fmbConf['daddy']['images_dir'] : 'images/';
        $attach_dir = isset($fmbConf['daddy']['attach_dir']) ? $fmbConf['daddy']['attach_dir'] : 'files/';

        $imageslist = $this->utils_listdir($images_dir);
        array_unshift($imageslist, '--');

        $attachslist = $this->utils_listdir($attach_dir);
        array_unshift($attachslist, '--');
/*
        echo "<!-- bbcode plugin -->\n";
        echo '<script type="text/javascript" src="'. plugin_geturl('bbcode') .'res/editor.js"></script>'."\n";
        echo $_FP_SMARTY->fetch('plugin:bbcode/toolbar');
        echo "<!-- end of bbcode plugin -->\n";
*/
    }

    /**
     * Adds special stlye definitions into the HTML head.
     *
     */
    private function plugin_daddy_style() {
        $tpl = PluginEngine::getTemplatePlugin();
/*
        echo "	<!-- bbcode plugin -->\n";
        echo '	<link rel="stylesheet" type="text/css" href="'. plugin_geturl('bbcode') ."res/bbcode.css\" />\n";
        echo "	<!-- end of bbcode plugin -->\n";
*/
    }

    // function prototype:
    // array utils_kexplode(string $string, string $delim='|')
    
    // explodes a string into an array by the given delimiter;
    // delimiter defaults to pipe ('|').
    // the string must be formatted as in:
    //  key1|value1|key2|value2 , etc.
    // the array will look like
    // $arr['key1'] = 'value1'; $arr['key2'] = 'value2'; etc.
    
    function utils_kexplode($string, $delim='|', $keyupper=true) {
        $arr = array();
        $string = trim($string);
    
        $k = strtolower(strtok($string, $delim));   
        $arr[$k] = strtok($delim);
        while (( $k = strtok($delim) ) !== false) {
            if ($keyupper && !preg_match('/[A-Z-_]/',$k)){
                /*  
                trigger_error("Failed parsing <pre>$string</pre>
                keys were supposed to be UPPERCASE but <strong>\"$k\"</strong> was found; file may be corrupted
                or in an expected format. <br /> 
                Some SimplePHPBlog files may raise this error: set DUMB_MODE_ENABLED 
                to true in your defaults.php to force parsing of the offending keys.", 
                E_USER_WARNING);
                */
                continue;
            }   
    
            $arr[strtolower($k)] = strtok($delim);
        }   
    
        return $arr;
    }   

    function utils_listdir($dir) {
        $array_items = array();
        if ($handle = opendir($dir)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != '.' && $file != '..') {
                    if (is_dir($dir. "/" . $file)) {
                        $array_items = array_merge($array_items, $this->utils_listdir($dir. '/' . $file));
                    } else {
                        $file = $dir . '/' . $file;
                        $file = preg_replace('/\/\//si', '/', $file);
                        $file = preg_replace('#'.FMB_PATH.'#si', '', $file);
                        $array_items[] = $file;
                    }
                }
            }
            closedir($handle);
        }
        return $array_items;
    }

    function cacheid2path($id) {
        return dirname(__FILE__).'/cache/'.substr($id,0,2).'/'.substr($id,2,2).'/';
    }

    function daddy_retrieve_image(&$d, &$h = null) {
        global $fmbConf;
        $images_dir = isset($fmbConf['daddy']['images_dir']) ? $fmbConf['daddy']['images_dir'] : 'images/';
        $use_handler = isset($fmbConf['daddy']['use_handler']) ? $fmbConf['daddy']['use_handler'] : false;
        $lpath = preg_replace('#'.FMB_PATH.'#si', '', dirname(__FILE__));
        $file = $fmbConf['daddy']['rewrite_handler'];
        $md5 = hash('md5', $d);
        $cacheid = substr($md5, 0, 16);
        $storagedir = Daddy::cacheid2path($cacheid);

        if (!is_dir($storagedir)) mkdir($storagedir, 0705, true);
        if (is_file($storagedir.$cacheid))
        {
            $data = json_decode(file_get_contents($storagedir.$cacheid));
            if (0 != strcmp($data->md5, $md5)) {
                // Oups... improbable collision.
                return false;
            } else if ($data->ok) {
                $d = $data->path;
                $h = $data->handle;
                return true;
            } else if ($data->last > (time()-(24*3600))) {
                // Only try to get the file once a day
                return false;
            }
        }
        list($httpstatus, $headers, $image) = Daddy::getHTTP($d);
        $ret = false;
        if (false === strpos($httpstatus,'200 OK')) {
            $data = array('md5'=>$md5, 'last'=>time(), 'ok'=>false);
        } else {
            $ret = true;
            $f = basename($d);

            if ($use_handler || $images_dir[0] == '/') {
                if (isset($file)) {
                    $file = str_replace('%f', $f, $file);
                } else {
                    $file = $lpath.'/getfile.php?f='.$f;
                }    
                $h = $file.'&amp;t=i';
            }

            $d = $images_dir.'/'.$f;

            if ($d[0] == '/') {
                $tmp = $d;
            } else {
                $tmp = FMB_PATH.$d;
            }

            while (is_file($tmp)) {
                $uniq = uniqid();
                $d = $images_dir.'/'.$uniq.'-'.$f;
                if ($d[0] == '/') {
                    $tmp = $d;
                } else {
                    $tmp = FMB_PATH.$d;
                }
            }

            file_put_contents($tmp, $image);

            $data = array('path'=>$d, 'handle'=>$h, 'md5'=>$md5, 'last'=>time(), 'ok'=>true);
        }
        // New cache
        file_put_contents($storagedir.$cacheid,json_encode($data));
        return $ret;
    }

    /**
     * Remaps the URL so that there's no hint to your attachs/ directory.
     *
     * @param string $d
     * @return boolean
     */
    function daddy_remap_url(&$d, &$h = null) {
        // NWM: "attachs/" is interpreted as a keyword, and it is translated to the actual path of ATTACHS_DIR
        global $fmbConf;

        $images_dir = isset($fmbConf['daddy']['images_dir']) ? $fmbConf['daddy']['images_dir'] : 'images/';
        $attach_dir = isset($fmbConf['daddy']['attach_dir']) ? $fmbConf['daddy']['attach_dir'] : 'files/';
        $use_handler = isset($fmbConf['daddy']['use_handler']) ? $fmbConf['daddy']['use_handler'] : false;
        $file = $fmbConf['daddy']['rewrite_handler'];
        $lpath = preg_replace('#'.FMB_PATH.'#si', '', dirname(__FILE__));

        if (isset($fmbConf['daddy']['remap']) && isset($fmbConf['daddy']['catch'])) {
            $m = $fmbConf['daddy']['remap']; 
            if (preg_match("#$m#", $d, $catch)) {
                $d = $catch[$fmbConf['daddy']['catch']];
            }
        }

        if (strpos($d, ':') === false) {
            // if is relative url
            // absolute path, relative to this server
            if ($d[0] == '/') {
                /*
                    If $d begins with a slash, we first 
                    strip it otherwise the string would
                    look like 
                    http://mysite.com//path/you/entered
                                     ^^ ugly double slash :P
                */
                $d = $fmbConf['site']['url'] . substr($d, 1);
            }
            if (substr($d, 0, 8) == 'attachs/') {
                if ($use_handler || $attach_dir[0] == '/') {
                    if (isset($file)) {
                        $file = str_replace('%f', substr($d, 8), $file);
                    } else {
                        $file = $lpath.'/getfile.php?f='.substr($d, 8);
                    }
                    $d = $file;
                } else {
                    $d = substr_replace ($d, $attach_dir, 0, 8 );
                }
                return true;
            }
            if ($use_handler && strncmp($d, $attach_dir, strlen($attach_dir)) == 0) {
                if (isset($file)) {
                    $file = str_replace('%f', substr($d, strlen($attach_dir)), $file);
                } else {
                    $file = $lpath.'/getfile.php?f='.substr($d, strlen($attach_dir));
                }
                $d = $file;
                return true;
            }
            if (substr($d, 0, 7) == 'images/') {
                if ($use_handler || $images_dir[0] == '/') {
                    if (isset($file)) {
                        $file = str_replace('%f', substr($d, 0, 7), $file);
                    } else {
                        $file = $lpath.'/getfile.php?f='.substr($d, 0, 7);
                    }
                    $h = $file.'&amp;t=i';
                } else {
                    $d = substr_replace ($d, $images_dir, 0, 7 );
                }
                return true;
            }
            if ($use_handler && strncmp($d, $images_dir, strlen($images_dir)) == 0) {
                    if (isset($file)) {
                        $file = str_replace('%f', substr($d, strlen($images_dir)), $file);
                    } else {
                        $file = $lpath.'/getfile.php?f='.substr($d, strlen($images_dir));
                    }
                $h = $file.'&amp;t=i';
                return true;
            }
        }
        if (strpos($d, 'www.') === 0) {
            $d = 'http://' . $d;
        }
        return false;
    }

    // Parse HTTP response headers and return an associative array.
    function http_parse_headers( $headers )
    {
        $res=array();
        foreach($headers as $header)
        {
            $i = strpos($header,': ');
            if ($i!==false)
            {
                $key=substr($header,0,$i);
                $value=substr($header,$i+2,strlen($header)-$i-2);
                $res[$key]=$value;
            }
        }
        return $res;
    }

    /* GET an URL.
       Input: $url : url to get (http://...)
              $timeout : Network timeout (will wait this many seconds for an anwser before giving up).
       Output: An array.  [0] = HTTP status message (eg. "HTTP/1.1 200 OK") or error message
                          [1] = associative array containing HTTP response headers (eg. echo getHTTP($url)[1]['Content-Type'])
                          [2] = data
        Example: list($httpstatus,$headers,$data) = getHTTP('http://sebauvage.net/');
                 if (strpos($httpstatus,'200 OK')!==false)
                     echo 'Data type: '.htmlspecialchars($headers['Content-Type']);
                 else
                     echo 'There was an error: '.htmlspecialchars($httpstatus)
    */
    function getHTTP($url,$timeout=30)
    {
        try
        {  
            $options = array('http'=>array('method'=>'GET','timeout' => $timeout)); // Force network timeout
            $context = stream_context_create($options);
            $data=file_get_contents($url,false,$context,-1, 10000000); // We download at most 10 Mb from source.
            if (!$data) { return array('HTTP Error',array(),''); }
            $httpStatus=$http_response_header[0]; // eg. "HTTP/1.1 200 OK"
            $responseHeaders=Daddy::http_parse_headers($http_response_header);
            return array($httpStatus,$responseHeaders,$data);
        }
        catch (Exception $e)  // getHTTP *can* fail silentely 
        {  
            return array($e->getMessage(),'','');
        }
    }

    /**
     * Function to link documents.
     *
     * @param string $action
     * @param array $attributes
     * @param string $content
     * @param mixed $params Not used
     * @param mixed $node_object Not used
     * @return string
     */
    function do_daddy_url ($action, $attributes, $content, $params, $node_object) {
        if ($action == 'validate') {
            return true;
        }
        global $fmbConf;
        $url_maxlen = isset($fmbConf['daddy']['url_maxlen']) ? $fmbConf['daddy']['url_maxlen'] : 80;
    
        // the code was specified as follows: [url]http://.../[/url]
        if (!isset ($attributes['default'])) {
            // cut url if longer than > URL_MAXLEN
            $url = $content;
            if (($l = strlen($url)) > $url_maxlen) {
                $t = (int)($url_maxlen / 2);
                $content = substr($url, 0, $t) .' &hellip; '. substr($url, $l-$t);
            }
        } else  {
            // else the code was specified as follows: [url=http://.../]Text[/url]
            $url = $attributes['default'];
        }
        $local = Daddy::daddy_remap_url($url);
        $the_url = $local
            ? ($fmbConf['site']['url'] . $url)
            : $url;
        $rel = isset($attributes['rel'])
            ? ' rel="' . $attributes['rel'] . '"'
            : '';
        $extern = !$local
            ? ' class="externlink" title="Go to '.$the_url.'"'
            : '';
        return '<a'. $extern .' href="'. $the_url .'"'. $rel .'>'. $content .'</a>';
    }

    /**
     * Function to include images.
     *
     * @param string $action
     * @param array $attributes
     * @param string $content
     * @param mixed $params Not used
     * @param mixed $node_object Not used
     * @return string
     */
    function do_daddy_img($action, $attributes, $content, $params, $node_object) {
        if ($action == 'validate') {
            return true;
        }
        global $fmbConf;
        $cache = isset($fmbConf['daddy']['cache_images']) ? $fmbConf['daddy']['cache_images'] : false;

        $path = $attributes['default'];
        if (!isset($attributes['default'])) {
            $path = $content;
        }
        $remap = false;
        $handle = null;

        $absolutepath = $actualpath = $path;
        // NWM: "images/" is interpreted as a keyword, and it is translated to the actual path of IMAGES_DIR
        $image_is_local = Daddy::daddy_remap_url($actualpath, $handle);
        $float = ' class="center" ';
        $popup_start = '';
        $popup_end = '';

        $alt = $title = basename($actualpath);
        $useimageinfo = true; // use IPTC info

        if (isset($attributes['alt'])) {
            $alt = htmlspecialchars($attributes['alt']);
            $useimageinfo = false;
        }

        if (isset($attributes['title'])) {
            $title = htmlspecialchars($attributes['title']);
            $useimageinfo = false;
        }

        if (false === $image_is_local && $cache || (isset($attributes['cache']) && strcasecmp($attributes['cache'], 'true') == 0)) {
            $image_is_local = Daddy::daddy_retrieve_image($actualpath, $handle);
        }        
        $remap = $actualpath != $path;

        $img_size = array();
        // let's disable socket functions for remote files
        // slow remote servers may otherwise lockup the system
        if ($image_is_local) {
            $img_info = array();
            if (true === $remap && $actualpath[0] != '/') {
                $img_size = @getimagesize(FMB_PATH.$actualpath, $img_info);
            } else {
                $img_size = @getimagesize($actualpath, $img_info);
            }
            $absolutepath = $fmbConf['site']['url'] . $actualpath;
            
            if ($useimageinfo && function_exists('iptcparse')) {
                if ($img_size['mime'] == 'image/jpeg') {
                    // tiffs won't be supported
                    
                    if(is_array($img_info)) {   
                        $iptc = iptcparse($img_info["APP13"]);
                        $title = @$iptc["2#005"][0]? htmlspecialchars($iptc["2#005"][0]) : $title;
                        $alt = isset($iptc["2#120"][0])? htmlspecialchars($iptc["2#120"][0],1) : $title;
                    }  
                
                }
            }
        }
        $orig_w = $width = isset($img_size[0])
            ? $img_size[0]
            : 0;
        $orig_h = $height = isset($img_size[1])
            ? $img_size[1]
            : 0;
        $thumbpath =  null;	
        // default: resize to 0, which means leaving it as it is, as width and hight will be ignored ;)
        $scalefact = 0;
        /*
            scale attribute has priority over width and height if scale is
            set popup is set to true automatically, unless it is explicitly
            set to false
        */
        if (isset($attributes['scale'])) {
            if (substr($attributes['scale'], -1, 1) == '%') {
                // Format: NN%. We ignore %
                $val = substr($attributes['scale'], 0, -1);
            } else {
                $val = $attributes['scale'];
            }
            $scalefact = $val / 100.0;
            $width = (int)($scalefact * $width); 
            $height = (int)($scalefact * $height);
        } elseif (isset($attributes['width']) && isset($attributes['height'])) {
            // if both width and height are set, we assume proportions are ok
            $width = (int)$attributes['width']; 
            $height = (int)$attributes['height'];
        } elseif (isset($attributes['width'])) {
            // if only width is set we calc proportions
            $scalefact = $orig_w? ($attributes['width'] / $orig_w) : 0;
            $width = (int)$attributes['width'];
            $height = (int)($scalefact * $orig_h);
        } elseif (isset($attributes['height'])) {
            // if only height is set we calc proportions
            $scalefact = $orig_w? ($attributes['height'] / $orig_h) : 0;
            $height = (int)$attributes['height'];
            $width = (int)($scalefact * $orig_w);
        } elseif (isset($fmbConf['thumb']['autoreduce']) && $orig_w > $fmbConf['thumb']['autoreduce']) {
            $width = $fmbConf['thumb']['autoreduce'];
            $height = ($fmbConf['thumb']['autoreduce'] / $orig_w) * $orig_h;
        }
        if ($height < $orig_h) {
            $attributes['popup'] = true;
        }
        if ($height != $orig_h) {
            $thumb = PluginEngine::getPlugin('thumb');
            $thumbpath = '';
            if (null != $thumb) {
                if (true === $remap && $actualpath[0] != '/') {
                    $thumbpath = $thumb->do_thumb(FMB_PATH.$actualpath, $img_size, array($width, $height));
                    $thumbpath = substr($thumbpath,strlen(FMB_PATH));
                } else {
                    $thumbpath = $thumb->do_thumb($actualpath, $img_size, array($width, $height));
                }
            }
        }
        

        $popup = null;
        if (isset($attributes['popup']) && ($attributes['popup'])) {
            $pop_width = $orig_w
                ? $orig_w
                : 800;
            $pop_height = $orig_h
                ? $orig_h
                : 600;
            $popup = ' onclick="Popup=window.open("'. $absolutepath
                .'","Popup","toolbar=no,location=no,status=no,"'
                .'"menubar=no,scrollbars=yes,resizable=yes,width='
                . $pop_width .',height='. $pop_height .'"); return false;"';
        }
        // Plugin hook, here lightbox attachs
        $slimfast = PluginEngine::getPlugin('SlimFast');
        if (null != $slimfast) {
            $popup = $slimfast->popup($attributes['rel'], $alt, $title); 
        }
        if (null != $popup) {
            $popup_start = '<a title="'. $title .'" href="'. (null != $handle ? $fmbConf['site']['url'].$handle : $absolutepath) .'"'. $popup .'>';
            $popup_end = '</a>';
        }

        $img_width = $width
            ? ' width="'.$width.'"'
            : '';
        $img_height = $height
            ? ' height="'.$height.'"'
            : '' ;
        if (isset($attributes['float'])) {
            $float = ($attributes['float'] == 'left' || $attributes['float'] == 'right')
                ? ' class="float'. $attributes['float'] .'"'
                : ' class="center"';
        }
        $src = $thumbpath
            ? ($fmbConf['site']['url'] . null != $handle ? $fmbConf['site']['url'].$handle.'&amp;thumb' : $thumbpath)
            : (null != $handle ? $handle : $absolutepath); // $attributes['default'])
        $pop = $popup_start
            ? ''
            : ' title="'.$title.'" ';
        return $popup_start .'<img src="'. $src .'" alt="'. $alt. '" '.
            $pop.$float.$img_width.$img_height .' />'. $popup_end;
    }

    /**
     * Function for embedding videos
     *
     * @param string $action
     * @param array $attr
     * @param string $content
     * @param mixed $params Not used
     * @param mixed $node_object Not used
     * @return string
     */
    function do_daddy_video($action, $attr, $content, $params, $node_object) {
        if ($action == 'validate') {
            return true;
        }
        $vurl = parse_url($attr['default']);
        if (isset($attr['type'])) {
            $type = $attr['type'];
        } else {
            // is it http://www.MYSITE.com  or http://MYSITE.com ?
            $web = explode('.', $vurl['host']);
            array_pop($web);
            $type = isset($web[1])
                ? $web[1]
                : $web[0];
        }
        $query = Daddy::utils_kexplode($vurl['query'], '=&');
        $the_url = null;
        $others = '';
        switch ($type) {
            case 'google':
                $the_url = "http://video.google.com/googleplayer.swf?docid={$query['docid']}";
                $others = '<param name="FlashVars" value="playerMode=embedded" />';
                break;
            case 'youtube':
                $the_url = "http://youtube.com/v/{$query['v']}";
                break;
            case 'default':
            default:
                $the_url = null;
        }
        if ($the_url) {
            $width = isset($attr['width'])
                ? $attr['width']
                : '400';
            $height = isset($attr['height'])
                ? $attr['height']
                : '326';
            $float = isset($attr['float'])
                ? "style=\"float: {$attr['float']}\" "
                : '';
            return '<object type="application/x-shockwave-flash" height="'.$height.'" width="'.$width.'" '
                . $float .'data="'. $the_url .'">'
                .'<param name="movie" value="'. $the_url .'" />'
                . $others .'</object>';
        }
        return '[unsupported video]';
    }

    /**
     * Function to return code
     *
     * @param string $action
     * @param array $attributes
     * @param string $content
     * @param mixed $params Not used
     * @param mixed $node_object Not used
     * @return string
     */
    function do_daddy_code ($action, $attributes, $content, $params, $node_object) {
        if ($action == 'validate') {
            return true;
        }
        $temp_str = $content;
        $temp_str = str_replace('<', '&lt;', $temp_str);
        $temp_str = str_replace('>', '&gt;', $temp_str);
        $temp_str = str_replace('<br />', chr(10), $temp_str);
        $temp_str = str_replace(chr(10). chr(10), chr(10), $temp_str);
        $temp_str = str_replace(chr(32), '&nbsp;', $temp_str);
        
        $a = '';
        if (null != PluginEngine::getPlugin('highlightjs')) {
            if (isset($attributes['default'])) {
                $a = $attributes['default'];
                if (strcasecmp($a,'c') == 0) {
                    $a = 'cpp';
                }
                if (strcasecmp($a,'none') == 0) {
                    $a = 'no-highlight';
                }
            }
        }
        if ($a) {
            $a = ' class="'. $a .'"';
        }
        if (isset($attributes['default']) && strcasecmp($attributes['default'],'html') == 0 || strcasecmp($attributes['default'],'php') == 0) {
            return Daddy::do_daddy_html($action, $attributes, $content, $params, $node_object);
        }
        return '<pre><code'. $a .'>'. $temp_str .'</code></pre>';
    }

    /**
     * Function to return html
     *
     * @param string $action
     * @param array $attributes
     * @param string $content
     * @param mixed $params Not used
     * @param mixed $node_object Not used
     * @return string
     */
    function do_daddy_html ($action, $attributes, $content, $params, $node_object) {
        if ($action == 'validate') {
            return true;
        }
        return '<pre><code class="html">'.htmlspecialchars($content).'</code></pre>';
    }

    /**
     * Function to colorize text.
     *
     * @param string $action
     * @param array $attributes
     * @param string $content
     * @param mixed $params Not used
     * @param mixed $node_object Not used
     * @return string
     */
    function do_daddy_color ($action, $attributes, $content, $params, $node_object) {
        if ($action == 'validate') {
            return true;
        }
        return '<span style="color:'. $attributes['default'] .'">'. $content .'</span>';
    }

    /**
     * Function to set font size.
     *
     * @param string $action
     * @param array $attributes
     * @param string $content
     * @param mixed $params Not used
     * @param mixed $node_object Not used
     * @return string
     */
    function do_daddy_size ($action, $attributes, $content, $params, $node_object) {
        if ($action == 'validate') {
            return true;
        }
        return '<span style="font-size:'. $attributes['default'] .'">'. $content .'</span>';
    }

    /**
     * Function to align elements.
     *
     * @param string $action
     * @param array $attributes
     * @param string $content
     * @param mixed $params Not used
     * @param mixed $node_object Not used
     * @return string
     */
    function do_daddy_align($action, $attr, $content, $params, $node_object) {
        return '<div style="text-align:'. $attr['default'] .'">'. $content .'</div>';
    }

    /**
     * Function to make a list.
     *
     * @param string $action
     * @param array $attributes
     * @param string $content
     * @param mixed $params Not used
     * @param mixed $node_object Not used
     * @return string
     */
    function do_daddy_list ($action, $attributes, $content, $params, $node_object) {
        if ($action == 'validate') {
            return true;
        }
        if (isset($attributes['default']) && $attributes['default'] == '#') {
            $list = 'ol';
        } else {
            $list = 'ul';
        }
        return "<$list>$content</$list>";
    }

    /**
     * Simplified codes for comments.
     *
     * @param string $text
     * @return strng
     */
    private function daddy_comment($text) {
        $comment = new StringParser_BBCode();
        // If you set it to false the case-sensitive will be ignored for all codes
        $comment->setGlobalCaseSensitive (false);
        $comment->setMixedAttributeTypes(true);
        $comment->addCode(
            'b',
            'simple_replace',
            null,
            array(
                'start_tag' => '<strong>',
                'end_tag' => '</strong>'
            ),
            'inline',
            array(
                'listitem', 'block', 'inline', 'link'
            ),
            array()
        );
        $comment->addCode(
            'strong',
            'simple_replace',
            null,
            array(
                'start_tag' => '<strong>',
                'end_tag' => '</strong>'
            ),
            'inline',
            array(
                'listitem', 'block', 'inline', 'link'
            ),
            array()
        );
        $comment->addCode(
            'i',
            'simple_replace',
            null,
            array(
                'start_tag' => '<em>',
                'end_tag' => '</em>'
            ),
            'inline',
            array(
                'listitem', 'block', 'inline', 'link'
            ),
            array()
        );
        $comment->addCode(
            'em',
            'simple_replace',
            null,
            array(
                'start_tag' => '<em>',
                'end_tag' => '</em>'
            ),
            'inline',
            array(
                'listitem', 'block', 'inline', 'link'
            ),
            array()
        );
        $comment->addCode(
            'ins',
            'simple_replace',
            null,
            array(
                'start_tag' => '<ins>',
                'end_tag' => '</ins>'
            ),
            'inline',
            array(
                'listitem', 'block', 'inline', 'link'
            ),
            array());
        $comment->addCode(
            'u',
            'simple_replace',
            null,
            array(
                'start_tag' => '<ins>',
                'end_tag' => '</ins>'
            ),
            'inline',
            array(
                'listitem', 'block', 'inline', 'link'
            ),
            array());
        $comment->addCode(
            'del',
            'simple_replace',
            null,
            array(
                'start_tag' => '<del>',
                'end_tag' => '</del>'
            ),
            'inline',
            array(
                'listitem', 'block', 'inline', 'link'
            ),
            array());
        $comment->addCode(
            'strike',
            'simple_replace',
            null,
            array(
                'start_tag' => '<del>',
                'end_tag' => '</del>'
            ),
            'inline',
            array(
                'listitem', 'block', 'inline', 'link'
            ),
            array());
        $comment->addCode(
            'blockquote',
            'simple_replace',
            null,
            array(
                'start_tag' => '<blockquote><p>',
                'end_tag' => '</p></blockquote>'
            ),
            'inline',
            array(
                'listitem', 'block', 'inline', 'link'
            ),
            array()
        );
        $comment->addCode(
            'quote',
            'simple_replace',
            null,
            array(
                'start_tag' => '<blockquote><p>',
                'end_tag' => '</p></blockquote>'
            ),
            'inline',
            array(
                'listitem', 'block', 'inline', 'link'
            ),
            array()
        );
        $comment->addCode(
            'pre',
            'simple_replace',
            null,
            array(
                'start_tag' => '<pre>',
                'end_tag' => '</pre>'
            ),
            'inline',
            array(
                'listitem', 'block', 'inline', 'link'
            ),
            array());
        $comment->addCode(
            'code',
            'usecontent',
            'Daddy::do_daddy_code',
            array(),
            'inline',
            array(
                'listitem', 'block', 'inline', 'link'
            ),
            array()
        );
        return $comment->parse($text);
    }

    function format($params)
    {
        $mem = PluginEngine::getCachingPlugin();
        if (null != $mem) {
            $key = md5($params[0]);
            $res = $mem->get($key);
            if (null == $res) {
                if (true === $params[1]) {
                    $res = $this->bbcode->parse($params[0]);
                } else {
                    $res = $this->daddy_comment($params[0]);
                }
                $mem->set($key, $res);
            }   
            return $res;
        }   
        if (true === $params[1]) {
            return $this->bbcode->parse($params[0]);
        } else {
            return $this->daddy_comment($params[0]);
        }
    }
}
?>
