# Role group name error on new installs

## What we know
- `RoleController::create()` renders the `role/edit` form with a brand new `Role` instance and no preloaded group collection, so any group data reaching the view can only come from the current request (via `old()` or the JavaScript that adds groups dynamically).【F:app/Http/Controllers/RoleController.php†L812-L894】
- The Blade template iterates over `old('groups', $groups)` and blindly dereferences `$group->name` whenever the element is an object. If the payload is a decoded `stdClass` without the `name` property, PHP raises `Undefined property: stdClass::$name`.【F:resources/views/role/edit.blade.php†L1116-L1133】

Because this is a fresh installation, the exception cannot be blamed on legacy database rows. Instead, it is most likely caused by one of:

1. **Malformed request/session payloads.** The "Add subschedule" UI builds nested inputs like `groups[new_0][name]`. If a validation error sends the user back, Laravel rehydrates that payload. Any JSON transformations (e.g., browser auto-fill extensions, a JS enhancement, or API-driven group creation) that convert the structure into plain objects would hit the Blade dereference path.
2. **Third-party integrations.** Webhooks or API clients that post to the same endpoint might send objects with `id`/`slug` only. On a clean database, that is enough to trigger the crash the very first time someone opens the form after such a request.

## Suggested next steps (prior to touching data)
1. **Harden the view.** Replace raw `$group->name` reads with `data_get($group, 'name', '')` (and the equivalent for `name_en`, `slug`, etc.), or cast objects to arrays before the loop. That guarantees the form renders even when the session contains object payloads.
2. **Normalize in the controller/request.** Before calling `view('role/edit')`, coerce `old('groups')` (or any inbound `groups` array) into a predictable shape—e.g., map every element to an array with default keys. Alternatively, add a `prepareForValidation()` hook in `RoleCreateRequest` that enforces `groups.*.name` and casts non-array entries to arrays.
3. **Instrument for visibility.** Temporarily log the structure of `old('groups')` when it contains objects so you can confirm which client flow is introducing `stdClass` instances on a brand-new install.

These changes remove the crash path without relying on seeded data, and they make future troubleshooting easier if an external integration is supplying incomplete group payloads.
