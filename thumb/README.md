# Description

This plugin generates thumbnails of local images.

The `Daddy` plugin will call it if you add particular atributes to the [img] tag.

# Examples

```
[img="images/mypicture.jpg" scale=50]
```

Then the image will be reduced by 50%.

```
[img="images/mypicture.jpg" width=500]
```

The image will be resized using 500px as width, the ratio will be kept.

Same with `height` instead of `width`.
You can use the both together but the ration won't be kept.

# Options

Alternatively, you can `autoresize` all the images using the following option:

``` php
$fmbConf['thumb']['autoreduce']
```

Example:

``` php
$fmbConf['thumb']['autoreduce'] = 500;
```

All the images will be resized using 500px as width (unless the image isn't
bigger than 500px)


Here are the available options:

``` php
$fmbConf['thumb']['autoreduce']  // auto resize large images
$fmbConf['thumb']['mode']        // mode of the generated images
$fmbConf['thumb']['path']        // where to store the thumbnails
```

Defaults:

``` php
$fmbConf['thumb']['mode'] = 0640;
$fmbConf['thumb']['path'] = "<plugin_path>/thumbs";
```

# Configuration

In order to enable ths plugin, you need to edit the following line in your `config.php`:

``` php
$fmbConf['plugins']['template_extend'] = array ('thumb' /*, 'other', 'plugins' */);
```
