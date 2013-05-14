<?php
use FMB\Core\Core;
use FMB\Plugins\PluginEngine;
use FMB\Plugins\Plugin;

Core::loadFile('src/plugins/Plugin.class.php');

class Baby extends Plugin
{

    private $imgURL;
    private $playerURL;

    public function init()
    {
        global $fmbConf;
        
        $this->imgURL=$fmbConf['baby']['url'];
        $this->playerURL=$fmbConf['baby']['url'].$fmbConf['baby']['player'];
        
        $fmbPluginEngine = PluginEngine::getInstance();
        $fmbPluginEngine->setHook('format', 'Baby', 'format');
        return true;
    }

    function format($params)
    {

        $mem = PluginEngine::getCachingPlugin();
        if (null != $mem) {
            $key = md5($params[0]);
            $res = $mem->get($key);
            if (null == $res) {
//                $res = $this->bbcode2html($params[0], $params[1]);
                $res = $this->extnl2br($params[0]);
                $mem->set($key, $res);
            }
            return $res;
        }
//        return $this->bbcode2html($params[0], $params[1]);

        return $this->extnl2br($params[0]);
    }

    function bbcode2html($text, $full=false)
    {
        $pregFull = array (
            // [color=X]
            '`(?<!\\\\)\[color(?::\w+)?=(.*?)\](.*?)\[\/color(?::\w+)?\]`si'
                => '<span style="color:\\1">\\2</span>',

            // [size=X]
            '`(?<!\\\\)\[size(?::\w+)?=(.*?)\](.*?)\[\/size(?::\w+)?\]`si'
                => '<span style="font-size:\\1">\\2</span>',

            /*******************************
             * Lists
             *******************************/
            // [*]
            '`(?<!\\\\)(?:\s*<br\s*\/?>\s*)?\[\*(?::\w+)?\](.*?)(?=(?:\s*<br\s*\/?>\s*)?\[\*|(?:\s*<br\s*\/?>\s*)?\[\/?list)`si'
                => "\n".'<li class="bb-listitem">\\1</li>',

            // [/list]
            '`(?<!\\\\)(?:\s*<br\s*\/?>\s*)?\[\/list(:(?!u|o)\w+)?\](?:<br\s*\/?>)?`si'
                => "\n".'</ul>',

            // [/list:u]
            '`(?<!\\\\)(?:\s*<br\s*\/?>\s*)?\[\/list:u(:\w+)?\](?:<br\s*\/?>)?`si'
                => "\n".'</ul>',

            // [/list:o]
            '`(?<!\\\\)(?:\s*<br\s*\/?>\s*)?\[\/list:o(:\w+)?\](?:<br\s*\/?>)?`si'
                => "\n".'</ol>',

            // [list]
            '`(?<!\\\\)(?:\s*<br\s*\/?>\s*)?\[list(:(?!u|o)\w+)?\]\s*(?:<br\s*\/?>)?`si'
                => "\n".'<ul class="bb-list-unordered">'."\n",

            // [list:u]
            '`(?<!\\\\)(?:\s*<br\s*\/?>\s*)?\[list:u(:\w+)?\]\s*(?:<br\s*\/?>)?`si'
                => "\n".'<ul class="bb-list-unordered">'."\n",

            // [list:o]
            '`(?<!\\\\)(?:\s*<br\s*\/?>\s*)?\[list:o(:\w+)?\]\s*(?:<br\s*\/?>)?`si'
                => "\n".'<ol class="bb-list-ordered">'."\n",

            // [list=1], [list:o=1]
            '`(?<!\\\\)(?:\s*<br\s*\/?>\s*)?\[list(?::o)?(:\w+)?=1\]\s*(?:<br\s*\/?>)?`si'
                => "\n".'<ol class="bb-list-ordered, bb-list-ordered-d">'."\n",

            // [list=i], [list:o=i]
            '`(?<!\\\\)(?:\s*<br\s*\/?>\s*)?\[list(?::o)?(:\w+)?=i\]\s*(?:<br\s*\/?>)?`s'
                => "\n".'<ol class="bb-list-ordered, bb-list-ordered-lr">'."\n",

            // [list=I], [list:o=I]
            '`(?<!\\\\)(?:\s*<br\s*\/?>\s*)?\[list(?::o)?(:\w+)?=I\]\s*(?:<br\s*\/?>)?`s'
                => "\n".'<ol class="bb-list-ordered, bb-list-ordered-ur">'."\n",

            // [list=a], [list:o=a]
            '`(?<!\\\\)(?:\s*<br\s*\/?>\s*)?\[list(?::o)?(:\w+)?=a\]\s*(?:<br\s*\/?>)?`s'
                => "\n".'<ol class="bb-list-ordered, bb-list-ordered-la">'."\n",

            // [list=A], [list:o=A]
            '`(?<!\\\\)(?:\s*<br\s*\/?>\s*)?\[list(?::o)?(:\w+)?=A\]\s*(?:<br\s*\/?>)?`s'
                => "\n".'<ol class="bb-list-ordered, bb-list-ordered-ua">'."\n",

            // [img]
            '`(?<!\\\\)\[img(?::\w+)?\](.*?)\[\/img(?::\w+)?\]`si'
                => '<img src="\\1" alt="\\1" class="bb-image" />',

            // [img=XxY]
            '`(?<!\\\\)\[img(?::\w+)?=(.*?)x(.*?)\](.*?)\[\/img(?::\w+)?\]`si'
                => '<img width="\\1" height="\\2" src="\\3" alt="\\3" class="bb-image" />',

            // [youtube]
            '`(?<!\\\\)\[youtube(?::\w+)?\](.*?)\[\/youtube(?::\w+)?\]`si'
                => '<object type="application/x-shockwave-flash" data="http://www.youtube.com/v/\\1?fs=1" width="480" height="385">'.
                   '<param name="movie" value="http://www.youtube.com/v/\\1?fs=1" />'.
                   '<param name="wmode" value="transparent" />'.
                   '</object>',

            // [dailymotion]
            '`(?<!\\\\)\[dailymotion(?::\w+)?\](.*?)\[\/dailymotion(?::\w+)?\]`si'
                => '<object type="application/x-shockwave-flash" data="http://www.dailymotion.com/swf/video/\\1" width="480" height="270">'.
                   '<param name="movie" value="http://www.dailymotion.com/swf/video/\\1" />'.
                   '</object>',

            // [mp3]
            '`(?<!\\\\)\[mp3(?::\w+)?\](.*?)\[\/mp3(?::\w+)?\]`si'
                => '<object type="application/x-shockwave-flash" data="'.$this->playerURL.'" height="20" width="200">'.
                   '<param name="movie" value="'.$this->playerURL.'" />'.
                   '<param name="FlashVars" value="mp3=\\1" />'.
                   '</object>',

            // [mp3=c1,c2,c3]
            '`(?<!\\\\)\[mp3(?::\w+)?=(.*?),(.*?),(.*?)\](.*?)\[\/mp3(?::\w+)?\]`si'
                => '<object type="application/x-shockwave-flash" data="'.$this->playerURL.'" height="20" width="200">'.
                   '<param name="movie" value="'.$this->playerURL.'" />'.
                   '<param name="FlashVars" value="c1=\\1&amp;c2=\\2&amp;c3=\\3&amp;mp3=\\4" />'.
                   '</object>',

            // [code]
            '/(?<!\\\\)\[code(?::\w+)?=(?:&quot;|"|\')?(.*?)["\']?(?:&quot;|"|\')?\](.*?)\[\/code\]/si'
                => '<pre>\\2</pre>',
        );
    
        $preg = array(
            /******************************
            * Text Formatting
            ******************************/
            // [b], [i], [u], [s]
        
            '`(?<!\\\\)\[b(?::\w+)?\](.*?)\[\/b(?::\w+)?\]`si'
                => '<span style="font-weight:bold">\\1</span>',

            '`(?<!\\\\)\[i(?::\w+)?\](.*?)\[\/i(?::\w+)?\]`si'
                => '<span style="font-style:italic">\\1</span>',

            '`(?<!\\\\)\[u(?::\w+)?\](.*?)\[\/u(?::\w+)?\]`si'
                => '<span style="text-decoration:underline">\\1</span>',

            '`(?<!\\\\)\[s(?::\w+)?\](.*?)\[\/s(?::\w+)?\]`si'
                => '<span style="text-decoration:line-through">\\1</span>',

            // [quote], [quote=X], [quote="X"]
            '`(?<!\\\\)\[quote(?::\w+)?\](.*?)\[\/quote(?::\w+)?\]`si'
                => '<div class="bb-quote">'.
                   '<div class="bb-quote-header">'._('Citation :').'</div>'.
                   '<div class="bb-quote-content">\\1</div>'.
                   '</div>',

            '`(?<!\\\\)\[quote(?::\w+)?=(?:&quot;|"|\')?(.*?)["\']?(?:&quot;|"|\')?\](.*?)\[\/quote\]`si'
                => '<div class="bb-quote">'.
                   '<div class="bb-quote-header">\\1 '._('a &eacute;crit :').'</div>'.
                   '<div class="bb-quote-content">\\2</div>'.
                   '</div>',

            /*******************************
             * Links
             *******************************/

            // [email]
            '`(?<!\\\\)\[email(?::\w+)?\](.*?)\[\/email(?::\w+)?\]`si'
                => '<a href="mailto:\\1" class="bb-email">\\1</a>',

            '`(?<!\\\\)\[email(?::\w+)?=(.*?)\](.*?)\[\/email(?::\w+)?\]`si'
                => '<a href="mailto:\\1" class="bb-email">\\2</a>',

            // [url]
            '`(?<!\\\\)\[url(?::\w+)?\]www\.(.*?)\[\/url(?::\w+)?\]`si'
                => '<a href="http://www.\\1" class="bb-url">\\1</a>',

            '`(?<!\\\\)\[url(?::\w+)?\](.*?)\[\/url(?::\w+)?\]`si'
                => '<a href="\\1" class="bb-url">\\1</a>',

            '`(?<!\\\\)\[url(?::\w+)?=(.*?)?\](.*?)\[\/url(?::\w+)?\]`si'
                => '<a href="\\1" class="bb-url">\\2</a>',

            // escaped tags like \[b], \[color], \[url], ...
            '`\\\\(\[\/?\w+(?::\w+)*\])`'
                => '\\1',
        );

        $text = htmlspecialchars($text);

        $text = preg_replace(array_keys($preg),
                                array_values($preg),
                                $text);
        if ($full) {
            $text = preg_replace(array_keys($pregFull),
                                 array_values($pregFull),
                                 $text);
        }
        
        return $this->extnl2br($text);
    }

    function extnl2br($text)
    {
        // Check for <pre> tag.
        if(!strpos($text, '<pre>')) {
            return nl2br($this->smilies($text));
        }

        // Some <pre> tag, start formatting consequently.
        $lines = explode("\n", $text);
        $res = '';
        $pre = '';
        $isPreFound = false;

        foreach($lines as $line) {

            if(strpos($line, '<pre>') !== false) {
                $isPreFound = true;
            }
            
            if(strpos($line, '</pre>') !== false) {
                
                $isPreFound = false;
                $res .= $pre;
                $pre = "";
            }

            if ($isPreFound) {    
                $pre .= $line."\n";
            } else {
                $res .= $this->smilies($line).'<br />';
            }
        }

        return $res;
    }

    function smilies($text)
    {
        $smilies = array (
            // :), :-)
            '`(?<!\\\\)\:-?\)`si'
                => '<img src="'.$this->imgURL.'blog/images/smilies/smile.png" alt=":)" />',

            // :(, :-(
            '`(?<!\\\\)\:-?\(`si'
                => '<img src="'.$this->imgURL.'blog/images/smilies/sad.png" alt=":(" />',

            // :|, :-|
            '`(?<!\\\\)\:-?\|`si'
                => '<img src="'.$this->imgURL.'blog/images/smilies/neutral.png" alt=":|" />',

            // ;), ;-)
            '`(?<!\\\\);-?\)`si'
                => '<img src="'.$this->imgURL.'blog/images/smilies/wink.png" alt=";)" />',

            // :p, :P, :-p, :-P
            '`(?<!\\\\)\:-?p`si'
                => '<img src="'.$this->imgURL.'blog/images/smilies/tongue.png" alt=":P" />',

            // :d, :D, :-d, :-D
            '`(?<!\\\\)\:-?d`si'
                => '<img src="'.$this->imgURL.'blog/images/smilies/lol.png" alt=":D" />',

            // :o, :O, :-o, :-O
            '`(?<!\\\\)\:-o`si'
                => '<img src="'.$this->imgURL.'blog/images/smilies/omg.png" alt=":-O" />',

            // :cry:, :'(, :'-(
            '`(?<!\\\\)\:cry\:|\:\'-?\(`si'
                => '<img src="'.$this->imgURL.'blog/images/smilies/cry.png" alt=":cry:" />',

            // ^^, ^_^
            '`(?<!\\\\)\^(\_|\.)?\^`si'
                => '<img src="'.$this->imgURL.'blog/images/smilies/happy.png" alt="^_^" />',

            // o_O, O_o
            '`(?<!\\\\)o\_o`si'
                => '<img src="'.$this->imgURL.'blog/images/smilies/dizzy.png" alt="o_O" />',

            // :-/
            '`(?<!\\\\)\:-/`si'
                => '<img src="'.$this->imgURL.'blog/images/smilies/confused.png" alt=":-/" />',

            // ><, >_<, >.<
            '`(?<!\\\\)\&gt;(\_|\.)?\&lt;`si'
                => '<img src="'.$this->imgURL.'blog/images/smilies/caca.png" alt="o_O" />'
        );
        
        return preg_replace(array_keys($smilies), array_values($smilies), $text);
    }
}
?>
