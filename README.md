## About
Panther is a fast, yet very secure forum application written in PHP and MySQL. Panther has a large variety of features that are very well optimized, we use intensive caching to speed up your forum and deliver the best possible speed, quality and security for all your users.

## License
Panther is an open source forum application released under the [GNU General Public License (GPL-3.0), Version 3, 29 June 2007](http://opensource.org/licenses/GPL-3.0). It is free to download and use and will remain so. 

## Database Transactions
Panther uses database transactions which allow changes on the server to not take place until the full script has finished. This means that in the event of a database error, Panther will automatically roll back the changes made to avoid half finished scripts being entered into the database. Due to transaction usage, Panther loads much quicker and is more efficient at querying the database.

## Caching
Panther uses advanced caching mechanisms to automatically cache all vital parts of your forum. This saves crucial database query time and allows for much more intensive usage of features, which do not sacrifice performance. This allows for the best possible speed for you and your users.

## PDO (PHP Data Objects)
Panther uses the benefits of PDO prepared statements to keep your forum 100% secured from SQL Injection at all times. Most other forum software do not utilize this feature and instead simply "escape" user data before placing it straight into the query. Escaping data is not as reliable as prepared statements, and leaves open the potential for SQL Injection. Once more, some applications are still vulnerable (particularly those running <= MySQL 5.5.11) when using PDO due to the use of "emulated" prepared statements. Emulated statements are when PHP will send the data and query to MySQL, but the MySQL server will simply escape all the data and then insert it into the query. This can become a security risk, especially if the MySQL server is running a charset such as the default (usually Latin 1). With Panther, we connect using utf-8 and disable emulated prepared statements to circumvent such security vulnerabilities. Security remains the #1 priority of your forum. Once more, you can only be SQL Vulnerable when you code a special, not standard way of querying the database.

## Developer Friendly
Our code is developer friendly, meaning you can very easily create your own modifications for Panther and add them in to Panther with a single file copied into the addons/ folder. Our plugin system allows you to never modify the core, which is always considered a bad idea in content management systems. Simply drop a file into the plugins/ folder, and access it through administration.
We use a custom-made, database driver to allow you to reap the rewards of PDO with having to over-complicate things for yourself. We use custom methods such as select, update and delete to allow you the ability to quickly access common functions for a database. If no method is there, you can also use the run method. Gone are the days of using try/catch blocks to catch exceptions, you simply need to use the available method, keeping an array of data separate from the query.

## Ease of Administration
Our system allows you to very easily and efficiently administrate your forum, without even needing to touch a single line of code. Moreover, when it comes to updating your software, we have a built in automatic updater which will guide your forum through the process. Don't want automatic updates? That's fine. These can be disabled or enabled at your will. We allow administrators easy access to stopping spammers, moderating forums and assigning new user groups.

## Security
Panther uses an sha-512 hash, along with 16 characters of salt for each user password in the database. This means that in the event your database was compromised, attackers would have to spent an awful lot of time attempting to crack your hashes. Each password hash is 128 characters in length. We use login keys for a one time login per user. These are re-generated on every logout, and each user has a unique key for a one time use only. This means that unlike other forum softwares, such as FluxBB or PunBB, who simply put the hashes user password into the cookie, it is much harder, if not impossible, to guess the cookie value of a Panther user. Each login key is 30 characters in length.
We use a system of tokens to prevent cross site request forgery attacks against your forum. Cross site request forgery attacks are when hackers can exploit unauthorized requests to your forum, allowing them to impersonate other users, potentially changing forum submission details and launching payments.
Panther uses a token system to prevent this exact thing from happening. And part of the token is made up of the unique login key for that session on the forum. Forum passwords are hashed using sha-512 and another 16 characters of salt. To prevent users from guessing forum passwords, cookies are also given salt, an impressive 64 characters of salt, unique to their cookie.
We use advanced brute-force protection to stop people from randomly "guessing" one of your users passwords, which can be toggled on or off at your will. Set the amount of login attempts per username as well as the total for all users as you use your very own login queue to prevent brute force attacks on your forum.

## Spammers
Spammers are troublesome users, who repeatedly enter nonsensical messages or links to websites. It's important to understand that spam is a very difficult thing to combat on certain applications and forums more than others. However, with Panther, we have already thought this out. Since most spammers are bots, we have already gone ahead and denied all bots access to your register page, and your login and posting pages. This means that no bot can register, login or post on your forum already blocking the vast majority of spam.
We also use configurable robot tests to allow only users who are really interested in what your forum has to offer to answer them. These can be configured on a group basis, and that's not all. We allow you to moderate all posts made in forums, or on a user-group basis to prevent spam from really getting through. Any combination of these is enough to truly bore human spammers making their entire goal redundant. Still not enough? You can download some addons to help you combat spam even more, including Google's reCAPTCHA. We also include StopForumSpam integration which will allow you to report the troublesome users to Stop Forum Spam directly through your forum interface. Dealing with Spam has never been so easy!

## CDN Support
Panther has CDN support built-in to help even more to produce the latest loading times for your forum. Compress images using the TingPNG compression tool in order to reduce server bandwidth and space, optimizing your website even more. Choose which files you want on your own content delivery network and which you want on the same domain as your forum.

## SEO Friendly URLs
Panther uses a total of five different URL schemes allowing you to choose the URL scheme of your forum. The default, file based, file based fancy, folder based and folder based fancy. You choose how you want search engines to perceive your website and rank it. Already use different URL schemes? No problem. Both the default URL scheme and any other chosen one will be accessible by bots, so you will not get penalized for the change. Moreover, we use the "canonical" tag to tell the Google bot how you want your page to be indexed. This means that even if they access it through the default scheme, you will still get indexed using the URL scheme you want.

## Modifications
Modifications are a difficult thing to achieve, because quite often they require you edit the core of the software. With Panther, we have introduced an extensive plugin and addon system, allowing you to simply copy files into the appropriate locations. You don't have to edit files by hand, making the process easy and reliable. Moreover, you can simply upload the plugins and addons straight through your forum interface, which makes things even easier. The time for logging in to your website control panel has now gone; the time for letting Panther do it for you has come. Don't want a previously installed add-on anymore? That's fine! Just select the addon you don't want, and delete it from the Administration Control Panel.

## Requirements
- A web server such as Apache, Nginx, Lighttpd etc.
- PHP 5.2.5 or later.
- A database server such as MySQL 5.0.15 or later.

## Recommendations
- Make use of a PHP accelerator. We recommend [Zend Opcache](https://pecl.php.net/package/ZendOpcache) + [Alternative PHP Cache (APC)](https://pecl.php.net/package/APC). It is a very good combination.

## Links
 - Homepage: https://www.pantherforum.org/
 - Forums: https://www.pantherforum.org/forums/
 - Documentation: https://www.pantherforum.org/docs/
 - Development: https://gitlab.com/PantherForum/Panther
 - Contributor: http://LetsCode.Online <Mtechnik: mtechniklol@gmail.com>
 
 ## GETTING STARTED!
 - To get started, be sure to start with Composer. Once you have downloaded the repository, you will then, need to first, install composer from https://getcomposer.org
 - Note: Two ways you can do this, is by pressing `Win Button + R`, or goto the selected folder where you want to download the source files too, after, you will `HOLD SHIFT + Right-Click` and select <b>Open Command Window Here</b>
 - Last step, you will then type in the Command Window: 
 
    `composer create-project mtechnik/panther . --stability=dev`

 - Enjoy!
