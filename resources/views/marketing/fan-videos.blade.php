<x-marketing-layout>
    <x-slot name="title">Fan Videos & Comments for Events - Event Schedule</x-slot>
    <x-slot name="description">Let fans add YouTube videos, photos, and comments to your event pages for free, with organizer approval before anything goes live.</x-slot>
    <x-slot name="breadcrumbTitle">Fan Videos, Photos & Comments</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule Fan Videos, Photos & Comments",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Community Engagement Software",
        "operatingSystem": "Web",
        "description": "Let fans send photos, a YouTube link and a comment to your event pages for free. Every submission arrives unapproved, files itself against the part of the night and the date it came from, and only appears once you approve it.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "{{ platform_currency() }}",
            "description": "Included free"
        },
        "featureList": [
            "Fan photo uploads, JPG, PNG, GIF or WebP up to 5 MB",
            "Fan videos from a pasted YouTube link",
            "Fan comments up to 1,000 characters",
            "Approval queue on the event's Fan Content tab",
            "Submissions filed against an agenda part and an occurrence date",
            "Guest submissions with a name, an email and a bot check",
            "Per-schedule switches for comments, photos and videos",
            "Photo gallery page per event with a lightbox",
            "Per-event override of the fan content switches on the Pro plan",
            "Bulk photo download as a zip on the Pro plan"
        ],
        "url": "{{ url()->current() }}",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>
    </x-slot>

    {{-- Motion gate: hidden pre-reveal states only apply when this class is present,
         so no-JS visitors, crawlers, and reduced-motion users always see everything. --}}
    <script {!! nonce_attr() !!}>
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('es-anim');
        }
    </script>

    <style {!! nonce_attr() !!}>
        /* ==============================================================
           For fan-videos "The Reel" styles. The shared es-* motion system
           lives in marketing.css; everything below is this page's own.

           THE CONCEPT: a reel is CUT, not collected. What the audience
           shoots at a show is raw footage - a hundred frames of which
           four are worth keeping - and the product's own shape is exactly
           that of an edit bench: frames arrive, you splice the good ones
           in, and the ones you cut are gone rather than hidden. The
           metaphor and the feature story are the same sentence.

           EVERY DEVICE MAPS ONTO A REAL PRIMITIVE:
             the frame     -> EventPhoto / EventVideo / EventComment, each
                              carrying event_id, event_part_id, event_date
                              and is_approved (app/Models/EventPhoto.php)
             the splice    -> approvePhoto/Video/Comment sets
                              is_approved = true; rejectPhoto DELETES the
                              row and the stored file, so a cut frame is
                              not a hidden frame. The page says so.
             one reel per
             band          -> content hangs off an EventPart, so a
                              three-band bill yields three galleries plus
                              one for the night as a whole
                              (EventPart::approvedPhotos, and
                              Event::approvedPhotos->whereNull('event_part_id'))
             the night     -> event_date is stored per submission, and only
                              when the event actually recurs
                              (EventController::submitPhoto:
                               $eventDate = $event->days_of_week ? ... : null)

           THE SIGNATURE DEVICE IS LENGTH, NOT DECORATION. In the "three
           reels" section every frame is a FIXED width, so a strip is
           literally as long as the amount of footage that landed on that
           part of the night. The headline set's strip runs off the end of
           the openers' strip without a word of copy, which is the point a
           bar chart would have to label.

           NO SPROCKET-HOLE ILLUSTRATION. The perforation rails are a
           repeating-linear-gradient MATERIAL on ::before/::after, not an
           outline SVG drawing of a film strip - CLAUDE.md forbids
           decorative line drawings on the WP.

           WHAT THIS PAGE MUST NOT CLAIM, checked against code:
             - Vimeo and "other major platforms". The column is
               `youtube_url` and UrlUtils::getYouTubeEmbed returns false
               for anything else. YouTube only. The first-wave page said
               otherwise in its FAQ and in its JSON-LD.
             - "Fans sign in and share". fan_content_require_account
               defaults to FALSE, so a guest can post with a name, an
               email and a Turnstile check.
             - Harvesting from social platforms. Nothing is fetched from
               anywhere; a fan submits on the event page.
             - Follower notifications. A signed-in poster is ATTACHED as a
               follower, which is what makes them newsletter-able; nothing
               emails them on its own.

           ADDED BY REVIEW (each was on the page and is now corrected):
             - The per-event override of the three switches is PRO. In
               event/edit.blade.php the whole override block sits behind an
               isPro() guard on the schedule; only the schedule-level
               switches in role/edit.blade.php are free. The page said an
               event could override "any of them" with no plan tag.
               (Do not paste a Blade directive into this comment: the
               compiler reads it even inside a CSS comment and 500s.)
             - There is no pending COUNT on the Fan Content tab. The count
               lives on the dashboard's pending-actions list
               (HomeController::395-420, messages.pending_action_fan_content),
               which deep-links to ?engagement=fan_content. There is also an
               optional owner email (notification_new_fan_content, default
               OFF) via NewFanContentNotification.
             - The gallery lightbox credits `$p->user?->first_name` and falls
               back to __('messages.user'), so a GUEST photo is not credited
               by name. The page claimed it "names who sent each shot".
             - downloadPhotos zips `is_approved = true` rows only.

           COLOUR: the page's existing orange, pulled down until it
           measures on a light ground. Amber/gold, rose/red, copper and
           rust are all spent elsewhere in this campaign, so distinctness
           comes from an achromatic warm-gray ground plus ONE vermilion,
           and from the film base being the same charcoal in both modes.

           Measured (WCAG AA, sRGB):
             ground #f4f3f0   card #ffffff   night #0c0b0a   band #17140f
             ink     #14120f  16.85 ground / 18.70 card
             muted   #55514a   7.11 ground /  7.89 card
             accent  #a83a10   5.77 ground /  6.41 card
             accent2 #c2410c   4.67 ground /  5.18 card  (gradient stop)
             white on #a83a10  6.41
             dink    #f3efe9  17.17 night / 15.92 dcard / 16.03 band
             dmuted  #a8a29a   7.77 night /  7.21 dcard /  7.26 band
             lit     #fb923c   8.69 night /  8.06 dcard /  8.12 band
             #1c1917 on #fb923c 7.73   (frame marks and dark-mode buttons)
             film    #1c1917: #f3efe9 15.27, #cfc8bf 10.55, #fb923c 7.73
           NEVER text-gray-500 on this ground - it measures 4.2-4.5 on a
           tinted light ground. Use .es-reel-muted (7.11).

           FIXED PHYSICAL OBJECTS, identical with .dark on and off:
             .es-reel-film - a strip of film is a strip of film. It carries
               no `dark:` utility and holds none of the shared classes that
               flip (es-glare, es-ring-glow, es-tilt-inner, grid-overlay).
             .es-reel-band - the cutting room is dark in both modes, so the
               shared classes that flip inside it are overridden below.
           ============================================================== */

        /* --- Ground and ink --- */
        .es-reel-page { background-color: #f4f3f0; color: #14120f; }
        .dark .es-reel-page { background-color: #0c0b0a; color: #f3efe9; }
        .es-reel-ink { color: #14120f; }
        .dark .es-reel-ink { color: #f3efe9; }
        .es-reel-muted { color: #55514a; }
        .dark .es-reel-muted { color: #a8a29a; }
        .es-reel-accent { color: #a83a10; }
        .dark .es-reel-accent { color: #fb923c; }
        /* Always-lit accent, for the bands that stay dark in both modes. */
        .es-reel-lit { color: #fb923c; }
        /* Ink that only ever sits on a permanently dark band. These are real
           rules rather than `text-[#f3efe9]` utilities on purpose: an arbitrary
           Tailwind value that is not already in the built bundle cannot be
           generated without a build, so it paints NOTHING and the band inherits
           the page's dark ink. That is exactly how this page failed its first
           contrast pass, with fifteen dark-on-dark nodes. */
        .es-reel-onink { color: #f3efe9; }
        .es-reel-ondim { color: #a8a29a; }
        .es-reel-micro { font-size: 0.62rem; }

        /* Hairlines, for the same reason. */
        .es-reel-edge { border-color: rgba(20, 18, 15, 0.09); }
        .dark .es-reel-edge { border-color: rgba(243, 239, 233, 0.09); }
        .es-reel-rule { border-color: rgba(20, 18, 15, 0.1); }
        .dark .es-reel-rule { border-color: rgba(243, 239, 233, 0.12); }

        /* Dot-nav label. */
        .es-reel-tip { background: #ffffff; border-color: rgba(20, 18, 15, 0.14); color: #14120f; }
        .dark .es-reel-tip { background: #1c1917; border-color: rgba(243, 239, 233, 0.14); color: #f3efe9; }

        /* Gradient headline: dark stops on the light ground, bright stops
           in the dark, because every stop is scored against its ground. */
        .es-reel-grad {
            background-image: linear-gradient(100deg, #a83a10, #c2410c);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            color: transparent;
        }
        .dark .es-reel-grad {
            background-image: linear-gradient(100deg, #fb923c, #fdba74);
        }

        /* --- Cards --- */
        .es-reel-card {
            border: 1px solid rgba(20, 18, 15, 0.12);
            border-radius: 1rem;
            background: #ffffff;
        }
        .dark .es-reel-card {
            border-color: rgba(243, 239, 233, 0.12);
            background: rgba(243, 239, 233, 0.04);
        }
        .es-reel-band .es-reel-card {
            border-color: rgba(243, 239, 233, 0.13);
            background: rgba(243, 239, 233, 0.05);
        }

        /* --- The cutting room: dark in both colour modes --- */
        .es-reel-band {
            background-color: #100e0c;
            background-image: radial-gradient(125% 100% at 50% 0%, #1d1813 0%, #14110d 55%, #0a0908 100%);
            box-shadow: inset 0 0 90px rgba(0, 0, 0, 0.55), inset 0 1px 0 rgba(243, 239, 233, 0.05);
        }
        /* Shared classes that would otherwise flip with the colour mode. */
        .es-reel-band .grid-overlay {
            background-image:
                linear-gradient(rgba(243, 239, 233, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(243, 239, 233, 0.05) 1px, transparent 1px);
        }
        .es-reel-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-reel-band .es-claim:focus-within {
            border-color: rgba(251, 146, 60, 0.75);
            box-shadow: 0 0 0 4px rgba(251, 146, 60, 0.22);
        }

        /* --------------------------------------------------------------
           THE FILM. A fixed physical object: charcoal base, perforation
           rails top and bottom, frames in the channel between them.
           -------------------------------------------------------------- */
        .es-reel-film {
            position: relative;
            border-radius: 0.4rem;
            background-color: #1c1917;
            padding: 1.3rem 0.7rem;
            box-shadow: inset 0 0 0 1px rgba(243, 239, 233, 0.08), 0 14px 30px -18px rgba(0, 0, 0, 0.55);
        }
        .es-reel-film::before,
        .es-reel-film::after {
            content: "";
            position: absolute;
            left: 0.5rem;
            right: 0.5rem;
            height: 0.4rem;
            border-radius: 1px;
            background-image: repeating-linear-gradient(90deg, #6b625a 0 0.42rem, rgba(0, 0, 0, 0) 0.42rem 0.95rem);
            opacity: 0.8;
        }
        .es-reel-film::before { top: 0.42rem; }
        .es-reel-film::after { bottom: 0.42rem; }
        /* Shrink-to-fit, for the per-set reels. This is the whole point of that
           section: the strip must end where its footage ends, so four strips of
           different lengths sit under each other and the headline set visibly
           out-shoots the openers. A full-width base would flatten that. */
        .es-reel-film-fit { display: inline-block; }
        /* Ink that only ever sits on the film base, so it is pinned too. */
        .es-reel-film-ink { color: #f3efe9; }
        .es-reel-film-dim { color: #cfc8bf; }
        .es-reel-film-rule { border-color: rgba(243, 239, 233, 0.12); }

        /* --- Frames --- */
        .es-reel-frames {
            display: flex;
            align-items: stretch;
            gap: 0.24rem;
        }
        .es-reel-frame {
            flex: 0 0 auto;
            width: clamp(0.66rem, 1.9vw, 1.15rem);
            height: 2.5rem;
            border-radius: 0.16rem;
            background: rgba(243, 239, 233, 0.08);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        /* A frame on the reel: approved, published, playing. */
        .es-reel-frame-in { background: #fb923c; }
        /* A frame still waiting for a decision. */
        .es-reel-frame-wait {
            background: rgba(0, 0, 0, 0);
            border: 1px dashed rgba(243, 239, 233, 0.5);
            animation: es-reel-wait 2.8s ease-in-out infinite;
        }
        /* The seam left where a frame was cut out and the strip closed up.
           Drawn as a stitched line rather than a solid bar, so it reads as a
           splice and not as a gap between two frames. */
        .es-reel-splice {
            flex: 0 0 auto;
            width: 4px;
            border-radius: 1px;
            background-image: repeating-linear-gradient(180deg, #cfc8bf 0 4px, rgba(0, 0, 0, 0) 4px 7px);
        }
        /* Marks inside a frame. Abstract, functional, on a measured fill. */
        .es-reel-play {
            width: 0;
            height: 0;
            border-top: 0.24rem solid rgba(0, 0, 0, 0);
            border-bottom: 0.24rem solid rgba(0, 0, 0, 0);
            border-left: 0.38rem solid #1c1917;
        }
        .es-reel-lines {
            display: flex;
            flex-direction: column;
            gap: 2px;
            width: 60%;
        }
        .es-reel-lines span {
            display: block;
            height: 2px;
            border-radius: 1px;
            background: #1c1917;
        }
        .es-reel-lines span:last-child { width: 62%; }
        /* A hero strip reads better a little taller. */
        .es-reel-frames-lg .es-reel-frame {
            width: clamp(1.05rem, 3.6vw, 1.9rem);
            height: 3.3rem;
            border-radius: 0.2rem;
        }
        .es-reel-frames-lg .es-reel-play {
            border-top-width: 0.34rem;
            border-bottom-width: 0.34rem;
            border-left-width: 0.55rem;
        }
        /* Insurance: a long strip scrolls inside its own box rather than
           widening the page. */
        .es-reel-scroll { overflow-x: auto; }

        /* --- Night index above the reels --- */
        .es-reel-night {
            display: inline-flex;
            align-items: center;
            padding: 0.2rem 0.6rem;
            border-radius: 0.4rem;
            border: 1px solid rgba(20, 18, 15, 0.16);
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            color: #55514a;
        }
        .dark .es-reel-night { border-color: rgba(243, 239, 233, 0.18); color: #a8a29a; }
        .es-reel-night-on {
            background: #a83a10;
            border-color: #a83a10;
            color: #ffffff;
        }
        .dark .es-reel-night-on {
            background: #fb923c;
            border-color: #fb923c;
            color: #1c1917;
        }

        /* --- Eyebrow / labels --- */
        .es-reel-tag {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.26em;
            text-transform: uppercase;
            color: #55514a;
        }
        .dark .es-reel-tag { color: #a8a29a; }
        .es-reel-band .es-reel-tag { color: #fb923c; }

        /* --- Section numeral: a frame counter --- */
        .es-reel-num {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.3rem 0.8rem;
            border-radius: 0.3rem;
            border: 1px solid rgba(20, 18, 15, 0.18);
            background: #ffffff;
            color: #14120f;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-weight: 800;
            letter-spacing: 0.06em;
        }
        .dark .es-reel-num { border-color: rgba(243, 239, 233, 0.2); background: rgba(243, 239, 233, 0.05); color: #f3efe9; }
        .es-reel-band .es-reel-num { border-color: rgba(243, 239, 233, 0.2); background: rgba(243, 239, 233, 0.05); color: #f3efe9; }
        .es-reel-num::before {
            content: "";
            width: 2px;
            align-self: stretch;
            border-radius: 1px;
            background: #a83a10;
        }
        .dark .es-reel-num::before { background: #fb923c; }
        .es-reel-band .es-reel-num::before { background: #fb923c; }

        /* --- Plan tags --- */
        .es-reel-plan {
            display: inline-flex;
            align-items: center;
            flex: none;
            padding: 0.1rem 0.45rem;
            border-radius: 0.25rem;
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            border: 1px solid rgba(168, 58, 16, 0.45);
            color: #a83a10;
        }
        .dark .es-reel-plan { border-color: rgba(251, 146, 60, 0.45); color: #fb923c; }
        .es-reel-plan-pro { border-color: rgba(20, 18, 15, 0.35); color: #14120f; }
        .dark .es-reel-plan-pro { border-color: rgba(243, 239, 233, 0.38); color: #f3efe9; }

        /* --- The bench: queue rows, pinned because the band is pinned --- */
        .es-reel-row {
            border: 1px solid rgba(243, 239, 233, 0.13);
            border-radius: 0.85rem;
            background: rgba(243, 239, 233, 0.045);
        }
        .es-reel-row-live {
            border-color: rgba(251, 146, 60, 0.38);
            background: rgba(251, 146, 60, 0.1);
        }
        .es-reel-kind {
            display: inline-flex;
            align-items: center;
            flex: none;
            padding: 0.12rem 0.5rem;
            border-radius: 0.3rem;
            border: 1px solid rgba(243, 239, 233, 0.2);
            color: #f3efe9;
            font-size: 0.62rem;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }
        .es-reel-state {
            display: inline-flex;
            align-items: center;
            flex: none;
            padding: 0.12rem 0.5rem;
            border-radius: 999px;
            background: rgba(243, 239, 233, 0.12);
            color: #f3efe9;
            font-size: 0.62rem;
            font-weight: 700;
            letter-spacing: 0.06em;
        }
        .es-reel-state-live { background: #fb923c; color: #1c1917; }
        .es-reel-act {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.3rem 0.75rem;
            border-radius: 0.5rem;
            background: #fb923c;
            color: #1c1917;
            font-size: 0.72rem;
            font-weight: 800;
        }
        .es-reel-act-cut { background: rgba(243, 239, 233, 0.14); color: #f3efe9; }
        .es-reel-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 1.25rem;
            height: 1.25rem;
            padding: 0 0.3rem;
            border-radius: 999px;
            background: #fb923c;
            color: #1c1917;
            font-size: 0.68rem;
            font-weight: 800;
        }

        /* --- The switch panel --- */
        .es-reel-sw {
            position: relative;
            flex: none;
            width: 2.3rem;
            height: 1.25rem;
            border-radius: 999px;
            background: rgba(20, 18, 15, 0.18);
        }
        .dark .es-reel-sw { background: rgba(243, 239, 233, 0.18); }
        .es-reel-sw-on { background: #a83a10; }
        .dark .es-reel-sw-on { background: #fb923c; }
        .es-reel-sw-knob {
            position: absolute;
            top: 0.19rem;
            inset-inline-start: 0.19rem;
            width: 0.87rem;
            height: 0.87rem;
            border-radius: 999px;
            background: #ffffff;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        }
        .es-reel-sw-on .es-reel-sw-knob { inset-inline-start: auto; inset-inline-end: 0.19rem; }
        .dark .es-reel-sw-on .es-reel-sw-knob { background: #1c1917; }

        /* --- The intake table --- */
        .es-reel-table { width: 100%; border-collapse: collapse; }
        .es-reel-table th,
        .es-reel-table td {
            text-align: start;
            padding: 0.8rem 0.9rem;
            vertical-align: top;
            border-top: 1px solid rgba(20, 18, 15, 0.1);
        }
        .dark .es-reel-table th,
        .dark .es-reel-table td { border-color: rgba(243, 239, 233, 0.1); }
        .es-reel-table thead th { border-top: 0; padding-bottom: 0.6rem; }

        /* --- Chips --- */
        .es-reel-chip {
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
            padding: 0.35rem 0.85rem;
            border-radius: 9999px;
            border: 1px solid rgba(20, 18, 15, 0.16);
            background: rgba(255, 255, 255, 0.7);
            color: #55514a;
            font-size: 0.76rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }
        .dark .es-reel-chip {
            border-color: rgba(243, 239, 233, 0.16);
            background: rgba(243, 239, 233, 0.05);
            color: #b8b2aa;
        }

        /* --- Links and buttons --- */
        .es-reel-link { color: #a83a10; }
        .es-reel-link:hover { color: #14120f; }
        .dark .es-reel-link { color: #fb923c; }
        .dark .es-reel-link:hover { color: #f3efe9; }

        .es-reel-btn {
            background-color: #a83a10;
            color: #ffffff;
            box-shadow: 0 18px 36px -14px rgba(168, 58, 16, 0.5);
        }
        .es-reel-btn:hover { background-color: #8e2f0c; box-shadow: 0 22px 44px -14px rgba(168, 58, 16, 0.6); }
        .dark .es-reel-btn { background-color: #fb923c; color: #1c1917; }
        .dark .es-reel-btn:hover { background-color: #fdba74; }

        /* --- Hover treatment shared by FAQ rows and the related grid --- */
        .es-reel-hover:hover { border-color: rgba(168, 58, 16, 0.45); }
        .dark .es-reel-hover:hover { border-color: rgba(251, 146, 60, 0.45); }
        .es-reel-hover:hover .es-reel-hover-title,
        .es-reel-hover:hover .es-reel-hover-arrow { color: #a83a10; }
        .dark .es-reel-hover:hover .es-reel-hover-title,
        .dark .es-reel-hover:hover .es-reel-hover-arrow { color: #fb923c; }

        /* --- Shared-system recolors (brand blue by default) --- */
        .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(168, 58, 16, 0.12), rgba(0, 0, 0, 0) 60%);
        }
        .dark .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(251, 146, 60, 0.1), rgba(0, 0, 0, 0) 60%);
        }
        .es-dot:hover .es-dot-pip { background-color: rgba(168, 58, 16, 0.6); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(251, 146, 60, 0.6); }
        .es-dot.is-active .es-dot-pip { background: #a83a10; }
        .dark .es-dot.is-active .es-dot-pip { background: #fb923c; }

        /* --- Focus rings. No border-radius here: it would change the
               element's own shape on focus. Outlines already follow it. --- */
        #es-reel-page a:focus-visible,
        #es-reel-page summary:focus-visible,
        #es-reel-page button:focus-visible {
            outline: 2px solid #a83a10;
            outline-offset: 3px;
        }
        .dark #es-reel-page a:focus-visible,
        .dark #es-reel-page summary:focus-visible,
        .dark #es-reel-page button:focus-visible {
            outline-color: #fb923c;
        }
        .es-reel-band a:focus-visible,
        .es-reel-band summary:focus-visible,
        .es-reel-band button:focus-visible {
            outline-color: #fb923c !important;
        }

        /* --- Motion --- */
        @keyframes es-reel-wait {
            0%, 100% { opacity: 0.45; }
            50% { opacity: 1; }
        }
        @media (prefers-reduced-motion: reduce) {
            .es-reel-frame-wait { animation: none !important; opacity: 1; }
        }
    </style>

    @php
        // One night of a three-band bill. Frame counts are the real shape of
        // the data: photos, videos and comments land against an EventPart,
        // and anything sent without one belongs to the night as a whole.
        // Every frame is drawn, so a strip is exactly as long as its counts,
        // and the totals stay inside the free plan's 25-photo allowance.
        $reels = [
            ['Openers', 'The Wash', '8:00 to 8:40 PM', 3, 1, 4],
            ['Second', 'Kaya Nine', '8:55 to 9:40 PM', 5, 1, 6],
            ['Headline', 'Dust Radio', '10:00 to 11:20 PM', 6, 2, 7],
            ['No band', 'The night itself', 'Sent to the event, not a set', 2, 0, 3],
        ];

        // photo / video / comment / pending, plus one splice where a frame
        // was cut. Order is deliberately mixed: footage does not arrive tidy.
        $heroFrames = ['photo', 'comment', 'video', 'photo', 'splice', 'photo', 'comment', 'video', 'photo', 'wait', 'wait'];

        $intake = [
            [
                'Photo',
                'A file straight off their phone',
                'JPG, PNG, GIF or WebP, up to 5 MB. The file type is checked three ways before it is stored.',
                '10 an hour',
                'Fan Content › Photos',
            ],
            [
                'Video',
                'A YouTube link, pasted',
                'YouTube only, and only the canonical watch URL is kept. The same clip cannot be posted twice to the same set on the same night.',
                '10 an hour',
                'Fan Content › Videos',
            ],
            [
                'Comment',
                'Typed on the event page',
                'Up to 1,000 characters of plain text.',
                '20 an hour',
                'Fan Content › Comments',
            ],
        ];

        $switches = [
            ['Comments', true, 'On for a new schedule.'],
            ['Photos', true, 'On for a new schedule.'],
            ['Videos', true, 'On for a new schedule.'],
            ['Require an account', false, 'Off, so a guest can post with a name and an email.'],
        ];

        $faqs = [
            [
                'q' => 'Are fan photos, videos and comments moderated before they appear?',
                'a' => 'Yes. A fan submission is created unapproved and never reaches the public page on its own. You approve or reject each one from the Fan Content tab on the event, and your dashboard counts what is waiting across every event and links straight there. Rejecting deletes the submission rather than hiding it, and a rejected photo takes its stored file with it.',
            ],
            [
                'q' => 'What video platforms are supported?',
                'a' => 'YouTube. A fan pastes a YouTube link, Event Schedule keeps the canonical watch URL, and the clip plays on the event page through youtube-nocookie.com. Links from anywhere else are turned away with an error rather than stored, and nothing is ever fetched from a social platform on its own.',
            ],
            [
                'q' => 'Where do approved photos, videos and comments appear?',
                'a' => 'On the event page, inside the part of the night they were sent from. A three-band bill ends up with three galleries plus one for the event as a whole, and photos also get a page of their own at the event URL followed by /photos, with a lightbox that shows when each shot arrived and credits the poster by first name when they were signed in.',
            ],
            [
                'q' => 'Do fans need an account?',
                'a' => 'Not by default. A fan can post with a name, an email address and a bot check. Turn on the require-an-account switch for your schedule if you would rather they sign in first. Either way the queue shows you who sent it, including the address a guest typed in, and that address is never shown on a public page.',
            ],
            [
                'q' => 'What does it cost?',
                'a' => 'Fan photos, videos and comments are on the free plan, approval queue and per-set galleries included. On eventschedule.com the free plan caps a schedule at 25 fan photos in total, and Pro removes the cap and adds a download of every approved photo on an event as a single zip. A selfhosted install has no photo cap at all.',
            ],
            [
                'q' => 'Can I switch it off, or only part of it?',
                'a' => 'Each of the three has its own switch on the schedule, so you can take photos and comments and leave video out. That is on the free plan; overriding the switches for one individual event is a Pro feature. Either way, a draft or internal event accepts nothing from the public, and an unlisted event only accepts submissions from your own team.',
            ],
            [
                'q' => 'What happens to the people who post?',
                'a' => 'A fan who is signed in when they submit is added to your schedule as a follower, which puts them on the list the next time you write a newsletter. Newsletters are free and the monthly allowance counts recipients: 10 on the free plan, 100 on Pro and 1,000 on Enterprise. Nothing is emailed to them automatically.',
            ],
        ];

        $dotSections = [
            ['top', 'The reel'],
            ['cut', 'The cut'],
            ['intake', 'Three channels'],
            ['reels', 'Three reels'],
            ['plays', 'Where it plays'],
            ['control', 'The switches'],
            ['more', 'Keep exploring'],
            ['faq', 'Questions'],
            ['claim', 'Start free'],
        ];
    @endphp

    <div id="es-reel-page" class="es-reel-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: one night's reel                                    -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero noise relative flex min-h-[calc(88svh-4rem)] scroll-mt-24 items-center overflow-hidden py-16">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(168, 58, 16, 0.2), rgba(168, 58, 16, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 45%, rgba(251, 146, 60, 0.14), rgba(251, 146, 60, 0) 65%);"></div>
            <div class="es-spot absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-[1.05fr_1fr]">
                <div>
                    <div class="es-fade-up es-d-1 glass mb-8 inline-flex items-center gap-3 rounded-full px-5 py-2.5">
                        <svg aria-hidden="true" class="es-reel-accent h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        <span class="es-reel-muted text-sm font-medium tracking-wide">Fan videos, photos &amp; comments</span>
                    </div>

                    <h1 class="es-balance es-reel-ink mb-8 text-[2.4rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <span class="es-mask"><span class="es-mask-line">The audience already</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="es-reel-grad">shot your show.</span></span></span>
                    </h1>

                    <p class="es-fade-up es-d-2 es-reel-muted mb-10 max-w-xl text-lg sm:text-xl">
                        Photos from the front, a YouTube link to the encore, a sentence about the night. Fans send it to the event page it belongs to, and nothing goes on the reel until you splice it in.
                    </p>

                    <div class="es-fade-up es-d-3 flex flex-col items-start gap-4 sm:flex-row">
                        <a href="{{ app_url('/sign_up') }}" class="es-reel-btn group inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-2xl px-7 py-4 text-base font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            Get started free
                            <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                        <a href="{{ route('marketing.docs.creating_events') }}#fan-content" class="glass group inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-2xl px-6 py-4 text-base font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
                            Read the Fan Content guide
                            <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </a>
                    </div>
                </div>

                <!-- The reel: what came in from one set, and what is still waiting. -->
                <div class="es-fade-up es-d-4" data-reveal>
                    <div class="es-reel-card p-5 sm:p-6">
                        <div class="mb-4 flex flex-wrap items-baseline justify-between gap-2">
                            <h2 class="es-reel-ink text-base font-bold">Dust Radio, headline set</h2>
                            <span class="es-reel-muted font-mono text-xs">Fri, Mar 13</span>
                        </div>

                        <div class="es-reel-scroll" aria-hidden="true">
                            <div class="es-reel-film">
                                <div class="es-reel-frames es-reel-frames-lg">
                                    @foreach ($heroFrames as $frame)
                                        @if ($frame === 'splice')
                                            <span class="es-reel-splice"></span>
                                        @else
                                            <span class="es-reel-frame @if ($frame === 'wait') es-reel-frame-wait @else es-reel-frame-in @endif">
                                                @if ($frame === 'video')
                                                    <span class="es-reel-play"></span>
                                                @elseif ($frame === 'comment')
                                                    <span class="es-reel-lines"><span></span><span></span></span>
                                                @endif
                                            </span>
                                        @endif
                                    @endforeach
                                </div>
                                <div class="es-reel-film-rule mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 border-t pt-2.5 es-reel-micro font-mono">
                                    <span class="es-reel-film-ink font-bold">9 on the reel</span>
                                    <span class="es-reel-film-dim">2 waiting</span>
                                    <span class="es-reel-film-dim">1 cut</span>
                                </div>
                            </div>
                        </div>

                        <dl class="mt-5 grid grid-cols-2 gap-x-4 gap-y-2 text-xs sm:grid-cols-4">
                            <div>
                                <dt class="es-reel-muted">Solid</dt>
                                <dd class="es-reel-ink font-semibold">Approved</dd>
                            </div>
                            <div>
                                <dt class="es-reel-muted">Play mark</dt>
                                <dd class="es-reel-ink font-semibold">A YouTube link</dd>
                            </div>
                            <div>
                                <dt class="es-reel-muted">Dashed</dt>
                                <dd class="es-reel-ink font-semibold">Waiting on you</dd>
                            </div>
                            <div>
                                <dt class="es-reel-muted">Seam</dt>
                                <dd class="es-reel-ink font-semibold">Cut, and gone</dd>
                            </div>
                        </dl>

                        <p class="es-reel-muted mt-5 es-reel-rule border-t pt-4 text-xs">
                            Every frame carries the set it was shot at, and on a recurring show the date it was shot on too, so the encore does not end up filed under the openers.
                        </p>
                    </div>
                </div>
            </div>

            <div class="es-fade-up es-d-4 mx-auto mt-14 max-w-3xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1">
                        <div class="es-marquee-track">
                            @for ($chipCopy = 0; $chipCopy < 2; $chipCopy++)
                                @foreach (['Photos', 'YouTube links', 'Comments', 'Per set', 'Per night', 'Approval queue', 'Guest or signed in', 'Free plan'] as $chip)
                                    <span @if ($chipCopy === 1) aria-hidden="true" @endif class="es-reel-chip">{{ $chip }}</span>
                                @endforeach
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. The cut (fixed-dark band): the approval queue             -->
    <!-- ============================================================ -->
    <section id="cut" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-reel-band noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-6xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-reel-num mb-6" data-reveal aria-hidden="true"><span>02</span></div>
                    <p class="es-reel-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The cut</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        A reel is <span class="es-reel-lit">cut</span>, not collected.
                    </h2>
                    <p class="mt-5 es-reel-ondim text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        Nothing a fan sends is live when it arrives. It waits on the event's Fan Content tab until you decide, which is the whole difference between a comments section and an edit.
                    </p>
                </div>

                <div class="mb-10 grid gap-6 md:grid-cols-3" data-reveal-group="110">
                    <div class="es-reel-card p-6" data-reveal="panel">
                        <p class="es-reel-tag mb-3">Arrives</p>
                        <h3 class="mb-2 es-reel-onink text-lg font-bold">Unapproved</h3>
                        <p class="es-reel-ondim text-sm">A fan's photo, link or comment is stored with its approval flag off. The public page does not show it. If the fan was signed in they can see their own while it waits, so nobody wonders whether it sent.</p>
                    </div>
                    <div class="es-reel-card p-6" data-reveal="panel">
                        <p class="es-reel-tag mb-3">Approve</p>
                        <h3 class="mb-2 es-reel-onink text-lg font-bold">One button, one frame</h3>
                        <p class="es-reel-ondim text-sm">Approving publishes that one submission, in the set and on the night it came from. Your own team's uploads skip the queue: anyone who can edit the event is approved on arrival.</p>
                    </div>
                    <div class="es-reel-card p-6" data-reveal="panel">
                        <p class="es-reel-tag mb-3">Reject</p>
                        <h3 class="mb-2 es-reel-onink text-lg font-bold">Cut, and gone</h3>
                        <p class="es-reel-ondim text-sm">Rejecting deletes the submission instead of hiding it, and a rejected photo's file is deleted with it. There is no shadow archive of things you turned down.</p>
                    </div>
                </div>

                <!-- The bench: the queue as the event page's Fan Content tab shows it. -->
                <div class="es-reel-card mx-auto max-w-3xl p-5 sm:p-7" data-reveal="panel">
                    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                        <span class="es-reel-onink text-sm font-bold">Fan Content</span>
                        <span class="es-reel-tag">Approval queue</span>
                    </div>

                    <div class="space-y-3">
                        <div class="es-reel-row p-4">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="mb-1.5 flex flex-wrap items-center gap-2">
                                        <span class="es-reel-kind">Photo</span>
                                        <span class="es-reel-state">Pending</span>
                                    </div>
                                    <p class="es-reel-ondim font-mono text-xs">Headline &middot; Mar 13 &middot; Submitted by Sarah M.</p>
                                </div>
                                <div class="flex flex-none gap-2" aria-hidden="true">
                                    <span class="es-reel-act">Approve</span>
                                    <span class="es-reel-act es-reel-act-cut">Reject</span>
                                </div>
                            </div>
                        </div>

                        <div class="es-reel-row p-4">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="mb-1.5 flex flex-wrap items-center gap-2">
                                        <span class="es-reel-kind">Video</span>
                                        <span class="es-reel-state">Pending</span>
                                        <span class="es-reel-state">Guest</span>
                                    </div>
                                    <p class="break-words es-reel-ondim font-mono text-xs">Encore &middot; Mar 13 &middot; Submitted by Marcus R. (marcus@example.com)</p>
                                </div>
                                <div class="flex flex-none gap-2" aria-hidden="true">
                                    <span class="es-reel-act">Approve</span>
                                    <span class="es-reel-act es-reel-act-cut">Reject</span>
                                </div>
                            </div>
                        </div>

                        <div class="es-reel-row es-reel-row-live p-4">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="mb-1.5 flex flex-wrap items-center gap-2">
                                        <span class="es-reel-kind">Comment</span>
                                        <span class="es-reel-state es-reel-state-live">On the page</span>
                                    </div>
                                    <p class="mb-1 es-reel-onink text-sm">&ldquo;Best set I have seen there all year.&rdquo;</p>
                                    <p class="es-reel-ondim font-mono text-xs">Second &middot; Mar 13 &middot; Submitted by Dana K.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 flex items-start gap-3">
                        <span class="es-reel-count mt-px" aria-hidden="true">2</span>
                        <p class="es-reel-ondim text-xs leading-relaxed">
                            Your dashboard counts what is waiting across every event and links straight to this tab, and you can switch on an email for the moment new fan content lands.
                        </p>
                    </div>

                    <p class="mt-3 es-reel-ondim text-xs leading-relaxed">
                        The queue names the set, the date and the sender, and shows you the email address a guest typed in. That address stays here: it is never printed on a public page.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. Three channels: the intake spec, as a record              -->
    <!-- ============================================================ -->
    <section id="intake" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-reel-num mb-6" data-reveal aria-hidden="true"><span>03</span></div>
                <p class="es-reel-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Three channels</p>
                <h2 class="es-balance es-reel-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Three things a fan can <span class="es-reel-grad">send you.</span>
                </h2>
                <p class="es-reel-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    No app, no account needed, no upload of a two-gigabyte video. A file, a link, or a sentence.
                </p>
            </div>

            <div class="es-reel-card p-4 sm:p-7" data-reveal="panel">
                <div class="overflow-x-auto">
                    <table class="es-reel-table">
                        <caption class="sr-only">What a fan can submit to an event, how it arrives, its limits, its rate limit, and the switch that controls it</caption>
                        <thead>
                            <tr class="es-reel-tag">
                                <th scope="col">Channel</th>
                                <th scope="col">How it arrives</th>
                                <th scope="col">Limits</th>
                                <th scope="col" class="whitespace-nowrap">Per visitor</th>
                                <th scope="col">Switch</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($intake as [$inKind, $inHow, $inLimit, $inRate, $inSwitch])
                                <tr>
                                    <th scope="row" class="es-reel-ink whitespace-nowrap text-sm font-bold">{{ $inKind }}</th>
                                    <td class="es-reel-muted text-sm">{{ $inHow }}</td>
                                    <td class="es-reel-muted text-sm">{{ $inLimit }}</td>
                                    <td class="es-reel-muted whitespace-nowrap font-mono text-xs">{{ $inRate }}</td>
                                    <td class="es-reel-muted whitespace-nowrap text-sm">{{ $inSwitch }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="es-reel-muted mt-4 px-2 text-xs sm:px-0">
                    All three are on the free plan. The rate limits are per visitor per hour and exist so one bad night cannot become a thousand rows.
                </p>
            </div>

            <div class="mt-8 grid gap-4 md:grid-cols-3" data-reveal-group="90">
                <div class="es-reel-card p-6" data-reveal="panel">
                    <h3 class="es-reel-ink mb-2 text-base font-bold">A link, not an upload</h3>
                    <p class="es-reel-muted text-sm">Video comes in as a YouTube URL, so a nine-minute clip costs your schedule nothing and plays through youtube-nocookie.com when it goes live.</p>
                </div>
                <div class="es-reel-card p-6" data-reveal="panel">
                    <h3 class="es-reel-ink mb-2 text-base font-bold">No duplicate clip</h3>
                    <p class="es-reel-muted text-sm">Links are compared by video id, not by text, so the long form of a URL and its short form are recognised as the same clip on the same set and night.</p>
                </div>
                <div class="es-reel-card p-6" data-reveal="panel">
                    <h3 class="es-reel-ink mb-2 text-base font-bold">Photos are checked</h3>
                    <p class="es-reel-muted text-sm">An upload has to pass its extension, its reported type and an actual image read before it is stored, so a renamed file does not get in.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. Three reels: length carries the information               -->
    <!-- ============================================================ -->
    <section id="reels" class="scroll-mt-24 es-reel-edge border-y py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-reel-num mb-6" data-reveal aria-hidden="true"><span>04</span></div>
                <p class="es-reel-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Three reels</p>
                <h2 class="es-balance es-reel-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                    A three-band bill makes <span class="es-reel-grad">three reels.</span>
                </h2>
                <p class="es-reel-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Fan content hangs off a part of the agenda, not off the evening. Put the running order on the event and every set gets its own gallery, with a fourth for the night as a whole.
                </p>
            </div>

            <div class="es-reel-card p-5 sm:p-8" data-reveal="panel">
                <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex flex-wrap items-center gap-2" aria-hidden="true">
                        <span class="es-reel-night">Feb 27</span>
                        <span class="es-reel-night">Mar 6</span>
                        <span class="es-reel-night es-reel-night-on">Mar 13</span>
                    </div>
                    <span class="es-reel-tag">One night of a weekly show</span>
                </div>

                <div class="space-y-6">
                    @foreach ($reels as [$slot, $act, $when, $nPhoto, $nVideo, $nComment])
                        <div>
                            <div class="mb-2 flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1">
                                <h3 class="es-reel-ink text-sm font-bold">
                                    {{ $act }}
                                    <span class="es-reel-muted font-normal">&middot; {{ $slot }}</span>
                                </h3>
                                <span class="es-reel-muted font-mono text-xs">{{ $when }}</span>
                            </div>

                            <div class="es-reel-scroll" aria-hidden="true">
                                <div class="es-reel-film es-reel-film-fit">
                                    <div class="es-reel-frames">
                                        @for ($f = 0; $f < $nPhoto; $f++)
                                            <span class="es-reel-frame es-reel-frame-in"></span>
                                        @endfor
                                        @for ($f = 0; $f < $nVideo; $f++)
                                            <span class="es-reel-frame es-reel-frame-in"><span class="es-reel-play"></span></span>
                                        @endfor
                                        @for ($f = 0; $f < $nComment; $f++)
                                            <span class="es-reel-frame es-reel-frame-in"><span class="es-reel-lines"><span></span><span></span></span></span>
                                        @endfor
                                    </div>
                                </div>
                            </div>

                            <p class="es-reel-muted mt-2 font-mono text-xs">
                                {{ $nPhoto }} photos &middot; {{ $nVideo }} {{ $nVideo === 1 ? 'video' : 'videos' }} &middot; {{ $nComment }} comments
                            </p>
                        </div>
                    @endforeach
                </div>

                <p class="es-reel-muted mt-7 es-reel-rule border-t pt-5 text-sm">
                    Every frame is drawn, so each strip is exactly as long as what landed on it. That is the headline set out-shooting the openers close to two to one, and it is the kind of thing you only see once the footage files itself.
                </p>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-2" data-reveal-group="90">
                <div class="es-reel-card p-6" data-reveal="panel">
                    <div class="mb-2 flex flex-wrap items-center gap-2">
                        <h3 class="es-reel-ink text-base font-bold">The night is part of the filing</h3>
                        <span class="es-reel-plan">Free</span>
                    </div>
                    <p class="es-reel-muted text-sm">A weekly show is one recurring event, so a submission stores the date it was sent for. Last Friday's reel stays last Friday's, and the page you are looking at shows that night's.</p>
                </div>
                <div class="es-reel-card p-6" data-reveal="panel">
                    <div class="mb-2 flex flex-wrap items-center gap-2">
                        <h3 class="es-reel-ink text-base font-bold">No running order? One reel</h3>
                        <span class="es-reel-plan">Free</span>
                    </div>
                    <p class="es-reel-muted text-sm">An event without an agenda takes everything against the event itself, which is the fourth strip above. Add sets later and new submissions start landing on them.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Where it plays                                            -->
    <!-- ============================================================ -->
    <section id="plays" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-reel-num mb-6" data-reveal aria-hidden="true"><span>05</span></div>
                <p class="es-reel-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Where it plays</p>
                <h2 class="es-balance es-reel-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Once it is on the reel, it <span class="es-reel-grad">plays everywhere.</span>
                </h2>
            </div>

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="80">
                <div class="es-reel-card flex flex-col p-6" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-reel-ink text-base font-bold">In the set it belongs to</h3>
                        <span class="es-reel-plan">Free</span>
                    </div>
                    <p class="es-reel-muted text-sm">Approved photos, videos and comments render inside their part of the agenda on the event page, so the encore clip sits under the encore.</p>
                </div>

                <div class="es-reel-card flex flex-col p-6" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-reel-ink text-base font-bold">A gallery of its own</h3>
                        <span class="es-reel-plan">Free</span>
                    </div>
                    <p class="es-reel-muted text-sm">Every event with fan photos gets a photo gallery page at its URL followed by /photos, with a lightbox that shows when each shot arrived, credits a signed-in poster by first name, and a share button.</p>
                </div>

                <div class="es-reel-card flex flex-col p-6" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-reel-ink text-base font-bold">Their own, while it waits</h3>
                        <span class="es-reel-plan">Free</span>
                    </div>
                    <p class="es-reel-muted text-sm">A signed-in fan sees their pending submission on the event page and nobody else does, which saves you the message asking whether it went through.</p>
                </div>

                <div class="es-reel-card flex flex-col p-6" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-reel-ink text-base font-bold">Embeds that behave</h3>
                        <span class="es-reel-plan">Free</span>
                    </div>
                    <p class="es-reel-muted text-sm">A fan video plays through youtube-nocookie.com with a strict referrer policy, and only the canonical watch URL is stored, so nothing a fan pasted onto the end of a link is kept.</p>
                </div>

                <div class="es-reel-card flex flex-col p-6" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-reel-ink text-base font-bold">The whole night as a zip</h3>
                        <span class="es-reel-plan es-reel-plan-pro">Pro</span>
                    </div>
                    <p class="es-reel-muted text-sm">Download every approved photo on an event in one archive, for the poster, the grant report or the band who asked. Pro also lifts the free plan's photo cap.</p>
                </div>

                <div class="es-reel-card flex flex-col p-6" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-reel-ink text-base font-bold">One feed for your own site</h3>
                        <span class="es-reel-plan es-reel-plan-pro">Pro</span>
                    </div>
                    <p class="es-reel-muted text-sm">The REST API returns comments, photos and videos as one list, filtered by event, night or type. Approved rows by default, or the pending queue if you ask for it.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. The switches                                              -->
    <!-- ============================================================ -->
    <section id="control" class="scroll-mt-24 es-reel-edge border-t py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-start gap-12 lg:grid-cols-[1fr_1.05fr]">
                <div>
                    <div class="es-reel-num mb-6" data-reveal aria-hidden="true"><span>06</span></div>
                    <p class="es-reel-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The switches</p>
                    <h2 class="es-balance es-reel-ink mb-5 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                        Four switches, and the <span class="es-reel-grad">defaults are sane.</span>
                    </h2>
                    <p class="es-reel-muted mb-6 text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        Comments, photos and videos each have their own switch on the schedule's Engagement settings, so you can take photos and leave video out. Pro adds a per-event override, so one night can differ from the rest.
                    </p>
                    <ul class="es-reel-muted space-y-3 text-sm" data-reveal style="--reveal-delay: 0.2s;">
                        <li class="flex gap-2.5">
                            <span class="es-reel-accent mt-px flex-none font-bold" aria-hidden="true">&rarr;</span>
                            <span>A guest submission needs a name, a valid email address and a bot check before it is accepted.</span>
                        </li>
                        <li class="flex gap-2.5">
                            <span class="es-reel-accent mt-px flex-none font-bold" aria-hidden="true">&rarr;</span>
                            <span>Turn on require-an-account and a fan is sent to sign in first, with what they were sending held and posted for them afterwards.</span>
                        </li>
                        <li class="flex gap-2.5">
                            <span class="es-reel-accent mt-px flex-none font-bold" aria-hidden="true">&rarr;</span>
                            <span>Draft and internal events take nothing from the public, and an unlisted event only takes submissions from your own team.</span>
                        </li>
                        <li class="flex gap-2.5">
                            <span class="es-reel-accent mt-px flex-none font-bold" aria-hidden="true">&rarr;</span>
                            <span>A fan who is signed in when they post is added as a follower, so they are on the list next time you write a newsletter.</span>
                        </li>
                    </ul>
                </div>

                <div class="space-y-4">
                    <div class="es-reel-card p-5 sm:p-7" data-reveal="panel">
                        <p class="es-reel-tag mb-4">Engagement &rsaquo; Fan Content</p>
                        <div class="space-y-3">
                            @foreach ($switches as [$swName, $swOn, $swNote])
                                <div class="flex items-start gap-3.5">
                                    <span class="es-reel-sw mt-0.5 @if ($swOn) es-reel-sw-on @endif" aria-hidden="true"><span class="es-reel-sw-knob"></span></span>
                                    <div class="min-w-0">
                                        <p class="es-reel-ink text-sm font-semibold">{{ $swName }}</p>
                                        <p class="es-reel-muted text-xs">{{ $swNote }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <p class="es-reel-muted mt-5 es-reel-rule border-t pt-4 text-xs">
                            Every schedule type has this tab: talent, venue and curator alike.
                        </p>
                    </div>

                    <div class="es-reel-card p-5 sm:p-7" data-reveal="panel">
                        <div class="mb-3 flex flex-wrap items-center gap-2">
                            <h3 class="es-reel-ink text-base font-bold">The photo allowance, plainly</h3>
                        </div>
                        <dl class="space-y-3 text-sm">
                            <div class="flex items-baseline justify-between gap-3">
                                <dt class="es-reel-muted">Free, on eventschedule.com</dt>
                                <dd class="es-reel-ink flex-none font-mono font-bold">25 photos</dd>
                            </div>
                            <div class="flex items-baseline justify-between gap-3">
                                <dt class="es-reel-muted">Pro, plus the zip download</dt>
                                <dd class="es-reel-ink flex-none font-mono font-bold">No cap</dd>
                            </div>
                            <div class="flex items-baseline justify-between gap-3">
                                <dt class="es-reel-muted">Selfhosted</dt>
                                <dd class="es-reel-ink flex-none font-mono font-bold">No cap</dd>
                            </div>
                        </dl>
                        <p class="es-reel-muted mt-4 text-xs">
                            The free cap counts photos across the whole schedule, not per event, and it only applies to photos. Videos are links and comments are text, so neither is capped.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('marketing.partials.pricing-nudge')

    <!-- ============================================================ -->
    <!-- 7. Keep exploring                                            -->
    <!-- ============================================================ -->
    <section id="more" class="scroll-mt-24 es-reel-edge border-t py-20">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-reel-num mb-6" data-reveal aria-hidden="true"><span>07</span></div>
                <h2 class="es-balance es-reel-ink mb-4 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    Keep <span class="es-reel-grad">exploring</span>
                </h2>
                <p class="es-reel-muted text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    See what else runs on your event pages.
                </p>
            </div>

            <div class="mb-10 grid gap-4 md:grid-cols-3" data-reveal-group="90">
                <a href="{{ marketing_url('/open-source') }}" class="es-reel-hover es-reel-card group flex flex-col p-7 transition-all duration-200 hover:shadow-md" data-reveal>
                    <h3 class="es-reel-hover-title es-reel-ink mb-3 text-xl font-bold transition-colors">Open Source &amp; API</h3>
                    <p class="es-reel-muted mb-4 text-sm">100% open source. Selfhost on your own server, where the fan photo cap does not apply, or read the fan-content feed over the REST API.</p>
                    <span class="es-reel-hover-arrow es-reel-link mt-auto inline-flex items-center gap-2 text-sm font-semibold transition-all group-hover:gap-3">
                        Learn more
                        <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </span>
                </a>

                <a href="{{ marketing_url('/features') }}" class="es-reel-hover es-reel-card group flex flex-col p-7 transition-all duration-200 hover:shadow-md" data-reveal>
                    <h3 class="es-reel-hover-title es-reel-ink mb-3 text-xl font-bold transition-colors">View all features</h3>
                    <p class="es-reel-muted mb-4 text-sm">Calendar sync, embeds, recurring events and RSVPs are free alongside this, and so is selling up to 25 tickets a month, scanning them at the door included. Pro takes the ceiling off and adds the live check-in dashboard, with zero platform fees on every plan.</p>
                    <span class="es-reel-hover-arrow es-reel-link mt-auto inline-flex items-center gap-2 text-sm font-semibold transition-all group-hover:gap-3">
                        See features
                        <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </span>
                </a>

                <div class="es-reel-card flex flex-col p-7" data-reveal>
                    <h3 class="es-reel-ink mb-4 text-xl font-bold">Popular with</h3>
                    <div class="space-y-2.5">
                        <a href="{{ marketing_url('/for-musicians') }}" class="es-reel-hover es-reel-card group/link flex items-center justify-between p-3 transition-all">
                            <span class="es-reel-hover-title es-reel-ink text-sm font-semibold transition-colors">Musicians</span>
                            <svg aria-hidden="true" class="es-reel-hover-arrow es-reel-muted h-4 w-4 transition-colors rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </a>
                        <a href="{{ marketing_url('/for-comedians') }}" class="es-reel-hover es-reel-card group/link flex items-center justify-between p-3 transition-all">
                            <span class="es-reel-hover-title es-reel-ink text-sm font-semibold transition-colors">Comedians</span>
                            <svg aria-hidden="true" class="es-reel-hover-arrow es-reel-muted h-4 w-4 transition-colors rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </a>
                        <a href="{{ marketing_url('/for-dance-groups') }}" class="es-reel-hover es-reel-card group/link flex items-center justify-between p-3 transition-all">
                            <span class="es-reel-hover-title es-reel-ink text-sm font-semibold transition-colors">Dance Groups</span>
                            <svg aria-hidden="true" class="es-reel-hover-arrow es-reel-muted h-4 w-4 transition-colors rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </a>
                    </div>
                </div>
            </div>

            <div class="mx-auto max-w-3xl">
                <h3 class="es-reel-ink mb-6 text-center text-lg font-bold" data-reveal>Related features</h3>
                <div class="space-y-3" data-reveal-group="80">
                    <div data-reveal>
                        <x-feature-link-card
                            name="Newsletters"
                            description="Email the followers a fan submission just added, with open rates"
                            :url="marketing_url('/features/newsletters')"
                            icon-color="orange"
                        >
                            <x-slot:icon>
                                <svg aria-hidden="true" class="w-5 h-5 text-orange-700 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </x-slot:icon>
                        </x-feature-link-card>
                    </div>
                    <div data-reveal>
                        <x-feature-link-card
                            name="Online Events"
                            description="One link on the event, for the nights the room is a stream"
                            :url="marketing_url('/features/online-events')"
                            icon-color="sky"
                        >
                            <x-slot:icon>
                                <svg aria-hidden="true" class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                            </x-slot:icon>
                        </x-feature-link-card>
                    </div>
                    <div data-reveal>
                        <x-feature-link-card
                            name="Embed Calendar"
                            description="Put your schedule on the website you already have"
                            :url="marketing_url('/features/embed-calendar')"
                            icon-color="teal"
                        >
                            <x-slot:icon>
                                <svg aria-hidden="true" class="w-5 h-5 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                                </svg>
                            </x-slot:icon>
                        </x-feature-link-card>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. FAQ                                                       -->
    <!-- ============================================================ -->
    <x-seo.faq-schema :items="$faqs" />

    <section id="faq" class="scroll-mt-24 es-reel-edge border-t py-20 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <div class="es-reel-num mb-6" data-reveal aria-hidden="true"><span>08</span></div>
                <h2 class="es-balance es-reel-ink mb-4 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    Frequently asked questions
                </h2>
                <p class="es-reel-muted text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    Everything you need to know about fan videos, photos and comments.
                </p>
            </div>

            <div class="space-y-3" data-reveal-group="80">
                @foreach ($faqs as $faqIndex => $faq)
                    <details name="faq" class="es-reel-hover es-reel-card group p-6 transition-all duration-200" data-reveal>
                        <summary class="es-reel-ink flex cursor-pointer items-start gap-3 font-semibold">
                            <span class="es-reel-accent flex-none font-mono text-sm font-bold" aria-hidden="true">{{ str_pad($faqIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="es-reel-hover-title flex-1 transition-colors">{{ $faq['q'] }}</span>
                            <svg aria-hidden="true" class="es-reel-muted mt-0.5 h-5 w-5 flex-none transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <p class="faq-answer es-reel-muted mt-4 leading-relaxed ps-9">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 9. Finale                                                    -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-reel-band noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>

                <div class="relative z-10">
                    <p class="es-reel-tag mb-4">Free forever</p>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Give the audience somewhere to <span class="es-reel-lit">send it.</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl es-reel-ondim text-lg">
                        Photos, a YouTube link and a comment, filed against the set and the night they came from, live only once you say so. No credit card required.
                    </p>

                    <div class="mx-auto mb-10 max-w-md" aria-hidden="true">
                        <div class="es-reel-scroll">
                            <div class="es-reel-film">
                                <div class="es-reel-frames es-reel-frames-lg justify-center">
                                    <span class="es-reel-frame es-reel-frame-in"></span>
                                    <span class="es-reel-frame es-reel-frame-in"><span class="es-reel-play"></span></span>
                                    <span class="es-reel-frame es-reel-frame-in"></span>
                                    <span class="es-reel-frame es-reel-frame-in"><span class="es-reel-lines"><span></span><span></span></span></span>
                                    <span class="es-reel-splice"></span>
                                    <span class="es-reel-frame es-reel-frame-in"></span>
                                    <span class="es-reel-frame es-reel-frame-wait"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="es-reel-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            <span class="relative z-10 flex items-center gap-2">
                                Start for free
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>

                    <p class="mt-6 es-reel-ondim text-sm">No credit card required</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Desktop dot nav -->
    <nav class="es-dotnav fixed top-1/2 z-40 hidden -translate-y-1/2 lg:block ltr:right-5 rtl:left-5" aria-label="Page sections">
        <ul class="glass flex flex-col items-center gap-1.5 rounded-full px-2 py-3">
            @foreach ($dotSections as [$sectionId, $sectionLabel])
                <li class="relative">
                    <a href="#{{ $sectionId }}" class="es-dot group block rounded-full" aria-label="{{ $sectionLabel }}">
                        <span class="es-dot-pip block h-2 w-2 rounded-full bg-gray-400/60 dark:bg-white/30"></span>
                        <span class="pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full es-reel-tip border px-3 py-1 text-xs font-medium opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3">{{ $sectionLabel }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    </div>

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
