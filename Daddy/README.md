# Description

`Daddy` is a more advanced bbcode parser using the `bbcode_parser` library available [here](http://christian-seiler.de/projekte/php/bbcode/index_en.html).

It is adapted from flatpress' [bbcode plugin](http://wiki.flatpress.org/doc:plugins:bbcode)

# Options

``` php
$fmbConf['daddy']['attach_dir'] // string: where the files are stored
$fmbConf['daddy']['images_dir'] // string: wherre the images are stored
$fmbConf['daddy']['url_maxlen'] // int: split long URLs
$fmbConf['daddy']['remap']      // string: regular expression if you'd like to remap URLS
$fmbConf['daddy']['catch']      // int: element of the previous expression to catch
$fmbConf['daddy']['use_handler']     // bool: use a php handler to download files. If images_dir and attach_dir are outside the site base dir, the handler is used.
$fmbConf['daddy']['rewrite_handler'] // string: when using the file handler, displays a "nice url". The '%f' string will be replaced by the filename
$fmbConf['daddy']['cache_images']    // bool: cache images within [img] tag. Requires $fmbConf['daddy']['use_handler'] = true
```

Default:

``` php
$fmbConf['daddy']['images_dir'] = "images/";
$fmbConf['daddy']['attach_dir'] = "files/";
$fmbConf['daddy']['url_maxlen'] = 80;
$fmbConf['daddy']['use_handler'] = false;
$fmbConf['daddy']['cache_images'] = false;
```

The remap option allows you to remap local URLs so you can use the thumb plugin.

# Examples

``` php
$fmbConf['daddy']['remap'] = "^(https?://)?(www\.)?domain.tld/?(.*)$";
$fmbConf['daddy']['catch'] = 3;
```

This will catch the 3rd element so the path of image will be local.
If I have the following:

```
[img]http://www.domain.tld/someimage.png[/img]
```

This will result in:

``` php
$fmbConf['daddy']['images_dir'].'someimage.png'
```

Note: The remapping only works for the `[img]` tag.

---

``` php
$fmbConf['daddy']['use_handler'] = true;
$fmbConf['daddy']['rewrite_handler'] = '/get-%f';
```

With the `rewrite_handler` we MUST setup a rewrite rule like this:

``` nginx
rewrite ^/get-(.+)$ /plugins/Daddy/getfile.php?f=$1;
```

With `use_handler` set to *true*, all the links prefixed with `attachs/` or prefixed with
`$fmbConf['daddy']['attach_dir']` will be passed through the handler.
Same with `images` instead of `attachs`.

---

``` php
$fmbConf['daddy']['cache_images'] = true;
```

If you use the `[img]` tag with an external link, we retrieve the image.

```
[img]http://somedomain.tld/image.png[/img]
```

will generate a mapped URL like the following:

``` php
$fmbConf['daddy']['images_dir'].'image.png'
```

# Configuration

In order to enable this plugin, you need to edit the following line in your `config.php`:

``` php
$fmbConf['plugins']['formatting'] = array ('Daddy' /*, 'other', 'plugins'*/);
```

**NOTE**: this plugin **cannot** be used before `ReadMore` and `Baby`
