<?php

// Language definitions used in admin_restrictions.php
$lang_admin_restrictions = array(
    
    //ADD/EDIT/DELETE: Stage 1
    'restrictions head' => 'Admin Restrictions',
    'restriction information' => 'Add New Restriction',
    'add new' => 'Add',
    'no other admins' => '- No Administrators -',
    'restrictions head 3' => 'Admin Restrictions',
    'restriction information 3' => 'Delete Existing Restriction',
    'delete' => 'Delete',
    'restrictions head 2' => 'Admin Restrictions',
    'restriction information 2' => 'Edit Existing Restriction',
    'edit' => 'Edit',
    'back' => 'Go Back',
    'delete label' => 'WARNING! You are about to permanently delete the restrictions for this administrator. Are you sure?',
    'no admins available' => 'There are currently no other administrators to issue restrictions to. Please promote another member to the administrator group and try again.',
    
    
    //ADD/EDIT: Stage 2
    'user not admin' => 'This user is not currently part of the administrator group.',
    'restrictions for user x' => 'Restrictions for user "%s"',
    'admin restrictions' => 'By using this page, you can configure restrictions for your chosen administrator, they will only have access to the pages you allow below. Note that the moderator control panel is not affected by these, and they will still have global moderation privileges on the forum.',
    'board config' => 'Forum Configuration',
    'change config label' => 'Allow this user to alter the board\'s main configuration. ',
    'board perms' => 'Posting Permissions',
    'change perms label' => 'Allow this user to alter the board\'s posting permissions. ',
    'board cats' => 'Forum Categories',
    'change cats label' => 'Allow this user to alter the board\'s categories. ',
    'board forums' => 'Board Forums',
    'change forums label' => 'Allow this user to alter the board\'s forums.',
    'board archive' => 'Topic Archiving',
    'change archive label' => 'Allow this user to alter the board\'s archiving rules for topics. Topics can only be unarchived by Administrators',
    'board smilies' => 'Emoticon Management',
    'change smilies label' => 'Allow this user to alter the board\'s emoticons. This is only applicable is emoticons are actually enabled.',
    'board warnings' => 'Warning Configuration',
    'change warnings label' => 'Allow this user to alter the board\'s warning types and warnings levels. This is only applicable if warnings are enabled.',
    'board groups' => 'User groups',
    'change groups label' => 'Allow this user to alter the board\'s user groups configuration.',
    'board users' => 'Alter User\'s User Group',
    'change users label' => 'Allow this user to alter the user group of other users (including your own) and delete user profiles. This only applies to the user group and they will not be stopped from editing the rest of a user\'s profile.',
    'board censoring' => 'Forum Censoring',
    'change censoring label' => 'Allow this user to alter the board\'s word censoring. If set to no, then they will have no access to the page at all, not even to view it.',
    'board moderate' => 'Multi-moderation actions',
    'change moderate label' => 'Allow this user to alter the board\'s multi-moderation actions. If set to no, then they will have no access to the page at all, not even to view it.',
    'board ranks' => 'User Ranks',
    'change ranks label' => 'Allow this user to alter the board\'s user ranks. If set to no, then they will have no access to the page at all, not even to view it.',
    'board maintenance' => 'Maintenance Features',
    'change maintenance label' => 'Allow this user to use the board\'s maintenance whilst in maintenance mode. If set to no, then they will still be able to access the remainder of the board whilst in maintenance mode.',
    'board plugins' => 'Allow use of Plugins',
    'change plugins label' => 'Allow this user to alter the configuration of the board\'s plugins. When disallowed access, it is to the full page.',
    'board restrictions' => 'Alter Admin Restrictions',
    'change restrictions label' => 'Allow this user to alter the admin restrictions. <strong style="color:red">WARNING!</strong> Enabling this option will allow them to change their own restrictions.',
    'board updates' => 'Install Updates',
    'install updates label' => 'Allow this user to install updates to the forum software. Updates are downloaded automatically from the Panther website if enabled.',
    'board attachments' => 'Search Attachments',
    'change attachments label' => 'Allow this user to alter the board\'s attachments. Note that this only applies to the attachment manage in administration and they will not be prohibited from editing or deleting attachments on the forum.',
    'board robots' => 'Robot validation',
    'change robots label' => 'Allow this user to alter the board\'s robot validation questions.',
    'board addons' => 'Forum Addons',
    'change addons label' => 'Allow this user to alter forum addons. <strong style="color:red">WARNING!</strong> Allowing additional administrators to upload addons is an extreme security threat to your forum. Addons are executed on the server like normal PHP files. Only upload addons you are certain came from the Panther website. We strongly recommend you disallow this.',
    'board tasks' => 'Background Tasks',
    'change tasks label' => 'Allow this user to alter background tasks for the forum. <strong style="color:red">WARNING!</strong> Allowing this will allow additional administrators to upload tasks, which can be a security threat to your forum. Tasks are executed on the server like normal PHP files.',
    
    //ADD: Stage 3
    'no user' => 'This user is not a valid board administrator.',
    'already restrictions' => 'This user already has restrictions set up. Instead you should edit those.',
    'added redirect' => 'Restrictions Imposed. Redirecting …',
    
    //EDIT: Stage 3
    'no restrictions' => 'This user already has no restrictions set up. You should create some instead.',
    'edited redirect' => 'Restrictions Edited. Redirecting …',
    
    //EDIT: Stage
    'removed redirect' => 'Restrictions Removed. Redirecting …'
    
);