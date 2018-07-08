# TriTan CMS
TriTan is a developer centric, lightweight content management system (CMS) and content management framework (CMF). It is easy to install, easy to manage, and easy to extend. The majority of TriTan's codebase is derived from WordPress, however, TriTan is not a fork of WordPress. It also should not be seen as a replacement for WordPress, Drupal, Joomla or any of the top used CMS's out there.

The main purpose of TriTan is to give developer's an option that is geared toward how they think, how they code, and how they build websites. Although you can use TriTan for your traditional CMS needs, you can also use it to build API centric applications.

TriTan is pretty stable at the moment, but it is currently in beta and should no be used in production until release 1.0.

## Screenshot
![TriTan CMS Screenshot](https://tritan-cms.s3.amazonaws.com/assets/images/TriTan-Screenshot.png)

## Notable Features
* Easier installation and deployment
* Schemaless database
* You can version control your entire site
* Easier to migrate from one server to another
* API first (read-only)
* Go headless or nearly-headless
* Caching (JSON by default when on)
* Multisite (needs testing)

## Requirements
* PHP 7.1+
    * gd graphics support
    * zip extention support
    * APC, XCache or Memcache(d) (optional)
* Apache or Nginx

## Installation
* Install [composer](//getcomposer.org/doc/00-intro.md) if not already installed
* Download the [latest release](//github.com/parkerj/TriTan-CMS/archive/master.zip)
* Extract the zip to the root or a subdirectory
* Copy config.sample.php to config.php
* Open config.php and edit the following constants and save the file: `TTCMS_MAINSITE` & `TTCMS_MAINSITE_PATH`. If you install on a dev server and then move it to a new server with a different domain, you will need to edit these for the new server.
* Run composer to install needed libraries: `composer install`
* Open your browser to `http://replace_url/login/` and login with the login credentials below:
    * username: TriTan
    * password: TriTanCMS
* Visit `http://replace_url/admin/options-general/`, and change the API Key to something unique.
* Create a cronjob on the server: `http://replace_url/cronjob/`. It should run every minute.

## Security
TriTan uses a schemaless database library and stores those files as well as some other important files on the server. Whether you are on Apache/Nginx, you must make sure to secure the following directories so that files in those directories are not downloadable:

* private/cookies/*
* private/db/*
* private/sites/{site_id}/files/cache/*

Here is an example of what should go inside of the .htaccess file to secure the above directories. Make sure to change the `key` to something unique and secure.

```
# BEGIN Privatization
# This .htaccess file ensures that other people cannot download your files.

<IfModule mod_rewrite.c>
RewriteEngine On
RewriteCond %{QUERY_STRING} !key=replace_me
RewriteRule (.*) - [F]
</IfModule>

# END Privatization
```

## Resources
* [Learn](//learn.tritancms.com/) - TriTan documentaion.
* [API Doc](//learn.tritancms.com/api/) - Documentation of classes and functions.
* [Rest API](//rest.tritancms.com/) - REST API documentation.

## Libraries
As mentioned previously, TriTan CMS is also a content management framework. You can use TriTan to build websites, API centric applications or both. Via composer, the following libraries become available to you to use.

| Library  | Description  | 
|---|---|
| [Monolog Cascade](//github.com/theorchard/monolog-cascade)  | Monolog Cascade is a Monolog extension that allows you to set up and configure multiple loggers and handlers from a single config file.  |
| [Date](//github.com/jenssegers/date)  | This date library extends Carbon with multi-language support. Methods such as format, diffForHumans, parse, createFromFormat and the new timespan, will now be translated based on your locale.  |
| [Validation](//github.com/Respect/Validation)  | The most awesome validation engine ever created for PHP.  |
| [Zebra Pagination](//github.com/stefangabos/Zebra_Pagination)  | A generic, Twitter Bootstrap compatible, pagination library that automatically generates navigation links.  |
| [Fenom](//github.com/fenom-template/fenom)  | Lightweight and fast template engine for PHP.  |
| [Foil](//github.com/FoilPHP/Foil)  | PHP template engine for native PHP templates.  |
| [Form](//github.com/adamwathan/form)  | Builds form HTML with a fluent-ish, hopefully intuitive syntax.  |
| [Mobile Detect](//github.com/serbanghita/Mobile-Detect)  | Mobile_Detect is a lightweight PHP class for detecting mobile devices (including tablets). It uses the User-Agent string combined with specific HTTP headers to detect the mobile environment.  |
| [Graphql](//github.com/webonyx/graphql-php)  | This is a PHP implementation of the GraphQL specification based on the reference implementation in JavaScript.  |
| [Promise](//github.com/reactphp/promise)  | A lightweight implementation of CommonJS Promises/A for PHP.  |
| [Hoa\Ruler](//github.com/hoaproject/Ruler)  | This library allows to manipulate a rule engine. Rules can be written by using a dedicated language, very close to SQL. Therefore, they can be written by a user and saved in a database.  |
| [Guzzle](//github.com/guzzle/guzzle)  | Guzzle is a PHP HTTP client that makes it easy to send HTTP requests and trivial to integrate with web services.  |
| [Image Optimizer](//github.com/psliwa/image-optimizer)  | This library is handy and very easy to use optimizer for image files. It uses optipng, pngquant, jpegoptim and few more libraries, so before use it you should install proper libraries on your server.  |

## Theming
There is currently no theme repository due to the nature of the project. However, you can download the [Vapor](//tritan-cms.s3.amazonaws.com/themes/Vapor.zip) theme. Use this theme as an example to build your own theme.

The Liten Framework was used in the build of TriTan CMS. So, if you are interested in adding a head to your TriTan install, you will need to learn about [routing](//www.litenframework.com/wiki/routing/) and [middlewares](//www.litenframework.com/wiki/middleware/).

## Plugins
Check out [Gitspace](https://gitspace.us/projects) for available TriTan CMS plugins.

## Todo
* ~~Finish dashboard~~
* ~~Edit user functionality on multisite users screen~~
* ~~Multisite testing (wildcard subdomains only)~~
* Unit testing
* Finish documentation (action and hook filters, etc.)

## Contributing
You are welcomed to contribute by tackling anything from the Todo list, sending pull requests, bug reports, etc.