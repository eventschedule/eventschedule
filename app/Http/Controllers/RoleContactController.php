<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

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

        if ($format === 'csv') {
            return $this->exportCsv($rows);
        }

        return $this->exportXlsx($rows);
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

    protected function exportCsv(array $rows): StreamedResponse
    {
        $response = new StreamedResponse(function () use ($rows) {
            $handle = fopen('php://output', 'w');

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set(
            'Content-Disposition',
            'attachment; filename="contacts-' . now()->format('Ymd-His') . '.csv"'
        );

        return $response;
    }

    protected function exportXlsx(array $rows)
    {
        $path = tempnam(sys_get_temp_dir(), 'contacts');

        if ($path === false) {
            abort(500, 'Unable to create temporary file.');
        }

        $zip = new ZipArchive();

        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            @unlink($path);
            abort(500, 'Unable to create spreadsheet archive.');
        }

        $zip->addFromString('[Content_Types].xml', $this->contentTypesXml());
        $zip->addFromString('_rels/.rels', $this->rootRelsXml());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRelsXml());
        $zip->addFromString('xl/workbook.xml', $this->workbookXml());
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->sheetXml($rows));

        $zip->close();

        $response = response()->download($path, 'contacts-' . now()->format('Ymd-His') . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);

        return $response->deleteFileAfterSend(true);
    }

    protected function contentTypesXml(): string
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
    <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
    <Default Extension="xml" ContentType="application/xml"/>
    <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
    <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
</Types>
XML;
    }

    protected function rootRelsXml(): string
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>
XML;
    }

    protected function workbookRelsXml(): string
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
</Relationships>
XML;
    }

    protected function workbookXml(): string
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
    <sheets>
        <sheet name="Contacts" sheetId="1" r:id="rId1"/>
    </sheets>
</workbook>
XML;
    }

    protected function sheetXml(array $rows): string
    {
        $xmlRows = [];

        foreach ($rows as $rowIndex => $row) {
            $cells = [];

            foreach ($row as $columnIndex => $value) {
                $columnLetter = $this->columnLetter($columnIndex + 1);
                $cells[] = '<c r="' . $columnLetter . ($rowIndex + 1) . '" t="inlineStr"><is><t>'
                    . htmlspecialchars((string) $value, ENT_XML1 | ENT_COMPAT, 'UTF-8')
                    . '</t></is></c>';
            }

            $xmlRows[] = '<row r="' . ($rowIndex + 1) . '">' . implode('', $cells) . '</row>';
        }

        $sheetData = implode('', $xmlRows);

        return <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
    <sheetData>{$sheetData}</sheetData>
</worksheet>
XML;
    }

    protected function columnLetter(int $index): string
    {
        $letter = '';

        while ($index > 0) {
            $index--;
            $letter = chr(($index % 26) + 65) . $letter;
            $index = intdiv($index, 26);
        }

        return $letter;
    }
}
