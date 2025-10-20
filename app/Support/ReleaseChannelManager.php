<?php

namespace App\Support;

use App\Enums\ReleaseChannel;

class ReleaseChannelManager
{
    public static function current(): ReleaseChannel
    {
        return ReleaseChannel::fromString(config('self-update.release_channel'));
    }

    public static function apply(ReleaseChannel $channel): void
    {
        config(['self-update.release_channel' => $channel->value]);
    }
}
