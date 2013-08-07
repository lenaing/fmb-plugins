# Description

`APCache` is a plugin used to cache *computed* values using `PHP APC`.
It is smart enough to cache SQL results and will be able to clean the cached
data every time you update your database without flushing all the cached data.

# Configuration

In order to enable this plugin, you need to edit the following line in your `config.php`:

``` php
$fmbConf['plugins']['caching'] = array ('APCache');
```
