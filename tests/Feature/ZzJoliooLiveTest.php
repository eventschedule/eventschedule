<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventFeed;
use App\Models\EventFeedItem;
use App\Services\Feeds\FeedImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

class ZzJoliooLiveTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    public array $override = [];

    private array $env;

    private function body(string $url): array
    {
        if (isset($this->override[$url])) {
            return $this->override[$url] === 500 ? [500, '500 - Exception'] : [200, $this->override[$url]];
        }
        $file = $this->env['cache'].'/'.sha1($url);
        if (! file_exists($file)) {
            return [500, 'not cached'];
        }
        [$code, $b] = explode("\n", file_get_contents($file), 2);

        return [(int) $code, $b];
    }

    public function test_live(): void
    {
        $this->env = json_decode(file_get_contents('/private/tmp/claude-501/-Users-hillel-Code-eventschedule/9c271aa6-4f57-47dc-a720-9f39457925dd/scratchpad/zzenv.json'), true);
        $feedUrl = $this->env['feed'];
        config(['app.hosted' => false]);
        Storage::fake(config('filesystems.default'));
        Http::fake(function ($request) {
            [$code, $b] = $this->body($request->url());
            $type = str_ends_with(parse_url($request->url(), PHP_URL_PATH), '.json') ? 'application/json' : (str_ends_with($request->url(), '.jpg') ? 'image/jpeg' : 'application/xml');

            return Http::response($b, $code, ['Content-Type' => $type]);
        });

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'curator', ['timezone' => 'Europe/Vienna', 'country_code' => 'AT']);
        $feed = EventFeed::create([
            'role_id' => $role->id, 'added_by' => $owner->id, 'name' => 'Live',
            'url' => $feedUrl, 'url_hash' => EventFeed::hashOf($feedUrl), 'host' => 'app.jolioo.com',
            'kind' => EventFeed::KIND_JOLIOO, 'source_timezone' => 'Europe/Vienna', 'can_see_leaving' => true,
            'publish_mode' => EventFeed::DRAFT, 'left_action' => EventFeed::LEFT_CANCEL, 'baseline_batch' => 'abcdef012345',
        ]);
        for ($i = 0; $i < 40 && ($i === 0 || $feed->items()->whereNull('detail_checked_at')->exists()); $i++) {
            app(FeedImporter::class)->read($feed->fresh(), microtime(true) + 120);
        }
        for ($i = 0; $i < 10 && $feed->items()->where('state', 'new')->whereNull('pending->left_out')->exists(); $i++) {
            $this->travel(25)->hours();
            app(FeedImporter::class)->read($feed->fresh(), microtime(true) + 120);
        }

        $out = ['events' => Event::count(), 'links_under_rss' => Event::where('registration_url', 'like', '%/rss/%')->count(),
            'credit_only' => Event::where('description', 'like', '©%')->count()];
        $id = $this->env['item'];
        $item = $feed->items()->where('external_key', EventFeedItem::keyFor($id))->first();
        $ev = Event::find($item?->event_id);
        if (! $ev) {
            $stuck = $feed->items()->where('state', 'new')->whereNull('pending->left_out')->get();
            $rows = [];
            foreach ($stuck as $s) {
                [, $b] = $this->body($s->detail_url);
                $p = json_decode($b, true)['posting'] ?? [];
                $rows[] = [$s->external_id, $p['post_message_title'] ?? null, $p['post_message_location'] ?? null, $p['post_message_start'] ?? null, $s->pending, $s->page_tries];
            }
            file_put_contents($this->env['out'], json_encode(['stuck' => $rows, 'out' => $out, 'item' => $item?->toArray(), 'items' => $feed->items()->count(), 'states' => $feed->items()->get()->groupBy(fn ($i) => $i->state.'/'.($i->pending['left_out'] ?? '-'))->map->count(), 'feed' => $feed->fresh()->only(['status', 'last_error', 'stats'])], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            return;
        }
        $out['example'] = [$ev->name, $ev->starts_at, $ev->duration, $ev->registration_url, $ev->venue?->only(['name', 'address1', 'postal_code', 'city', 'country_code'])];
        $out['venues'] = \App\Models\Role::where('type', 'venue')->get()->map(fn ($v) => implode(' / ', [$v->name, $v->address1, $v->postal_code, $v->city, $v->country_code]))->all();

        [, $page] = $this->body($feedUrl);
        $this->override[$item->detail_url] = 500;
        $this->override[$feedUrl] = preg_replace('#<item>(?:(?!</item>).)*?/item/'.$id.'\.xml(?:(?!</item>).)*?</item>#s', '', $page);
        foreach ([1, 2] as $look) {
            DB::table('event_feed_items')->update(['last_seen_at' => DB::raw('DATE_SUB(last_seen_at, INTERVAL 2 HOUR)')]);
            $importer = app(FeedImporter::class);
            $r = $importer->read($feed->fresh(), microtime(true) + 120);
            $fetcher = app(\App\Services\Feeds\FeedFetcher::class);
            $whole = (new \ReflectionMethod($importer, 'wholeList'));
            $reading = $whole->invoke($importer, \App\Services\Feeds\FeedKind::reader($feed->kind), $feed->fresh(), $role, $fetcher->get($feedUrl));
            $out['looks'][] = ['read' => $r, 'missing' => $item->fresh()->missing_reads, 'complete' => $reading->complete, 'since' => $reading->listedSince, 'n' => count($reading->items), 'item_published' => $item->fresh()->pending['hint']['published'] ?? null, 'item_starts' => (string) $item->fresh()->starts_at];
        }
        $out['item_after'] = $item->fresh()->only(['state', 'missing_reads', 'last_seen_at', 'decided_hash']);
        $out['feed_after'] = $feed->fresh()->only(['held_leaving', 'status', 'paused_at']);
        $out['override_has_item'] = str_contains($this->override[$feedUrl], '/item/'.$id.'.xml');
        $out['example_after_takedown'] = ['cancelled' => (bool) $ev->fresh()->is_cancelled, 'others_cancelled' => Event::where('is_cancelled', true)->where('id', '!=', $ev->id)->count()];

        file_put_contents($this->env['out'], json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $this->assertTrue(true);
    }
}
