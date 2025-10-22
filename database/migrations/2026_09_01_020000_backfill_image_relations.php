<?php

use App\Models\Image;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('images')) {
            return;
        }

        $disk = storage_public_disk();

        $this->backfillEvents($disk);
        $this->backfillRoles($disk);
        $this->backfillUsers($disk);
    }

    public function down(): void
    {
        // No-op
    }

    private function backfillEvents(string $disk): void
    {
        $events = DB::table('events')
            ->select('id', 'user_id', 'flyer_image_url', 'flyer_image_id')
            ->whereNotNull('flyer_image_url')
            ->get();

        foreach ($events as $event) {
            if ($event->flyer_image_id) {
                continue;
            }

            $imageId = $this->firstOrCreateImage($disk, $event->flyer_image_url, $event->user_id);

            if ($imageId) {
                DB::table('events')->where('id', $event->id)->update([
                    'flyer_image_id' => $imageId,
                ]);
            }
        }
    }

    private function backfillRoles(string $disk): void
    {
        $roles = DB::table('roles')
            ->select(
                'id',
                'user_id',
                'profile_image_url',
                'background_image_url',
                'header_image_url',
                'profile_image_id',
                'background_image_id',
                'header_image_id'
            )
            ->where(function ($query) {
                $query->whereNotNull('profile_image_url')
                    ->orWhereNotNull('background_image_url')
                    ->orWhereNotNull('header_image_url');
            })
            ->get();

        foreach ($roles as $role) {
            if ($role->profile_image_url && ! $role->profile_image_id) {
                $imageId = $this->firstOrCreateImage($disk, $role->profile_image_url, $role->user_id);
                if ($imageId) {
                    DB::table('roles')->where('id', $role->id)->update(['profile_image_id' => $imageId]);
                }
            }

            if ($role->background_image_url && ! $role->background_image_id) {
                $imageId = $this->firstOrCreateImage($disk, $role->background_image_url, $role->user_id);
                if ($imageId) {
                    DB::table('roles')->where('id', $role->id)->update(['background_image_id' => $imageId]);
                }
            }

            if ($role->header_image_url && ! $role->header_image_id) {
                $imageId = $this->firstOrCreateImage($disk, $role->header_image_url, $role->user_id);
                if ($imageId) {
                    DB::table('roles')->where('id', $role->id)->update(['header_image_id' => $imageId]);
                }
            }
        }
    }

    private function backfillUsers(string $disk): void
    {
        $users = DB::table('users')
            ->select('id', 'profile_image_url', 'profile_image_id')
            ->whereNotNull('profile_image_url')
            ->get();

        foreach ($users as $user) {
            if ($user->profile_image_id) {
                continue;
            }

            $imageId = $this->firstOrCreateImage($disk, $user->profile_image_url, $user->id);
            if ($imageId) {
                DB::table('users')->where('id', $user->id)->update(['profile_image_id' => $imageId]);
            }
        }
    }

    private function firstOrCreateImage(string $disk, ?string $path, ?int $userId): ?int
    {
        if (! is_string($path) || trim($path) === '') {
            return null;
        }

        $normalized = storage_normalize_path($path);

        /** @var Image|null $image */
        $image = Image::query()->where([
            'disk' => $disk,
            'path' => $normalized,
        ])->first();

        if (! $image) {
            $image = Image::create([
                'disk' => $disk,
                'path' => $normalized,
                'user_id' => $userId,
            ]);
        }

        return $image?->id;
    }
};
