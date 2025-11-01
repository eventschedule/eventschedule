<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Utils\SimpleSpreadsheetExporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RoleContactController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'role_id' => ['required', 'integer'],
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
        ])->after(function ($validator) use ($request) {
            if ($this->isContactEmpty($request)) {
                $validator->errors()->add('name', __('messages.contact_requires_details'));
            }
        });

        if ($validator->fails()) {
            return back()
                ->withErrors($validator, 'createContact')
                ->withInput()
                ->with('open_modal', 'add-contact');
        }

        $validated = $validator->validated();

        $role = $this->findRoleForUser($request, Arr::get($validated, 'role_id'));

        $contacts = collect($role->contacts);
        $contacts->push([
            'name' => (string) ($validated['name'] ?? ''),
            'email' => (string) ($validated['email'] ?? ''),
            'phone' => (string) ($validated['phone'] ?? ''),
        ]);

        $role->contacts = $contacts->all();
        $role->save();

        return redirect()
            ->route('role.contacts')
            ->with('success', __('messages.contact_added_successfully'));
    }

    public function update(Request $request, Role $role, int $contact): RedirectResponse
    {
        $role = $this->ensureRoleForUser($request, $role);

        $validator = Validator::make($request->all(), [
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
        ])->after(function ($validator) use ($request) {
            if ($this->isContactEmpty($request)) {
                $validator->errors()->add('name', __('messages.contact_requires_details'));
            }
        });

        if ($validator->fails()) {
            return back()
                ->withErrors($validator, 'updateContact')
                ->withInput()
                ->with('open_modal', 'edit-contact-' . $role->id . '-' . $contact);
        }

        $validated = $validator->validated();

        $contacts = collect($role->contacts);

        if (! $contacts->has($contact)) {
            abort(404);
        }

        $contacts[$contact] = [
            'name' => (string) ($validated['name'] ?? ''),
            'email' => (string) ($validated['email'] ?? ''),
            'phone' => (string) ($validated['phone'] ?? ''),
        ];

        $role->contacts = $contacts->values()->all();
        $role->save();

        return redirect()
            ->route('role.contacts')
            ->with('success', __('messages.contact_updated_successfully'));
    }

    public function destroy(Request $request, Role $role, int $contact): RedirectResponse
    {
        $role = $this->ensureRoleForUser($request, $role);

        $contacts = collect($role->contacts);

        if (! $contacts->has($contact)) {
            abort(404);
        }

        $contacts->forget($contact);

        $role->contacts = $contacts->values()->all();
        $role->save();

        return redirect()
            ->route('role.contacts')
            ->with('success', __('messages.contact_deleted_successfully'));
    }

    public function export(Request $request, string $format)
    {
        $format = strtolower($format);

        if (! in_array($format, ['csv', 'xlsx'], true)) {
            abort(404);
        }

        $roles = $request->user()
            ->member()
            ->whereIn('roles.type', ['venue', 'curator', 'talent'])
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        $typeLabels = [
            'venue' => __('messages.venues'),
            'curator' => __('messages.curators'),
            'talent' => Str::plural(__('messages.talent')),
        ];

        $rows = $this->buildExportRows($roles, $typeLabels);

        $timestamp = now()->format('Ymd-His');

        if ($format === 'csv') {
            return SimpleSpreadsheetExporter::downloadCsv(
                $rows,
                'contacts-' . $timestamp . '.csv'
            );
        }

        return SimpleSpreadsheetExporter::downloadXlsx(
            $rows,
            'contacts-' . $timestamp . '.xlsx',
            __('messages.contacts')
        );
    }

    protected function findRoleForUser(Request $request, ?int $roleId): Role
    {
        if ($roleId === null) {
            abort(404);
        }

        $role = $request->user()
            ->member()
            ->whereIn('roles.type', ['venue', 'curator', 'talent'])
            ->whereKey($roleId)
            ->first();

        if (! $role || ! $role->supportsMultipleContacts()) {
            abort(403);
        }

        return $role;
    }

    protected function ensureRoleForUser(Request $request, Role $role): Role
    {
        return $this->findRoleForUser($request, $role->id);
    }

    protected function isContactEmpty(Request $request): bool
    {
        return trim((string) $request->input('name')) === ''
            && trim((string) $request->input('email')) === ''
            && trim((string) $request->input('phone')) === '';
    }

    protected function buildExportRows(Collection $roles, array $typeLabels): array
    {
        $rows = [[
            __('messages.name'),
            __('messages.email'),
            __('messages.phone'),
            __('messages.role'),
            __('messages.type'),
        ]];

        foreach ($roles as $role) {
            $contacts = collect($role->contacts);

            foreach ($contacts as $contact) {
                $rows[] = [
                    $contact['name'] ?? '',
                    $contact['email'] ?? '',
                    $contact['phone'] ?? '',
                    $role->getDisplayName(false),
                    $typeLabels[$role->type] ?? ucfirst($role->type),
                ];
            }
        }

        return $rows;
    }

}
