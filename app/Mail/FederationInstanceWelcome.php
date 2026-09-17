<?php

namespace App\Mail;

use App\Models\FederatedInstance;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\HtmlString;

/**
 * Welcomes an operator whose install was just approved, and walks them through getting their
 * first events listed.
 *
 * Approval alone publishes nothing: every schedule on a selfhost install starts "Not decided
 * yet", and its events stay off the network until someone lists it. The old approval note said
 * "your events are now listed" to installs that had sent nothing, so this leads with where the
 * install actually stands - live, received, or nothing yet - and what to do next.
 *
 * Every link points at this site. site_url, name, app_version and contact_email come from an
 * unauthenticated registration, so a button to the install's own host would put a
 * registrant-chosen URL behind our branding. In both the HTML and the text part the host is only
 * printed, through FederatedInstance::displayHost(), which stops mail clients auto-linking it;
 * the name is never printed, and the version only when it is a plain version number.
 */
class FederationInstanceWelcome extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public FederatedInstance $instance) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('messages.federation_welcome_subject'),
            // The copy invites a reply, so make sure it reaches a person.
            replyTo: [new Address((string) config('app.support_email'))],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.federation_instance_welcome',
            text: 'emails.federation_instance_welcome_text',
            with: $this->welcomeData(),
        );
    }

    /**
     * Everything the two views need, decided once so they cannot disagree.
     *
     * Read when the mail is built, not when it is queued, so a welcome sent from the admin
     * screen long after approval reports what the install has sent since.
     */
    public function welcomeData(): array
    {
        $instance = $this->instance;

        // Listable: stored image, not blocked, not expired - and only for an approved install. A
        // welcome queued moments before a suspension is built after it, and must not report
        // listings that are hidden.
        $liveCount = $instance->isApproved() ? $instance->events()->live()->count() : 0;

        // Without the stored-image requirement: images are only fetched once an install is
        // approved, so at the moment of approval every received listing is still imageless and
        // a live-only count would always say nothing arrived. image_url is still required - a
        // fetch that failed clears it, and that row can never go live.
        $receivedCount = $instance->events()
            ->whereNull('blocked_at')
            ->whereNotNull('image_url')
            ->where('next_occurrence_at', '>=', now()->subDay())
            ->count();

        $state = $liveCount > 0 ? 'live' : ($receivedCount > 0 ? 'received' : 'empty');

        $oneClick = $instance->supportsOneClickListing();
        $required = FederatedInstance::ONE_CLICK_LISTING_VERSION;
        $installedVersion = $instance->displayVersion();

        // The app's own labels, in the operator's language, so every step names exactly what
        // they will see on screen - including the buttons and menus the steps walk through.
        $labels = [
            'setting' => __('messages.federation_schedule_toggle'),
            'option' => __('messages.federation_schedule_choice_listed'),
            'undecided' => __('messages.federation_schedule_choice_undecided'),
            'section' => __('messages.federation_settings_title'),
            'edit' => __('messages.edit_schedule'),
            'schedule_settings' => __('messages.schedule_settings'),
            'advanced' => __('messages.advanced'),
            'admin' => __('messages.admin'),
            'system' => __('messages.system'),
            'admin_settings' => __('messages.settings'),
        ];

        $guideUrl = marketing_url('/docs/selfhost/federation').'#per-schedule';

        return [
            'instance' => $instance,
            'host' => $instance->displayHost(),
            'state' => $state,
            'liveCount' => $liveCount,
            'receivedCount' => $receivedCount,
            'oneClick' => $oneClick,
            // Only for a version this site can read, and only once the release that has the
            // feature exists: this site runs that code before the selfhost release is tagged,
            // and the tip must never name a version nobody can install yet.
            'showUpdateTip' => ! $oneClick
                && $installedVersion !== null
                && FederatedInstance::versionAtLeast(config('self-update.version_installed'), $required),
            'installedVersion' => $installedVersion,
            'requiredVersion' => $required,
            'labels' => $labels,
            'ctaUrl' => $state === 'empty' ? $guideUrl : $instance->listingsUrl(),
            'ctaLabel' => $state === 'empty'
                ? __('messages.federation_welcome_cta_guide')
                : __('messages.federation_welcome_cta_listings'),
            'guideUrl' => $guideUrl,
            'updateGuideUrl' => marketing_url('/docs/selfhost/admin').'#system-app-update',
            'browseUrl' => marketing_url('/browse').'#network',
            'isRtl' => in_array(app()->getLocale(), ['ar', 'he'], true),
            'bold' => fn (string $key, array $replace = [], array $boldKeys = []) => $this->bold($key, $replace, $boldKeys),
        ];
    }

    /**
     * Translate, escape, then embolden the named replacements.
     *
     * __() does not escape what it substitutes, and the host is registrant-supplied, so the whole
     * string is escaped first and the bold values are swapped in afterwards, each escaped on its
     * own. Sentinels rather than the values themselves, so a value that happens to appear
     * elsewhere in the sentence is not emboldened twice.
     *
     * The host is marked left-to-right: Gmail drops the dir attribute from <html>, and a Latin
     * hostname inside a Hebrew or Arabic sentence otherwise reorders around its dots.
     */
    public function bold(string $key, array $replace = [], array $boldKeys = []): HtmlString
    {
        $sentinels = [];

        foreach ($boldKeys as $index => $name) {
            if (! array_key_exists($name, $replace)) {
                continue;
            }

            $sentinel = '@@BOLD'.$index.'@@';
            $sentinels[$sentinel] = '<strong'.($name === 'host' ? ' dir="ltr"' : '').' style="color: inherit;">'
                .e((string) $replace[$name])
                .'</strong>';
            $replace[$name] = $sentinel;
        }

        $html = e(__($key, $replace));

        return new HtmlString(strtr($html, $sentinels));
    }
}
