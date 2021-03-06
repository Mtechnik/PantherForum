<?php

// Language definitions used in admin_maintenance.php
$lang_admin_maintenance = array(
   
    'Registration errors' => 'Registration errors',
    'Registration errors info' => 'The following errors need to be corrected before the account can be created:',
    'Maintenance head' => 'Forum maintenance',
    'Rebuild index subhead' => 'Rebuild search index',
    'Rebuild index info' => 'If you\'ve added, edited or removed posts manually in the database or if you\'re having problems searching, you should rebuild the search index. For best performance, you should put the forum in <a href="%s">%s</a> during rebuilding. <strong>Rebuilding the search index can take a long time and will increase server load during the rebuild process!</strong>',
    'Posts per cycle label' => 'Posts per cycle',
    'Posts per cycle help' => 'The number of posts to process per pageview. E.g. if you were to enter 300, three hundred posts would be processed and then the page would refresh. This is to prevent the script from timing out during the rebuild process.',
    'Starting post label' => 'Starting post ID',
    'Starting post help' => 'The post ID to start rebuilding at. The default value is the first available ID in the database. Normally you wouldn\'t want to change this.',
    'Empty index label' => 'Empty index',
    'Empty index help' => 'Select this if you want the search index to be emptied before rebuilding (see below).',
    'Rebuild completed info' => 'Once the process has completed, you will be redirected back to this page. If you are forced to abort the rebuild process, make a note of the last processed post ID and enter that ID+1 in "Starting post ID" when/if you want to continue ("Empty index" must not be selected).',
    'Rebuild index' => 'Rebuild index',
    'Rebuilding search index' => 'Rebuild in progesss. %s posts processed - current post ID %s. Redirecting …',
    'Posts must be integer message' => 'Posts per cycle must be a positive integer value.',
    'Days must be integer message' => 'Days to prune must be a positive integer value.',
    'No old topics message' => 'There are no topics that are %s days old. Please decrease the value of "Days old" and try again.',
    'Posts pruned redirect' => 'Posts pruned. Redirecting …',
    'Prune head' => 'Prune',
    'Prune subhead' => 'Prune old posts',
    'Days old label' => 'Days old',
    'Days old help' => 'The number of days "old" a topic must be to be pruned. E.g. if you were to enter 30, every topic that didn\'t contain a post dated less than 30 days old would be deleted.',
    'Prune sticky label' => 'Prune sticky topics',
    'Prune sticky help' => 'When enabled, sticky topics will also be pruned.',
    'Prune from label' => 'Prune from forum',
    'All forums' => 'All forums',
    'Prune from help' => 'The forum from which you want to prune posts.',
    'Prune info' => 'Use this feature with caution. <strong>Pruned posts can never be recovered.</strong> For best performance, you should put the forum in <a href="%s">%s</a> during pruning.',
    'Confirm prune subhead' => 'Confirm prune posts',
    'Confirm prune info' => 'Are you sure that you want to prune all topics older than %s days from %s (%s topics).',
    'Confirm prune warn' => 'WARNING! Pruning posts deletes them permanently.',
    'merge legend 2' => 'User to receive the merge',
    'merge legend' => 'All content by this user on the forum will be given to the other user, and then this user will be deleted.',
    'merge help' => 'This user will receive all content from the user above. After selection, there will be a confirmation page to ensure everything is correct. For best performance, you should put the forum in <a href="%s">%s</a> during merging (or at the very least stop these users from accessing the board).',
    'continue' => 'Continue to Confirmation Page',
    'user merge legend' => 'Merge user accounts',
    'confirm merge 2' => 'Confirm Merge (step 2 of 3)',
    'merge submit' => 'Merge Users',
    'no merge user from' => 'You must select a user to merge from.',
    'no merge user to' => 'You must select a user to merge to.',
    'merge users same' => 'You must select two different users.',
    'users merged redirect' => 'Users merged successfully. Redirecting …',
    
    'merge message' => 'Please do not press the submit button multiple times. This process may take a few minutes.',
    
    // Pruning & adding new users
    'Pruning complete message' => 'Pruning complete. %s users pruned.',
    'User created message' => 'User created',
    'Confirm pass' => 'Confirm password',
    'Pass info' => 'Passwords must be at least 6 characters long. Passwords are case sensitive.',
    'User prune head' => 'User prune',
    'Settings subhead' => 'Settings',
    'Prune by label' => 'Prune by',
    'Prune help' => 'Decides if the minimum number of days is calculated since the registered date or last login.',
    'Registered date' => 'Registered date',
    'Last login' => 'Last login',
    'Minimum days label' => 'Minimum days since registration/last login',
    'Minimum days help' => 'Minimum number of days before users are pruned.',
    'Maximum posts label' => 'Maximum number of posts',
    'Maximum posts help' => 'Users with more posts than this won\'t be pruned. e.g. a value of 1 will remove users with no posts.',
    'Delete admins and mods label' => 'Delete admins and mods',
    'Delete admins and mods help' => 'When enabled, any affected admins and moderators will also be pruned.',
    'User status label' => 'User status',
    'User status help' => 'Decides if (un)verified users should be deleted.',
    'Delete any' => 'Delete any',
    'Delete only verified' => 'Delete only verified',
    'Delete only unverified' => 'Delete only unverified',
    'Add user head' => 'Add user',
    'Generate random password label' => 'Generate random password',
    'Generate random password help' => 'When enabled, a random password will be generated and emailed to the above email address.',
    'Password help' => 'Specify a password if not generating a random password.'
    
);