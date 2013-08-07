# Description

The `ClaudeTag` plugin allows you to display a tag cloud in the sidebar.
It uses its own template and style sheet so you can adapt it as you want.

# Options

Here are the available options:

``` php
$fmbConf['ClaudeTag']['nbtags'] = 30; // number of tags to display
$fmbConf['ClaudeTag']['sizemin'] = 10; // size of the smallest item
$fmbConf['ClaudeTag']['sizemax'] = 30; // size of the largest item
```

# Configuration

In order to enable this plugin, you need to edit the following line in your `config.php`:

``` php
$fmbConf['plugins']['template_extend'] = array ('ClaudeTag' /*, 'other', 'plugins' */);
```
