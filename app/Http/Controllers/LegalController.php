<?php

namespace App\Http\Controllers;

use App\Models\LegalDocument;

/**
 * Serves the privacy policy, terms of service and cookie policy.
 *
 * Each one is either operator-authored (issue #116) or the page shipped with the
 * app. This controller is the single place that decision is made, so the nexus
 * routes and the selfhost routes cannot drift apart.
 */
class LegalController extends Controller
{
    /**
     * Resolution order, the same one policy_url() applies: an external URL the
     * operator pointed at, then an in-app document, then the built-in page.
     */
    public function show(string $type)
    {
        abort_unless(in_array($type, LegalDocument::TYPES, true), 404);

        $document = LegalDocument::index()[$type] ?? null;

        // URL before content, matching policy_url(). If these two ever disagree,
        // every link in the app points somewhere this page does not.
        if ($document && $document['url']) {
            return redirect()->away($document['url']);
        }

        if ($document && $document['has_content']) {
            $rendered = LegalDocument::rendered($type);

            return view('legal.show', [
                'type' => $type,
                'title' => __('messages.legal_'.$type.'_title'),
                'html' => $rendered['html'],
                'lastUpdated' => $rendered['updated_at'],
            ]);
        }

        // Nothing overridden. The cookie policy has no built-in page - its
        // disclosure lives inside the privacy policy - so there is nothing to
        // fall back to and no marketing URL to send anyone to.
        if (! isset(LegalDocument::BUILTIN_VIEWS[$type])) {
            abort(404);
        }

        if (config('app.is_nexus')) {
            return view(LegalDocument::BUILTIN_VIEWS[$type]);
        }

        // Off the marketing instance these documents belong to eventschedule.com,
        // which is exactly where every consent link already points. Serve the
        // bundled page rather than redirecting when marketing_url() resolves back
        // to this same route (it returns a local URL in testing), which would loop.
        $fallback = marketing_url(LegalDocument::PATHS[$type]);

        return $fallback === url(LegalDocument::PATHS[$type])
            ? view(LegalDocument::BUILTIN_VIEWS[$type])
            : redirect($fallback);
    }
}
