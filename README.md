## $this=> "ReadMe.md"
**PantherForums**, is a fast, yet very secure forum application written in PHP and MySQL. Panther has a large variety of features that are very well optimized, we use intensive caching to speed up your forum and deliver the best possible speed, quality and security for all your users.

## License
Panther is an open source forum application released under the [GNU General Public License (GPL-3.0), Version 3, 29 June 2007](http://opensource.org/licenses/GPL-3.0). It is free to download and use and will remain so. 

## CDN Support
Panther has CDN support built-in to help even more to produce the latest loading times for your forum. Compress images using the TingPNG compression tool in order to reduce server bandwidth and space, optimizing your website even more. Choose which files you want on your own content delivery network and which you want on the same domain as your forum.

## Modifications
Modifications are a difficult thing to achieve, because quite often they require you edit the core of the software. With Panther, we have introduced an extensive plugin and addon system, allowing you to simply copy files into the appropriate locations. You don't have to edit files by hand, making the process easy and reliable. Moreover, you can simply upload the plugins and addons straight through your forum interface, which makes things even easier. The time for logging in to your website control panel has now gone; the time for letting Panther do it for you has come. Don't want a previously installed add-on anymore? That's fine! Just select the addon you don't want, and delete it from the Administration Control Panel.

## Requirements
- A web server such as Apache, Nginx, Lighttpd etc.
- PHP 5.2.5 or later.
- A database server such as MySQL 5.0.15 or later.

## Recommendations
- Make use of a PHP accelerator. We recommend [Zend Opcache](https://pecl.php.net/package/ZendOpcache) + [Alternative PHP Cache (APC)](https://pecl.php.net/package/APC). It is a very good combination.

## Links
 - Homepage: "https://www.pantherforum.org"
 - Forums: "https://www.pantherforum.org/forums/"
 - Documentation: "https://www.pantherforum.org/docs/"
 - Development: "https://gitlab.com/PantherForum/Panther"
 - Contributor: "http://LetsCode.Online" <Mtechnik: mtechniklol@gmail.com>
 
 ## GETTING STARTED!
 - To get started, be sure to start with Composer. Once you have downloaded the repository, you will then, need to first, install composer
 - Next open your Command screen by pressing Win Button + R and type in: "cmd"
 - Note: Two ways you can do this, is by pressing Win Button + R, or goto the selected folder where you want to download the source files too, after, you will "HOLD SHIFT + Right-Click" and select "Open Command Window Here"
 - Last step, you will then type in the Command Window: 
 - 
    ```
    composer create-project Mtechnik/PantherForum . --stability=stable
    ```
 - Enjoy!
