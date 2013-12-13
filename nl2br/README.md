# Description

The `nl2br` plugin converts every remaining `\n` characters in `<br />` except those between `<pre>` tag.

# Configuration

In order to enable this plugin, you should edit the following line in your `config.php`:

``` php
$fmbConf['plugins']['formatting'] = array ('nl2br' /*, 'other', 'plugins' */);
```

**NOTE**: This plugin should be the last one.
