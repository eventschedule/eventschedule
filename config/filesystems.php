<?php

/*
 * The endpoint of an S3/Spaces disk must NOT name the bucket, and this strips it if it does.
 *
 * The AWS SDK addresses S3 virtual-hosted style unless `use_path_style_endpoint` is on - neither
 * Spaces disk below sets it - so it takes the endpoint HOST and prepends "{bucket}.". DigitalOcean's
 * console shows each bucket's "origin endpoint" as https://{bucket}.{region}.digitaloceanspaces.com,
 * which is the value an operator naturally copies, and that lands on the wire as
 * {bucket}.{bucket}.{region}.digitaloceanspaces.com. DO's wildcard certificate covers exactly ONE
 * label, so every request dies in the TLS handshake - `cURL error 60: SSL: no alternative
 * certificate subject name matches target host name` - before it ever reaches the bucket. That is
 * how every backup export on hosted failed from the v1.0.130 cutover until this was added.
 *
 * Stripping a leading "{bucket}." is canonicalisation, not a guess: the SDK puts the same label
 * straight back, so the host on the wire is byte-identical to the one that was pasted. It is a
 * no-op on an endpoint that does not begin with the bucket, which is why it can sit on a disk whose
 * endpoint is already correct.
 *
 * Two things worth knowing before reusing it:
 *
 *  - Never apply it to a PATH-STYLE disk. There the endpoint host is used verbatim and the bucket
 *    becomes the first path SEGMENT, so a bucket-prefixed endpoint produces a valid host and an
 *    object key silently prefixed with the bucket name. That is also why path style is not the fix
 *    for the failure above: it repairs the hostname and moves the damage into every key.
 *  - The stripped value can look wrong in isolation. Bucket "acme" with endpoint "https://acme.com"
 *    leaves "https://com", and that is still exactly right, because nothing ever uses the endpoint
 *    without the bucket in front of it.
 *
 * `config:cache` bakes the result, which is fine: it is a pure function of the same env vars every
 * other value here reads.
 */
$endpointWithoutBucket = function (?string $endpoint, ?string $bucket): ?string {
    if (! $endpoint || ! $bucket) {
        return $endpoint;
    }

    $host = parse_url($endpoint, PHP_URL_HOST);

    if (! $host || strcasecmp(substr($host, 0, strlen($bucket) + 1), $bucket.'.') !== 0) {
        return $endpoint;
    }

    return substr_replace($endpoint, '', strpos($endpoint, $host), strlen($bucket) + 1);
};

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
            'endpoint' => $endpointWithoutBucket(env('DO_SPACES_ENDPOINT'), env('DO_SPACES_BUCKET')),
            'visibility' => 'public',

            /*
             * Every key in this bucket is unique to its content: user uploads are
             * strtolower(<prefix>.Str::random(32).<ext>) at all ~20 write sites, AI-generated
             * images go through ImageUtils::saveImageData() which uses the same random name, and
             * derivatives are ImageUtils::variantFilename() = "<that random name>_w480.webp".
             * Replacing an image mints a new name rather than overwriting the old one, so a key
             * that resolves today resolves to the same bytes forever - which is exactly what
             * `immutable` promises. Without this the DO Spaces CDN applies its own 1-hour default
             * and repeat visitors re-fetch the whole poster wall every hour.
             *
             * createS3Driver() hands this array to the AwsS3V3Adapter as its default $options, and
             * createOptionsFromConfig() copies every key in the adapter's AVAILABLE_OPTIONS -
             * CacheControl among them - onto PutObject. So one entry here covers every write path
             * without touching a single call site.
             *
             * The one way to break it is to regenerate a derivative for an unchanged original with
             * different encoder settings: same key, new bytes, and edges hold the old copy for a
             * year. ImageUtils only builds MISSING widths, so this cannot happen by accident - but
             * a deliberate re-encode needs a CDN purge.
             *
             * Deliberately not on the 'backups' disk below: those objects are private tenant
             * exports with a 7-day retention.
             */
            'options' => [
                'CacheControl' => 'public, max-age=31536000, immutable',
            ],
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
         * BACKUP_SPACES_BUCKET therefore has NO fallback to DO_SPACES_BUCKET, and must keep none:
         * a missing value must fail loudly rather than quietly write tenant data into the images
         * bucket. It is also the one key below that must NOT get the `?:` treatment - an empty
         * bucket has to stay empty and blow up, not reach for something else.
         *
         * Everything else uses `?:` rather than env()'s second argument, which only fires on a
         * MISSING key. .env.example and the SaaS setup doc both ship this block with empty values
         * for the operator to fill in, so an uncommented-but-unfilled BACKUP_SPACES_KEY= would
         * otherwise resolve to '' and silently skip the DO_SPACES_* fallback advertised here.
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
            'driver' => env('BACKUP_DISK_DRIVER') ?: 'local',

            // Kept in step with 'driver' above, including the `?:`: if the two ever disagree about
            // what the driver is, an s3 disk gets a filesystem path as its object key prefix.
            // Only the local driver wants a filesystem root. createS3Driver() uses this same key as
            // the OBJECT KEY PREFIX, so leaving storage_path() in on s3 silently turns every key
            // into /var/www/.../storage/app/backups/... The app would not notice - every read and
            // write goes through the same prefixer - but a bucket lifecycle rule or an IAM policy
            // scoped to "backups/*" would then match nothing, which is exactly the sort of control
            // an operator sets up as the backstop for the 7-day retention.
            'root' => (env('BACKUP_DISK_DRIVER') ?: 'local') === 'local' ? storage_path('app') : '',
            'key' => env('BACKUP_SPACES_KEY') ?: env('DO_SPACES_KEY'),
            'secret' => env('BACKUP_SPACES_SECRET') ?: env('DO_SPACES_SECRET'),
            'region' => env('BACKUP_SPACES_REGION') ?: env('DO_SPACES_REGION'),
            // Canonicalised AFTER the `?:` fallback, and against the BACKUPS bucket - that is the
            // label the SDK prepends to whichever endpoint wins. A blank BACKUP_SPACES_BUCKET makes
            // it a no-op, so the fallback advertised above is untouched.
            'endpoint' => $endpointWithoutBucket(
                env('BACKUP_SPACES_ENDPOINT') ?: env('DO_SPACES_ENDPOINT'),
                env('BACKUP_SPACES_BUCKET')
            ),
            'bucket' => env('BACKUP_SPACES_BUCKET'),
            'visibility' => 'private',
            'throw' => true,

            // Without this, AwsS3V3Adapter::readObject() leaves the SDK's default sink in place and
            // Guzzle buffers the ENTIRE object into php://temp before the first byte reaches the
            // client - so BackupController::download()'s StreamedResponse streams from a copy that
            // already materialised on the container's ephemeral disk, and TTFB is the full S3
            // fetch. Nothing caps the size of an export, so that copy is unbounded.
            'stream_reads' => true,
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
