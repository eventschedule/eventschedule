<?php

namespace App\Http\Controllers;

use App\Models\LegalDocument;
use App\Services\AuditService;
use App\Utils\MarkdownUtils;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Admin legal-page manager: lets a super-admin replace the built-in privacy
 * policy, terms of service and cookie policy with their own, so an install can
 * meet the rules that apply where it operates - GDPR, PAIA, POPIA (issue #116).
 *
 * Each document is either a link to a policy hosted elsewhere or Markdown
 * written here. LegalController and policy_url() consume the result.
 */
class AdminLegalController extends Controller
{
    public function index()
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        return view('admin.legal', [
            'documents' => LegalDocument::query()->get()->keyBy('type'),
        ]);
    }

    /**
     * Each document posts to its own endpoint. Sharing one action across the
     * three panels would mean every panel had to carry the other two's values
     * as hidden inputs, and a miss would silently wipe them.
     */
    public function update(Request $request, string $type): RedirectResponse
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        abort_unless(in_array($type, LegalDocument::TYPES, true), 404);

        $request->validate([
            // Rendered into an href on public pages, so the scheme is pinned
            // rather than trusted: Laravel's `url` rule accepts javascript:.
            'url' => ['nullable', 'string', 'max:255', 'url', 'starts_with:http://,https://'],
            'content' => ['nullable', 'string', 'max:65535'],
        ]);

        if (is_demo_mode()) {
            return redirect()->route('admin.legal')->with('error', __('messages.demo_mode_settings_disabled'));
        }

        $url = trim((string) $request->input('url')) ?: null;
        $content = trim((string) $request->input('content')) ?: null;

        // A document that HTML Purifier strips to nothing (an HTML-comment-only
        // draft, a lone <style> paste) would otherwise save "successfully" and
        // then serve a blank policy page that every consent link points at. Tell
        // the operator instead of shipping an empty privacy policy.
        if ($content !== null && blank(MarkdownUtils::convertToHtml($content, tables: true))) {
            throw ValidationException::withMessages([
                'content' => __('messages.legal_document_content_empty'),
            ]);
        }

        $document = LegalDocument::firstOrNew(['type' => $type]);
        $old = ['url' => $document->url, 'content_length' => strlen((string) $document->content)];

        $document->url = $url;
        $document->content = $content;
        $document->save();

        AuditService::log(
            AuditService::ADMIN_LEGAL_UPDATE,
            auth()->id(),
            null,
            null,
            $old,
            // The document body is not logged: it can be 60 KB, and the audit
            // trail only needs to record that it changed and by how much.
            ['url' => $url, 'content_length' => strlen((string) $content)],
            'Updated the '.$type.' legal page',
        );

        return redirect()->route('admin.legal')
            ->with('success', __('messages.legal_document_saved'))
            ->withFragment($type);
    }
}
