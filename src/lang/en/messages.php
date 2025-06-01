<?php
return [
    // Common
    'app_name' => 'FlowBreath',
    'confirm' => 'Confirm',
    'cancel' => 'Cancel',
    'save' => 'Save',
    'edit' => 'Edit',
    'delete' => 'Delete',
    'search' => 'Search',
    'back' => 'Back',
    'yes' => 'Yes',
    'no' => 'No',

    // Resources
    'resource' => [
        'list' => 'Resource List',
        'create' => 'Create Resource',
        'edit' => 'Edit Resource',
        'delete' => 'Delete Resource',
        'title' => 'Title',
        'summary' => 'Summary',
        'content' => 'Content',
        'url' => 'Original URL',
        'author' => 'Author',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
        'tags' => 'Tags',
        'visibility' => [
            'all' => 'All',
            'public' => 'Public',
            'private' => 'Private'
        ],
        'is_pinned' => 'Pinned',
        'initial_impression' => 'Initial Impression',
        'personal_connection' => 'Personal Connection',
        'reflection_insights' => 'Reflection & Insights',
        'application_ideas' => 'Application Ideas',
        'search_placeholder' => 'Search in title, content, or summary',
        'select_tags' => 'Select tags',
        'sort' => [
            'latest' => 'Latest',
            'oldest' => 'Oldest',
            'title' => 'Title'
        ],
        'filter' => [
            'all' => 'All',
            'public' => 'Public Only',
            'private' => 'Private Only'
        ],
        'messages' => [
            'create_success' => 'Resource has been created successfully.',
            'update_success' => 'Resource has been updated successfully.',
            'delete_success' => 'Resource has been deleted successfully.',
            'delete_confirm' => 'Are you sure you want to delete this resource?',
            'delete_warning' => 'This action cannot be undone.',
            'not_found' => 'Resource not found.',
            'no_results' => 'No results found. Try different search terms or filters.'
        ]
    ],

    // Tags
    'tag' => [
        'management' => 'Tag Management',
        'add' => 'Add Tag',
        'remove' => 'Remove Tag',
        'name' => 'Tag Name',
        'count' => 'Usage Count',
        'messages' => [
            'add_success' => 'Tag has been added successfully.',
            'remove_success' => 'Tag has been removed successfully.',
            'exists' => 'Tag already exists.'
        ]
    ],

    // Pagination
    'pagination' => [
        'previous' => 'Previous',
        'next' => 'Next',
        'showing' => 'Showing :from-:to of :total',
    ],

    // Errors
    'error' => [
        'title' => 'An error occurred',
        'back_to_home' => 'Back to Home',
        'unauthorized' => 'Unauthorized access.',
        'invalid_request' => 'Invalid request.',
        'required_field' => 'The :field field is required.',
        'server_error' => 'Server error occurred.'
    ],

    // Authentication
    'auth' => [
        'login' => 'Login',
        'logout' => 'Logout',
        'login_with_google' => 'Sign in with Google',
        'login_help' => 'Having trouble logging in?',
        'contact_support' => 'Contact Support',
        'registration_success' => 'Registration completed successfully.',
        'google_login_failed' => 'An error occurred during Google login.',
        'unauthorized' => 'Unauthorized access.',
        'login_required' => 'Login required to access this service.',
        'already_logged_in' => 'You are already logged in.',
        'invalid_credentials' => 'Invalid login credentials.',
        'account_not_found' => 'Account not found.',
        'account_disabled' => 'Account has been disabled.',
        'verify_email' => 'Email verification required.',
        'logout_success' => 'You have been logged out.'
    ],

    // Profile
    'profile' => [
        'title' => 'My Profile',
        'edit_title' => 'Edit Profile',
        'profile_image' => 'Profile Image',
        'name' => 'Name',
        'name_required' => 'Please enter your name.',
        'email' => 'Email',
        'google_connected' => 'Connected with Google',
        'current_password' => 'Current Password',
        'new_password' => 'New Password',
        'confirm_password' => 'Confirm Password',
        'password_hint' => 'Password must be at least 8 characters long.',
        'password_mismatch' => 'Passwords do not match.',
        'notifications' => 'Notification Settings',
        'notify_comments' => 'Receive comment notifications',
        'notify_updates' => 'Receive update notifications',
        'delete_account' => 'Delete Account',
        'delete_warning' => 'Deleting your account will permanently remove all your data.',
        'delete_confirm' => 'Are you sure you want to delete your account?',
        'delete_permanent' => 'This action cannot be undone.',
        'update_success' => 'Profile has been updated successfully.',
        'update_error' => 'An error occurred while updating your profile.',
        'wrong_password' => 'Current password is incorrect.',
        'image_upload_error' => 'An error occurred while uploading the image.',
        'image_type_error' => 'Unsupported image format.',
        'image_size_error' => 'Image size cannot exceed 2MB.',
        'login' => [
            'title' => 'Login',
            'email' => 'Email',
            'password' => 'Password',
            'remember_me' => 'Remember me',
            'submit' => 'Login',
            'register' => 'Register',
            'google_login' => 'Sign in with Google'
        ],
        'stats' => [
            'title' => 'Activity Statistics',
            'resources' => 'Resources',
            'likes' => 'Likes',
            'views' => 'Views',
            'comments' => 'Comments',
            'total_resources' => 'Total Resources',
            'public_resources' => 'Public Resources',
            'total_likes' => 'Total Likes',
            'total_views' => 'Total Views',
            'avg_views' => 'Average Views',
            'total_comments' => 'Total Comments'
        ],
        'edit' => [
            'title' => 'Edit Profile',
            'image_change' => 'Change Profile Image',
            'select_image' => 'Select Image',
            'change' => 'Change',
            'bio_placeholder' => 'Write your bio here.',
            'account_settings' => 'Account Settings',
            'completion' => 'Profile Completion'
        ]
    ],

    // Admin
    'admin' => [
        'dashboard' => 'Admin Dashboard',
        'users' => 'User Management',
        'resources' => 'Resource Management',
        'tags' => 'Tag Management',
        'settings' => 'System Settings',
        'total_users' => 'Total Users',
        'total_resources' => 'Total Resources',
        'total_tags' => 'Total Tags',
        'storage_used' => 'Storage Used',
        'new_today' => '{count} new today',
        'most_used' => 'Most used: {tag}',
        'total_files' => '{count} total files',
        'recent_users' => 'Recent Users',
        'recent_resources' => 'Recent Resources',
        'view_all' => 'View All',
        'name' => 'Name',
        'email' => 'Email',
        'joined_at' => 'Joined At',
        'actions' => 'Actions',
        'title' => 'Title',
        'author' => 'Author',
        'created_at' => 'Created At',
        'system_status' => 'System Status',
        'php_version' => 'PHP Version',
        'server_info' => 'Server Info',
        'database_size' => 'Database Size',
        'user_not_found' => 'User not found.',
        'name_required' => 'Name is required.',
        'password_too_short' => 'Password must be at least 8 characters long.',
        'user_updated' => 'User has been updated successfully.',
        'tags_merged' => 'Tags have been merged successfully.',
        'tag_deleted' => 'Tag has been deleted successfully.',
        'tag_renamed' => 'Tag has been renamed successfully.',
        'settings_updated' => 'Settings have been updated successfully.',
        'dashboard_error' => 'An error occurred while loading the dashboard.',
        'users_error' => 'An error occurred while loading the user list.',
        'resources_error' => 'An error occurred while loading the resource list.',
        'tags_error' => 'An error occurred while loading the tag list.',
        'settings_error' => 'An error occurred while loading the settings.'
    ],

    // Comments
    'comment' => [
        'write_comment' => 'Write a comment...',
        'submit' => 'Submit',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'reply' => 'Reply',
        'report' => 'Report',
        'like' => 'Like',
        'dislike' => 'Dislike',
        'edit_comment' => 'Edit Comment',
        'delete_comment' => 'Delete Comment',
        'delete_confirm' => 'Are you sure you want to delete this comment?',
        'load_more' => 'Load More Comments',
        'character_limit' => 'Maximum {count} characters',
        'report_reason' => 'Report Reason',
        'report_submit' => 'Submit Report',
        'report_success' => 'Comment reported successfully',
        'block_success' => 'Comment blocked successfully',
        'edit_success' => 'Comment updated successfully',
        'delete_success' => 'Comment deleted successfully',
        'reply_success' => 'Reply posted successfully',
        'login_required' => 'Please login to comment',
        'content_required' => 'Comment content is required',
        'too_long' => 'Comment is too long',
        'too_short' => 'Comment is too short',
        'invalid_parent' => 'Invalid parent comment',
        'max_depth' => 'Maximum reply depth reached',
        'not_found' => 'Comment not found',
        'no_permission' => 'You do not have permission to perform this action',
        'stats' => [
            'total' => 'Total Comments',
            'replies' => 'Total Replies',
            'likes' => 'Total Likes',
            'dislikes' => 'Total Dislikes',
            'reports' => 'Total Reports',
            'average_length' => 'Average Comment Length',
            'most_active' => 'Most Active Commenters',
            'recent_activity' => 'Recent Activity'
        ]
    ],

    // Navigation
    'nav' => [
        'breathing' => 'Breathing Exercise',
        'resources' => 'Resources',
        'tags' => 'Tags',
        'community' => 'Practice Diary',
        'my_info' => 'My Profile',
        'login' => 'Login',
        'register' => 'Register',
        'logout' => 'Logout'
    ],

    // Breathing Exercise
    'breathing' => [
        'title' => 'Breathing Exercise',
        'patterns' => [
            'title' => 'Breathing Patterns',
            'danjeon' => 'Danjeon Breathing',
            '478' => '4-7-8 Breathing',
            'box' => 'Box Breathing'
        ],
        'settings' => [
            'title' => 'Settings',
            'sound' => 'Sound',
            'vibration' => 'Vibration'
        ],
        'timer' => [
            'title' => 'Breathing Time (seconds)',
            'inhale' => 'Inhale',
            'exhale' => 'Exhale',
            'duration' => 'Exercise Duration (seconds)'
        ],
        'controls' => [
            'ready' => 'Ready',
            'start' => 'Start',
            'stop' => 'Stop'
        ]
    ],

    // Time
    'time' => [
        'just_now' => 'just now',
        'minutes_ago' => '%d minutes ago',
        'hours_ago' => '%d hours ago',
        'days_ago' => '%d days ago',
        'weeks_ago' => '%d weeks ago',
        'months_ago' => '%d months ago',
        'years_ago' => '%d years ago'
    ],

    'diary' => [
        'title' => 'Practice Diary',
        'create' => 'Write Diary',
        'edit' => 'Edit Diary',
        'delete' => 'Delete Diary',
        'update' => 'Update',
        'save' => 'Save',
        'content' => 'Content',
        'tags' => 'Tags',
        'tags_placeholder' => 'Separate with commas',
        'tags_help' => 'Example: practice, meditation, breathing',
        'public' => 'Public',
        'private' => 'Private',
        'no_diaries' => 'No diaries have been written yet.',
        'delete_confirm' => 'Are you sure you want to delete this diary?',
        'save_error' => 'An error occurred while saving the diary.',
        'update_error' => 'An error occurred while updating the diary.',
        'search' => 'Search',
        'search_placeholder' => 'Search by title or content',
        'date_range' => 'Date Range',
        'comment' => 'Comment',
        'comment_submit' => 'Write Comment',
        'no_comments' => 'No comments yet.',
        'comment_error' => 'An error occurred while writing the comment.',
        'comment_delete_confirm' => 'Are you sure you want to delete this comment?',
    ],

    'common' => [
        'cancel' => 'Cancel',
    ],

    'pagination' => [
        'next' => 'Next',
        'previous' => 'Previous',
    ],
]; 