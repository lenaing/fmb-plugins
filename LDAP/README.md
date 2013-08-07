# Description

`LDAP` is a `login` plugin that allows you to authenticate against a LDAP
server.
It requires ldap support enabled in you php installation (see [this page](http://www.php.net/manual/en/ldap.installation.php)
for more details)

# Options

The following options can be set in your `config.php` file:

``` php
// this option is optional, default is 'ldap://127.0.0.1'
$fmbConf['ldap']['host'] = 'ldaps://ldap.master.example.org'
// this option is mendatory. '%u' is replaced by the given username in the login page
$fmbConf['ldap']['connect_string'] = 'uid=%u,ou=People,dc=example,dc=org'
```
