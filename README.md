Graphite
========
Graphite is a Simple MVC web-application framework

Usage
=====
Basic Graphite invocation:
```php
graphite\core\Runtime::getInstance()->main();
```

In your HTTPD config or .htaccess
```
RewriteEngine on

##for passing request to the Controller
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^(.+) /index.php?_path=$1 [QSA,L]
```
