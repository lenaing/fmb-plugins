# Description

The `Markdown` plugin adds a new syntax style using [php-markdown](https://github.com/michelf/php-markdown).

# Example

You can now write your posts in Markdown.

For example, this code:

``` markdown
# A title

1. an
2. ordered
3. list

~~~~ .c
/* some C code */
#include <stdio.h>

int main (int argc, char **argv) {
	fprintf (stdout, "Hello World!\n");

	return 0;
}
~ (4 times)

Some *emphased* text, and some **bold** text.
```

Will result in the following:

---

# A title

1. an
2. ordered
3. list

``` c
/* some C code */
#include <stdio.h>

int main (int argc, char **argv) {
	fprintf (stdout, "Hello World!\n");

	return 0;
}
```

Some *emphased* text, and some **bold** text.

---

# Configuration

In order to enable this plugin, you should edit the following line in your `config.php`:

``` php
$fmbConf['plugins']['formatting'] = array ('Markdown' /*, 'other', 'plugins' */);
```

**NOTE**: This plugin *should* be used just before `nl2br`.
