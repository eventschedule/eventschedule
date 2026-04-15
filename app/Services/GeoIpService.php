<?php

namespace App\Services;

use GeoIp2\Database\Reader;

class GeoIpService
{
    protected ?Reader $reader = null;

    protected bool $initialized = false;

    public function lookup(string $ip): ?string
    {
        if (! $this->initialize()) {
            return null;
        }

        try {
            $record = $this->reader->country($ip);

            return $record->country->isoCode;
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function initialize(): bool
    {
        if ($this->initialized) {
            return $this->reader !== null;
        }

        $this->initialized = true;
        $dbPath = database_path('geoip/dbip-country-lite.mmdb');

        if (! file_exists($dbPath)) {
            return false;
        }

        try {
            $this->reader = new Reader($dbPath);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
