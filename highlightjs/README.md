# Description

`Highlightjs` allows you to enable syntax highlighting using [highlight.js](https://github.com/isagalaev/highlight.js).
Your code must be surrounded by the `<pre><code>` tags.

By default `highlighjs` tries to autodetext the laguage, but you can force it
using the *"class"* attribute of the `<code>` tag.

Example:

``` html
<pre><code class="C">
#include <stdio.h>

int main (int argc, char **argv) {
    fprintf (stdout, "Hello World!\n");
    return 0;
}
</code></pre>
```

# Options

There are multiple themes available, take a look at the *highlight.js/styles*
directory.
To use a specific theme, you can set the following option in your `config.php` file:

``` php
$fmbConf['highlightjs']['theme'] = 'github';
```

# Configuration

In order to enable this plugin, you need to edit the following line in your `config.php`:

``` php
$fmbConf['plugins']['template_extend'] = array ('Highlightjs' /*, 'other', 'plugins' */);
```
