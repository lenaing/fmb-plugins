# Description

The `TeX` plugin adds a new `bbcode` tag in which you can write LaTeX code.
It will then convert it to an image.

# Configuration

In order to enable this plugin, you need to edit the following line in your `config.php`:

``` php
$fmbConf['plugins']['formatting'] = array ('TeX' /*, 'other', 'plugins' */);
```

**NOTE**: This plugin *should* be used after `Daddy` in order to escape LaTeX code within the `[code]` tag.
