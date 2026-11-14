<?php

namespace App\Http\Controllers;

use App\Models\MarketingCopyHost;
use App\Services\MarketingCopyWatch;
use App\Utils\UrlUtils;
use Illuminate\Http\Request;

/**
 * /admin/copies: sites that may be serving a copy of the marketing pages (MarketingCopyWatch).
 * Registered on every install and 404'd off the nexus, like the federation pages, so the alert
 * row and the page stay testable under phpunit's pinned IS_NEXUS.
 */
class AdminCopyController extends Controller
{
    public const PER_PAGE = 50;

    public function index(Request $request)
    {
        $this->authorizeNexusAdmin();

        $likely = MarketingCopyHost::query()
            ->whereIn('status', ['copy', 'contacted'])
            ->orderByRaw("status = 'contacted'")
            ->orderByDesc('copy_found_at')
            ->get();

        $watched = MarketingCopyHost::query()
            ->whereNotIn('status', ['copy', 'contacted'])
            ->orderByRaw("status = 'dismissed'")
            ->orderByDesc('visits')
            ->orderByDesc('last_seen_at')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('admin.copies', [
            'likely' => $likely,
            'watched' => $watched,
            'counts' => [
                'copies' => $likely->where('status', 'copy')->count(),
                'contacted' => $likely->where('status', 'contacted')->count(),
                'watched' => MarketingCopyHost::whereNotIn('status', ['copy', 'contacted', 'dismissed'])->count(),
            ],
        ]);
    }

    public function decide(Request $request, string $hash)
    {
        $this->authorizeNexusAdmin();

        $action = $request->validate(['action' => 'required|in:contacted,dismissed,watch'])['action'];
        $row = $this->find($hash);

        $row->forceFill($action === 'watch'
            ? ['status' => 'unchecked', 'checked_at' => null, 'check_failures' => 0, 'decided_by' => $request->user()->id, 'decided_at' => now()]
            : ['status' => $action, 'decided_by' => $request->user()->id, 'decided_at' => now()]
        )->save();

        return redirect()->to(route('admin.copies').($action === 'contacted' ? '#copies' : '#watched'))
            ->with('message', __('messages.copies_saved', ['host' => $row->host]));
    }

    public function check(Request $request, string $hash)
    {
        $this->authorizeNexusAdmin();

        $row = $this->find($hash);
        $status = MarketingCopyWatch::check($row);
        $row->refresh();

        $key = match (true) {
            $status === 'unreachable' || ($row->checked_at && $row->check_failures > 0) => 'copies_checked_unreachable',
            count($row->markers ?? []) >= 2 => 'copies_checked_copy',
            default => 'copies_checked_clear',
        };

        return redirect()->to(route('admin.copies').(in_array($row->status, ['copy', 'contacted'], true) ? '#copies' : '#watched'))
            ->with('message', __('messages.'.$key, ['host' => $row->host]));
    }

    private function find(string $hash): MarketingCopyHost
    {
        return MarketingCopyHost::findOrFail(UrlUtils::decodeId($hash));
    }

    private function authorizeNexusAdmin(): void
    {
        abort_unless(MarketingCopyWatch::enabled(), 404);
        abort_unless(auth()->user()?->isAdmin(), 403);
    }
}
