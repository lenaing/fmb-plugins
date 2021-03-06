# Description

This plugin allows you to use your webserver's rewrite rules in order to hace "nice" URLs.

# Example

In order to properly use this plugin, you need a compatible template and to 
setup a few rewrite rules.

Here are the rewrite rules needed by Nginx:

``` nginx

        rewrite ^(.*)/post-(\d+)(-.*)\.html$          $1/index.php?page=post&id=$2;
        rewrite ^(.*)/tag-(\d+)(-.*)\.html$           $1/index.php?page=posts&tag=$2;
        rewrite ^(.*)/archives\.html$                 $1/index.php?page=archives;
        rewrite "^(.*)/date-(\d{4})-(\d{1,2})\.html$" $1/index.php?page=posts&y=$2&m=$3;
        rewrite ^(.*)/cat-(\d+)(-.*)\.html$           $1/index.php?page=posts&cat=$2;
        rewrite ^(.*)/login$                          $1/login.php?from=blog;
        rewrite ^(.*)/logout$                         $1/login.php?from=blog&action=logout;
        rewrite ^(.*)/subscribe$                      $1/subscribe.php?from=blog;
        rewrite ^(.*)/unsubscribe$                    $1/subscribe.php?from=blog&action=unsubscribe;
```

# Configuration

In order to enable this plugin, you need to edit the following line in your `config.php`:

``` php
$fmbConf['plugins']['template_extend'] = array ('NiceURL' /*, 'other', 'plugins' */);
```
