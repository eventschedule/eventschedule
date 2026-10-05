<?php

namespace App\Http\Controllers;

use App\Utils\SetupGuide;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * The one endpoint the setup guide writes through (App\Utils\SetupGuide holds the rules).
 *
 * Everything here changes only the signed-in user's own users.setup_guide, so there is no
 * schedule to authorise against: the guide's schedule is already pinned in that column, and a
 * request can neither name another one nor start a guide that was never started.
 */
class SetupGuideController extends Controller
{
    private const ACTIONS = ['dismiss', 'restore', 'skip', 'unskip', 'share', 'embed', 'celebrated', 'complete'];

    public function update(Request $request): JsonResponse|RedirectResponse
    {
        // Not decoration: `step` lands in a stored list, and without the allow-list any string a
        // script cared to post would be kept there.
        $validated = $request->validate([
            'action' => ['required', Rule::in(self::ACTIONS)],
            'step' => ['nullable', 'required_if:action,skip,unskip', Rule::in(SetupGuide::SKIPPABLE)],
        ]);

        $user = $request->user();
        $action = $validated['action'];
        $guide = $user->setup_guide;

        if (is_demo_mode() || ! is_array($guide) || empty($guide['role_id'])) {
            return $this->answer($request, $action);
        }

        switch ($action) {
            case 'dismiss':
                // A finished guide is not hidden, it is put away: there is nothing to bring back
                // and no line in the sidebar to bring it back with. Forgetting the session key is
                // what ends it (SetupGuide::pinned()), and dismissed_at stays "hid a guide they
                // had not finished", which is what the growth export reports it as.
                if (! empty($guide['completed_at']) || (SetupGuide::state($user)['finished'] ?? false)) {
                    SetupGuide::stamp($user, 'completed_at');
                    session()->forget('setup_guide_finished');
                    break;
                }

                SetupGuide::stamp($user, 'dismissed_at');
                break;

            case 'restore':
                SetupGuide::clear($user, 'dismissed_at');
                break;

            case 'skip':
            case 'unskip':
                SetupGuide::skip($user, $validated['step'], $action === 'skip');
                break;

            case 'share':
                SetupGuide::stamp($user, 'shared_at');
                break;

            case 'embed':
                SetupGuide::stamp($user, 'embedded_at');
                break;

            case 'celebrated':
                // Only once it is true. The page asks for this when it has shown the moment, and
                // a stray request must not spend the one celebration before the schedule is live.
                if (SetupGuide::state($user)['celebrate'] ?? false) {
                    SetupGuide::stamp($user, 'celebrated_at');
                }
                break;

            case 'complete':
                break;
        }

        // The answer that finishes the guide records the finish, in the same request. The page
        // also sends "complete" when it shows the finish, but as a second request: sent beside
        // the answer it could be judged before the answer was stored, and sent after it, it is
        // lost if the page is left first. Either way the next page would celebrate again.
        if (in_array($action, ['skip', 'share', 'embed', 'complete'], true) && (SetupGuide::state($user)['finished'] ?? false)) {
            SetupGuide::stamp($user, 'completed_at');
            // The finished guide stays a while in this session (SetupGuide::pinned()).
            session(['setup_guide_finished' => (int) $guide['role_id']]);
        }

        return $this->answer($request, $action);
    }

    /**
     * JSON to the component; a redirect to a plain form. Bringing the guide back goes to the
     * dashboard, where it lives, rather than back to wherever the sidebar happened to be open.
     */
    private function answer(Request $request, string $action): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return $action === 'restore'
            ? redirect(route('home').'#setup-guide')
            : redirect()->back();
    }
}
