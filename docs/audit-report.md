# EventSchedule QA/QC Audit

## A. High-Level Architecture & Behavior Summary
- **Core models:** `User` with system roles (`auth_roles` pivot via `SystemRole`) and domain roles (`Role` for venues, curators, talent) plus events, tickets, media assets, and settings. 【F:app/Models/User.php†L92-L176】【F:app/Models/Role.php†L10-L118】
- **Relationships:** Users belong to many `Role` records through `role_user` with a `level` (owner/admin/viewer/follower); roles relate to many `Event` records through `event_role`. 【F:app/Models/User.php†L146-L198】【F:app/Models/Role.php†L88-L110】
- **RBAC implementation:** Global permissions live in `permissions` and `auth_roles` tables, cached by `AuthorizationService`, and enforced via the `ability` middleware plus helper methods `User::hasPermission()`/`canViewResource()`/`canManageResource()`. 【F:app/Services/Authorization/AuthorizationService.php†L11-L97】【F:app/Models/User.php†L230-L319】
- **Workflows:**
  - Admins assign system roles and resource scopes through `UserManagementController` forms; invitations optionally send password reset links. 【F:app/Http/Controllers/UserManagementController.php†L53-L117】
  - Venues/curators/talent are managed through `RoleController` with team membership support and membership-based event management in `EventController`. 【F:app/Http/Controllers/RoleController.php†L932-L1036】【F:app/Http/Controllers/EventController.php†L124-L169】

## B. Critical Bugs (Must-Fix)
1) **User status dropdown has no backend support**
- *Location:* `resources/views/settings/users/create-modern.blade.php` renders a “Status” selector and surfaces the value on edit. 【F:resources/views/settings/users/create-modern.blade.php†L90-L106】
- *Problem:* The controller never validates, persists, or reads a `status` attribute, and no database column exists. The dropdown misleads admins and silently discards changes.
- *Fix:* Add a nullable `status` column (e.g., enum `active`/`inactive`) to `users`, include it in `$fillable`/casts, and validate + assign it in `UserManagementController@store`/`@update` so inactive users can be filtered or blocked from login.

2) **No deactivation path—only hard deletes**
- *Location:* `UserManagementController@destroy` calls `$user->delete()` without a soft-delete or status flag, while the UI implies active/inactive management. 【F:app/Http/Controllers/UserManagementController.php†L185-L196】
- *Problem:* Admins cannot temporarily revoke access; deleting removes history and loses referential integrity for audit trails.
- *Fix:* Introduce `softDeletes` on `users` or honor the status field described above to block login while retaining history; guard authentication middleware to reject inactive/soft-deleted users.

## C. RBAC / Permission and User Management Issues
- **Status-based access missing:** Because the status field is unimplemented, admins cannot safely disable compromised accounts without deleting them, undermining least-privilege goals. (See fixes above.)
- **Route-level checks rely on ad-hoc logic:** Most role/event routes depend on manual membership checks (`canEditEvent`, `isMember`) instead of consistent `ability` middleware, making it easy to miss new endpoints during future development. Consider wrapping sensitive routes in `ability:resources.manage` (for mutations) and `ability:resources.view` (for reads) alongside membership checks. 【F:routes/web.php†L86-L187】【F:app/Http/Controllers/EventController.php†L124-L169】
- **Team access levels limited to admin/viewer on add:** `MemberAddRequest` only allows `admin` or `viewer` levels, preventing ownership transfer during creation and forcing a second step to promote to owner. Expand allowed levels or offer an explicit “owner” option in the add-member flow for clarity. 【F:app/Http/Requests/MemberAddRequest.php†L14-L20】【F:app/Http/Controllers/RoleController.php†L768-L800】

## D. General Functional & UX Issues
- **Misleading user management UI:** The presence of a Status selector with no effect confuses admins and creates false expectations about account control. (See Bug #1.)
- **Owner promotion requires hidden workflow:** Because owners cannot be set during member creation, admins must add a user as admin/viewer and then edit to owner, an unintuitive two-step process (see MemberAddRequest limitation above).

## E. Architectural & Code Quality Improvements
- **Centralize authorization:** Replace scattered `not_authorized` redirects with policy/ability checks to standardize 403 handling and reduce drift between new routes and RBAC expectations. 【F:app/Http/Controllers/EventController.php†L124-L169】
- **Align UI with domain:** Either remove the unused Status form fields or fully implement status handling in models, validation, and authentication middleware to keep UI truthful. 【F:resources/views/settings/users/create-modern.blade.php†L90-L106】【F:app/Http/Controllers/UserManagementController.php†L53-L117】

## F. Security & Data Integrity Concerns
- **Account disabling gap:** Lack of an inactive/soft-delete mechanism means compromised users remain able to authenticate until fully deleted, risking unauthorized access and loss of audit continuity. (See Bug #2.)

## G. Testing Recommendations
- **Feature test:** Create/update a user with `status=inactive` and verify authentication is blocked and list filters respect status once implemented.
- **Feature test:** Add a team member as owner in a single step (after expanding allowed levels) and assert ownership/pivot level updates correctly.
- **Authorization regression tests:** Cover representative role/event routes to ensure `ability` middleware (or policies) gates unauthorized users.

## H. Prioritized Action Plan
- **High:** Implement user status/soft-delete handling end-to-end (migration, model, controllers, auth guards) to enable safe deactivation. (Addresses Bugs #1 and #2.)
- **Medium:** Harden route authorization with consistent `ability` middleware/policies across role/event management flows.
- **Low:** Streamline team member UX by allowing owner assignment during creation and clarifying available levels.

