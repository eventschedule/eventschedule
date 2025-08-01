<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Default source repository type
    |--------------------------------------------------------------------------
    |
    | The default source repository type you want to pull your updates from.
    |
    */

    'default' => env('SELF_UPDATER_SOURCE', 'github'),

    /*
    |--------------------------------------------------------------------------
    | Version installed
    |--------------------------------------------------------------------------
    |
    | Set this to the version of your software installed on your system.
    |
    */

    'version_installed' => env('SELF_UPDATER_VERSION_INSTALLED', 'v1.0.30'),

    /*
    |--------------------------------------------------------------------------
    | Repository types
    |--------------------------------------------------------------------------
    |
    | A repository can be of different types, which can be specified here.
    | Current options:
    | - github
    | - gitlab
    | - http
    |
    */

    'repository_types' => [
        'github' => [
            'type'                 => 'github',
            'repository_vendor'    => env('SELF_UPDATER_REPO_VENDOR', 'eventschedule'),
            'repository_name'      => env('SELF_UPDATER_REPO_NAME', 'eventschedule'),
            'repository_url'       => 'https://github.com/eventschedule/eventschedule',
            'download_path'        => env('SELF_UPDATER_DOWNLOAD_PATH', '/tmp'),
            'private_access_token' => env('SELF_UPDATER_GITHUB_PRIVATE_ACCESS_TOKEN', ''),
            'use_branch'           => env('SELF_UPDATER_USE_BRANCH', ''),
            'package_file_name'    => env('SELF_UPDATER_PACKAGE_FILE_NAME', 'eventschedule.zip'),
        ],
        'gitlab' => [
            'base_url'             => '',
            'type'                 => 'gitlab',
            'repository_id'        => env('SELF_UPDATER_REPO_URL', ''),
            'download_path'        => env('SELF_UPDATER_DOWNLOAD_PATH', '/tmp'),
            'private_access_token' => env('SELF_UPDATER_GITLAB_PRIVATE_ACCESS_TOKEN', ''),
        ],
        'http' => [
            'type'                 => 'http',
            'repository_url'       => env('SELF_UPDATER_REPO_URL', ''),
            'pkg_filename_format'  => env('SELF_UPDATER_PKG_FILENAME_FORMAT', 'v_VERSION_'),
            'download_path'        => env('SELF_UPDATER_DOWNLOAD_PATH', '/tmp'),
            'private_access_token' => env('SELF_UPDATER_HTTP_PRIVATE_ACCESS_TOKEN', ''),
        ],
        'gitea' => [
            'type'                 => 'gitea',
            'repository_vendor'    => env('SELF_UPDATER_REPO_VENDOR', ''),
            'gitea_url'            => env('SELF_UPDATER_GITEA_URL', ''),
            'repository_name'      => env('SELF_UPDATER_REPO_NAME', ''),
            'download_path'        => env('SELF_UPDATER_DOWNLOAD_PATH', '/tmp'),
            'private_access_token' => env('SELF_UPDATER_GITEA_PRIVATE_ACCESS_TOKEN', ''),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Exclude folders from update
    |--------------------------------------------------------------------------
    |
    | Specific folders which should not be updated and will be skipped during the
    | update process.
    |
    | Here's already a list of good examples to skip. You may want to keep those.
    |
    */

    'exclude_folders' => [
        '__MACOSX',
        'node_modules',
        'bootstrap/cache',
        'bower',
        'storage/app',
        'storage/framework',
        'storage/logs',
        'storage/self-update',
        //'vendor',
    ],

    /*
    |--------------------------------------------------------------------------
    | Download Timeout
    |--------------------------------------------------------------------------
    |
    | Specifies the duration (in seconds) for how long downloads can take
    | until they timeout.
    |
    */

    'download_timeout' => env('SELF_UPDATER_DOWNLOAD_TIMEOUT', 600),

    /*
    |--------------------------------------------------------------------------
    | Event Logging
    |--------------------------------------------------------------------------
    |
    | Configure if fired events should be logged
    |
    */

    'log_events' => env('SELF_UPDATER_LOG_EVENTS', false),

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    |
    | Specify for which events you want to get notifications. Out of the box you can use 'mail'.
    |
    */

    /*
    'notifications' => [
        'notifications' => [
            \Codedge\Updater\Notifications\Notifications\UpdateSucceeded::class => ['mail'],
            \Codedge\Updater\Notifications\Notifications\UpdateFailed::class    => ['mail'],
            \Codedge\Updater\Notifications\Notifications\UpdateAvailable::class => ['mail'],
        ],

        'notifiable' => \Codedge\Updater\Notifications\Notifiable::class,

        'mail' => [
            'to' => [
                'address' => env('SELF_UPDATER_MAILTO_ADDRESS', 'notifications@example.com'),
                'name'    => env('SELF_UPDATER_MAILTO_NAME', ''),
            ],

            'from' => [
                'address' => env('SELF_UPDATER_MAIL_FROM_ADDRESS', 'updater@example.com'),
                'name'    => env('SELF_UPDATER_MAIL_FROM_NAME', 'Update'),
            ],
        ],
    ],
    */

    /*
    |---------------------------------------------------------------------------
    | Register custom artisan commands
    |---------------------------------------------------------------------------
    */

    'artisan_commands' => [
        'pre_update' => [
            //'command:signature' => [
            //    'class' => Command class
            //    'params' => []
            //]
        ],
        'post_update' => [

        ],
    ],

];
