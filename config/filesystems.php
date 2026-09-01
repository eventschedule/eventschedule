<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'throw' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'throw' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
        ],

        'do_spaces' => [
            'driver' => 's3',
            'key' => env('DO_SPACES_KEY'),
            'secret' => env('DO_SPACES_SECRET'),
            'region' => env('DO_SPACES_REGION'),
            'bucket' => env('DO_SPACES_BUCKET'),
            'endpoint' => env('DO_SPACES_ENDPOINT'),
            'visibility' => 'public',
        ],

        /*
         * Backup exports. A schedule export ZIP holds every sale, attendee email and phone number
         * for the schedules inside it, so this is deliberately NOT the do_spaces disk above: that
         * bucket is fronted by a public CDN, and ImageUtils::getUrl() addresses any object in it by
         * concatenating the raw storage key onto the CDN hostname. One wrong ACL there would publish
         * a tenant's whole export at a guessable path - backups/{user_id}/backup-{Y-m-d-His}.zip is
         * a small integer plus a second-resolution timestamp the owner can already see - and CDN
         * edges cache, so making the object private again does not revoke access until a purge.
         *
         * BACKUP_SPACES_BUCKET therefore has NO fallback to DO_SPACES_BUCKET. A missing value must
         * fail loudly rather than quietly write tenant data into the images bucket.
         *
         * 'throw' is on because put() otherwise returns false on a failed write, and
         * ProcessBackupExport discards that return and marks the job completed - mailing the user a
         * success notice with a dead link. Throwing lets its catch block mark the job failed.
         *
         * The driver defaults to 'local' with the same root as the 'local' disk, so selfhost
         * installs and every backups/... path already stored in backup_jobs.file_path keep working
         * with no migration.
         */
        'backups' => [
            'driver' => env('BACKUP_DISK_DRIVER', 'local'),

            // Only the local driver wants a filesystem root. createS3Driver() uses this same key as
            // the OBJECT KEY PREFIX, so leaving storage_path() in on s3 silently turns every key
            // into /var/www/.../storage/app/backups/... The app would not notice - every read and
            // write goes through the same prefixer - but a bucket lifecycle rule or an IAM policy
            // scoped to "backups/*" would then match nothing, which is exactly the sort of control
            // an operator sets up as the backstop for the 7-day retention.
            'root' => env('BACKUP_DISK_DRIVER', 'local') === 'local' ? storage_path('app') : '',
            'key' => env('BACKUP_SPACES_KEY', env('DO_SPACES_KEY')),
            'secret' => env('BACKUP_SPACES_SECRET', env('DO_SPACES_SECRET')),
            'region' => env('BACKUP_SPACES_REGION', env('DO_SPACES_REGION')),
            'endpoint' => env('BACKUP_SPACES_ENDPOINT', env('DO_SPACES_ENDPOINT')),
            'bucket' => env('BACKUP_SPACES_BUCKET'),
            'visibility' => 'private',
            'throw' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
