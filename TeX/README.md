# Description

The `TeX` plugin adds a new `bbcode` tag in which you can write LaTeX code.
It will then convert it to an image.

# Example

Here is a TeX example:

``` latex
\[ f(n) = \left\{
  \begin{array}{l l}
    n/2 & \quad \text{if $n$ is even}\\
    -(n+1)/2 & \quad \text{if $n$ is odd}
  \end{array} \right.\]
```

Resulting in the following image:

![LaTeX formula](http://ziirish.info/plugins/TeX/pictures/e269cd892241a4f3d0b2a8a3109dca4c_1375339961.png)

# Configuration

In order to enable this plugin, you need to edit the following line in your `config.php`:

``` php
$fmbConf['plugins']['formatting'] = array ('TeX' /*, 'other', 'plugins' */);
```

**NOTE**: This plugin *should* be used after `Daddy` in order to escape LaTeX code within the `[code]` tag.
