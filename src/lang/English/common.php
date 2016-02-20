<?php

// Language definitions for frequently used strings
$lang_common = array(
    
    // Text orientation and encoding
    'lang_direction' => 'ltr', // ltr (Left-To-Right) or rtl (Right-To-Left)
    'lang_identifier' => 'en',
    
    // Number formatting
    'lang_decimal_point' => '.',
    'lang_thousands_sep' => ',',
    
    // Notices
    'Bad request' => 'Bad request. The link you followed is incorrect or outdated.',
    'Not found' => 'The requested URL /%s  was not found on this server.',
    'No view' => 'You do not have permission to view these forums.',
    'No permission' => 'You do not have permission to access this page.',
    'Bad referrer' => 'Incorrect cross site request forgery token sent. You were referred to this page from an unauthorised source. If the issue persists please ensure that you are navigating around the forum by using the correct URL.',
    'No cookie' => 'You appear to have logged in successfully, however a cookie has not been set. Please check your settings and if applicable, enable cookies for this website.',

    // Miscellaneous
    'Announcement' => 'Announcement',
    'Avatar' => 'Avatar',
    'Options' => 'Options',
    'Submit' => 'Submit', // "Name" of submit buttons
    'Ban message' => 'You have been banned.',
    'Ban message 2' => 'The ban expires at the end of',
    'Ban message 3' => 'The administrator or moderator that banned you left the following message:',
    'Ban message 4' => 'Please direct any inquiries to the administrator at',
    'Never' => 'Never',
    'posting_ban' => 'You have been restricted from posting until: %s',
    'Unable to add spam data' => 'The post was unable to be reported to Stop Forum Spam. Refresh this page to re-try.',
    'Today' => 'Today',
    'Yesterday' => 'Yesterday',
    'Info' => 'Info', // A common table header
    'Go back' => 'Go back',
    'Maintenance' => 'Maintenance',
    'Redirecting' => 'Redirecting',
    'Click redirect' => 'Click here if you do not want to wait any longer (or if your browser does not automatically forward you)',
    'on' => 'on', // As in "BBCode is on"
    'off' => 'off',
    'Invalid email' => 'The email address you entered is invalid.',
    'Required' => '(Required)',
    'required field' => 'is a required field in this form.', // For javascript form validation
    'Last post' => 'Last post',
    'by' => 'by', // As in last post by some user
    'By' => 'By',
    'In' => 'In', // As in in some topic
    'New posts' => 'New posts', // The link that leads to the first new post
    'New posts info' => 'Go to the first new post in this topic.', // The popup text for new posts links
    'Username' => 'Username',
    'Password' => 'Password',
    'Email' => 'Email',
    'Send email' => 'Send email',
    'Moderated by' => 'Moderated by',
    'Registered' => 'Registered',
    'Subject' => 'Subject',
    'Message' => 'Message',
    'Topic' => 'Topic',
    'Started by' => 'Started by',
    'Forum' => 'Forum',
    'Posts' => 'Posts',
    'Replies' => 'Replies',
    'Pages' => 'Pages:',
    'Page' => 'Page %s',
    'BBCode' => 'BBCode:', // You probably shouldn't change this
    'Spoiler' => 'Spoiler:',
    'url tag' => '[url] tag:',
    'img tag' => '[img] tag:',
    'Smilies' => 'Smilies:',
    'and' => 'and',
    'Image link' => 'image', // This is displayed (i.e. <image>) instead of images when "Show images" is disabled in the profile
    'wrote' => 'said:', // For [quote]'s
    'Mailer' => '%s Mailer', // As in "MyForums Mailer" in the signature of outgoing emails
    'Important information' => 'Important information',
    'Write message legend' => 'Write your message and submit',
    'Previous' => 'Previous',
    'Next' => 'Next',
    'Spacer' => '…', // Ellipsis for paginate
    
    // Title
    'Title' => 'Title',
    'Member' => 'Member', // Default title
    'Moderator' => 'Moderator',
    'Administrator' => 'Administrator',
    'Banned' => 'Banned',
    'Guest' => 'Guest',
    
    // Stuff for include/parser.php
    'BBCode error no opening tag' => '[/%1$s] was found without a matching [%1$s]',
    'BBCode error invalid nesting' => '[%1$s] was opened within [%2$s], this is not allowed',
    'BBCode error invalid self-nesting' => '[%s] was opened within itself, this is not allowed',
    'BBCode error no closing tag' => '[%1$s] was found without a matching [/%1$s]',
    'BBCode error empty attribute' => '[%s] tag had an empty attribute section',
    'BBCode error tag not allowed' => 'You are not allowed to use [%s] tags',
    'BBCode error tag url not allowed' => 'You are not allowed to post links',
    'BBCode list size error' => 'Your list was too long to parse, please make it smaller!',
    
    // Stuff for the navigator (top of every page)
    'Index' => 'Index',
    'User list' => 'User list',
    'Moderating Team' => 'The Moderating Team',
    'Online' => 'Users Online',
    'Rules' => 'Rules',
    'Search' => 'Search',
    'Register' => 'Register',
    'Login' => 'Login',
    'Not logged in' => 'You are not logged in.',
    'Profile' => 'Profile',
    'Logout' => 'Logout',
    'Logged in as' => 'Logged in as',
    'Admin' => 'Administration',
    'Last visit' => 'Last visit: %s',
    'Topic searches' => 'Topics:',
    'New posts header' => 'New',
    'Active topics' => 'Active',
    'Unanswered topics' => 'Unanswered',
    'Posted topics' => 'Posted',
    'Show new posts' => 'Find topics with new posts since your last visit.',
    'Show active topics' => 'Find topics with recent posts.',
    'Show unanswered topics' => 'Find topics with no replies.',
    'Show posted topics' => 'Find topics you have posted to.',
    'Mark all as read' => 'Mark all topics as read',
    'Mark forum read' => 'Mark this forum as read',
    'Title separator' => ' | ',
    
    // Forum password stuff
    'password legend' => 'To view, post or use the other features of this forum, a password must be entered',
    'password information' => 'If you have forgotten the password for this forum, please contact the board administrator.',
    'incorrect password' => 'The forum password you have entered is not valid.',
    'forum password' => 'Forum Password',
    'Protected forum' => 'Protected Forum',
    
    // Stuff for the page footer
    'Board footer' => 'Board footer',
    'Jump to' => 'Jump to',
    'Go' => ' Go ', // Submit button in forum jump
    'Moderate topic' => 'Moderate topic',
    'All' => 'All',
    'Move topic' => 'Move topic',
    'Open topic' => 'Open topic',
    'Close topic' => 'Close topic',
    'Unstick topic' => 'Unstick topic',
    'Stick topic' => 'Stick topic',
    'Moderate forum' => 'Moderate forum',
    'Powered by' => 'Powered by <a href="https://www.pantherforum.org/">Panther</a>',
    'Warning links' => 'Warning links',
    
    // Debug information
    'Debug table' => 'Debug information',
    'Querytime' => 'Generated in %1$s seconds, %2$s queries executed',
    'Memory usage' => 'Memory usage: %1$s',
    'Peak usage' => '(Peak: %1$s)',
    'Query times' => 'Time (s)',
    'Query' => 'Query',
    'Total query time' => 'Total query time: %s s',
    
    // For extern.php RSS feed
    'RSS description' => 'The most recent topics at %s.',
    'RSS description topic' => 'The most recent posts in %s.',
    'RSS reply' => 'Re: ', // The topic subject will be appended to this string (to signify a reply)
    'RSS active topics feed' => 'RSS active topics feed',
    'Atom active topics feed' => 'Atom active topics feed',
    'RSS forum feed' => 'RSS forum feed',
    'Atom forum feed' => 'Atom forum feed',
    'RSS topic feed' => 'RSS topic feed',
    'Atom topic feed' => 'Atom topic feed',
    
    // Admin related stuff in the header
    'New reports' => 'There are new reports',
    'Maintenance mode enabled' => 'Maintenance mode is enabled!',
    'New unapproved posts' => 'There are new unapproved posts',
    'New PM' => 'You have a new private message',
    
    // Units for file sizes
    'Size unit B' => '%s B',
    'Size unit KiB' => '%s KiB',
    'Size unit MiB' => '%s MiB',
    'Size unit GiB' => '%s GiB',
    'Size unit TiB' => '%s TiB',
    'Size unit PiB' => '%s PiB',
    'Size unit EiB' => '%s EiB',
    
    // Misc
    'multi_moderate topic' => 'More Moderation Actions',
    'Topics' => 'Topics',
    'Link to' => 'Link to:', // As in "Link to: http://pantherforum.org/"
    
    'Days' => 'Days',
    'Months' => 'Months',
    'Years' => 'Years',
    'Unarchive topic' => 'Unarchive topic',
    'Archive topic' => 'Archive topic',
    
    // Private messaging
    'PM' => 'Private Messaging',
    'PM amount' => 'Private Messaging (%s)',
    
    'Robot title' => 'Verification question',
    'Robot info' => 'Refreshing this page will show a different question.',
    'Robot test fail' => 'You have not answered the verification question correctly. Please try again.',
    
	// Updating
	'Hosting environment does not support Panther x' => 'This hosting environment does not support Panther %s because it requires at least MySQL version %s and PHP version %s. Your MySQL version is %s and your PHP version is %s.',
	'Invalid update patch' => 'An invalid update patch was downloaded. Please try again. If the issue persists, you can manually download it from <a href="https://www.pantherforum.org/downloads.php">the Panther website</a>.',
	'Unable to open archive' => 'Opening the downloaded zip archive %s failed.',
	
	'No file' => 'You did not select a file for upload.',
    'Move failed' => 'The server was unable to save the uploaded file.',
    'Unknown failure' => 'An unknown error occurred. Please try again.',
    'Too large ini' => 'The selected file was too large to upload. The server didn\'t allow the upload.',
    'Partial upload' => 'The selected file was only partially uploaded. Please try again.',
    'No tmp directory' => 'PHP was unable to save the uploaded file to a temporary location.',
	
	'Announcement' => 'Announcement',
	'Sticky' => 'Sticky',
	'Popular' => 'Popular',
	'Moved' => 'Moved',
	'Closed' => 'Closed',
	'Redirect' => 'Redirect',
	'No new posts' => 'No new posts',
	'New posts' => 'New posts/topics',
	'New sticky posts' => 'New sticky posts',
	'Panther dashboard' => 'Panther dashboard',
	'Collapse menu' => 'Collapse the menu',
);