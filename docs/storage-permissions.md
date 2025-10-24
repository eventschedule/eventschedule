# Storage Directory Permissions

Laravel expects the directories under `storage/` to be both **writable** and **traversable** by the user that runs PHP (typically `www-data` or `nginx`). When a directory is missing the execute (`x`) bit Laravel cannot open files inside it, even if read (`r`) permissions are present. Likewise, without write (`w`) permissions the framework cannot create cache files, compiled views, or logs.

The listing below illustrates a problematic layout:

```
drw-r--r-- 5 82 82   5 Oct 16 09:52 framework
drw-r--r-- 5 82 82   5 Oct 16 09:52 framework
-rw-r--r-- 1 82 82 803 Oct 16 09:52 gradients.json
-rw-r--r-- 1 82 82 841 Oct 16 09:52 backgrounds.json
drw-r--r-- 2 82 82   3 Oct 16 09:53 logs
drw-r--r-- 2 82 82   2 Oct 16 10:40 app
```

Every directory (`framework`, `logs`, `app`) lacks the execute bit and is owned by UID/GID `82`. In this state Laravel cannot:

- read JSON configuration files that live below `storage/app/`
- write cache or compiled files under `storage/framework/`
- create the daily log file in `storage/logs/`

The usual symptom is an HTTP 500 error because the framework fails to write to the log or cache directory while bootstrapping the request.

## Running the Permission Inspector

Use the bundled Artisan command to check whether Laravel can access the expected directories and files:

```bash
php artisan storage:permissions
```

The command scans `storage/` and `bootstrap/cache`, reporting any entries that are not readable, writable, or (for directories) traversable. Add the `--json` flag when you want to feed the output to another tool.

## Fixing Ownership

Ensure the storage tree is owned by the same user that runs the PHP process. On many Linux distributions that is `www-data`:

```bash
sudo chown -R www-data:www-data /data/Docker/eventscheduler/storage
sudo chown -R www-data:www-data /data/Docker/eventscheduler/bootstrap/cache
```

If you deploy via Docker, run the command inside the container as `root` but target the user configured for the PHP-FPM process.

## Fixing Permissions

Grant the owner read, write, and execute access, and allow the group to traverse the directories:

```bash
sudo find /data/Docker/eventscheduler/storage -type d -exec chmod 775 {} +
sudo find /data/Docker/eventscheduler/storage -type f -exec chmod 664 {} +
sudo find /data/Docker/eventscheduler/bootstrap/cache -type d -exec chmod 775 {} +
sudo find /data/Docker/eventscheduler/bootstrap/cache -type f -exec chmod 664 {} +
```

This results in directory entries such as `drwxrwxr-x`, which lets PHP create and read files while preventing anonymous users from writing to the directory.

After adjusting the ownership and permissions, reload the application: the `/new/talent`, `/new/venue`, and `/new/curator` pages should respond normally once Laravel can read the JSON files and write its log and cache entries.

## Serving Public Assets from Storage

If your web server runs as a user that is **not** part of the `www-data` group, the default `775/664` permission scheme can still block it from reading images or documents exposed via `storage/app/public`. A common symptom is that profile or event artwork responds with `404 Not Found` even though the files exist on disk.

To make the assets world-readable while keeping ownership consistent, run the following commands (adjust the paths to match your deployment):

```bash
sudo chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
sudo find /var/www/html/storage -type d -exec chmod 755 {} \;
sudo find /var/www/html/storage -type f -exec chmod 644 {} \;
sudo chmod 755 /var/www /var/www/html /var/www/html/public
```

The first command ensures Laravel can continue writing to the directories, while the `chmod` calls grant "read" access to everyone and leave the execute bit on directories so nginx (or another HTTP server) can traverse the tree. Making the parent directories traversable is required for nginx to follow the symlink created by `php artisan storage:link`.

Because `755/644` is more permissive than the earlier recommendation, prefer to scope it to the storage tree or to servers that do not share the host with untrusted users.
