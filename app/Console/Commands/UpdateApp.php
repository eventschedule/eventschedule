<?php

namespace App\Console\Commands;

use App\Enums\ReleaseChannel;
use App\Services\ReleaseChannelService;
use App\Support\ReleaseChannelManager;
use Codedge\Updater\UpdaterManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use RuntimeException;
use Throwable;
class UpdateApp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update {--channel= : Release channel to use (production or beta)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(UpdaterManager $updater, ReleaseChannelService $releaseChannels)
    {
        if (config('app.hosted')) {
            $this->error('Not authorized');
            return self::FAILURE;
        }

        $defaultChannel = ReleaseChannelManager::current();
        $channel = ReleaseChannel::fromString($this->option('channel') ?? $defaultChannel->value);

        $this->info(sprintf('Updating app via the %s channel. This can take a few minutes...', $channel->label()));

        try {
            $latestRelease = $releaseChannels->getLatestRelease($channel);
            $versionAvailable = $latestRelease['version'];
            $releaseTag = $latestRelease['tag'];
            $installedVersion = $updater->source()->getVersionInstalled();
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        if ($versionAvailable === $installedVersion) {
            $this->info('No updates available');

            return self::SUCCESS;
        }

        try {
            $release = $updater->source()->fetch($releaseTag);

            $updateResult = $updater->source()->update($release);

            if ($updateResult === false) {
                throw new RuntimeException(
                    'The update could not be installed. Please check the application logs for more details.'
                );
            }

            Artisan::call('migrate', ['--force' => true]);
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info('App updated successfully! ' . $versionAvailable);

        return self::SUCCESS;
    }
}
