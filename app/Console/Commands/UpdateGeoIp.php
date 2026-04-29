<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdateGeoIp extends Command
{
    protected $signature = 'app:update-geoip';

    protected $description = 'Download or update the GeoIP database for visitor location analytics';

    public function handle()
    {
        $targetDir = database_path('geoip');
        $targetPath = $targetDir.'/dbip-country-lite.mmdb';

        if (! is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $year = now()->format('Y');
        $month = now()->format('m');
        $url = "https://download.db-ip.com/free/dbip-country-lite-{$year}-{$month}.mmdb.gz";

        $this->info('Downloading DB-IP Lite Country database...');
        $this->line("URL: {$url}");

        $gzData = @file_get_contents($url);
        if ($gzData === false) {
            $this->error('Failed to download the GeoIP database.');
            $this->line('Check your internet connection and try again.');

            return 1;
        }

        $mmdbData = @gzdecode($gzData);
        if ($mmdbData === false) {
            $this->error('Failed to decompress the GeoIP database.');

            return 1;
        }

        file_put_contents($targetPath, $mmdbData);

        // Verify the file is readable
        try {
            $reader = new \GeoIp2\Database\Reader($targetPath);
            $reader->close();
            $this->info('GeoIP database updated successfully.');
            $this->line('File: '.$targetPath);
            $this->line('Size: '.number_format(filesize($targetPath) / 1024 / 1024, 1).' MB');
        } catch (\Exception $e) {
            $this->error('Downloaded file appears to be invalid: '.$e->getMessage());

            return 1;
        }

        return 0;
    }
}
