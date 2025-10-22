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
            'visibility' => 'public',
            'directory_visibility' => 'public',
            'permissions' => [
                'file' => [
                    'public' => 0644,
                ],
                'dir' => [
                    'public' => 0755,
                ],
            ],
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

        'images' => (static function () {
            $driver = env('IMAGES_DISK', env('FILESYSTEM_DISK', 'local'));

            if ($driver === 's3') {
                return [
                    'driver' => 's3',
                    'key' => env('IMAGES_AWS_ACCESS_KEY_ID', env('AWS_ACCESS_KEY_ID')),
                    'secret' => env('IMAGES_AWS_SECRET_ACCESS_KEY', env('AWS_SECRET_ACCESS_KEY')),
                    'region' => env('IMAGES_AWS_DEFAULT_REGION', env('AWS_DEFAULT_REGION')),
                    'bucket' => env('IMAGES_AWS_BUCKET', env('AWS_BUCKET')),
                    'url' => env('IMAGES_AWS_URL', env('AWS_URL')),
                    'endpoint' => env('IMAGES_AWS_ENDPOINT', env('AWS_ENDPOINT')),
                    'visibility' => env('IMAGES_VISIBILITY', 'public'),
                    'use_path_style_endpoint' => env('IMAGES_AWS_USE_PATH_STYLE_ENDPOINT', env('AWS_USE_PATH_STYLE_ENDPOINT', false)),
                    'throw' => false,
                ];
            }

            return [
                'driver' => 'local',
                'root' => env('IMAGES_LOCAL_ROOT', storage_path('app/images')),
                'url' => env('IMAGES_URL'),
                'visibility' => env('IMAGES_VISIBILITY', 'public'),
                'throw' => false,
            ];
        })(),
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

    'links' => array_filter([
        public_path('storage') => storage_path('app/public'),
        public_path('storage/images') => env('IMAGES_DISK', env('FILESYSTEM_DISK', 'local')) === 's3'
            ? null
            : env('IMAGES_LOCAL_ROOT', storage_path('app/images')),
    ], static fn ($value) => !is_null($value)),

    'image_storage' => [
        'disk' => env('IMAGES_STORAGE_DISK', 'images'),
        'directory' => env('IMAGES_DIRECTORY', 'images/originals'),
        'variants_directory' => env('IMAGES_VARIANTS_DIRECTORY', 'images/variants'),
        'import_paths' => array_values(array_filter(array_map(
            'trim',
            explode(',', env('IMAGES_IMPORT_PATHS', 'public/images'))
        ))),
    ],

];
