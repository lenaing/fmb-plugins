<?php
require_once('/www/nginx-default/src/base.inc.php');
use FMB\Core\Core;

global $fmbConf;

if (isset($_GET['f']) && !preg_match('#(../|./)#', $_GET['f']) && !preg_match('#^/#', $_GET['f'])) {

    if (isset($_GET['t']) && $_GET['t'] == 'i') {
        if (isset($_GET['thumb'])) {
            $tpath = isset($fmbConf['thumb']['path']) ? $fmbConf['thumb']['path'] : FMB_PATH.'plugins/thumb/thumbs/';
        } else {
            $tpath = $fmbConf['daddy']['images_dir'];
        }
    } else {
        $tpath = $fmbConf['daddy']['attach_dir'];
    }
    if (substr($tpath, 0, 1) == '/') {
        $file = $tpath.'/'.$_GET['f'];
    } else {
        $file = FMB_PATH.'/'.$tpath.'/'.$_GET['f'];
    }

    if (file_exists($file)) {
        if (isset($_GET['t']) && $_GET['t'] == 'i') {
            $expires = 60*60*24*14;
            $last_modified_time = filemtime($file); 
            $last_modified_time = 0;
            $etag = md5_file($file);
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $type = finfo_file($finfo, $file);
            finfo_close($finfo);

            header("Last-Modified: " . 0 . " GMT");
            header("Pragma: public");
            header("Cache-Control: max-age=360000");
            header("Etag: $etag"); 
            header("Cache-Control: maxage=".$expires);
            header('Expires: ' . gmdate('D, d M Y H:i:s', time()+$expires) . ' GMT');
            header('Content-type: '.$type);
        } else {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename='.basename($file));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
        }
        header('Content-Length: ' . filesize($file));
        ob_clean();
        flush();
        readfile($file);
        exit(0);
    }
}

if (!isset($_GET['t']) || $_GET['t'] != 'i') {
    if ($_SERVER["HTTP_REFERER"]) {
        $redir = $_SERVER["HTTP_REFERER"];
    } else {
        $redir = $fmbConf['blog']['url'];
    }
    header('location: '.$redir);
} else {
    header("Status: 404 Not Found");
}

exit(0);

?>
