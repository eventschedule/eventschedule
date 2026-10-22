{{-- The page's background as it was drawn before it had a layer of its own: on `body` itself.

     Kept for one case, a Pro owner whose custom CSS styles the page's background on html, body or
     :root (Role::customCssStylesPageBackground()), as marketing/custom-css told them to: their
     override was written against these declarations, and a layer would cover it. Every other
     page draws partials/guest-background. The rules are unchanged, so those pages look as they
     did, the calendar's scrolling picture included.

     $bg is the schedule whose background this is ($otherRole on an event page that wears it). --}}
            background-attachment: {{ (($event && !request()->embed && !request()->graphic) || $role->activeEventLayout() === 'list') ? 'fixed' : 'scroll' }};
            @if ($bg->background == 'gradient')
                background-image: linear-gradient({{ $bg->background_rotation }}deg, {{ $bg->background_colors }});
            @elseif ($bg->background == 'image')
                @if (!$showMobileBackground)
                @media (min-width: 768px) {
                @endif
                    @if ($bg->background_image)
                        background-image:
                            @if (request()->graphic)
                                linear-gradient(rgba(255, 255, 255, 0.5), rgba(255, 255, 255, 0.5)),
                            @endif
                        url("{{ css_url(asset('images/backgrounds/' . $bg->background_image . '.webp')) }}");
                        background-image:
                            @if (request()->graphic)
                                linear-gradient(rgba(255, 255, 255, 0.5), rgba(255, 255, 255, 0.5)),
                            @endif
                        image-set(
                            url("{{ css_url(asset('images/backgrounds/' . $bg->background_image . '.webp')) }}") type("image/webp"),
                            url("{{ css_url(asset('images/backgrounds/' . $bg->background_image . '.png')) }}") type("image/png")
                        );
                    @elseif ($showMobileBackground)
                        background-image:
                            @if (request()->graphic)
                                linear-gradient(rgba(255, 255, 255, 0.5), rgba(255, 255, 255, 0.5)),
                            @endif
                        url("{{ css_url($bg->backgroundImageUrl(960, pageWidth: true)) }}");
                        @media (min-width: 768px) {
                            background-image:
                                @if (request()->graphic)
                                    linear-gradient(rgba(255, 255, 255, 0.5), rgba(255, 255, 255, 0.5)),
                                @endif
                            url("{{ css_url($bg->backgroundImageUrl(1920, pageWidth: true)) }}");
                        }
                    @else
                        background-image:
                            @if (request()->graphic)
                                linear-gradient(rgba(255, 255, 255, 0.5), rgba(255, 255, 255, 0.5)),
                            @endif
                        url("{{ css_url($bg->backgroundImageUrl(1920, pageWidth: true)) }}");
                    @endif
                    background-size: cover;
                    background-position: center;
                    height: 100%;
                    margin: 0;
                @if (!$showMobileBackground)
                }
                @endif
            @endif
