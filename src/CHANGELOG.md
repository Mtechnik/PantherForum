## 1.1.3 -
- Getting enviornment ready for developers to work locally

## 1.1.2 - 

- Fixed problem with number of topics and posts on the index page not being displayed properly with forum_number_format
- Removed turning off magic quotes as the global setting for it was deprecated in PHP 5.3 and removed in PHP 5.4
- Fixed capitalisation of "Pantherone" folder which prevented the Administration section from using a stylesheet
- Fixed spelling of "Organise" on the Administration index
- Fixed problem with attachment permissions being exactly the same regardless of the global attachments setting
- Optimised checking for existing attachments when editing a post by removing duplicate $data array
- Fixed a few undefined variables on various pages when the setting 'users online' is disabled
- Added run() method to replace __construct() in Tasks
- Added constant to turn off all bans in case an Administrator accidently bans an account or IP that prevents them from accessing the board
- Added GetText to replace old obsolete language system
- Removed several obsolete global variables from various functions
- Optimised usage of a csrf token in profile.php for using gravatar
- Fixed number of warning points appearing as null in the "Essentials" section of a user profile if no warnings have been issued to that user
- Fixed csrf token problem when unsubscribing to topics on the forum - the token was not generated correctly
- Fixed hard-coded language strings for "online" and "offline" when viewing a topic
- Fixed problem with previewing poll options - the number of total inputs would appear even if the number of options entered is less
- Removed obsolete function "forum_list_langs" - this is now handled by the languge class which is more efficient and updated for GetText
- Fixed problem with deleting posts - if the previous post is unapproved or deleted, then it will redirect to a bad request message instead
- Fixed a problem with sending personal messages that only users who were both restricted from using it on a user-group basis and had disabled it in their profile would be prevented from receiving messages
- Added specific language string to indicate no warning types are configured rather than generic bad request message for warnings.php when no warning types or levels have been configured
- Fixed broken link 'Details' in warnings system when viewing recent warnings
- Fixed incorrect CSS for showing the changelog notes by default in admin_updates.tpl
- Changed function phpversion() to constant PHP_VERSION in admin/statistics.php for better optimisation
- Fixed PHP version not showing on admin/statistics.php
- Changed strlen() to panther_strlen() for validation in admin/moderate.php - we want the number of characters, not bytes
- Optimised language strings in admin_restrictions.po
- Altered language strings in admin_moderate.po
- Changed several occurences in several files of strlen() to panther_strlen()
- Fixed problem with changing default URL scheme not changing the URL scheme automatically when redirecting which could cause a 404 error
- Fixed minor XSS vulnerability introduced by Twig, when deleting a forum
- Fixed spelling of "load_template" (load_templates) in groups.php that caused a fatal error if a group was being deleted and it had members
- Fixed various errors in move_group.tpl
- Changed domain name to "example.org" in admin_options.po for the base URL help option
- Fixed bug that did not allow guests to download attachments if no row was present in the forum_perms table explicity setting the download setting to 1
- Renamed function colourize_groups to colourise_groups
- Fixed problem with default theme switching to brown for the first time the options are updated after installing
- Added support for MyISAM as well as InnoDB
- Added new installer
- Fixed incorrect topic title being indexed when installing the forum (%s instead of the Panther version being installed)
- Fixed errors with parser for PHP 5.3
- Fixed problem with parser that prevented nested lists
- Changed error handler to a class to remove another four global variables

## 1.1.1 - 17-12-2015

- Fixed incorrect index in index.tpl (Quy)
- Fixed no question showing for robot tests (Quy)
- Fixed HTML markup on the index when displaying the groups legend (Quy)
- Fixed rel attribute in topic.tpl when displaing user website field (Quy)
- Fixed undefined variable in pms_send.php when user has email notifications enabled
- Fixed undefined variable in edit.php when a user does not have the ability to edit/delete attachments
- Fixed unauthorised access to protected forums via the "search" feature if all forums were searched
- Removed two variables placed directly into the LIMIT() clause when getting topics in viewforum.php
- Fixed error in quickjump generation that would allow for forums to be repeated multiple times the higher the group ID, if all quickjump generation was done at the same time, e.g. saving a forum
- Fixed undefined index with SMTP if no port was provided
- Fixed incorrect breadcrumbs when searching for unanswered, new, posted or active posts vis the links in the header (Quy)
- Fixed pagination problems when searching - if a non-default URL scheme was selected, pagination would not work (Quy)
- Removed obsolete language string in reputation and optimised reputation language
- Fixed missing sprintf value when having a reputation interval (Quy)
- Fixed feeds showing topics from global forums when a forum ID is specified, if a non-default URL scheme is used (Quy)
- Fixed array parameter from str_replace call on any URL rewritten scheme
- Fixed slash in emails for shortcut URLs (inside messages)
- Fixed problem with incorrect shortcut URLs being sent in subscription emails
- Fixed problem with the CSS only being loaded in certain circumstances for the server error template
- Fixed problem with $panther_config always being reset, thus always showing the error despite debug mode, when a server error was encountered
- Fixed possiblity of recursive server error if $panther_config is not set and an error was encountered during an Ajax error or by setting the style path
- Fixed spelling of "smilies" in admin/options.php (Quy)
- Removed "raw" filter on edit reason that allowed an XSS attack from a trusted user on the forum
- Fixed problem when previewing posts where the hide smilies checkbox would be checked even when the user had unchecked it before previewing the post
- Fixed function "clear_feed_cache" from invalidating all cache files, and replaced PHP 4 while loops with much more efficient scandir(). It now only invalidates feed caches
- Fixed the "id" of the navigation items for PM and The Moderating Team - they were the same as the ones for Profile and Userlist.
- Fixed undefined index (poster_ip) when reporting posts to Stop Forum Spam
- Parser now changed to a class to remove 26 global variables and better optimise the code
- Fixed closing transaction in help.php
- Fixed bug where topic review would not be displayed
- Fixed cache quickjump generation where forums would be repeated twice
- Strengthened CSRF check when logging out 
- Fixed server error viewing a PM when no CDN is used and a group image is uploaded
- Fixed incorrect getimagesize call in vewtopic.php resulting in an supressed server error if a group image is uploaded and no CDN is used
- Fixed forum password not being displayed in inputs viewing editing a forum, if a forum password had been added. The password was still updated or added.
- Optimised extern.php
- Fixed undefined variable (cur_post) when subscribing to a password-protected forum
- Fixed extern.php showing topics in protected forums (Quy)
- Fixed extern.php showing topics in password-protected forums (Quy)
- Fixed allowing users to subscribe (and be notified) to topics in protected forums
- Fixed allowing users to report posts in protected forums in topics which they could not view
- Added csrf tokens for subscribing/unsubscribing to to forums or topics
- Fixed duplicate language string in admin/forums.php when editing a forum
- Added missing lang_common to the template for moving topics (Moderate Forum -> Move)
- Fixed permissions with attachments that allowed users without the download permission to see and download attachments regardless
- Added global editor option to enable/disable the WYSIWYG editor
- Fixed incorrectly placed raw filter in template admin_options.tpl for a language string
- Fixed "poll" not showing when searching forums
- Fix incorrect information for bbcode being off instead of on when sending or replying to a PM
- Fixed XSS vulnerability in admin/reports.php due to incorrectly placed "raw" filter
- Removed <em> tags in admin/groups.php that appeared in text
- Fixed XSS with usernames introduced by Twig
- Optimised reputation JavaScript, adding a csrf check and removing obsolete, deprecated onclick event
- Fixed admin-added warnings from not displaying to choose from when issuing a warning
- Fixed number of points not being shown for automatic bans when displaying all warnings
- Fixed missing "Name" for guests when posting in quickpost mode
- Enhanced and improved style
- Added csrf tokens to subscription emails and promote user link
- Fixed being unable to remove reputation due to non-ending transaction
- Added Google Breadcrumbs

## 1.1.0 - 11-11-2015

- Prevented pagination being displayed in online.php when only one page is available
- Added Twig templating
- Made errors inline with form when issuing a new warning and improved error checking
- Fixed bug returning NULL for database query when issuing a warning to a user, if no previous warnings had been issued
- Improved error checking when sending an email from the email form, and made error messages inline
- Improved error checking when sending a report from the report form, and made error messages inline
- Improved way in which "The moderating team" page is generated and does not require a database qery to get the forum information
- Changed static link to dynamically generated in edit.php when adding a poll
- Altered validation of CSRF tokens in profile.php to be more efficient
- Fixed duplicate 'form_sent' and 'csrf_token'inputs in profile.php for the section 'privacy'
- Fixed undefined variable 'user' when editing/adding a posting ban and te user was an admin or a moderator
- Used message() instead of exit() for a better display when users are unauthorised with HTTP authentication
- Improved error checking in post.php
- Fixed undefined index 'poster_id' in announcement.php if the user viewing the announcement does not have permission to view the user's email address directly and instead must use form email
- Fixed undefined variable when deleting a poll from a topic
- Fixed static link for redirecting after deleting a private message post
- Fixed 404 error when quoting PM post due to the modifier being incorrect
- Added AJAX detection when saving admin notes in the admin dashboard. If no ajax request is detected, access to saving is denied
- Prevented potential problem caused by multiple language conflicts when setting archive rules, based on the value saved being language-dependant
- Fixed double end tag for select element in admin/archive.php
- Fixed undefined variable in admin/updates.php
- Fixed undefined index with the updater and corrected a potential error if the server does not support the class 'ZipArchive'
- Improved database connection by merging variables in config.php into a single array
- Improved form checking in admin/permissions.php by ensuring that the $_POST['form'] value is an array before using array_map() (left over from FluxBB)
- Fixed CRSF token generation in admin/attachments.php
- Added token check when deleting orphan attachments (through admin panel)
- Removed obsolete language string 'Go back' in admin restrictions when deleting a restriction
- Fixed token issue in admin/users.php when attempting to ban multiple users at once
- Fixed issue with deleting the same forum's posts and topics in a category if more than one forum is present in a category and is deleted from admin/categories.php
- Removed double loop in admin/categories.php when displaying categories
- Fixed hard-coded language string in admin/tasks.php
- Improved error checking in admin/robots.php
- Fixed undefined index when deleting a post and reporting it to StopForumSpam
- Prevented using explode() multiple times in a loop when editing an announcement
- Fixed 404 error when deleting announcement using any URL scheme other than default
- Enhanced admin/moderate.php by merging the sections to add and edit moderation actions
- Removed obsolete option when generating moderation action, to 'add reply'
- Added token checks in admin/moderate.php
- Fixed hard-coded redirect link in admin/moderate.php when redirecting from editing an action
- Fixed tabindex when editing or adding admin restrictions
- Changed javascript redirect when rebuilding search index into redirect() function
- Fixed potential issue in help.php where the topic referenced may not exist
- Changed static links in help.php to dynamically generate ones based on URL scheme
- Changed static content in help.php to dynamically generated based on the current forum
- Removed un-needed loop in admin/groups.php
- Fixed two missing templates - one if there are no categories for admin/categories.php and one for admin/groups.php when deleting a group
- Fixed issue with "#" appearing after the end of the previous link of pagination
- Removed function "forum_unregister_globals" and dropped PHP 5.2 support
- Added header to help page in an attempt to improve navigation on it
- Function 'panther_htmlspecialchars' has been deprecated
- Fixed static link in include/reputation.php when alerting reputation abuse
- Increased security check of downloaded patch updates
- Removed old feed compatiblity in extern.php (from FluxBB)
- Moved functions authenticate_user, panther_htmlspecialchars_decode and escape_cdata into extern.php which is the only place they are used
- Fixed issue with not displaying new posts properly on index page if sub forums exist and the sub forums have posts while the standard forum does not
- Added 'theme' support (different colour schemes per style)
- Fixed bug of adding new members to personal conversations where if anothe ruser present in the conversation had uppercase  letters in their username they would be removed
- Fixed "&amp;" intefering with the default URL scheme
- Fixed error when deleting an unverified user(s) from the admin panel
- Removed obsolete language strings about updating in the common language file
- Removed function fix_request_uri and placed the code in the rewrite file, the only location where used
- Dropped support for old system of addons and moved to new system of XML extensions
- Fixed old FluxBB profile messaging fields being displayed in admin/users.php
- Renamed admin extensions language file to 'admin_extensions.php'
- Fixed timing attack vulnerability with the forum
- Fixed issue in personal conversations where if a user had already deleted their copy of the conversation and another user attempted to edit the first post the user who deleted their copy of the conversation would still get their PM number decremented knocking the PM counter out
- Fixed issue with PANTHER_ACTIVE_PAGE constant being defined as the index page in all PM pages apart from viewing a personal conversation
- Fixed javascript error in admin/archive.php

## 1.0.9 - 18-09-15
- Fixed a few template issues.
- Fixed bug when promoting user from unverified group.
- Fixed template bug when editing private message.
- Fixed a bug in the WYSIWYG editor where no CSS file for it is loaded.
- Doubled length of login keys to 60 unique characters.
- Removed redundant function "curl_get_contents" from 1.0.5 Beta.
- Optimised URL schemes.
- Fixed PHP errors if a database error occurs when installing.
- Fixed minor XSS Vulnerability when displaying the server administrator email if a database error occured when installing ($_SERVER['SERVER_ADMIN']).
- Fixed 404 error when deleting user (if any URL scheme is used other then default).
- Fixed undefined variable when issuing warning.
- Fixed PHP errors when using the error_handler function during installation.
- Added task scheduler.
- Fixed message showing topics have been archived if archiving is not enabled and the administrator of a forum updates the archive rules.
- Fixed bug where the first available forum name would be used when redirecting after moving a topic.
- Fixed bug where a forum administrator is unable to access their control panel if the update cache is not set, and their forum was unable to generate a new one.
- Added database optimisation task.
- Added update task.
- Removed obsolete global variable from include/cache.php.
- Fixed issues where the file "include/cache.php" could be included twice, causing a fatal error.
- Fixed undefined variable when viewing a forum which contains a sub forum, and no moderators are present.
- Fixed undefined variable when uploading an attachment and checking the attachment mime.
- Fixed an undefined offset error is the amount of icons and amount of extensions (for attachments) specified are not the same amount.
- Fixed issue where if a user requested a new password, the password hash would be truncated down to 80 characters from 128.
- Fixed issue if a GIF file was upload (i.e. as an avatar) and the request was sent to TinyPNG, not compressed, returned nothing and triggered a blank error handler call.
- Prevented administrators having to manually edit files to enable PHP debugging.
- Fixed "attachment_tpl" being undefined when editing posts and users cannot upload or edit attachments.
- Fixed array to string conversion if multiple language packs are used.
- Fixed user-specified language pack not being chosen due to incorrect post values.
- Prevented cache error when caching smilies if no smilies are present in the database.
- Corrected typo in poll langauge file (cyberman).
- Fixed database error when deleting reputation due to incorrect table being updated - "reputation" instead of "posts".
- Fixed bug where posts/topics are always permanently deleted in moderate.php when opting to delete them.
- Renamed function "get_link" to "panther_link" to avoid confliction with WordPress.
- Added missing footer to email template "rep_abuse.tpl".
- Added support for persistent connections.
- Added ability to add/remove users from private message conversations.
- Fixed bug where adding a new reply to a private message checks whether the last user specified blocked who sent the message.
- Removed PHP 4 directory iteration support in common_admin.php and replaced with much more efficient function 'scandir'
- Removed function get_microtime and replaced with built-in PHP 5 function
- Excluded new lines when parsing a username in quote tags
- Removed $db->close due to PDO automatically closing connections when the script ends and slightly re-factored the __construct code for it

## 1.0.8 - 29-07-15
- Fixed change email bug in profile.php.
- Fixed multi moderation undefined subject index.
- Fixed caching issue for APC.
- Allowed for suppressed errors (using @) in the new error handler function.
- Fixed undefined index when viewing online (if a user was viewing a topic).
- Added ability to batch archive or unarchive topics (administrators only).
- Removed double slash for displaying smilies in the editor.
- Fixed array to string conversion error when updating the forums which are moderated.
- Fixed issue being unable to have a timelimit with usergroups for editing and deleting posts.
- Fix overwriting variable names when applying multi moderation action.
- Fix query error for applying multi moderation action.
- Added extensive templating system.
- Fixed issue with being unable to use custom styles due to incorrect style directory used.
- Removed double "strong" tag from HTML if an edit had taken place and a staff member added a reason.
- Fixed undefined index "style" if $panther_user was not set and a database error was present.
- Fixed generate_avatar_markup error when viewing a profile.
- Fixed undefined index when sending a message (if the message receiver chose to receive PM notifications).
- Fixed undefined index in announcement.php.
- Prevented PHP version from being exposed in header ('X-Powered-By') if it has not been disabled on the server.
- Fixed an XSS Vulnerability in profile.php ("steam" messaging input).
- Fixed post deletion bug with "num_replies" being decremeneted incorrectly.
- Fixed post deletion bug where if the previous post was deleted or unapproved you would be redirected to a non-existng post.
- Fixed issue when enabling/disabling Gravatar if the URL scheme is "folder_based".
- Removed obsolete onclick event for updating the content of a signature (if the editor is used) and replaced with dynamically generated jQuery.
- Fixed bug showing delete button for users in the original administrator group.
- Fixed database error when viewing attachments and selecting certain options.
- Removed tripe foreach() loop in admin_groups.php which can be condensed into one (left over from FluxBB).
- Fixed bug in admin_posts.php when displaying a username in file_based_fancy or folder_based_fancy URL scheme.
- Fixed bug with the value of "id" in the admin restrictions table not being incremented.
- Added CSRF token checks in admin_smilies.php.
- Fixed undefined offset in admin_smilies.php.
- Fixed array checking and assigning in admin_smilies.php.
- Fixed security when deleting emoticons by checking if the deleted file is an image.
- Removed obsolete language strings from admin_smilies.php.
- Added strings "edit" and "delete" into language file when viewing an announcement, and have the ability to edit or delete it.
- Fixed undefined index error if no addons are present and a user clicks "delete selected" in admin_addons.php.
- Fixed 404 link for the topic in breadcrumbs when reporting a post.
- Added csrf token check in admin_warnings.php when deleting a warning type. A token was already present in the forum but was not validated.
- Removed "<panther_favicon>" from HTML when redirecting.
- Added several additional csrf token checks around the forum to prvent exploits.
- Removed "forum_hmac" function, which had support for PHP 4.
- Fixed RSS feed always being dhown on the index regardless if atom is chosen using "folder based fancy", "folder based", "file based fancy" or "file based" URL schemes.
- Removed obsolete onlick attribute to update editor contents when editing a private message.
- Fixed an XSS vulnerability in pms_misc.php when deleting post messages, if the user has permission to delete messages.
- Due to the addition of templates, a display bug has been fixed in "viewforum.php" which moved the breadcrumbs down the page when a forum has a sub forum.
- Fixed several issues in warnings.php.
- Fixed hard-coded links in poll_misc.php, and made them dynamically generated.
- Added basic plugin manager.
- Added additional check when uploading addon to ensure that it doesn't already exist.
- Fixed bug in help.php if smilies are on a CDN.
- Fixed bug which showed the full path of the smilies in the JavaScript array "url" in admin/index.php.
- Fixed bug in multi-moderation when no move-to-forum was selected, the forum ID was updated to "0" in the database.

## 1.0.7 - 15-06-2015
- Added canonical tag in important pages.
- Fixed bugs with IIS6 & IIS7 web servers not setting REQUEST URIs.
- Changed layout of HTML syntax in the head of forum pages.
- Added ability to detect ajax requests and if an exception occurs, no HTML will be alerted to the user.
- Ensured cURL is enabled before using TinyPNG compression tool.
- Fixed issue with blank multi moderation email being sent.
- Fixed issue with checking incorrect smiley path is writable in admin_index.php.
- Removed "panther_favicon" from HTML.
- Fixed issue with uploading avatars on a non-default URL scheme.
- Fixed issue with uploading default favicon & avatar on non-default URL scheme.
- Fixed incorrect URL detection for uploading avatar in URL scheme "file based fancy".
- Removed hard-coded link for uploading group images in admin_groups.php.
- Fixed being unable to upload group images in admin_groups.
- Fixed fatal errors uploading smilies due to non-default URL scheme.
- Fixed undefined index of "Delete" in admin_attachments which meant no language was on the delete button for deleting an attachment.
- Fixed undefined redirect index when deleting an attachment in admin_attachments.php.
- Fixed issue with the editor not showing smilies if a CDN is used, or a custom URL is specified for smilies.
- Fixed issue with uploading avatars (bad request errors on "file based" or "folder based" URL schemes).
- Fixed being unable to upload group images.
- Removed obsolete onclick event for updating the content of a textarea (if the editor is used) and replaced with dynamically generated jQuery.
- Fixed bug preventing warning status being updated in admin_options.
- Fixed parse error in parser.php when posting a message if broken bbcode tags couldn't be fixed.
- Prevented page timing out or blank message due to update check failure.
- Prevented undefined index on registration page if user removed username and password fields.
- Added error handling system to prevent PHP errors being displayed in full.
- Fixed bug being unable to alter style directory in admin_options.
- Changed error reporting to E_PARSE (parse errors are shown before the script attempts to compile, thus unavoidable).
- Fixed fogotten password feature and added csrf token check.
- Fix styling issue when in maintenance mode.
- Changed htmlspecialchars to panther_htmlspecialchars.
- Added favicon to redirects and maintenance messages.
- Fixed issue with the number of replies in a topic not being decremented when deleting posts.
- Changed variable "pun_config" to "panther_config" in delete.php (left over from FluxBB).
- Added type="image/x-icon" to favicon tag.
- Fixed prev and next tag in viewforum/topic.php.
- Fixed bug with multiple pages in HTML for forums and topics.
- Fixed group colour bug for sub forums.
- Added pure CSS lightbox with the ":target" selector.

## 1.0.6 - 03-06-2015
- Fixed no warning langauge file being loaded when a non admin user views a user's profile.
- Fixed issue with user being logged out if a URL scheme is present other than the default.
- Fixed multiple issues with all URL schemes, including not being able to use plugins.
- Added the last topic on the index page, and on sub forums.
- Added addons Manager and updates admin restrictions for addons manager. Important: If you've already issued restrictions, after the update you will have to delete them and re-add them otherwise the new column for the addon manager will not take effect.
- Added spoiler tag.
- Added a new hook in header.php for addons.
- Added a hook in include/common.php for addons.
- Fixed multi moderation wiping current subject if subject is changed by it
- Fixed admin restrictions crashes and database errors when deleting or editing restrictions.
- Changed FORUM_VERSION constant in install.php to 1.0.6 so Panther doesn't detect updates the forum already has.

## 1.0.5 - 29-05-2015
- Fixed issue with new and active posts.
- Fixed "404 Not Found" when viewing the IP address of a user in "folder based fancy" URL mode.
- Fixed issue with removing reputation.
- Fixed error saying you had already given reputation if a user had with a higher ID than you.
- Added hook into index.php at the end of the page.
- Fixed issue with not being able to update a user's group ID if they had to verify their registration.
- Fixed no CSRF token being passed when selecting a multi moderation action.
- Fixed no username being sent in the registration email with profile URL (if any "fancy" URL schemes were used.
- Fixed issue with being unable to give reputation if reputation.js is on a CDN, or outside of the default location.
- Fixed several other "404 Not Found" errors in the "folder based fancy" URL scheme.

## 1.0.4 - 27-05-2015
- Fixed issue with avatar displaying a different size in online.php.
- Fixed issue with FluxBB constant irrelevant to Panther.
- Fixed issue with reputation not counting correctly when giving negative rep.
- Fixed issue with reputation not selecting entered rep correctly (when giving negative rep).
- Added hard-coded "404 Not Found" not found text (in include/rewrite.php) to lang/*/common.php in a language file.
- Removed unneeded query when logging in.
- Fixed potential undefined index error when logging in that prevents a cookie from being set.
- Fixed potential issue where users could see forum names they don't have permission to view when viewing the moderating team (if a standard forum moderator is assigned to one of them).
- Removed duplicate (and unneeded) database query for each standard forum moderator when viewing "the moderating team" page to gather the total number of forums - and improved page code formatting.
- Fixed issue with cache forum permissions never being written to file.
- Fixed issue that when no moderators or global moderators existed on the board the legends were still shown for them on "the moderating team" page.
- Fixed table formatting issue in online.php.
- Fixed unnecessary prefix duplication issues in viewforum.php if users online are enabled.
- Removed pointless copying of variable in post.php (left over from FluxBB).
- Fixed potential password salt conflict issue when activating user accounts, changing your email or changing your password.
- Changed database driver to much more secure PDO prepared statements, and changed while() loops to much more efficient foreach() loops when collecting data from the database queries.
- Altered alert message which appears when a user has given reputation too quickly and made it shorter.
- Removed old search action compatibility left over from FluxBB.
- Fixed potential "404 Not Found" issue when viewing new posts in a forum due to incorrect parameter - "forum" was passed instead of "fid".
- Removed code from update_users_online function that would never run due to it being duplicate code from earlier forum function.
- Added system of one time use login keys to prevent potential password stealing from the value in the cookie, then possible to crack the password hash of the user (either by malware on a user's pc or by the user attempting to change the password value, thus making brute force protection pointless).
- Fixed issue with guest reputation always being shown (and processed even though it would always be zero).
- Fixed issue with transactions in MySQL due to the engine type being MyISAM.
- Added boolean index "is_admin" to $panther_user to avoid having to check against the constant PANTHER_ADMIN for every admin check.
- Changed default database engine to InnoDB to avoid transaction errors in MySQL (and giving other benefits of using InnoDB).
- Removed double foreach loop in moderate.php.
- Prevented potential changing of values in HTML form of multi moderation which could bring up an error (due to the multi moderation action not existing).
- Ensured that the topic ID given for a multi moderation action is valid before attempting to go further and that it has been approved. Also removed the redundant preg_match() which is slow.
- Fixed bug which multi moderation would still be shown if no actions were available.
- Merged code for sticking/unsticking topics to avoid duplicated code in moderate.php (left over from FluxBB).
- Changed hard-coded links in admin_restrictions.php to reflect the chosen admin URL scheme.
- Fixed CSS display issue in admin_restricitons.php when attempting to edit/add/delete restrictions for a non administrator.
- Added csrf token check in admin_restrictions.php.
- Fixed header() redirect in admin_maintenance.php that could crash if a big forum database is present.
- Added section for merging user accounts in admin_maintenance.php and related email templates.
- Fixed hard-coded "save notes" button and added to a language file.
- Changed trim() to panther_trim() and corrected potential issue of array error in admin_forums.php (if for example the user changed the input information on the HTML form). This was left over from FluxBB.
- Fixed bad display issue with emoticons: roll.png, neutral.png, sad.png, smile.png.
- Fixed blank page in profile.php when updating a user's group membership even though the database was still updated.
- Fixed hard coded redirect links in admin_users.php.
- Fixed "404 Not Found" issue on admin_users.php when using file based URL scheme.
- Added topic archiving system to the forum.
- Fixed undefined offset error when an administrator (other than the original one) is in administration.
- Fixed multi moderation not displaying as the current page.
- Fixed issue with FluxBB versions required for installing PANTHER_ADMIN.
- Added alert in admin_index.php for unwritable avatars directory on the forum.
- Fixed database error when deleting a topic or post.
- Added ability to permanently delete or restore posts/topics.
- Stopped users from viewing admin restrictions page if no other administrators are present on the board.
- Dropped PHP 4 support.
- Dropped PostgreSQL and SQLite support because the effects of PDO on them are not certain and MySQL is the largest used driver, meaning it can be fully supported, faster, less cluttered and more efficient.
- Added support for adding new files and replacing old files when updating the forum through the auto updater.
- Added inline CSS in admin_updates.php to the style files.
- Fixed issue with uploading avatar - bad referrer message was given due to no csrf token being sent in the form.
- Removed duplicate call to url_friendly() function when subscribing or unsubscribing to a topic.
- Added ability to password protect forums.
- Fixed a large number of issues with different URL schemes and "404 Not Found" errors.
- Added ability to unapprove posts.
- Fixed issue with multi moderation, deleted posts and unapproving posts where they would still appear as part of the number of topics in the forum (on the index page).
- Added several new emoticons, such as cry, angry, sleep, angel, mad, xd, what and happy cry.
- Fixed issue with moderators being able to view moderator control panel even if they weren't allowed to (though no links were shown).
- Added private messaging system into the forum.
- Fixed issue with choosing a different URL scheme from the default and visiting the board index page by "index.php".
- Fixed updating the online list twice if a different URL scheme is chosen from the default.
- Added static variable to function get_link and remove pointless global variables from function.
- Fixed "404 Not Found" link to Panther forum in admin_index.php.
- Fixed a number of URL scheme issues due to no csrf token being passed when moderating topics.
- Added user tags to posts.
- Removed redundant language file update.php from FluxBB for db_update.php.
- Added user group images.
- Added option for users to be notified when banned (admin option only).
- Added ability for multiple administrator groups.
- Added version checking when upgrading Panther.
- Restructured the admin restrictions table (and renamed it to simply "restrictions").
- Added ability for administrators to add robot validation tests.
- Added ability for user groups to only view theri own topic on a forum.
- Added announcements to forums.
- Added CDN support.

## 1.0.3 - 22-04-2015
- Fixed issue in admin_index.php which meant that the downloaded update would never display (added after previous update).
- Fixed error after removing posts from moderate.php.
- Fixed issue with install file alert existing always returning true, even if file didn't exist.
- Added user avatar next to username in online.php.
- Fixed spelling error of "administrators" in lang/English/admin_options.php for the maximum attachment legend.
- Fixed issue with global moderators appearing in the standard moderator heading in misc.php?action=leaders.

## 1.0.2 - 22-04-2015
- Fixed multi moderation SQL Syntax (only applicable after using previous update).
- Fixed issue with updating versions and file not being found from auto-updater.
- Fixed potential issue with non existing file dbu_update.php (left over from FluxBB).
- Updated emoticons to be more efficient.
- Fixed issue with even after updating the forum the updates would appear again.
- Fixed undefined variable in admin_updates.php.
- Fixed issue with cache being re-created before the new updates could be cached (meaning Panther would automatically detect the same version to update to again potentially resulting in errors).
- Now updates forum version to "1.0.2 Beta" after nothing was updated prior.
- Fixed PHP syntax error in delete.php.

## 1.0.1 - 22-04-2015
- Fixed link hard coded into post.php.
- Fixed undefined index in admin_index.php.
- Fixed issue with only allowing php files to be replaced in admin_update.php.
- Fixed link hard coded into moderate.php.
- Fixed issue with sending email to yourself when applying a moderation action (if you were the original poster).
- Fixed multi moderation issue with unapproved posts influencing the count of posts in topic.
- Fixed multi moderation issue with topi post influencing the count of posts in topic.
- Fixed "404 Not Found" when viewing a profile which has a numbers in a username (applies to file based fancy url scheme only).
- Fixed "404 Not Found" when attempting to add multi moderation action (any url scheme other than default).
- Fixed issue with install.php - no copy of the file was present.

## 1.0.0 - 15-04-2015
- An update to FluxBB 1.5.8, including many improvements and changes to the software.