# Description

`Meuhcache` is a plugin used to cache "computed" values using Memcache.
It is smart enough to cache SQL results and will be able to clean the cached
data every time you update your database without flushing all the cached data
(if you share the memcache instance with multiple applications for example).

# Options

You can specify the memcache address and port using the following options:

``` php
$fmbConf['meuhcache']['server'] // default: "localhost"
$fmbConf['meuhcache']['port']   // default: 11211
```

# Configuration

In order to enable this plugin, you need to edit the following line in your `config.php`:

``` php
$fmbConf['plugins']['caching'] = array ('Meuhcache');
```
