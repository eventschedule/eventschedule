<?php

namespace App\Utils;

use App\Models\Role;
use Illuminate\Support\Facades\Cache;

/**
 * Which AI image names this app handed to which schedule.
 *
 * The AI endpoints write the image to storage first and hand the browser its bare filename; the
 * edit form posts that name back on save (ai_profile_image, ai_header_image, ai_background_image,
 * ai_flyer_image, and agenda_image_url for the photo an agenda scan kept on the create form), and
 * the save stores it and deletes the image it replaces. Nothing tied the name to the schedule, so a
 * posted name could be ANOTHER schedule's file: stored here, the next replace or delete here
 * deleted it, along with its derivatives.
 *
 * So each name is recorded where the file is written, keyed to the schedule in that request's URL,
 * and a save stores a posted name only when it was issued to the same schedule. A session list
 * cannot do this: RoleController::generateStyleImage() and EventController::generateFlyer() write
 * their file after the response, when the session has already been saved, and the style modal runs
 * its image requests in parallel, each of which rewrites the whole session. One cache key per file
 * has no such race.
 *
 * A name is stored once. accept() only checks it, and the save uses up its record with consume()
 * once the schedule or event holds it. So a save that fails before then can be posted again, where
 * a record used up by accept() told the owner to generate the image again. And once it is used up,
 * a form left open in another tab cannot put back a name whose file a newer image has replaced and
 * deleted, and a second event cannot take a file another event holds. The cache is wiped by a
 * deploy and a record lasts a day, so a genuine name can be refused; the save then keeps the
 * current image and asks the owner to generate again.
 */
class AiImageIssuance
{
    /**
     * The extensions an issued file can have: those ImageUtils::saveImageData() gives a generated
     * image, which are exactly the values of ImageUtils::getImageExtension(), plus jpeg, which an
     * agenda scan keeps from the uploaded photo (EventController::parseEventParts()).
     * tests/Unit/AiImageIssuanceTest.php fails if they drift.
     */
    private const EXTENSIONS = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'bmp'];

    public static function record(string $filename, ?int $roleId, ?int $userId): void
    {
        Cache::put(self::key($filename), ['role_id' => $roleId, 'user_id' => $userId], now()->addDay());
    }

    /**
     * $value when it is a name this app issued to $role for $slot, else null. Accepting it leaves
     * its record: the caller uses that up with consume() once the name is saved.
     *
     * $slot is the filename prefix the generator used: 'profile', 'header' or 'background' for a
     * schedule's style images, 'flyer' and 'agenda' for an event's. Anchored with \z rather than $,
     * which also matches before a trailing newline.
     */
    public static function accept(string $slot, mixed $value, Role $role): ?string
    {
        $pattern = '/^'.preg_quote($slot, '/').'_[a-z0-9]{32}\.(?:'.implode('|', self::EXTENSIONS).')\z/';

        if (! is_string($value) || ! preg_match($pattern, $value)) {
            return null;
        }

        $record = Cache::get(self::key($value));

        if (! is_array($record) || ! isset($record['role_id']) || (int) $record['role_id'] !== (int) $role->getKey()) {
            return null;
        }

        return $value;
    }

    /**
     * Use up the records of names accept() passed, once the schedule or event that took them has
     * been saved with them, so no later save can take them again.
     */
    public static function consume(string ...$filenames): void
    {
        foreach ($filenames as $filename) {
            Cache::forget(self::key($filename));
        }
    }

    private static function key(string $filename): string
    {
        return 'ai_image_issued:'.$filename;
    }
}
