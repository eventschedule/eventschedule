<?php

namespace App\Http\Controllers;

use App\Models\PromoterLink;
use App\Models\Role;
use App\Services\PromoterLinks;
use App\Utils\UrlUtils;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * What changes a schedule's promoter links. Their list is a tab of the schedule (role.view_admin,
 * tab "promoters"); these answer its forms. For the owner and the admins, and only on Pro: a link
 * made on Pro stays listed after a downgrade, and can still be archived, but nothing is added.
 */
class PromoterLinkController extends Controller
{
    private function schedule(string $subdomain): Role|RedirectResponse
    {
        $role = Role::subdomain($subdomain)->where('is_deleted', false)->firstOrFail();

        if (! auth()->user()->isEditor($subdomain)) {
            return redirect()->route('home')->with('error', __('messages.not_authorized'));
        }

        return $role;
    }

    private function tab(Role $role): RedirectResponse
    {
        return redirect()->route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'promoters']);
    }

    private function link(Role $role, string $hash): PromoterLink
    {
        return PromoterLink::where('role_id', $role->id)->whereKey(UrlUtils::decodeId($hash))->firstOrFail();
    }

    /** @return array{name: string, code: ?string, commission_percent: ?float} */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['nullable', 'string', 'max:40', 'regex:/^[A-Za-z0-9_-]+$/'],
            'commission_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ], [
            'code.regex' => __('messages.promoter_code_invalid'),
        ]);

        return [
            // As typed: Blade escapes it wherever it is printed, and "Sam <3 Events" is a name.
            'name' => trim($data['name']),
            'code' => isset($data['code']) && trim($data['code']) !== '' ? trim($data['code']) : null,
            'commission_percent' => isset($data['commission_percent']) && $data['commission_percent'] !== '' ? round((float) $data['commission_percent'], 2) : null,
        ];
    }

    public function store(Request $request, string $subdomain)
    {
        $role = $this->schedule($subdomain);
        if ($role instanceof RedirectResponse) {
            return $role;
        }

        if (! $role->isPro()) {
            return $this->tab($role)->with('error', __('messages.not_authorized'));
        }

        if (PromoterLink::where('role_id', $role->id)->count() >= PromoterLinks::MAX_LINKS) {
            return $this->tab($role)->with('error', __('messages.promoter_links_limit', ['count' => PromoterLinks::MAX_LINKS]));
        }

        $data = $this->validated($request);

        if ($data['code'] !== null && PromoterLinks::codeTaken($role, $data['code'])) {
            throw ValidationException::withMessages(['code' => __('messages.promoter_code_taken')]);
        }

        $link = new PromoterLink([
            'role_id' => $role->id,
            'name' => $data['name'],
            'code' => $data['code'] ?? PromoterLinks::codeFromName($role, $data['name']),
            'commission_percent' => $data['commission_percent'],
        ]);

        // Checked above, and still possible: two of the same add at once meet at the unique index.
        try {
            $link->save();
        } catch (UniqueConstraintViolationException $e) {
            throw ValidationException::withMessages(['code' => __('messages.promoter_code_taken')]);
        }

        return $this->tab($role)->with('message', __('messages.promoter_link_added'));
    }

    public function update(Request $request, string $subdomain, string $hash)
    {
        $role = $this->schedule($subdomain);
        if ($role instanceof RedirectResponse) {
            return $role;
        }

        // Archive stays open below Pro, so a lapsed schedule can tidy up; changing a link does not.
        if (! $role->isPro()) {
            return $this->tab($role)->with('error', __('messages.not_authorized'));
        }

        $link = $this->link($role, $hash);
        $data = $this->validated($request);

        // Once followed, the code is printed somewhere: a new one would break every copy of it.
        if ($data['code'] !== null && $data['code'] !== $link->code) {
            if ($link->codeLocked()) {
                throw ValidationException::withMessages(['code' => __('messages.promoter_code_locked')]);
            }
            if (PromoterLinks::codeTaken($role, $data['code'], $link->id)) {
                throw ValidationException::withMessages(['code' => __('messages.promoter_code_taken')]);
            }
            $link->code = $data['code'];
        }

        $link->name = $data['name'];
        $link->commission_percent = $data['commission_percent'];

        try {
            $link->save();
        } catch (UniqueConstraintViolationException $e) {
            throw ValidationException::withMessages(['code' => __('messages.promoter_code_taken')]);
        }

        return $this->tab($role)->with('message', __('messages.promoter_link_saved'));
    }

    public function archive(Request $request, string $subdomain, string $hash)
    {
        $role = $this->schedule($subdomain);
        if ($role instanceof RedirectResponse) {
            return $role;
        }

        $link = $this->link($role, $hash);
        $link->archived_at = now();
        $link->save();

        return $this->tab($role)->with('message', __('messages.promoter_link_archived'));
    }

    public function restore(Request $request, string $subdomain, string $hash)
    {
        $role = $this->schedule($subdomain);
        if ($role instanceof RedirectResponse) {
            return $role;
        }

        if (! $role->isPro()) {
            return $this->tab($role)->with('error', __('messages.not_authorized'));
        }

        $link = $this->link($role, $hash);
        $link->archived_at = null;
        $link->save();

        return $this->tab($role)->with('message', __('messages.promoter_link_restored'));
    }
}
