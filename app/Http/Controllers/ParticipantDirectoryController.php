<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Services\ParticipantDirectory;
use App\Utils\UrlUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ParticipantDirectoryController extends Controller
{
    private function schedule(Request $request, string $subdomain): Role
    {
        abort_unless($request->user()->isEditor($subdomain), 403);

        return Role::subdomain($subdomain)->where('is_deleted', false)->firstOrFail();
    }

    private function entry(Role $schedule, string $entry)
    {
        return DB::table('backstage_entries')->where('schedule_id', $schedule->id)->where('id', UrlUtils::decodeIdOrFail($entry))->firstOrFail();
    }

    private function page(Role $schedule, ?int $entry = null)
    {
        return redirect()->route('role.view_admin', array_filter(['subdomain' => $schedule->subdomain, 'tab' => 'participants', 'entry' => $entry ? UrlUtils::encodeId($entry) : null]));
    }

    public function store(Request $request, string $subdomain)
    {
        $schedule = $this->schedule($request, $subdomain);
        $input = $request->validate(['participant' => 'nullable|string|prohibits:name', 'name' => 'nullable|required_without:participant|string|max:255']);
        $id = DB::transaction(function () use ($request, $schedule, $input) {
            DB::table('roles')->where('id', $schedule->id)->lockForUpdate()->firstOrFail();
            if ($request->filled('participant')) {
                $person = ParticipantDirectory::choices($schedule, $request->user())->findOrFail(UrlUtils::decodeIdOrFail($input['participant']));
            } else {
                $person = new Role;
                $person->type = 'talent';
                $person->name = $input['name'];
                $person->is_unlisted = true;
                $person->subdomain = Role::generateSubdomain($person->name, shortest: false);
                $person->language_code = $schedule->language_code;
                $person->timezone = $schedule->timezone;
                $person->country_code = $schedule->country_code;
                $person->save();
            }
            ParticipantDirectory::register($schedule->id, [$person->id]);

            return DB::table('backstage_entries')->where(['schedule_id' => $schedule->id, 'participant_id' => $person->id])->lockForUpdate()->firstOrFail()->id;
        });

        return $this->page($schedule, $id);
    }

    public function destroy(Request $request, string $subdomain, string $entry)
    {
        $schedule = $this->schedule($request, $subdomain);
        DB::table('backstage_entries')->delete($this->entry($schedule, $entry)->id);

        return $this->page($schedule);
    }

    public function basicInfo(Request $request, string $subdomain, string $entry)
    {
        $schedule = $this->schedule($request, $subdomain);
        $id = $this->entry($schedule, $entry)->id;
        $data = $request->validate([
            'participant_email' => 'present|nullable|email|max:255',
            'participant_phone' => 'present|nullable|string|max:80',
            'participant_website' => 'present|nullable|url:http,https|max:2048',
        ]);
        DB::table('backstage_entries')->where('id', $id)->update([...$data, 'participant_info_updated_at' => now()]);

        return $this->page($schedule, $id);
    }

    public function profileVideo(Request $request, string $subdomain, string $entry)
    {
        $schedule = $this->schedule($request, $subdomain);
        $record = $this->entry($schedule, $entry);
        DB::transaction(function () use ($request, $record) {
            $person = Role::where('is_deleted', false)->lockForUpdate()->findOrFail($record->participant_id);
            abort_unless($person->isEditableBy($request->user()), 403);
            $request->validate(['original_url' => 'required|string']);
            abort_unless($person->getFirstVideoUrl() === $request->original_url, 409);
            $links = json_decode($person->youtube_links, true);
            if ($request->isMethod('delete')) {
                array_shift($links);
            } else {
                $url = $request->validate(['url' => 'required|url:http,https|max:2048'])['url'];
                $videoId = UrlUtils::extractYouTubeVideoId($url);
                if (! $videoId) {
                    throw ValidationException::withMessages(['url' => __('messages.invalid_youtube_url')]);
                }
                if ($url === $request->original_url) {
                    return;
                }
                $links[0] = ['url' => $url, 'name' => '', 'thumbnail_url' => UrlUtils::getYouTubeThumbnail($url)];
            }
            $person->youtube_links = $links ? json_encode($links) : null;
            $person->save();
        });

        return $this->page($schedule, $record->id);
    }

    public function promotion(Request $request, string $subdomain, string $entry)
    {
        $schedule = $this->schedule($request, $subdomain);
        $id = $this->entry($schedule, $entry)->id;
        $data = $request->validate(['promotion' => 'present|nullable|string|max:15000']);
        DB::table('backstage_entries')->where('id', $id)->update($data);

        return $this->page($schedule, $id);
    }

    public function item(Request $request, string $subdomain, string $entry, string $kind, ?string $item = null)
    {
        $schedule = $this->schedule($request, $subdomain);
        $id = $this->entry($schedule, $entry)->id;
        // Route constrains kind to these three tables.
        $rows = DB::table('backstage_'.$kind)->where('entry_id', $id);
        if ($item !== null) {
            $rows->where('id', UrlUtils::decodeIdOrFail($item))->firstOrFail();
        }
        if ($request->isMethod('delete')) {
            abort_if($item === null, 404);
            $rows->delete();
        } elseif ($kind === 'files' && $item === null) {
            $this->upload($request, $schedule, $id);
        } else {
            $data = $request->validate(match ($kind) {
                'contacts' => ['name' => 'required|string|max:255', 'label' => 'nullable|string|max:255', 'email' => 'nullable|email|max:255', 'phone' => 'nullable|string|max:80', 'website' => 'nullable|url:http,https|max:2048'],
                'links' => ['platform' => 'required|in:spotify,apple_music,bandcamp,soundcloud,instagram,facebook,tiktok,linktree', 'url' => 'required|url:http,https|max:2048'],
                'files' => ['label' => 'present|nullable|string|max:255'],
            });
            if ($item === null) {
                $rows->insert(['entry_id' => $id] + $data);
            } else {
                $rows->update($data);
            }
        }

        return $this->page($schedule, $id);
    }

    private function upload(Request $request, Role $schedule, int $id): void
    {
        $types = 'pdf,png,jpg,jpeg,doc,docx,xls,xlsx,ppt,pptx';
        $data = $request->validate(['file' => 'required|file|mimes:'.$types.'|extensions:'.$types.'|max:'.(max(0, config('backstage.file_mb')) * 1024), 'label' => 'nullable|string|max:255']);
        $file = $data['file'];
        DB::transaction(function () use ($schedule, $id, $data, $file) {
            DB::table('roles')->where('id', $schedule->id)->lockForUpdate()->firstOrFail();
            DB::table('backstage_entries')->where('id', $id)->where('schedule_id', $schedule->id)->lockForUpdate()->firstOrFail();
            $used = DB::table('backstage_files as f')->join('backstage_entries as e', 'f.entry_id', '=', 'e.id')->where('e.schedule_id', $schedule->id)->sum('f.bytes');
            if ($used + $file->getSize() > config('backstage.schedule_mb') * 1048576) {
                throw ValidationException::withMessages(['file' => __('backstage.quota')]);
            }
            $name = basename(str_replace('\\', '/', $file->getClientOriginalName()));
            $fileId = DB::table('backstage_files')->insertGetId([
                'entry_id' => $id,
                'bytes' => $file->getSize(),
                'label' => $data['label'] ?? null,
                'filename' => mb_substr(preg_replace('/[[:cntrl:]]/', '_', $name), 0, 255),
            ]);
            $stream = $file->openFile('rb');
            $batch = [];
            while (! $stream->eof()) {
                $chunk = $stream->fread(60000);
                if ($chunk !== '') {
                    $batch[] = ['file_id' => $fileId, 'contents' => $chunk];
                }
                if (count($batch) === 12 || $stream->eof()) {
                    DB::table('backstage_file_chunks')->insert($batch);
                    $batch = [];
                }
            }
        });
    }

    public function download(Request $request, string $subdomain, string $entry, string $item)
    {
        $id = $this->entry($this->schedule($request, $subdomain), $entry)->id;
        $file = DB::table('backstage_files')->where('entry_id', $id)->where('id', UrlUtils::decodeIdOrFail($item))->firstOrFail();

        return response()->streamDownload(function () use ($file) {
            foreach (DB::table('backstage_file_chunks')->where('file_id', $file->id)->lazyById(12) as $chunk) {
                echo $chunk->contents;
            }
        }, $file->filename, ['Content-Type' => 'application/octet-stream', 'Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']);
    }
}
