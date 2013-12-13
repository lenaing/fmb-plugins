# Description

`ReadMore` is a plugin that allows you to *split* long posts on the homepage.

# Example

For example, if you write this in a post:

```
Hello World!

[more]

Here is a post.
```

You will only see `Hello World!` on the homepage and you will have to click the `Read more...`
link in order to see the rest.

# Requirements

In order to properly use this plugin, you will have to alter the database.

Here is the SQL query to execute:

``` sql
update fmb_blog_posts set post_body = post_body||E'\r\n'||'[more]'||E'\r\n'||post_more;
```

Then you will have to use an alternate template that handle this plugin.

# Configuration

To enable this plugin, edit the following line in your `config.php`:

``` php
$fmbConf['plugins']['formatting'] = array ('ReadMore' /*, 'other', 'plugins' */);
```

**NOTE**: This plugin should be the first one in the array to improve performances.
