<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventTemplate;
use App\Models\Role;
use App\Repos\EventRepo;
use App\Utils\UrlUtils;
use Illuminate\Http\Request;

class EventTemplateController extends Controller
{
    /**
     * Save an existing event as a reusable template for the schedule.
     */
    public function store(Request $request, $subdomain, $hash)
    {
        $role = $this->authorizedRole($subdomain);
        if (! $role instanceof Role) {
            return $role;
        }

        $event = Event::with(['tickets', 'addons', 'roles', 'curators', 'parts'])
            ->findOrFail(UrlUtils::decodeId($hash));

        if ($request->user()->cannot('update', $event)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $payload = $this->templateAdjustments(EventRepo::buildClonePayload($event));

        EventTemplate::create([
            'role_id' => $role->id,
            'user_id' => $request->user()->id,
            'name' => $validated['name'],
            'template_data' => $payload,
        ]);

        return redirect()->back()->with('message', __('messages.template_saved'));
    }

    /**
     * Apply a template: load its payload into the clone session and send the user to
     * the prefilled create form. An optional ?date is forwarded so a template can be
     * dropped onto a specific calendar day.
     */
    public function apply(Request $request, $subdomain, $hash)
    {
        $role = $this->authorizedRole($subdomain);
        if (! $role instanceof Role) {
            return $role;
        }

        $template = $this->findTemplate($role, $hash);

        session(['cloned_event' => $template->template_data]);

        $params = ['subdomain' => $subdomain];
        if ($request->date) {
            $params['date'] = $request->date;
        }

        return redirect(route('event.create', $params))
            ->with('message', __('messages.started_from_template'));
    }

    /**
     * Rename a template.
     */
    public function update(Request $request, $subdomain, $hash)
    {
        $role = $this->authorizedRole($subdomain);
        if (! $role instanceof Role) {
            return $role;
        }

        $template = $this->findTemplate($role, $hash);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $template->update(['name' => $validated['name']]);

        return redirect()->back()->with('message', __('messages.template_updated'));
    }

    /**
     * Delete a template.
     */
    public function destroy(Request $request, $subdomain, $hash)
    {
        $role = $this->authorizedRole($subdomain);
        if (! $role instanceof Role) {
            return $role;
        }

        $this->findTemplate($role, $hash)->delete();

        return redirect()->back()->with('message', __('messages.template_deleted'));
    }

    /**
     * Resolve the schedule and enforce editor + claimed + Pro access. Returns the Role
     * on success, or a redirect response to return early.
     */
    private function authorizedRole($subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();

        if (! auth()->user()->isEditor($subdomain)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        if (! $role->isClaimed()) {
            return redirect()->back()->with('error', __('messages.email_not_verified'));
        }

        if (! $role->isPro()) {
            return redirect()->back()->with('error', __('messages.pro_feature_required'));
        }

        return $role;
    }

    /**
     * Find a template scoped to the given schedule (404/403 otherwise).
     */
    private function findTemplate(Role $role, $hash): EventTemplate
    {
        $template = EventTemplate::findOrFail(UrlUtils::decodeId($hash));

        if ($template->role_id !== $role->id) {
            abort(403);
        }

        return $template;
    }

    /**
     * Template-only tweaks to the shared clone payload: clear the date (the field users
     * change each use), drop calendar-specific recurrence exceptions, keep the recurrence
     * pattern coherent, and never preset a password or carry a flyer file reference.
     */
    private function templateAdjustments(array $payload): array
    {
        $payload['event']['starts_at'] = null;
        $payload['event']['event_password'] = null;
        $payload['event']['recurring_include_dates'] = null;
        $payload['event']['recurring_exclude_dates'] = null;

        if (($payload['event']['recurring_end_type'] ?? null) === 'on_date') {
            $payload['event']['recurring_end_value'] = null;
            $payload['event']['recurring_end_type'] = 'never';
        }

        // Don't point a long-lived template at a source event's flyer file.
        $payload['flyer_image_filename'] = null;

        return $payload;
    }
}
