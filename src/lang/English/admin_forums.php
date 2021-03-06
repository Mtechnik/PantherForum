<?php

// Language definitions used in admin_forums.php
$lang_admin_forums = array(
    
    'Forum added redirect' => 'Forum added. Redirecting …',
    'Forum deleted redirect' => 'Forum deleted. Redirecting …',
    'Forums updated redirect' => 'Forums updated. Redirecting …',
    'Forum updated redirect' => 'Forum updated. Redirecting …',
    'Perms reverted redirect' => 'Permissions reverted to defaults. Redirecting …',
    'Must enter name message' => 'You must enter a forum name.',
    'Must be integer message' => 'Position must be a positive integer value.',
    'New forum' => 'New forum',
    
    // Entry page
    'Add forum head' => 'Add forum',
    'Create new subhead' => 'Create a new forum',
    'Add forum label' => 'Add forum to category',
    'Add forum help' => 'Select the category to which you wish to add a new forum.',
    'Add forum' => 'Add forum',
    'No categories exist' => 'No categories exist',
    'Edit forums head' => 'Edit forums',
    'Category subhead' => 'Category:',
    'Forum label' => 'Forum',
    'Edit link' => 'Edit',
    'Delete link' => 'Delete',
    'Position label' => 'Position',
    'Update positions' => 'Update positions',
    'Confirm delete head' => 'Confirm delete forum',
    'Confirm delete subhead' => 'Important! Read before deleting',
    'Confirm delete info' => 'Are you sure that you want to delete the forum <strong>%s</strong>?',
    'Confirm delete warn' => 'WARNING! Deleting a forum will delete all posts (if any) in that forum!',
    
    // Detailed edit page
    'Edit forum head' => 'Edit forum',
    'Edit details subhead' => 'Edit forum details',
    'Forum name label' => 'Forum name',
    'Forum description label' => 'Description (HTML)',
    'Category label' => 'Category',
    'Sort by label' => 'Sort topics by',
    'Last post' => 'Last post',
    'Topic start' => 'Topic start',
    'Subject' => 'Subject',
    'Redirect label' => 'Redirect URL',
    'Redirect help' => 'Only available in empty forums',
    'force approve' => 'Manually approve all posts',
    'force approve help' => 'Force all posts, topics or topics and posts to be manually approved by a moderator before appearing on this forum. This setting is honoured for every user group except moderators for that forum, global moderators and administrators. It cannot be overridden by specific forum settings.',
    'force approve topics' => 'Approve topics only',
    'force approve posts' => 'Approve posts only',
    'force approve both' => 'Approve topics and posts',
    'no force approve' => 'Approve nothing',
    'Quickjump label' => 'Enable in Quickjump menu',
    'Quickjump help' => 'This will enable this forum in the Quickjump menu (jump to forum) drop list, if the Quickjump menu is enabled in administration.',
    'Group permissions subhead' => 'Edit group permissions for this forum',
    'Group permissions info' => 'In this form, you can set the forum specific permissions for the different user groups. If you haven\'t made any changes to this forum\'s group permissions, what you see below is the default based on settings in <a href="%s">%s</a>. Administrators always have full permissions and are thus excluded. Permission settings that differ from the default permissions for the user group are marked red. The "Read forum" permission checkbox will be disabled if the group in question lacks the "Read board" permission. For redirect forums, only the "Read forum" permission is editable.',
    'Read forum label' => 'Read forum',
    'Post replies label' => 'Post replies',
    'Post topics label' => 'Post topics',
    'Post polls label' => 'Post polls',
    'Upload label' => 'Upload',
    'Download label' => 'Download',
    'Delete label' => 'Delete Attachments',
    'Revert to default' => 'Revert to default',
    'Allow reputation' => 'Allow reputation',
    'Allow reputation help' => 'Allow the use of reputation in this forum. If set to no, then not even administrators will have access to reputation in this forum',
    'Parent forum' => 'Parent forum',
    'No parent forum' => 'No parent forum',
    'Open forum' => 'Closed forum',
    'Open forum help' => 'If enabled, users will only be able to view the topics which they have posted. Moderators for that forum, global moderators and administrators can still view all topics. Note that this applies to every other user group and cannot be overridden by specific user group settings.',
    'Increment post count' => 'Increment post count',
    'Increment posts help' => 'Increment the amount of posts which a user has when they post in this forum. This applies to both topics and posts.',
    
    'forum password label' => 'Forum Password',
    'forum password label 2' => 'Confirm Forum Password',
    'forum password help' => 'This will force all users who use this forum to enter the specified password before allowing them to proceed, however cannot be used on redirect forums. Leave blank to use no password.',
    'forum password change help' => 'Note: To prevent the forum password being displayed, a randomly generated password will be put in the password fields. It will only be updated or removed if the box to the left is checked.',
    'forum password help 2' => 'Only use this field if you are changing or adding a password to the current forum.',
    'passwords do not match' => 'The entered forum passwords do not match.',
    'protected forum label' => 'Show post info',
    'protected forum help' => 'Show the last poster of the forum on the index page. This is useful when a forum password is set or a user group can only read their own topics in a forum.'
    
);