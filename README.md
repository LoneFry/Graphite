Graphite
========
Graphite is a Simple MVC web-application framework

Components
----------
- /^/ : Core files, includes base classes for Models, Controllers, a Dispatcher, and a View helper
- /^HTML5/ : HTML5 default site, contains HTML5/JS/CSS for basic functionality
- /^CLI/ : A Simple Command Line Interface, other components can expose commands to this

It is possible to build an application on Graphite using only the core files (in path /^/).
The other components are optional, existing as generic implementations of common functionality,
such as user/role administration.

Installation
------------
1. Copy the core directory (`/^`), `index.php`, and any of the optional components into the root of your webspace.
2. Copy the `.htaccess` file into the root of your webspace, or its contents into your apache config (in an appropriate `<directory>` section).
3. Restart apache.
4. Visit the domain in your browser and the installer action should come up by default.

Created By
----------
LoneFry
dev at lonefry.com

License
-------
CC BY-NC-SA
Creative Commons Attribution-NonCommercial-ShareAlike
http://creativecommons.org/licenses/by-nc-sa/3.0/
