<x-app-admin-layout>

@php
  // Set by EventController::create() only. A first event gets a slimmer form: the step
  // indicator, no Boost or locked upgrade controls, and the rarely used sections folded away.
  $isFirstEventRun = $isFirstEventRun ?? false;

  // The setup guide (App\Utils\SetupGuide) is docked beside this form for someone on their way to
  // a first live event. For them the line under the save button says what saving does, in place
  // of the general note that events are public.
  $setupGuidePromise = $isFirstEventRun && \App\Utils\SetupGuide::surface() === 'dock';

  // Participants, Agenda and Engagement fold behind "More options" on a first event: seven
  // sections is a lot to face before anything is saved, and none of these three is needed to
  // publish. Vue shows them again on request (showMoreSections), and showSection() opens the
  // fold itself when something - a #hash, an invalid field - has to reach one of them.
  $moreSectionAttrs = $isFirstEventRun ? 'v-cloak v-show="showMoreSections"' : '';
  // The sections that carry those attributes, for the script: it opens the fold when something has
  // to reach one of them. Built here because a list with commas cannot be written inside the JSON
  // directive, which splits its argument on them.
  $foldedSectionIds = $isFirstEventRun
      ? ['section-participants', 'section-agenda', 'section-gallery', 'section-listing', 'section-engagement', 'section-event-settings']
      : [];

  // The Event tab's own gate. Its nav link, its section and the Listing tab (which holds fields that
  // used to sit on it) all ask this, so the three cannot diverge.
  $detailsShown = ! $role->isVenue() || $user->isMember($role->subdomain) || $user->canEditEvent($event);

  // Google and Outlook sync share one tab; either is enough to show it.
  $showGoogleSync = $event->exists && $event->canBeSyncedToGoogleCalendarForSubdomain(request()->subdomain);
  $showMicrosoftSync = $event->exists && $event->canBeSyncedToMicrosoftCalendarForSubdomain(request()->subdomain);

  // About is folded until asked for, except when the save came back with something wrong inside it.
  $aboutOpenOnLoad = $errors->hasAny(['short_description', 'description'])
      || collect($errors->keys())->contains(fn ($key) => str_starts_with($key, 'custom_field'));

  // The venue's contact fields and the rest of its address are folded too, on the same terms.
  $venueContactOpenOnLoad = $errors->hasAny(['venue_email', 'venue_phone']);
  $venueMoreOpenOnLoad = $errors->hasAny(['venue_state', 'venue_postal_code', 'venue_country_code', 'venue_website']);

  // Which tab each error of a refused save belongs to, by the field it is keyed on. Worked out
  // here because most rules have no inline message in this view to find: a save refused over a
  // promo code or a payment method used to come back with every tab looking fine. A key that
  // matches nothing belongs to the Event tab.
  $errorTabPrefixes = [
      'section-tickets' => ['tickets', 'promo_codes', 'addons', 'payment_', 'installment', 'ticket_', 'rsvp_', 'coupon_', 'registration_url', 'terms_url', 'expire_unpaid', 'custom_fields', 'seating_plan', 'total_tickets'],
      'section-participants' => ['members', 'member_'],
      'section-agenda' => ['event_parts', 'agenda_'],
      'section-listing' => ['slug', 'category_id', 'event_password', 'curators', 'curator_', 'is_draft', 'is_private', 'is_internal'],
      'section-event-settings' => ['sponsor', 'event_sponsor', 'existing_event_sponsors', 'new_event_sponsor'],
      'section-engagement' => ['fan_', 'feedback_', 'polls'],
  ];
  $errorSectionIds = [];
  foreach ($errors->keys() as $errorKey) {
      $errorTab = 'section-details';
      foreach ($errorTabPrefixes as $tabId => $prefixes) {
          foreach ($prefixes as $prefix) {
              if (str_starts_with($errorKey, $prefix)) {
                  $errorTab = $tabId;
                  break 2;
              }
          }
      }
      $errorSectionIds[$errorTab] = true;
  }
  $errorSectionIds = array_keys($errorSectionIds);

  // The Tickets tab has tabs of its own, and an error on a promo code is not on the one it opens
  // with. The first of these a refused save has an error under is the one to open.
  $ticketTabOnError = null;
  foreach ([
      'promo_codes' => 'promo_codes', 'addons' => 'add_ons',
      'payment_' => 'payment', 'installment' => 'payment', 'ticket_currency_code' => 'payment',
      'terms_url' => 'options', 'expire_unpaid' => 'options', 'custom_fields' => 'options', 'ticket_notes' => 'options',
      'tickets' => 'tickets', 'seating_plan' => 'tickets', 'total_tickets' => 'tickets',
  ] as $prefix => $innerTab) {
      if (collect($errors->keys())->contains(fn ($key) => str_starts_with($key, $prefix))) {
          $ticketTabOnError = $innerTab;
          break;
      }
  }

  // The words the tab summaries and the save bar are built from. One array through one JSON
  // directive: translated text never goes into a Vue attribute.
  $tabLabels = [
      'tabs' => [
          'section-details' => __('messages.event'),
          'section-tickets' => __('messages.tickets'),
          'section-participants' => __('messages.participants'),
          'section-agenda' => __('messages.agenda'),
          'section-gallery' => __('messages.gallery'),
          'section-listing' => __('messages.listing'),
          'section-calendar-sync' => __('messages.calendar_sync'),
          'section-engagement' => __('messages.engagement'),
          'section-event-settings' => __('messages.sponsors'),
      ],
      'visibility' => [
          'public' => __('messages.public'),
          'draft' => __('messages.draft'),
          'internal' => __('messages.internal'),
          'unlisted' => __('messages.unlisted'),
      ],
      'visibility_label' => __('messages.visibility'),
      'unsaved' => __('messages.unsaved'),
      'check' => __('messages.check_tabs'),
      'signups_confirm' => __('messages.signups_exist_confirm'),
      'saving_removes_tickets' => __('messages.saving_removes_ticket_types'),
      'saving_removes_addons' => __('messages.saving_removes_event_addons'),
      'saving_hides' => __('messages.saving_hides_event'),
      'saving_publishes' => __('messages.saving_publishes_event'),
      'save' => __('messages.save'),
      'save_draft' => __('messages.save_draft'),
      'save_first' => __('messages.save_changes_first'),
      'sold' => __('messages.sold'),
      'tickets' => __('messages.tickets'),
      'free' => __('messages.free'),
      'registration' => __('messages.registration'),
      'limit' => __('messages.limit'),
      'no_tickets' => __('messages.no_tickets'),
      'participants_prompt' => __('messages.participants_prompt'),
      'agenda_prompt' => __('messages.agenda_prompt'),
      'gallery_prompt' => __('messages.gallery_add_first'),
      'engagement_prompt' => __('messages.engagement_prompt'),
      'about_prompt' => __('messages.about_prompt'),
      'polls' => __('messages.polls'),
      'to_review' => __('messages.to_review'),
      'same_as_schedule' => __('messages.same_as_schedule'),
      'no_unsaved' => __('messages.no_unsaved_changes'),
      'none' => __('messages.none'),
      'publish' => __('messages.publish'),
      'unsaved_changes' => __('messages.unsaved_changes'),
      'saving_removes_times' => __('messages.saving_removes_agenda_times'),
      'saving_removes_sponsors' => __('messages.saving_removes_event_sponsors'),
      'import_found_nothing' => __('messages.agenda_import_found_nothing'),
      'sponsor_needs_logo' => __('messages.sponsor_needs_logo'),
      'engagement' => [
          'names' => [
              'fan_comments_enabled' => __('messages.fan_comments_enabled'),
              'fan_photos_enabled' => __('messages.fan_photos_enabled'),
              'fan_videos_enabled' => __('messages.fan_videos_enabled'),
          ],
          'on' => mb_strtolower(__('messages.enabled')),
          'off' => mb_strtolower(__('messages.disabled')),
          'enabled' => __('messages.enabled'),
          'disabled' => __('messages.disabled'),
          'polls_prompt' => __('messages.polls_row_prompt'),
          'fan_prompt' => __('messages.fan_content_row_prompt'),
          'feedback_prompt' => __('messages.feedback_row_prompt'),
          'poll_unfinished' => __('messages.poll_needs_question_and_options'),
          'settings_on_plan' => (bool) $role->isPro(),
      ],
      // The switches on the Tickets tab's Options row, named in its one-line summary.
      'options' => [
          'ask_phone' => __('messages.ask_for_phone_number'),
          'individual_tickets' => __('messages.individual_tickets'),
          'sell_after_start' => __('messages.sell_after_start'),
          'sales_dates' => __('messages.configure_sales_dates'),
          'show_unavailable' => __('messages.show_unavailable_tickets'),
          'custom_fields' => __('messages.custom_fields'),
          'ticket_notes' => __('messages.ticket_notes'),
          'registration_notes' => __('messages.registration_notes'),
          'terms_url' => __('messages.terms_url'),
          'prompt' => __('messages.ticket_options_prompt'),
      ],
      'no_sponsors' => __('messages.no_sponsors'),
      'sponsors' => __('messages.sponsors'),
      'copy' => __('messages.copy'),
      'copied' => __('messages.copied'),
  ];

  // Sync changes reload the page, so these two are fixed for the life of it.
  $calendarSummary = collect([
      $showGoogleSync ? 'Google: '.($event->isSyncedToGoogleCalendarForSubdomain(request()->subdomain) ? __('messages.synced') : __('messages.not_synced')) : null,
      $showMicrosoftSync ? 'Outlook: '.($event->isSyncedToMicrosoftCalendarForSubdomain(request()->subdomain) ? __('messages.synced') : __('messages.not_synced')) : null,
  ])->filter()->implode(' · ');

  // What About says when folded: the short description, or failing that the start of the long one.
  $aboutSummary = trim((string) old('short_description', $event->short_description))
      ?: \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags((string) $event->description_html))), 140);

  // The house segmented control, same strings as role/partials/appointment-editor.blade.php.
  // $segRadio keeps a real radio (keyboard, arrow keys, screen reader) and paints only
  // the sibling span, so no :class binding is needed to show the selection.
  $segShell = 'inline-flex flex-wrap items-center gap-1 rounded-xl bg-gray-100 dark:bg-gray-800 p-1';
  $segIdle = 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300';
  $segItem = 'rounded-lg px-3 py-1.5 text-sm font-medium transition-all duration-200';
  $segRadio = $segItem.' block cursor-pointer '.$segIdle
      .' peer-checked:bg-white dark:peer-checked:bg-gray-900 peer-checked:text-gray-900 dark:peer-checked:text-white'
      .' peer-checked:shadow-[inset_0_2px_4px_rgba(0,0,0,0.08)]'
      .' peer-focus-visible:ring-2 peer-focus-visible:ring-[var(--brand-blue)]';
  // No opacity: gray-500 on the group's gray-100 is already only ~4.4:1, and
  // dimming it further drops it to ~2.6:1. The padlock carries the locked signal.
  // focus-visible (not focus) so a mouse click does not leave a ring behind, matching
  // the radios' peer-focus-visible.
  $segLocked = $segItem.' '.$segIdle.' inline-flex items-center gap-1.5'
      .' focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)]';

  // The photo gallery (partials/gallery-editor). Its plan gate is the event's OWNING schedule
  // (ticketingRole()), not the one this form was reached through, because that is the one whose
  // plan decides whether guests see it. edit: everything; downgraded: the plan lapsed with photos
  // in it, so they can only be removed; locked: nothing to edit yet; readonly: demo mode.
  $galleryRole = ($event->exists ? $event->ticketingRole() : null) ?? $role;
  $galleryUnlocked = $galleryRole->isPro();
  $galleryCanUpgrade = config('app.hosted') && $user->isEditor($galleryRole->subdomain);
  $galleryState = \App\Utils\GalleryUtils::editorState($galleryRole, $event, $user);
  $galleryMode = is_demo_mode() ? 'readonly' : ($galleryUnlocked ? 'edit' : (count($galleryState['images']) ? 'downgraded' : 'locked'));
  // A first event gets no locked upgrade controls, and nobody gets a section they cannot save.
  $galleryShown = (! $event->exists || $user->can('update', $event))
      && ! ($isFirstEventRun && $galleryMode === 'locked');
  $galleryFanPhotos = ($approvedPhotos ?? collect())->map(fn ($photo) => [
      'id' => \App\Utils\UrlUtils::encodeId($photo->id),
      'url' => $photo->photo_url,
      'name' => $photo->submitterName(),
  ])->values();

  // The Tickets panel is shown only to someone who may see the event's ticket setup, and the
  // page's Vue data has to hold to the same rule: it used to carry every ticket type, promo code,
  // add-on and the coupon and payment text regardless, so a curator's staff who were refused the
  // panel could read all of it in the page source. $eventForPage is what data() spreads; the
  // model itself is untouched, because the rest of this view still reads it.
  $canSeeTicketData = ! $event->exists || $user->canViewEventData($event);
  $eventForPage = $canSeeTicketData ? $event : (clone $event)
      ->unsetRelation('tickets')->unsetRelation('promoCodes')->unsetRelation('addons')
      ->makeHidden(array_merge(\App\Repos\EventRepo::TICKET_PANEL_FIELDS, ['payment_instructions_html', 'ticket_notes_html']));

  // Whether anything has been sold, which is all the Sales link in the Tickets tab's title line asks.
  // Whether anything has been sold, which is all the Sales link beside the Tickets row asks.
  // tickets.sold is a JSON map of date to count, never a number.
  $ticketsSoldOnLoad = 0;
  if ($event->exists && $canSeeTicketData) {
      foreach ($event->tickets as $ticketRow) {
          $ticketsSoldOnLoad += array_sum(array_map('intval', (array) json_decode($ticketRow->sold ?: '[]', true)));
      }
  }
@endphp

{{-- The step band, for the guest-submit flow only. A first event of one's own is walked by the
     setup guide instead, which the admin layout includes (partials/setup-guide). --}}
@if(session('pending_request'))
    <div class="my-6">
        <x-step-indicator :currentStep="3" />
    </div>
@endif

@php
  // The gateway registry, consulted three ways on the Payment tab: which gateways this owner has
  // actually connected (drives the setup nudge), which of those can take this event's currency
  // (drives the dropdown), and what each one supports (drives the Vue gates further down).
  $paymentGateways = payment_gateways();
  $connectedGateways = $paymentGateways->connectedFor($user);
  $selectableGateways = $paymentGateways->availableFor($user, $event->ticket_currency_code);

  // Connected is not the same question as usable HERE. connectedFor() is currency-blind, so an owner
  // whose only gateway cannot settle this event's currency - Payfast is rand-only, and a selfhost
  // install can supply it to everyone from .env - passes the "has connected something" test while the
  // dropdown offers nothing but cash. Without this they would be shown a healthy-looking form and
  // publish a paid event that can only be settled by hand.
  $onlineGateways = array_diff_key($selectableGateways, ['cash' => true]);

  // What the Tickets tab's Payment row says when a price has nowhere to go: the same two questions,
  // in the same order, as the Payment pane it opens. "Connect Stripe" is wrong advice for an owner
  // who IS connected to something that cannot take this currency, so it is never sent to one.
  // Every tab's column: 48rem, and the narrow one on a first event (the setup guide stands beside it).
  $tabCol = $isFirstEventRun ? 'max-w-xl' : 'event-tickets-wide';
  // Visibility as the form last had it. After a save the server refused, that is what was CHOSEN,
  // not what is stored: read from the event alone, Draft chosen and a save refused over another
  // field came back as Public, and the next save published the event.
  $draftNow = (bool) old('is_draft', $event->is_draft);
  $privateNow = (bool) old('is_private', $event->is_private);
  $internalNow = (bool) old('is_internal', $event->is_internal);
  $visibilityNow = $internalNow ? 'internal' : ($draftNow ? 'draft' : ($privateNow ? 'unlisted' : 'public'));
  // What the Save button reads once the page's script runs (saveLabel), for the moment before.
  $saveLabelOnLoad = in_array($visibilityNow, ['draft', 'internal'], true)
      ? __('messages.save_draft')
      : (! $event->exists && $visibilityNow === 'public' ? __('messages.publish') : __('messages.save'));
  // The same for "Also list on": the boxes as they were ticked, when the form is coming back.
  $curatorsFromOld = old('curators_submitted') ? array_map('strval', (array) old('curators', [])) : null;
  // And for the venue: the one that was CHOSEN before the refusal, or none if it was taken off.
  // Read from the stored event alone, another venue chosen and a save refused over another field
  // came back on the old venue, with nothing to say so, and the next save kept the event there;
  // on a new event it came back with no venue at all. Only a venue this page offers is looked up:
  // toData() is the whole schedule, and the id is whatever was posted.
  // What is STORED stays what a change is measured against (hasKeyChange): measured against the
  // venue the page came back on, a venue changed before the refusal would no longer be a change,
  // and the question about telling the people who signed up would not be asked.
  $savedVenueId = $event->exists && $selectedVenue ? \App\Utils\UrlUtils::encodeId($selectedVenue->id) : null;
  if (old('venue_submitted')) {
      $venueFromOld = (string) old('venue_id', '');
      $venueWasOffered = $venueFromOld !== '' && collect($venues)->contains('id', $venueFromOld);
      $selectedVenue = $venueWasOffered ? \App\Models\Role::find(\App\Utils\UrlUtils::decodeId($venueFromOld)) : null;
  }
  // The Engagement tab's four settings ("" same as the schedule, "1" on, "0" off), as last chosen.
  $engagementSettingsNow = [];
  foreach (['fan_comments_enabled', 'fan_photos_enabled', 'fan_videos_enabled', 'feedback_enabled'] as $engagementField) {
      $engagementStored = is_null($event->$engagementField) ? '' : ($event->$engagementField ? '1' : '0');
      $engagementSettingsNow[$engagementField] = session()->hasOldInput($engagementField) ? (string) (old($engagementField) ?? '') : $engagementStored;
  }
  // What was typed into the Tickets tab before a save the server refused, laid over the stored
  // event in the page's data (the last spread in data().event). Only the choice used to come
  // back: a limit typed and refused came back blank, and the next save made registration
  // unlimited; a link typed under "Tickets elsewhere" came back as "Not needed".
  // Read from the flashed input itself, key by key: a field posted blank is there as null, which
  // is "cleared", while a field that was not posted keeps what is stored. Empty on an ordinary
  // load, and for someone refused the Tickets panel, whose post carries none of these.
  $ticketPanelPosted = $canSeeTicketData && session()->hasOldInput('tickets_enabled');
  $ticketFieldsTyped = [];
  if ($ticketPanelPosted) {
      $postedNow = (array) session()->getOldInput();
      $choiceCameBack = old('tickets_enabled') ? 'tickets' : (old('rsvp_enabled') ? 'rsvp' : 'external');
      $elsewhereFields = ['registration_url', 'ticket_price', 'coupon_code', 'coupon_discount_type', 'coupon_discount'];
      $typedKinds = [
          'rsvp_limit' => 'number', 'registration_url' => 'text', 'ticket_price' => 'number',
          'coupon_code' => 'text', 'coupon_discount_type' => 'choice', 'coupon_discount' => 'number',
          'ticket_currency_code' => 'choice', 'payment_method' => 'choice', 'payment_instructions' => 'text',
          'total_tickets_mode' => 'choice', 'seating_plan_id' => 'text',
          'installments_enabled' => 'switch', 'installment_count' => 'number',
          'installment_final_days_before' => 'number', 'installment_min_order_amount' => 'number',
          'ask_phone' => 'switch', 'require_phone' => 'switch', 'country_code_phone' => 'switch',
          'individual_tickets' => 'switch', 'individual_ticket_fields' => 'switch',
          'sell_after_start' => 'switch', 'show_unavailable_tickets' => 'switch',
          'expire_unpaid_tickets' => 'number', 'ticket_notes' => 'text', 'terms_url' => 'text',
      ];
      foreach ($typedKinds as $typedField => $typedKind) {
          if (! array_key_exists($typedField, $postedNow) || is_array($postedNow[$typedField])) {
              continue;
          }
          // The fields of a choice that is not on screen ride in hidden ones. One of those the
          // server refused goes back to what is saved: nobody could see it to fix it, and it
          // would refuse every save after this one the same way.
          if ($choiceCameBack !== 'external' && in_array($typedField, $elsewhereFields, true) && $errors->has($typedField)) {
              continue;
          }
          $typedValue = $postedNow[$typedField];
          // A choice with nothing chosen keeps the page's own default for it.
          if ($typedKind === 'choice' && ($typedValue === null || $typedValue === '')) {
              continue;
          }
          $ticketFieldsTyped[$typedField] = match ($typedKind) {
              'switch' => (bool) $typedValue,
              'number' => is_numeric($typedValue) ? $typedValue + 0 : null,
              default => $typedValue === null ? null : (string) $typedValue,
          };
      }
  }
  $ticketFieldNow = fn (string $field) => array_key_exists($field, $ticketFieldsTyped) ? $ticketFieldsTyped[$field] : $event->$field;
  // "Tickets elsewhere" is the choice on screen when it holds something, as typed or as stored.
  $externalChosenNow = $canSeeTicketData
      && (filled($ticketFieldNow('registration_url')) || filled($ticketFieldNow('ticket_price')) || filled($ticketFieldNow('coupon_code')));
  $showExpireUnpaidNow = $ticketFieldNow('expire_unpaid_tickets') > 0;
  // The "tickets elsewhere" fields as the event holds them, which a half-typed one falls back to
  // when another choice is made (chooseTickets). What is SAVED, so never from the typed fields.
  // Nothing of the ticket setup goes to someone refused the Tickets panel, who has no such fields.
  $savedExternalNow = $canSeeTicketData ? [
      'registration_url' => (string) $event->registration_url,
      'ticket_price' => $event->ticket_price,
      'coupon_code' => (string) $event->coupon_code,
      'coupon_discount' => $event->coupon_discount,
  ] : new \stdClass;
  // What "same as the schedule" comes to for each, for the rows' one-line summaries.
  $engagementSchedule = $event->exists ? ($event->roles->first(fn ($r) => $r->isTalent()) ?? $event->roles->first() ?? $role) : $role;
  $engagementInherited = [];
  foreach (['fan_comments_enabled' => true, 'fan_photos_enabled' => true, 'fan_videos_enabled' => true, 'feedback_enabled' => false] as $engagementField => $engagementDefault) {
      $engagementInherited[$engagementField] = (bool) ($engagementSchedule->$engagementField ?? $engagementDefault);
  }
  // A save the server refused gives the lists back as they were being edited, not as they are
  // stored. Each is read only when its own tab was part of that post (its marker field).
  $selectedMembersNow = $selectedMembers ?? [];
  if (old('members_submitted')) {
      $knownMembers = collect($selectedMembers ?? [])->merge($members ?? [])->keyBy('id');
      $selectedMembersNow = [];
      foreach ((array) old('members', []) as $postedId => $postedMember) {
          $knownMember = (array) ($knownMembers->get((string) $postedId) ?? ['id' => (string) $postedId, 'user_id' => null, 'url' => null]);
          // Only an unclaimed participant's details are editable here; a claimed schedule's are its own.
          $selectedMembersNow[] = empty($knownMember['user_id']) ? array_merge($knownMember, [
              'name' => (string) ($postedMember['name'] ?? ($knownMember['name'] ?? '')),
              'email' => (string) ($postedMember['email'] ?? ''),
              'phone' => (string) ($postedMember['phone'] ?? ''),
              'youtube_url' => (string) ($postedMember['youtube_url'] ?? ''),
          ]) : $knownMember;
      }
  }
  $eventPartsNow = $event->exists ? $event->parts : ($clonedParts ?? []);
  if (session()->hasOldInput('agenda_show_times')) {
      $eventPartsNow = collect((array) old('event_parts', []))->map(fn ($postedPart) => [
          'id' => $postedPart['id'] ?? '',
          'name' => (string) ($postedPart['name'] ?? ''),
          'description' => (string) ($postedPart['description'] ?? ''),
          'start_time' => (string) ($postedPart['start_time'] ?? ''),
          'end_time' => (string) ($postedPart['end_time'] ?? ''),
      ])->values()->all();
  }
  // A refused save gives the ticket types, promo codes and add-ons back as they were typed. The
  // choice of "Sell tickets" already came back (old('tickets_enabled')) and these did not: the
  // page showed one blank row, and the next save published it as one free, unlimited ticket.
  // A row that was stored keeps what it knew about itself (its id, what it has sold), with what
  // was typed laid over it; a row removed before the refusal stays removed.
  $typedJson = fn ($value, $default) => is_array($value) ? $value : (is_string($value) && $value !== '' ? (json_decode($value, true) ?? $default) : $default);
  $typedOr = fn (array $row, string $key) => array_key_exists($key, $row) && $row[$key] !== '' && $row[$key] !== null ? $row[$key] : null;
  $ticketsNow = $canSeeTicketData ? ($event->tickets ?? collect()) : collect();
  $promoCodesNow = $canSeeTicketData ? ($event->promoCodes ?? collect()) : collect();
  $addonsNow = $canSeeTicketData ? ($event->addons ?? collect()) : collect();
  // Each list is taken from the post only when the post held it. Ticket types are sent whether
  // tickets are on or off. Promo codes and add-ons sit in fieldsets that are disabled while
  // tickets are off, and are rows only on a plan that has them: taken from a post that could not
  // hold them, both came back empty, and switching "Sell tickets" back on and saving deleted
  // every stored code and add-on.
  $ticketRowsTyped = $ticketPanelPosted && (old('tickets_enabled') || is_array(old('tickets')));
  $ticketExtrasTyped = $ticketPanelPosted && old('tickets_enabled') && $role->isPro();
  $storedById = fn ($rows) => collect($rows)->keyBy('id');
  if ($ticketRowsTyped) {
      $stored = $storedById($ticketsNow);
      $ticketsNow = collect((array) old('tickets', []))->values()->map(function ($typed) use ($stored, $typedJson, $typedOr) {
          $typed = (array) $typed;
          $base = ! empty($typed['id']) && $stored->has((int) $typed['id']) ? $stored->get((int) $typed['id'])->toArray() : [];
          $row = array_merge($base, [
              'id' => $base['id'] ?? null,
              'type' => (string) ($typed['type'] ?? ''),
              'quantity' => $typedOr($typed, 'quantity'),
              'price' => $typedOr($typed, 'price'),
              'description' => (string) ($typed['description'] ?? ''),
              'max_per_order' => $typedOr($typed, 'max_per_order'),
              'seating_band' => (string) ($typed['seating_band'] ?? ''),
              'is_pass' => ! empty($typed['is_pass']),
              'pass_allow_booking' => ! empty($typed['pass_allow_booking']),
              'typed_coverage' => ['group' => (string) ($typed['pass_scope_group_id'] ?? ''), 'events' => $typedJson($typed['pass_event_ids'] ?? null, [])],
              'custom_fields' => $typedJson($typed['custom_fields'] ?? null, []) ?: new \stdClass,
              'volume_discount' => $typedJson($typed['volume_discount'] ?? null, null),
              'sales_start_at' => $typedOr($typed, 'sales_start_at'),
              'sales_end_at' => $typedOr($typed, 'sales_end_at'),
          ]);
          foreach (['pass_usage_type', 'pass_max_uses', 'pass_valid_days', 'pass_scope', 'pass_seats_per_occurrence', 'pass_cancel_cutoff_hours', 'pass_late_cancel_policy', 'pass_admits_per_event'] as $passField) {
              if (array_key_exists($passField, $typed)) {
                  $row[$passField] = $typedOr($typed, $passField);
              }
          }

          return $row;
      })->all();
  }
  if ($ticketExtrasTyped) {
      $stored = $storedById($promoCodesNow);
      $promoCodesNow = collect((array) old('promo_codes', []))->values()->map(function ($typed) use ($stored, $typedJson, $typedOr) {
          $typed = (array) $typed;
          $base = ! empty($typed['id']) && $stored->has((int) $typed['id']) ? $stored->get((int) $typed['id'])->toArray() : [];

          return array_merge($base, [
              'id' => $base['id'] ?? null,
              'code' => (string) ($typed['code'] ?? ''),
              'type' => (string) ($typed['type'] ?? 'percentage'),
              'value' => $typedOr($typed, 'value'),
              'max_uses' => $typedOr($typed, 'max_uses'),
              'times_used' => $base['times_used'] ?? 0,
              'expires_at' => $typedOr($typed, 'expires_at'),
              'is_active' => ! empty($typed['is_active']),
              'ticket_ids' => $typedJson($typed['ticket_ids'] ?? null, []),
          ]);
      })->all();

      $stored = $storedById($addonsNow);
      // A picture chosen before the refusal was sent as text beside its row (addon_image_data, by
      // the row's place), so it can come back, and be sent again; one taken off stays off, where
      // the page used to show it again while still sending its removal.
      $typedPictures = (array) old('addon_image_data', []);
      $addonsNow = collect((array) old('addons', []))->map(function ($typed, $place) use ($stored, $typedOr, $typedPictures) {
          $typed = (array) $typed;
          $base = ! empty($typed['id']) && $stored->has((int) $typed['id']) ? $stored->get((int) $typed['id'])->toArray() : [];
          $picture = $typedPictures[$place] ?? null;
          if (is_string($picture) && str_starts_with($picture, 'data:image/')) {
              $base['image_url'] = $picture;
          } elseif (! empty($typed['remove_image'])) {
              $base['image_url'] = null;
          }

          return array_merge($base, [
              'id' => $base['id'] ?? null,
              'type' => (string) ($typed['type'] ?? ''),
              'quantity' => $typedOr($typed, 'quantity'),
              'max_per_order' => $typedOr($typed, 'max_per_order'),
              'price' => $typedOr($typed, 'price'),
              'description' => (string) ($typed['description'] ?? ''),
              'url' => (string) ($typed['url'] ?? ''),
              'remove_image' => ! empty($typed['remove_image']),
          ]);
      })->values()->all();
  }
  // Worked out here, not inside @json(): that directive splits its argument on commas.
  $showSalesDatesNow = collect($ticketsNow)->contains(fn ($t) => data_get($t, 'sales_start_at') || data_get($t, 'sales_end_at'));
  $agendaHasTimes = collect($eventPartsNow)->contains(fn ($part) => filled(data_get($part, 'start_time')) || filled(data_get($part, 'end_time')));
  $agendaShowTimesNow = (bool) old('agenda_show_times', $agendaHasTimes ?: ($role->agenda_show_times ?? true));
  // The Sponsors tab shows where a save would act on it (EventRepo::saveEvent(), $sponsorsOnPlan):
  // this schedule's plan has sponsors, or the event's own schedule's does.
  $sponsorsShown = $user->isEditor($subdomain)
      && ($role->isPro() || ($event->exists && $event->ticketingRole()?->isPro()));
  $paymentRowWarning = null;
  if (! $connectedGateways) {
      $paymentRowWarning = __('messages.connect_stripe_to_get_paid');
  } elseif (! $onlineGateways && ! ($user->stripe_account_id && ! $user->stripe_completed_at)) {
      $paymentRowWarning = __('messages.no_payment_method_for_currency', ['currency' => $event->ticket_currency_code]);
  }

  // The saved method when it is no longer on offer - currency changed after saving, or the gateway
  // was disconnected. Computed here rather than inside the select because the select itself has to
  // render for it: with nothing connected the whole block is otherwise hidden, leaving the stale
  // value invisible AND unchangeable, which is the state this is meant to rescue.
  $storedGateway = null;
  if ($event->payment_method && ! array_key_exists($event->payment_method, $selectableGateways)) {
      $candidate = $paymentGateways->get($event->payment_method);
      // isSelectableForEvents() keeps this aligned with the in: rule on the form request, which is
      // built from selectableKeys() - otherwise a future non-selectable driver would render an
      // option the request then rejects with no field-level message.
      $storedGateway = ($candidate && $candidate->isSelectableForEvents()) ? $candidate : null;
  }
  $gatewayCapabilities = $paymentGateways->capabilityMap();

  $use24hr = get_use_24_hour_time($role ?? null);
  // Prefill the time in the SCHEDULE's timezone so the form round-trips with the schedule-anchored
  // capture in EventRepo::saveEvent(). Role::captureTimezone() rather than the expression spelled
  // out here, because EventController hands the SAME call to saveEvent() as its $timezoneOverride -
  // one source of truth is what makes the round trip an identity, rather than two chains that have
  // to be kept in step. An existing event stored under a different timezone will visibly show its
  // real (possibly wrong) schedule-local time here, which is intended: it surfaces the problem so a
  // single re-save corrects it.
  $scheduleTz = (isset($role) && $role) ? $role->captureTimezone() : config('app.timezone');
  $prefillStartsAt = $event->starts_at
      ? $event->getStartDateTime(null, true, $scheduleTz)->format('Y-m-d H:i:s')
      : '';
  $oldStartsAt = old('starts_at', $prefillStartsAt);
  // Whether this existing event is anchored to a different timezone than the schedule (so its time
  // above may be wrong and a re-save will correct it).
  $eventIsOffTimezone = $event->exists && $event->starts_at && isset($role) && $role && $role->timezone && $event->isOffTimezoneFor($role);
  $eventEffectiveTz = $eventIsOffTimezone ? $event->getEffectiveTimezone() : null;
  $oldDuration = old('duration', $event->duration);
  $eventDate = '';
  $eventStartTime = '';
  $eventEndTime = '';
  $isMultiDay = $event->exists && $event->duration >= 24;
  $eventEndDate = '';
  if ($oldStartsAt) {
      $localDt = \Carbon\Carbon::parse($oldStartsAt);
      $eventDate = $localDt->format('Y-m-d');
      $eventStartTime = $use24hr ? $localDt->format('H:i') : $localDt->format('g:i A');
      if ($oldDuration) {
          $endDt = $localDt->copy()->addMinutes(round($oldDuration * 60));
          $eventEndTime = $use24hr ? $endDt->format('H:i') : $endDt->format('g:i A');
          if ($isMultiDay) {
              $eventEndDate = $endDt->format('Y-m-d');
          }
      }
  }
@endphp

<x-slot name="head">
  <link rel="stylesheet" href="{{ asset('vendor/intl-tel-input/css/intlTelInput.css') }}">
  <style {!! nonce_attr() !!}>
    form button {
      min-width: 100px;
      min-height: 40px;
    }
    .dark .iti {
      --iti-dropdown-bg: rgb(var(--ap-bg));
      --iti-hover-color: rgb(var(--ap-border));
      --iti-border-color: rgb(var(--ap-border));
      --iti-dialcode-color: rgb(var(--ap-ink-3));
      --iti-arrow-color: rgb(var(--ap-ink-2));
    }
    .dark .iti__dropdown-content { color: rgb(var(--ap-ink-2)); }
    .dark .iti__selected-dial-code { color: rgb(var(--ap-ink-2)); }
    .dark .iti__search-input { background: rgb(var(--ap-bg)); color: rgb(var(--ap-ink-2)); border-color: rgb(var(--ap-border)); }
    .iti:not(.iti--country-only) > .iti__country-container { padding: 0 0 0 4px !important; }
    
    /* Hide all sections except the first one by default */
    .section-content {
      display: none;
    }
    .event-tab {
      display: grid;
      /* minmax(0, ...): a grid track is as wide as its longest unbreakable line unless told
         otherwise, and About's one-line summary is exactly that. */
      grid-template-columns: minmax(0, 1fr);
      gap: 1rem;
    }
    .event-basics-grid {
      display: grid;
      grid-template-columns: minmax(0, 1fr);
      gap: 1.5rem;
    }
    .event-basics-grid > * > .mb-6:last-child,
    .event-basics-grid > .mb-6:last-child {
      margin-bottom: 0;
    }
    /* The date block is followed by the recurring settings, most of which are not on the page for a
       one-time event: space them from above, so the last one showing leaves nothing under it. */
    .event-basics-when > .mb-6 {
      margin-bottom: 0;
    }
    .event-basics-when > .mb-6 ~ .mb-6 {
      margin-top: 1.5rem;
    }
    @media (min-width: 768px) {
      .event-basics-grid {
        grid-template-columns: minmax(0, 1fr) 11rem;
        column-gap: 1.5rem;
      }
      .event-basics-name { grid-column: 1; grid-row: 1; }
      .event-basics-when { grid-column: 1; grid-row: 2; }
      .event-basics-flyer { grid-column: 2; grid-row: 1 / span 2; }
      .event-basics-location, .event-basics-sub { grid-column: 1 / -1; }
    }
    .event-loc3, .event-loc2 {
      display: grid;
      grid-template-columns: minmax(0, 1fr);
      gap: 0.75rem;
    }
    @media (min-width: 640px) {
      .event-loc3 { grid-template-columns: 1fr 1.25fr 0.8fr; }
      .event-loc2 { grid-template-columns: 1fr 1fr; }
    }
    .event-links {
      display: flex;
      flex-wrap: wrap;
      gap: 0.375rem 1.125rem;
      margin-top: 0.625rem;
    }
    .event-ai-row:not(:has(button)) {
      display: none;
    }

    /* On a phone the date takes a line and the two times share the next: side by side, three
       fields left "Start Time" too narrow to read its own placeholder. */
    @media (max-width: 639px) {
      .event-basics-when input.datepicker-date {
        flex-basis: 100%;
      }
      .event-basics-when input.datepicker-date ~ div {
        flex: 1;
      }
      .event-basics-when input.datepicker-date ~ div > .relative {
        flex: 1;
        width: auto;
      }
    }

    /* A first event: one column, and room beside it for the setup guide's card (which looks for
       the form's max-w-xl column to stand next to). */
    @media (min-width: 768px) {
      .event-tab-first .event-basics-grid {
        grid-template-columns: minmax(0, 1fr);
      }
      .event-tab-first .event-basics-name,
      .event-tab-first .event-basics-when {
        grid-column: auto;
        grid-row: auto;
      }
    }
    .event-about-flyer {
      padding: 1.25rem 1.5rem 0;
    }
    .event-about-flyer .mb-6 {
      margin-bottom: 0.75rem;
    }
    /* One line per ticket type where there is room for it. */
    @media (min-width: 768px) {
      #section-tickets .event-ticket-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
      }
      #section-tickets .event-ticket-grid > .flex {
        grid-column: 1 / -1;
      }
    }
    /* Agenda: one line for each part. */
    .event-agenda {
      border-top: 1px solid rgb(var(--ap-border));
    }
    .event-agenda-row {
      padding: 0.625rem 0;
      border-bottom: 1px solid rgb(var(--ap-border));
    }
    .event-agenda-line {
      display: grid;
      grid-template-columns: auto minmax(0, 1fr) auto;
      align-items: center;
      gap: 0.5rem;
    }
    .event-agenda-times {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      grid-column: 2 / -1;
      gap: 0.5rem;
    }
    .event-agenda-line input[type="text"] {
      margin-top: 0;
    }
    @media (min-width: 640px) {
      .event-agenda-line.has-times {
        grid-template-columns: auto 17rem minmax(0, 1fr) auto;
      }
      .event-agenda-times {
        grid-column: auto;
        grid-row: 1;
        grid-column-start: 2;
      }
      .event-agenda-line.has-times .event-agenda-name {
        grid-column-start: 3;
        grid-row: 1;
      }
      .event-agenda-line.has-times .event-agenda-actions {
        grid-column-start: 4;
        grid-row: 1;
      }
    }
    .event-agenda-actions {
      display: flex;
      align-items: center;
      gap: 0.125rem;
    }
    .event-agenda-sub {
      margin-top: 0.375rem;
      padding-inline-start: 1.75rem;
    }
    .event-agenda-sub textarea {
      padding: 0.5rem 0.75rem;
      font-size: 0.875rem;
    }
    /* One line of labels over the list, where each row had its own three. */
    .event-agenda-head {
      display: none;
    }
    @media (min-width: 640px) {
      .event-agenda-head {
        display: grid;
        grid-template-columns: 1.75rem 17rem minmax(0, 1fr);
        gap: 0.5rem;
        padding-bottom: 0.375rem;
        font-size: 0.8125rem;
        font-weight: 500;
        color: rgb(var(--ap-ink-2));
      }
      .event-agenda-head.no-times {
        grid-template-columns: 1.75rem minmax(0, 1fr);
      }
      .event-agenda-head > span:first-of-type {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.5rem;
      }
    }
    .event-agenda-actions .is-remove {
      margin-inline-start: 0.5rem;
    }
    /* A price is typed, not stepped. */
    #section-tickets input[type="number"] {
      -moz-appearance: textfield;
      appearance: textfield;
    }
    #section-tickets input[type="number"]::-webkit-inner-spin-button,
    #section-tickets input[type="number"]::-webkit-outer-spin-button {
      -webkit-appearance: none;
      margin: 0;
    }
    /* "Not needed", when it is the choice in force: it reads as chosen, the way a tile does. */
    form button.event-link.event-link-quiet.is-current {
      color: var(--brand-blue);
      cursor: default;
    }
    form button.event-link.event-link-quiet.is-current::before {
      content: "\2713\00a0";
    }
    form button.event-link.event-link-quiet.is-current:hover {
      text-decoration: none;
    }
    /* A switch is a switch: the rule above that sizes this form's text buttons was also stretching
       these to 100 by 40. */
    form button[role="switch"] {
      min-width: 0;
      min-height: 0;
    }
    /* The setup guide keeps a ring in the bottom corner, which is where Save now lives. It rides
       above a page's own bottom bar by this much (SetupGuide.vue, --sg-bar). */
    @media (min-width: 1024px) {
      :root {
        --sg-bar: 4.75rem;
      }
    }

    /* The flyer tile: a button to choose a file that is also where one is dropped. */
    .event-flyer-pick {
      display: flex;
      flex-direction: column;
      gap: 0.375rem;
    }
    form button.event-flyer-tile {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 0.125rem;
      width: 100%;
      min-height: 6.5rem;
      padding: 0.75rem;
      border: 1px dashed rgb(var(--ap-border-strong));
      border-radius: 0.75rem;
      background: rgb(var(--ap-bg));
      text-align: center;
      transition: all 0.2s;
    }
    form button.event-flyer-tile:hover,
    #event-basics.is-dropping .event-flyer-tile {
      border-color: var(--brand-blue);
      background: var(--brand-blue-a10);
    }
    .event-flyer-tile svg {
      width: 1.5rem;
      height: 1.5rem;
      margin-bottom: 0.125rem;
      color: rgb(var(--ap-ink-3));
    }
    .event-flyer-title {
      font-size: 0.875rem;
      font-weight: 600;
      color: rgb(var(--ap-ink));
    }
    .event-flyer-help {
      font-size: 0.75rem;
      color: rgb(var(--ap-ink-3));
    }
    .section-content:first-of-type {
      display: block;
    }

    /* Custom time picker dropdown */
    .time-dropdown {
      display: none;
      position: absolute;
      z-index: 50;
      width: 100%;
      max-height: 200px;
      overflow-y: auto;
      background: linear-gradient(to bottom, #ffffff, #fafafa);
      border: 1px solid rgba(0, 0, 0, 0.08);
      border-radius: 0.5rem;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
      margin-top: 2px;
    }
    .dark .time-dropdown {
      background: linear-gradient(to bottom, rgb(var(--ap-border)), rgb(var(--ap-rail-active)));
      border-color: rgba(255, 255, 255, 0.06);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.4);
    }
    .time-dropdown.open {
      display: block;
    }
    .time-dropdown-item {
      padding: 6px 12px;
      cursor: pointer;
      font-size: 0.875rem;
      color: #111827;
      transition: all 0.15s ease;
    }
    .dark .time-dropdown-item {
      color: rgb(var(--ap-ink-2));
    }
    .time-dropdown-item:hover,
    .time-dropdown-item.highlighted {
      background: linear-gradient(to bottom, #f3f4f6, #eef0f3);
      color: #111827;
    }
    .dark .time-dropdown-item:hover,
    .dark .time-dropdown-item.highlighted {
      background: linear-gradient(to bottom, rgb(var(--ap-border-strong)), #383838);
      color: #fff;
    }
    .time-dropdown-item.hidden {
      display: none;
    }

    /* Shake animation for incomplete data warning */
    @keyframes shake {
      0%, 100% { transform: translateX(0); }
      20%, 60% { transform: translateX(-5px); }
      40%, 80% { transform: translateX(5px); }
    }
    .shake {
      animation: shake 0.4s ease-in-out;
    }
  </style>
  <script {!! nonce_attr() !!}>
    if ('scrollRestoration' in history) {
        history.scrollRestoration = 'manual';
    }
    // Smart scroll guard: corrects automatic scroll-down during init,
    // but stops as soon as the user intentionally scrolls.
    (function() {
        var userScrolled = false;
        function onUserScroll() { userScrolled = true; }
        window.addEventListener('wheel', onUserScroll, { passive: true });
        window.addEventListener('touchmove', onUserScroll, { passive: true });

        function fixScroll() {
            if (!userScrolled && window.scrollY > 0) {
                window.scrollTo(0, 0);
            }
        }

        window.addEventListener('load', function() {
            fixScroll();
            requestAnimationFrame(function() {
                fixScroll();
                requestAnimationFrame(function() {
                    fixScroll();
                    window.removeEventListener('wheel', onUserScroll);
                    window.removeEventListener('touchmove', onUserScroll);
                });
            });
        });
    })();
  </script>
  <script src="{{ asset('js/vue.global.prod.js') }}" {!! nonce_attr() !!}></script>
  <script src="{{ asset('js/sortable.min.js') }}" {!! nonce_attr() !!}></script>
  <script src="{{ asset('vendor/intl-tel-input/js/intlTelInput.min.js') }}" {!! nonce_attr() !!}></script>
  <script {!! nonce_attr() !!}>
    // --- Global time helper functions (used by main event and parts) ---
    var use24hr = {{ $use24hr ? 'true' : 'false' }};

    function parseTimeToMinutes(timeStr) {
        if (!timeStr) return null;
        timeStr = timeStr.trim();

        // Try 24hr format: HH:mm or H:mm
        var match24 = timeStr.match(/^(\d{1,2}):(\d{2})$/);
        if (match24 && use24hr) {
            var h = parseInt(match24[1], 10);
            var m = parseInt(match24[2], 10);
            if (h >= 0 && h <= 23 && m >= 0 && m <= 59) return h * 60 + m;
        }

        // Try 12hr format: h:mm AM/PM or h:mmAM/PM
        var match12 = timeStr.match(/^(\d{1,2}):(\d{2})\s*(AM|PM|am|pm)$/i);
        if (match12) {
            var h = parseInt(match12[1], 10);
            var m = parseInt(match12[2], 10);
            var period = match12[3].toUpperCase();
            if (h >= 1 && h <= 12 && m >= 0 && m <= 59) {
                if (period === 'AM' && h === 12) h = 0;
                else if (period === 'PM' && h !== 12) h += 12;
                return h * 60 + m;
            }
        }

        // Try shorthand: 2pm, 11am
        var matchShort = timeStr.match(/^(\d{1,2})\s*(AM|PM|am|pm)$/i);
        if (matchShort) {
            var h = parseInt(matchShort[1], 10);
            var period = matchShort[2].toUpperCase();
            if (h >= 1 && h <= 12) {
                if (period === 'AM' && h === 12) h = 0;
                else if (period === 'PM' && h !== 12) h += 12;
                return h * 60;
            }
        }

        // Try bare HH:mm in 12hr mode (assume as-is if valid)
        if (!use24hr && match24) {
            var h = parseInt(match24[1], 10);
            var m = parseInt(match24[2], 10);
            if (h >= 0 && h <= 23 && m >= 0 && m <= 59) return h * 60 + m;
        }

        return null;
    }

    function formatMinutesToTime(minutes) {
        var h = Math.floor(minutes / 60) % 24;
        var m = minutes % 60;
        if (use24hr) {
            return (h < 10 ? '0' : '') + h + ':' + (m < 10 ? '0' : '') + m;
        } else {
            var period = h < 12 ? 'AM' : 'PM';
            var h12 = h % 12 || 12;
            return h12 + ':' + (m < 10 ? '0' : '') + m + ' ' + period;
        }
    }

    // Simplified time picker for event parts (lazy initialization)
    function initPartTimePicker(inputEl, dropdownEl) {
        if (inputEl._timepickerInit) return; // Already initialized
        inputEl._timepickerInit = true;

        var timeOptions = [];
        for (var m = 0; m < 1440; m += 30) {
            timeOptions.push(formatMinutesToTime(m));
        }

        // Build dropdown items
        timeOptions.forEach(function(label) {
            var div = document.createElement('div');
            div.className = 'time-dropdown-item';
            div.textContent = label;
            div.setAttribute('data-value', label);
            div.addEventListener('mousedown', function(e) {
                e.preventDefault();
                inputEl.value = label;
                closeDropdown();
                inputEl.dispatchEvent(new Event('change', { bubbles: true }));
            });
            dropdownEl.appendChild(div);
        });

        var highlightedIndex = -1;

        function getVisibleItems() {
            return Array.from(dropdownEl.querySelectorAll('.time-dropdown-item:not(.hidden)'));
        }

        function setHighlight(idx) {
            var items = getVisibleItems();
            items.forEach(function(el, i) {
                el.classList.toggle('highlighted', i === idx);
            });
            highlightedIndex = idx;
            if (idx >= 0 && idx < items.length) {
                items[idx].scrollIntoView({ block: 'nearest' });
            }
        }

        function showAllItems() {
            var items = dropdownEl.querySelectorAll('.time-dropdown-item');
            items.forEach(function(el) {
                el.classList.remove('hidden');
            });
            highlightedIndex = -1;
        }

        function openDropdown() {
            showAllItems();
            dropdownEl.classList.add('open');
            scrollToNearest();
        }

        function closeDropdown() {
            dropdownEl.classList.remove('open');
            highlightedIndex = -1;
        }

        function filterItems() {
            var query = inputEl.value.trim().toLowerCase();
            var items = dropdownEl.querySelectorAll('.time-dropdown-item');
            items.forEach(function(el) {
                var val = el.getAttribute('data-value').toLowerCase();
                if (!query || val.indexOf(query) !== -1) {
                    el.classList.remove('hidden');
                } else {
                    el.classList.add('hidden');
                }
            });
            highlightedIndex = -1;
        }

        function scrollToNearest() {
            var minutes = parseTimeToMinutes(inputEl.value);
            if (minutes === null) minutes = 540; // Default to 9am
            var closest = Math.round(minutes / 30) * 30;
            if (closest >= 1440) closest = 0;
            var target = formatMinutesToTime(closest);
            var items = getVisibleItems();
            for (var i = 0; i < items.length; i++) {
                if (items[i].getAttribute('data-value') === target) {
                    items[i].scrollIntoView({ block: 'center' });
                    setHighlight(i);
                    return;
                }
            }
        }

        inputEl.addEventListener('focus', function() {
            openDropdown();
        });

        inputEl.addEventListener('click', function() {
            if (!dropdownEl.classList.contains('open')) {
                openDropdown();
            }
        });

        inputEl.addEventListener('input', function() {
            filterItems();
            if (dropdownEl.classList.contains('open')) {
                var visible = getVisibleItems();
                if (visible.length > 0) {
                    setHighlight(0);
                }
            }
        });

        inputEl.addEventListener('blur', function() {
            closeDropdown();
        });

        inputEl.addEventListener('keydown', function(e) {
            if (!dropdownEl.classList.contains('open')) return;
            var items = getVisibleItems();
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                var next = highlightedIndex + 1;
                if (next >= items.length) next = 0;
                setHighlight(next);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                var prev = highlightedIndex - 1;
                if (prev < 0) prev = items.length - 1;
                setHighlight(prev);
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (highlightedIndex >= 0 && highlightedIndex < items.length) {
                    inputEl.value = items[highlightedIndex].getAttribute('data-value');
                    closeDropdown();
                    inputEl.dispatchEvent(new Event('change', { bubbles: true }));
                }
            } else if (e.key === 'Escape' || e.key === 'Tab') {
                closeDropdown();
            }
        });

        // Close on click outside
        document.addEventListener('mousedown', function(e) {
            if (!inputEl.contains(e.target) && !dropdownEl.contains(e.target)) {
                closeDropdown();
            }
        });

        // Open immediately since this is called on focus
        openDropdown();
    }

    document.addEventListener('DOMContentLoaded', function() {
        var fpLocale = window.flatpickrLocales ? window.flatpickrLocales[window.appLocale] : null;
        var localeConfig = fpLocale ? { locale: fpLocale } : {};

        var f = flatpickr('.datepicker-date', Object.assign({
            allowInput: true,
            enableTime: false,
            altInput: true,
            altFormat: "M j, Y",
            dateFormat: "Y-m-d",
            onChange: function(selectedDates) {
                updateHiddenFields();
                @if (! $event->exists)
                // A new event's next step after the date is its time, so go there: focusing the
                // time input opens its list. After the calendar has closed and taken its focus
                // back, hence the timeout.
                var startEl = document.getElementById('start_time');
                if (selectedDates.length && startEl && ! startEl.value) {
                    setTimeout(function () { startEl.focus(); }, 0);
                }
                @endif
            },
        }, localeConfig));
        // https://github.com/flatpickr/flatpickr/issues/892#issuecomment-604387030
        if (f._input) f._input.onkeydown = () => false;

        // Initialize recurring end date picker if it exists on page load
        var endDateInput = document.querySelector('.datepicker-end-date');
        if (endDateInput && endDateInput.value) {
            var endDatePicker = flatpickr(endDateInput, Object.assign({
                allowInput: true,
                enableTime: false,
                altInput: true,
                altFormat: "M j, Y",
                dateFormat: "Y-m-d",
            }, localeConfig));
            if (endDatePicker._input) {
                endDatePicker._input.onkeydown = () => false;
            }
        }

        // Initialize multi-day end date picker
        var multiDayEndDateEl = document.getElementById('event_end_date');
        if (multiDayEndDateEl) {
            var startDateVal = document.getElementById('event_date')._flatpickr
                ? document.getElementById('event_date')._flatpickr.selectedDates[0]
                : null;
            var minEndDate = startDateVal ? new Date(startDateVal.getTime() + 86400000) : null;
            var multiDayEndPicker = flatpickr(multiDayEndDateEl, Object.assign({
                allowInput: true,
                enableTime: false,
                altInput: true,
                altFormat: "M j, Y",
                dateFormat: "Y-m-d",
                minDate: minEndDate,
                onChange: function() {
                    updateHiddenFields();
                },
            }, localeConfig));
            if (multiDayEndPicker._input) multiDayEndPicker._input.onkeydown = () => false;

            // Update minDate when start date changes
            var startDateFp = document.getElementById('event_date')._flatpickr;
            if (startDateFp) {
                var origOnChange = startDateFp.config.onChange;
                startDateFp.config.onChange.push(function(selectedDates) {
                    if (selectedDates[0]) {
                        var newMin = new Date(selectedDates[0].getTime() + 86400000);
                        multiDayEndPicker.set('minDate', newMin);
                        if (multiDayEndPicker.selectedDates[0] && multiDayEndPicker.selectedDates[0] <= selectedDates[0]) {
                            multiDayEndPicker.clear();
                        }
                    }
                });
            }

            // Toggle behavior
            var multiDayToggle = document.getElementById('is_multi_day');
            if (multiDayToggle) {
                multiDayToggle.addEventListener('change', function() {
                    var row = document.getElementById('multi_day_end_date_row');
                    var endTimeMultiEl = document.getElementById('end_time_multi');
                    if (this.checked) {
                        row.style.display = '';
                        if (endTimeMultiEl) endTimeMultiEl.value = endTimeEl.value;
                        if (window.vueApp) window.vueApp.isMultiDay = true;
                    } else {
                        row.style.display = 'none';
                        if (endTimeMultiEl) endTimeEl.value = endTimeMultiEl.value;
                        if (window.vueApp) window.vueApp.isMultiDay = false;
                        multiDayEndPicker.clear();
                    }
                    updateHiddenFields();
                });
            }
        }

        // --- Time combobox logic for main event ---

        function normalizeTimeInput(inputEl) {
            var val = inputEl.value;
            var minutes = parseTimeToMinutes(val);
            if (minutes !== null) {
                inputEl.value = formatMinutesToTime(minutes);
            } else if (val.trim() !== '') {
                inputEl.value = '';
            }
        }

        function updateHiddenFields() {
            var dateEl = document.getElementById('event_date');
            var startEl = document.getElementById('start_time');
            var endEl = document.getElementById('end_time');
            var hiddenStartsAt = document.getElementById('starts_at');
            var hiddenDuration = document.getElementById('duration');
            var multiDayToggle = document.getElementById('is_multi_day');

            var dateVal = dateEl._flatpickr ? dateEl._flatpickr.selectedDates[0] : null;
            var dateStr = dateVal ? dateEl._flatpickr.formatDate(dateVal, 'Y-m-d') : dateEl.value;
            var startMinutes = parseTimeToMinutes(startEl.value);
            var endMinutes = parseTimeToMinutes(endEl.value);

            if (dateStr && startMinutes !== null) {
                var hh = (Math.floor(startMinutes / 60) < 10 ? '0' : '') + Math.floor(startMinutes / 60);
                var mm = (startMinutes % 60 < 10 ? '0' : '') + (startMinutes % 60);
                hiddenStartsAt.value = dateStr + ' ' + hh + ':' + mm + ':00';
                if (window.vueApp) { window.vueApp.startsAt = hiddenStartsAt.value; }
            } else if (dateStr && multiDayToggle && multiDayToggle.checked) {
                hiddenStartsAt.value = dateStr + ' 00:00:00';
                if (window.vueApp) { window.vueApp.startsAt = hiddenStartsAt.value; }
            } else {
                hiddenStartsAt.value = '';
                if (window.vueApp) { window.vueApp.startsAt = ''; }
            }

            // Multi-day duration calculation
            if (multiDayToggle && multiDayToggle.checked) {
                var endDateEl = document.getElementById('event_end_date');
                var endDateVal = endDateEl && endDateEl._flatpickr ? endDateEl._flatpickr.selectedDates[0] : null;
                if (endDateVal && dateVal) {
                    var daysDiffMs = endDateVal.getTime() - dateVal.getTime();
                    var daysDiff = Math.round(daysDiffMs / 86400000);
                    if (startMinutes !== null && endMinutes !== null) {
                        var totalMinutes = (daysDiff * 1440) + endMinutes - startMinutes;
                        hiddenDuration.value = (totalMinutes / 60).toFixed(2).replace(/\.?0+$/, '');
                    } else {
                        hiddenDuration.value = (daysDiff * 24).toString();
                    }
                } else {
                    hiddenDuration.value = '';
                }
            } else {
                // Single-day duration calculation
                if (endMinutes !== null && startMinutes !== null) {
                    var diff = endMinutes - startMinutes;
                    if (diff < 0) diff += 1440; // crosses midnight
                    hiddenDuration.value = (diff / 60).toFixed(2).replace(/\.?0+$/, '');
                } else {
                    hiddenDuration.value = '';
                }
            }

            if (window.vueApp) { window.vueApp.currentDuration = hiddenDuration.value; }

            updateScheduleTimePreview(hiddenStartsAt.value, multiDayToggle);

            // Every date and time change comes through here, so this is where a shown
            // "date and time required" message learns it has been answered.
            if (window.vueApp && window.vueApp.refreshDateTimeError) { window.vueApp.refreshDateTimeError(); }
        }

        // Display-only preview of the entered time in the schedule's timezone. The entered wall-clock
        // already IS the schedule-local time (capture is schedule-anchored), so we format its
        // components directly (no timezone conversion) and append the schedule's timezone name.
        function updateScheduleTimePreview(startsAtVal, multiDayToggle) {
            var el = document.getElementById('starts_at_preview');
            if (! el) return;
            var tz = el.getAttribute('data-tz');
            var m = (startsAtVal || '').match(/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2})/);
            var isMidnightMultiDay = multiDayToggle && multiDayToggle.checked && m && m[4] === '00' && m[5] === '00';
            if (window.vueApp && ! m) {
                window.vueApp.whenLabel = '';
            }
            if (! tz || ! m || isMidnightMultiDay) {
                el.textContent = '';
                return;
            }
            try {
                var previewDate = new Date(+m[1], +m[2] - 1, +m[3], +m[4], +m[5]);
                var locale = document.documentElement.lang || undefined;
                var formatted = new Intl.DateTimeFormat(locale, { weekday: 'short', month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' }).format(previewDate);
                el.textContent = (el.getAttribute('data-prefix') || '') + ' ' + formatted;
                if (window.vueApp) {
                    window.vueApp.whenLabel = formatted;
                }
            } catch (e) {
                el.textContent = '';
            }
        }

        // Track duration for auto-adjust
        var lastDurationMinutes = 60;
        var initStart = parseTimeToMinutes(document.getElementById('start_time').value);
        var initEnd = parseTimeToMinutes(document.getElementById('end_time').value);
        if (initStart !== null && initEnd !== null) {
            var diff = initEnd - initStart;
            if (diff < 0) diff += 1440;
            lastDurationMinutes = diff;
        }

        var startTimeEl = document.getElementById('start_time');
        var endTimeEl = document.getElementById('end_time');

        startTimeEl.addEventListener('blur', function() {
            normalizeTimeInput(startTimeEl);
            updateHiddenFields();
        });

        endTimeEl.addEventListener('blur', function() {
            normalizeTimeInput(endTimeEl);
            var startMin = parseTimeToMinutes(startTimeEl.value);
            var endMin = parseTimeToMinutes(endTimeEl.value);
            if (startMin !== null && endMin !== null) {
                var diff = endMin - startMin;
                if (diff < 0) diff += 1440;
                lastDurationMinutes = diff;
            }
            updateHiddenFields();
        });

        startTimeEl.addEventListener('input', function() { updateHiddenFields(); });
        endTimeEl.addEventListener('input', function() { updateHiddenFields(); });

        // Initialize hidden fields on page load
        updateHiddenFields();

        // Custom time picker dropdown
        function initTimePicker(inputEl, dropdownEl, getDefaultMinutes) {
            var timeOptions = [];
            for (var m = 0; m < 1440; m += 30) {
                timeOptions.push(formatMinutesToTime(m));
            }

            // Build dropdown items
            timeOptions.forEach(function(label) {
                var div = document.createElement('div');
                div.className = 'time-dropdown-item';
                div.textContent = label;
                div.setAttribute('data-value', label);
                div.addEventListener('mousedown', function(e) {
                    e.preventDefault();
                    inputEl.value = label;
                    closeDropdown();
                    normalizeTimeInput(inputEl);
                    updateHiddenFields();
                });
                dropdownEl.appendChild(div);
            });

            var highlightedIndex = -1;

            function getVisibleItems() {
                return Array.from(dropdownEl.querySelectorAll('.time-dropdown-item:not(.hidden)'));
            }

            function setHighlight(idx) {
                var items = getVisibleItems();
                items.forEach(function(el, i) {
                    el.classList.toggle('highlighted', i === idx);
                });
                highlightedIndex = idx;
                if (idx >= 0 && idx < items.length) {
                    items[idx].scrollIntoView({ block: 'nearest' });
                }
            }

            function showAllItems() {
                var items = dropdownEl.querySelectorAll('.time-dropdown-item');
                items.forEach(function(el) {
                    el.classList.remove('hidden');
                });
                highlightedIndex = -1;
            }

            function openDropdown() {
                showAllItems();
                dropdownEl.classList.add('open');
                scrollToNearest();
            }

            function closeDropdown() {
                dropdownEl.classList.remove('open');
                highlightedIndex = -1;
            }

            function filterItems() {
                var query = inputEl.value.trim().toLowerCase();
                var items = dropdownEl.querySelectorAll('.time-dropdown-item');
                items.forEach(function(el) {
                    var val = el.getAttribute('data-value').toLowerCase();
                    if (!query || val.indexOf(query) !== -1) {
                        el.classList.remove('hidden');
                    } else {
                        el.classList.add('hidden');
                    }
                });
                highlightedIndex = -1;
            }

            function scrollToNearest() {
                var minutes = parseTimeToMinutes(inputEl.value);
                if (minutes === null) minutes = getDefaultMinutes();
                // Find closest 30-min slot
                var closest = Math.round(minutes / 30) * 30;
                if (closest >= 1440) closest = 0;
                var target = formatMinutesToTime(closest);
                var items = getVisibleItems();
                for (var i = 0; i < items.length; i++) {
                    if (items[i].getAttribute('data-value') === target) {
                        items[i].scrollIntoView({ block: 'center' });
                        setHighlight(i);
                        return;
                    }
                }
            }

            inputEl.addEventListener('focus', function() {
                openDropdown();
            });

            inputEl.addEventListener('click', function() {
                if (!dropdownEl.classList.contains('open')) {
                    openDropdown();
                }
            });

            inputEl.addEventListener('input', function() {
                filterItems();
                if (dropdownEl.classList.contains('open')) {
                    var visible = getVisibleItems();
                    if (visible.length > 0) {
                        setHighlight(0);
                    }
                }
                updateHiddenFields();
            });

            inputEl.addEventListener('blur', function() {
                closeDropdown();
                normalizeTimeInput(inputEl);
                updateHiddenFields();
            });

            inputEl.addEventListener('keydown', function(e) {
                if (!dropdownEl.classList.contains('open')) return;
                var items = getVisibleItems();
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    var next = highlightedIndex + 1;
                    if (next >= items.length) next = 0;
                    setHighlight(next);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    var prev = highlightedIndex - 1;
                    if (prev < 0) prev = items.length - 1;
                    setHighlight(prev);
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    if (highlightedIndex >= 0 && highlightedIndex < items.length) {
                        inputEl.value = items[highlightedIndex].getAttribute('data-value');
                        closeDropdown();
                        normalizeTimeInput(inputEl);
                        updateHiddenFields();
                    }
                } else if (e.key === 'Escape' || e.key === 'Tab') {
                    closeDropdown();
                }
            });

            // Close on click outside
            document.addEventListener('mousedown', function(e) {
                if (!inputEl.contains(e.target) && !dropdownEl.contains(e.target)) {
                    closeDropdown();
                }
            });
        }

        // Where the start-time list opens when nothing is picked yet: 8 PM for a new event, which
        // is also the time EventController::create() gives a day clicked on the calendar. It only
        // scrolls the list; no value is filled in.
        initTimePicker(startTimeEl, document.getElementById('start_time_dropdown'), function() { return {{ $event->exists ? 540 : 1200 }}; });
        initTimePicker(endTimeEl, document.getElementById('end_time_dropdown'), function() {
            var startMinutes = parseTimeToMinutes(startTimeEl.value);
            if (startMinutes === null) return 600;
            return (startMinutes + 60) % 1440;
        });

        var endTimeMultiEl = document.getElementById('end_time_multi');
        if (endTimeMultiEl) {
            initTimePicker(endTimeMultiEl, document.getElementById('end_time_multi_dropdown'), function() {
                var startMinutes = parseTimeToMinutes(startTimeEl.value);
                if (startMinutes === null) return 600;
                return (startMinutes + 60) % 1440;
            });

            endTimeMultiEl.addEventListener('blur', function() {
                normalizeTimeInput(endTimeMultiEl);
                endTimeEl.value = endTimeMultiEl.value;
                updateHiddenFields();
            });
            endTimeMultiEl.addEventListener('input', function() {
                endTimeEl.value = endTimeMultiEl.value;
                updateHiddenFields();
            });
        }
    });

    function onChangeDateType() {
        if (typeof window.vueApp === 'undefined' || !window.vueApp.event) {
            return;
        }
        var value = $('input[name="schedule_type"]:checked').val();
        if (value == 'one_time') {
            window.vueApp.isRecurring = false;
        } else {
            window.vueApp.isRecurring = true;
            if (!window.vueApp.event.recurring_frequency) {
                window.vueApp.event.recurring_frequency = 'weekly';
            }
            // Hide and uncheck multi-day when switching to recurring
            var multiDayToggle = document.getElementById('is_multi_day');
            if (multiDayToggle && multiDayToggle.checked) {
                multiDayToggle.checked = false;
                multiDayToggle.dispatchEvent(new Event('change'));
            }
        }
        updateRecurringFieldVisibility();
    }

    function updateRecurringFieldVisibility() {
        if (typeof window.vueApp === 'undefined' || !window.vueApp.event) {
            return;
        }
        var freq = window.vueApp.event.recurring_frequency || 'weekly';
        var showDays = window.vueApp.isRecurring && (freq === 'weekly' || freq === 'every_n_weeks');
        if (showDays) {
            $('#days_of_week_div').show();
        } else {
            $('#days_of_week_div').hide();
        }
    }

    function onValidateClick() {
        $('#address_response').text(@json(__('messages.searching')) + '...').show();
        $('#accept_button').hide();
        var ci = window.getCountryInput('venue_country_code');
        var country = ci ? ci.getSelectedCountryData() : null;
        $.post({
            url: '{{ route('validate_address') }}',
            data: {
                _token: '{{ csrf_token() }}',
                address1: $('#venue_address1').val(),
                city: $('#venue_city').val(),
                state: $('#venue_state').val(),
                postal_code: $('#venue_postal_code').val(),
                country_code: country ? country.iso2 : '',
            },
            success: function(response) {
                if (response) {
                    var address = response['data']['formatted_address'];
                    $('#address_response').text(address);
                    $('#accept_button').show();
                    $('#address_response').data('validated_address', response['data']);
                } else {
                    $('#address_response').text(@json(__('messages.address_not_found')));
                }
            },
            error: function(xhr, status, error) {
                $('#address_response').text(@json(__('messages.an_error_occurred')) + ': ' + error);
            }
        });
    }

    function viewMap() {
        var address = [
            $('#venue_address1').val(),
            $('#venue_city').val(),
            $('#venue_state').val(),
            $('#venue_postal_code').val(),
            (window.getCountryInput('venue_country_code') ? window.getCountryInput('venue_country_code').getSelectedCountryData().name : '')
        ].filter(Boolean).join(', ');

        if (address) {
            var url = 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(address);
            window.open(url, '_blank');
        } else {
            alert(@json(__('messages.please_enter_address')));
        }
    }

    function acceptAddress(event) {
        event.preventDefault();
        var validatedAddress = $('#address_response').data('validated_address');
        if (validatedAddress) {
            // Into Vue, not into the inputs: they are v-model fields, so a value written to the
            // DOM alone is not what the hidden venue_* inputs post, and the next re-render puts
            // the old text back.
            if (window.vueApp) {
                window.vueApp.venueAddress1 = validatedAddress['address1'];
                window.vueApp.venueCity = validatedAddress['city'];
                window.vueApp.venueState = validatedAddress['state'];
                window.vueApp.venuePostalCode = validatedAddress['postal_code'];
            }

            // Hide the address response and accept button after accepting
            $('#address_response').hide();
            $('#accept_button').hide();
        }
    }

    function clearFileInput(inputId) {
        var input = document.getElementById(inputId);
        input.value = '';
        // Directly hide preview instead of relying on change event
        var previewDiv = document.getElementById('image_preview');
        var filenameSpan = document.getElementById('flyer_image_filename');
        var warningElement = document.getElementById('image_size_warning');
        if (previewDiv) previewDiv.style.display = 'none';
        if (filenameSpan) filenameSpan.textContent = '';
        if (warningElement) warningElement.style.display = 'none';
        var tile = document.getElementById('flyer-choose-btn');
        if (tile && inputId === 'flyer_image') tile.style.display = '';
    }

    function previewImage(input) {
        var preview = document.getElementById('preview_img');
        var previewDiv = document.getElementById('image_preview');
        var warningElement = document.getElementById('image_size_warning');
        var filenameSpan = document.getElementById('flyer_image_filename');

        if (input.files && input.files[0]) {
            if (filenameSpan) {
                filenameSpan.textContent = input.files[0].name;
            }
            var reader = new FileReader();

            reader.onload = function(e) {
                preview.src = e.target.result;
                previewDiv.style.display = 'inline-block';
                // The picked image stands where the tile was.
                var tile = document.getElementById('flyer-choose-btn');
                if (tile) tile.style.display = 'none';
            }

            reader.readAsDataURL(input.files[0]);

            // Check file size
            var fileSize = input.files[0].size / 1024 / 1024; // in MB
            if (fileSize > 2.5) {
                warningElement.textContent = @json(__('messages.image_size_warning'));
                warningElement.style.display = 'block';
            } else {
                warningElement.textContent = '';
                warningElement.style.display = 'none';
            }
        } else {
            if (filenameSpan) {
                filenameSpan.textContent = '';
            }
            preview.src = '#';
            previewDiv.style.display = 'none';
            warningElement.textContent = '';
            warningElement.style.display = 'none';
        }
    }

    function toggleEventSlugEdit() {
        const urlDisplay = document.getElementById('event-url-display');
        const slugEdit = document.getElementById('event-slug-edit');
        const editButton = document.getElementById('edit-slug-btn');
        const cancelButton = document.getElementById('cancel-slug-btn');
        const slugInput = document.getElementById('event_slug');

        if (urlDisplay.classList.contains('hidden')) {
            urlDisplay.classList.remove('hidden');
            slugEdit.classList.add('hidden');
            editButton.classList.remove('hidden');
            cancelButton.classList.add('hidden');
            slugInput.disabled = true;
        } else {
            urlDisplay.classList.add('hidden');
            slugEdit.classList.remove('hidden');
            editButton.classList.add('hidden');
            cancelButton.classList.remove('hidden');
            slugInput.disabled = false;
            slugInput.focus();
        }
    }

    @php
        $eventEditUrl = $event->exists ? $event->getShortGuestUrl($subdomain, true) : '';
        // registrationHref(), as viewGuest() reads it: a trailing slash on an event whose link is
        // no web page would only fall through to the event page.
        if ($event->exists && $role->direct_registration && $event->registrationHref()) {
            if (str_contains($eventEditUrl, '?')) {
                $eventEditUrl = str_replace('?', '/?', $eventEditUrl);
            } else {
                $eventEditUrl .= '/';
            }
        }
    @endphp

    function copyEventUrl(button) {
        const url = '{{ $eventEditUrl }}';
        navigator.clipboard.writeText(url).then(() => {
            const originalHTML = button.innerHTML;
            button.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
            `;
            setTimeout(() => {
                button.innerHTML = originalHTML;
            }, 2000);
        }).catch(() => {});
    }

    </script>

</x-slot>

<div id="app">

  @if ($event->exists && $event->is_cancelled)
  <div class="mb-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div class="flex items-start gap-2">
      <svg class="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" /></svg>
      <p class="text-sm text-red-800 dark:text-red-300 font-medium">{{ __('messages.event_is_cancelled_ap') }}</p>
    </div>
    @can('delete', $event)
    <button type="button" @click="submitRestore()" :disabled="isSubmittingRestore" class="shrink-0 inline-flex items-center justify-center px-4 py-2 text-sm font-semibold rounded-lg text-white bg-[var(--brand-button-bg)] hover:bg-[var(--brand-button-bg-hover)] transition-colors">{{ __('messages.restore_event') }}</button>
    @endcan
  </div>
  @endif

  <div class="pb-4 flex items-center justify-between">
    <div class="min-w-0">
      @if ($event->exists)
      <p class="event-eyebrow">{{ $title }}</p>
      @endif
      <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
        {{-- v-pre: an event's name is its owner's text, inside the Vue mount. --}}
        <h2 class="text-xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:truncate sm:text-2xl sm:tracking-tight" v-pre>
          {{ $event->exists ? $event->name : $title }}
        </h2>
        @if ($event->exists)
        {{-- What is SAVED, not what is on screen: choosing Draft does not change it until Save. --}}
        <button type="button" class="event-badge is-{{ $event->visibilityState() }}" id="event-saved-state" @click="goToTab('section-listing')">{{ __('messages.'.$event->visibilityState()) }}</button>
        @endif
      </div>
      @if ($isFirstEventRun)
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.first_event_form_subtitle') }}</p>
      @endif
      @if ($event->exists && $eventEditUrl)
      @php
          // Split by hand: parse_url() mangles a non-ASCII host on macOS.
          $eventLinkText = \App\Utils\UrlUtils::clean($eventEditUrl);
          $eventLinkSlash = strpos($eventLinkText, '/');
          $eventLinkHost = $eventLinkSlash === false ? $eventLinkText : substr($eventLinkText, 0, $eventLinkSlash);
          $eventLinkPath = $eventLinkSlash === false ? '' : substr($eventLinkText, $eventLinkSlash);
      @endphp
      {{-- The public link, where it can be copied without opening a tab to find it. v-pre: a
           schedule's address and an event's slug are their owner's text, inside the Vue mount. --}}
      <div class="event-url-strip">
        <span class="event-url-text" dir="ltr" v-pre><span class="event-url-host">{{ $eventLinkHost }}</span><span class="event-url-path">{{ $eventLinkPath }}</span></span>
        <button type="button" class="event-link" id="copy-event-link-btn" v-cloak @click="copyEventLink" v-text="linkCopied ? tabLabels.copied : tabLabels.copy"></button>
        <a href="{{ $eventEditUrl }}" target="_blank" rel="noopener" class="event-link">{{ __('messages.view') }}</a>
      </div>
      @endif
    </div>

    <div class="hidden lg:flex items-center gap-3">
        @if ($event->exists)
        {{-- Boost button --}}
        @php
            $boostStaticDisabled = null;
            $activeBoost = null;
            if (!config('services.meta.app_id')) {
                $boostStaticDisabled = __('messages.boost_not_configured');
            } elseif (!$role->isPro()) {
                $boostStaticDisabled = 'upgrade';
            } else {
                $activeBoost = $event->boostCampaigns()->whereIn('status', ['active', 'paused'])->first();
            }
        @endphp
        @if ($activeBoost)
        <a href="{{ route('boost.show', ['hash' => $activeBoost->hashedId()]) }}"
           class="inline-flex items-center justify-center rounded-lg bg-white dark:bg-gray-800 px-4 py-3 text-base font-semibold text-gray-900 dark:text-gray-100 shadow-sm border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800">
            <svg class="me-2 h-5 w-5 text-green-500" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M13.13 22.19L11.5 18.36C13.07 17.78 14.54 17 15.9 16.09L13.13 22.19M5.64 12.5L1.81 10.87L7.91 8.1C7 9.46 6.22 10.93 5.64 12.5M19.22 4C19.5 4 19.75 4 19.96 4.05C20.13 5.44 19.94 8.3 16.66 11.58C14.96 13.29 12.93 14.6 10.65 15.47L8.5 13.37C9.42 11.06 10.73 9.03 12.42 7.34C14.71 5.05 17.11 4.1 18.78 4.04C18.91 4 19.06 4 19.22 4Z"/>
            </svg>
            {{ __('messages.boosted') }} - {{ number_format($activeBoost->reach) }} {{ __('messages.reach') }}
        </a>
        @elseif ($boostStaticDisabled === 'upgrade' && config('app.hosted'))
        <button type="button" @click.prevent="openUpgrade('upgrade-boost')"
           class="inline-flex items-center justify-center rounded-lg bg-white dark:bg-gray-800 px-4 py-3 text-base font-semibold text-gray-900 dark:text-gray-100 shadow-sm border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800">
            <svg class="me-2 h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M13.13 22.19L11.5 18.36C13.07 17.78 14.54 17 15.9 16.09L13.13 22.19M5.64 12.5L1.81 10.87L7.91 8.1C7 9.46 6.22 10.93 5.64 12.5M19.22 4C19.5 4 19.75 4 19.96 4.05C20.13 5.44 19.94 8.3 16.66 11.58C14.96 13.29 12.93 14.6 10.65 15.47L8.5 13.37C9.42 11.06 10.73 9.03 12.42 7.34C14.71 5.05 17.11 4.1 18.78 4.04C18.91 4 19.06 4 19.22 4Z"/>
            </svg>
            {{ __('messages.boost_event') }}
        </button>
        @elseif ($boostStaticDisabled)
        <button type="button" @click="showMessage(@js($boostStaticDisabled))"
           class="inline-flex items-center justify-center rounded-lg bg-white dark:bg-gray-800 px-4 py-3 text-base font-semibold text-gray-900 dark:text-gray-100 shadow-sm border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800">
            <svg class="me-2 h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M13.13 22.19L11.5 18.36C13.07 17.78 14.54 17 15.9 16.09L13.13 22.19M5.64 12.5L1.81 10.87L7.91 8.1C7 9.46 6.22 10.93 5.64 12.5M19.22 4C19.5 4 19.75 4 19.96 4.05C20.13 5.44 19.94 8.3 16.66 11.58C14.96 13.29 12.93 14.6 10.65 15.47L8.5 13.37C9.42 11.06 10.73 9.03 12.42 7.34C14.71 5.05 17.11 4.1 18.78 4.04C18.91 4 19.06 4 19.22 4Z"/>
            </svg>
            {{ __('messages.boost_event') }}
        </button>
        @else
        <a v-cloak v-if="boostCanEnable" href="{{ route('boost.create', ['event_id' => \App\Utils\UrlUtils::encodeId($event->id), 'role_id' => \App\Utils\UrlUtils::encodeId($role->id)]) }}"
           class="inline-flex items-center justify-center rounded-lg bg-white dark:bg-gray-800 px-4 py-3 text-base font-semibold text-gray-900 dark:text-gray-100 shadow-sm border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800">
            <svg class="me-2 h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M13.13 22.19L11.5 18.36C13.07 17.78 14.54 17 15.9 16.09L13.13 22.19M5.64 12.5L1.81 10.87L7.91 8.1C7 9.46 6.22 10.93 5.64 12.5M19.22 4C19.5 4 19.75 4 19.96 4.05C20.13 5.44 19.94 8.3 16.66 11.58C14.96 13.29 12.93 14.6 10.65 15.47L8.5 13.37C9.42 11.06 10.73 9.03 12.42 7.34C14.71 5.05 17.11 4.1 18.78 4.04C18.91 4 19.06 4 19.22 4Z"/>
            </svg>
            {{ __('messages.boost_event') }}
        </a>
        <button v-cloak v-else type="button" v-on:click="showBoostError()"
           class="inline-flex items-center justify-center rounded-lg bg-white dark:bg-gray-800 px-4 py-3 text-base font-semibold text-gray-900 dark:text-gray-100 shadow-sm border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800">
            <svg class="me-2 h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M13.13 22.19L11.5 18.36C13.07 17.78 14.54 17 15.9 16.09L13.13 22.19M5.64 12.5L1.81 10.87L7.91 8.1C7 9.46 6.22 10.93 5.64 12.5M19.22 4C19.5 4 19.75 4 19.96 4.05C20.13 5.44 19.94 8.3 16.66 11.58C14.96 13.29 12.93 14.6 10.65 15.47L8.5 13.37C9.42 11.06 10.73 9.03 12.42 7.34C14.71 5.05 17.11 4.1 18.78 4.04C18.91 4 19.06 4 19.22 4Z"/>
            </svg>
            {{ __('messages.boost_event') }}
        </button>
        @endif
        {{-- Nothing to boost before the event exists, so a new event has no Boost button: one that
             could only answer "save first" was noise on the page that has to be the easiest to finish. --}}
        @endif

        @if ($event->exists)
        {{-- Actions dropdown --}}
        <div class="relative inline-block text-start">
            <button type="button" class="popup-toggle inline-flex w-full justify-center rounded-lg bg-white dark:bg-gray-800 px-4 py-3 text-base font-semibold text-gray-900 dark:text-gray-100 shadow-sm border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800" id="event-actions-menu-button" data-popup-target="event-actions-pop-up-menu" aria-expanded="false" aria-haspopup="true">
                {{ __('messages.actions') }}
                <svg class="-me-1 ms-2 h-6 w-6 text-gray-400 dark:text-gray-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                </svg>
            </button>
            <div id="event-actions-pop-up-menu" class="ap-dropdown pop-up-menu hidden absolute end-0 z-10 mt-2 w-64 {{ is_rtl() ? 'origin-top-left' : 'origin-top-right' }} divide-y divide-gray-100 dark:divide-white/[0.06] rounded-lg ring-1 ring-black/5 dark:ring-white/[0.06] focus:outline-none" role="menu" aria-orientation="vertical" aria-labelledby="event-actions-menu-button" tabindex="-1">
                <div class="py-2" role="none" id="event-actions-pop-up-menu-items" data-popup-target="event-actions-pop-up-menu">
                    <a href="{{ route('event.clone', ['subdomain' => $subdomain, 'hash' => \App\Utils\UrlUtils::encodeId($event->id)]) }}" class="group flex items-center px-5 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:bg-gray-100 dark:focus:bg-gray-700 focus:outline-none transition-colors" role="menuitem" tabindex="0">
                        <svg class="me-3 h-5 w-5 text-gray-400 group-hover:text-gray-500 dark:group-hover:text-gray-400" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M19,21H8V7H19M19,5H8A2,2 0 0,0 6,7V21A2,2 0 0,0 8,23H19A2,2 0 0,0 21,21V7A2,2 0 0,0 19,5M16,1H4A2,2 0 0,0 2,3V17H4V3H16V1Z" />
                        </svg>
                        <div>
                            {{ __('messages.clone_event') }}
                        </div>
                    </a>
                    @if ($role->isPro())
                    <button type="button" class="js-save-as-template-open w-full group flex items-center px-5 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:bg-gray-100 dark:focus:bg-gray-700 focus:outline-none transition-colors" role="menuitem" tabindex="0">
                        <svg class="me-3 h-5 w-5 text-gray-400 group-hover:text-gray-500 dark:group-hover:text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
                        </svg>
                        <div>
                            {{ __('messages.save_as_template') }}
                        </div>
                    </button>
                    @endif
                    @can('delete', $event)
                    <div class="py-2" role="none">
                        <div class="border-t border-gray-100 dark:border-gray-700"></div>
                    </div>
                    @if (! $event->is_cancelled)
                    <button type="button" @click="openCancelModal()" class="w-full group flex items-center px-5 py-3 text-sm text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-900/20 focus:bg-amber-50 dark:focus:bg-amber-900/20 focus:outline-none transition-colors" role="menuitem" tabindex="0">
                        <svg class="me-3 h-5 w-5 text-amber-400 group-hover:text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                        <div>{{ __('messages.cancel_event') }}</div>
                    </button>
                    @else
                    <button type="button" @click="submitRestore()" :disabled="isSubmittingRestore" class="w-full group flex items-center px-5 py-3 text-sm text-green-600 dark:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/20 focus:bg-green-50 dark:focus:bg-green-900/20 focus:outline-none transition-colors" role="menuitem" tabindex="0">
                        <svg class="me-3 h-5 w-5 text-green-400 group-hover:text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
                        <div>{{ __('messages.restore_event') }}</div>
                    </button>
                    @endif
                    <form method="POST" action="{{ route('event.delete', ['subdomain' => $subdomain, 'hash' => \App\Utils\UrlUtils::encodeId($event->id)]) }}" id="event-delete-form" class="block">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full group flex items-center px-5 py-3 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-700 dark:hover:text-red-300 focus:bg-red-50 dark:focus:bg-red-900/20 focus:text-red-700 dark:focus:text-red-300 focus:outline-none transition-colors" role="menuitem" tabindex="0">
                            <svg class="me-3 h-5 w-5 text-red-400 group-hover:text-red-500 dark:group-hover:text-red-400" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M19,4H15.5L14.5,3H9.5L8.5,4H5V6H19M6,19A2,2 0 0,0 8,21H16A2,2 0 0,0 18,19V7H6V19Z" />
                            </svg>
                            <div>
                                {{ __('messages.delete_event') }}
                            </div>
                        </button>
                    </form>
                    @endcan
                </div>
            </div>
        </div>
        @endif

    </div>

    {{-- Mobile Actions dropdown (header) --}}
    @if ($event->exists)
    <div class="lg:hidden relative inline-block text-start">
        <button type="button" class="popup-toggle inline-flex items-center justify-center rounded-lg bg-white dark:bg-gray-800 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-100 shadow-sm border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800" id="mobile-header-event-actions-menu-button" data-popup-target="mobile-header-event-actions-pop-up-menu" aria-expanded="false" aria-haspopup="true">
            {{ __('messages.actions') }}
            <svg class="-me-1 ms-1.5 h-5 w-5 text-gray-400 dark:text-gray-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
            </svg>
        </button>
        <div id="mobile-header-event-actions-pop-up-menu" class="ap-dropdown pop-up-menu hidden absolute {{ is_rtl() ? 'start-0' : 'end-0' }} z-10 mt-2 w-64 {{ is_rtl() ? 'origin-top-left' : 'origin-top-right' }} divide-y divide-gray-100 dark:divide-white/[0.06] rounded-lg ring-1 ring-black/5 dark:ring-white/[0.06] focus:outline-none" role="menu" aria-orientation="vertical" aria-labelledby="mobile-header-event-actions-menu-button" tabindex="-1">
            <div class="py-2" role="none" id="mobile-header-event-actions-pop-up-menu-items" data-popup-target="mobile-header-event-actions-pop-up-menu">
                @if ($activeBoost)
                <a href="{{ route('boost.show', ['hash' => $activeBoost->hashedId()]) }}" class="group flex items-center px-5 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:bg-gray-100 dark:focus:bg-gray-700 focus:outline-none transition-colors" role="menuitem" tabindex="0">
                    <svg class="me-3 h-5 w-5 text-green-500" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M13.13 22.19L11.5 18.36C13.07 17.78 14.54 17 15.9 16.09L13.13 22.19M5.64 12.5L1.81 10.87L7.91 8.1C7 9.46 6.22 10.93 5.64 12.5M19.22 4C19.5 4 19.75 4 19.96 4.05C20.13 5.44 19.94 8.3 16.66 11.58C14.96 13.29 12.93 14.6 10.65 15.47L8.5 13.37C9.42 11.06 10.73 9.03 12.42 7.34C14.71 5.05 17.11 4.1 18.78 4.04C18.91 4 19.06 4 19.22 4Z"/>
                    </svg>
                    <div>{{ __('messages.boosted') }} - {{ number_format($activeBoost->reach) }} {{ __('messages.reach') }}</div>
                </a>
                @elseif ($boostStaticDisabled === 'upgrade' && config('app.hosted'))
                <button type="button" @click.prevent="openUpgrade('upgrade-boost')" class="w-full group flex items-center px-5 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:bg-gray-100 dark:focus:bg-gray-700 focus:outline-none transition-colors" role="menuitem" tabindex="0">
                    <svg class="me-3 h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M13.13 22.19L11.5 18.36C13.07 17.78 14.54 17 15.9 16.09L13.13 22.19M5.64 12.5L1.81 10.87L7.91 8.1C7 9.46 6.22 10.93 5.64 12.5M19.22 4C19.5 4 19.75 4 19.96 4.05C20.13 5.44 19.94 8.3 16.66 11.58C14.96 13.29 12.93 14.6 10.65 15.47L8.5 13.37C9.42 11.06 10.73 9.03 12.42 7.34C14.71 5.05 17.11 4.1 18.78 4.04C18.91 4 19.06 4 19.22 4Z"/>
                    </svg>
                    <div>{{ __('messages.boost_event') }}</div>
                </button>
                @elseif ($boostStaticDisabled)
                <button type="button" @click="showMessage(@js($boostStaticDisabled))" class="w-full group flex items-center px-5 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:bg-gray-100 dark:focus:bg-gray-700 focus:outline-none transition-colors" role="menuitem" tabindex="0">
                    <svg class="me-3 h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M13.13 22.19L11.5 18.36C13.07 17.78 14.54 17 15.9 16.09L13.13 22.19M5.64 12.5L1.81 10.87L7.91 8.1C7 9.46 6.22 10.93 5.64 12.5M19.22 4C19.5 4 19.75 4 19.96 4.05C20.13 5.44 19.94 8.3 16.66 11.58C14.96 13.29 12.93 14.6 10.65 15.47L8.5 13.37C9.42 11.06 10.73 9.03 12.42 7.34C14.71 5.05 17.11 4.1 18.78 4.04C18.91 4 19.06 4 19.22 4Z"/>
                    </svg>
                    <div>{{ __('messages.boost_event') }}</div>
                </button>
                @else
                <a v-cloak v-if="boostCanEnable" href="{{ route('boost.create', ['event_id' => \App\Utils\UrlUtils::encodeId($event->id), 'role_id' => \App\Utils\UrlUtils::encodeId($role->id)]) }}" class="group flex items-center px-5 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:bg-gray-100 dark:focus:bg-gray-700 focus:outline-none transition-colors" role="menuitem" tabindex="0">
                    <svg class="me-3 h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M13.13 22.19L11.5 18.36C13.07 17.78 14.54 17 15.9 16.09L13.13 22.19M5.64 12.5L1.81 10.87L7.91 8.1C7 9.46 6.22 10.93 5.64 12.5M19.22 4C19.5 4 19.75 4 19.96 4.05C20.13 5.44 19.94 8.3 16.66 11.58C14.96 13.29 12.93 14.6 10.65 15.47L8.5 13.37C9.42 11.06 10.73 9.03 12.42 7.34C14.71 5.05 17.11 4.1 18.78 4.04C18.91 4 19.06 4 19.22 4Z"/>
                    </svg>
                    <div>{{ __('messages.boost_event') }}</div>
                </a>
                <button v-cloak v-else type="button" v-on:click="showBoostError()" class="w-full group flex items-center px-5 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:bg-gray-100 dark:focus:bg-gray-700 focus:outline-none transition-colors" role="menuitem" tabindex="0">
                    <svg class="me-3 h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M13.13 22.19L11.5 18.36C13.07 17.78 14.54 17 15.9 16.09L13.13 22.19M5.64 12.5L1.81 10.87L7.91 8.1C7 9.46 6.22 10.93 5.64 12.5M19.22 4C19.5 4 19.75 4 19.96 4.05C20.13 5.44 19.94 8.3 16.66 11.58C14.96 13.29 12.93 14.6 10.65 15.47L8.5 13.37C9.42 11.06 10.73 9.03 12.42 7.34C14.71 5.05 17.11 4.1 18.78 4.04C18.91 4 19.06 4 19.22 4Z"/>
                    </svg>
                    <div>{{ __('messages.boost_event') }}</div>
                </button>
                @endif
                <div class="py-2" role="none">
                    <div class="border-t border-gray-100 dark:border-gray-700"></div>
                </div>
                <a href="{{ route('event.clone', ['subdomain' => $subdomain, 'hash' => \App\Utils\UrlUtils::encodeId($event->id)]) }}" class="group flex items-center px-5 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:bg-gray-100 dark:focus:bg-gray-700 focus:outline-none transition-colors" role="menuitem" tabindex="0">
                    <svg class="me-3 h-5 w-5 text-gray-400 group-hover:text-gray-500 dark:group-hover:text-gray-400" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M19,21H8V7H19M19,5H8A2,2 0 0,0 6,7V21A2,2 0 0,0 8,23H19A2,2 0 0,0 21,21V7A2,2 0 0,0 19,5M16,1H4A2,2 0 0,0 2,3V17H4V3H16V1Z" />
                    </svg>
                    <div>{{ __('messages.clone_event') }}</div>
                </a>
                @if ($role->isPro())
                <button type="button" class="js-save-as-template-open w-full group flex items-center px-5 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:bg-gray-100 dark:focus:bg-gray-700 focus:outline-none transition-colors" role="menuitem" tabindex="0">
                    <svg class="me-3 h-5 w-5 text-gray-400 group-hover:text-gray-500 dark:group-hover:text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
                    </svg>
                    <div>{{ __('messages.save_as_template') }}</div>
                </button>
                @endif
                @can('delete', $event)
                <div class="py-2" role="none">
                    <div class="border-t border-gray-100 dark:border-gray-700"></div>
                </div>
                @if (! $event->is_cancelled)
                <button type="button" @click="openCancelModal()" class="w-full group flex items-center px-5 py-3 text-sm text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-900/20 focus:bg-amber-50 dark:focus:bg-amber-900/20 focus:outline-none transition-colors" role="menuitem" tabindex="0">
                    <svg class="me-3 h-5 w-5 text-amber-400 group-hover:text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                    <div>{{ __('messages.cancel_event') }}</div>
                </button>
                @else
                <button type="button" @click="submitRestore()" :disabled="isSubmittingRestore" class="w-full group flex items-center px-5 py-3 text-sm text-green-600 dark:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/20 focus:outline-none transition-colors" role="menuitem" tabindex="0">
                    <svg class="me-3 h-5 w-5 text-green-400 group-hover:text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
                    <div>{{ __('messages.restore_event') }}</div>
                </button>
                @endif
                <button type="submit" form="event-delete-form" class="w-full group flex items-center px-5 py-3 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-700 dark:hover:text-red-300 focus:bg-red-50 dark:focus:bg-red-900/20 focus:text-red-700 dark:focus:text-red-300 focus:outline-none transition-colors" role="menuitem" tabindex="0">
                    <svg class="me-3 h-5 w-5 text-red-400 group-hover:text-red-500 dark:group-hover:text-red-400" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M19,4H15.5L14.5,3H9.5L8.5,4H5V6H19M6,19A2,2 0 0,0 8,21H16A2,2 0 0,0 18,19V7H6V19Z" />
                    </svg>
                    <div>{{ __('messages.delete_event') }}</div>
                </button>
                @endcan
            </div>
        </div>
    </div>
    @endif
  </div>

  @if ($event->exists && $role->isPro())
  {{-- Save as template: name modal (plain JS; default name is an escaped attribute, not a compiled text node) --}}
  <div id="save-as-template-modal" class="fixed inset-0 z-50 hidden" aria-labelledby="save-as-template-title" role="dialog" aria-modal="true">
      <div class="js-save-as-template-close fixed inset-0 bg-gray-500/75 dark:bg-gray-900/75 transition-opacity"></div>
      <div class="fixed inset-0 z-10 overflow-y-auto">
          <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
              <div class="relative transform overflow-hidden rounded-xl bg-white dark:bg-gray-800 px-4 pb-4 pt-5 text-start shadow-xl dark:shadow-gray-900/50 transition-all sm:my-8 sm:w-full sm:max-w-md sm:p-6">
                  <form method="POST" action="{{ route('event_template.store', ['subdomain' => $subdomain, 'hash' => \App\Utils\UrlUtils::encodeId($event->id)]) }}">
                      @csrf
                      <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-gray-100 mb-2" id="save-as-template-title">{{ __('messages.save_as_template') }}</h3>
                      <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">{{ __('messages.save_as_template_help') }}</p>
                      <label for="save-as-template-input" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('messages.template_name') }}</label>
                      <input type="text" id="save-as-template-input" name="name" value="{{ $event->name }}" required maxlength="255"
                          class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] sm:text-sm" />
                      <div class="mt-6 flex flex-row gap-3">
                          <button type="button"
                              class="js-save-as-template-close flex-1 inline-flex items-center justify-center px-4 py-3 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-base text-gray-700 dark:text-gray-300 shadow-sm transition-all duration-200 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                              {{ __('messages.cancel') }}
                          </button>
                          <button type="submit"
                              class="flex-1 inline-flex items-center justify-center px-4 py-3 bg-[var(--brand-button-bg)] border border-transparent rounded-lg font-semibold text-base text-white shadow-sm transition-all duration-200 hover:bg-[var(--brand-button-bg-hover)] focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                              {{ __('messages.save') }}
                          </button>
                      </div>
                  </form>
              </div>
          </div>
      </div>
  </div>
  <script {!! nonce_attr() !!}>
      // Capture-phase delegation: the trigger buttons live inside .pop-up-menu (whose bubble-phase
      // handler calls stopPropagation) and inside the Vue #app (which re-renders the nodes), so a
      // document-level capture listener is the only robust, CSP-compliant option.
      (function () {
          function openModal() {
              var modal = document.getElementById('save-as-template-modal');
              if (! modal) return;
              modal.classList.remove('hidden');
              var input = document.getElementById('save-as-template-input');
              if (input) { input.focus(); input.select(); }
          }
          function closeModal() {
              var modal = document.getElementById('save-as-template-modal');
              if (modal) modal.classList.add('hidden');
          }
          document.addEventListener('click', function (e) {
              if (! e.target.closest) return;
              if (e.target.closest('.js-save-as-template-open')) { openModal(); }
              else if (e.target.closest('.js-save-as-template-close')) { closeModal(); }
          }, true);
          document.addEventListener('keydown', function (e) {
              if (e.key === 'Escape') closeModal();
          });
      })();
  </script>
  @endif

  <form method="POST"
        @submit="validateForm"
        action="{{ $event->exists ? route('event.update', ['subdomain' => $subdomain, 'hash' => \App\Utils\UrlUtils::encodeId($event->id)]) : route('event.store', ['subdomain' => $subdomain]) }}"
        enctype="multipart/form-data"
        novalidate
        id="edit-form">

        @csrf

        @if ($event->exists)
        @method('put')
        @endif


        <x-text-input name="venue_name" type="hidden" v-model="venueName" />
        <x-text-input name="venue_email" type="hidden" v-model="venueEmail" />                                                                
        <x-text-input name="venue_address1" type="hidden" v-model="venueAddress1" />                                                                
        <x-text-input name="venue_city" type="hidden" v-model="venueCity" />                                                                
        <x-text-input name="venue_state" type="hidden" v-model="venueState" />                                                                
        <x-text-input name="venue_postal_code" type="hidden" v-model="venuePostalCode" />                                                                
        <x-text-input name="venue_country_code" type="hidden" v-model="venueCountryCode" autocomplete="off" />
        {{-- Attendee change-notification decision (issue #94) --}}
        <input type="hidden" name="notify_attendees" :value="notifyAttendees ? 1 : 0">
        <input type="hidden" name="notify_message" :value="notifyMessage">
        <x-text-input name="venue_website" type="hidden" v-model="venueWebsite" />

        <div class="py-5">
            <div class="mx-auto lg:grid lg:grid-cols-12 lg:gap-6">
                <!-- Sidebar Navigation (hidden on small screens, visible on lg+) -->
                <div class="hidden lg:block lg:col-span-3">
                    <div class="sticky top-6">
                        <nav class="space-y-1">
                            @if ($detailsShown)
                            <a href="#section-details" class="section-nav-link" data-section="section-details">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                                <span class="section-nav-text">
                                    <span>{{ __('messages.event') }}</span>
                                    <span class="section-nav-summary" v-cloak :class="{ 'is-empty': tabSummaries['section-details'].empty }"><bdi v-text="tabSummaries['section-details'].text"></bdi></span>
                                </span>
                                <span class="section-nav-dot" v-cloak v-show="sectionDirty['section-details']"></span>
                            </a>
                            @endif
                            {{-- canViewEventData(), not canEditEvent(): this panel carries prices,
                                 payment setup and the link to the sales it produces, and
                                 canEditEvent() has no curator exception - it would hand a
                                 curator's staff the ticket setup for an event another
                                 schedule created. Same predicate TicketController::sales() and
                                 BoxOfficeController use. It used to be
                                 `$event->user_id == $user->id`, which is creator identity
                                 rather than permission, so an Enterprise team admin saw no
                                 Tickets tab at all on their own schedule's events. --}}
                            @if ($user->canViewEventData($event))
                            <a href="#section-tickets" class="section-nav-link" data-section="section-tickets">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z" />
                                </svg>
                                <span class="section-nav-text">
                                    <span>{{ __('messages.tickets') }}</span>
                                    <span class="section-nav-summary" v-cloak :class="{ 'is-empty': tabSummaries['section-tickets'].empty }"><bdi v-text="tabSummaries['section-tickets'].text"></bdi></span>
                                </span>
                                <span class="section-nav-dot" v-cloak v-show="sectionDirty['section-tickets']"></span>
                            </a>
                            @endif
                            <a href="#section-participants" class="section-nav-link" data-section="section-participants" {!! $moreSectionAttrs !!}>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                </svg>
                                <span class="section-nav-text">
                                    <span>{{ __('messages.participants') }}</span>
                                    <span class="section-nav-summary" v-cloak :class="{ 'is-empty': tabSummaries['section-participants'].empty }"><bdi v-text="tabSummaries['section-participants'].text"></bdi></span>
                                </span>
                                <span class="section-nav-dot" v-cloak v-show="sectionDirty['section-participants']"></span>
                            </a>
                            <a href="#section-agenda" class="section-nav-link" data-section="section-agenda" {!! $moreSectionAttrs !!}>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                </svg>
                                <span class="section-nav-text">
                                    <span>{{ __('messages.agenda') }}</span>
                                    <span class="section-nav-summary" v-cloak :class="{ 'is-empty': tabSummaries['section-agenda'].empty }"><bdi v-text="tabSummaries['section-agenda'].text"></bdi></span>
                                </span>
                                <span class="section-nav-dot" v-cloak v-show="sectionDirty['section-agenda']"></span>
                            </a>
                            @if ($galleryShown)
                            <a href="#section-gallery" class="section-nav-link" data-section="section-gallery" {!! $moreSectionAttrs !!}>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                </svg>
                                <span class="section-nav-text">
                                    <span>{{ __('messages.gallery') }}</span>
                                    <span class="section-nav-summary" v-cloak :class="{ 'is-empty': tabSummaries['section-gallery'].empty }"><bdi v-text="tabSummaries['section-gallery'].text"></bdi></span>
                                </span>
                                <span class="section-nav-dot" v-cloak v-show="sectionDirty['section-gallery']"></span>
                                @if ($galleryMode === 'locked')
                                <x-lock-badge tier="pro" />
                                @endif
                            </a>
                            @endif
                            @if ($detailsShown)
                            <a href="#section-listing" class="section-nav-link" data-section="section-listing" {!! $moreSectionAttrs !!}>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span class="section-nav-text">
                                    <span>{{ __('messages.listing') }}</span>
                                    <span class="section-nav-summary" v-cloak :class="{ 'is-empty': tabSummaries['section-listing'].empty }"><bdi v-text="tabSummaries['section-listing'].text"></bdi></span>
                                </span>
                                <span class="section-nav-dot" v-cloak v-show="sectionDirty['section-listing']"></span>
                            </a>
                            @endif
                            @if ($showGoogleSync || $showMicrosoftSync)
                            <a href="#section-calendar-sync" class="section-nav-link" data-section="section-calendar-sync">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                </svg>
                                <span class="section-nav-text">
                                    <span>{{ __('messages.calendar_sync') }}</span>
                                    <span class="section-nav-summary" v-cloak :class="{ 'is-empty': tabSummaries['section-calendar-sync'].empty }"><bdi v-text="tabSummaries['section-calendar-sync'].text"></bdi></span>
                                </span>
                                <span class="section-nav-dot" v-cloak v-show="sectionDirty['section-calendar-sync']"></span>
                            </a>
                            @endif
                            @php $fanContentPendingCount = $event->exists ? (($pendingVideos->count() ?? 0) + ($pendingComments->count() ?? 0) + ($pendingPhotos->count() ?? 0)) : 0; @endphp
                            <a href="#section-engagement" class="section-nav-link" data-section="section-engagement" {!! $moreSectionAttrs !!}>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                                </svg>
                                <span class="section-nav-text">
                                    <span>{{ __('messages.engagement') }}</span>
                                    <span class="section-nav-summary" v-cloak :class="{ 'is-empty': tabSummaries['section-engagement'].empty }"><bdi v-text="tabSummaries['section-engagement'].text"></bdi></span>
                                </span>
                                <span class="section-nav-dot" v-cloak v-show="sectionDirty['section-engagement']"></span>
                            </a>
                            @if ($sponsorsShown)
                            <a href="#section-event-settings" class="section-nav-link" data-section="section-event-settings" {!! $moreSectionAttrs !!}>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" /></svg>
                                <span class="section-nav-text">
                                    <span>{{ __('messages.sponsors') }}</span>
                                    <span class="section-nav-summary" v-cloak :class="{ 'is-empty': tabSummaries['section-event-settings'].empty }"><bdi v-text="tabSummaries['section-event-settings'].text"></bdi></span>
                                </span>
                                <span class="section-nav-dot" v-cloak v-show="sectionDirty['section-event-settings']"></span>
                            </a>
                            @endif
                            @if ($isFirstEventRun)
                            {{-- Not a .section-nav-link: those are collected on load and each one
                                 opens the section its data-section names, which this has none of. --}}
                            <button type="button" v-cloak v-if="!showMoreSections" @click="showMoreSections = true"
                                class="flex w-full items-center gap-2 px-3 py-3 text-lg font-medium text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 transition-all duration-200">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                <span class="section-nav-text">
                                    <span>{{ __('messages.more_options') }}</span>
                                    <span class="section-nav-summary is-empty is-wrap"><bdi v-text="moreTabNames"></bdi></span>
                                </span>
                            </button>
                            @endif
                        </nav>
                    </div>
                </div>

                <!-- Main Content Area -->
                <div class="lg:col-span-9 space-y-6 lg:space-y-0">
                @if ($detailsShown)
                <button type="button" class="mobile-section-header" data-section="section-details">
                    <span class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                        <span class="section-nav-text">
                            <span>{{ __('messages.event') }}</span>
                            <span class="section-nav-summary" v-cloak :class="{ 'is-empty': tabSummaries['section-details'].empty }"><bdi v-text="tabSummaries['section-details'].text"></bdi></span>
                        </span>
                        <span class="section-nav-dot" v-cloak v-show="sectionDirty['section-details']"></span>
                    </span>
                    <svg class="w-5 h-5 text-gray-400 transition-transform duration-200 accordion-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                {{-- The Event tab: what nearly every event needs, asked in the order people think of it.
                     section-plain drops the one-card look every other section has, because this one is
                     several cards. --}}
                <div id="section-details" class="section-content section-plain">
                    <div class="event-tab{{ $isFirstEventRun ? ' event-tab-first max-w-xl' : '' }}">

                        <div class="ap-card rounded-xl p-4 sm:p-6" id="event-basics">
                        <div class="event-basics-grid">

                        <div class="event-basics-name">
                        <div class="mb-6">
                            <x-input-label for="event_name" :value="__('messages.event_name') . ' *'" />
                            <x-text-input id="event_name" name="name" type="text" class="mt-1 block w-full"
                                :value="old('name', $event->name)"
                                v-model="eventName" @keydown.enter="onNameEnter"
                                required autocomplete="off" />
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                            {{-- An event a feed keeps up to date: said where it is edited, because
                                 what is changed here stops following the source. The feed's name
                                 is somebody's own text inside the form's Vue mount: v-pre. --}}
                            @php
                                $eventFeed = $event->keptByFeed();
                            @endphp
                            @if ($eventFeed)
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400" id="event-from-feed">
                                <span v-pre>{{ __('messages.feeds_event_line', ['feed' => $eventFeed->name]) }}</span>
                                @if ((int) $eventFeed->role_id === (int) $role->id)
                                <x-link :href="route('role.feeds.show', ['subdomain' => $role->subdomain, 'hash' => \App\Utils\UrlUtils::encodeId($eventFeed->id)])">{{ __('messages.feeds_open_feed') }}</x-link>
                                @endif
                            </p>
                            @endif
                        </div>
                        </div>

                        <div class="event-basics-when">
                        <div class="mb-6">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div>
                                    <x-input-label for="event_date" :value="__('messages.date_and_time') . ' *'" v-show="!isMultiDay" />
                                    <x-input-label for="event_date" :value="__('messages.start_date') . ' *'" v-show="isMultiDay" />
                                </div>
                                {{-- Whether it repeats is part of when it is, so it sits on the date's own line.
                                     Plain radios wired by id on load (onChangeDateType): they must never sit
                                     under a v-if. --}}
                                <div class="{{ $segShell }}">
                                    <label>
                                        <input id="one_time" name="schedule_type" type="radio" value="one_time" {{ $event->days_of_week ? '' : 'CHECKED' }} class="sr-only peer">
                                        <span class="{{ $segRadio }}">{{ __('messages.one_time') }}</span>
                                    </label>
                                    <label>
                                        <input id="recurring" name="schedule_type" type="radio" value="recurring" {{ $event->days_of_week ? 'CHECKED' : '' }} class="sr-only peer">
                                        <span class="{{ $segRadio }}">{{ __('messages.recurring') }}</span>
                                    </label>
                                </div>
                            </div>
                            <div class="flex flex-wrap sm:flex-nowrap items-center gap-2 sm:gap-3 mt-1">
                                <input type="text" id="event_date"
                                    class="datepicker-date flex-1 min-w-[140px] border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm {{ rtl_class($role, 'rtl', '', true) }}"
                                    value="{{ $eventDate }}" autocomplete="off" placeholder="{{ __('messages.date') }}" aria-label="{{ __('messages.date') }}" />
                                <div class="flex items-center gap-2 sm:gap-3">
                                    <div class="relative w-28 sm:w-32">
                                        <input type="text" id="start_time"
                                            class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm {{ rtl_class($role, 'rtl', '', true) }}"
                                            value="{{ $eventStartTime }}" placeholder="{{ __('messages.start_time') }}"
                                            autocomplete="off" aria-label="{{ __('messages.start_time') }}" />
                                        <div class="time-dropdown" id="start_time_dropdown"></div>
                                    </div>
                                    <span v-show="!isMultiDay" class="text-gray-500 dark:text-gray-400 text-sm shrink-0">{{ __('messages.to') }}</span>
                                    <div v-show="!isMultiDay" class="relative w-28 sm:w-32">
                                        <input type="text" id="end_time"
                                            class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm {{ rtl_class($role, 'rtl', '', true) }}"
                                            value="{{ $eventEndTime }}" placeholder="{{ __('messages.end_time') }}"
                                            autocomplete="off" aria-label="{{ __('messages.end_time') }}" />
                                        <div class="time-dropdown" id="end_time_dropdown"></div>
                                    </div>
                                </div>
                            </div>
                            <p v-if="dateTimeError && dateTimeErrorField !== 'end_date'" v-cloak role="alert" class="mt-2 text-sm text-red-600 dark:text-red-400">@{{ dateTimeError }}</p>
                            @php $eventScheduleTz = (isset($role) && $role && $role->timezone) ? $role->timezone : null; @endphp
                            @if($eventScheduleTz)
                                <p class="mt-1.5 text-sm text-gray-500 dark:text-gray-400">
                                    {{ __('messages.times_are_in_timezone') }} <span v-pre class="font-medium">{{ $eventScheduleTz }}</span>.
                                    <span id="starts_at_preview" data-tz="{{ $eventScheduleTz }}" data-prefix="{{ __('messages.event_appears_as') }}" class="block sm:inline sm:ms-1 text-gray-400 dark:text-gray-500"></span>
                                </p>
                                @if($eventIsOffTimezone)
                                <div class="mt-2 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3 flex items-start gap-2" v-pre>
                                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                    </svg>
                                    <p class="text-sm text-amber-700 dark:text-amber-300">{{ str_replace([':from', ':to'], [$eventEffectiveTz, $eventScheduleTz], __('messages.event_timezone_mismatch_hint')) }}</p>
                                </div>
                                @endif
                            @else
                                <div class="mt-2 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3 flex items-start gap-2">
                                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                    </svg>
                                    <p class="text-sm text-amber-700 dark:text-amber-300">{{ __('messages.schedule_timezone_missing') }}</p>
                                </div>
                            @endif
                            {{-- Attendee change-notification hint (issue #94): shown only when the event has registrants.
                                 The set-up-your-own-email link goes to the Email Settings tab, which only the hosted
                                 service has, so a selfhosted install without a working mailer shows nothing. --}}
                            @if ($event->exists)
                            @if (config('app.hosted'))
                            <p v-if="registrantCount > 0" v-cloak class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                <span v-if="scheduleHasEmailSettings">{{ __('messages.change_notifies_attendees_hint') }}</span>
                                <a v-else href="{{ route('role.edit', ['subdomain' => $subdomain]) }}" class="text-[var(--brand-blue)] hover:underline">{{ __('messages.notify_requires_email_settings') }}</a>
                            </p>
                            @else
                            <p v-if="registrantCount > 0 && scheduleHasEmailSettings" v-cloak class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.change_notifies_attendees_hint') }}</p>
                            @endif
                            @endif
                            {{-- v-show, not v-if: the toggle and the end-date row below are wired once, on
                                 load (their change listener, date picker and time picker). Removing them for
                                 a recurring event put unwired copies back when the event became one-time
                                 again, and multi-day stayed dead until a reload. --}}
                            <div v-show="!isRecurring" class="mt-3 flex justify-end">
                                <x-toggle name="is_multi_day" id="is_multi_day" :label="__('messages.multi_day_event')" :checked="$isMultiDay" />
                            </div>
                            <input type="hidden" name="starts_at" id="starts_at" value="{{ $oldStartsAt }}" />
                            <input type="hidden" name="duration" id="duration" value="{{ $oldDuration }}" />
                            <x-input-error class="mt-2" :messages="$errors->get('starts_at')" />
                            <x-input-error class="mt-2" :messages="$errors->get('duration')" />

                            {{-- Two elements because two things hide this row: Vue while the event recurs, and
                                 the toggle's own listener, which sets the inner row's display itself. --}}
                            <div v-show="!isRecurring">
                            <div id="multi_day_end_date_row" class="mt-3" style="{{ $isMultiDay ? '' : 'display:none' }}">
                                <x-input-label for="event_end_date" :value="__('messages.end_date') . ' *'" />
                                <div class="flex flex-wrap sm:flex-nowrap items-center gap-2 sm:gap-3 mt-1">
                                    <input type="text" id="event_end_date"
                                        class="flex-1 min-w-[140px] border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm {{ rtl_class($role, 'rtl', '', true) }}"
                                        value="{{ $eventEndDate }}" autocomplete="off" aria-label="{{ __('messages.end_date') }}" />
                                    <div class="relative w-28 sm:w-32">
                                        <input type="text" id="end_time_multi"
                                            class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm {{ rtl_class($role, 'rtl', '', true) }}"
                                            value="{{ $eventEndTime }}" placeholder="{{ __('messages.end_time') }}"
                                            autocomplete="off" aria-label="{{ __('messages.end_time') }}" />
                                        <div class="time-dropdown" id="end_time_multi_dropdown"></div>
                                    </div>
                                </div>
                                <p v-if="dateTimeError && dateTimeErrorField === 'end_date'" v-cloak role="alert" class="mt-2 text-sm text-red-600 dark:text-red-400">@{{ dateTimeError }}</p>
                            </div>
                            </div>
                        </div>

                        {{-- The rest of a recurring event's settings, which used to be a tab of their own. --}}
                        <div v-if="isRecurring" class="mb-6">
                            <x-input-label :value="__('messages.frequency')" />
                            <select name="recurring_frequency" v-model="event.recurring_frequency" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                                <option value="daily">{{ __('messages.daily') }}</option>
                                <option value="weekly">{{ __('messages.weekly') }}</option>
                                <option value="every_n_weeks">{{ __('messages.every_n_weeks') }}</option>
                                <option value="monthly_date">{{ __('messages.monthly_same_date') }}</option>
                                <option value="monthly_weekday">{{ __('messages.monthly_same_weekday') }}</option>
                                <option value="yearly">{{ __('messages.yearly') }}</option>
                            </select>
                        </div>

                        <div v-if="isRecurring && event.recurring_frequency === 'every_n_weeks'" class="mb-6">
                            <x-input-label :value="__('messages.repeat_every_n_weeks')" />
                            <x-text-input type="number" name="recurring_interval" class="mt-1 block w-full" min="2" max="52"
                                v-model="event.recurring_interval" />
                        </div>

                        <div id="days_of_week_div" class="mb-6 {{ ! $event || ! $event->days_of_week || !in_array($event->recurring_frequency, ['weekly', 'every_n_weeks', null]) ? 'hidden' : '' }}">
                            <x-input-label :value="__('messages.days_of_week')" />
                            @foreach (['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'] as $index => $day)
                            <label for="days_of_week_{{ $index }}" class="me-3 text-sm font-medium leading-6 text-gray-900 dark:text-gray-100 cursor-pointer">
                                <input type="checkbox" id="days_of_week_{{ $index }}" name="days_of_week_{{ $index }}" class="h-4 w-4 rounded border-gray-300 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]"
                                    {{ $event && $event->days_of_week && $event->days_of_week[$index] == '1' ? 'checked' : '' }}/> &nbsp;
                                {{ __('messages.' . $day) }}
                            </label>
                            @endforeach
                        </div>

                        <div v-if="isRecurring" id="recurring_end_div" class="mb-6">
                            <x-input-label :value="__('messages.recurring_end')" />
                            <div class="mt-2 space-y-4">
                                <div class="flex items-center">
                                    <input id="recurring_end_never" name="recurring_end_type" type="radio" value="never" v-model="event.recurring_end_type"
                                        class="h-4 w-4 border-gray-300 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                                    <label for="recurring_end_never"
                                        class="ms-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100 cursor-pointer">{{ __('messages.never') }}</label>
                                </div>
                                <div class="flex items-center">
                                    <input id="recurring_end_on_date" name="recurring_end_type" type="radio" value="on_date" v-model="event.recurring_end_type"
                                        class="h-4 w-4 border-gray-300 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                                    <label for="recurring_end_on_date"
                                        class="ms-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100 cursor-pointer">{{ __('messages.on_date') }}</label>
                                </div>
                                <div v-if="event.recurring_end_type === 'on_date'" class="ms-7">
                                    <x-text-input type="text" id="recurring_end_date" name="recurring_end_value" class="datepicker-end-date mt-1 block w-full"
                                        value="{{ old('recurring_end_value', $event->recurring_end_value) }}"
                                        autocomplete="off" />
                                    <x-input-error class="mt-2" :messages="$errors->get('recurring_end_value')" />
                                </div>
                                <div class="flex items-center">
                                    <input id="recurring_end_after_events" name="recurring_end_type" type="radio" value="after_events" v-model="event.recurring_end_type"
                                        class="h-4 w-4 border-gray-300 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                                    <label for="recurring_end_after_events"
                                        class="ms-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100 cursor-pointer">{{ __('messages.after_events') }}</label>
                                </div>
                                <div v-if="event.recurring_end_type === 'after_events'" class="ms-7">
                                    <x-text-input type="number" id="recurring_end_count" name="recurring_end_value" class="mt-1 block w-full"
                                        :value="old('recurring_end_value', $event->recurring_end_value)"
                                        v-model="event.recurring_end_value"
                                        min="1" autocomplete="off" />
                                    <x-input-error class="mt-2" :messages="$errors->get('recurring_end_value')" />
                                </div>
                            </div>
                        </div>

                        <div v-if="isRecurring" class="mb-6">
                            <x-input-label :value="__('messages.include_dates')" />
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 mb-3">{{ __('messages.include_dates_help') }}</p>
                            <div id="recurring-include-dates-items">
                                <div v-for="(date, index) in recurringIncludeDates" :key="'inc-' + index" class="mb-2">
                                    <div class="flex items-center">
                                        <input type="text" :class="'datepicker-include-date'" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm bg-gray-50 dark:bg-gray-800" readonly autocomplete="off" />
                                        <input type="hidden" name="recurring_include_dates[]" :value="date" />
                                        <button type="button" @click="removeIncludeDate(index)"
                                            class="ms-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 text-lg leading-none">&times;</button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" @click="addIncludeDate()" class="text-sm text-[var(--brand-blue)] hover:text-[var(--brand-blue-dark)]">
                                + {{ __('messages.add_date') }}
                            </button>
                        </div>

                        <div v-if="isRecurring" class="mb-6">
                            <x-input-label :value="__('messages.exclude_dates')" />
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 mb-3">{{ __('messages.exclude_dates_help') }}</p>
                            <div id="recurring-exclude-dates-items">
                                <div v-for="(date, index) in recurringExcludeDates" :key="'exc-' + index" class="mb-2">
                                    <div class="flex items-center">
                                        <input type="text" :class="'datepicker-exclude-date'" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm bg-gray-50 dark:bg-gray-800" readonly autocomplete="off" />
                                        <input type="hidden" name="recurring_exclude_dates[]" :value="date" />
                                        <button type="button" @click="removeExcludeDate(index)"
                                            class="ms-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 text-lg leading-none">&times;</button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" @click="addExcludeDate()" class="text-sm text-[var(--brand-blue)] hover:text-[var(--brand-blue-dark)]">
                                + {{ __('messages.add_date') }}
                            </button>
                        </div>
                        </div>

                        @if (! $isFirstEventRun)
                        @include('event.partials.flyer')
                        @endif

                        @if($effectiveRole->groups && count($effectiveRole->groups))
                        <div class="mb-6 event-basics-sub">
                            <x-input-label for="current_role_group_id" :value="__('messages.subschedule')" />
                            <select id="current_role_group_id" name="current_role_group_id" data-searchable data-optional class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm {{ rtl_class($role, 'rtl', '', true) }}">
                                <option value="">{{ __('messages.none') }}</option>
                                @foreach($effectiveRole->groups as $group)
                                    @php
                                        $selectedGroupId = null;
                                        if ($event->exists) {
                                            $selectedGroupId = $event->getGroupIdForSubdomain($effectiveRole->subdomain);
                                            if ($selectedGroupId) {
                                                $selectedGroupId = \App\Utils\UrlUtils::encodeId($selectedGroupId);
                                            }
                                        }
                                    @endphp
                                    <option v-pre value="{{ \App\Utils\UrlUtils::encodeId($group->id) }}" {{ old('current_role_group_id', $selectedGroupId) == \App\Utils\UrlUtils::encodeId($group->id) ? 'selected' : '' }}>{{ $group->translatedName() }}</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('current_role_group_id')" />
                        </div>
                        @endif

                        </div>
                        </div>

                        {{-- Where it is, as a section of its own. --}}
                        <div class="ap-card rounded-xl p-4 sm:p-6" id="event-location">
                        {{-- Where it is. Everything the Venue tab held, in the same Vue values and under the
                             same ids; only what shows first is different. --}}
                        <div class="event-basics-location">
                            <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                                <span class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{ __('messages.location') }}</span>
                                {{-- Two checkboxes, not a choice between them: an event can be both. --}}
                                <div class="event-pills">
                                    <label class="event-pill">
                                        <input id="in_person" name="event_type" type="checkbox" v-model="isInPerson" class="sr-only"
                                            :disabled="roleIsVenue" @change="onChangeVenueType('in_person')">
                                        <span>{{ __('messages.in_person') }}</span>
                                    </label>
                                    <label class="event-pill">
                                        <input id="online" name="event_type" type="checkbox" v-model="isOnline" class="sr-only"
                                            @change="onChangeVenueType('online')">
                                        <span>{{ __('messages.online') }}</span>
                                    </label>
                                </div>
                            </div>

                            <x-text-input name="venue_id" v-bind:value="selectedVenue.id" type="hidden" />
                            {{-- Marks that the venue field was submitted, so the backend treats a previously-attached
                                 venue absent from this request as an intentional removal. Absent from API/import
                                 submissions, which therefore preserve the existing venue. --}}
                            <input type="hidden" name="venue_submitted" value="1">

                            <div v-if="isInPerson">
                                <div v-if="!selectedVenue || showVenueAddressFields">
                                    {{-- Someone who already has venues picks one first, as they always have. --}}
                                    <div v-if="!selectedVenue && Object.keys(venues).length > 0 && venueType === 'use_existing'">
                                        {{-- The rooms this schedule was last at. A venue's name is its owner's
                                             text: v-text, never server-rendered into the mount. --}}
                                        <div v-if="recentVenues.length" class="event-chips" id="recent-venues">
                                            <span class="event-chips-label">{{ __('messages.recent_venues') }}</span>
                                            <button type="button" class="event-chip" v-for="venue in recentVenues" :key="venue.id" @click="selectedVenue = venue"><bdi v-text="venue.name || venue.address1"></bdi></button>
                                        </div>
                                        <div class="flex items-center justify-between gap-3">
                                            <x-input-label for="selected_venue" :value="__('messages.all_your_venues')" />
                                            <button type="button" class="event-link" @click="venueType = 'create_new'">{{ __('messages.new_venue') }}</button>
                                        </div>
                                        <select id="selected_venue"
                                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm {{ rtl_class($role, 'rtl', '', true) }}"
                                                v-model="selectedVenue">
                                                <option value="" disabled selected>{{ __('messages.please_select') }}</option>                                
                                                <option v-for="venue in venues" :key="venue.id" :value="venue">
                                                    @{{ venue.name || venue.address1 }} <template v-if="venue.email">(@{{ venue.email }})</template>
                                                </option>
                                        </select>

                                        {{-- Static text and a route() href only. This sits inside the Vue mount, so
                                             anything user-controlled here would be compiled as a template. --}}
                                        @if (! empty($duplicateVenueGroupCount))
                                        <div class="mt-2 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3 flex items-start gap-3" v-pre>
                                            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                            </svg>
                                            <div class="text-sm text-gray-800 dark:text-gray-200 flex-1">
                                                {{ __('messages.duplicate_venues_hidden') }}
                                                <x-link href="{{ route('following.merge_venues') }}">{{ __('messages.review_duplicate_venues') }}</x-link>
                                            </div>
                                        </div>
                                        @endif
                                    </div>

                                    <div v-if="showAddressFields()">
                                        {{-- Signals that the venue fields were submitted from the editable form, so a blank is an
                                             intentional clear (EventRepo honors blanks via has() only when this is set). Absent from
                                             programmatic callers/imports, which keep filled() so a blank never wipes shared venue data. --}}
                                        <input type="hidden" name="venue_details_editable" value="1">

                                        <div class="event-loc3">
                                            <div>
                                                <x-input-label for="venue_name" :value="__('messages.venue_name')" />
                                                <x-text-input id="venue_name" name="venue_name" type="text"
                                                    class="mt-1 block w-full" v-model="venueName" autocomplete="off" />
                                                <x-input-error class="mt-2" :messages="$errors->get('venue_name')" />
                                            </div>
                                            <div>
                                                <x-input-label for="venue_address1" :value="__('messages.street_address')" />
                                                <x-text-input id="venue_address1" name="venue_address1" type="text"
                                                    class="mt-1 block w-full" v-model="venueAddress1" autocomplete="off" />
                                                <x-input-error class="mt-2" :messages="$errors->get('venue_address1')" />
                                            </div>
                                            <div>
                                                <x-input-label for="venue_city" :value="__('messages.city')" />
                                                <x-text-input id="venue_city" name="venue_city" type="text" class="mt-1 block w-full"
                                                    v-model="venueCity" autocomplete="off" />
                                                <x-input-error class="mt-2" :messages="$errors->get('venue_city')" />
                                            </div>
                                        </div>

                                        {{-- Typing the name of a venue that is already saved: offer it, so the
                                             same room is not created a second time. --}}
                                        <div v-if="venueNameMatches.length" class="event-chips mt-3" id="venue-name-matches">
                                            <span class="event-chips-label">{{ __('messages.saved_venues') }}</span>
                                            <button type="button" class="event-chip" v-for="venue in venueNameMatches" :key="venue.id" @click="selectedVenue = venue"><bdi v-text="[venue.name, venue.city].filter(Boolean).join(', ')"></bdi></button>
                                        </div>

                                        <div class="event-links">
                                            <button type="button" class="event-link" v-show="! showVenueContact" @click="openVenueContact">{{ __('messages.find_venue_by_contact') }}</button>
                                            <button type="button" class="event-link" v-show="! showVenueMore" @click="openVenueMore">{{ __('messages.venue_more_address') }}</button>
                                            <button type="button" class="event-link" v-if="! selectedVenue && Object.keys(venues).length > 0" @click="venueType = 'use_existing'">{{ __('messages.saved_venues') }}</button>
                                        </div>

                                        {{-- Matching a venue by its email or phone, exactly as before: the lookup on leaving
                                             the email, the lookup from eight digits of a phone, the results with Select,
                                             and the invitation box. v-show, never v-if: the phone widget is set up by
                                             watchers and has to stay on the page while it is folded. --}}
                                        <div v-show="showVenueContact" class="mt-4">
                                            <div class="event-loc2">
                                                <div>
                                                    <x-input-label for="venue_email" :value="__('messages.email')" />
                                                    <x-text-input id="venue_email" name="venue_email" type="email" class="mt-1 block w-full"
                                                        @blur="searchVenues" v-model="venueEmail" autocomplete="off" />
                                                    <x-input-error class="mt-2" :messages="$errors->get('venue_email')" />
                                                </div>
                                                <div>
                                                    <x-input-label for="venue_phone_input" :value="__('messages.phone_number')" />
                                                    <input type="hidden" name="venue_phone" v-model="venuePhone">
                                        <input type="tel" id="venue_phone_input" ref="venuePhoneInput"
                                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm"
                                            autocomplete="off" />
                                                </div>
                                            </div>
                                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ __('messages.venue_lookup_help') }}</p>
                                            <div class="mt-4">
                                    <div v-if="(venueType === 'create_new' || !selectedVenue.user_id) && ((venueEmail && isHosted) || (venuePhone && smsConfigured))" class="mb-6">
                                        <div class="flex items-center">
                                            <template v-if="venueEmail && isHosted">
                                                <input id="send_email_to_venue" name="send_email_to_venue" type="checkbox" v-model="sendEmailToVenue"
                                                    class="h-4 w-4 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)] border-gray-300 rounded">
                                                <label for="send_email_to_venue" class="ms-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">
                                                    {{ __('messages.send_email_to_notify_them') }}
                                                </label>
                                            </template>
                                            <template v-else-if="venuePhone && smsConfigured">
                                                <input id="send_sms_to_venue" name="send_sms_to_venue" type="checkbox" v-model="sendSmsToVenue"
                                                    class="h-4 w-4 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)] border-gray-300 rounded">
                                                <label for="send_sms_to_venue" class="ms-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">
                                                    {{ __('messages.send_sms_to_notify_them') }}
                                                </label>
                                            </template>
                                        </div>
                                    </div>
                                            </div>
                                            <div class="mt-4">
                                    <div v-if="venueSearchResults.length" class="mb-6">
                                        <div class="space-y-2">
                                            <div v-for="venue in venueSearchResults" :key="venue.id" class="flex items-center justify-between">
                                                <div class="flex items-center">
                                                    <span class="text-sm text-gray-900 dark:text-gray-100 truncate">
                                                        <a :href="venue.url" target="_blank" class="hover:underline">@{{ venue.name }}</a>:
                                                        @{{ venue.address1 }}
                                                    </span>
                                                </div>
                                                <x-brand-button size="sm" @click="selectVenue(venue)">
                                                    {{ __('messages.select') }}
                                                </x-brand-button>
                                            </div>
                                        </div>
                                    </div>
                                            </div>
                                        </div>

                                        <div v-show="showVenueMore" class="mt-4">
                                            <div class="event-loc3">
                                                <div>
                                                    <x-input-label for="venue_state" :value="__('messages.state_province')" />
                                                    <x-text-input id="venue_state" name="venue_state" type="text" class="mt-1 block w-full"
                                                        v-model="venueState" autocomplete="off" />
                                                    <x-input-error class="mt-2" :messages="$errors->get('venue_state')" />
                                                </div>
                                                <div>
                                                    <x-input-label for="venue_postal_code" :value="__('messages.postal_code')" />
                                                    <x-text-input id="venue_postal_code" name="venue_postal_code" type="text"
                                                        class="mt-1 block w-full" v-model="venuePostalCode" autocomplete="off" />
                                                    <x-input-error class="mt-2" :messages="$errors->get('venue_postal_code')" />
                                                </div>
                                                <div>
                                                    <x-input-label for="venue_country_code" :value="__('messages.country')" />
                                                    <x-country-input id="venue_country_code" name="venue_country_code" :auto-init="false" :value="$selectedVenue && $selectedVenue->country ? $selectedVenue->country : ($role && $role->country_code ? $role->country_code : '')" />
                                                    <x-input-error class="mt-2" :messages="$errors->get('venue_country_code')" />
                                                </div>
                                            </div>
                                            <div class="mt-4">
                                                <x-input-label for="venue_website" :value="__('messages.website')" />
                                                <x-text-input id="venue_website" name="venue_website" type="url"
                                                    class="mt-1 block w-full" v-model="venueWebsite" autocomplete="off" />
                                                <x-input-error class="mt-2" :messages="$errors->get('venue_website')" />
                                            </div>
                                            <div class="mt-4 flex flex-wrap items-center gap-3">
                                                {{-- Vue handlers, not listeners bound by id after load: these fields sit under
                                                     v-if, so every change of venue puts NEW buttons on the page, and a listener
                                                     on the old ones is gone. --}}
                                                <x-secondary-button id="view_map_button" @click="viewVenueMap">{{ __('messages.view_map') }}</x-secondary-button>
                                                @if (config('services.google.backend'))
                                                <x-secondary-button id="validate_button" @click="validateVenueAddress">{{ __('messages.validate_address') }}</x-secondary-button>
                                                <x-secondary-button id="accept_button" class="hidden" @click="acceptVenueAddress">{{ __('messages.accept') }}</x-secondary-button>
                                                @endif
                                            </div>
                                            <div id="address_response" class="mt-4 hidden text-gray-900 dark:text-gray-100"></div>
                                        </div>

                                        <div v-if="showVenueAddressFields" class="mt-4">
                                            <x-brand-button size="sm" @click="updateSelectedVenue()">{{ __('messages.done') }}</x-brand-button>
                                        </div>
                                    </div>
                                </div>
                                {{-- A chosen venue is one line, however it was chosen. --}}
                                <div v-else class="event-picked">
                                    <span class="min-w-0 truncate text-sm text-gray-900 dark:text-gray-100">
                                        <template v-if="selectedVenue.url">
                                            <a :href="selectedVenue.url" target="_blank" class="hover:underline" v-text="pickedVenueLine"></a>
                                        </template>
                                        <template v-else>
                                            <span v-text="pickedVenueLine"></span>
                                        </template>
                                        <template v-if="venueEmail">
                                            (<a :href="'mailto:' + venueEmail" class="hover:underline">@{{ venueEmail }}</a>)
                                        </template>
                                    </span>
                                    <span class="flex flex-none items-center gap-3">
                                        <button v-if="!selectedVenue.user_id" @click="editSelectedVenue" type="button" class="event-link">{{ __('messages.edit') }}</button>
                                        <button v-if="!roleIsVenue" @click="clearSelectedVenue" type="button" class="event-link">{{ __('messages.change') }}</button>
                                    </span>
                                </div>
                            </div>

                            <div v-if="isOnline" class="mt-4">
                                <x-input-label for="event_url" :value="__('messages.event_url')" />
                                <x-text-input id="event_url" name="event_url" type="url" class="mt-1 block w-full"
                                    v-model="event.event_url" autocomplete="off" />
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('messages.event_url_help') }}</p>
                                <x-input-error class="mt-2" :messages="$errors->get('event_url')" />
                            </div>
                            <div v-if="!isOnline">
                                <input type="hidden" name="event_url" value="" />
                            </div>
                        </div>
                        </div>

                        <div class="ap-card rounded-xl" id="event-about">
                            @if ($isFirstEventRun)
                            {{-- On a first event the flyer waits here, after the name, the date and the place. --}}
                            <div class="event-about-flyer">
                                @include('event.partials.flyer')
                            </div>
                            @endif
                            <button type="button" class="event-row" @click="aboutOpen = ! aboutOpen"
                                :aria-expanded="aboutOpen ? 'true' : 'false'" aria-controls="event-about-body">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="event-row-icon" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h10.5" />
                                </svg>
                                <span class="event-row-title">{{ __('messages.about') }}</span>
                                <span class="event-row-summary" v-cloak v-show="! aboutOpen" :class="{ 'is-empty': ! aboutSummary }"><bdi v-text="aboutSummary || tabLabels.about_prompt"></bdi></span>
                                <svg class="event-row-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            {{-- v-show, with the inline style standing in until Vue mounts: the description is a
                                 markdown editor wired once on load, so it is hidden and never removed. --}}
                            <div id="event-about-body" class="event-row-body" v-show="aboutOpen" @if (! $aboutOpenOnLoad) style="display: none" @endif>
                            <div class="max-w-xl">
                            <div class="event-ai-row mb-4 flex justify-end">
                            @if ((config('services.google.gemini_key') || config('services.openai.api_key')) && !is_demo_mode())
                                @if ($role->isEnterprise())
                                    <button type="button" @click.prevent="openModal('ai-event-details')"
                                        class="inline-flex items-center px-2 py-1 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-xs font-medium rounded-lg transition-colors border border-gray-300 dark:border-gray-600"
                                        title="{{ __('messages.ai_generator') }}">
                                        <svg class="w-4 h-4 ltr:mr-1 rtl:ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
                                        </svg>
                                        {{ __('messages.ai_generator') }}
                                    </button>
                                @elseif (config('app.hosted') && ! $isFirstEventRun)
                                    <button type="button" @click.prevent="openUpgrade('upgrade-ai-details')"
                                        class="inline-flex items-center px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500 text-xs font-medium rounded-lg border border-gray-300 dark:border-gray-600 opacity-75"
                                        title="{{ __('messages.ai_generator') }}">
                                        <svg class="w-4 h-4 ltr:mr-1 rtl:ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
                                        </svg>
                                        {{ __('messages.ai_generator') }}
                                    </button>
                                @endif
                            @endif
                            </div>

                        <div class="mb-6">
                            <x-input-label for="short_description" :value="__('messages.short_description')" />
                            <x-text-input id="short_description" name="short_description" type="text" class="mt-1 block w-full" :value="old('short_description', $event->short_description)" maxlength="200" autocomplete="off" />
                            <x-input-error class="mt-2" :messages="$errors->get('short_description')" />
                        </div>

                        <div class="mb-6">
                            <x-input-label for="description" :value="__('messages.description')" />
                            {{-- v-pre: Vue compiles the mustaches inside a <textarea> too, and this text is read by
                                 every schedule the event is listed on. --}}
                            <textarea v-pre id="description" name="description" data-content-dir="{{ content_dir($role) }}"
                                class="html-editor mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm"
                                autocomplete="off">{{ old('description', $event->description) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('description')" />
                        </div>

                            @if ($role->isPro() && count($role->getEventCustomFields()) > 0)
                            @php
                                $eventCustomFields = $role->getEventCustomFields();
                                // Only this schedule's own answers: values another schedule saved are keyed by
                                // ITS fields, and prefilling them here would show a talent's private "Fee" in
                                // this venue's "Notes" and save it as this schedule's answer.
                                $customFieldValues = $event->customFieldValuesBelongTo($role, unknownCounts: true) ? $event->getCustomFieldValues() : [];
                            @endphp

                            @foreach($eventCustomFields as $fieldKey => $field)
                            <x-custom-field-input
                                :role="$role"
                                :field="$field"
                                :field-key="$fieldKey"
                                :value="$customFieldValues[$fieldKey] ?? ''" />
                            @endforeach
                            @endif
                            </div>
                            </div>
                        </div>

                    </div>
                </div>
                @endif
                    {{-- Same predicate as the sidebar link above; the two must not diverge. --}}
                    @if ($user->canViewEventData($event))
                    <button type="button" class="mobile-section-header" data-section="section-tickets">
                        <span class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z" />
                            </svg>
                            <span class="section-nav-text">
                                <span>{{ __('messages.tickets') }}</span>
                                <span class="section-nav-summary" v-cloak :class="{ 'is-empty': tabSummaries['section-tickets'].empty }"><bdi v-text="tabSummaries['section-tickets'].text"></bdi></span>
                            </span>
                            <span class="section-nav-dot" v-cloak v-show="sectionDirty['section-tickets']"></span>
                        </span>
                        <svg class="w-5 h-5 text-gray-400 transition-transform duration-200 accordion-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div id="section-tickets" class="section-content lg:mt-0">
                        <div class="{{ $isFirstEventRun ? 'max-w-xl' : 'event-tickets-wide' }}">                                                
                            <div class="mb-6 flex items-center justify-between">
                                <h2 class="section-heading-name text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z" />
                                    </svg>
                                    {{ __('messages.tickets') }}
                                </h2>
                                <span class="flex flex-wrap items-center gap-x-4 gap-y-1">
                                @if ($ticketsSoldOnLoad > 0)
                                <span class="text-sm text-gray-500 dark:text-gray-400" v-cloak v-text="ticketSoldLine"></span>
                                <a href="{{ route('sales', ['filter' => $event->name]) }}" class="event-row-link" style="margin-inline-end: 0">{{ __('messages.sales') }}</a>
                                @endif
                                @if ($event->exists && $role->isPro() && !$event->is_private)
                                <a href="#" id="embed-ticket-link" v-show="event.tickets_enabled || event.rsvp_enabled"
                                    class="js-open-embed-ticket-modal inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 transition-colors">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                        <path d="M12.89,3L14.85,3.4L11.11,21L9.15,20.6L12.89,3M19.59,12L16,8.41V5.58L22.42,12L16,18.41V15.58L19.59,12M1.58,12L8,5.58V8.41L4.41,12L8,15.58V18.41L1.58,12Z" />
                                    </svg>
                                    {{ $event->rsvp_enabled && !$event->tickets_enabled ? __('messages.embed_registration') : __('messages.embed_tickets') }}
                                </a>
                                @endif
                                </span>
                            </div>

                            <input type="hidden" name="rsvp_enabled" :value="event.rsvp_enabled ? 1 : 0">
                            <input type="hidden" name="tickets_enabled" :value="event.tickets_enabled ? 1 : 0">
                            <fieldset :class="{ 'mb-6': ticketChoice }">
                                {{-- How people sign up. The tiles ARE the mode (ticketMode): pressing one turns it on
                                     and shows what it needs right under it; "Not needed" turns it off.
                                     ticket-mode-radio and value are what the Help link reads (layouts/navigation). --}}
                                <div class="event-tiles" role="group" aria-label="{{ __('messages.tickets') }}">
                                    <button type="button" class="event-tile ticket-mode-radio" id="ticket_choice_rsvp" value="rsvp" :class="{ 'is-on': ticketChoice === 'rsvp' }"
                                        :aria-pressed="ticketChoice === 'rsvp' ? 'true' : 'false'" @click="chooseTickets('rsvp')">
                                        <span class="event-tile-title">{{ __('messages.free_registration') }}</span>
                                        <span class="event-tile-help">{{ __('messages.free_registration_help') }}</span>
                                    </button>
                                    <button type="button" class="event-tile ticket-mode-radio" id="ticket_choice_tickets" value="tickets" :class="{ 'is-on': ticketChoice === 'tickets' }"
                                        :aria-pressed="ticketChoice === 'tickets' ? 'true' : 'false'" @click="chooseTickets('tickets')">
                                        <span class="event-tile-title">{{ __('messages.sell_tickets') }}</span>
                                        <span class="event-tile-help">{{ __('messages.sell_tickets_help') }}</span>
                                    </button>
                                    <button type="button" class="event-tile ticket-mode-radio" id="ticket_choice_external" value="external" :class="{ 'is-on': ticketChoice === 'external' }"
                                        :aria-pressed="ticketChoice === 'external' ? 'true' : 'false'" @click="chooseTickets('external')">
                                        <span class="event-tile-title">{{ __('messages.tickets_elsewhere') }}</span>
                                        <span class="event-tile-help">{{ __('messages.tickets_elsewhere_help') }}</span>
                                    </button>
                                </div>
                                {{-- Always on the page, and shown as the one that is chosen when no tile is: with
                                     nothing selected and no line under the tiles, the tab did not say that tickets
                                     were off. --}}
                                <div class="mt-2 flex justify-end" v-cloak>
                                    <button type="button" class="event-link event-link-quiet" id="ticket_choice_none" @click="chooseTickets(null)"
                                        :class="{ 'is-current': ! ticketChoice }" :aria-pressed="ticketChoice ? 'false' : 'true'">{{ __('messages.not_needed') }}</button>
                                </div>
                                @if ($paymentRowWarning)
                                {{-- Said where tickets are switched on, not only on the closed Payment row. --}}
                                <div v-cloak v-show="ticketChoice === 'tickets' && anyTicketPriced && paymentWarning" class="mt-3 flex items-start gap-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3">
                                    <svg class="w-5 h-5 flex-shrink-0 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
                                    <p class="text-sm text-amber-800 dark:text-amber-200">
                                        <span v-text="paymentWarning"></span>
                                        <button type="button" class="event-link ms-2" @click="activeTicketTab = 'payment'">{{ __('messages.payment') }}</button>
                                    </p>
                                </div>
                                @endif

                                {{-- The evergreen place a free organizer learns what their plan
                                     covers here: free registration is unlimited, priced rows are Pro. --}}
                                {{-- The schedule whose plan decides - the event's creator once it
                                     exists - not whichever schedule this form was reached through.
                                     The hint, the countdown and the paywall banner below all read it,
                                     or a venue admin editing a talent's event is told about the
                                     venue's trial above a banner saying the rows cannot sell. --}}
                                @php
                                    $sellingRole = ($event->exists ? $event->ticketingRole() : null) ?? $role;
                                @endphp
                                @if ($sellingRole->onTicketTrial())
                                <p v-cloak v-show="ticketChoice === 'tickets'" class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                                    {{ trans_choice('messages.ticket_trial_days_left', $sellingRole->ticketTrialDaysRemaining(), ['count' => $sellingRole->ticketTrialDaysRemaining()]) }}
                                    @if ($sellingRole->user_id === $user->id)
                                    <x-link href="{{ route('role.view_admin', ['subdomain' => $sellingRole->subdomain, 'tab' => 'plan']) }}">{{ __('messages.plan') }}</x-link>
                                    @endif
                                </p>
                                @elseif (! $sellingRole->isPro())
                                <p v-cloak v-show="ticketChoice === 'tickets'" class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                                    {{ __('messages.ticket_mode_free_hint') }}
                                </p>
                                @endif
                            </fieldset>

                            <!-- Registration URL (only visible when tickets and RSVP are disabled) -->
                            {{-- What a switched-off choice holds is still sent, as hidden fields (which the browser does
                                 not check): a saved link stays saved while tickets are sold here, since the guest page
                                 falls back on it when the plan cannot sell, and "Not needed" empties them all
                                 (chooseTickets). --}}
                            <template v-if="ticketChoice !== 'external'">
                                <input type="hidden" name="registration_url" :value="event.registration_url || ''">
                                <input type="hidden" name="ticket_price" :value="event.ticket_price === null || event.ticket_price === undefined ? '' : event.ticket_price">
                                <input type="hidden" name="coupon_code" :value="event.coupon_code || ''">
                                <input type="hidden" name="coupon_discount_type" :value="event.coupon_discount_type || 'fixed'">
                                <input type="hidden" name="coupon_discount" :value="event.coupon_discount === null || event.coupon_discount === undefined ? '' : event.coupon_discount">
                            </template>
                            <input type="hidden" name="rsvp_limit" v-if="ticketChoice !== 'rsvp'" :value="event.rsvp_limit >= 1 ? event.rsvp_limit : ''">
                            {{-- The fields of a choice that is not the one chosen are switched off, not just hidden: a
                                 link typed under "Tickets elsewhere" and left there when "Sell tickets" was pressed
                                 was still checked by the browser (an invalid one refused the save with nothing on
                                 screen to show why) and still stored. --}}
                            <fieldset class="mb-6 event-fieldset" v-show="ticketChoice === 'external'" :disabled="ticketChoice !== 'external'">
                                <x-input-label for="registration_url" :value="__('messages.registration_url')" />
                                <x-text-input id="registration_url" name="registration_url" type="url" class="mt-1 block w-full"
                                    v-model="event.registration_url" />
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.registration_url_help') }}</p>
                            </fieldset>

                            <!-- External Event Price (only visible when tickets and RSVP are disabled) -->
                            <fieldset class="mb-6 event-fieldset" v-show="ticketChoice === 'external'" :disabled="ticketChoice !== 'external'">
                                <x-input-label :value="__('messages.price')" />
                                <div class="mt-1 flex flex-col sm:flex-row gap-3">
                                    <select name="ticket_currency_code" v-model="event.ticket_currency_code" data-searchable @disabled($currencyLocked ?? false)
                                        class="w-full sm:w-28 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                                        @foreach ($currencies as $currency)
                                        @if ($loop->index == 2)
                                        <option disabled>──────</option>
                                        @endif
                                        <option value="{{ $currency->value }}">{{ $currency->value }}</option>
                                        @endforeach
                                    </select>
                                    <x-text-input type="number" name="ticket_price" step="0.01" min="0"
                                        class="flex-1" v-model="event.ticket_price" />
                                </div>
                                {{-- Locked once the event has taken money (EventController::edit()). A disabled select
                                     posts nothing, so the save keeps the stored currency. --}}
                                @if (($currencyLocked ?? false) && ! $errors->has('ticket_currency_code'))
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.currency_locked_after_sales') }}</p>
                                @endif
                                <x-input-error class="mt-2" :messages="$errors->get('ticket_currency_code')" />
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.external_price_help') }}</p>
                            </fieldset>

                            <fieldset class="mb-6 event-fieldset" v-show="ticketChoice === 'external'" :disabled="ticketChoice !== 'external'">
                                <x-input-label for="coupon_code" :value="__('messages.coupon_code')" />
                                <x-text-input id="coupon_code" name="coupon_code" type="text" class="mt-1 block w-full"
                                    v-model="event.coupon_code" maxlength="255" />
                                <x-input-error class="mt-2" :messages="$errors->get('coupon_code')" />
                            </fieldset>

                            <!-- What the coupon is worth (only visible when tickets and RSVP are disabled) -->
                            <fieldset class="mb-6 event-fieldset" v-show="ticketChoice === 'external'" :disabled="ticketChoice !== 'external'">
                                <x-input-label for="coupon_discount" :value="__('messages.discount')" />
                                <div class="mt-1 flex flex-col sm:flex-row gap-3">
                                    <select name="coupon_discount_type" v-model="event.coupon_discount_type"
                                        class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                                        <option value="fixed">@{{ event.ticket_currency_code }}</option>
                                        <option value="percentage">%</option>
                                    </select>
                                    <x-text-input id="coupon_discount" type="number" name="coupon_discount" step="0.01" min="0"
                                        class="flex-1" v-model="event.coupon_discount" />
                                </div>
                                <x-input-error class="mt-2" :messages="$errors->get('coupon_discount')" />
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.coupon_discount_help') }}</p>
                            </fieldset>

                            <div v-show="event.tickets_enabled || event.rsvp_enabled">

                                {{-- Paid selling is Pro/Enterprise. Free schedules keep unlimited free
                                     registration and $0 ticket rows, so this shows only when the event
                                     actually has a priced row that cannot sell.

                                     Keyed on the SCHEDULE, not on $event->canSellPaidTickets(): the
                                     create page passes an unsaved Event whose creator_role_id is null
                                     (EventController::create() sets it only on the clone branch), so
                                     ticketingRole() returns null there and the event-level call would
                                     answer "cannot sell" for every schedule, Pro and Enterprise
                                     included.

                                     WHETHER the schedule can sell is decided here; whether a priced
                                     row exists is decided live by Vue (ticketsNeedPro), so the banner
                                     appears as a price is typed rather than after a save and reload.
                                     That includes the first-event form: this is not one of the
                                     "locked upgrade controls" that form hides, it is the consequence
                                     of the price just typed, and a first event is where most first
                                     tickets are made. Starts hidden inline, which Vue 3.4+'s v-show
                                     treats as "no original display", so nothing flashes before mount. --}}
                                @php
                                    // Saved events ask the EVENT, because User::canEditEvent()
                                    // grants edit to an owner or admin of any attached schedule -
                                    // so an admin of an attached Pro venue editing a free
                                    // creator's event would see no banner while its paid rows were
                                    // dead. Unsaved events have to ask $role: the create page
                                    // passes an Event with creator_role_id null, so ticketingRole()
                                    // is null and the event-level call would warn every schedule,
                                    // Pro and Enterprise included.
                                    $cannotSellPaid = config('app.hosted')
                                        && ($event->exists ? ! $event->canSellPaidTickets() : ! $role->canSellPaidTickets());
                                    $ticketsNeedProOnLoad = $cannotSellPaid
                                        && $event->tickets_enabled
                                        && $event->tickets->contains(fn ($t) => ! $t->is_addon && (float) $t->price > 0);
                                    // $sellingRole is set above the ticket-mode hint.
                                    $ownsSellingRole = $sellingRole->user_id === $user->id;
                                    $offerTicketTrial = $cannotSellPaid && $ownsSellingRole && $sellingRole->isEligibleForTicketTrial();
                                @endphp
                                @if ($cannotSellPaid)
                                <x-plan-gate
                                    variant="banner"
                                    tier="pro"
                                    class="mb-4"
                                    :role="$sellingRole"
                                    :subdomain="$sellingRole->subdomain"
                                    source="tickets"
                                    :canUpgrade="$ownsSellingRole"
                                    :learnMoreUrl="marketing_url('/features/ticketing')"
                                    :title="__('messages.tickets_need_pro_title')"
                                    :style="$ticketsNeedProOnLoad ? null : 'display: none'"
                                    v-show="ticketsNeedPro">
                                    {{ __('messages.tickets_need_pro_body') }}
                                    @if ($offerTicketTrial)
                                    {{-- Started in place with fetch rather than a form: this banner
                                         sits inside the event form, so a nested form would submit
                                         the event instead, and a redirect would lose the unsaved
                                         price the organizer just typed. Forward action last. --}}
                                    <x-slot:actions>
                                        <a href="{{ route('role.subscribe', ['subdomain' => $sellingRole->subdomain, 'tier' => 'pro', 'source' => 'tickets']) }}"
                                           class="text-sm font-medium text-amber-900 dark:text-amber-100 underline">{{ __('messages.upgrade_to_pro_plan') }}</a>
                                        <button type="button" @click="startTicketTrial" :disabled="ticketTrialStarting"
                                            class="inline-flex items-center gap-1.5 rounded-lg bg-[var(--brand-button-bg)] px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:bg-[var(--brand-button-bg-hover)] focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-60">
                                            {{ __('messages.ticket_trial_start', ['days' => (int) config('app.trial_days', 7)]) }}
                                        </button>
                                        <p v-show="ticketTrialError" style="display: none" class="w-full text-sm text-red-700 dark:text-red-400" role="alert">@{{ ticketTrialError }}</p>
                                    </x-slot:actions>
                                    @endif
                                </x-plan-gate>
                                @if ($offerTicketTrial)
                                <div v-show="ticketTrialMessage" style="display: none" role="status"
                                    class="mb-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg p-3 text-sm text-green-800 dark:text-green-200">
                                    @{{ ticketTrialMessage }}
                                </div>
                                @endif
                                @endif

                                {{-- The embed, separately, because it is the one thing a grandfather
                                     stamp does NOT restore. hasProTicketingPlan() deliberately
                                     ignores tickets_grandfathered_at (see its docblock): the stamp
                                     buys back paid selling, not the Pro extras. So this exact set -
                                     a stamped event on a schedule that is not Pro - keeps selling on
                                     its own page while any <iframe ?tickets=true> the owner put on
                                     another website goes dark.

                                     Worth saying out loud even though the widget has always been
                                     advertised as Pro (/features/embed-tickets said so before this
                                     shipped): the code did not enforce it, so an owner can have a
                                     working embed today with no reason to think it was unentitled,
                                     and the first signal would otherwise be a customer complaint.
                                     We cannot detect a live iframe, so this shows to everyone it
                                     could apply to. --}}
                                @if ($event->exists
                                    && $event->tickets_grandfathered_at !== null
                                    && ! $event->hasProTicketingPlan())
                                <x-plan-gate
                                    variant="banner"
                                    tier="pro"
                                    class="mb-4"
                                    :role="$role"
                                    :subdomain="$subdomain"
                                    :learnMoreUrl="marketing_url('/features/embed-tickets')"
                                    :title="__('messages.tickets_embed_needs_pro_title')"
                                    v-show="event.tickets_enabled">
                                    {{ __('messages.tickets_embed_needs_pro_body') }}
                                </x-plan-gate>
                                @endif

                                {{-- This used to be a blanket "disabled state wrapper" that greyed out and
                                     froze the entire ticket area for a non-Pro schedule. The free plan can
                                     sell now, so the whole panel has to be usable; the individual Pro-only
                                     options carry their own locks instead. Kept as a plain div so the
                                     surrounding structure and indentation are unchanged. --}}
                                <div>

                                <div class="mt-2 mb-2" v-show="event.rsvp_enabled">
                                    <x-input-label for="rsvp_limit" :value="__('messages.rsvp_limit')" />
                                    <x-text-input id="rsvp_limit" name="rsvp_limit" type="number" min="1" class="mt-1 block w-full event-tile-narrow" placeholder="{{ __('messages.unlimited') }}"
                                        v-model="event.rsvp_limit" v-bind:disabled="ticketChoice !== 'rsvp'" />
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.rsvp_limit_help') }}</p>
                                </div>

                                <!-- Tickets Tab -->
                                <div v-show="event.tickets_enabled" data-ticket-pane="tickets">
                                @php
                                    // Deliberately distinctive names: these blocks share the VIEW
                                    // scope, so a plain $plans here would be visible to every
                                    // block below it.
                                    // isVenue() as well as the plan gate: only a venue owns a
                                    // plan, so on any other schedule type this is a query that
                                    // can only ever come back empty.
                                    $seatingPlanList = $role->isVenue() && $role->seatingEnabled()
                                        ? \App\Models\SeatingPlan::with('sections')
                                            ->where('role_id', $role->id)->where('is_deleted', false)
                                            ->orderBy('name')->get()
                                        : collect();
                                    // bandCounts: how many seats each band actually holds. The band
                                    // select was names alone, so the organizer priced blind - and a
                                    // band no ticket claims is invisible until the box office
                                    // refuses a sale from it. thumb: which room this is, since four
                                    // plans in a dropdown are four indistinguishable strings.
                                    // One grouped count per plan rather than a query PAIR per band.
                                    // sections is already eager-loaded and the relation excludes
                                    // deleted ones, so the section ids and the standing capacities
                                    // both come from memory.
                                    $seatingPlanOptions = $seatingPlanList->map(function ($p) {
                                        $byBand = $p->sections->filter(fn ($s) => filled($s->band))->groupBy('band');

                                        $seatsPerSection = $p->seats()
                                            ->whereIn('seating_section_id', $p->sections->pluck('id'))
                                            ->toBase()
                                            ->selectRaw('seating_section_id, count(*) as aggregate')
                                            ->groupBy('seating_section_id')
                                            ->pluck('aggregate', 'seating_section_id');

                                        $countFor = function ($sections) use ($seatsPerSection) {
                                            $seats = $sections->sum(fn ($s) => (int) ($seatsPerSection[$s->id] ?? 0));

                                            // Standing sections hold a capacity, not seats, and the
                                            // band select shows whichever the room actually uses.
                                            return $seats ?: (int) $sections->where('kind', 'standing')->sum('capacity');
                                        };

                                        return [
                                            'id' => $p->id,
                                            'name' => $p->name,
                                            'bands' => $byBand->keys()->values()->all(),
                                            'bandCounts' => $byBand->map($countFor)->all(),
                                            'seats' => (int) $seatsPerSection->sum(),
                                            'thumb' => $p->thumbnail(200),
                                        ];
                                    })->values();
                                @endphp

                                {{-- A schedule with no plan yet used to see nothing at all here, so
                                     allocated seating was invisible unless you already knew to go
                                     looking for the tab. Say it exists, and link to where it is
                                     built. --}}
                                @if ($role->isVenue() && $role->seatingEnabled() && $seatingPlanOptions->isEmpty())
                                <div class="mb-6 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                                    <x-input-label :value="__('messages.seating_plan')" />
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.seating_no_plans_yet') }}</p>
                                    <a href="{{ route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'seating']) }}"
                                       class="mt-2 inline-block text-sm font-medium text-[var(--brand-blue)] hover:underline">
                                        {{ __('messages.seating_new_plan') }}
                                    </a>
                                </div>
                                @endif

                                @if ($seatingPlanOptions->isNotEmpty())
                                <div class="mb-6">
                                    <x-input-label for="seating_plan_id" :value="__('messages.seating_plan')" />
                                    <select id="seating_plan_id" name="seating_plan_id" v-model="event.seating_plan_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] shadow-sm">
                                        <option value="">{{ __('messages.seating_no_plan') }}</option>
                                        @foreach ($seatingPlanOptions as $option)
                                            <option v-pre value="{{ $option['id'] }}">{{ $option['name'] }}</option>
                                        @endforeach
                                    </select>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.seating_plan_help') }}</p>

                                    {{-- The room, once one is chosen. Decorative - the seat count
                                         beside it says the same thing in words. --}}
                                    <div v-if="selectedSeatingPlan && selectedSeatingPlan.thumb" class="mt-3 flex items-center gap-4">
                                        <svg :viewBox="selectedSeatingPlan.thumb.viewBox" class="h-20 w-40 shrink-0"
                                             aria-hidden="true" focusable="false" preserveAspectRatio="xMidYMid meet">
                                            <circle v-for="(dot, i) in selectedSeatingPlan.thumb.dots" :key="i"
                                                :cx="dot.x" :cy="dot.y" r="6" :fill="dot.c" opacity="0.75" />
                                        </svg>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            @{{ selectedSeatingPlan.seats }} {{ __('messages.seating_seats') }}
                                        </p>
                                    </div>

                                    {{-- A section whose band no ticket prices is unsellable, and the
                                         only place that ever surfaced was a box-office refusal at
                                         the counter. Say it while the prices are being set. --}}
                                    <div v-if="unmappedSeatingBands.length"
                                         class="mt-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3 flex items-start gap-2">
                                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0" fill="none" viewBox="0 0 24 24"
                                             stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                                        </svg>
                                        <p class="text-sm text-amber-800 dark:text-amber-200">
                                            @{{ unmappedBandWarning }}
                                        </p>
                                    </div>
                                    @if ($event->exists && $event->hasAllocatedSeating())
                                        <div v-if="event.seating_plan_id" class="mt-3 flex flex-wrap gap-3">
                                            <x-secondary-link :href="route('box_office.show', ['subdomain' => $subdomain, 'hash' => \App\Utils\UrlUtils::encodeId($event->id)])">
                                                {{ __('messages.seating_box_office') }}
                                            </x-secondary-link>
                                            <x-secondary-link :href="route('seating.occurrence_design', ['subdomain' => $subdomain, 'hash' => \App\Utils\UrlUtils::encodeId($event->id)])">
                                                {{ __('messages.seating_modify_this_date') }}
                                            </x-secondary-link>
                                        </div>
                                    @endif
                                </div>
                                @endif

                                <div class="mb-6">
                                    {{-- First run used to be two unlabelled number boxes and nothing else:
                                         the controller always seeds one blank Ticket row
                                         (EventController::create/edit), so there is no zero-ticket state to
                                         write an empty state for. This says what the row is instead. --}}
                                    @if (($interestCount ?? 0) > 0)
                                        {{-- Real demand, stated where the decision is made. Rendered
                                             server-side and never from a Vue expression, and it is a
                                             plain integer, so there is no user-controlled text inside
                                             this mount to guard with v-pre. --}}
                                        <div class="mb-4 rounded-lg border border-blue-200 bg-blue-50 p-3 dark:border-blue-700 dark:bg-blue-900/20">
                                            <p class="text-sm font-medium text-blue-800 dark:text-blue-300">
                                                {{ trans_choice('messages.event_interest_waiting_count', $interestCount, ['count' => $interestCount]) }}
                                            </p>
                                            {{-- The count keeps reporting people who asked before the card
                                                 was switched off, so say that nobody new can join, and
                                                 where the switch is. It is the CREATOR's switch
                                                 (Event::offersInterestCapture()), which may not be the
                                                 schedule this editor was opened from; link only when
                                                 this user can edit that one. The text is a translation
                                                 and the subdomain a slug, so nothing user-controlled
                                                 enters this Vue mount. --}}
                                            @if (! $event->creatorRole?->show_event_interest)
                                                <p class="mt-1 text-sm text-blue-700 dark:text-blue-300">
                                                    {{ __('messages.event_interest_signups_off') }}
                                                    @if ($event->creatorRole && auth()->user()->isEditor($event->creatorRole->subdomain))
                                                        <x-link href="{{ route('role.edit', ['subdomain' => $event->creatorRole->subdomain]) }}#section-settings">{{ __('messages.settings') }}</x-link>
                                                    @endif
                                                </p>
                                            @endif
                                        </div>
                                    @endif
                                    <div v-if="tickets.length === 1 && !tickets[0].id" class="mt-4">
                                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('messages.your_first_ticket_type') }}</h4>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.your_first_ticket_type_help') }}</p>
                                    </div>
                                    <div v-for="(ticket, index) in tickets" :key="ticket.uid"
                                        :class="{'mt-4 p-4 border border-gray-300 dark:border-gray-700 rounded-lg': tickets.length > 1, 'mt-4': tickets.length === 1}">
                                        <input type="hidden" v-bind:name="`tickets[${index}][id]`" v-model="ticket.id">
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 event-ticket-grid">
                                            <div>
                                                <x-input-label>{{ __('messages.price') }} <span class="event-tab-aside font-normal" v-cloak v-text="event.ticket_currency_code"></span></x-input-label>
                                                <x-text-input type="number" step="0.01" v-bind:name="`tickets[${index}][price]`" 
                                                    v-model="ticket.price" class="mt-1 block w-full" placeholder="{{ __('messages.free') }}" />
                                            </div>
                                            <div>
                                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{ __('messages.quantity') }} @{{ (! isRecurring && ticket.sold && Object.values(JSON.parse(ticket.sold))[0] > 0) ? (' - ' + Object.values(JSON.parse(ticket.sold))[0] + ' ' +soldLabel) : '' }}</label>
                                                <x-text-input type="number" v-bind:name="`tickets[${index}][quantity]`"
                                                    v-model="ticket.quantity" class="mt-1 block w-full" placeholder="{{ __('messages.unlimited') }}"
                                                    v-bind:readonly="!!ticket.seating_band" v-bind:class="{ 'opacity-60': !!ticket.seating_band }" />
                                                <p v-if="ticket.seating_band" class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('messages.seating_quantity_from_plan') }}</p>
                                            </div>
                                            <div v-if="seatingBands.length && !ticket.is_pass">
                                                <x-input-label :value="__('messages.seating_band')" />
                                                <select v-bind:name="`tickets[${index}][seating_band]`" v-model="ticket.seating_band"
                                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] shadow-sm">
                                                    <option value="">{{ __('messages.seating_band_none') }}</option>
                                                    <option v-for="band in seatingBands" :key="band" :value="band">@{{ bandOptionLabel(band) }}</option>
                                                </select>
                                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('messages.seating_band_ticket_help') }}</p>
                                            </div>
                                            {{-- Always shown. It used to appear only once a SECOND row
                                                 existed, so the field that names the thing a buyer is
                                                 choosing was invisible on the first ticket anyone made.
                                                 Still only REQUIRED with more than one row, where an
                                                 unnamed ticket is genuinely ambiguous. --}}
                                            <div>
                                                {{-- Plain label, not <x-input-label :value>: that binding is a
                                                     BLADE expression, so a Vue one in it is parsed as PHP and
                                                     `tickets` raises "Undefined constant". Same shape the
                                                     Quantity label above already uses. --}}
                                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{ __('messages.type') }}@{{ tickets.length > 1 ? ' *' : '' }}</label>
                                                <x-text-input type="text" v-bind:name="`tickets[${index}][type]`" v-model="ticket.type"
                                                    class="mt-1 block w-full" placeholder="{{ __('messages.ticket_type_placeholder') }}"
                                                    v-bind:required="event.tickets_enabled && tickets.length > 1" v-bind:class="{ 'border-red-500': formSubmitAttempted && tickets.length > 1 && !ticket.type }" />
                                                <p v-if="formSubmitAttempted && tickets.length > 1 && !ticket.type" class="mt-1 text-xs text-red-600">{{ __('messages.ticket_type_required') }}</p>
                                            </div>
                                            <div v-if="tickets.length > 1" class="flex items-end gap-3 flex-wrap">
                                                <button type="button" @click="addTicketCustomField(index)" class="mt-1 text-sm text-[var(--brand-blue)] hover:text-[var(--brand-blue-dark)]" v-if="getTicketCustomFieldCount(index) < 10">
                                                    + {{ __('messages.add_field') }}
                                                </button>
                                                <button type="button" @click="addTicketVolumeDiscount(index)" class="mt-1 text-sm text-[var(--brand-blue)] hover:text-[var(--brand-blue-dark)]" v-if="!ticket.volume_discount">
                                                    + {{ __('messages.add_volume_discount') }}
                                                </button>
                                                <button type="button" @click="addTicketMaxPerOrder(index)" class="mt-1 text-sm text-[var(--brand-blue)] hover:text-[var(--brand-blue-dark)]" v-if="ticket.max_per_order === null || ticket.max_per_order === undefined">
                                                    + {{ __('messages.add_limit') }}
                                                </button>
                                                <button type="button" @click="openTicketDescription(ticket)" class="mt-1 text-sm text-[var(--brand-blue)] hover:text-[var(--brand-blue-dark)]" v-if="! ticket.description && ! ticketDescOpen[ticket.uid]">
                                                    + {{ __('messages.add_description') }}
                                                </button>
                                                <button type="button" @click="removeTicket(index)" class="mt-1 text-red-600 hover:text-red-800 dark:text-red-400 text-sm">
                                                    {{ __('messages.remove') }}
                                                </button>
                                            </div>
                                        </div>
                                        <div v-if="tickets.length === 1" class="mt-2 flex flex-wrap items-center gap-3">
                                            <button type="button" @click="addTicketCustomField(index)" class="text-sm text-[var(--brand-blue)] hover:text-[var(--brand-blue-dark)]" v-if="getTicketCustomFieldCount(index) < 10">
                                                + {{ __('messages.add_field') }}
                                            </button>
                                            <button type="button" @click="addTicketVolumeDiscount(index)" class="text-sm text-[var(--brand-blue)] hover:text-[var(--brand-blue-dark)]" v-if="!ticket.volume_discount">
                                                + {{ __('messages.add_volume_discount') }}
                                            </button>
                                            <button type="button" @click="addTicketMaxPerOrder(index)" class="text-sm text-[var(--brand-blue)] hover:text-[var(--brand-blue-dark)]" v-if="ticket.max_per_order === null || ticket.max_per_order === undefined">
                                                + {{ __('messages.add_limit') }}
                                            </button>
                                            <button type="button" @click="openTicketDescription(ticket)" class="text-sm text-[var(--brand-blue)] hover:text-[var(--brand-blue-dark)]" v-if="! ticket.description && ! ticketDescOpen[ticket.uid]">
                                                + {{ __('messages.add_description') }}
                                            </button>
                                        </div>
                                        <div v-if="ticket.volume_discount" class="mt-4">
                                            <x-input-label :value="__('messages.volume_discount')" />
                                            <div class="mt-2 p-3 border border-gray-200 dark:border-gray-600 rounded-lg">
                                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                                <div>
                                                    <x-input-label :value="__('messages.volume_discount_min_quantity')" class="text-xs" />
                                                    <x-text-input type="number" min="2" step="1" v-model.number="ticket.volume_discount.min_quantity" class="mt-1 block w-full text-sm" />
                                                </div>
                                                <div>
                                                    <x-input-label :value="__('messages.type')" class="text-xs" />
                                                    <select v-model="ticket.volume_discount.type" class="mt-1 block w-full text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                                                        <option value="percentage">{{ __('messages.percentage') }}</option>
                                                        <option value="fixed">{{ __('messages.fixed_amount') }}</option>
                                                    </select>
                                                </div>
                                                <div>
                                                    <x-input-label :value="__('messages.volume_discount_value')" class="text-xs" />
                                                    <x-text-input type="number" step="0.01" min="0.01" v-bind:max="ticket.volume_discount.type === 'percentage' ? 100 : undefined" v-model.number="ticket.volume_discount.value" class="mt-1 block w-full text-sm" />
                                                </div>
                                                </div>
                                                <div class="mt-2 flex justify-end">
                                                    <button type="button" @click="removeTicketVolumeDiscount(index)" class="text-red-600 hover:text-red-800 dark:text-red-400 text-sm">
                                                        {{ __('messages.remove') }}
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-if="ticket.max_per_order !== null && ticket.max_per_order !== undefined" class="mt-4">
                                            <x-input-label :value="__('messages.max_per_order')" />
                                            <div class="mt-2 p-3 border border-gray-200 dark:border-gray-600 rounded-lg">
                                                <x-text-input type="number" min="1" step="1" v-model.number="ticket.max_per_order" class="block w-full sm:w-48" />
                                                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ __('messages.max_per_order_help') }}</p>
                                                <div class="mt-2 flex justify-end">
                                                    <button type="button" @click="removeTicketMaxPerOrder(index)" class="text-red-600 hover:text-red-800 dark:text-red-400 text-sm">
                                                        {{ __('messages.remove') }}
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Pass / subscription configuration -->
                                        {{-- Pro, and locked rather than hidden for the same reason as
                                             the individual-tickets toggle above: turning it on below
                                             Pro only to have EventRepo::saveEvent() reset it to the
                                             stored value reads as the form losing the setting. An
                                             already-sold pass keeps its switch usable so a lapsed Pro
                                             schedule can still turn one OFF. --}}
                                        <div class="mt-4" :class="(isPro || ticket.is_pass) ? '' : 'opacity-60'">
                                            <label class="flex items-start gap-3" :class="(isPro || ticket.is_pass) ? 'cursor-pointer' : 'cursor-not-allowed'">
                                                <button type="button" role="switch" :aria-checked="ticket.is_pass ? 'true' : 'false'" @click="toggleTicketPass(index)"
                                                    :disabled="!isPro && !ticket.is_pass"
                                                    :class="['relative inline-flex h-6 w-11 flex-shrink-0 rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800', ticket.is_pass ? 'bg-[var(--brand-button-bg)]' : 'bg-gray-200 dark:bg-gray-700']">
                                                    <span :class="['inline-block h-5 w-5 transform rounded-full bg-white shadow transition-transform mt-0.5', ticket.is_pass ? 'translate-x-5' : 'translate-x-0.5']"></span>
                                                </button>
                                                <span>
                                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('messages.subscription_toggle_label') }}</span>
                                                    <template v-if="!isPro"> <x-lock-badge tier="pro" /></template>
                                                    <span class="block text-xs text-gray-500 dark:text-gray-400">{{ __('messages.subscription_toggle_help') }}</span>
                                                </span>
                                            </label>
                                            <p class="text-xs mt-1 ms-14" v-if="!isPro && !ticket.is_pass">
                                                <button type="button" data-modal-open="upgrade-tickets" class="font-medium text-[var(--brand-blue)] hover:underline">{{ __('messages.ticket_see_pro') }}</button>
                                            </p>

                                            <div v-if="ticket.is_pass" class="mt-3 ms-14 space-y-4">
                                                <!-- Subscription type -->
                                                <div>
                                                    <x-input-label :value="__('messages.subscription_type')" />
                                                    <select v-model="ticket.pass_usage_type" @change="normalizePassScope(ticket)" class="mt-1 block w-full sm:w-72 text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                                                        <option value="total">{{ __('messages.pass_type_visit_pass') }}</option>
                                                        <option value="unlimited">{{ __('messages.pass_type_membership') }}</option>
                                                        <option value="per_event">{{ __('messages.pass_type_festival') }}</option>
                                                        <option v-if="isRecurring" value="per_occurrence">{{ __('messages.pass_type_season') }}</option>
                                                    </select>
                                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">@{{ passTypeHelp(ticket.pass_usage_type) }}</p>
                                                </div>

                                                <!-- Admissions per event (holder + guests) -->
                                                <div>
                                                    <x-input-label :value="__('messages.pass_admits_per_event')" />
                                                    <x-text-input type="number" min="1" step="1" placeholder="1" v-model.number="ticket.pass_admits_per_event" class="mt-1 block w-full sm:w-48" />
                                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('messages.pass_admits_per_event_help') }}</p>
                                                    <p v-if="ticket.pass_admits_per_event > 1" class="mt-1 text-xs text-[var(--brand-blue)]">@{{ ticket.pass_admits_per_event }} {{ __('messages.pass_admits_people_including_holder') }}</p>
                                                </div>

                                                <!-- Visit cap (total) -->
                                                <div v-if="ticket.pass_usage_type === 'total'">
                                                    <x-input-label :value="__('messages.pass_max_uses')" />
                                                    <x-text-input type="number" min="1" step="1" v-model.number="ticket.pass_max_uses" class="mt-1 block w-full sm:w-48" />
                                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('messages.pass_max_uses_help') }}</p>
                                                </div>

                                                <!-- Validity window (not for season pass, which is bound by recurrence) -->
                                                <div v-if="ticket.pass_usage_type !== 'per_occurrence'">
                                                    <x-input-label :value="__('messages.pass_valid_days')" />
                                                    <x-text-input type="number" min="1" step="1" v-model.number="ticket.pass_valid_days" class="mt-1 block w-full sm:w-48" />
                                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('messages.pass_valid_days_help') }}</p>
                                                </div>

                                                <!-- Coverage (cross-event subscriptions) -->
                                                <div v-if="ticket.pass_usage_type !== 'per_occurrence'">
                                                    <x-input-label :value="__('messages.pass_coverage')" />
                                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{ __('messages.pass_coverage_help') }}</p>
                                                    <select v-model="ticket.pass_scope" class="block w-full sm:w-72 text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                                                        <option value="all_events">{{ __('messages.pass_scope_all_events') }}</option>
                                                        <option value="sub_schedule">{{ __('messages.pass_scope_sub_schedule') }}</option>
                                                        <option value="specific_events">{{ __('messages.pass_scope_specific_events') }}</option>
                                                    </select>
                                                    <p v-if="ticket.pass_scope === 'all_events'" class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('messages.pass_scope_all_events_help') }}</p>

                                                    <div v-if="ticket.pass_scope === 'sub_schedule'" class="mt-2">
                                                        <select v-model="ticket.pass_scope_group_id" class="block w-full sm:w-72 text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                                                            <option value="">{{ __('messages.select_sub_schedule') }}</option>
                                                            <option v-for="g in passGroups" :key="g.id" :value="g.id">@{{ g.name }}</option>
                                                        </select>
                                                        <p v-if="passGroups.length === 0" class="mt-1 text-xs text-amber-600 dark:text-amber-400">{{ __('messages.pass_no_sub_schedules') }}</p>
                                                    </div>

                                                    <div v-if="ticket.pass_scope === 'specific_events'" class="mt-2">
                                                        <input type="text" v-model="passEventSearch[index]" placeholder="{{ __('messages.search_events') }}" class="block w-full text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                                                        <div class="mt-2 max-h-48 overflow-y-auto border border-gray-200 dark:border-gray-700 rounded-lg p-2 space-y-1">
                                                            <label v-for="e in getFilteredPassEvents(index)" :key="e.id" class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                                                                <input type="checkbox" :value="e.id" v-model="ticket.pass_event_ids" class="h-4 w-4 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)] border-gray-300 rounded">
                                                                <span>@{{ e.name }}</span>
                                                            </label>
                                                            <p v-if="getFilteredPassEvents(index).length === 0" class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.no_events_found') }}</p>
                                                        </div>
                                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">@{{ ticket.pass_event_ids.length }} {{ __('messages.events_covered') }}</p>
                                                        <p v-if="ticket.pass_event_ids.length === 0" class="mt-1 text-xs text-amber-600 dark:text-amber-400">{{ __('messages.pass_no_events_warning') }}</p>
                                                    </div>
                                                </div>

                                                <!-- Advance booking -->
                                                <div>
                                                    <label class="flex items-center gap-3 cursor-pointer">
                                                        <button type="button" role="switch" :aria-checked="ticket.pass_allow_booking ? 'true' : 'false'" @click="ticket.pass_allow_booking = !ticket.pass_allow_booking"
                                                            :class="['relative inline-flex h-6 w-11 flex-shrink-0 rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800', ticket.pass_allow_booking ? 'bg-[var(--brand-button-bg)]' : 'bg-gray-200 dark:bg-gray-700']">
                                                            <span :class="['inline-block h-5 w-5 transform rounded-full bg-white shadow transition-transform mt-0.5', ticket.pass_allow_booking ? 'translate-x-5' : 'translate-x-0.5']"></span>
                                                        </button>
                                                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('messages.pass_allow_booking_label') }}</span>
                                                    </label>
                                                    <p class="mt-1 ms-14 text-xs text-gray-500 dark:text-gray-400">{{ __('messages.pass_allow_booking_help') }}</p>

                                                    <div v-if="ticket.pass_allow_booking" class="mt-3 ms-14 space-y-3">
                                                        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3">
                                                            <div class="flex items-start gap-2">
                                                                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                                                                </svg>
                                                                <p class="text-xs text-amber-800 dark:text-amber-200">{{ __('messages.pass_shared_capacity_warning') }}</p>
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <x-input-label :value="__('messages.pass_seats_per_occurrence')" />
                                                            <x-text-input type="number" min="1" step="1" v-model.number="ticket.pass_seats_per_occurrence" class="mt-1 block w-full sm:w-48" />
                                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('messages.pass_seats_per_occurrence_help') }}</p>
                                                        </div>
                                                        <div>
                                                            <x-input-label :value="__('messages.pass_cancel_cutoff_label')" />
                                                            <select v-model="ticket.pass_cancel_cutoff_hours" class="mt-1 block w-full sm:w-72 text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                                                                <option value="">{{ __('messages.pass_cancel_anytime') }}</option>
                                                                <option :value="0">{{ __('messages.pass_cancel_until_start') }}</option>
                                                                @foreach ([12, 24, 48, 72, 168] as $cutoffHours)
                                                                <option :value="{{ $cutoffHours }}">{{ __('messages.pass_cancel_hours_before', ['hours' => $cutoffHours]) }}</option>
                                                                @endforeach
                                                                <!-- A stored value outside the presets (e.g. restored from a backup)
                                                                     must stay visible and selected, not render a blank select. -->
                                                                <option v-if="![0, 12, 24, 48, 72, 168].includes(ticket.pass_cancel_cutoff_hours) && ticket.pass_cancel_cutoff_hours !== '' && ticket.pass_cancel_cutoff_hours !== null"
                                                                    :value="ticket.pass_cancel_cutoff_hours">@{{ passCancelHoursLabel(ticket.pass_cancel_cutoff_hours) }}</option>
                                                            </select>
                                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('messages.pass_cancel_cutoff_help') }}</p>
                                                        </div>
                                                        <div v-if="ticket.pass_cancel_cutoff_hours !== '' && ticket.pass_cancel_cutoff_hours !== null">
                                                            <x-input-label :value="__('messages.pass_late_cancel_policy_label')" />
                                                            <select v-model="ticket.pass_late_cancel_policy" class="mt-1 block w-full sm:w-72 text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                                                                <option value="forfeit">{{ __('messages.pass_late_cancel_forfeit') }}</option>
                                                                <option value="block">{{ __('messages.pass_late_cancel_block') }}</option>
                                                            </select>
                                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('messages.pass_late_cancel_policy_help') }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <template v-if="ticket.is_pass">
                                                <input type="hidden" v-bind:name="`tickets[${index}][is_pass]`" :value="1">
                                                <input type="hidden" v-bind:name="`tickets[${index}][pass_usage_type]`" :value="ticket.pass_usage_type">
                                                <input type="hidden" v-bind:name="`tickets[${index}][pass_max_uses]`" :value="ticket.pass_max_uses || ''">
                                                <input type="hidden" v-bind:name="`tickets[${index}][pass_valid_days]`" :value="ticket.pass_valid_days || ''">
                                                <input type="hidden" v-bind:name="`tickets[${index}][pass_scope]`" :value="ticket.pass_scope">
                                                <input type="hidden" v-bind:name="`tickets[${index}][pass_scope_group_id]`" :value="ticket.pass_scope_group_id || ''">
                                                <input type="hidden" v-bind:name="`tickets[${index}][pass_event_ids]`" :value="JSON.stringify(ticket.pass_event_ids || [])">
                                                <input type="hidden" v-bind:name="`tickets[${index}][pass_allow_booking]`" :value="ticket.pass_allow_booking ? 1 : 0">
                                                <input type="hidden" v-bind:name="`tickets[${index}][pass_seats_per_occurrence]`" :value="ticket.pass_seats_per_occurrence || ''">
                                                <input type="hidden" v-bind:name="`tickets[${index}][pass_cancel_cutoff_hours]`" :value="ticket.pass_cancel_cutoff_hours === '' || ticket.pass_cancel_cutoff_hours === null ? '' : ticket.pass_cancel_cutoff_hours">
                                                <input type="hidden" v-bind:name="`tickets[${index}][pass_late_cancel_policy]`" :value="ticket.pass_late_cancel_policy || 'forfeit'">
                                                <input type="hidden" v-bind:name="`tickets[${index}][pass_admits_per_event]`" :value="ticket.pass_admits_per_event || ''">
                                            </template>
                                            <input v-else type="hidden" v-bind:name="`tickets[${index}][is_pass]`" :value="0">
                                        </div>
                                        <!-- Ticket-level Custom Fields -->
                                        <div class="mt-4" v-if="ticket.custom_fields && Object.keys(ticket.custom_fields).length > 0">
                                            <x-input-label :value="__('messages.custom_fields') . ' (' . __('messages.per_ticket') . ')'" />
                                            <div :id="`ticket-${index}-custom-fields`">
                                            <div v-for="(field, fieldKey) in ticket.custom_fields" :key="fieldKey" :data-ticket-field-key="fieldKey" class="mt-2 p-3 border border-gray-200 dark:border-gray-600 rounded-lg flex items-start gap-2">
                                                <div v-show="Object.keys(ticket.custom_fields).length > 1" class="custom-field-drag-handle cursor-grab text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 flex-shrink-0 mt-1">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M7 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM7 8a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 8a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM7 14a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 14a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/>
                                                    </svg>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                                    <div>
                                                        <x-input-label :value="__('messages.field_name') . ' *'" class="text-xs" />
                                                        <x-text-input type="text" v-model="field.name" class="mt-1 block w-full text-sm" v-bind:required="event.tickets_enabled || event.rsvp_enabled" v-bind:class="{ 'border-red-500': formSubmitAttempted && !field.name }" />
                                                        <p v-if="formSubmitAttempted && !field.name" class="mt-1 text-xs text-red-600">{{ __('messages.field_name_required') }}</p>
                                                    </div>
                                                    <div>
                                                        <x-input-label :value="__('messages.field_type')" class="text-xs" />
                                                        <select v-model="field.type" class="mt-1 block w-full text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                                                            <option value="string">{{ __('messages.type_string') }}</option>
                                                            <option value="multiline_string">{{ __('messages.type_multiline_string') }}</option>
                                                            <option value="switch">{{ __('messages.type_switch') }}</option>
                                                            <option value="date">{{ __('messages.type_date') }}</option>
                                                            <option value="dropdown">{{ __('messages.type_dropdown') }}</option>
                                                            <option value="multiselect">{{ __('messages.type_multiselect') }}</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                @if($role->language_code !== 'en')
                                                <div class="mt-2">
                                                    <x-input-label :value="__('messages.english_name')" class="text-xs" />
                                                    <x-text-input type="text" v-model="field.name_en" class="mt-1 block w-full text-sm" placeholder="{{ __('messages.auto_translated_placeholder') }}" />
                                                </div>
                                                @endif
                                                <div class="mt-2" v-if="field.type === 'dropdown' || field.type === 'multiselect'">
                                                    <x-input-label :value="__('messages.field_options')" class="text-xs" />
                                                    <x-text-input type="text" v-model="field.options" class="mt-1 block w-full text-sm" placeholder="{{ __('messages.options_placeholder') }}" />
                                                </div>
                                                <div class="mt-2 flex items-center justify-between">
                                                    <div class="flex items-center">
                                                        <input type="checkbox" v-model="field.required" :id="`ticket_${index}_field_required_${fieldKey}`" class="h-4 w-4 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)] border-gray-300 rounded">
                                                        <label :for="`ticket_${index}_field_required_${fieldKey}`" class="ms-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">{{ __('messages.field_required') }}</label>
                                                    </div>
                                                    <button type="button" @click="removeTicketCustomField(index, fieldKey)" class="text-red-600 hover:text-red-800 dark:text-red-400 text-sm">
                                                        {{ __('messages.remove') }}
                                                    </button>
                                                </div>
                                                </div>
                                            </div>
                                            </div>
                                        </div>
                                        <input type="hidden" v-bind:name="`tickets[${index}][custom_fields]`" :value="JSON.stringify(ticket.custom_fields || {})">
                                        <input type="hidden" v-bind:name="`tickets[${index}][volume_discount]`" :value="ticket.volume_discount ? JSON.stringify(ticket.volume_discount) : ''">
                                        <input type="hidden" v-bind:name="`tickets[${index}][max_per_order]`" :value="(ticket.max_per_order === null || ticket.max_per_order === undefined || ticket.max_per_order === '') ? '' : ticket.max_per_order">

                                        {{-- v-show, never v-if: the editor is set up once per row. Empty and unopened it
                                             stays folded, so a ticket type is a line of fields and not a page. --}}
                                        <div class="mt-4" v-show="ticket.description || ticketDescOpen[ticket.uid]">
                                            <x-input-label :value="__('messages.description')" />
                                            <textarea v-bind:name="`tickets[${index}][description]`" v-model="ticket.description" rows="4" data-content-dir="{{ content_dir($role) }}"
                                                class="html-editor mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm"></textarea>
                                        </div>

                                        <template v-if="showSalesDates">
                                            <div class="mt-4">
                                                <x-input-label :value="__('messages.ticket_sales_start_at')" />
                                                <div class="flex items-center gap-2 mt-1">
                                                    <input type="text"
                                                        class="datepicker-ticket-sales-start flex-1 min-w-[110px] border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm text-sm"
                                                        :data-ticket-index="index"
                                                        :value="ticket.sales_start_at_date"
                                                        autocomplete="off" />
                                                    <div class="relative w-28">
                                                        <input type="text"
                                                            class="ticket-sales-start-time-input w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm text-sm"
                                                            :value="formatPartTime(ticket.sales_start_at_time)"
                                                            @focus="initTicketSalesStartTimePickerOnFocus($event, index)"
                                                            @change="onTicketSalesStartTimeChange(index, $event)"
                                                            autocomplete="off" placeholder="{{ __('messages.time') }}" />
                                                        <div class="time-dropdown" :ref="'ticket_sales_start_time_dropdown_' + index"></div>
                                                    </div>
                                                </div>
                                                <input type="hidden" v-bind:name="`tickets[${index}][sales_start_at]`" :value="ticket.sales_start_at_date && ticket.sales_start_at_time ? ticket.sales_start_at_date + ' ' + ticket.sales_start_at_time + ':00' : (ticket.sales_start_at_date ? ticket.sales_start_at_date + ' 00:00:00' : '')" />
                                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                                    {{ __('messages.ticket_sales_start_at_help') }}
                                                </p>
                                            </div>

                                            <div class="mt-4">
                                                <x-input-label :value="__('messages.ticket_sales_end_at')" />
                                                <div class="flex items-center gap-2 mt-1">
                                                    <input type="text"
                                                        class="datepicker-ticket-sales-end flex-1 min-w-[110px] border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm text-sm"
                                                        :data-ticket-index="index"
                                                        :value="ticket.sales_end_at_date"
                                                        autocomplete="off" />
                                                    <div class="relative w-28">
                                                        <input type="text"
                                                            class="ticket-sales-end-time-input w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm text-sm"
                                                            :value="formatPartTime(ticket.sales_end_at_time)"
                                                            @focus="initTicketSalesEndTimePickerOnFocus($event, index)"
                                                            @change="onTicketSalesEndTimeChange(index, $event)"
                                                            autocomplete="off" placeholder="{{ __('messages.time') }}" />
                                                        <div class="time-dropdown" :ref="'ticket_sales_end_time_dropdown_' + index"></div>
                                                    </div>
                                                </div>
                                                <input type="hidden" v-bind:name="`tickets[${index}][sales_end_at]`" :value="ticket.sales_end_at_date && ticket.sales_end_at_time ? ticket.sales_end_at_date + ' ' + ticket.sales_end_at_time + ':00' : (ticket.sales_end_at_date ? ticket.sales_end_at_date + ' 23:59:00' : '')" />
                                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                                    {{ __('messages.ticket_sales_end_at_help') }}
                                                </p>
                                            </div>
                                        </template>
                                        <template v-else>
                                            <input type="hidden" :name="`tickets[${index}][sales_start_at]`" value="" />
                                            <input type="hidden" :name="`tickets[${index}][sales_end_at]`" value="" />
                                        </template>
                                    </div>

                                    <!-- Total Tickets Mode Selection -->
                                    <!-- Hidden for an allocated event: quantities come from the plan, so two
                                         equal sections would offer "combined" by accident and the server
                                         refuses it anyway (Event::hasSameTicketQuantities). -->
                                    <div v-if="hasSameTicketQuantities && tickets.length > 1 && !event.seating_plan_id" class="mt-6 p-4 border rounded-lg bg-gray-50 dark:bg-gray-800">
                                        <div class="space-y-3">
                                            <div class="flex items-center">
                                                <input id="total_tickets_individual" name="total_tickets_mode" type="radio"
                                                    value="individual" v-model="event.total_tickets_mode"
                                                    class="h-4 w-4 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)] border-gray-300">
                                                <label for="total_tickets_individual" class="ms-3 block text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    {{ __('messages.individual_quantities') }} (@{{ getTotalTicketQuantity }} total)
                                                    <p class="text-xs text-gray-600 dark:text-gray-400">
                                                        {{ __('messages.individual_quantities_help') }}
                                                    </p>
                                                </label>
                                            </div>
                                            <div class="flex items-center">
                                                <input id="total_tickets_combined" name="total_tickets_mode" type="radio"
                                                    value="combined" v-model="event.total_tickets_mode"
                                                    class="h-4 w-4 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)] border-gray-300">
                                                <label for="total_tickets_combined" class="ms-3 block text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    {{ __('messages.combined_total') }} (@{{ getCombinedTotalQuantity }} total)
                                                    <p class="text-xs text-gray-600 dark:text-gray-400">
                                                        {{ __('messages.combined_total_help') }}
                                                    </p>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex gap-2 mt-4">
                                        <button type="button" @click="addTicket" class="text-sm text-[var(--brand-blue)] hover:text-[var(--brand-blue-dark)]">
                                            + {{ __('messages.add_type') }}
                                        </button>
                                    </div>
                                </div>
                                </div>

                                {{-- What used to be four more tabs. Each is a row that says what it holds and opens in place,
                                     one at a time (activeTicketTab; "tickets" means none is open). --}}
                                <div class="event-subrows" v-show="event.tickets_enabled || event.rsvp_enabled">
                                <button type="button" class="event-subrow ticket-tab" data-tab="payment" v-cloak v-show="event.tickets_enabled" @click="toggleTicketRow('payment')"
                                    :aria-expanded="activeTicketTab === 'payment' ? 'true' : 'false'">
                                    <span class="event-row-title">{{ __('messages.payment') }}</span>
                                    <span class="event-row-summary" :class="{ 'is-empty': ticketRows.payment.empty, 'is-warn': ticketRows.payment.warn }"><bdi v-text="ticketRows.payment.text"></bdi></span>
                                    <svg class="event-row-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </button>
                                <!-- Payment Tab -->
                                <div v-show="activeTicketTab === 'payment'" data-ticket-pane="payment" class="event-subrow-body">

                                {{-- No payment method configured at all. The selector below is skipped
                                     entirely in that case, so the whole tab used to be a currency
                                     dropdown and a text-xs link, and the event would save, publish and
                                     take no money. That was survivable while selling was Pro-only; the
                                     free plan sends a much larger cohort down this exact path, so the
                                     setup step has to be first-class.

                                     Opens in a new tab deliberately: this form is unsaved. --}}
                                @if (! $connectedGateways)
                                <div class="mb-6 ap-card rounded-xl p-6" v-show="event.tickets_enabled">
                                    <div class="flex items-start gap-3">
                                        <div class="dashboard-icon p-2 rounded-xl bg-blue-50 dark:bg-blue-500/10">
                                            <svg class="w-5 h-5 text-[var(--brand-blue)]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                                            </svg>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ __('messages.connect_stripe_to_get_paid') }}</h3>
                                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('messages.connect_stripe_to_get_paid_body') }}</p>
                                            <div class="mt-4 flex flex-wrap items-center gap-3">
                                                <x-brand-link href="{{ route('profile.edit') }}#section-payment-methods" target="_blank" rel="noopener">
                                                    {{ __('messages.connect_stripe') }}
                                                </x-brand-link>
                                                <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.connect_stripe_reload_hint') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @elseif ($user->stripe_account_id && ! $user->stripe_completed_at)
                                {{-- Stripe onboarding is asynchronous, so this window is real and had no UI
                                     anywhere in the panel. --}}
                                <div class="mb-6 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3 flex items-start gap-2" v-show="event.tickets_enabled">
                                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                    <div class="text-sm text-amber-800 dark:text-amber-200">{{ __('messages.stripe_verifying') }}</div>
                                </div>
                                @elseif (! $onlineGateways)
                                {{-- Connected, but not to anything that can take money in this currency. The
                                     nudge above is Stripe-specific and would be actively wrong advice here:
                                     on the selfhost installs this case is commonest on, Stripe is precisely
                                     what is unavailable. Naming the currency instead points at the fix. --}}
                                <div class="mb-6 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3 flex items-start gap-2" v-show="event.tickets_enabled">
                                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
                                    <div class="text-sm text-amber-800 dark:text-amber-200">
                                        {{-- v-pre: the currency is the event's own text, and this is inside the mount. --}}
                                        <div class="font-semibold" v-pre>{{ __('messages.no_payment_method_for_currency', ['currency' => $event->ticket_currency_code]) }}</div>
                                        <p class="mt-1" v-pre>{{ __('messages.no_payment_method_for_currency_body', ['currency' => $event->ticket_currency_code]) }}</p>
                                    </div>
                                </div>
                                @endif

                                {{-- PayPal in test mode, which has nowhere else to announce itself.
                                     Payfast puts its warning on the interstitial it renders before
                                     redirecting; PayPal is a plain redirect, so the only signal an
                                     owner gets is the suffix in the dropdown below - and a forgotten
                                     toggle sells tickets that look entirely normal and take no money.
                                     Worth more here than for Payfast, because PayPal's sandbox is a
                                     separate API host AND a separate set of credentials, so it is
                                     easier to leave switched on by accident.

                                     v-show on the Vue model rather than a server-side branch on the
                                     stored method: the owner can pick PayPal without reloading. --}}
                                @if (! empty($paymentGateways->get('paypal')?->credentialsFor($user)['paypal_sandbox']))
                                <div class="mb-6 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3 flex items-start gap-2"
                                     v-show="event.tickets_enabled && event.payment_method === 'paypal'">
                                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
                                    <div class="text-sm text-amber-800 dark:text-amber-200">{{ __('messages.paypal_test_mode_warning') }}</div>
                                </div>
                                @endif

                                @if ($connectedGateways || $storedGateway)
                                <div class="mb-6">
                                    <x-input-label for="payment_method" :value="__('messages.payment_method')"/>
                                    <select id="payment_method" name="payment_method" v-model="event.payment_method" :required="event.tickets_enabled"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                                        {{-- Ordered by config('payments.gateways'), and filtered to what this owner has
                                             connected and what can settle this event's currency. A gateway that cannot
                                             take the currency is omitted rather than shown and then rejected.

                                             v-pre on every option: the label can carry owner-typed text (a Payfast
                                             merchant id, an API-sourced company name), and this select sits inside the
                                             Vue mount, where a server-rendered text node is compiled as a template.
                                             Same guard as the sub-schedule options further up this file. --}}
                                        @foreach ($selectableGateways as $gatewayKey => $gateway)
                                        <option v-pre value="{{ $gatewayKey }}">{{ $gateway->label($user) }}</option>
                                        @endforeach
                                        {{-- The SAVED method stays visible even when no longer offerable (currency
                                             changed after saving, gateway disconnected). Without this the select
                                             rendered blank, a blank select posts nothing, and the stale value silently
                                             survived every save - the state that let a USD event keep charging through
                                             Payfast. Showing it lets the owner see and fix it; checkout guards remain
                                             the authority either way. --}}
                                        @if ($storedGateway)
                                        <option v-pre value="{{ $event->payment_method }}">{{ $storedGateway->label($user) }} - {{ __('messages.payment_method_unavailable') }}</option>
                                        @endif
                                    </select>
                                    <div class="text-xs pt-1">
                                        <x-link href="{{ route('profile.edit') }}#section-payment-methods" target="_blank">
                                            {{ __('messages.manage_payment_methods') }}
                                        </x-link>
                                    </div>
                                </div>
                                @endif

                                <div class="mb-6">
                                    <x-input-label for="ticket_currency_code" :value="__('messages.currency')"/>
                                    <select id="ticket_currency_code" name="ticket_currency_code" v-model="event.ticket_currency_code" :required="event.tickets_enabled" data-searchable @disabled($currencyLocked ?? false)
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                                        @foreach ($currencies as $currency)
                                        @if ($loop->index == 2)
                                        <option disabled>──────────</option>
                                        @endif
                                        <option value="{{ $currency->value }}" {{ $event->ticket_currency_code == $currency->value ? 'selected' : '' }}>
                                            {{ $currency->value }} - {{ $currency->label }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @if (($currencyLocked ?? false) && ! $errors->has('ticket_currency_code'))
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.currency_locked_after_sales') }}</p>
                                    @endif
                                    <x-input-error class="mt-2" :messages="$errors->get('ticket_currency_code')" />
                                    @if (! $connectedGateways)
                                    <div class="text-xs pt-1">
                                        <x-link href="{{ route('profile.edit') }}#section-payment-methods" target="_blank">
                                            {{ __('messages.manage_payment_methods') }}
                                        </x-link>
                                    </div>
                                    @endif
                                </div>

                                <div class="mb-6" v-show="gatewayCapabilities[event.payment_method]?.payment_instructions">
                                    <x-input-label for="payment_instructions" :value="__('messages.payment_instructions')" />
                                    <textarea id="payment_instructions" name="payment_instructions" v-model="event.payment_instructions" rows="4" data-content-dir="{{ content_dir($role) }}"
                                        class="html-editor mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm"></textarea>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.payment_instructions_help') }}</p>
                                </div>

                                {{-- Installments. Event-level and here rather than per ticket row: the
                                     split is computed on the post-discount ORDER total, so a per-ticket
                                     flag would have states that silently do nothing (on for one ticket
                                     and off for another, buyer picks both, option vanishes). This is
                                     also where an organizer already is when thinking about how they get
                                     paid. Stripe only - nothing else can charge a saved card. --}}
                                <div class="mb-6" v-show="gatewayCapabilities[event.payment_method]?.installments">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <label for="installments_enabled" class="text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                                {{ __('messages.installments_label') }}
                                                @if (! $role->isPro())
                                                    <x-lock-badge tier="pro" />
                                                @endif
                                            </label>
                                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('messages.installments_help') }}</p>
                                        </div>
                                        <button type="button" role="switch" id="installments_enabled"
                                            :aria-checked="event.installments_enabled ? 'true' : 'false'"
                                            :disabled="!isPro && !event.installments_enabled"
                                            @click="(isPro || event.installments_enabled) && (event.installments_enabled = !event.installments_enabled)"
                                            :class="[event.installments_enabled ? 'bg-[var(--brand-button-bg)]' : 'bg-gray-200 dark:bg-gray-700', (!isPro && !event.installments_enabled) ? 'opacity-60 cursor-not-allowed' : 'cursor-pointer']"
                                            class="relative inline-flex h-6 w-11 flex-shrink-0 rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                                            <span aria-hidden="true" :class="event.installments_enabled ? 'translate-x-5' : 'translate-x-0'"
                                                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200"></span>
                                        </button>
                                    </div>

                                    {{-- The v-else is load-bearing: with only the v-if inputs, switching
                                         the toggle OFF posts nothing at all and the stored values
                                         survive the save. Same idiom as the pass block. --}}
                                    <template v-if="event.installments_enabled">
                                        <input type="hidden" name="installments_enabled" value="1">
                                    </template>
                                    <input v-else type="hidden" name="installments_enabled" value="0">

                                    <div v-if="event.installments_enabled" class="mt-4 pl-1 border-l-2 border-gray-100 dark:border-gray-700">
                                        <div class="pl-4 grid grid-cols-1 min-[900px]:grid-cols-3 gap-4">
                                            <div>
                                                <x-input-label for="installment_count" :value="__('messages.installment_count')" />
                                                <select id="installment_count" name="installment_count" v-model.number="event.installment_count"
                                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                                                    <option v-for="n in [2,3,4,5,6,8,10,12]" :key="n" :value="n">@{{ n }}</option>
                                                </select>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('messages.installment_count_help') }}</p>
                                            </div>
                                            <div>
                                                <x-input-label for="installment_final_days_before" :value="__('messages.installment_final_days_before')" />
                                                <input id="installment_final_days_before" name="installment_final_days_before" type="number" min="7" max="365"
                                                    v-model.number="event.installment_final_days_before"
                                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm" />
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('messages.installment_final_days_before_help') }}</p>
                                            </div>
                                            <div>
                                                <x-input-label for="installment_min_order_amount" :value="__('messages.installment_min_order_amount')" />
                                                <input id="installment_min_order_amount" name="installment_min_order_amount" type="number" min="0" step="0.01"
                                                    v-model="event.installment_min_order_amount"
                                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm" />
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('messages.installment_min_order_amount_help') }}</p>
                                            </div>
                                        </div>

                                        {{-- Live preview rather than a save-time validation error. By the
                                             time a validation message fires the organizer has already
                                             made their choices; this tells them, while they are choosing,
                                             that four monthly payments on a November event sold in August
                                             would land the last one after the doors open. --}}
                                        <div class="pl-4 mt-4">
                                            <div v-if="installmentPreviewFits === false" class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3 flex items-start gap-2">
                                                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
                                                <div class="text-sm text-amber-800 dark:text-amber-200">@{{ installmentPreviewText }}</div>
                                            </div>
                                            <p v-else-if="installmentPreviewText" class="text-sm text-gray-600 dark:text-gray-400">@{{ installmentPreviewText }}</p>
                                        </div>
                                    </div>
                                </div>
                                </div>

                                <button type="button" class="event-subrow ticket-tab" data-tab="options" v-cloak v-show="event.tickets_enabled || event.rsvp_enabled" @click="toggleTicketRow('options')"
                                    :aria-expanded="activeTicketTab === 'options' ? 'true' : 'false'">
                                    <span class="event-row-title"><span v-if="event.rsvp_enabled">{{ __('messages.more_options') }}</span><span v-else>{{ __('messages.options') }}</span></span>
                                    <span class="event-row-summary" :class="{ 'is-empty': ticketRows.options.empty, 'is-warn': ticketRows.options.warn }"><bdi v-text="ticketRows.options.text"></bdi></span>
                                    <svg class="event-row-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </button>
                                <!-- Options Tab -->
                                <div v-show="activeTicketTab === 'options'" data-ticket-pane="options" class="event-subrow-body">

                                <!-- Phone Number -->
                                <div class="mb-6">
                                    <div class="flex items-center gap-3">
                                        <label class="relative w-11 h-6 cursor-pointer flex-shrink-0">
                                            <input id="ask_phone_checkbox" type="checkbox"
                                                v-model="event.ask_phone"
                                                class="sr-only peer"
                                                @change="event.ask_phone || (event.require_phone = false, event.country_code_phone = false)">
                                            <div class="w-11 h-6 bg-gray-300 dark:bg-gray-600 rounded-full peer-checked:bg-[var(--brand-button-bg)] transition-colors"></div>
                                            <div class="absolute top-0.5 ltr:left-0.5 rtl:right-0.5 w-5 h-5 bg-white rounded-full shadow-md transition-transform duration-200 peer-checked:ltr:translate-x-5 peer-checked:rtl:-translate-x-5"></div>
                                        </label>
                                        <label for="ask_phone_checkbox" class="text-sm font-medium text-gray-700 dark:text-gray-300 cursor-pointer">
                                            {{ __('messages.ask_for_phone_number') }}
                                        </label>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 ms-14">{{ __('messages.ask_for_phone_number_help') }}</p>
                                    <div class="flex items-center gap-4 mt-2 ms-14" v-if="event.ask_phone">
                                        <div class="flex items-center">
                                            <input type="checkbox" v-model="event.require_phone" id="require_phone_checkbox" class="h-4 w-4 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)] border-gray-300 rounded">
                                            <label for="require_phone_checkbox" class="ms-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">{{ __('messages.field_required') }}</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input type="checkbox" v-model="event.country_code_phone" id="country_code_phone_checkbox" class="h-4 w-4 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)] border-gray-300 rounded">
                                            <label for="country_code_phone_checkbox" class="ms-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">{{ __('messages.country_code') }}</label>
                                        </div>
                                    </div>
                                    <input type="hidden" name="ask_phone" :value="event.ask_phone ? 1 : 0">
                                    <input type="hidden" name="require_phone" :value="event.require_phone ? 1 : 0">
                                    <input type="hidden" name="country_code_phone" :value="event.country_code_phone ? 1 : 0">
                                </div>

                                <!-- Individual Tickets -->
                                {{-- Pro. The blanket non-Pro wrapper that used to freeze this whole
                                     panel is gone (the free plan sells now), so the control carries
                                     its own lock: disabled rather than hidden, so a free organizer
                                     can see the feature exists instead of turning it on and watching
                                     EventRepo::saveEvent() silently scrub it back off. --}}
                                <div class="mb-6">
                                    <div class="flex items-center gap-3" :class="isPro ? '' : 'opacity-60'">
                                        <label class="relative w-11 h-6 flex-shrink-0" :class="isPro ? 'cursor-pointer' : 'cursor-not-allowed'">
                                            <input id="individual_tickets_checkbox" type="checkbox"
                                                v-model="event.individual_tickets"
                                                :disabled="!isPro"
                                                class="sr-only peer">
                                            <div class="w-11 h-6 bg-gray-300 dark:bg-gray-600 rounded-full peer-checked:bg-[var(--brand-button-bg)] transition-colors"></div>
                                            <div class="absolute top-0.5 ltr:left-0.5 rtl:right-0.5 w-5 h-5 bg-white rounded-full shadow-md transition-transform duration-200 peer-checked:ltr:translate-x-5 peer-checked:rtl:-translate-x-5"></div>
                                        </label>
                                        <label for="individual_tickets_checkbox" class="text-sm font-medium text-gray-700 dark:text-gray-300" :class="isPro ? 'cursor-pointer' : 'cursor-not-allowed'">
                                            {{ __('messages.individual_tickets') }}
                                        </label>
                                        <template v-if="!isPro"><x-lock-badge tier="pro" /></template>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 ms-14">{{ __('messages.individual_tickets_description') }}</p>
                                    <p class="text-xs mt-1 ms-14" v-if="!isPro">
                                        <button type="button" data-modal-open="upgrade-tickets" class="font-medium text-[var(--brand-blue)] hover:underline">{{ __('messages.ticket_see_pro') }}</button>
                                    </p>
                                    <input type="hidden" name="individual_tickets" :value="event.individual_tickets ? 1 : 0">

                                    <!-- Individual Ticket Fields sub-toggle -->
                                    <div v-show="event.individual_tickets" class="mt-3 ms-14">
                                        <div class="flex items-center gap-3">
                                            <label class="relative w-11 h-6 cursor-pointer flex-shrink-0">
                                                <input id="individual_ticket_fields_checkbox" type="checkbox"
                                                    v-model="event.individual_ticket_fields"
                                                    class="sr-only peer">
                                                <div class="w-11 h-6 bg-gray-300 dark:bg-gray-600 rounded-full peer-checked:bg-[var(--brand-button-bg)] transition-colors"></div>
                                                <div class="absolute top-0.5 ltr:left-0.5 rtl:right-0.5 w-5 h-5 bg-white rounded-full shadow-md transition-transform duration-200 peer-checked:ltr:translate-x-5 peer-checked:rtl:-translate-x-5"></div>
                                            </label>
                                            <label for="individual_ticket_fields_checkbox" class="text-sm font-medium text-gray-700 dark:text-gray-300 cursor-pointer">
                                                {{ __('messages.individual_ticket_fields') }}
                                            </label>
                                        </div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 ms-14">{{ __('messages.individual_ticket_fields_description') }}</p>
                                        <input type="hidden" name="individual_ticket_fields" :value="event.individual_ticket_fields ? 1 : 0">
                                    </div>
                                </div>

                                <!-- Ticket-only toggles -->
                                <div v-show="event.tickets_enabled" class="mb-6">
                                    <div class="flex items-center gap-3">
                                        <label class="relative w-11 h-6 cursor-pointer flex-shrink-0">
                                            <input id="sell_after_start_checkbox" type="checkbox"
                                                v-model="event.sell_after_start"
                                                class="sr-only peer">
                                            <div class="w-11 h-6 bg-gray-300 dark:bg-gray-600 rounded-full peer-checked:bg-[var(--brand-button-bg)] transition-colors"></div>
                                            <div class="absolute top-0.5 ltr:left-0.5 rtl:right-0.5 w-5 h-5 bg-white rounded-full shadow-md transition-transform duration-200 peer-checked:ltr:translate-x-5 peer-checked:rtl:-translate-x-5"></div>
                                        </label>
                                        <label for="sell_after_start_checkbox" class="text-sm font-medium text-gray-700 dark:text-gray-300 cursor-pointer">
                                            {{ __('messages.sell_after_start') }}
                                        </label>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 ms-14">{{ __('messages.sell_after_start_help') }}</p>
                                    <input type="hidden" name="sell_after_start" :value="event.sell_after_start ? 1 : 0">
                                </div>

                                <div v-show="event.tickets_enabled" class="mb-6">
                                    <div class="flex items-center gap-3">
                                        <label class="relative w-11 h-6 cursor-pointer flex-shrink-0">
                                            <input id="show_sales_dates_checkbox" type="checkbox"
                                                v-model="showSalesDates"
                                                class="sr-only peer"
                                                @change="onToggleSalesDates">
                                            <div class="w-11 h-6 bg-gray-300 dark:bg-gray-600 rounded-full peer-checked:bg-[var(--brand-button-bg)] transition-colors"></div>
                                            <div class="absolute top-0.5 ltr:left-0.5 rtl:right-0.5 w-5 h-5 bg-white rounded-full shadow-md transition-transform duration-200 peer-checked:ltr:translate-x-5 peer-checked:rtl:-translate-x-5"></div>
                                        </label>
                                        <label for="show_sales_dates_checkbox" class="text-sm font-medium text-gray-700 dark:text-gray-300 cursor-pointer">
                                            {{ __('messages.configure_sales_dates') }}
                                        </label>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 ms-14">{{ __('messages.configure_sales_dates_help') }}</p>
                                </div>

                                <div v-show="event.tickets_enabled" class="mb-6">
                                    <div class="flex items-center gap-3">
                                        <label class="relative w-11 h-6 cursor-pointer flex-shrink-0">
                                            <input id="show_unavailable_tickets_checkbox" type="checkbox"
                                                v-model="event.show_unavailable_tickets"
                                                class="sr-only peer">
                                            <div class="w-11 h-6 bg-gray-300 dark:bg-gray-600 rounded-full peer-checked:bg-[var(--brand-button-bg)] transition-colors"></div>
                                            <div class="absolute top-0.5 ltr:left-0.5 rtl:right-0.5 w-5 h-5 bg-white rounded-full shadow-md transition-transform duration-200 peer-checked:ltr:translate-x-5 peer-checked:rtl:-translate-x-5"></div>
                                        </label>
                                        <label for="show_unavailable_tickets_checkbox" class="text-sm font-medium text-gray-700 dark:text-gray-300 cursor-pointer">
                                            {{ __('messages.show_unavailable_tickets') }}
                                        </label>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 ms-14">{{ __('messages.show_unavailable_tickets_help') }}</p>
                                    <input type="hidden" name="show_unavailable_tickets" :value="event.show_unavailable_tickets ? 1 : 0">
                                </div>

                                <div v-if="hasLimitedPaidTickets" v-show="event.tickets_enabled">
                                    <div class="mb-6">
                                        <div class="flex items-center gap-3">
                                            <label class="relative w-11 h-6 cursor-pointer flex-shrink-0">
                                                <input id="expire_unpaid_tickets_checkbox" name="expire_unpaid_tickets_checkbox" type="checkbox"
                                                    v-model="showExpireUnpaid"
                                                    class="sr-only peer"
                                                    @change="toggleExpireUnpaid">
                                                <div class="w-11 h-6 bg-gray-300 dark:bg-gray-600 rounded-full peer-checked:bg-[var(--brand-button-bg)] transition-colors"></div>
                                                <div class="absolute top-0.5 ltr:left-0.5 rtl:right-0.5 w-5 h-5 bg-white rounded-full shadow-md transition-transform duration-200 peer-checked:ltr:translate-x-5 peer-checked:rtl:-translate-x-5"></div>
                                            </label>
                                            <label for="expire_unpaid_tickets_checkbox" class="text-sm font-medium text-gray-700 dark:text-gray-300 cursor-pointer">
                                                {{ __('messages.expire_unpaid_tickets') }}
                                            </label>
                                        </div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 ms-14">{{ __('messages.expire_unpaid_tickets_help') }}</p>
                                    </div>

                                    <div class="mb-6" v-if="showExpireUnpaid">
                                        <x-input-label for="expire_unpaid_tickets" :value="__('messages.after_number_of_hours')" />
                                        <x-text-input id="expire_unpaid_tickets" name="expire_unpaid_tickets" type="number" class="mt-1 block w-full"
                                            :value="old('expire_unpaid_tickets', $event->expire_unpaid_tickets)"
                                            v-model="event.expire_unpaid_tickets"
                                            autocomplete="off" />
                                        <x-input-error class="mt-2" :messages="$errors->get('expire_unpaid_tickets')" />
                                    </div>
                                    <div v-else>
                                        <input type="hidden" name="expire_unpaid_tickets" value="0"/>
                                    </div>
                                </div>

                                <!-- Event-level Custom Fields -->
                                <template v-if="isPro">
                                <div class="mb-6">
                                    <x-input-label :value="__('messages.custom_fields') . ' (' . __('messages.per_order') . ')'" />
                                    <p class="event-hint mb-3">{{ __('messages.order_fields_help') }}</p>

                                    <div v-if="eventCustomFields && Object.keys(eventCustomFields).length > 0" id="event-custom-fields-sortable">
                                        <div v-for="(field, fieldKey) in eventCustomFields" :key="fieldKey" :data-event-field-key="fieldKey" class="mt-2 p-3 border border-gray-200 dark:border-gray-600 rounded-lg flex items-start gap-2">
                                            <div v-show="Object.keys(eventCustomFields).length > 1" class="custom-field-drag-handle cursor-grab text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 flex-shrink-0 mt-1">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M7 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM7 8a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 8a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM7 14a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 14a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/>
                                                </svg>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                                <div>
                                                    <x-input-label :value="__('messages.field_name') . ' *'" class="text-xs" />
                                                    <x-text-input type="text" v-model="field.name" class="mt-1 block w-full text-sm" v-bind:required="event.tickets_enabled || event.rsvp_enabled" v-bind:class="{ 'border-red-500': formSubmitAttempted && !field.name }" />
                                                    <p v-if="formSubmitAttempted && !field.name" class="mt-1 text-xs text-red-600">{{ __('messages.field_name_required') }}</p>
                                                </div>
                                                <div>
                                                    <x-input-label :value="__('messages.field_type')" class="text-xs" />
                                                    <select v-model="field.type" class="mt-1 block w-full text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                                                        <option value="string">{{ __('messages.type_string') }}</option>
                                                        <option value="multiline_string">{{ __('messages.type_multiline_string') }}</option>
                                                        <option value="switch">{{ __('messages.type_switch') }}</option>
                                                        <option value="date">{{ __('messages.type_date') }}</option>
                                                        <option value="dropdown">{{ __('messages.type_dropdown') }}</option>
                                                        <option value="multiselect">{{ __('messages.type_multiselect') }}</option>
                                                    </select>
                                                </div>
                                            </div>
                                            @if($role->language_code !== 'en')
                                            <div class="mt-2">
                                                <x-input-label :value="__('messages.english_name')" class="text-xs" />
                                                <x-text-input type="text" v-model="field.name_en" class="mt-1 block w-full text-sm" placeholder="{{ __('messages.auto_translated_placeholder') }}" />
                                            </div>
                                            @endif
                                            <div class="mt-2" v-if="field.type === 'dropdown' || field.type === 'multiselect'">
                                                <x-input-label :value="__('messages.field_options')" class="text-xs" />
                                                <x-text-input type="text" v-model="field.options" class="mt-1 block w-full text-sm" placeholder="{{ __('messages.options_placeholder') }}" />
                                            </div>
                                            <div class="mt-2 flex items-center justify-between">
                                                <div class="flex items-center">
                                                    <input type="checkbox" v-model="field.required" :id="`event_field_required_${fieldKey}`" class="h-4 w-4 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)] border-gray-300 rounded">
                                                    <label :for="`event_field_required_${fieldKey}`" class="ms-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">{{ __('messages.field_required') }}</label>
                                                </div>
                                                <button type="button" @click="removeEventCustomField(fieldKey)" class="text-red-600 hover:text-red-800 dark:text-red-400 text-sm">
                                                    {{ __('messages.remove') }}
                                                </button>
                                            </div>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="custom_fields" :value="JSON.stringify(eventCustomFields || {})">
                                    <button type="button" @click="addEventCustomField" class="mt-2 text-sm text-[var(--brand-blue)] hover:text-[var(--brand-blue-dark)]" v-if="getEventCustomFieldCount() < 10">
                                        + {{ __('messages.add_field') }}
                                    </button>
                                </div>
                                </template>
                                <template v-else>
                                <x-upgrade-prompt tier="pro" :learnMoreUrl="marketing_url('/features/ticketing')" :subdomain="$subdomain" v-show="event.rsvp_enabled" class="mb-6">
                                    <x-slot:icon>
                                        <svg class="h-7 w-7 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
                                        </svg>
                                    </x-slot:icon>
                                    {{ __('messages.custom_fields_pro_only') }}
                                </x-upgrade-prompt>
                                </template>
                                </div>

                                <!-- Options Tab (continued) - shared by ticketed and registration (RSVP) events -->
                                <div v-show="activeTicketTab === 'options'" data-ticket-pane="options" class="event-subrow-body">
                                <div class="mb-6">
                                    <x-input-label for="ticket_notes" v-show="event.tickets_enabled" :value="__('messages.ticket_notes')" />
                                    <x-input-label for="ticket_notes" v-show="!event.tickets_enabled" :value="__('messages.registration_notes')" />
                                    <textarea id="ticket_notes" name="ticket_notes" v-model="event.ticket_notes" rows="4" data-content-dir="{{ content_dir($role) }}"
                                        class="html-editor mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm"></textarea>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.ticket_notes_help') }}</p>
                                    <x-link href="{{ marketing_url('/docs/creating-schedules#available-variables') }}" target="_blank" class="text-sm mt-1 inline-block">{{ __('messages.show_available_variables') }}</x-link>
                                </div>

                                <div class="mb-6" v-show="event.tickets_enabled">
                                    <x-input-label for="terms_url" :value="__('messages.terms_url')" />
                                    <x-text-input id="terms_url" name="terms_url" type="url" class="mt-1 block w-full"
                                        :value="old('terms_url', $event->terms_url)"
                                        v-model="event.terms_url" />
                                    <x-input-error class="mt-2" :messages="$errors->get('terms_url')" />
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        {{ __('messages.terms_url_help') }}
                                    </p>
                                </div>

                                </div>

                                <button type="button" class="event-subrow ticket-tab" data-tab="promo_codes" v-cloak v-show="event.tickets_enabled" @click="toggleTicketRow('promo_codes')"
                                    :aria-expanded="activeTicketTab === 'promo_codes' ? 'true' : 'false'">
                                    <span class="event-row-title">{{ __('messages.promo_codes') }}</span>
                                    <span class="event-row-summary" :class="{ 'is-empty': ticketRows.promo_codes.empty, 'is-warn': ticketRows.promo_codes.warn }"><bdi v-text="ticketRows.promo_codes.text"></bdi></span>
                                    <svg class="event-row-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </button>
                                {{-- Disabled while tickets are off, so a row left behind is neither validated nor posted.
                                     The server does not read these unless tickets are on (EventRepo::saveEvent). --}}
                                <fieldset class="event-fieldset" :disabled="! event.tickets_enabled">
                                <!-- Promo Codes Tab -->
                                <div v-show="activeTicketTab === 'promo_codes'" data-ticket-pane="promo_codes" class="event-subrow-body">
                                @if (! $role->isPro())
                                {{-- The tab still opens and shows what promo codes do. A user who can see
                                     the feature is far likelier to want it than one who hits a dead tab. --}}
                                <x-plan-gate
                                    tier="pro"
                                    :role="$role"
                                    :subdomain="$subdomain"
                                    :learnMoreUrl="marketing_url('/features/ticketing')"
                                    :title="__('messages.promo_codes')"
                                    :bullets="[
                                        __('messages.plan_gate_promo_bullet_types'),
                                        __('messages.plan_gate_promo_bullet_limits'),
                                        __('messages.plan_gate_promo_bullet_expiry'),
                                        __('messages.plan_gate_promo_bullet_reporting'),
                                    ]">
                                    {{ __('messages.plan_gate_promo_body') }}
                                </x-plan-gate>
                                @else
                                <div class="mb-6">
                                    <div v-for="(promoCode, pcIndex) in promoCodes" :key="pcIndex" class="mt-4 p-4 border border-gray-300 dark:border-gray-700 rounded-lg">
                                        <input type="hidden" v-bind:name="`promo_codes[${pcIndex}][id]`" :value="promoCode.id">

                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            <div>
                                                <x-input-label :value="__('messages.promo_code') . ' *'" class="text-xs" />
                                                <x-text-input type="text" v-bind:name="`promo_codes[${pcIndex}][code]`" v-model="promoCode.code" class="mt-1 block w-full text-sm" required maxlength="50" style="text-transform: uppercase" />
                                            </div>
                                            <div>
                                                <x-input-label :value="__('messages.discount_type')" class="text-xs" />
                                                <select v-bind:name="`promo_codes[${pcIndex}][type]`" v-model="promoCode.type" class="mt-1 block w-full text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                                                    <option value="percentage">{{ __('messages.percentage') }}</option>
                                                    <option value="fixed">{{ __('messages.fixed_amount') }}</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-3">
                                            <div>
                                                <x-input-label :value="__('messages.discount_value') . ' *'" class="text-xs" />
                                                <div class="relative mt-1">
                                                    <x-text-input type="number" step="0.01" min="0.01" v-bind:max="promoCode.type === 'percentage' ? 100 : undefined" v-bind:name="`promo_codes[${pcIndex}][value]`" v-model="promoCode.value" class="block w-full text-sm pe-14 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" required />
                                                    <span class="absolute inset-y-0 end-0 flex items-center pe-3 text-gray-400 text-sm">@{{ promoCode.type === 'percentage' ? '%' : event.ticket_currency_code }}</span>
                                                </div>
                                            </div>
                                            <div>
                                                <x-input-label :value="__('messages.max_uses')" class="text-xs" />
                                                <x-text-input type="number" min="1" v-bind:name="`promo_codes[${pcIndex}][max_uses]`" v-model="promoCode.max_uses" class="mt-1 block w-full text-sm" :placeholder="__('messages.unlimited')" />
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-3">
                                            <div>
                                                <x-input-label :value="__('messages.expires_at')" class="text-xs" />
                                                <div class="flex items-center gap-2 mt-1">
                                                    <input type="text"
                                                        :class="'datepicker-promo-date flex-1 min-w-[110px] border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm text-sm'"
                                                        :data-pc-index="pcIndex"
                                                        :value="promoCode.expires_at_date"
                                                        autocomplete="off" />
                                                    <div class="relative w-28">
                                                        <input type="text"
                                                            class="promo-time-input w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm text-sm"
                                                            :data-pc-index="pcIndex"
                                                            :value="formatPartTime(promoCode.expires_at_time)"
                                                            @focus="initPromoTimePickerOnFocus($event, pcIndex)"
                                                            @change="onPromoTimeChange(pcIndex, $event)"
                                                            autocomplete="off" placeholder="{{ __('messages.time') }}" />
                                                        <div class="time-dropdown" :ref="'promo_time_dropdown_' + pcIndex"></div>
                                                    </div>
                                                </div>
                                                <input type="hidden" v-bind:name="`promo_codes[${pcIndex}][expires_at]`" :value="promoCode.expires_at_date && promoCode.expires_at_time ? promoCode.expires_at_date + ' ' + promoCode.expires_at_time + ':00' : (promoCode.expires_at_date ? promoCode.expires_at_date + ' 23:59:00' : '')" />
                                            </div>
                                            <div class="flex items-end pb-1 gap-4">
                                                <div class="flex items-center gap-3">
                                                    <label class="relative w-11 h-6 cursor-pointer flex-shrink-0">
                                                        <input type="checkbox" :checked="promoCode.is_active"
                                                            @change="promoCode.is_active = $event.target.checked"
                                                            class="sr-only peer">
                                                        <div class="w-11 h-6 bg-gray-300 dark:bg-gray-600 rounded-full peer-checked:bg-[var(--brand-button-bg)] transition-colors"></div>
                                                        <div class="absolute top-0.5 ltr:left-0.5 rtl:right-0.5 w-5 h-5 bg-white rounded-full shadow-md transition-transform duration-200 peer-checked:ltr:translate-x-5 peer-checked:rtl:-translate-x-5"></div>
                                                    </label>
                                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('messages.active') }}</span>
                                                </div>
                                                <input type="hidden" v-bind:name="`promo_codes[${pcIndex}][is_active]`" :value="promoCode.is_active ? 1 : 0">
                                                <span v-if="promoCode.times_used > 0" class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ __('messages.times_used') }}: @{{ promoCode.times_used }}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="mt-3" v-if="tickets.length > 1 && !(isInvoiceNinjaPaymentLink && event.payment_method === 'invoiceninja')">
                                            <x-input-label :value="__('messages.applies_to')" class="text-xs" />
                                            <div class="mt-1 flex items-center gap-3">
                                                <label class="flex items-center gap-1 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                                                    <input type="radio" :name="`promo_ticket_mode_${pcIndex}`" value="all" :checked="!promoCode.ticket_ids || promoCode.ticket_ids.length === 0" @change="promoCode.ticket_ids = []" class="text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                                                    {{ __('messages.all_tickets') }}
                                                </label>
                                                <label class="flex items-center gap-1 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                                                    <input type="radio" :name="`promo_ticket_mode_${pcIndex}`" value="specific" :checked="promoCode.ticket_ids && promoCode.ticket_ids.length > 0" @change="promoCode.ticket_ids = promoCode.ticket_ids && promoCode.ticket_ids.length > 0 ? promoCode.ticket_ids : [tickets[0]?.id]" class="text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                                                    {{ __('messages.specific_tickets') }}
                                                </label>
                                            </div>
                                            <div v-if="promoCode.ticket_ids && promoCode.ticket_ids.length > 0" class="mt-2 ms-6 space-y-1">
                                                <label v-for="ticket in tickets" :key="ticket.uid" class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 cursor-pointer">
                                                    <input type="checkbox" :value="ticket.id" v-model="promoCode.ticket_ids" class="h-4 w-4 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)] border-gray-300 rounded">
                                                    <span v-text="ticket.type || '{{ __('messages.ticket') }}'"></span>
                                                </label>
                                            </div>
                                            <input type="hidden" v-bind:name="`promo_codes[${pcIndex}][ticket_ids]`" :value="JSON.stringify(promoCode.ticket_ids || [])">
                                        </div>

                                        <div class="mt-3 flex items-center gap-1.5">
                                            <template v-if="promoCode.code && promoLinkBaseUrl">
                                                <span class="text-xs text-gray-500 dark:text-gray-400 flex-none">{{ __('messages.promo_share_link') }}</span>
                                                <div class="flex items-center gap-1 min-w-0 w-fit">
                                                    <a :href="promoLinkBaseUrl + '?promo=' + promoCode.code.trim().toUpperCase()"
                                                       target="_blank"
                                                       class="text-xs text-gray-500 dark:text-gray-400 hover:text-[var(--brand-blue)] truncate flex-shrink"
                                                       :title="promoLinkBaseUrl + '?promo=' + promoCode.code.trim().toUpperCase()">@{{ (promoLinkBaseUrl + '?promo=' + promoCode.code.trim().toUpperCase()).replace(/^https?:\/\//, '') }}</a>
                                                    <button type="button" @click="copyPromoLink(promoCode)" class="inline-flex items-center justify-center flex-shrink-0 text-gray-400 hover:text-[var(--brand-blue)] transition-colors" :title="promoCode._copied ? '{{ __('messages.copied') }}' : '{{ __('messages.copy_link') }}'">
                                                        <svg v-if="!promoCode._copied" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9.75a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184" />
                                                        </svg>
                                                        <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-green-500">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </template>
                                            <button type="button" @click="removePromoCode(pcIndex)" class="ms-auto text-red-600 hover:text-red-800 dark:text-red-400 text-sm flex-shrink-0">
                                                {{ __('messages.remove') }}
                                            </button>
                                        </div>
                                    </div>

                                    <button type="button" @click="addPromoCode"
                                        v-show="!(isInvoiceNinjaPaymentLink && event.payment_method === 'invoiceninja' && promoCodes.length >= 1)"
                                        class="mt-2 text-sm text-[var(--brand-blue)] hover:text-[var(--brand-blue-dark)]">
                                        + {{ __('messages.add_promo_code') }}
                                    </button>
                                </div>
                                @endif
                                </div>

                                </fieldset>
                                <button type="button" class="event-subrow ticket-tab" data-tab="add_ons" v-cloak v-show="event.tickets_enabled" @click="toggleTicketRow('add_ons')"
                                    :aria-expanded="activeTicketTab === 'add_ons' ? 'true' : 'false'">
                                    <span class="event-row-title">{{ __('messages.add_ons') }}</span>
                                    <span class="event-row-summary" :class="{ 'is-empty': ticketRows.add_ons.empty, 'is-warn': ticketRows.add_ons.warn }"><bdi v-text="ticketRows.add_ons.text"></bdi></span>
                                    <svg class="event-row-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </button>
                                {{-- Disabled while tickets are off, so a row left behind is neither validated nor posted.
                                     The server does not read these unless tickets are on (EventRepo::saveEvent). --}}
                                <fieldset class="event-fieldset" :disabled="! event.tickets_enabled">
                                <!-- Add-ons Tab -->
                                <div v-show="activeTicketTab === 'add_ons'" data-ticket-pane="add_ons" class="event-subrow-body">
                                    @if (! $role->isPro())
                                    <x-plan-gate
                                        tier="pro"
                                        :role="$role"
                                        :subdomain="$subdomain"
                                        :learnMoreUrl="marketing_url('/features/ticketing')"
                                        :title="__('messages.add_ons')"
                                        :bullets="[
                                            __('messages.plan_gate_addons_bullet_extras'),
                                            __('messages.plan_gate_addons_bullet_stock'),
                                            __('messages.plan_gate_addons_bullet_limits'),
                                            __('messages.plan_gate_addons_bullet_reporting'),
                                        ]">
                                        {{ __('messages.add_ons_help') }}
                                    </x-plan-gate>
                                    @else
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">{{ __('messages.add_ons_help') }}</p>

                                    <div v-for="(addon, aIndex) in addons" :key="addon._key" class="mb-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('messages.name') }} *</label>
                                                <input type="text" v-model="addon.type" :name="`addons[${aIndex}][type]`" required
                                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] text-sm">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('messages.price') }}</label>
                                                <input type="number" v-model="addon.price" :name="`addons[${aIndex}][price]`" step="0.01" min="0"
                                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] text-sm">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('messages.quantity') }}</label>
                                                <input type="number" v-model="addon.quantity" :name="`addons[${aIndex}][quantity]`" min="0" :placeholder="'{{ __('messages.unlimited') }}'"
                                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] text-sm">
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('messages.description') }}</label>
                                            <textarea v-model="addon.description" :name="`addons[${aIndex}][description]`" rows="2"
                                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] text-sm"></textarea>
                                        </div>
                                        <div class="mt-3">
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('messages.url') }}</label>
                                            <input type="url" v-model="addon.url" :name="`addons[${aIndex}][url]`" placeholder="https://..."
                                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] text-sm" />
                                        </div>
                                        <div class="mt-3">
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('messages.image') }}</label>
                                            <div v-if="addon.image_url" class="mb-2 relative inline-block">
                                                <img :src="addon.image_url" :alt="addon.type" style="max-height: 80px" class="rounded-lg border border-gray-200 dark:border-gray-600" />
                                                <button type="button" @click="removeAddonImage(aIndex)" style="width: 20px; height: 20px; min-width: 20px; min-height: 20px;" class="absolute -top-2 -right-2 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                </button>
                                            </div>
                                            <div v-if="!addon.image_url">
                                                <button type="button" @click="(Array.isArray($refs['addon_image_' + aIndex]) ? $refs['addon_image_' + aIndex][0] : $refs['addon_image_' + aIndex]).click()"
                                                    class="inline-flex items-center px-3 py-1.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm font-medium rounded-lg transition-colors border border-gray-300 dark:border-gray-600">
                                                    <svg class="w-4 h-4 ltr:mr-1.5 rtl:ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                    </svg>
                                                    {{ __('messages.choose_file') }}
                                                </button>
                                            </div>
                                            <input type="file" :ref="'addon_image_' + aIndex"
                                                accept="image/png, image/jpeg, image/gif, image/webp" class="hidden"
                                                @change="onAddonFileChange(aIndex, $event)" />
                                            <input type="hidden" v-if="addon.image_url && addon.image_url.startsWith('data:')" :name="`addon_image_data[${aIndex}]`" :value="addon.image_url">
                                            <input type="hidden" :name="`addons[${aIndex}][remove_image]`" :value="addon.remove_image ? 1 : 0" />
                                        </div>
                                        <div v-if="addon.max_per_order !== null && addon.max_per_order !== undefined" class="mt-3">
                                            <x-input-label :value="__('messages.max_per_order')" />
                                            <div class="mt-2 p-3 border border-gray-200 dark:border-gray-600 rounded-lg">
                                                <x-text-input type="number" min="1" step="1" v-model.number="addon.max_per_order" class="block w-full sm:w-48" />
                                                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ __('messages.max_per_order_help') }}</p>
                                                <div class="mt-2 flex justify-end">
                                                    <button type="button" @click="removeAddonMaxPerOrder(aIndex)" class="text-red-600 hover:text-red-800 dark:text-red-400 text-sm">
                                                        {{ __('messages.remove') }}
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <input type="hidden" :name="`addons[${aIndex}][max_per_order]`" :value="(addon.max_per_order === null || addon.max_per_order === undefined || addon.max_per_order === '') ? '' : addon.max_per_order">
                                        <div class="mt-2 flex justify-between items-center">
                                            <input type="hidden" :name="`addons[${aIndex}][id]`" :value="addon.id">
                                            <button type="button" @click="addAddonMaxPerOrder(aIndex)" class="text-sm text-[var(--brand-blue)] hover:text-[var(--brand-blue-dark)]" v-if="addon.max_per_order === null || addon.max_per_order === undefined">
                                                + {{ __('messages.add_limit') }}
                                            </button>
                                            <div v-else></div>
                                            <button type="button" @click="removeAddon(aIndex)" class="text-red-600 hover:text-red-800 dark:text-red-400 text-sm">
                                                {{ __('messages.remove') }}
                                            </button>
                                        </div>
                                    </div>

                                    <button type="button" @click="addAddon"
                                        class="mt-2 text-sm text-[var(--brand-blue)] hover:text-[var(--brand-blue-dark)]">
                                        + {{ __('messages.add_add_on') }}
                                    </button>
                                    @endif
                                </div>

                                </fieldset>
                                </div>
                                </div><!-- /was the blanket non-Pro wrapper; now a plain div -->


                            </div>

                            {{-- Saving this as the default belongs to a choice that has been made. --}}
                            <div v-cloak v-show="ticketChoice || ticketsTouched">
                            <hr class="my-4 border-gray-200 dark:border-gray-700">

                            @if ($user->isMember($subdomain))
                            {{-- Saving the ticket set as this schedule's default is not a Pro feature at
                                 all; it only carried the blanket non-Pro wrapper because it sat next to
                                 the ticket area. --}}
                            <div class="flex items-center gap-3 mt-3">
                                <label class="relative w-11 h-6 cursor-pointer flex-shrink-0">
                                    <input id="save_default_tickets" name="save_default_tickets" type="checkbox"
                                        class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-300 dark:bg-gray-600 rounded-full peer-checked:bg-[var(--brand-button-bg)] transition-colors"></div>
                                    <div class="absolute top-0.5 ltr:left-0.5 rtl:right-0.5 w-5 h-5 bg-white rounded-full shadow-md transition-transform duration-200 peer-checked:ltr:translate-x-5 peer-checked:rtl:-translate-x-5"></div>
                                </label>
                                <label for="save_default_tickets" class="text-sm font-medium text-gray-700 dark:text-gray-300 cursor-pointer">
                                    {{ __('messages.save_as_default') }}
                                </label>
                            </div>
                            @endif
                            </div>

                        </div>
                    </div>
                @endif

                <button type="button" class="mobile-section-header" data-section="section-participants" {!! $moreSectionAttrs !!}>
                    <span class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                        </svg>
                        <span class="section-nav-text">
                            <span>{{ __('messages.participants') }}</span>
                            <span class="section-nav-summary" v-cloak :class="{ 'is-empty': tabSummaries['section-participants'].empty }"><bdi v-text="tabSummaries['section-participants'].text"></bdi></span>
                        </span>
                        <span class="section-nav-dot" v-cloak v-show="sectionDirty['section-participants']"></span>
                    </span>
                    <svg class="w-5 h-5 text-gray-400 transition-transform duration-200 accordion-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div id="section-participants" class="section-content lg:mt-0">
                    {{-- Marks that the participants section was submitted, so the backend treats a
                         previously-attached talent absent from members[] as an intentional removal.
                         Absent from API/import submissions, which therefore preserve existing talents. --}}
                    <input type="hidden" name="members_submitted" value="1">
                    <div class="{{ $tabCol }}">
                        <div class="event-tab-title">
                            <h2 class="section-heading-name text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                            </svg>
                            {{ __('messages.participants') }}
                            </h2>
                            <span class="event-tab-aside" v-cloak>
                                @if ($role->isVenue()){{ __('messages.optional') }}@endif
                            </span>
                        </div>
                        {{-- Always, not only while the list is empty: "Participants" reads as the people
                             coming until this says it is the people on stage. --}}
                        <p class="event-hint">{{ __('messages.participants_help') }}</p>

                        <div>
                            <div v-cloak v-if="selectedMembers && selectedMembers.length > 0" class="event-list">
                                <div v-for="member in selectedMembers" :key="member.id" class="event-list-row">
                                    <input type="hidden" v-bind:name="'members[' + member.id + '][email]'" v-bind:value="member.email" />
                                    <input type="hidden" v-bind:name="'members[' + member.id + '][phone]'" v-bind:value="member.phone" />
                                    <div v-show="editMemberId === member.id" class="w-full">
                                        <div class="event-grid2">
                                            <div>
                                                <x-input-label :value="__('messages.name') . ' *'" />
                                                <x-text-input v-bind:id="'edit_member_name_' + member.id"
                                                    v-bind:name="'members[' + member.id + '][name]'" type="text" class="mt-1 block w-full"
                                                    v-model="selectedMembers.find(m => m.id === member.id).name" v-bind:required="editMemberId === member.id"
                                                    @keydown.enter.prevent="editMember()" autocomplete="off" />
                                            </div>
                                            <div>
                                                <x-input-label :value="__('messages.email')" />
                                                <x-text-input v-bind:id="'edit_member_email_' + member.id"
                                                    v-bind:name="'members[' + member.id + '][email]'" type="email" class="mt-1 block w-full"
                                                    v-model="selectedMembers.find(m => m.id === member.id).email" @keydown.enter.prevent="editMember()" autocomplete="off" />
                                            </div>
                                            <div>
                                                <x-input-label :value="__('messages.phone_number')" />
                                                <input type="tel" :id="'edit_member_phone_' + member.id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm"
                                                    @keydown.enter.prevent="editMember()" autocomplete="off" />
                                            </div>
                                            <div>
                                                <x-input-label :value="__('messages.youtube_video_url')" />
                                                <x-text-input v-bind:id="'edit_member_youtube_url_' + member.id"
                                                    v-bind:name="'members[' + member.id + '][youtube_url]'" type="url" class="mt-1 block w-full"
                                                    v-model="selectedMembers.find(m => m.id === member.id).youtube_url" @keydown.enter.prevent="editMember()" autocomplete="off" />
                                            </div>
                                        </div>
                                        <div class="mt-3 flex items-center gap-3">
                                            <button type="button" class="event-link event-link-quiet" @click="cancelEditMember()">{{ __('messages.cancel') }}</button>
                                            <x-brand-button size="sm" @click="editMember()">{{ __('messages.done') }}</x-brand-button>
                                        </div>
                                    </div>
                                    <div v-show="editMemberId !== member.id" class="flex items-center gap-3 w-full min-w-0">
                                        <span class="event-avatar" aria-hidden="true" v-text="(member.name || '?').trim().charAt(0).toUpperCase()"></span>
                                        <div class="min-w-0 flex-1">
                                            <div class="event-list-name truncate">
                                                <a v-if="member.url" :href="member.url" target="_blank" class="hover:underline">@{{ member.name }}</a>
                                                <template v-else>@{{ member.name }}</template>
                                                <span class="event-chip" v-if="roleIsTalent && member.id === roleEncodedId">{{ __('messages.this_schedule') }}</span>
                                            </div>
                                            <div class="event-list-sub truncate" v-if="! (roleIsTalent && member.id === roleEncodedId) && (member.email || member.phone || member.youtube_url)">
                                                <a v-if="member.email" :href="'mailto:' + member.email" class="hover:underline">@{{ member.email }}</a>
                                                <template v-else-if="member.phone">@{{ member.phone }}</template>
                                                <template v-if="member.youtube_url"><span v-if="member.email || member.phone" aria-hidden="true"> &middot; </span><a :href="member.youtube_url" target="_blank" class="hover:underline">{{ __('messages.video_link') }}</a></template>
                                            </div>
                                            <div v-if="((member.id && member.id.toString().startsWith('new_')) || (!member.user_id)) && ((member.email && isHosted) || (member.phone && smsConfigured))" class="mt-1.5">
                                                <div class="flex items-center">
                                                    <template v-if="member.email && isHosted">
                                                        <input type="checkbox" :id="'send_email_to_member_' + member.id" :name="'send_email_to_members[' + member.email + ']'" v-model="sendEmailToMembers[member.email]"
                                                            class="h-4 w-4 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)] border-gray-300 rounded">
                                                        <label :for="'send_email_to_member_' + member.id" class="ms-2 block text-sm text-gray-700 dark:text-gray-300">{{ __('messages.send_email_to_notify_them') }}</label>
                                                    </template>
                                                    <template v-else-if="member.phone && smsConfigured">
                                                        <input type="checkbox" :id="'send_sms_to_member_' + member.id" :name="'send_sms_to_members[' + member.phone + ']'" v-model="sendSmsToMembers[member.phone]"
                                                            class="h-4 w-4 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)] border-gray-300 rounded">
                                                        <label :for="'send_sms_to_member_' + member.id" class="ms-2 block text-sm text-gray-700 dark:text-gray-300">{{ __('messages.send_sms_to_notify_them') }}</label>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="event-list-actions">
                                            <button v-if="!member.user_id" @click="editMember(member)" type="button" class="event-link">{{ __('messages.edit') }}</button>
                                            <button v-if="!(roleIsTalent && member.id === roleEncodedId)" @click="removeMember(member)" type="button" class="event-link is-danger">{{ __('messages.remove') }}</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-cloak v-if="!showMemberTypeRadio" class="mt-3">
                                <button type="button" @click="showAddMemberForm" class="event-link">+ {{ __('messages.add_participant') }}</button>
                            </div>

                            {{-- v-show, never v-if: the phone field inside is wired once on load. Disabled while it
                                 is closed, so what it holds is never validated when the event is saved. --}}
                            <fieldset v-cloak v-show="showMemberTypeRadio" :disabled="! showMemberTypeRadio" class="event-fieldset event-add-box">
                                <div v-if="memberType === 'use_existing' && filteredMembers.length > 0">
                                    <div class="flex items-center justify-between gap-3">
                                        <label for="selected_member" class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{ __('messages.your_schedules') }}</label>
                                        <button type="button" class="event-link" @click="memberType = 'create_new'">{{ __('messages.someone_new') }}</button>
                                    </div>
                                    <select v-model="selectedMember" @change="addExistingMember" id="selected_member" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                                        <option value="" disabled selected>{{ __('messages.please_select') }}</option>
                                        <option v-for="member in filteredMembers" :key="member.id" :value="member">
                                            @{{ member.name }} <template v-if="member.email">(@{{ member.email }})</template>
                                        </option>
                                    </select>
                                    <div class="mt-3" v-if="selectedMembers.length > 0">
                                        <button type="button" class="event-link event-link-quiet" @click="cancelAddMember">{{ __('messages.cancel') }}</button>
                                    </div>
                                </div>

                                <div v-show="memberType === 'create_new' || filteredMembers.length === 0">
                                    <div class="event-grid2">
                                        <div>
                                            <div class="flex items-center justify-between gap-3">
                                                <x-input-label for="member_name" :value="__('messages.name') . ' *'" />
                                            </div>
                                            <x-text-input id="member_name" @keydown.enter.prevent="addMember"
                                                v-model="memberName" type="text" class="mt-1 block w-full" :required="false" autocomplete="off" />
                                        </div>
                                        <div>
                                            <div class="flex items-center justify-between gap-3">
                                                <x-input-label for="member_email" :value="__('messages.email')" />
                                                <button type="button" class="event-link" v-if="filteredMembers.length > 0" @click="memberType = 'use_existing'">{{ __('messages.pick_from_your_schedules') }}</button>
                                            </div>
                                            <x-text-input id="member_email" type="email" class="mt-1 block w-full"
                                                @keydown.enter.prevent="addMember" @blur="searchMembers" v-model="memberEmail" autocomplete="off" />
                                            <x-input-error class="mt-2" :messages="$errors->get('member_email')" />
                                        </div>
                                    </div>
                                    <div class="event-links" v-if="! memberMoreOpen && ! memberYoutubeUrl">
                                        <button type="button" class="event-link" @click="memberMoreOpen = true">+ {{ __('messages.phone_or_video_link') }}</button>
                                    </div>
                                    <div class="event-grid2 mt-3" v-show="memberMoreOpen || memberYoutubeUrl">
                                        <div>
                                            <x-input-label for="member_phone_input" :value="__('messages.phone_number')" />
                                            <input type="hidden" v-model="memberPhone">
                                            <input type="tel" id="member_phone_input" ref="memberPhoneInput" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm"
                                                @keydown.enter.prevent="addMember" autocomplete="off" />
                                        </div>
                                        <div>
                                            <x-input-label for="member_youtube_url" :value="__('messages.youtube_video_url')" />
                                            <x-text-input id="member_youtube_url" @keydown.enter.prevent="addMember"
                                                v-model="memberYoutubeUrl" type="url" class="mt-1 block w-full" autocomplete="off" />
                                        </div>
                                    </div>

                                    <div v-if="(memberEmail && isHosted) || (memberPhone && smsConfigured)" class="mt-3">
                                        <div class="flex items-center">
                                            <template v-if="memberEmail && isHosted">
                                                <input id="send_email_to_new_member" type="checkbox" v-model="sendEmailToNewMember"
                                                    class="h-4 w-4 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)] border-gray-300 rounded">
                                                <label for="send_email_to_new_member" class="ms-2 block text-sm text-gray-700 dark:text-gray-300">{{ __('messages.send_email_to_notify_them') }}</label>
                                            </template>
                                            <template v-else-if="memberPhone && smsConfigured">
                                                <input id="send_sms_to_new_member" type="checkbox" v-model="sendSmsToNewMember"
                                                    class="h-4 w-4 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)] border-gray-300 rounded">
                                                <label for="send_sms_to_new_member" class="ms-2 block text-sm text-gray-700 dark:text-gray-300">{{ __('messages.send_sms_to_notify_them') }}</label>
                                            </template>
                                        </div>
                                    </div>

                                    <div v-if="memberSearchResults.length" class="mt-3">
                                        <p class="event-group-label">{{ __('messages.matching_schedules') }}</p>
                                        <div class="event-list">
                                            <div v-for="member in memberSearchResults" :key="member.id" class="event-list-row is-centered">
                                                <span class="event-avatar" aria-hidden="true" v-text="(member.name || '?').trim().charAt(0).toUpperCase()"></span>
                                                <div class="min-w-0 flex-1">
                                                    <div class="event-list-name truncate"><a :href="member.url" target="_blank" class="hover:underline">@{{ member.name }}</a></div>
                                                    <div class="event-list-sub truncate" v-if="member.email">@{{ member.email }}</div>
                                                </div>
                                                <x-secondary-button type="button" @click="selectMember(member)">{{ __('messages.select') }}</x-secondary-button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-4 flex items-center gap-3">
                                        <button v-if="selectedMembers.length > 0" type="button" class="event-link event-link-quiet" @click="cancelAddMember">{{ __('messages.cancel') }}</button>
                                        <x-brand-button size="sm" id="add-member-btn" @click="addMember">{{ __('messages.add') }}</x-brand-button>
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    </div>
                </div>

                <!-- Agenda Section -->
                <button type="button" class="mobile-section-header" data-section="section-agenda" {!! $moreSectionAttrs !!}>
                    <span class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                        </svg>
                        <span class="section-nav-text">
                            <span>{{ __('messages.agenda') }}</span>
                            <span class="section-nav-summary" v-cloak :class="{ 'is-empty': tabSummaries['section-agenda'].empty }"><bdi v-text="tabSummaries['section-agenda'].text"></bdi></span>
                        </span>
                        <span class="section-nav-dot" v-cloak v-show="sectionDirty['section-agenda']"></span>
                    </span>
                    <svg class="w-5 h-5 text-gray-400 transition-transform duration-200 accordion-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div id="section-agenda" class="section-content lg:mt-0">
                    <div class="{{ $tabCol }} {{ auth()->check() && auth()->user()->isRtl() ? 'rtl' : '' }}">
                        <div class="event-tab-title">
                            <h2 class="section-heading-name text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                            </svg>
                            {{ __('messages.agenda') }}
                            </h2>
                            <div v-cloak v-if="eventParts.length" class="flex-none">
                                <label class="flex items-center gap-3 cursor-pointer">
    <button type="button" role="switch" :aria-checked="agendaShowTimes ? 'true' : 'false'" @click="agendaShowTimes = ! agendaShowTimes; markTabDirty('section-agenda')"
        :class="['relative inline-flex h-6 w-11 flex-shrink-0 rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800', agendaShowTimes ? 'bg-[var(--brand-button-bg)]' : 'bg-gray-200 dark:bg-gray-700']">
        <span :class="['inline-block h-5 w-5 transform rounded-full bg-white shadow transition-transform mt-0.5', agendaShowTimes ? 'translate-x-5' : 'translate-x-0.5']"></span>
    </button>
    <span><span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('messages.show_times') }}</span></span>
</label>
                            </div>
                        </div>

                        <p v-cloak v-if="eventParts.length && ! agendaShowTimes" class="event-hint">{{ __('messages.agenda_show_times_help') }}</p>
                        <p v-cloak v-if="!eventParts.length" class="event-empty">{{ __('messages.agenda_empty_help') }}</p>

                        <div v-cloak v-if="eventParts.length" class="event-agenda-head" :class="{ 'no-times': ! agendaShowTimes }" aria-hidden="true">
                            <i></i>
                            <span v-if="agendaShowTimes"><span>{{ __('messages.start_time') }}</span><span>{{ __('messages.end_time') }}</span></span>
                            <span>{{ __('messages.part_name') }}</span>
                        </div>
                        <div v-cloak v-if="eventParts.length" class="event-agenda" @dragover.prevent="onContainerPartDragOver($event)" @drop="onPartDrop()">
                            <div v-for="(part, index) in eventParts" :key="part.uid" class="event-agenda-row"
                                 @dragover="onPartDragOver(index, $event)" @drop="onPartDrop()"
                                 :class="{ 'opacity-50': partDragIndex === index }"
                                 :style="{ paddingTop: partDropTargetIndex === index && partDragIndex !== null && partDragIndex !== index ? '2.5rem' : '', transition: 'padding 150ms ease' }">
                                <div class="event-agenda-line" :class="{ 'has-times': agendaShowTimes }">
                                    {{-- Only the handle starts a drag, so text in the fields can still be selected. --}}
                                    <span class="event-drag" draggable="true" @dragstart="onPartDragStart(index)" @dragend="onPartDragEnd" title="{{ __('messages.drag_to_reorder') }}">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                                            <circle cx="5.5" cy="3.5" r="1.5"/><circle cx="10.5" cy="3.5" r="1.5"/><circle cx="5.5" cy="8" r="1.5"/><circle cx="10.5" cy="8" r="1.5"/><circle cx="5.5" cy="12.5" r="1.5"/><circle cx="10.5" cy="12.5" r="1.5"/>
                                        </svg>
                                    </span>
                                    <input type="text" v-model="part.name" :name="'event_parts[' + index + '][name]'" required @keydown.enter.prevent placeholder="{{ __('messages.part_name') }}" aria-label="{{ __('messages.part_name') }}"
                                        class="event-agenda-name block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm" />
                                    <div class="event-agenda-actions">
                                        <button type="button" class="event-icon-btn" @click="movePartUp(index)" :disabled="index === 0" title="{{ __('messages.up') }}" aria-label="{{ __('messages.up') }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" /></svg>
                                        </button>
                                        <button type="button" class="event-icon-btn" @click="movePartDown(index)" :disabled="index === eventParts.length - 1" title="{{ __('messages.down') }}" aria-label="{{ __('messages.down') }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                                        </button>
                                        <button type="button" class="event-icon-btn is-remove" @click="removePart(index)" title="{{ __('messages.remove') }}" aria-label="{{ __('messages.remove') }}">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                    <div class="event-agenda-times" v-if="agendaShowTimes">
                                        <div class="relative">
                                            <input type="text" :value="formatPartTime(part.start_time)" placeholder="{{ __('messages.start_time') }}" aria-label="{{ __('messages.start_time') }}"
                                                   @focus="initPartTimePickerOnFocus($event, part.uid, 'start')" @change="onPartTimeChange(index, 'start_time', $event)" @keydown.enter.prevent
                                                   class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm" />
                                            <div class="time-dropdown" :ref="'part_start_dropdown_' + part.uid"></div>
                                        </div>
                                        <div class="relative">
                                            <input type="text" :value="formatPartTime(part.end_time)" placeholder="{{ __('messages.end_time') }}" aria-label="{{ __('messages.end_time') }}"
                                                   @focus="initPartTimePickerOnFocus($event, part.uid, 'end')" @change="onPartTimeChange(index, 'end_time', $event)" @keydown.enter.prevent
                                                   class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm" />
                                            <div class="time-dropdown" :ref="'part_end_dropdown_' + part.uid"></div>
                                        </div>
                                    </div>
                                </div>
                                {{-- Always posted. With "Show times" off the agenda has no times, and that is SAID: sent
                                     as empty values, and announced by the save bar. It used to happen by the fields
                                     simply not being on the page, which the server read as "delete them". --}}
                                <input type="hidden" :name="'event_parts[' + index + '][start_time]'" :value="agendaShowTimes ? part.start_time : ''" />
                                <input type="hidden" :name="'event_parts[' + index + '][end_time]'" :value="agendaShowTimes ? part.end_time : ''" />
                                <input type="hidden" :name="'event_parts[' + index + '][id]'" :value="part.id || ''" />
                                <div class="event-agenda-sub">
                                    <button type="button" class="event-link" v-if="! partDescOpen[part.uid]" @click="openPartDescription(part)">+ {{ __('messages.add_description') }}</button>
                                    <div v-show="partDescOpen[part.uid]">
                                        <textarea :ref="'partDescription_' + part.uid" :name="'event_parts[' + index + '][description]'" rows="1"
                                                  data-content-dir="{{ content_dir($role) }}" aria-label="{{ __('messages.description') }}"
                                                  class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">@{{ part.description }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="button" @click="addPart" class="event-link">+ {{ __('messages.add_part') }}</button>
                        </div>
                        <input type="hidden" name="agenda_show_times" :value="agendaShowTimes ? '1' : '0'" />

                        {{-- A first event is not shown a locked row: the same rule as the description's generator. --}}
                        @if ((config('services.google.gemini_key') || config('services.openai.api_key')) && ! ($isFirstEventRun && config('app.hosted') && ! $role->isEnterprise()))
                        <div class="event-subrows">
                            @if (config('app.hosted') && ! $role->isEnterprise())
                            {{-- Not on this plan: the row says so and opens the upgrade prompt. It used to show the
                                 controls, which then failed with an alert. --}}
                            <button type="button" class="event-subrow" aria-expanded="false" @click.prevent="openUpgrade('upgrade-ai-details')">
                                <span class="event-row-title">{{ __('messages.import') }}</span>
                                <span class="event-row-summary">{{ __('messages.agenda_import_prompt') }}</span>
                                <x-lock-badge tier="enterprise" />
                            </button>
                            @else
                            <button type="button" class="event-subrow" :aria-expanded="(agendaImportOpen || showPartsPreview) ? 'true' : 'false'" @click="agendaImportOpen = ! agendaImportOpen">
                                <span class="event-row-title">{{ __('messages.import') }}</span>
                                <span class="event-row-summary">{{ __('messages.agenda_import_prompt') }}</span>
                                <svg class="event-row-chevron" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                            </button>
                            <div v-show="agendaImportOpen || showPartsPreview" class="event-subrow-body">
                                <div v-if="partsImportError" role="alert" class="mb-3 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-sm text-red-700 dark:text-red-400">@{{ partsImportError }}</div>
                                <textarea v-model="partsText" rows="4" class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm" placeholder="{{ __('messages.paste_setlist_or_agenda') }}" aria-label="{{ __('messages.paste_setlist_or_agenda') }}"></textarea>
                                <div class="mt-2 flex flex-wrap items-center gap-2">
                                    <x-brand-button size="sm" @click="parsePartsFromText" v-bind:disabled="parsingParts || !partsText">{{ __('messages.read_this_text') }}</x-brand-button>
                                    <x-secondary-button type="button" @click="$refs.partsImageInput.click()" v-bind:disabled="parsingParts">
                                        <span v-if="parsingParts">{{ __('messages.parsing_image') }}</span>
                                        <span v-else>{{ __('messages.or_choose_a_photo') }}</span>
                                    </x-secondary-button>
                                    <input type="file" ref="partsImageInput" @change="parsePartsFromImage($event)" accept="image/*" class="hidden" />
                                </div>
                                <p class="event-hint">{{ __('messages.agenda_import_adds') }}</p>
                                <div v-if="showPartsPreview" class="mt-3 border border-blue-200 dark:border-blue-800 rounded-lg p-4 bg-blue-50 dark:bg-blue-900/20">
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">{{ __('messages.preview_parts') }}</h3>
                                    <div class="space-y-2 mb-4">
                                        <div v-for="(part, index) in parsedPartsPreview" :key="index" class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                            <span class="font-medium text-gray-500">@{{ index + 1 }}.</span>
                                            <span>@{{ part.name }}</span>
                                            <span v-if="part.start_time" class="text-gray-400">(@{{ formatPartTime(part.start_time) }}<span v-if="part.end_time"> - @{{ formatPartTime(part.end_time) }}</span>)</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <button type="button" class="event-link event-link-quiet" @click="showPartsPreview = false; parsedPartsPreview = []">{{ __('messages.discard') }}</button>
                                        <x-brand-button size="sm" @click="acceptParsedParts">{{ __('messages.accept_parts') }}</x-brand-button>
                                    </div>
                                </div>
                                <div class="event-links" v-if="! agendaPromptOpen && ! partsAiPrompt">
                                    <button type="button" class="event-link" @click="agendaPromptOpen = true">+ {{ __('messages.instructions_for_the_ai') }}</button>
                                </div>
                                <div class="mt-3" v-show="agendaPromptOpen || partsAiPrompt">
                                    <x-input-label :value="__('messages.ai_agenda_prompt')" />
                                    <textarea v-model="partsAiPrompt" rows="2" maxlength="500" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm"
                                        placeholder="{{ __('messages.ai_agenda_prompt_placeholder') }}"></textarea>
                                    <div class="mt-2">
                                        <label class="flex items-center gap-3 cursor-pointer">
    <button type="button" role="switch" :aria-checked="savePartsAiPromptDefault ? 'true' : 'false'" @click="savePartsAiPromptDefault = ! savePartsAiPromptDefault; markTabDirty('section-agenda')"
        :class="['relative inline-flex h-6 w-11 flex-shrink-0 rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800', savePartsAiPromptDefault ? 'bg-[var(--brand-button-bg)]' : 'bg-gray-200 dark:bg-gray-700']">
        <span :class="['inline-block h-5 w-5 transform rounded-full bg-white shadow transition-transform mt-0.5', savePartsAiPromptDefault ? 'translate-x-5' : 'translate-x-0.5']"></span>
    </button>
    <span><span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('messages.use_instructions_for_every_event') }}</span></span>
</label>
                                    </div>
                                </div>
                                <input type="hidden" name="agenda_ai_prompt" :value="partsAiPrompt" />
                                <input type="hidden" name="save_ai_prompt_default" :value="savePartsAiPromptDefault ? '1' : '0'" />
                                <div class="mt-3">
                                    <label class="flex items-start gap-3 cursor-pointer">
    <button type="button" role="switch" :aria-checked="saveAgendaImage ? 'true' : 'false'" @click="saveAgendaImage = ! saveAgendaImage; markTabDirty('section-agenda')"
        :class="['relative inline-flex h-6 w-11 flex-shrink-0 rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800', saveAgendaImage ? 'bg-[var(--brand-button-bg)]' : 'bg-gray-200 dark:bg-gray-700']">
        <span :class="['inline-block h-5 w-5 transform rounded-full bg-white shadow transition-transform mt-0.5', saveAgendaImage ? 'translate-x-5' : 'translate-x-0.5']"></span>
    </button>
    <span><span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('messages.keep_the_photo') }}</span><span class="block text-xs text-gray-500 dark:text-gray-400">{{ __('messages.keep_the_photo_help') }}</span></span>
</label>
                                </div>
                                <div v-if="agendaImageFullUrl" class="mt-3">
                                    <div class="relative inline-block">
                                        <img :src="agendaImageFullUrl" style="max-height:120px" class="rounded-lg border border-gray-200 dark:border-gray-600" />
                                        <button type="button" @click="deleteAgendaImage" title="{{ __('messages.remove') }}" aria-label="{{ __('messages.remove') }}"
                                            style="width: 20px; height: 20px; min-width: 20px; min-height: 20px;"
                                            class="absolute -top-2 -right-2 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                        @endif
                        <input type="hidden" name="save_agenda_image" :value="saveAgendaImage ? '1' : '0'" />
                        <input type="hidden" name="agenda_image_url" :value="agendaImageUrl" />
                    </div>
                </div>
                @if ($galleryShown)
                <button type="button" class="mobile-section-header" data-section="section-gallery" {!! $moreSectionAttrs !!}>
                    <span class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                        </svg>
                        <span class="section-nav-text">
                            <span>{{ __('messages.gallery') }}</span>
                            <span class="section-nav-summary" v-cloak :class="{ 'is-empty': tabSummaries['section-gallery'].empty }"><bdi v-text="tabSummaries['section-gallery'].text"></bdi></span>
                        </span>
                        <span class="section-nav-dot" v-cloak v-show="sectionDirty['section-gallery']"></span>
                        @if ($galleryMode === 'locked')
                        <x-lock-badge tier="pro" />
                        @endif
                    </span>
                    <svg class="w-5 h-5 text-gray-400 transition-transform duration-200 accordion-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div id="section-gallery" class="section-content lg:mt-0">
                    @include('partials.gallery-section', [
                        'galleryContext' => 'event',
                        'galleryMode' => $galleryMode,
                        'galleryRole' => $galleryRole,
                        'galleryCanUpgrade' => $galleryCanUpgrade,
                        'galleryImageCount' => count($galleryState['images']),
                        'galleryWrapperClass' => $tabCol,
                    ])
                </div>
                @endif

                @php
                    $schedules = $user->availableEventSchedules();
                    $schedules = $schedules->filter(function($schedule) use ($subdomain) {
                        return $schedule->subdomain !== $subdomain;
                    });
                @endphp

                @if ($detailsShown)
                <button type="button" class="mobile-section-header" data-section="section-listing" {!! $moreSectionAttrs !!}>
                    <span class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span class="section-nav-text">
                            <span>{{ __('messages.listing') }}</span>
                            <span class="section-nav-summary" v-cloak :class="{ 'is-empty': tabSummaries['section-listing'].empty }"><bdi v-text="tabSummaries['section-listing'].text"></bdi></span>
                        </span>
                        <span class="section-nav-dot" v-cloak v-show="sectionDirty['section-listing']"></span>
                    </span>
                    <svg class="w-5 h-5 text-gray-400 transition-transform duration-200 accordion-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                {{-- Where and how the event is shown: who can see it, its address, its category and
                     the other schedules it appears on. --}}
                <div id="section-listing" class="section-content lg:mt-0">
                    <div class="{{ $tabCol }}">
                        <h2 class="section-heading-name text-lg font-semibold text-gray-900 dark:text-gray-100 mb-5 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            {{ __('messages.listing') }}
                        </h2>

                        @if ($event->exists)
                        @php
                            // The link is under the page's title, with Copy and View. Here it is only what
                            // can be changed about it: its ending.
                            $cleanEventUrl = \App\Utils\UrlUtils::clean($eventEditUrl);
                            $slugEditable = $role->isCurator() || !($event->venue && $event->venue->isClaimed() && $event->role() && $event->role()->isClaimed());
                            $slugPrefix = ($event->slug && str_ends_with($cleanEventUrl, '/'.$event->slug)) ? substr($cleanEventUrl, 0, -strlen($event->slug)) : null;
                        @endphp
                        @if ($slugEditable)
                        <div class="mb-6">
                            <x-input-label for="event_slug" :value="__('messages.event_link')" />
                            {{-- Open on load when the ending was refused: its message is inside, and a disabled
                                 field would not be sent again. --}}
                            <div id="event-url-display" class="event-picked mt-1 {{ $errors->has('slug') ? 'hidden' : '' }}">
                                <span class="min-w-0 truncate text-sm text-gray-700 dark:text-gray-300" dir="ltr" v-pre>{{ $cleanEventUrl }}</span>
                                <button type="button" id="edit-slug-btn" class="event-link">{{ __('messages.edit') }}</button>
                            </div>
                            <div id="event-slug-edit" class="{{ $errors->has('slug') ? '' : 'hidden' }} mt-1">
                                <div class="event-slug-field">
                                    @if ($slugPrefix)<span class="event-slug-prefix" dir="ltr" v-pre>{{ $slugPrefix }}</span>@endif
                                    <x-text-input id="event_slug" name="slug" type="text" class="block w-full"
                                        :value="old('slug', $event->slug)" :disabled="! $errors->has('slug')" />
                                </div>
                                <x-input-error class="mt-2" :messages="$errors->get('slug')" />
                                <button type="button" id="cancel-slug-btn" class="{{ $errors->has('slug') ? '' : 'hidden' }} event-link event-link-quiet mt-2">{{ __('messages.cancel') }}</button>
                            </div>
                        </div>
                        @endif
                        @endif
                        {{-- Visibility selector (unifies Public / Draft / Internal / Unlisted).
                             One row of pills rather than four stacked cards: the choice costs a
                             line instead of half the first screen, and the description below
                             follows whichever option is hovered or focused so every state stays
                             readable without selecting it. --}}
                        @php
                            $visibilityOptions = [
                                ['value' => 'public',   'label' => __('messages.public'),   'desc' => __('messages.visibility_public_desc'),   'enterprise' => false],
                                ['value' => 'draft',    'label' => __('messages.draft'),    'desc' => __('messages.visibility_draft_desc'),    'enterprise' => false],
                                ['value' => 'internal', 'label' => __('messages.internal'), 'desc' => __('messages.visibility_internal_desc'), 'enterprise' => true],
                                ['value' => 'unlisted', 'label' => __('messages.unlisted'), 'desc' => __('messages.visibility_unlisted_desc'), 'enterprise' => true],
                            ];

                            // The segmented-control strings ($segShell and friends) are defined at the top of the file:
                            // the One-time / Recurring control on the Event tab uses them too, and renders first.
                            // Server mirror of the Vue getter, so the right pill is already pressed
                            // on first paint instead of only once Vue mounts.
                            $currentVisibility = $visibilityNow;
                        @endphp
                        <fieldset class="mb-6">
                            <legend class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{ __('messages.visibility') }}</legend>
                            <input type="hidden" name="is_draft" :value="event.is_draft ? 1 : 0">
                            <input type="hidden" name="is_private" :value="event.is_private ? 1 : 0">
                            <input type="hidden" name="is_internal" :value="event.is_internal ? 1 : 0">

                            {{-- The reset lives on the container, not on each pill: leaving one pill for the
                                 next must not depend on mouseleave/mouseenter firing in a particular order. --}}
                            <div class="{{ $segShell }} mt-1" @mouseleave="hoveredVisibility = null">
                                @foreach ($visibilityOptions as $opt)
                                    @if (! $opt['enterprise'] || $role->isEnterprise())
                                        <label @mouseenter="hoveredVisibility = '{{ $opt['value'] }}'">
                                            <input type="radio" name="visibility_ui" value="{{ $opt['value'] }}" class="sr-only peer"
                                                v-model="visibility" {{ $currentVisibility === $opt['value'] ? 'checked' : '' }}
                                                @focus="hoveredVisibility = '{{ $opt['value'] }}'" @blur="hoveredVisibility = null">
                                            <span class="{{ $segRadio }}">{{ $opt['label'] }}</span>
                                        </label>
                                    @elseif (! $isFirstEventRun)
                                        {{-- Locked: a button rather than a disabled radio, so the click still
                                             reaches the upgrade modal, and it stays out of the radio group.
                                             Not offered on a first event, where it is only an upsell in the
                                             way of the two choices that matter. --}}
                                        <button type="button" class="{{ $segLocked }}"
                                            title="{{ $opt['label'] }} ({{ __('messages.enterprise') }})"
                                            aria-label="{{ $opt['label'] }} ({{ __('messages.enterprise') }})"
                                            @click="openUpgrade('upgrade-privacy')"
                                            @mouseenter="hoveredVisibility = '{{ $opt['value'] }}'"
                                            @focus="hoveredVisibility = '{{ $opt['value'] }}'" @blur="hoveredVisibility = null">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5 flex-shrink-0">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                                            </svg>
                                            {{ $opt['label'] }}
                                        </button>
                                    @endif
                                @endforeach
                            </div>

                            {{-- One line, right under the choices: what the chosen one means, or the one under
                                 the pointer or the keyboard while choosing. A list of all four below the
                                 choices was tried in 2026-10 and taken back: it parted each choice from its
                                 own text. Deliberately not an aria-live region: this text follows hover as
                                 well as selection, and announcing on unintended pointer movement is worse
                                 than silence. The radio announces the selection itself. --}}
                            <div class="mt-2 min-h-[1.25rem]" id="visibility-desc">
                                @foreach ($visibilityOptions as $opt)
                                    <p class="text-xs text-gray-500 dark:text-gray-400"
                                        @if ($currentVisibility !== $opt['value']) v-cloak @endif
                                        v-show="(hoveredVisibility || visibility) === '{{ $opt['value'] }}'">{{ $opt['desc'] }}</p>
                                @endforeach
                            </div>

                            @if ($role->isEnterprise())
                            {{-- Password applies only to Unlisted events --}}
                            <div class="mt-4" v-show="visibility === 'unlisted'">
                                <x-input-label for="event_password" :value="__('messages.event_password')" />
                                <x-text-input id="event_password" name="event_password" type="text" class="mt-1 block w-full"
                                    v-model="event.event_password" maxlength="255" />
                                <x-input-error class="mt-2" :messages="$errors->get('event_password')" />
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.event_password_help') }}</p>
                            </div>
                            @endif

                            {{-- Warn before an already-hidden event is made public --}}
                            <div class="mt-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3 flex items-start gap-2"
                                v-show="initiallyHidden && visibility === 'public'">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                                </svg>
                                <span class="text-sm text-amber-800 dark:text-amber-300">{{ __('messages.visibility_publish_warning') }}</span>
                            </div>
                        </fieldset>

                        <div class="mb-6">
                            <x-input-label for="category_id" :value="__('messages.category')" />
                            <select id="category_id" name="category_id" data-searchable class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm {{ rtl_class($role, 'rtl', '', true) }}">
                                <option value="">{{ __('messages.please_select') }}</option>
                                @foreach($event_categories as $id => $label)
                                    <option v-pre value="{{ $id }}" {{ old('category_id', $event->category_id) == $id ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('category_id')" />
                        </div>

                        @php
                            // The event's own venue and participants are on it because of the Event and
                            // Participants tabs, and those decide it (EventRepo::saveEvent() exempts what
                            // they post from this list's detach). They were offered here too, as ticked
                            // boxes: unticking the venue took it off the event while the Event tab still
                            // showed it picked. So they are named in a line, and not offered.
                            $ownPlaces = $event->exists ? $event->roles->filter(fn ($r) => ($r->isVenue() || $r->isTalent()) && $r->id !== $role->id) : collect();
                            $ownPlaceIds = $ownPlaces->pluck('id')->all();
                            $ownPlaceNames = $ownPlaces->filter(fn ($r) => $r->pivot->is_accepted)->pluck('name')->all();
                            $listSchedules = $schedules->reject(fn ($s) => in_array($s->id, $ownPlaceIds));
                            $scheduleGroups = array_filter([
                                ['label' => null, 'items' => $listSchedules->filter(fn ($s) => ($s->pivot->level ?? null) !== 'follower')],
                                ['label' => __('messages.schedules_you_follow'), 'items' => $listSchedules->filter(fn ($s) => ($s->pivot->level ?? null) === 'follower')],
                            ], fn ($g) => $g['items']->count() > 0);
                        @endphp
                        @if ($listSchedules->count() > 0)
                        <x-input-label :value="__('messages.also_list_on')" />
                        {{-- v-pre: schedule names, some of them other people's, inside the Vue mount. --}}
                        <p class="event-hint">{{ __('messages.also_list_on_help') }}@if ($ownPlaceNames) <span v-pre>{{ __('messages.already_listed_through', ['names' => implode(', ', $ownPlaceNames)]) }}</span>@endif</p>
                        <div>
                            {{-- Marks this section as rendered, so EventRepo can tell an unticked box
                                 from a save that never showed the section (API, importers, calendar
                                 sync). Without it an auto-sourced curator could not be removed here. --}}
                            <input type="hidden" name="curators_submitted" value="1">

                            @foreach ($scheduleGroups as $scheduleGroup)
                            @if ($scheduleGroup['label'])<p class="event-group-label mt-4">{{ $scheduleGroup['label'] }}</p>@endif
                            <div class="event-check-grid">
                            @foreach($scheduleGroup['items'] as $schedule)
                            @php
                                $isClonedSchedule = isset($clonedCurators) && $clonedCurators->contains(function($c) use ($schedule) { return $c->id == $schedule->id; });
                                // $event->curators has no is_accepted filter, so a row the
                                // schedule declined would otherwise render ticked - claiming the
                                // event is on a schedule it was deliberately removed from.
                                $attachedRow = $event->exists ? $event->curators->firstWhere('id', $schedule->id) : null;
                                $isAttachedAndNotDeclined = $attachedRow && $attachedRow->pivot->is_accepted !== 0 && $attachedRow->pivot->is_accepted !== false;
                                $isScheduleChecked = (! $event->exists && ($role->subdomain == $schedule->subdomain || session('pending_request') == $schedule->subdomain)) || $isAttachedAndNotDeclined || $isClonedSchedule;
                                if ($curatorsFromOld !== null) {
                                    $isScheduleChecked = in_array((string) $schedule->encodeId(), $curatorsFromOld, true);
                                }
                            @endphp
                            <div>
                                <div class="flex items-center h-6">
                                    <input type="checkbox"
                                           id="curator_{{ $schedule->encodeId() }}"
                                           name="curators[]"
                                           value="{{ $schedule->encodeId() }}"
                                           {{ $isScheduleChecked ? 'checked' : '' }}
                                           class="h-4 w-4 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)] border-gray-300 rounded"
                                           @change="toggleCuratorGroupSelection('{{ $schedule->encodeId() }}')">
                                    <label for="curator_{{ $schedule->encodeId() }}" class="ms-2 block text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{-- Inside the #app Vue mount, and $schedules includes schedules
                                             owned by other people (followed + accept_requests), so this
                                             name would otherwise be compiled as a Vue template. --}}
                                        <x-user-text>{{ $schedule->name }}</x-user-text>
                                    </label>
                                    {{-- Only where it is true: a schedule you belong to, or one that takes
                                         events without review, lists it at once. --}}
                                    @if (! $schedule->autoAcceptsEventFrom($user, $role))
                                    <span class="event-list-sub ms-2 flex-shrink-0">{{ __('messages.needs_approval') }}</span>
                                    @endif
                                    <div class="ms-2 flex-shrink-0">
                                        @if($schedule->accept_requests && $schedule->request_terms)
                                        <div class="relative group">
                                            <button type="button" class="text-[var(--brand-blue)] hover:text-[var(--brand-blue-dark)] focus:outline-none">
                                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                            <div class="absolute bottom-full start-1/2 transform -translate-x-1/2 mb-2 px-4 py-3 bg-gray-900 dark:bg-gray-700 text-white text-sm rounded-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 w-[28rem] max-w-lg z-10">
                                                {{-- v-pre rather than <x-user-text>: this emits <br> tags, so
                                                 the slot would double-escape them. --}}
                                                <div v-pre class="leading-relaxed"
                                                     dir="{{ is_rtl() ? 'rtl' : 'ltr' }}"
                                                     style="{{ is_rtl() ? 'text-align: right;' : 'text-align: left;' }}">{!! nl2br(e($schedule->translatedRequestTerms())) !!}</div>
                                                <div class="absolute top-full start-1/2 transform -translate-x-1/2 w-0 h-0 border-s-4 border-e-4 border-t-4 border-transparent border-t-gray-900 dark:border-t-gray-700"></div>
                                            </div>
                                        </div>
                                        @else
                                        <div class="w-4 h-4"></div>
                                        @endif
                                    </div>
                                </div>

                                @if($schedule->groups && count($schedule->groups) > 0)
                                <div id="curator_group_{{ $schedule->encodeId() }}" class="ms-6 mb-2" style="display: {{ $isScheduleChecked ? 'block' : 'none' }};">
                                    <select id="curator_group_{{ $schedule->encodeId() }}"
                                            name="curator_groups[{{ $schedule->encodeId() }}]"
                                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                                        <option value="">{{ __('messages.please_select') }}</option>
                                        @foreach($schedule->groups as $group)
                                            @php
                                                $selectedGroupId = null;
                                                if ($event->exists) {
                                                    $selectedGroupId = $event->getGroupIdForSubdomain($schedule->subdomain);
                                                    if ($selectedGroupId) {
                                                        $selectedGroupId = \App\Utils\UrlUtils::encodeId($selectedGroupId);
                                                    }
                                                } elseif (isset($clonedCuratorGroups) && isset($clonedCuratorGroups[$schedule->encodeId()])) {
                                                    $selectedGroupId = $clonedCuratorGroups[$schedule->encodeId()];
                                                }
                                            @endphp
                                            {{-- v-pre: another user's sub-schedule name, inside the Vue mount. --}}
                                            <option v-pre value="{{ \App\Utils\UrlUtils::encodeId($group->id) }}" {{ old('curator_groups.' . $schedule->encodeId(), $selectedGroupId) == \App\Utils\UrlUtils::encodeId($group->id) ? 'selected' : '' }}>{{ $group->translatedName() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif
                            </div>
                            @endforeach
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                @if ($showGoogleSync || $showMicrosoftSync)
                <button type="button" class="mobile-section-header" data-section="section-calendar-sync">
                    <span class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                        </svg>
                        <span class="section-nav-text">
                            <span>{{ __('messages.calendar_sync') }}</span>
                            <span class="section-nav-summary" v-cloak :class="{ 'is-empty': tabSummaries['section-calendar-sync'].empty }"><bdi v-text="tabSummaries['section-calendar-sync'].text"></bdi></span>
                        </span>
                        <span class="section-nav-dot" v-cloak v-show="sectionDirty['section-calendar-sync']"></span>
                    </span>
                    <svg class="w-5 h-5 text-gray-400 transition-transform duration-200 accordion-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div id="section-calendar-sync" class="section-content lg:mt-0">
                    <div class="{{ $tabCol }}">
                        <div class="event-tab-title">
                            <h2 class="section-heading-name text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                            </svg>
                                {{ __('messages.calendar_sync') }}
                            </h2>
                        </div>
                        <p class="event-empty">{{ __('messages.calendar_sync_help') }}</p>
                        <div class="event-list">
                            @if ($showGoogleSync)
                            <div class="event-list-row is-centered">
                                <div class="min-w-0 flex-1">
                                    <div class="event-list-name">{{ __('messages.google_calendar_sync') }}</div>
                                    @if ($event->isSyncedToGoogleCalendarForSubdomain(request()->subdomain))
                                    <span class="event-status is-on">{{ __('messages.synced') }}</span>
                                    @else
                                    <span class="event-status">{{ __('messages.not_synced') }}</span>
                                    {{-- Why, since the line above the list says a published event is copied on every
                                         save: members only (Draft and Internal both set is_draft), or simply not
                                         saved since the calendar was connected. --}}
                                    <p class="event-list-sub">{{ $event->is_draft ? __('messages.calendar_not_synced_draft') : __('messages.calendar_not_synced_next_save') }}</p>
                                    @endif
                                    <span id="sync-status-{{ $event->id }}" class="hidden event-list-sub ms-2">{{ __('messages.syncing') }}</span>
                                    <p id="sync-error-{{ $event->id }}" role="alert" class="hidden mt-1 text-sm text-red-600 dark:text-red-400"></p>
                                </div>
                                <div class="event-list-actions">
                                    @if ($event->isSyncedToGoogleCalendarForSubdomain(request()->subdomain))
                                    <button type="button" id="unsync-event-btn" class="event-link is-danger" data-subdomain="{{ $subdomain }}" data-event-id="{{ $event->id }}">{{ __('messages.remove') }}</button>
                                    @else
                                    <x-secondary-button type="button" id="sync-event-btn" data-subdomain="{{ $subdomain }}" data-event-id="{{ $event->id }}">{{ __('messages.sync_now') }}</x-secondary-button>
                                    @endif
                                </div>
                            </div>
                            @endif
                            @if ($showMicrosoftSync)
                            <div class="event-list-row is-centered">
                                <div class="min-w-0 flex-1">
                                    <div class="event-list-name">{{ __('messages.microsoft_calendar_sync') }}</div>
                                    @if ($event->isSyncedToMicrosoftCalendarForSubdomain(request()->subdomain))
                                    <span class="event-status is-on">{{ __('messages.synced') }}</span>
                                    @else
                                    <span class="event-status">{{ __('messages.not_synced') }}</span>
                                    {{-- Why, since the line above the list says a published event is copied on every
                                         save: members only (Draft and Internal both set is_draft), or simply not
                                         saved since the calendar was connected. --}}
                                    <p class="event-list-sub">{{ $event->is_draft ? __('messages.calendar_not_synced_draft') : __('messages.calendar_not_synced_next_save') }}</p>
                                    @endif
                                    <span id="microsoft-sync-status-{{ $event->id }}" class="hidden event-list-sub ms-2">{{ __('messages.syncing') }}</span>
                                    <p id="microsoft-sync-error-{{ $event->id }}" role="alert" class="hidden mt-1 text-sm text-red-600 dark:text-red-400"></p>
                                </div>
                                <div class="event-list-actions">
                                    @if ($event->isSyncedToMicrosoftCalendarForSubdomain(request()->subdomain))
                                    <button type="button" id="microsoft-unsync-event-btn" class="event-link is-danger" data-subdomain="{{ $subdomain }}" data-event-id="{{ $event->id }}">{{ __('messages.remove') }}</button>
                                    @else
                                    <x-secondary-button type="button" id="microsoft-sync-event-btn" data-subdomain="{{ $subdomain }}" data-event-id="{{ $event->id }}">{{ __('messages.sync_now') }}</x-secondary-button>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif

            <button type="button" class="mobile-section-header" data-section="section-engagement" {!! $moreSectionAttrs !!}>
                <span class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                    </svg>
                    <span class="section-nav-text">
                        <span>{{ __('messages.engagement') }}</span>
                        <span class="section-nav-summary" v-cloak :class="{ 'is-empty': tabSummaries['section-engagement'].empty }"><bdi v-text="tabSummaries['section-engagement'].text"></bdi></span>
                    </span>
                    <span class="section-nav-dot" v-cloak v-show="sectionDirty['section-engagement']"></span>
                </span>
                <svg class="w-5 h-5 text-gray-400 transition-transform duration-200 accordion-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div id="section-engagement" class="section-content lg:mt-0">
                <div class="{{ $tabCol }}">
                    <h2 class="section-heading-name text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                        </svg>
                        {{ __('messages.engagement') }}
                    </h2>

                    @php
                        // What "Same as schedule" means right now: the schedule the event takes its settings from.
                        $settingRole = $event->exists ? ($event->roles->first(fn ($r) => $r->isTalent()) ?? $event->roles->first() ?? $role) : $role;
                        $settingDefaults = ['fan_comments_enabled' => true, 'fan_photos_enabled' => true, 'fan_videos_enabled' => true, 'feedback_enabled' => false];
                    @endphp
                    <div class="event-subrows">
                    <button type="button" class="event-subrow engagement-tab" data-tab="polls" :aria-expanded="activeEngagementTab === 'polls' ? 'true' : 'false'"
                        @click="activeEngagementTab = activeEngagementTab === 'polls' ? '' : 'polls'">
                        <span class="event-row-title">{{ __('messages.polls') }}</span>
                        <span class="event-row-summary" v-cloak v-text="engagementRows.polls"></span>
                        <span v-cloak v-if="polls.some(p => p.pending_options && p.pending_options.length > 0)" class="event-badge-count">@{{ polls.reduce((sum, p) => sum + (p.pending_options ? p.pending_options.length : 0), 0) }}</span>@if (! $role->isPro())<x-lock-badge tier="pro" />@endif
                        <svg class="event-row-chevron" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                    </button>
                    <div v-show="activeEngagementTab === 'polls'" class="event-subrow-body" data-engagement-pane="polls">
                    @if ($role->isPro())
                        {{-- Poll message/error --}}
                        <div v-if="pollMessage" class="mb-4 p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-sm text-green-700 dark:text-green-400">
                            @{{ pollMessage }}
                        </div>
                        <div v-if="pollError" class="mb-4 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-sm text-red-700 dark:text-red-400">
                            @{{ pollError }}
                        </div>

                        {{-- Existing Polls --}}
                        <div v-if="polls.length > 0" class="space-y-4 mb-4">
                            <div v-for="(poll, pollIndex) in polls" :key="poll.hash || ('new-' + pollIndex)" class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                {{-- Question input --}}
                                <div class="flex items-start justify-between gap-3 mb-3">
                                    <div class="flex-1">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('messages.poll_question') }}</label>
                                        <input type="text" v-model="poll.question" maxlength="500" @keydown.enter.prevent class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] sm:text-sm" :placeholder="'{{ __('messages.poll_question') }}'">
                                    </div>
                                    <span v-if="poll.hash" class="shrink-0 mt-6 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                                          :class="poll.is_active ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-400'">
                                        @{{ poll.is_active ? pollLabelActive : pollLabelClosed }}
                                    </span>
                                </div>

                                {{-- Options: read-only with vote results when has votes --}}
                                <template v-if="poll.votes_count > 0">
                                    <div v-for="(option, idx) in poll.options" :key="idx" class="mb-2">
                                        <div class="flex justify-between text-sm mb-1">
                                            <span class="text-gray-700 dark:text-gray-300">@{{ option }}</span>
                                            <span class="text-gray-500 dark:text-gray-400 text-xs tabular-nums">@{{ pollOptionCount(poll, idx) }} (@{{ pollOptionPct(poll, idx) }}%)</span>
                                        </div>
                                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                            <div class="h-2 rounded-full" :style="pollBarStyle(poll, idx)"></div>
                                        </div>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 mb-3">
                                        @{{ poll.votes_count }} {{ __('messages.votes') }}
                                    </p>
                                    <p class="event-hint">{{ __('messages.cannot_edit_poll_with_votes') }}</p>
                                </template>

                                {{-- Options: editable when no votes --}}
                                <template v-else>
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('messages.poll_options') }}</label>
                                        <div v-for="(option, idx) in poll.options" :key="idx" class="flex items-center gap-2 mb-2">
                                            <input type="text" v-model="poll.options[idx]" maxlength="200" @keydown.enter.prevent class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] sm:text-sm" :placeholder="'{{ __('messages.option_placeholder') }} ' + (idx + 1)">
                                            <button v-if="poll.options.length > 2" type="button" @click="poll.options.splice(idx, 1); markTabDirty('section-engagement')" class="event-icon-btn is-remove flex-shrink-0" title="{{ __('messages.remove') }}" aria-label="{{ __('messages.remove') }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                                            </button>
                                        </div>
                                        <div class="flex items-center justify-between mt-1">
                                            <button v-if="poll.options.length < 10" type="button" @click="poll.options.push(''); markTabDirty('section-engagement')" class="text-sm text-[var(--brand-blue)] hover:text-[var(--brand-blue-dark)] flex items-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                                {{ __('messages.add_option') }}
                                            </button>
                                            <span v-else></span>
                                            <button v-if="!poll.hash" type="button" @click="polls.splice(pollIndex, 1); markTabDirty('section-engagement')" class="text-sm text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300">
                                                {{ __('messages.remove') }}
                                            </button>
                                        </div>
                                    </div>
                                </template>

                                {{-- User options toggles --}}
                                <div class="mb-3 space-y-2">
                                    <div class="flex items-center gap-3">
                                        <label class="relative w-11 h-6 cursor-pointer flex-shrink-0">
                                            <input type="checkbox" :checked="poll.allow_user_options"
                                                @change="poll.allow_user_options = $event.target.checked; if (!$event.target.checked) poll.require_option_approval = false"
                                                class="sr-only peer">
                                            <div class="w-11 h-6 bg-gray-300 dark:bg-gray-600 rounded-full peer-checked:bg-[var(--brand-button-bg)] transition-colors"></div>
                                            <div class="absolute top-0.5 ltr:left-0.5 rtl:right-0.5 w-5 h-5 bg-white rounded-full shadow-md transition-transform duration-200 peer-checked:ltr:translate-x-5 peer-checked:rtl:-translate-x-5"></div>
                                        </label>
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('messages.allow_user_options') }}</span>
                                    </div>
                                    <div v-if="poll.allow_user_options" class="flex items-center gap-3">
                                        <label class="relative w-11 h-6 cursor-pointer flex-shrink-0">
                                            <input type="checkbox" :checked="poll.require_option_approval"
                                                @change="poll.require_option_approval = $event.target.checked"
                                                class="sr-only peer">
                                            <div class="w-11 h-6 bg-gray-300 dark:bg-gray-600 rounded-full peer-checked:bg-[var(--brand-button-bg)] transition-colors"></div>
                                            <div class="absolute top-0.5 ltr:left-0.5 rtl:right-0.5 w-5 h-5 bg-white rounded-full shadow-md transition-transform duration-200 peer-checked:ltr:translate-x-5 peer-checked:rtl:-translate-x-5"></div>
                                        </label>
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('messages.require_option_approval') }}</span>
                                    </div>
                                </div>

                                {{-- Pending options --}}
                                <template v-if="poll.hash && poll.pending_options && poll.pending_options.length > 0">
                                    <div class="mb-3">
                                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            {{ __('messages.pending_options') }}
                                            <span class="inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-red-500 rounded-full ms-1">@{{ poll.pending_options.length }}</span>
                                        </p>
                                        <div v-for="(pending, pendingIdx) in poll.pending_options" :key="pendingIdx" class="flex items-center justify-between gap-2 mb-2 px-3 py-2 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700">
                                            <span class="text-sm text-gray-800 dark:text-gray-200">@{{ pending.label }}</span>
                                            <div class="flex items-center gap-1 shrink-0">
                                                <button type="button" @click="approvePollOption(poll, pendingIdx)" :disabled="pollSubmitting"
                                                        class="text-xs px-2 py-1 rounded bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 hover:bg-green-200 dark:hover:bg-green-900/50 disabled:opacity-50">
                                                    {{ __('messages.approve') }}
                                                </button>
                                                <button type="button" @click="rejectPollOption(poll, pendingIdx)" :disabled="pollSubmitting"
                                                        class="text-xs px-2 py-1 rounded bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-900/50 disabled:opacity-50">
                                                    {{ __('messages.reject') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                {{-- Actions --}}
                                <div v-if="poll.hash" class="flex items-center gap-4">
                                    <button type="button" @click="togglePoll(poll)" :disabled="pollSubmitting" class="event-link disabled:opacity-50">
                                        @{{ poll.is_active ? pollLabelClose : pollLabelReopen }}
                                    </button>
                                    <button type="button" @click="deletePoll(poll, pollIndex)" :disabled="pollSubmitting" class="event-link is-danger disabled:opacity-50">
                                        {{ __('messages.delete') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-gray-500 dark:text-gray-400 mb-4">{{ __('messages.no_polls') }}</p>

                        {{-- Hidden inputs for form submission --}}
                        <template v-for="(poll, pIdx) in polls" :key="'poll-input-' + pIdx">
                            <input type="hidden" :name="'polls[' + pIdx + '][hash]'" :value="poll.hash || ''">
                            <input type="hidden" :name="'polls[' + pIdx + '][question]'" :value="poll.question">
                            <input type="hidden" :name="'polls[' + pIdx + '][options]'" :value="JSON.stringify(poll.options)">
                            <input type="hidden" :name="'polls[' + pIdx + '][allow_user_options]'" :value="poll.allow_user_options ? '1' : '0'">
                            <input type="hidden" :name="'polls[' + pIdx + '][require_option_approval]'" :value="poll.require_option_approval ? '1' : '0'">
                        </template>

                        {{-- Add Poll Link --}}
                        <button type="button" @click="polls.push({hash: null, question: '', options: ['', ''], is_active: true, allow_user_options: false, require_option_approval: false, pending_options: [], votes_count: 0, results: []}); markTabDirty('section-engagement')" v-if="polls.length < 5"
                                class="text-sm text-[var(--brand-blue)] hover:text-[var(--brand-blue-dark)] flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            {{ __('messages.add_poll') }}
                        </button>
                    @else
                        <x-upgrade-prompt tier="pro" :learnMoreUrl="marketing_url('/features/polls')" :subdomain="$subdomain">
                            <x-slot:icon>
                                <svg class="h-7 w-7 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                                </svg>
                            </x-slot:icon>
                            {{ __('messages.polls_pro_only') }}
                        </x-upgrade-prompt>
                    @endif
                    </div>

                    <button type="button" class="event-subrow engagement-tab" data-tab="fan_content" :aria-expanded="activeEngagementTab === 'fan_content' ? 'true' : 'false'"
                        @click="activeEngagementTab = activeEngagementTab === 'fan_content' ? '' : 'fan_content'">
                        <span class="event-row-title">{{ __('messages.fan_content') }}</span>
                        <span class="event-row-summary" v-cloak v-text="engagementRows.fan_content"></span>
                        <span v-cloak v-if="fanContentPending > 0" class="event-badge-count" v-text="fanContentPending"></span>
                        <svg class="event-row-chevron" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                    </button>
                    <div v-show="activeEngagementTab === 'fan_content'" class="event-subrow-body" data-engagement-pane="fan_content">
                    @if ($role->isPro())

                        <div class="event-setting">
                            <span class="event-setting-label" id="fan_comments_enabled_label">{{ __('messages.fan_comments_enabled') }}<span class="event-tab-aside block font-normal">{{ __('messages.schedule') }}: {{ mb_strtolower($engagementInherited['fan_comments_enabled'] ? __('messages.enabled') : __('messages.disabled')) }}</span></span>
                            <div class="{{ $segShell }} event-seg" role="radiogroup" aria-labelledby="fan_comments_enabled_label">
                                {{-- The first choice says what the schedule's own setting is. --}}
                                @foreach (['' => __('messages.same_as_schedule'), '1' => __('messages.enabled'), '0' => __('messages.disabled')] as $settingValue => $settingLabel)
                                <label>
                                    <input type="radio" class="sr-only peer" name="fan_comments_enabled" value="{{ $settingValue }}" v-model="engagementSettings.fan_comments_enabled" {{ (string) $settingValue === $engagementSettingsNow['fan_comments_enabled'] ? 'checked' : '' }}>
                                    <span class="{{ $segRadio }}">{{ $settingLabel }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="event-setting">
                            <span class="event-setting-label" id="fan_photos_enabled_label">{{ __('messages.fan_photos_enabled') }}<span class="event-tab-aside block font-normal">{{ __('messages.schedule') }}: {{ mb_strtolower($engagementInherited['fan_photos_enabled'] ? __('messages.enabled') : __('messages.disabled')) }}</span></span>
                            <div class="{{ $segShell }} event-seg" role="radiogroup" aria-labelledby="fan_photos_enabled_label">
                                {{-- The first choice says what the schedule's own setting is. --}}
                                @foreach (['' => __('messages.same_as_schedule'), '1' => __('messages.enabled'), '0' => __('messages.disabled')] as $settingValue => $settingLabel)
                                <label>
                                    <input type="radio" class="sr-only peer" name="fan_photos_enabled" value="{{ $settingValue }}" v-model="engagementSettings.fan_photos_enabled" {{ (string) $settingValue === $engagementSettingsNow['fan_photos_enabled'] ? 'checked' : '' }}>
                                    <span class="{{ $segRadio }}">{{ $settingLabel }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="event-setting">
                            <span class="event-setting-label" id="fan_videos_enabled_label">{{ __('messages.fan_videos_enabled') }}<span class="event-tab-aside block font-normal">{{ __('messages.schedule') }}: {{ mb_strtolower($engagementInherited['fan_videos_enabled'] ? __('messages.enabled') : __('messages.disabled')) }}</span></span>
                            <div class="{{ $segShell }} event-seg" role="radiogroup" aria-labelledby="fan_videos_enabled_label">
                                {{-- The first choice says what the schedule's own setting is. --}}
                                @foreach (['' => __('messages.same_as_schedule'), '1' => __('messages.enabled'), '0' => __('messages.disabled')] as $settingValue => $settingLabel)
                                <label>
                                    <input type="radio" class="sr-only peer" name="fan_videos_enabled" value="{{ $settingValue }}" v-model="engagementSettings.fan_videos_enabled" {{ (string) $settingValue === $engagementSettingsNow['fan_videos_enabled'] ? 'checked' : '' }}>
                                    <span class="{{ $segRadio }}">{{ $settingLabel }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    @if ($event->exists)
                        @if ($pendingVideos->count() == 0 && $pendingComments->count() == 0 && $pendingPhotos->count() == 0 && $approvedVideos->count() == 0 && $approvedComments->count() == 0 && $approvedPhotos->count() == 0)
                        <p class="event-group-label mt-4">{{ __('messages.from_guests') }}</p><p class="event-empty">{{ __('messages.no_fan_content') }}</p>
                        @else

                        {{-- Pending Section --}}
                        @if ($pendingVideos->count() > 0 || $pendingComments->count() > 0 || $pendingPhotos->count() > 0)
                        <div class="mb-8">
                            <h3 class="text-md font-semibold text-gray-800 dark:text-gray-200 mb-4">{{ __('messages.pending_approval') }}</h3>
                            <div class="space-y-4">
                                @foreach ($pendingVideos as $video)
                                <div class="flex flex-col sm:flex-row sm:items-center gap-3 p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800">
                                    <div class="flex-1">
                                        <div class="rounded overflow-hidden mb-2">
                                            {{-- A thumbnail from this install that opens the video on YouTube, not an
                                                 embed: nothing is requested from Google until someone chooses to watch
                                                 (privacy policy, clause 10). The link is rebuilt from the video id, never
                                                 the submitted URL. --}}
                                            @php
                                                $moderationVideoId = \App\Utils\UrlUtils::extractYouTubeVideoId($video->youtube_url);
                                            @endphp
                                            @if (is_string($moderationVideoId) && preg_match('/^[A-Za-z0-9_-]{11}$/', $moderationVideoId))
                                            <a href="https://www.youtube.com/watch?v={{ $moderationVideoId }}" target="_blank" rel="noopener noreferrer" class="group relative block" aria-label="{{ __('messages.consent_embed_video_button') }}">
                                                <img src="{{ \App\Utils\UrlUtils::getYouTubeThumbnail($video->youtube_url) }}" alt="" loading="lazy" class="w-full object-cover" style="aspect-ratio:16/9">
                                                <span class="absolute inset-0 flex items-center justify-center bg-black/30 transition-all duration-200 group-hover:bg-black/45" aria-hidden="true">
                                                    <svg class="h-10 w-10 text-white" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z" /></svg>
                                                </span>
                                            </a>
                                            @endif
                                        </div>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            <span v-pre>{{ $video->eventPart ? $video->eventPart->name : __('messages.general') }}</span>
                                            @if ($video->event_date)
                                            &middot; {{ \Carbon\Carbon::parse($video->event_date)->format('M j, Y') }}
                                            @endif
                                            &middot; {{ __('messages.submitted_by') }} <span v-pre>{{ $video->submitterName() }}</span>@if ($video->isGuestSubmission() && $video->submitterEmail()) <span v-pre class="text-gray-500 dark:text-gray-500">({{ $video->submitterEmail() }})</span>@endif
                                        </p>
                                    </div>
                                    <div class="flex gap-2 shrink-0">
                                        <button type="submit" form="form-approve-video-{{ $video->id }}" class="px-3 py-1.5 text-sm bg-green-600 text-white rounded hover:bg-green-700">{{ __('messages.approve') }}</button>
                                        <button type="submit" form="form-reject-video-{{ $video->id }}" class="px-3 py-1.5 text-sm bg-red-600 text-white rounded hover:bg-red-700">{{ __('messages.reject') }}</button>
                                    </div>
                                </div>
                                @endforeach

                                @foreach ($pendingComments as $comment)
                                <div class="flex flex-col sm:flex-row sm:items-center gap-3 p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800">
                                    <div class="flex-1">
                                        <p v-pre class="text-gray-800 dark:text-gray-200">{{ $comment->comment }}</p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                            <span v-pre>{{ $comment->eventPart ? $comment->eventPart->name : __('messages.general') }}</span>
                                            @if ($comment->event_date)
                                            &middot; {{ \Carbon\Carbon::parse($comment->event_date)->format('M j, Y') }}
                                            @endif
                                            &middot; {{ __('messages.submitted_by') }} <span v-pre>{{ $comment->submitterName() }}</span>@if ($comment->isGuestSubmission() && $comment->submitterEmail()) <span v-pre class="text-gray-500 dark:text-gray-500">({{ $comment->submitterEmail() }})</span>@endif
                                        </p>
                                    </div>
                                    <div class="flex gap-2 shrink-0">
                                        <button type="submit" form="form-approve-comment-{{ $comment->id }}" class="px-3 py-1.5 text-sm bg-green-600 text-white rounded hover:bg-green-700">{{ __('messages.approve') }}</button>
                                        <button type="submit" form="form-reject-comment-{{ $comment->id }}" class="px-3 py-1.5 text-sm bg-red-600 text-white rounded hover:bg-red-700">{{ __('messages.reject') }}</button>
                                    </div>
                                </div>
                                @endforeach

                                @foreach ($pendingPhotos as $photo)
                                <div class="flex flex-col sm:flex-row sm:items-center gap-3 p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800">
                                    <div class="flex-1">
                                        <img src="{{ $photo->photo_url }}" alt="" class="h-32 w-auto rounded object-cover mb-2">
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            <span v-pre>{{ $photo->eventPart ? $photo->eventPart->name : __('messages.general') }}</span>
                                            @if ($photo->event_date)
                                            &middot; {{ \Carbon\Carbon::parse($photo->event_date)->format('M j, Y') }}
                                            @endif
                                            &middot; {{ __('messages.submitted_by') }} <span v-pre>{{ $photo->submitterName() }}</span>@if ($photo->isGuestSubmission() && $photo->submitterEmail()) <span v-pre class="text-gray-500 dark:text-gray-500">({{ $photo->submitterEmail() }})</span>@endif
                                        </p>
                                    </div>
                                    <div class="flex gap-2 shrink-0">
                                        <button type="submit" form="form-approve-photo-{{ $photo->id }}" class="px-3 py-1.5 text-sm bg-green-600 text-white rounded hover:bg-green-700">{{ __('messages.approve') }}</button>
                                        <button type="submit" form="form-reject-photo-{{ $photo->id }}" class="px-3 py-1.5 text-sm bg-red-600 text-white rounded hover:bg-red-700">{{ __('messages.reject') }}</button>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        {{-- Approved Section --}}
                        @if ($approvedVideos->count() > 0 || $approvedComments->count() > 0 || $approvedPhotos->count() > 0)
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-md font-semibold text-gray-800 dark:text-gray-200">{{ __('messages.approved_content') }}</h3>
                                @if ($role->isPro() && $approvedPhotos->count() > 0)
                                <a href="{{ route('event.download_photos', ['subdomain' => $role->subdomain, 'event_hash' => $event->hashedId()]) }}" class="inline-flex items-center gap-1.5 text-sm text-[var(--brand-blue)] hover:underline font-medium">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                                    {{ __('messages.download_all_photos') }}
                                </a>
                                @endif
                            </div>
                            <div class="space-y-4">
                                @foreach ($approvedVideos as $video)
                                <div class="flex flex-col sm:flex-row sm:items-center gap-3 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <div class="flex-1">
                                        <div class="rounded overflow-hidden mb-2">
                                            {{-- A thumbnail from this install that opens the video on YouTube, not an
                                                 embed: nothing is requested from Google until someone chooses to watch
                                                 (privacy policy, clause 10). The link is rebuilt from the video id, never
                                                 the submitted URL. --}}
                                            @php
                                                $moderationVideoId = \App\Utils\UrlUtils::extractYouTubeVideoId($video->youtube_url);
                                            @endphp
                                            @if (is_string($moderationVideoId) && preg_match('/^[A-Za-z0-9_-]{11}$/', $moderationVideoId))
                                            <a href="https://www.youtube.com/watch?v={{ $moderationVideoId }}" target="_blank" rel="noopener noreferrer" class="group relative block" aria-label="{{ __('messages.consent_embed_video_button') }}">
                                                <img src="{{ \App\Utils\UrlUtils::getYouTubeThumbnail($video->youtube_url) }}" alt="" loading="lazy" class="w-full object-cover" style="aspect-ratio:16/9">
                                                <span class="absolute inset-0 flex items-center justify-center bg-black/30 transition-all duration-200 group-hover:bg-black/45" aria-hidden="true">
                                                    <svg class="h-10 w-10 text-white" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z" /></svg>
                                                </span>
                                            </a>
                                            @endif
                                        </div>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            <span v-pre>{{ $video->eventPart ? $video->eventPart->name : __('messages.general') }}</span>
                                            @if ($video->event_date)
                                            &middot; {{ \Carbon\Carbon::parse($video->event_date)->format('M j, Y') }}
                                            @endif
                                            &middot; {{ __('messages.submitted_by') }} <span v-pre>{{ $video->submitterName() }}</span>@if ($video->isGuestSubmission() && $video->submitterEmail()) <span v-pre class="text-gray-500 dark:text-gray-500">({{ $video->submitterEmail() }})</span>@endif
                                        </p>
                                    </div>
                                    <div class="shrink-0">
                                        <button type="submit" form="form-reject-video-{{ $video->id }}" class="px-3 py-1.5 text-sm bg-red-600 text-white rounded hover:bg-red-700">{{ __('messages.reject') }}</button>
                                    </div>
                                </div>
                                @endforeach

                                @foreach ($approvedComments as $comment)
                                <div class="flex flex-col sm:flex-row sm:items-center gap-3 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <div class="flex-1">
                                        <p v-pre class="text-gray-800 dark:text-gray-200">{{ $comment->comment }}</p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                            <span v-pre>{{ $comment->eventPart ? $comment->eventPart->name : __('messages.general') }}</span>
                                            @if ($comment->event_date)
                                            &middot; {{ \Carbon\Carbon::parse($comment->event_date)->format('M j, Y') }}
                                            @endif
                                            &middot; {{ __('messages.submitted_by') }} <span v-pre>{{ $comment->submitterName() }}</span>@if ($comment->isGuestSubmission() && $comment->submitterEmail()) <span v-pre class="text-gray-500 dark:text-gray-500">({{ $comment->submitterEmail() }})</span>@endif
                                        </p>
                                    </div>
                                    <div class="shrink-0">
                                        <button type="submit" form="form-reject-comment-{{ $comment->id }}" class="px-3 py-1.5 text-sm bg-red-600 text-white rounded hover:bg-red-700">{{ __('messages.reject') }}</button>
                                    </div>
                                </div>
                                @endforeach

                                @foreach ($approvedPhotos as $photo)
                                <div class="flex flex-col sm:flex-row sm:items-center gap-3 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <div class="flex-1">
                                        <img src="{{ $photo->photo_url }}" alt="" class="h-32 w-auto rounded object-cover mb-2">
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            <span v-pre>{{ $photo->eventPart ? $photo->eventPart->name : __('messages.general') }}</span>
                                            @if ($photo->event_date)
                                            &middot; {{ \Carbon\Carbon::parse($photo->event_date)->format('M j, Y') }}
                                            @endif
                                            &middot; {{ __('messages.submitted_by') }} <span v-pre>{{ $photo->submitterName() }}</span>@if ($photo->isGuestSubmission() && $photo->submitterEmail()) <span v-pre class="text-gray-500 dark:text-gray-500">({{ $photo->submitterEmail() }})</span>@endif
                                        </p>
                                    </div>
                                    <div class="shrink-0">
                                        <button type="submit" form="form-reject-photo-{{ $photo->id }}" class="px-3 py-1.5 text-sm bg-red-600 text-white rounded hover:bg-red-700">{{ __('messages.reject') }}</button>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        @endif
                    @else
                        <p class="event-empty mt-3">{{ __('messages.fan_content_save_first') }}</p>
                    @endif
                    </div>

                    <button type="button" class="event-subrow engagement-tab" data-tab="feedback" :aria-expanded="activeEngagementTab === 'feedback' ? 'true' : 'false'"
                        @click="activeEngagementTab = activeEngagementTab === 'feedback' ? '' : 'feedback'">
                        <span class="event-row-title">{{ __('messages.feedback') }}</span>
                        <span class="event-row-summary" v-cloak v-text="engagementRows.feedback"></span>
                        @if (! $role->isPro())<x-lock-badge tier="pro" />@endif
                        <svg class="event-row-chevron" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                    </button>
                    <div v-show="activeEngagementTab === 'feedback'" class="event-subrow-body" data-engagement-pane="feedback">
                    @if ($role->isPro())
                        <div class="event-setting">
                            <span class="event-setting-label" id="feedback_enabled_label">{{ __('messages.feedback_override') }}<span class="event-tab-aside block font-normal">{{ __('messages.schedule') }}: {{ mb_strtolower($engagementInherited['feedback_enabled'] ? __('messages.enabled') : __('messages.disabled')) }}</span></span>
                            <div class="{{ $segShell }} event-seg" role="radiogroup" aria-labelledby="feedback_enabled_label">
                                {{-- The first choice says what the schedule's own setting is. --}}
                                @foreach (['' => __('messages.same_as_schedule'), '1' => __('messages.enabled'), '0' => __('messages.disabled')] as $settingValue => $settingLabel)
                                <label>
                                    <input type="radio" class="sr-only peer" name="feedback_enabled" value="{{ $settingValue }}" v-model="engagementSettings.feedback_enabled" {{ (string) $settingValue === $engagementSettingsNow['feedback_enabled'] ? 'checked' : '' }}>
                                    <span class="{{ $segRadio }}">{{ $settingLabel }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 mb-2">{{ __('messages.feedback_override_help') }}</p>
                    @else
                        <x-upgrade-prompt tier="pro" :learnMoreUrl="marketing_url('/features/feedback')" :subdomain="$subdomain">
                            <x-slot:icon>
                                <svg class="h-7 w-7 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
                                </svg>
                            </x-slot:icon>
                            {{ __('messages.feedback_pro_only') }}
                        </x-upgrade-prompt>
                    @endif
                    </div>

                    @if ($role->carpool_enabled)
                    <button type="button" class="event-subrow engagement-tab" data-tab="carpool" :aria-expanded="activeEngagementTab === 'carpool' ? 'true' : 'false'"
                        @click="activeEngagementTab = activeEngagementTab === 'carpool' ? '' : 'carpool'">
                        <span class="event-row-title">{{ __('messages.carpool') }}</span>
                        <span class="event-row-summary">{{ __('messages.carpool_row_prompt') }}</span>
                        
                        <svg class="event-row-chevron" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                    </button>
                    <div v-show="activeEngagementTab === 'carpool'" class="event-subrow-body" data-engagement-pane="carpool">
                        @php
                            $carpoolOffers = $event->carpoolOffers()->with(['user', 'approvedRequests', 'reports.reporter'])->where('status', 'active')->get();
                            $carpoolReports = \App\Models\CarpoolReport::whereIn('carpool_offer_id', $carpoolOffers->pluck('id'))->with(['reporter', 'reported', 'offer'])->get();
                        @endphp

                        @if ($carpoolOffers->count() > 0)
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">{{ __('messages.carpool_active_offers') }}: {{ $carpoolOffers->count() }}</p>
                        <div class="space-y-3 mb-4">
                            @foreach ($carpoolOffers as $carpoolOffer)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-3">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <span v-pre class="text-sm font-medium text-gray-900 dark:text-white">{{ $carpoolOffer->user->name }}</span>
                                        <span v-pre class="text-xs text-gray-500 dark:text-gray-400 ms-2">{{ $carpoolOffer->city }} &middot; {{ $carpoolOffer->directionLabel() }}</span>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $carpoolOffer->approvedRequests->count() }}/{{ $carpoolOffer->total_spots }} {{ __('messages.carpool_spots') }}</p>
                                    </div>
                                    {{-- A button for a form that lives after the main one (see "External forms"): a
                                         form written here is nested, the browser drops the first such tag, and its
                                         _method=DELETE then posts with the event itself. --}}
                                    <button type="submit" form="form-remove-carpool-offer-{{ $carpoolOffer->id }}" class="text-xs text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300">{{ __('messages.carpool_remove_offer') }}</button>
                                </div>
                                @php $offerReports = $carpoolReports->where('carpool_offer_id', $carpoolOffer->id); @endphp
                                @if ($offerReports->count() > 0)
                                <div class="mt-2 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-2">
                                    <p class="text-xs font-medium text-red-700 dark:text-red-400 mb-1">{{ __('messages.carpool_reports') }} ({{ $offerReports->count() }})</p>
                                    @foreach ($offerReports as $report)
                                    <div class="flex items-start justify-between">
                                        <p v-pre class="text-xs text-red-600 dark:text-red-300">{{ $report->reporter->name }}: {{ $report->reason }}</p>
                                        <button type="submit" form="form-dismiss-carpool-report-{{ $report->id }}" class="text-xs text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 ms-2 whitespace-nowrap">{{ __('messages.carpool_dismiss_report') }}</button>
                                    </div>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.carpool_no_offers') }}</p>
                        @endif
                    </div>
                    @endif
                    </div>

                </div>
            </div>

                @if ($sponsorsShown)
                    <button type="button" class="mobile-section-header" data-section="section-event-settings" {!! $moreSectionAttrs !!}>
                        <span class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" /></svg>
                            <span class="section-nav-text">
                                <span>{{ __('messages.sponsors') }}</span>
                                <span class="section-nav-summary" v-cloak :class="{ 'is-empty': tabSummaries['section-event-settings'].empty }"><bdi v-text="tabSummaries['section-event-settings'].text"></bdi></span>
                            </span>
                            <span class="section-nav-dot" v-cloak v-show="sectionDirty['section-event-settings']"></span>
                        </span>
                        <svg class="w-5 h-5 text-gray-400 transition-transform duration-200 accordion-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div id="section-event-settings" class="section-content lg:mt-0">
                        <div class="{{ $tabCol }}">
                            <h2 class="section-heading-name text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" /></svg>
                                {{ __('messages.sponsors') }}
                            </h2>

                            <div>
                                <input type="hidden" name="sponsor_mode" :value="event.sponsor_mode">
                                <div class="event-tiles mb-6" role="group" aria-label="{{ __('messages.sponsors') }}">
                                    <button type="button" class="event-tile" :class="{ 'is-on': event.sponsor_mode === 'default' }" :aria-pressed="event.sponsor_mode === 'default' ? 'true' : 'false'" @click="event.sponsor_mode = 'default'; markTabDirty('section-event-settings')">
                                        <span class="event-tile-title">{{ __('messages.same_as_schedule') }}</span>
                                        {{-- Whose and which: the names of the schedule's sponsors, or that it has none.
                                             v-pre: a sponsor's name is its owner's text, inside the Vue mount. --}}
                                        @php $scheduleSponsorNames = collect($role->getSponsorLogos())->pluck('name')->filter()->implode(', '); @endphp
                                        {{-- And when the schedule has them switched off (Engagement > Sponsors), the tile says
                                             so: an event that follows the schedule shows none then. --}}
                                        <span class="event-tile-help" v-pre>{{ $scheduleSponsorNames ? $scheduleSponsorNames.($role->show_sponsors === false ? ' · '.__('messages.sponsors_hidden') : '') : __('messages.sponsors_same_help').': '.mb_strtolower(__('messages.none')) }}</span>
                                    </button>
                                    <button type="button" class="event-tile" :class="{ 'is-on': event.sponsor_mode === 'none' }" :aria-pressed="event.sponsor_mode === 'none' ? 'true' : 'false'" @click="event.sponsor_mode = 'none'; markTabDirty('section-event-settings')">
                                        <span class="event-tile-title">{{ __('messages.no_sponsors') }}</span>
                                        <span class="event-tile-help">{{ __('messages.sponsors_none_help') }}</span>
                                    </button>
                                    <button type="button" class="event-tile" :class="{ 'is-on': event.sponsor_mode === 'custom' }" :aria-pressed="event.sponsor_mode === 'custom' ? 'true' : 'false'" @click="event.sponsor_mode = 'custom'; markTabDirty('section-event-settings')">
                                        <span class="event-tile-title">{{ __('messages.sponsors_own') }}</span>
                                        <span class="event-tile-help">{{ __('messages.sponsors_own_help') }}</span>
                                    </button>
                                </div>

                                <div v-show="event.sponsor_mode === 'custom'">
                                    <input type="hidden" name="existing_event_sponsors" :value="JSON.stringify(eventSponsors.filter(s => !s.newFile))">
                                    {{-- The ones posted as uploads, without their files: for the page a refused save
                                         comes back to, which cannot be sent the files again. A stored sponsor being
                                         given a new logo is in this list and not the one above, and used to vanish
                                         from the page, so that the next save deleted it. The server ignores it. --}}
                                    <input type="hidden" name="pending_event_sponsors" :value="JSON.stringify(eventSponsors.filter(s => s.newFile).map(s => ({ logo: s.logo || '', name: s.name || '', url: s.url || '', tier: s.tier || '' })))">
                                    <div id="new-event-sponsor-inputs-container"></div>

                                    <!-- Sponsor list -->
                                    <p class="event-hint">{{ __('messages.sponsors_own_hint') }}</p>
                                    <div id="event-sponsors-list" class="event-list mb-4">
                                        <div v-for="(sponsor, index) in eventSponsors" :key="index"
                                            class="sponsor-item event-list-row is-centered"
                                            :class="{'ring-2 ring-[var(--brand-blue)]': editingSponsorIndex === index}">
                                            <div class="drag-handle cursor-grab text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 flex-shrink-0">
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M7 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM7 8a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 8a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM7 14a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 14a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/>
                                                </svg>
                                            </div>
                                            <div class="flex-shrink-0 bg-white dark:bg-gray-700 rounded border border-gray-200 dark:border-gray-600 flex items-center justify-center overflow-hidden" style="width: 72px; height: 48px;">
                                                <img v-if="sponsor.logo_url" :src="sponsor.logo_url" :alt="sponsor.name || ''" class="max-w-full max-h-full object-contain" />
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">@{{ sponsor.name || '' }}</div>
                                                <span v-if="sponsor.tier === 'gold'" class="inline-block text-xs px-1.5 py-0.5 rounded bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300">{{ __('messages.gold') }}</span>
                                                <span v-if="sponsor.tier === 'silver'" class="inline-block text-xs px-1.5 py-0.5 rounded bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300">{{ __('messages.silver') }}</span>
                                                <span v-if="sponsor.tier === 'bronze'" class="inline-block text-xs px-1.5 py-0.5 rounded bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-300">{{ __('messages.bronze') }}</span>
                                                <div v-if="sponsor.url" class="text-xs text-gray-500 dark:text-gray-400 truncate">@{{ sponsor.url }}</div>
                                            </div>
                                            <button type="button" @click="editEventSponsor(index)" class="event-icon-btn" title="{{ __('messages.edit') }}" aria-label="{{ __('messages.edit') }}">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                </svg>
                                            </button>
                                            <button type="button" @click="removeEventSponsor(index)" class="event-icon-btn" title="{{ __('messages.remove') }}" aria-label="{{ __('messages.remove') }}">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Limit message -->
                                    <div v-if="eventSponsors.length >= maxSponsors" class="mb-4">
                                        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3">
                                            <p class="text-sm text-amber-800 dark:text-amber-200 flex items-start gap-2">
                                                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                </svg>
                                                <span>{{ __('messages.max_sponsors_reached', ['count' => config('app.max_sponsors')]) }}</span>
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Unsaved-uploads advisory: PHP caps files per request -->
                                    <div v-if="eventSponsors.filter(s => s.newFile).length >= maxPendingSponsorUploads" class="mb-4">
                                        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3">
                                            <p class="text-sm text-amber-800 dark:text-amber-200 flex items-start gap-2">
                                                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                </svg>
                                                <span>{{ __('messages.save_sponsors_before_adding_more') }}</span>
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Add/Edit sponsor form -->
                                    <div v-if="eventSponsors.length > 0 && eventSponsors.length < maxSponsors && ! sponsorFormOpen && editingSponsorIndex < 0">
                                        <button type="button" class="event-link" @click="sponsorFormOpen = true">+ {{ __('messages.add_sponsor') }}</button>
                                    </div>
                                    <div v-if="(eventSponsors.length < maxSponsors && (sponsorFormOpen || eventSponsors.length === 0)) || editingSponsorIndex >= 0">
                                        <div class="event-add-box">
                                            <div v-if="sponsorFormError" role="alert" class="mb-3 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-sm text-red-700 dark:text-red-400">@{{ sponsorFormError }}</div>
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                                                <div>
                                                    <x-input-label for="event_sponsor_name_input" :value="__('messages.sponsor_name')" />
                                                    <input type="text" id="event_sponsor_name_input" maxlength="100" v-model="sponsorForm.name"
                                                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm text-sm" />
                                                </div>
                                                <div>
                                                    <x-input-label for="event_sponsor_url_input" :value="__('messages.sponsor_url')" />
                                                    <input type="url" id="event_sponsor_url_input" maxlength="500" v-model="sponsorForm.url"
                                                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm text-sm" />
                                                </div>
                                            </div>
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                                                <div>
                                                    <x-input-label :value="__('messages.sponsor_tier')" />
                                                    <select id="event_sponsor_tier_input" v-model="sponsorForm.tier"
                                                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm text-sm">
                                                        <option value="">-</option>
                                                        <option value="gold">{{ __('messages.gold') }}</option>
                                                        <option value="silver">{{ __('messages.silver') }}</option>
                                                        <option value="bronze">{{ __('messages.bronze') }}</option>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{ __('messages.logo') }} <span v-show="editingSponsorIndex < 0">*</span></label>
                                                    <input type="file" ref="eventSponsorLogoInput" accept="image/*" @change="previewEventSponsorLogo"
                                                        class="mt-1 block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-[var(--brand-button-bg)] file:text-white hover:file:bg-[var(--brand-button-bg-hover)]" />
                                                    <img v-if="sponsorLogoPreview" :src="sponsorLogoPreview" alt="Logo Preview" style="max-height:120px;" class="mt-2 rounded-lg border border-gray-200 dark:border-gray-600" />
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <button v-if="editingSponsorIndex >= 0" type="button" @click="cancelEditEventSponsor" class="event-link event-link-quiet">
                                                    {{ __('messages.cancel') }}
                                                </button>
                                                <x-brand-button size="sm" @click="addOrSaveEventSponsor">
                                                    <svg v-if="editingSponsorIndex < 0" class="w-4 h-4 me-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                    </svg>
                                                    <svg v-else class="w-4 h-4 me-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    <span>@{{ editingSponsorIndex >= 0 ? @json(__('messages.done')) : @json(__('messages.add_sponsor')) }}</span>
                                                </x-brand-button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                @endif

                @if ($isFirstEventRun)
                <button type="button" v-cloak v-if="!showMoreSections" @click="showMoreSections = true"
                    class="lg:hidden flex w-full items-center justify-center gap-2 px-4 py-3 text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 transition-all duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span>{{ __('messages.more_options') }}<span class="font-normal" v-if="moreTabNames">: <bdi v-text="moreTabNames"></bdi></span></span>
                </button>
                @endif

                </div> <!-- End of main content area -->
            </div> <!-- End of grid container -->
        </div> {{-- End of the py-5 wrapper. It used to stay open past </form>, which put every helper
                    form and modal below this point inside the event form. --}}

        {{-- The save bar: one for every width. Fixed to the bottom of a phone, as it always was, and
             sticky at the bottom of the form on a desktop, where Save used to sit in the sidebar. Its
             status line says what Save will do, or what is in its way. --}}
        <div class="event-save-spacer"></div>
        <div class="event-save-bar">
            <div class="event-save-bar-inner">
                <div class="event-save-status" aria-live="polite">
                    <span v-cloak v-if="confirmingDiscard">{{ __('messages.discard_unsaved_changes') }}</span>
                    <span v-cloak v-else-if="barStatus.kind === 'tabs'">
                        <span v-text="barStatus.label + ':'"></span>
                        <template v-for="(tab, index) in barStatus.tabs" :key="tab.id">
                            <span v-if="index > 0" aria-hidden="true">&middot;</span>
                            <button type="button" class="event-link" @click="goToTab(tab.id)" v-text="tab.label"></button>
                        </template>
                    </span>
                    <span v-cloak v-else-if="barStatus.kind === 'text'" :class="{ 'event-save-strong': barStatus.strong, 'event-save-quiet': barStatus.quiet }" v-text="barStatus.text"></span>
                    <span v-cloak v-else-if="barStatus.kind === 'new'">
                        @if ($setupGuidePromise)
                        {{-- v-pre: the schedule's name is its owner's text, inside the Vue mount. --}}
                        <span v-if="visibility === 'public'"><span v-pre>{{ __('messages.setup_guide_then_live', ['name' => $role->name]) }}</span></span>
                        <span v-else class="event-save-pair">
                            <span v-text="tabLabels.visibility_label + ':'"></span>
                            <button type="button" class="event-link" @click="goToTab('section-listing')" v-text="barStatus.visibility"></button>
                        </span>
                        @else
                        <span class="event-save-pair">
                            <span v-text="tabLabels.visibility_label + ':'"></span>
                            <button type="button" class="event-link" @click="goToTab('section-listing')" v-text="barStatus.visibility"></button>
                        </span>
                        @endif
                    </span>
                </div>

                <div class="event-save-actions" v-show="! confirmingDiscard">
                    <button type="button" class="event-bar-text" @click="cancelEdit">{{ __('messages.cancel') }}</button>
                    @if ($event->exists && $event->is_draft && ! $event->is_internal)
                    <button type="button" @click="publishEvent()" v-bind:disabled="isSaving"
                        class="event-bar-publish inline-flex items-center justify-center px-4 py-3 bg-green-600 border border-transparent rounded-lg font-semibold text-base text-white hover:bg-green-500 active:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-all duration-200">
                        {{ __('messages.publish') }}
                    </button>
                    @endif
                    <x-brand-button type="submit" class="event-bar-save" v-bind:disabled="isSaving || galleryWaiting" v-bind:class="{ 'is-idle': eventIsSaved && ! isDirty }">
                        {{-- v-cloak on all but the one a plain browser should show: until the page's script
                             ran, the button read "{{ galleryFinishingText }}Saving..." on every load. --}}
                        <span v-if="galleryWaiting" v-cloak>@{{ galleryFinishingText }}</span>
                        <span v-else-if="isSaving" v-cloak>{{ __('messages.saving') }}</span>
                        @if ($isFirstEventRun)
                        <span v-else>{{ __('messages.create_event') }}</span>
                        @else
                        {{-- A mustache, not v-text: v-text on an element that has children does not compile. --}}
                        <span v-else v-cloak>@{{ saveLabel }}</span>
                        {{-- What saveLabel will say, for the moment before it can: v-if="false" means
                             nothing to a browser, and Vue takes the element away when it mounts. --}}
                        <span v-if="false">{{ $saveLabelOnLoad }}</span>
                        @endif
                    </x-brand-button>
                </div>
                <div class="event-save-actions" v-cloak v-show="confirmingDiscard">
                    <button type="button" class="event-bar-text" @click="confirmingDiscard = false">{{ __('messages.keep_editing') }}</button>
                    <button type="button" class="event-bar-quiet" @click="discardAndLeave">{{ __('messages.discard') }}</button>
                </div>
                {{-- The page's own cancel (layouts/app-admin: back, or the fallback URL), which the bar's
                     Cancel calls once it knows nothing unsaved is being thrown away. --}}
                <x-cancel-button id="event-cancel-real" class="hidden" tabindex="-1" aria-hidden="true" />
            </div>
        </div>

    </form>

    {{-- External forms for fan content approve/reject buttons (outside main form to avoid nesting) --}}
    @if ($event->exists)
        {{-- The carpool tab's two actions, for the same reason. $carpoolOffers is set where that tab
             renders, which is only on a schedule with carpooling on. --}}
        @foreach ($carpoolOffers ?? [] as $carpoolOffer)
        <form id="form-remove-carpool-offer-{{ $carpoolOffer->id }}" method="POST" action="{{ route('carpool.admin_remove_offer', ['subdomain' => $role->subdomain, 'offer_hash' => \App\Utils\UrlUtils::encodeId($carpoolOffer->id)]) }}" data-confirm="{{ __('messages.are_you_sure') }}" class="hidden">@csrf @method('DELETE')</form>
        @endforeach
        @foreach ($carpoolReports ?? [] as $report)
        <form id="form-dismiss-carpool-report-{{ $report->id }}" method="POST" action="{{ route('carpool.admin_dismiss_report', ['subdomain' => $role->subdomain, 'report_hash' => \App\Utils\UrlUtils::encodeId($report->id)]) }}" class="hidden">@csrf @method('DELETE')</form>
        @endforeach
        @foreach ($pendingVideos as $video)
        <form id="form-approve-video-{{ $video->id }}" method="POST" action="{{ route('event.approve_video', ['subdomain' => $subdomain, 'hash' => \App\Utils\UrlUtils::encodeId($video->id)]) }}" class="hidden">@csrf</form>
        <form id="form-reject-video-{{ $video->id }}" method="POST" action="{{ route('event.reject_video', ['subdomain' => $subdomain, 'hash' => \App\Utils\UrlUtils::encodeId($video->id)]) }}" class="hidden">@csrf @method('DELETE')</form>
        @endforeach
        @foreach ($pendingComments as $comment)
        <form id="form-approve-comment-{{ $comment->id }}" method="POST" action="{{ route('event.approve_comment', ['subdomain' => $subdomain, 'hash' => \App\Utils\UrlUtils::encodeId($comment->id)]) }}" class="hidden">@csrf</form>
        <form id="form-reject-comment-{{ $comment->id }}" method="POST" action="{{ route('event.reject_comment', ['subdomain' => $subdomain, 'hash' => \App\Utils\UrlUtils::encodeId($comment->id)]) }}" class="hidden">@csrf @method('DELETE')</form>
        @endforeach
        @foreach ($approvedVideos as $video)
        <form id="form-reject-video-{{ $video->id }}" method="POST" action="{{ route('event.reject_video', ['subdomain' => $subdomain, 'hash' => \App\Utils\UrlUtils::encodeId($video->id)]) }}" class="hidden">@csrf @method('DELETE')</form>
        @endforeach
        @foreach ($approvedComments as $comment)
        <form id="form-reject-comment-{{ $comment->id }}" method="POST" action="{{ route('event.reject_comment', ['subdomain' => $subdomain, 'hash' => \App\Utils\UrlUtils::encodeId($comment->id)]) }}" class="hidden">@csrf @method('DELETE')</form>
        @endforeach
        @foreach ($pendingPhotos as $photo)
        <form id="form-approve-photo-{{ $photo->id }}" method="POST" action="{{ route('event.approve_photo', ['subdomain' => $subdomain, 'hash' => \App\Utils\UrlUtils::encodeId($photo->id)]) }}" class="hidden">@csrf</form>
        <form id="form-reject-photo-{{ $photo->id }}" method="POST" action="{{ route('event.reject_photo', ['subdomain' => $subdomain, 'hash' => \App\Utils\UrlUtils::encodeId($photo->id)]) }}" class="hidden">@csrf @method('DELETE')</form>
        @endforeach
        @foreach ($approvedPhotos as $photo)
        <form id="form-reject-photo-{{ $photo->id }}" method="POST" action="{{ route('event.reject_photo', ['subdomain' => $subdomain, 'hash' => \App\Utils\UrlUtils::encodeId($photo->id)]) }}" class="hidden">@csrf @method('DELETE')</form>
        @endforeach
    @endif


@php
    $eventSponsorsWithUrls = collect(json_decode($event->sponsor_logos ?? '[]', true) ?: [])->map(function ($s) {
        $logoUrl = '';
        if (!empty($s['logo'])) {
            if (str_starts_with($s['logo'], 'demo_')) {
                $logoUrl = url('/images/demo/' . $s['logo']);
            } elseif (config('app.hosted') && config('filesystems.default') == 'do_spaces') {
                $logoUrl = 'https://eventschedule.nyc3.cdn.digitaloceanspaces.com/' . $s['logo'];
            } elseif (in_array(config('filesystems.default'), ['local', 'public'])) {
                $logoUrl = url('/storage/' . $s['logo']);
            } else {
                $logoUrl = $s['logo'];
            }
        }
        return array_merge($s, ['logo_url' => $logoUrl]);
    })->values()->toArray();
    if (session()->hasOldInput('existing_event_sponsors')) {
        $storedSponsors = collect($eventSponsorsWithUrls)->keyBy('logo');
        $eventSponsorsWithUrls = collect(json_decode(old('existing_event_sponsors') ?: '[]', true) ?: [])
            ->map(fn ($s) => isset($s['logo']) && $storedSponsors->has($s['logo'])
                ? array_merge($storedSponsors->get($s['logo']), ['name' => (string) ($s['name'] ?? ''), 'url' => $s['url'] ?? null, 'tier' => (string) ($s['tier'] ?? '')])
                : null)
            ->filter()->values()->all();

        // What was posted as an upload. One that is stored comes back under the logo it has, with
        // what was typed; a new one cannot come back without its file, so its form opens on it.
        $lostSponsor = null;
        foreach ((array) (json_decode(old('pending_event_sponsors') ?: '[]', true) ?: []) as $pending) {
            $pending = (array) $pending;
            $typedSponsor = ['name' => (string) ($pending['name'] ?? ''), 'url' => ($pending['url'] ?? '') ?: null, 'tier' => (string) ($pending['tier'] ?? '')];
            if (! empty($pending['logo']) && $storedSponsors->has($pending['logo'])) {
                $place = min($storedSponsors->keys()->search($pending['logo']), count($eventSponsorsWithUrls));
                array_splice($eventSponsorsWithUrls, $place, 0, [array_merge($storedSponsors->get($pending['logo']), $typedSponsor)]);
            } elseif ($lostSponsor === null) {
                $lostSponsor = ['name' => $typedSponsor['name'], 'url' => (string) $typedSponsor['url'], 'tier' => $typedSponsor['tier']];
            }
        }
    }
    $sponsorFormNow = ($lostSponsor ?? null) ?: ['name' => '', 'url' => '', 'tier' => ''];
    $sponsorFormOpenNow = ! empty($lostSponsor);
@endphp

    {{-- Attendee change-notification confirm dialog (issue #94). Vue-driven (not the Alpine x-modal). --}}
    <div v-if="showNotifyModal" v-cloak @keydown.esc="closeNotifyModal()" tabindex="-1" class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="notify-modal-title">
        <div class="fixed inset-0 bg-black/50" @click="closeNotifyModal()"></div>
        <div class="relative ap-card rounded-2xl shadow-lg w-full max-w-md p-6">
            <h2 id="notify-modal-title" class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('messages.notify_attendees_title') }}</h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">@{{ notifyBody() }}</p>
            <p v-if="changeSummary()" class="mt-1 text-sm text-gray-500 dark:text-gray-400">@{{ changeSummary() }}</p>

            <div class="mt-4">
                <label for="notify_message_field" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('messages.notify_message_label') }}</label>
                <textarea id="notify_message_field" ref="notifyMessageField" v-model="notifyMessage" maxlength="280" rows="3"
                    placeholder="{{ __('messages.notify_message_placeholder') }}"
                    class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm focus:ring-[var(--brand-blue)] focus:border-[var(--brand-blue)]"></textarea>
                <div class="mt-1 text-xs text-gray-400 text-end">@{{ notifyMessage.length }}/280</div>
            </div>

            <div v-if="recentlyNotifiedMinutes() !== null" class="mt-2 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3 flex items-start gap-2">
                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
                <p class="text-xs text-amber-800 dark:text-amber-300">@{{ recentlyNotifiedCaption() }}</p>
            </div>

            <p class="mt-3 text-xs text-gray-400">{{ __('messages.notify_channels_note') }}</p>

            <div class="mt-2">
                <button type="button" @click="openNotifyPreview()" class="text-sm text-[var(--brand-blue)] hover:underline">{{ __('messages.preview_email') }}</button>
            </div>

            <div class="mt-6 flex items-center justify-end gap-3">
                <button type="button" @click="confirmNotify(false)" :disabled="isSaving" class="px-4 py-3 text-base rounded-lg border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors disabled:opacity-50">{{ __('messages.dont_notify') }}</button>
                <button type="button" @click="confirmNotify(true)" :disabled="isSaving" class="px-4 py-3 text-base rounded-lg text-white bg-[var(--brand-button-bg)] hover:bg-[var(--brand-button-bg-hover)] transition-colors disabled:opacity-50">{{ __('messages.notify_attendees_button') }}</button>
            </div>
        </div>
    </div>

    {{-- Hidden form that POSTs the current (unsaved) values to render an email preview in a new tab. --}}
    @if ($event->exists)
    <form id="notify-preview-form" method="POST" action="{{ route('event.notify_preview', ['subdomain' => $subdomain, 'hash' => \App\Utils\UrlUtils::encodeId($event->id)]) }}" target="_blank" class="hidden">
        @csrf
        <input type="hidden" name="notify_message">
        <input type="hidden" name="name">
        <input type="hidden" name="event_date">
        <input type="hidden" name="start_time">
        <input type="hidden" name="duration">
        <input type="hidden" name="event_url">
        <input type="hidden" name="venue_name">
        <input type="hidden" name="venue_id">
    </form>
    @can('delete', $event)
    <form id="event-cancel-form" method="POST" action="{{ route('event.cancel', ['subdomain' => $subdomain, 'hash' => \App\Utils\UrlUtils::encodeId($event->id)]) }}" class="hidden">
        @csrf
        <input type="hidden" name="notify_attendees" value="0">
        <input type="hidden" name="notify_message">
    </form>
    <form id="event-restore-form" method="POST" action="{{ route('event.restore', ['subdomain' => $subdomain, 'hash' => \App\Utils\UrlUtils::encodeId($event->id)]) }}" class="hidden">
        @csrf
    </form>
    @endcan
    @endif

    {{-- Cancel-event confirm dialog (issue #94). --}}
    @if ($event->exists)
    <div v-if="showCancelModal" v-cloak @keydown.esc="closeCancelModal()" tabindex="-1" class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="cancel-modal-title">
        <div class="fixed inset-0 bg-black/50" @click="closeCancelModal()"></div>
        <div class="relative ap-card rounded-2xl shadow-lg w-full max-w-md p-6">
            <h2 id="cancel-modal-title" class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('messages.cancel_event_title') }}</h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                <span v-if="cancelWillNotify()">@{{ cancelBody() }}</span>
                <span v-else>{{ __('messages.cancel_event_body_simple') }}</span>
            </p>

            <div class="mt-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3 flex items-start gap-2">
                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
                <p class="text-xs text-amber-800 dark:text-amber-300">{{ __('messages.cancel_refund_note') }}</p>
            </div>

            <div v-if="cancelWillNotify()" class="mt-4">
                <label for="cancel_message_field" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('messages.notify_message_label') }}</label>
                <textarea id="cancel_message_field" ref="cancelMessageField" v-model="cancelMessage" maxlength="280" rows="3"
                    placeholder="{{ __('messages.cancel_message_placeholder') }}"
                    class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 text-sm focus:ring-[var(--brand-blue)] focus:border-[var(--brand-blue)]"></textarea>
                <div class="mt-1 text-xs text-gray-400 text-end">@{{ cancelMessage.length }}/280</div>
            </div>

            <div class="mt-6 flex items-center justify-end gap-3">
                <button type="button" @click="closeCancelModal()" class="px-4 py-3 text-base rounded-lg border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">{{ __('messages.keep_event') }}</button>
                <button type="button" @click="submitCancel()" :disabled="isSubmittingCancel" class="px-4 py-3 text-base rounded-lg text-white bg-red-600 hover:bg-red-700 transition-colors disabled:opacity-50">
                    <span v-if="cancelWillNotify()">{{ __('messages.cancel_and_notify') }}</span>
                    <span v-else>{{ __('messages.cancel_event') }}</span>
                </button>
            </div>
        </div>
    </div>
    @endif

</div>

@include('partials.gallery-editor')

<script {!! nonce_attr() !!}>
  const { createApp, ref } = Vue

  // event-section-aliases:start
  // Fragments that name a tab INSIDE Engagement, not a section of this form. Notification emails,
  // push messages and the redirects after approving fan content have always linked to
  // #section-fan-content, and the poll actions to #section-polls, and neither id exists here, so
  // every one of those links opened the first section instead. The fragment wins over
  // ?engagement=: approving fan content from a page opened with ?engagement=polls comes back with
  // both, and it is the fan content that was just acted on.
  //
  // The second half are sections that stopped being tabs of their own: the venue and the repeat
  // settings are on the Event tab, "Schedules" is part of Listing, and the two calendars share one
  // tab. Links to the old ids are in sent email and in bookmarks, so they keep landing somewhere.
  window.eventSectionAliases = {
    'section-fan-content': ['section-engagement', 'fan_content'],
    'section-polls': ['section-engagement', 'polls'],
    'section-carpool': ['section-engagement', 'carpool'],
    'section-venue': ['section-details'],
    'section-recurring': ['section-details'],
    'section-schedules': ['section-listing'],
    'section-google-calendar': ['section-calendar-sync'],
    'section-microsoft-calendar': ['section-calendar-sync'],
  };
  // Sections folded behind "More options" on a first event. showSection() opens the fold when
  // something has to reach one of them.
  window.eventFoldedSections = @json($foldedSectionIds);
  window.resolveEventSectionHash = function (hash, search) {
    var name = String(hash || '').replace('#', '');
    var alias = window.eventSectionAliases[name];
    var requested = new URLSearchParams(search || '').get('engagement');
    var tabs = ['fan_content', 'polls', 'feedback', 'carpool'];
    return {
      section: alias ? alias[0] : name,
      // The row of the Engagement tab to open; none unless the link names one.
      engagementTab: alias && alias[1] ? alias[1] : (tabs.includes(requested) ? requested : ''),
    };
  };
  // event-section-aliases:end

  // The gallery's store lives outside the component so the Gallery section, the strip under the
  // flyer, the Save buttons and validateForm() all read the same uploads.
  @php
    $galleryUploadUrl = route('gallery.upload', ['subdomain' => $subdomain]);
    $galleryFanPhotosUrl = $event->exists ? route('gallery.from_fan_photos', ['subdomain' => $subdomain]) : null;
    $galleryEventHash = $event->exists ? \App\Utils\UrlUtils::encodeId($event->id) : null;
    // Rendered here rather than split in the browser: the translations use Laravel's {1} / [2,*]
    // plural syntax, which only trans_choice() reads.
    $galleryCountForms = [
        'one' => trans_choice('messages.gallery_photo_count', 1, ['count' => 1]),
        'many' => trans_choice('messages.gallery_photo_count', 2, ['count' => ':count']),
    ];
  @endphp
  const galleryStore = window.EsGallery.createStore({
    uploadUrl: @json($galleryUploadUrl),
    fanPhotosUrl: @json($galleryFanPhotosUrl),
    target: @json($event->exists ? 'event' : 'new_event'),
    eventHash: @json($galleryEventHash),
    draftToken: @json($galleryState['token']),
    csrf: @json(csrf_token()),
    max: @json(\App\Utils\GalleryUtils::maxImages()),
    maxBytes: @json(\App\Utils\GalleryUtils::maxUploadBytes()),
    initialImages: @json($galleryState['images']),
    knownIds: @json($galleryState['known']),
    // A photo added, removed, moved or captioned. None of that goes through a field of the form
    // (the caption dialog is outside it), so the tab is marked here: marking only "unsaved" left
    // the bar reading "No unsaved changes" with photos waiting to be saved.
    onChange: function () {
      if (window.vueApp) {
        window.vueApp.isDirty = true;
        window.vueApp.markTabDirty('section-gallery');
      }
    },
  });

  {{-- Names somebody else can write (an event a guest sent in, one a curator listed here, one
       read from a calendar feed; a sub-schedule's) are built here and handed to the json
       directive as one plain variable with its options spelled out. Given an expression with a
       comma in it, the directive reads what follows the comma as its own options and stops
       escaping tags, and inside a script block the characters of an opening comment followed by
       an opening script tag keep the block's own closing tag from ending it: one event with
       such a name stopped this form for every event of the schedule
       (EventFormTemplateInjectionTest). --}}
  @php
    $scriptSafe = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_PARTIAL_OUTPUT_ON_ERROR;
    $eventNameNow = old('name', $event->name ?? '');
    $passGroupOptions = $role->groups->map(fn ($g) => ['id' => \App\Utils\UrlUtils::encodeId($g->id), 'name' => $g->name])->values()->all();
    $passEventOptions = $role->events()->whereNotNull('events.starts_at')->orderBy('events.starts_at', 'desc')->limit(200)->get()->map(fn ($e) => ['id' => \App\Utils\UrlUtils::encodeId($e->id), 'name' => $e->name])->unique('id')->values()->all();
  @endphp
  app = createApp({
    data() {
      return {
        event: {
          ...@json($eventForPage),
          event_password: @json(old('event_password', $event->event_password ?? '') ?? ''),
          is_draft: @json($draftNow),
          is_private: @json($privateNow),
          is_internal: @json($internalNow),
          tickets_enabled: @json((bool) old('tickets_enabled', $event->tickets_enabled)),
          rsvp_enabled: @json((bool) old('rsvp_enabled', $event->rsvp_enabled)),
          rsvp_limit: @json($canSeeTicketData ? $event->rsvp_limit : null),
          total_tickets_mode: @json($event->total_tickets_mode ?? 'individual'),
          // A select bound to null renders blank, and decimal(13,3) serializes as the
          // string "15.000" which is not what a number input should start life holding.
          // A row that predates the column has no type, so it opens on the model's default
          // rather than on whichever option happens to be listed first.
          coupon_discount_type: @json($event->coupon_discount_type ?: \App\Models\Event::DEFAULT_COUPON_DISCOUNT_TYPE),
          coupon_discount: @json($canSeeTicketData && $event->coupon_discount !== null ? (float) $event->coupon_discount : null),
          recurring_end_type: @json($event->recurring_end_type ?? 'never'),
          recurring_end_value: @json($event->recurring_end_value ?? null),
          recurring_frequency: @json($event->recurring_frequency ?? 'weekly'),
          recurring_interval: @json($event->recurring_interval ?? 2),
          ask_phone: {{ $event->ask_phone ? 'true' : 'false' }},
          require_phone: {{ $event->require_phone ? 'true' : 'false' }},
          country_code_phone: {{ $event->country_code_phone ? 'true' : 'false' }},
          individual_tickets: {{ $event->individual_tickets ? 'true' : 'false' }},
          individual_ticket_fields: {{ $event->individual_ticket_fields ? 'true' : 'false' }},
          sell_after_start: {{ $event->sell_after_start ? 'true' : 'false' }},
          show_unavailable_tickets: {{ $event->show_unavailable_tickets ? 'true' : 'false' }},
          sponsor_mode: @json(old('sponsor_mode', $event->sponsor_mode ?? 'default')),
          installments_enabled: {{ $event->installments_enabled ? 'true' : 'false' }},
          installment_count: {{ (int) ($event->installment_count ?: 4) }},
          installment_final_days_before: {{ (int) ($event->installment_final_days_before ?? 14) }},
          installment_min_order_amount: @json($canSeeTicketData ? $event->installment_min_order_amount : null),
          // Last, so it wins: the Tickets tab's fields as they were typed before a refused save
          // ($ticketFieldsTyped). An empty object on an ordinary load.
          ...@json((object) $ticketFieldsTyped), // typed
        },
        // What each gateway can do, keyed by payment_method, so the Payment tab gates on capability
        // instead of naming gateways. Bound through data() and read with Vue's own interpolation
        // rather than echoed into a v-show attribute, which would not survive the mount.
        gatewayCapabilities: @json($gatewayCapabilities),
        initiallyHidden: @json($event->exists && ($event->is_draft || $event->is_private)),
        // Lets the visibility description line follow the hovered/focused pill and fall
        // back to the selected one, so a locked Enterprise option still explains itself.
        hoveredVisibility: null,
        // Participants, Agenda and Engagement are folded away on a first event ($moreSectionAttrs).
        showMoreSections: @json(! $isFirstEventRun),
        // Folded parts of the Event tab. Each starts open only when a refused save has an error inside it.
        aboutOpen: @json($aboutOpenOnLoad),
        showVenueContact: @json($venueContactOpenOnLoad),
        showVenueMore: @json($venueMoreOpenOnLoad),
        // The tabs a refused save has errors on, and the tabs with changes not yet saved.
        sectionErrors: @json($errorSectionIds),
        sectionDirty: {},
        dirtyArmed: false,
        confirmingDiscard: false,
        tabLabels: @json($tabLabels, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG),
        calendarSummary: @json($calendarSummary),
        aboutSummary: @json($aboutSummary),
        fanContentPending: @json($fanContentPendingCount ?? 0),
        eventIsSaved: @json($event->exists),
        // How the event is shown as SAVED. The save bar compares the choice on screen with it.
        savedVisibility: @json($event->exists ? $event->visibilityState() : null),
        savedVenueId: @json($savedVenueId),
        // Set by the page's date helpers (updateScheduleTimePreview), which live outside Vue.
        whenLabel: '',
        categoryLabel: '',
        alsoListedOn: [],
        eventLink: @json($eventEditUrl),
        linkCopied: false,
        // "Tickets elsewhere" pressed before a link is typed: the one choice with nothing saved to
        // read it back from. Never posted.
        externalChosen: @json($externalChosenNow),
        externalStash: null,
        sponsorFormOpen: @json($sponsorFormOpenNow),
        memberMoreOpen: false,
        partDescOpen: {},
        partsImportError: '',
        sponsorFormError: @json($sponsorFormOpenNow ? __('messages.sponsor_needs_logo') : ''),
        engagementSettings: @json($engagementSettingsNow),
        engagementInherited: @json($engagementInherited),
        savedSponsorCount: @json($event->exists && $event->sponsor_mode === 'custom' ? count(json_decode($event->sponsor_logos ?? '[]', true) ?: []) : 0),
        // Add-ons go when tickets are switched off, with their pictures, where this schedule's
        // plan has them (EventRepo::saveEvent, $ticketExtrasAllowed).
        savedExternal: @json($savedExternalNow),
        savedAddonCount: @json($event->exists && $canSeeTicketData && $role->isPro() ? ($event->addons ?? collect())->count() : 0),
        agendaImportOpen: false,
        agendaPromptOpen: false,
        ticketsTouched: false,
        // What is SAVED for tickets, for the save bar to compare the choice on screen against.
        savedTicketMode: @json($event->exists && $event->tickets_enabled ? 'tickets' : ($event->exists && $event->rsvp_enabled ? 'rsvp' : 'external')),
        savedTicketTypes: @json($event->exists && $canSeeTicketData ? $event->tickets->count() : 0),
        // Ticket descriptions opened by hand, by the row's uid. Never posted.
        ticketDescOpen: {},
        paymentLabels: @json(collect($selectableGateways)->map(fn ($gateway) => $gateway->label($user))),
        paymentWarning: @json($paymentRowWarning),
        // The Event tab is not shown to everyone who can open this form ($detailsShown).
        // An action that reloads the page was pressed while the form had unsaved changes.
        heldNotice: false,
        recentVenueIds: @json($recentVenueIds ?? []),
        // The tabs a first event keeps behind "More options", by name.
        moreTabNames: '',
        // Inline in place of the alert() it used to be: says what is missing under the fields
        // themselves, so it is still on screen while they are being filled in.
        dateTimeError: '',
        // Which field the message is about, so the end-date one sits under the end-date row.
        dateTimeErrorField: '',
        isPro: @json($role->isPro()),
        // old() first: a refused save comes back on the choice that was on screen, where its
        // error is, not on the saved one.
        ticketMode: @json(old('tickets_enabled', $event->tickets_enabled) ? 'tickets' : (old('rsvp_enabled', $event->rsvp_enabled) ? 'rsvp' : 'external')),
        venues: @json($venues),
        members: @json($members ?? []),
        venueType: "{{ count($venues) > 0 ? 'use_existing' : 'create_new' }}",
        memberType: "{{ 'use_existing' }}",
        venueName: @json($selectedVenue ? $selectedVenue->name : ''),
        venueEmail: @json($selectedVenue ? $selectedVenue->email : ''),
        venueAddress1: @json($selectedVenue ? $selectedVenue->address1 : ''),
        venueCity: @json($selectedVenue ? $selectedVenue->city : ''),
        venueState: @json($selectedVenue ? $selectedVenue->state : ''),
        venuePostalCode: @json($selectedVenue ? $selectedVenue->postal_code : ''),
        venueCountryCode: @json($selectedVenue ? $selectedVenue->country_code : ''),
        venueWebsite: @json($selectedVenue ? $selectedVenue->website : ''),
        venueSearchEmail: "",
        venueSearchResults: [],
        selectedVenue: @json($selectedVenue ? $selectedVenue->toData() : ""),
        roleIsVenue: {{ $role->isVenue() ? 'true' : 'false' }},
        roleIsTalent: {{ $role->isTalent() ? 'true' : 'false' }},
        roleEncodedId: '{{ \App\Utils\UrlUtils::encodeId($role->id) }}',
        selectedMembers: @json($selectedMembersNow),
        memberSearchResults: [],
        selectedMember: "",
        editMemberId: "",
        editMemberOriginalPhone: "",
        editMemberSnapshot: null,
        editMemberOriginalEmail: "",
        memberEmail: "",
        memberName: "",
        memberPhone: "",
        memberYoutubeUrl: "",
        showMemberTypeRadio: @json(empty($selectedMembers)),
        showVenueAddressFields: false,
        isInPerson: false,
        isOnline: false,
        eventName: @json($eventNameNow, $scriptSafe),
        // What was typed into a save the server refused, or null on an ordinary load. mounted()
        // would otherwise put the saved name (or, on a new event, the schedule's) over it.
        refusedEventName: @json(old('name')),
        startsAt: @json($oldStartsAt ?? ''),
        currentDuration: @json((string) ($oldDuration ?? '')),
        @php
        // Encoded coverage (group / event ids) per existing ticket, so the
        // subscription selectors round-trip encoded ids (decoded in EventRepo).
        $ticketPassCoverage = collect($canSeeTicketData ? ($event->tickets ?? []) : [])->mapWithKeys(fn ($t) => [$t->id => [
            'group' => $t->pass_scope_group_id ? \App\Utils\UrlUtils::encodeId($t->pass_scope_group_id) : '',
            'events' => collect($t->pass_event_ids ?? [])->map(fn ($id) => \App\Utils\UrlUtils::encodeId($id))->values()->all(),
        ]])->all();
        @endphp
        // [{id, name, bands[]}] for the schedule's plans, computed in the Tickets tab markup
        // above - Blade has already executed that block by the time this line runs.
        // (Do NOT write the Blade directive name in a comment here: Blade compiles it wherever
        // it appears, including inside JS comments, and the view then dies on a parse error.)
        seatingPlanOptions: @json($seatingPlanOptions ?? []),
        // The server's half of the paid-ticket paywall: this schedule cannot sell a priced row.
        // The other half, whether one exists, is ticketsNeedPro below. ?? because the tickets
        // section that sets it is not rendered for everyone who can open this form.
        cannotSellPaid: @json($cannotSellPaid ?? false),
        paywallSeenUrl: @json(route('subscription.paywall_seen', ['subdomain' => $subdomain])),
        paywallSeenSent: false,
        ticketTrialUrl: @json(isset($sellingRole) ? route('subscription.ticket_trial', ['subdomain' => $sellingRole->subdomain]) : null),
        ticketTrialStarting: false,
        ticketTrialMessage: '',
        ticketTrialError: '',
        tickets: @json($ticketsNow).map((ticket, i) => ({
          uid: i,
          ...ticket,
          volume_discount: ticket.volume_discount && typeof ticket.volume_discount === 'object' ? ticket.volume_discount : null,
          max_per_order: ticket.max_per_order ?? null,
          seating_band: ticket.seating_band ?? '',
          is_pass: !!ticket.is_pass,
          pass_usage_type: ticket.pass_usage_type || 'per_occurrence',
          pass_max_uses: ticket.pass_max_uses ?? null,
          pass_valid_days: ticket.pass_valid_days ?? null,
          pass_scope: ticket.pass_scope || 'this_event',
          pass_allow_booking: !!ticket.pass_allow_booking,
          pass_seats_per_occurrence: ticket.pass_seats_per_occurrence ?? null,
          pass_cancel_cutoff_hours: ticket.pass_cancel_cutoff_hours ?? '',
          pass_late_cancel_policy: ticket.pass_late_cancel_policy || 'forfeit',
          pass_admits_per_event: ticket.pass_admits_per_event ?? 1,
          // typed_coverage: what a refused save was sent, which beats what is stored.
          pass_scope_group_id: (ticket.typed_coverage || @json($ticketPassCoverage)[ticket.id] || ticket.pass_coverage || {}).group || '',
          pass_event_ids: (ticket.typed_coverage || @json($ticketPassCoverage)[ticket.id] || ticket.pass_coverage || {}).events || [],
          custom_fields: ticket.custom_fields || {},
          sales_start_at_date: ticket.sales_start_at ? ticket.sales_start_at.substring(0, 10) : '',
          sales_start_at_time: ticket.sales_start_at ? ticket.sales_start_at.substring(11, 16) : '',
          sales_end_at_date: ticket.sales_end_at ? ticket.sales_end_at.substring(0, 10) : '',
          sales_end_at_time: ticket.sales_end_at ? ticket.sales_end_at.substring(11, 16) : '',

          // Raw value, never Intl.NumberFormat: the input below is type="number", and a
          // locale-formatted string ("25,00", "1,500.00") is not a valid floating-point
          // number, so the browser's value sanitization blanks the field - on load and
          // again on every re-render. parseFloat also drops the decimal(13,3) trailing
          // zeros MySQL returns. Same shape as the add-on prices further down.
          // undefined too: a new event's blank ticket has no price key at all, and parseFloat of
          // that is NaN, which a number input refuses with a console warning.
          price: (ticket.price === null || ticket.price === undefined || ticket.price === '') ? null : parseFloat(ticket.price)
        })),
        ticketUidCounter: @json(max(1, count($ticketsNow))),
        eventCustomFields: @json($canSeeTicketData ? ($event->custom_fields ?? []) : []),
        showExpireUnpaid: @json($showExpireUnpaidNow),
        showSalesDates: @json($showSalesDatesNow),
        isInvoiceNinjaPaymentLink: @json($user->invoiceninja_api_key && $user->invoiceninja_mode === 'payment_link'),
        // Which row of the Tickets tab is open; "tickets" means none.
        activeTicketTab: @json($ticketTabOnError ?? 'tickets'),
        activeEngagementTab: (function() {
          // Deep-link support: the dashboard "Needs attention" list links here with
          // ?engagement=<tab> (plus #section-engagement, which the section nav already
          // opens on load) to land on the right Engagement sub-tab.
          // Read here, in data(), because the section script below strips the hash on load.
          return window.resolveEventSectionHash(window.location.hash, window.location.search).engagementTab;
        })(),
        sponsorForm: @json($sponsorFormNow),
        sponsorLogoPreview: null,
        sponsorLogoFile: null,
        editingSponsorIndex: -1,
        eventSponsorSortableInstance: null,
        eventSponsorFileCounter: 0,
        eventSponsors: @json($eventSponsorsWithUrls),
        maxSponsors: @json(config('app.max_sponsors')),
        // PHP's max_file_uploads defaults to 20, and every unsaved sponsor adds one file input
        // to the same POST. Warn well before that so uploads can't be silently dropped.
        maxPendingSponsorUploads: 15,
        promoCodes: (() => {
          var pcs = @json($promoCodesNow).map(pc => ({
            ...pc,
            value: pc.value ? parseFloat(pc.value) : pc.value,
            ticket_ids: pc.ticket_ids || [],
            is_active: pc.is_active ? true : false,
            expires_at_date: pc.expires_at ? pc.expires_at.substring(0, 10) : '',
            expires_at_time: pc.expires_at ? pc.expires_at.substring(11, 16) : '',
          }));
          // Not after a refused save: no codes then means the codes were taken off.
          if (pcs.length === 0 && ! @json($ticketExtrasTyped)) {
            var defaults = @json($defaultPromoCodes ?? []);
            if (defaults.length > 0) {
              pcs = defaults.map(pc => ({
                id: null, code: pc.code || '', type: pc.type || 'percentage',
                value: pc.value ? parseFloat(pc.value) : null,
                max_uses: pc.max_uses || null, times_used: 0,
                expires_at_date: '', expires_at_time: '',
                is_active: pc.is_active ? true : true, ticket_ids: pc.ticket_ids || [],
              }));
            }
          }
          return pcs;
        })(),
        addons: @json($addonsNow).map((addon, i) => ({
          id: addon.id || null,
          _key: addon.id ? ('existing_' + addon.id) : ('init_' + i),
          type: addon.type || '',
          quantity: addon.quantity ?? null,
          max_per_order: addon.max_per_order ?? null,
          price: addon.price ?? null,
          description: addon.description || '',
          image_url: addon.image_url || null,
          url: addon.url || '',
          remove_image: !! addon.remove_image,
        })),
        formSubmitAttempted: false,
        isSaving: false,
        // A page that came back from a refused save holds what was typed, none of it saved.
        isDirty: @json($errors->any()),
        galleryStore: galleryStore,
        galleryFanPhotos: @json($galleryFanPhotos),
        galleryUnsavedLabel: @json(__('messages.gallery_unsaved_event')),
        galleryRecurringText: @json(__('messages.gallery_recurring_hint')),
        galleryStripLabels: @json($galleryCountForms),
        galleryFinishingLabel: @json(__('messages.gallery_finishing')),
        // Set while a Save waits for uploads to finish (validateForm()).
        galleryWaiting: false,
        soldLabel: @json(__('messages.sold_reserved')),
        isRecurring: @json($event->days_of_week ? true : false),
        // Attendee change-notification UX (issue #94): confirm dialog on save when a key detail changed.
        registrantCount: @json($registrantCount ?? 0),
        interestedCount: @json($interestedCount ?? 0),
        scheduleHasEmailSettings: @json($scheduleHasEmailSettings ?? false),
        attendeesNotifiedAt: @json($attendeesNotifiedAt ?? null),
        notifyAttendees: false,
        notifyMessage: '',
        showNotifyModal: false,
        notifyConfirmed: false,
        showCancelModal: false,
        cancelMessage: '',
        isSubmittingCancel: false,
        isSubmittingRestore: false,
        origStartsAt: null,
        origDuration: null,
        origVenueId: null,
        origEventUrl: '',
        origIsOnline: false,
        origIsInPerson: false,
        passGroups: @json($passGroupOptions, $scriptSafe),
        passEvents: @json($passEventOptions, $scriptSafe),
        passEventSearch: {},
        isMultiDay: @json($isMultiDay),
        recurringIncludeDates: @json($event->recurring_include_dates ?? []),
        recurringExcludeDates: @json($event->recurring_exclude_dates ?? []),
        sendEmailToVenue: false,
        sendEmailToMembers: {},
        sendEmailToNewMember: false,
        sendSmsToVenue: false,
        sendSmsToMembers: {},
        sendSmsToNewMember: false,
        venuePhone: @json($selectedVenue ? $selectedVenue->phone : ''),
        smsConfigured: @json(\App\Services\SmsService::isConfigured() && config('app.hosted')),
        isHosted: @json(config('app.hosted')),
        phoneInputInstances: {},
        eventParts: @json($eventPartsNow).map((part, i) => ({
          uid: i,
          id: part.id || '',
          name: part.name || '',
          description: part.description || '',
          start_time: part.start_time || '',
          end_time: part.end_time || '',
        })),
        partUidCounter: @json(count($eventPartsNow)),
        parsingParts: false,
        parsedPartsPreview: [],
        showPartsPreview: false,
        showPartsTextInput: false,
        partsText: '',
        partsAiPrompt: @json($event->agenda_ai_prompt ?? $role->agenda_ai_prompt ?? ''),
        savePartsAiPromptDefault: false,
        // An agenda that has times opens showing them: starting from the schedule's last choice
        // opened such an event already set to remove them, which nobody had asked for.
        agendaShowTimes: @json($agendaShowTimesNow),
        agendaShowDescription: true,
        saveAgendaImage: @json($role->agenda_save_image ?? false),
        agendaImageUrl: @json($event->getAttributes()['agenda_image_url'] ?? ''),
        agendaImageFullUrl: @json($event->agenda_image_url ?: ''),
        partDragIndex: null,
        partDropTargetIndex: null,
        promoLinkBaseUrl: @json($event->exists ? $event->getGuestUrl($subdomain) : ''),
        partEditors: {},
        @php
            $pollsJson = $polls->map(function ($poll) {
                return [
                    'hash' => \App\Utils\UrlUtils::encodeId($poll->id),
                    'question' => $poll->question,
                    'options' => $poll->options,
                    'is_active' => $poll->is_active,
                    'allow_user_options' => $poll->allow_user_options,
                    'require_option_approval' => $poll->require_option_approval,
                    'pending_options' => $poll->pending_options ?? [],
                    'votes_count' => $poll->votes_count ?? 0,
                    'results' => $poll->getResults(),
                ];
            })->values();
            // After a refused save: the polls as they were being edited. One that has votes cannot
            // be edited, so it stays as stored.
            if (is_array(old('polls'))) {
                $storedPolls = $pollsJson->keyBy('hash');
                $pollsJson = collect(old('polls'))->map(function ($posted) use ($storedPolls) {
                    $stored = $storedPolls->get($posted['hash'] ?? '') ?? ['hash' => null, 'is_active' => true, 'pending_options' => [], 'votes_count' => 0, 'results' => []];
                    if (($stored['votes_count'] ?? 0) > 0) {
                        return $stored;
                    }
                    $postedOptions = json_decode($posted['options'] ?? '[]', true);

                    return array_merge($stored, [
                        'question' => (string) ($posted['question'] ?? ''),
                        'options' => is_array($postedOptions) ? array_values(array_map('strval', $postedOptions)) : [],
                        'allow_user_options' => (bool) ($posted['allow_user_options'] ?? false),
                        'require_option_approval' => (bool) ($posted['require_option_approval'] ?? false),
                    ]);
                })->values();
            }
        @endphp
        polls: @json($pollsJson),
        eventExists: @json($event->exists),
        pollSubmitting: false,
        pollMessage: '',
        pollError: '',
        pollLabelActive: @json(__('messages.poll_active')),
        pollLabelClosed: @json(__('messages.poll_closed_status')),
        pollLabelClose: @json(__('messages.close_poll')),
        pollLabelReopen: @json(__('messages.reopen_poll')),
      }
    },
    methods: {
      // The card-free selling trial, started from the paywall banner. On success the schedule can
      // sell, so the banner goes (cannotSellPaid) and the price the organizer typed stays put.
      startTicketTrial() {
        if (this.ticketTrialStarting || ! this.ticketTrialUrl) {
          return;
        }
        this.ticketTrialStarting = true;
        this.ticketTrialError = '';
        const failed = @json(__('messages.something_went_wrong'));
        fetch(this.ticketTrialUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': @json(csrf_token()),
          },
          body: JSON.stringify({ source: 'tickets' }),
        })
          .then(response => response.json().then(data => ({ ok: response.ok && data.ok, data })))
          .then(({ ok, data }) => {
            if (ok) {
              this.cannotSellPaid = false;
              this.ticketTrialMessage = data.message;
            } else {
              this.ticketTrialError = data.message || failed;
            }
          })
          .catch(() => {
            this.ticketTrialError = failed;
          })
          .finally(() => {
            this.ticketTrialStarting = false;
          });
      },
      /** "Stalls (72 seats)" - the band select was names alone, so bands were priced blind. */
      bandOptionLabel(band) {
        const plan = this.selectedSeatingPlan;
        const count = plan && plan.bandCounts ? plan.bandCounts[band] : 0;

        return count
          ? band + ' (' + count + ' ' + @json(__('messages.seating_seats')).toLowerCase() + ')'
          : band;
      },

      previewEventSponsorLogo(e) {
        var file = e.target.files[0];
        if (!file) {
          this.sponsorLogoPreview = null;
          this.sponsorLogoFile = null;
          return;
        }
        this.sponsorLogoFile = file;
        var reader = new FileReader();
        reader.onload = (ev) => { this.sponsorLogoPreview = ev.target.result; };
        reader.readAsDataURL(file);
      },
      addOrSaveEventSponsor() {
        var name = (this.sponsorForm.name || '').trim();
        var url = (this.sponsorForm.url || '').trim();
        var tier = this.sponsorForm.tier || '';

        if (this.editingSponsorIndex >= 0) {
          // Editing existing
          var sponsor = this.eventSponsors[this.editingSponsorIndex];
          sponsor.name = name;
          sponsor.url = url || null;
          sponsor.tier = tier;
          if (this.sponsorLogoFile) {
            sponsor.logo_url = this.sponsorLogoPreview;
            sponsor.newFile = this.sponsorLogoFile;
          }
          this.cancelEditEventSponsor();
        } else {
          // Adding new. A logo is what a sponsor is shown by: say so, where this used to do nothing.
          if (!this.sponsorLogoFile) {
            this.sponsorFormError = this.tabLabels.sponsor_needs_logo;
            return false;
          }
          if (this.eventSponsors.length >= this.maxSponsors) return false;
          this.eventSponsors.push({
            name: name,
            logo: '',
            logo_url: this.sponsorLogoPreview,
            url: url || null,
            tier: tier,
            newFile: this.sponsorLogoFile,
          });
          this.resetSponsorForm();
          this.sponsorFormOpen = false;
        }
        this.syncEventSponsorInputs();
        this.markTabDirty('section-event-settings');

        return true;
      },
      editEventSponsor(index) {
        this.editingSponsorIndex = index;
        var sponsor = this.eventSponsors[index];
        this.sponsorForm.name = sponsor.name || '';
        this.sponsorForm.url = sponsor.url || '';
        this.sponsorForm.tier = sponsor.tier || '';
        this.sponsorLogoPreview = null;
        this.sponsorLogoFile = null;
        if (this.$refs.eventSponsorLogoInput) this.$refs.eventSponsorLogoInput.value = '';
      },
      removeEventSponsor(index) {
        if (this.editingSponsorIndex === index) {
          this.cancelEditEventSponsor();
        } else if (this.editingSponsorIndex > index) {
          this.editingSponsorIndex--;
        }
        this.eventSponsors.splice(index, 1);
        this.syncEventSponsorInputs();
      },
      cancelEditEventSponsor() {
        this.editingSponsorIndex = -1;
        this.resetSponsorForm();
      },
      resetSponsorForm() {
        this.sponsorFormError = '';
        this.sponsorForm = { name: '', url: '', tier: '' };
        this.sponsorLogoPreview = null;
        this.sponsorLogoFile = null;
        if (this.$refs.eventSponsorLogoInput) this.$refs.eventSponsorLogoInput.value = '';
      },
      syncEventSponsorInputs() {
        // Rebuild hidden file inputs for new/changed logos
        var container = document.getElementById('new-event-sponsor-inputs-container');
        if (!container) return;
        container.innerHTML = '';
        var existing = [];
        var newIdx = 0;
        this.eventSponsors.forEach((sponsor) => {
          if (sponsor.newFile) {
            var dt = new DataTransfer();
            dt.items.add(sponsor.newFile);
            var fileInput = document.createElement('input');
            fileInput.type = 'file';
            fileInput.name = 'event_sponsor_logos[' + newIdx + ']';
            fileInput.style.display = 'none';
            fileInput.files = dt.files;
            container.appendChild(fileInput);

            var nameInput = document.createElement('input');
            nameInput.type = 'hidden';
            nameInput.name = 'event_sponsor_names[' + newIdx + ']';
            nameInput.value = sponsor.name || '';
            container.appendChild(nameInput);

            var urlInput = document.createElement('input');
            urlInput.type = 'hidden';
            urlInput.name = 'event_sponsor_urls[' + newIdx + ']';
            urlInput.value = sponsor.url || '';
            container.appendChild(urlInput);

            var tierInput = document.createElement('input');
            tierInput.type = 'hidden';
            tierInput.name = 'event_sponsor_tiers[' + newIdx + ']';
            tierInput.value = sponsor.tier || '';
            container.appendChild(tierInput);
            newIdx++;
          } else {
            existing.push({ name: sponsor.name || '', logo: sponsor.logo || '', url: sponsor.url || null, tier: sponsor.tier || '' });
          }
        });
        // Update the hidden existing sponsors input via Vue data (bound in template)
      },
      initEventSponsorSortable() {
        var el = document.getElementById('event-sponsors-list');
        if (el && typeof Sortable !== 'undefined') {
          if (this.eventSponsorSortableInstance) {
            this.eventSponsorSortableInstance.destroy();
          }
          this.eventSponsorSortableInstance = Sortable.create(el, {
            handle: '.drag-handle',
            animation: 150,
            ghostClass: 'opacity-50',
            onEnd: (evt) => {
              var item = this.eventSponsors.splice(evt.oldIndex, 1)[0];
              this.eventSponsors.splice(evt.newIndex, 0, item);
              this.syncEventSponsorInputs();
            }
          });
        }
      },
      showBoostError() {
        alert(this.boostDynamicReason);
      },
      // The venue's three address buttons. The work stays in the page helpers; these exist so the
      // buttons are wired by the template that renders them (see the note beside the buttons).
      viewVenueMap() {
        viewMap();
      },
      validateVenueAddress() {
        onValidateClick();
      },
      acceptVenueAddress(event) {
        acceptAddress(event);
      },
      // A change anywhere in the form: remember which tab it was on.
      markDirty(el) {
        var section = el && el.closest ? el.closest('.section-content') : null;
        if (section && section.id) {
          this.sectionDirty[section.id] = true;
          this.clearTabError(section.id);
        }
        this.isDirty = true;
      },
      markTabDirty(sectionId) {
        if (! this.dirtyArmed) {
          return;
        }
        this.sectionDirty[sectionId] = true;
        this.clearTabError(sectionId);
        this.isDirty = true;
      },
      // A tab a refused save pointed at stops being pointed at once something on it is changed.
      // The list was never emptied, so the bar read "Check: ..." for as long as the page was
      // open, which also kept every "Saving removes" line off it.
      clearTabError(sectionId) {
        var at = this.sectionErrors.indexOf(sectionId);
        if (at === -1) {
          return;
        }
        this.sectionErrors.splice(at, 1);
        document.querySelectorAll('.section-nav-link[data-section="' + sectionId + '"], .mobile-section-header[data-section="' + sectionId + '"]').forEach(function (tab) {
          tab.classList.remove('validation-error');
        });
      },
      goToTab(sectionId) {
        window.showEventSection(sectionId);
        window.scrollTo({ top: 0 });
      },
      // Put the caret on one of the Event tab's fields.
      focusBasics(field) {
        this.$nextTick(() => {
          var el = null;
          if (field === 'name') {
            el = document.getElementById('event_name');
          } else if (field === 'date') {
            var date = document.getElementById('event_date');
            el = date && date._flatpickr && date._flatpickr.altInput ? date._flatpickr.altInput : date;
          }
          if (el) {
            el.focus();
            if (el.scrollIntoView) { el.scrollIntoView({ block: 'center' }); }
          }
        });
      },
      // A field the browser refuses the save over may be folded away; unfold what holds it.
      revealField(el) {
        if (! el || ! el.closest) { return; }
        if (el.closest('#event-about-body')) { this.aboutOpen = true; }
        // A row of the Tickets tab that is closed: open it. The ticket types themselves are
        // always showing ("tickets" is the name for no row open).
        var pane = el.closest('[data-ticket-pane]');
        if (pane) { this.activeTicketTab = pane.getAttribute('data-ticket-pane'); }
        // The same for a row of the Engagement tab.
        var row = el.closest('[data-engagement-pane]');
        if (row) { this.activeEngagementTab = row.getAttribute('data-engagement-pane'); }
      },
      copyEventLink() {
        var done = () => {
          this.linkCopied = true;
          setTimeout(() => { this.linkCopied = false; }, 2000);
        };
        if (navigator.clipboard && navigator.clipboard.writeText) {
          navigator.clipboard.writeText(this.eventLink).then(done).catch(function () {});
        }
      },
      // The three tiles at the top of the Tickets tab, and "Not needed" (null). They set the
      // tab's own mode. A tile that is already on does nothing: switching off is "Not needed"
      // and nothing else, because off is not a small thing (below).
      chooseTickets(choice) {
        var was = this.ticketChoice;
        if (choice === was) {
          return;
        }
        // Saving with tickets off removes the event's ticket types (EventRepo::saveEvent), and
        // changing how sign-up works under people who have already signed up is not something to
        // do by a slip of the finger. Ask, when anybody has.
        if ((was === 'tickets' || was === 'rsvp') && this.registrantCount > 0 && ! window.confirm(this.tabLabels.signups_confirm)) {
          return;
        }
        var e = this.event;
        if (was === 'external') {
          // Kept aside, so pressing the tile again brings them back.
          this.externalStash = { registration_url: e.registration_url, ticket_price: e.ticket_price, coupon_code: e.coupon_code, coupon_discount: e.coupon_discount };
          // The other choices send these along from hidden fields the browser does not check, so
          // one left half-typed here ("not a link") goes back to what the event was saved with:
          // nobody could see it there to fix it. The tile brings what was typed back.
          if (choice) {
            var saved = this.savedExternal;
            document.querySelectorAll('#section-tickets fieldset.event-fieldset [name]').forEach(function (field) {
              if (Object.prototype.hasOwnProperty.call(saved, field.name) && ! field.checkValidity()) {
                e[field.name] = saved[field.name];
              }
            });
          }
        }
        this.externalChosen = choice === 'external';
        if (choice === 'tickets' || choice === 'rsvp') {
          this.ticketMode = choice;
        } else {
          this.ticketMode = 'external';
          if (choice === 'external' && this.externalStash) {
            Object.assign(e, this.externalStash);
          } else if (! choice) {
            // Off means off: a link or a price left behind would still be shown to guests.
            e.registration_url = '';
            e.ticket_price = null;
            e.coupon_code = '';
            e.coupon_discount = null;
          }
        }
        this.ticketsTouched = true;
        this.sectionDirty['section-tickets'] = true;
        this.isDirty = true;
        var selector = { tickets: '#section-tickets [name="tickets[0][price]"]', external: '#registration_url', rsvp: '#rsvp_limit' }[choice];
        if (selector) {
          this.$nextTick(function () {
            var field = document.querySelector(selector);
            if (field) { field.focus({ preventScroll: true }); }
          });
        }
      },
      // An action that reloads the page (approving a fan photo, removing a carpool offer, a calendar
      // sync) would take the form's unsaved changes with it. While there are any, it waits, and the
      // save bar says why.
      holdAction() {
        this.heldNotice = true;
        clearTimeout(this._heldTimer);
        this._heldTimer = setTimeout(() => { this.heldNotice = false; }, 5000);
      },
      // Enter in the name of an event with no date yet goes on to the date. It used to submit the
      // form, and the first reply a new organizer got was a date error.
      onNameEnter(e) {
        var date = document.getElementById('event_date');
        if (date && ! date.value) {
          e.preventDefault();
          this.focusBasics('date');
        }
      },
      toggleTicketRow(tab) {
        this.activeTicketTab = this.activeTicketTab === tab ? 'tickets' : tab;
      },
      openTicketDescription(ticket) {
        this.ticketDescOpen[ticket.uid] = true;
      },
      // tickets.sold is a JSON map of date to count.
      soldCount(ticket) {
        if (! ticket || ! ticket.sold) { return 0; }
        try {
          var map = typeof ticket.sold === 'string' ? JSON.parse(ticket.sold) : ticket.sold;
          return Object.values(map || {}).reduce(function (n, v) { return n + (parseInt(v, 10) || 0); }, 0);
        } catch (e) {
          return 0;
        }
      },
      // Cancel. With nothing changed it leaves at once; otherwise the bar asks first, because
      // leaving through Cancel skips the browser's own "unsaved changes" warning.
      cancelEdit() {
        if (this.isDirty) {
          this.confirmingDiscard = true;
          return;
        }
        this.discardAndLeave();
      },
      discardAndLeave() {
        document.getElementById('event-cancel-real').click();
      },
      // Read what the tab summaries need from fields that are not Vue's.
      readPlainFields() {
        var category = document.getElementById('category_id');
        this.categoryLabel = category && category.value ? category.options[category.selectedIndex].text.trim() : '';
        this.alsoListedOn = Array.prototype.map.call(document.querySelectorAll('input[name="curators[]"]:checked'), function (box) {
          var label = document.querySelector('label[for="' + box.id + '"]');
          return label ? label.textContent.trim() : '';
        }).filter(Boolean);
        var short = document.getElementById('short_description');
        if (short && short.value.trim()) {
          this.aboutSummary = short.value.trim();
        }
      },
      // Unfold the venue's contact fields, where matching a venue by email or phone happens. The
      // phone widget measures itself when it is set up, and it was set up while folded.
      openVenueContact() {
        this.showVenueContact = true;
        this.$nextTick(() => {
          this.destroyPhoneInput('venue_phone_input');
          this.initPhoneInput('venue_phone_input', (number) => { this.venuePhone = number; }, this.venuePhone);
          var email = document.getElementById('venue_email');
          if (email) { email.focus(); }
        });
      },
      openVenueMore() {
        this.showVenueMore = true;
        this.$nextTick(() => {
          if (document.getElementById('venue_country_code_tel')) {
            if (typeof window.destroyCountryInput === 'function') {
              window.destroyCountryInput('venue_country_code');
            }
            window.initCountryInput('venue_country_code', this.venueCountryCode);
            this.bindVenueCountryChange();
          }
        });
      },
      clearSelectedVenue() {
        this.selectedVenue = "";
      },
      editSelectedVenue() {
        this.showVenueAddressFields = true;
        // An existing venue's details are all shown: folding away an email it already has would
        // hide the one thing being checked.
        this.showVenueContact = true;
        this.showVenueMore = true;

        this.$nextTick(() => {
            if (typeof window.destroyCountryInput === 'function') {
                window.destroyCountryInput('venue_country_code');
            }
            var ci = window.initCountryInput('venue_country_code', this.venueCountryCode);
            if (ci) ci.setCountry(this.venueCountryCode);
            this.bindVenueCountryChange();
        });
      },
      updateSelectedVenue() {
        this.showVenueAddressFields = false;
      },
      bindVenueCountryChange() {
        var hidden = document.getElementById('venue_country_code');
        if (!hidden || hidden.dataset.vueBound === '1') return;
        hidden.addEventListener('change', () => {
          this.venueCountryCode = hidden.value;
        });
        hidden.dataset.vueBound = '1';
      },
      searchVenues() {
        if (! this.venueEmail) {
          return;
        }

        const emailInput = document.getElementById('venue_email');
        
        if (!emailInput.checkValidity()) {
          emailInput.reportValidity();
          return;
        }

        fetch(`{{ url('/search-roles') }}?type=venue&search=${encodeURIComponent(this.venueEmail)}`, {
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
          }
        })
        .then(response => {
          if (!response.ok) throw new Error('Request failed');
          return response.json();
        })
        .then(data => {
          this.venueSearchResults = data;
        })
        .catch(error => {
          console.error('Error searching venues:', error);
        });
      },
      searchVenueByPhone() {
        if (!this.venuePhone || this.venuePhone.length < 8) {
          return;
        }

        fetch(`{{ url('/search-roles') }}?type=venue&search=${encodeURIComponent(this.venuePhone)}`, {
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
          }
        })
        .then(response => {
          if (!response.ok) throw new Error('Request failed');
          return response.json();
        })
        .then(data => {
          this.venueSearchResults = data;
        })
        .catch(error => {
          console.error('Error searching venues:', error);
        });
      },
      selectVenue(venue) {
        this.selectedVenue = venue;
        this.venueName = venue.name;
        this.venueEmail = venue.email;
        this.venuePhone = venue.phone || '';
        this.venueAddress1 = venue.address1;
        this.venueCity = venue.city;
        this.venueState = venue.state;
        this.venuePostalCode = venue.postal_code;
        this.venueCountryCode = venue.country_code;
        this.venueWebsite = venue.website;
      },
      setFocusBasedOnVenueType() {
        this.$nextTick(() => {
          if (this.venueType === 'create_new') {
            const venueNameInput = document.getElementById('venue_name');
            if (venueNameInput) {
              venueNameInput.focus();
            }
          }
        });
      },
      showAddressFields() {
        return (this.venueType === 'use_existing' && this.selectedVenue && ! this.selectedVenue.user_id) 
            || this.venueType === 'create_new';
      },
      searchMembers() {
        if (! this.memberEmail) {
          return;
        }

        const emailInput = document.getElementById('member_email');

        if (!emailInput.checkValidity()) {
          emailInput.reportValidity();
          return;
        }

        this.searchMember(this.memberEmail);
      },
      searchByPhone() {
        if (!this.memberPhone || this.memberPhone.length < 8) {
          return;
        }

        this.searchMember(this.memberPhone);
      },
      searchMember(search) {
        fetch(`{{ url('/search-roles') }}?type=member&search=${encodeURIComponent(search)}`, {
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
          }
        })
        .then(response => {
          if (!response.ok) throw new Error('Request failed');
          return response.json();
        })
        .then(data => {
          this.memberSearchResults = data;
        })
        .catch(error => {
          console.error('Error searching members:', error);
        });
      },
      selectMember(member) {
        if (! this.selectedMembers.some(m => m.id === member.id)) {
          this.selectedMembers.push(member);
          if (!member.user_id) {
            if (member.email) {
              this.sendEmailToMembers[member.email] = false;
            } else if (member.phone) {
              this.sendSmsToMembers[member.phone] = false;
            }
          }
        }
        this.memberSearchResults = [];
        this.memberEmail = "";
        this.memberName = "";
        this.memberPhone = "";
        this.memberYoutubeUrl = "";
        this.sendEmailToNewMember = false;
        this.sendSmsToNewMember = false;
        this.clearNewMemberPhone();
        this.memberMoreOpen = false;
        this.showMemberTypeRadio = false;
      },
      removeMember(member) {
        if (this.roleIsTalent && member.id === this.roleEncodedId) {
          return;
        }
        if (this.editMemberId === member.id) {
          this.destroyPhoneInput('edit_member_phone_' + member.id);
          this.editMemberId = "";
        }
        this.selectedMembers = this.selectedMembers.filter(m => m.id !== member.id);
        // Remove from sendEmailToMembers/sendSmsToMembers
        if (member.email && this.sendEmailToMembers[member.email] !== undefined) {
          delete this.sendEmailToMembers[member.email];
        }
        if (member.phone && this.sendSmsToMembers[member.phone] !== undefined) {
          delete this.sendSmsToMembers[member.phone];
        }
        if (this.selectedMembers.length === 0) {
          this.showMemberTypeRadio = true;
        }
      },
      migrateEditedMemberPreferences() {
        var editedMember = this.selectedMembers.find(m => m.id === this.editMemberId);
        if (!editedMember) return;
        if (this.editMemberOriginalPhone && editedMember.phone !== this.editMemberOriginalPhone) {
          if (this.sendSmsToMembers[this.editMemberOriginalPhone] !== undefined) {
            if (editedMember.phone) {
              this.sendSmsToMembers[editedMember.phone] = this.sendSmsToMembers[this.editMemberOriginalPhone];
            }
            delete this.sendSmsToMembers[this.editMemberOriginalPhone];
          }
        }
        if (this.editMemberOriginalEmail && editedMember.email !== this.editMemberOriginalEmail) {
          if (this.sendEmailToMembers[this.editMemberOriginalEmail] !== undefined) {
            if (editedMember.email) {
              this.sendEmailToMembers[editedMember.email] = this.sendEmailToMembers[this.editMemberOriginalEmail];
            }
            delete this.sendEmailToMembers[this.editMemberOriginalEmail];
          }
        }
      },
      editMember(member) {
        if (member) {
          if (this.editMemberId && this.editMemberId !== member.id) {
            this.closeMemberEdit();
          }
          // The add form closes for the edit, unless something is typed into it: that stays, and
          // Save still adds it.
          if (! this.pendingMember) {
            this.showMemberTypeRadio = false;
          }
          this.editMemberId = member.id;
          this.editMemberSnapshot = { name: member.name, email: member.email, phone: member.phone, youtube_url: member.youtube_url };
          this.editMemberOriginalPhone = member.phone || "";
          this.editMemberOriginalEmail = member.email || "";
          this.$nextTick(() => {
            const memberNameInput = document.getElementById(`edit_member_name_${member.id}`);
            if (memberNameInput) {
              memberNameInput.focus();
            }
            var editPhoneId = 'edit_member_phone_' + member.id;
            var memberData = this.selectedMembers.find(m => m.id === member.id);
            this.initPhoneInput(editPhoneId, (number) => { memberData.phone = number; }, memberData.phone);
          });
        } else {
           const memberNameInput = document.getElementById(`edit_member_name_${this.editMemberId}`);
           if (memberNameInput && !memberNameInput.checkValidity()) {
            memberNameInput.reportValidity();
            return;
          }

          const emailInput = document.getElementById(`edit_member_email_${this.editMemberId}`);
          if (emailInput && emailInput.value && !emailInput.checkValidity()) {
            emailInput.reportValidity();
            return;
          }

          const youtubeInput = document.getElementById(`edit_member_youtube_url_${this.editMemberId}`);
          if (youtubeInput && youtubeInput.value && !youtubeInput.checkValidity()) {
            youtubeInput.reportValidity();
            return;
          }

          this.closeMemberEdit();
        }
      },
      // Close the row being edited, keeping what was typed. A name left empty goes back to what it
      // was: the field is only required while its row is open, so an empty one could be posted by
      // pressing another row's Edit.
      closeMemberEdit() {
        var member = this.selectedMembers.find(m => m.id === this.editMemberId);
        if (member && ! (member.name || '').trim() && this.editMemberSnapshot) {
          member.name = this.editMemberSnapshot.name;
        }
        this.migrateEditedMemberPreferences();
        this.destroyPhoneInput('edit_member_phone_' + this.editMemberId);
        this.editMemberId = "";
        this.editMemberSnapshot = null;
      },
      // Leave the row being edited as it was before Edit was pressed.
      cancelEditMember() {
        var member = this.selectedMembers.find(m => m.id === this.editMemberId);
        if (member && this.editMemberSnapshot) {
          Object.assign(member, this.editMemberSnapshot);
        }
        this.destroyPhoneInput('edit_member_phone_' + this.editMemberId);
        this.editMemberId = "";
        this.editMemberSnapshot = null;
      },
      addExistingMember() {
        if (this.selectedMember && !this.selectedMembers.some(m => m.id === this.selectedMember.id)) {
          this.selectedMembers.push(this.selectedMember);
          // Initialize sendEmailToMembers/sendSmsToMembers for selected member
          if (!this.selectedMember.user_id) {
            if (this.selectedMember.email) {
              this.sendEmailToMembers[this.selectedMember.email] = false;
            } else if (this.selectedMember.phone) {
              this.sendSmsToMembers[this.selectedMember.phone] = false;
            }
          }
          this.$nextTick(() => {
            this.selectedMember = "";
          });
          this.showMemberTypeRadio = false;
        }
      },
      addMember() {
        // Check if name is empty (since we can't use HTML required attribute)
        if (!this.memberName.trim()) {
          const nameInput = document.getElementById('member_name');
          nameInput.focus();
          return false;
        }

        const nameInput = document.getElementById('member_name');
        if (!nameInput.checkValidity()) {
          nameInput.reportValidity();
          return false;
        }

        const emailInput = document.getElementById('member_email');    
        if (!emailInput.checkValidity()) {
          emailInput.reportValidity();
          return false;
        }

        const youtubeInput = document.getElementById('member_youtube_url');
        if (youtubeInput && youtubeInput.value && !youtubeInput.checkValidity()) {
          youtubeInput.reportValidity();
          return false;
        }

        const newMember = {
          id: 'new_' + Date.now(),
          name: this.memberName,
          email: this.memberEmail,
          phone: this.memberPhone,
          youtube_url: this.memberYoutubeUrl,
        };

        this.selectedMembers.push(newMember);
        // Initialize sendEmailToMembers/sendSmsToMembers for new member using the checkbox value
        if (newMember.email) {
          this.sendEmailToMembers[newMember.email] = this.sendEmailToNewMember;
        } else if (newMember.phone) {
          this.sendSmsToMembers[newMember.phone] = this.sendSmsToNewMember;
        }
        this.memberSearchResults = [];
        this.memberName = "";
        this.memberEmail = "";
        this.memberPhone = "";
        this.memberYoutubeUrl = "";
        this.sendEmailToNewMember = false;
        this.sendSmsToNewMember = false;
        this.clearNewMemberPhone();
        this.memberMoreOpen = false;
        this.showMemberTypeRadio = false;
        this.markTabDirty('section-participants');

        return true;
      },
      // The add form's phone field is wired once and kept (it sits under v-show): emptied, never
      // destroyed. Destroying and rewiring it per state is how it came to be unwired in three.
      clearNewMemberPhone() {
        var instance = this.phoneInputInstances['member_phone_input'];
        if (instance) {
          instance.iti.setNumber('');
        }
        this.memberPhone = "";
      },

      setFocusBasedOnMemberType() {
        this.$nextTick(() => {
          if (this.memberType === 'create_new') {
            const nameInput = document.getElementById('member_name');
            if (nameInput) {
              nameInput.focus();
            }
          }
        });
      },
      cancelAddMember() {
        this.memberName = "";
        this.memberEmail = "";
        this.memberYoutubeUrl = "";
        this.memberSearchResults = [];
        this.sendEmailToNewMember = false;
        this.sendSmsToNewMember = false;
        this.clearNewMemberPhone();
        this.memberMoreOpen = false;
        this.showMemberTypeRadio = false;
      },
      showAddMemberForm() {
        if (this.editMemberId) {
          this.closeMemberEdit();
        }
        this.showMemberTypeRadio = true;
        if (this.filteredMembers.length === 0) {
          this.memberType = 'create_new';
        }
        this.setFocusBasedOnMemberType();
      },
      initPhoneInput(inputId, callback, initialValue) {
        var input = document.getElementById(inputId);
        if (!input || this.phoneInputInstances[inputId]) return;
        var iti = window.intlTelInput(input, {
          utilsScript: '{{ asset('vendor/intl-tel-input/js/utils.js') }}',
          initialCountry: '{{ strtolower($role->country_code ?? 'us') }}',
          separateDialCode: true,
          strictMode: true,
          nationalMode: false,
          autoPlaceholder: 'off',
        });
        var wrapper = input.closest('.iti');
        if (wrapper) {
          wrapper.style.setProperty('display', 'block', 'important');
          wrapper.style.setProperty('width', '100%', 'important');
        }
        if (initialValue) {
          iti.setNumber(initialValue);
        }
        var updateFn = function() {
          callback(iti.getNumber() || '');
        };
        input.addEventListener('change', updateFn);
        input.addEventListener('input', updateFn);
        input.addEventListener('countrychange', updateFn);
        this.phoneInputInstances[inputId] = { iti: iti, updateFn: updateFn };
      },
      destroyPhoneInput(inputId) {
        var instance = this.phoneInputInstances[inputId];
        if (instance) {
          var input = document.getElementById(inputId);
          if (input && instance.updateFn) {
            input.removeEventListener('change', instance.updateFn);
            input.removeEventListener('input', instance.updateFn);
            input.removeEventListener('countrychange', instance.updateFn);
          }
          instance.iti.destroy();
          delete this.phoneInputInstances[inputId];
        }
      },
      clearEventUrl() {
        this.event.event_url = "";
      },
      addPart() {
        const newPart = { uid: this.partUidCounter++, id: '', name: '', description: '', start_time: '', end_time: '' };
        this.eventParts.push(newPart);
        this.initPartEditor(newPart);
      },
      removePart(index) {
        const part = this.eventParts[index];
        this.destroyPartEditor(part);
        this.eventParts.splice(index, 1);
      },
      formatPartTime(time) {
        if (!time) return '';
        var minutes = parseTimeToMinutes(time);
        if (minutes === null) return time;
        return formatMinutesToTime(minutes);
      },
      onPartTimeChange(index, field, event) {
        var minutes = parseTimeToMinutes(event.target.value);
        if (minutes !== null) {
          var h = Math.floor(minutes / 60);
          var m = minutes % 60;
          this.eventParts[index][field] = (h < 10 ? '0' : '') + h + ':' + (m < 10 ? '0' : '') + m;
          event.target.value = formatMinutesToTime(minutes);
        } else {
          this.eventParts[index][field] = '';
        }
      },
      initPartTimePickerOnFocus(event, uid, type) {
        var inputEl = event.target;
        var dropdownRef = 'part_' + type + '_dropdown_' + uid;
        var dropdownEl = this.$refs[dropdownRef];
        if (Array.isArray(dropdownEl)) {
          dropdownEl = dropdownEl[0];
        }
        if (dropdownEl && !inputEl._timepickerInit) {
          initPartTimePicker(inputEl, dropdownEl);
        }
      },
      movePartUp(index) {
        if (index > 0) {
          const temp = this.eventParts[index];
          this.eventParts.splice(index, 1);
          this.eventParts.splice(index - 1, 0, temp);
        }
      },
      movePartDown(index) {
        if (index < this.eventParts.length - 1) {
          const temp = this.eventParts[index];
          this.eventParts.splice(index, 1);
          this.eventParts.splice(index + 1, 0, temp);
        }
      },
      onPartDragStart(index) {
        this.partDragIndex = index;
      },
      onPartDragOver(index, event) {
        event.preventDefault();
        if (this.partDragIndex === null) return;
        const rect = event.currentTarget.getBoundingClientRect();
        const midpoint = rect.top + rect.height / 2;
        const target = event.clientY < midpoint ? index : index + 1;
        if (target === this.partDragIndex || target === this.partDragIndex + 1) {
          this.partDropTargetIndex = null;
        } else {
          this.partDropTargetIndex = target;
        }
      },
      onPartDrop() {
        if (this.partDragIndex === null || this.partDropTargetIndex === null) {
          this.partDragIndex = null;
          this.partDropTargetIndex = null;
          return;
        }
        const item = this.eventParts.splice(this.partDragIndex, 1)[0];
        const insertAt = this.partDropTargetIndex > this.partDragIndex ? this.partDropTargetIndex - 1 : this.partDropTargetIndex;
        this.eventParts.splice(insertAt, 0, item);
        this.partDragIndex = null;
        this.partDropTargetIndex = null;
      },
      onPartDragEnd() {
        this.partDragIndex = null;
        this.partDropTargetIndex = null;
      },
      onContainerPartDragOver(event) {
        if (this.partDragIndex === null || this.eventParts.length === 0) return;
        const container = event.currentTarget;
        const firstChild = container.children[0];
        if (firstChild) {
          const rect = firstChild.getBoundingClientRect();
          if (event.clientY < rect.top + rect.height / 2) {
            if (0 !== this.partDragIndex && 0 !== this.partDragIndex + 1) {
              this.partDropTargetIndex = 0;
            } else {
              this.partDropTargetIndex = null;
            }
          }
        }
      },
      openPartDescription(part) {
        this.partDescOpen[part.uid] = true;
        this.$nextTick(() => {
          var editor = this.partEditors[part.uid];
          var field = this.$refs['partDescription_' + part.uid];
          if (editor && editor.codemirror) {
            editor.codemirror.focus();
          } else if (field && field[0]) {
            field[0].focus();
          }
        });
      },
      initPartEditor(part) {
        if (!this.agendaShowDescription) return;
        this.$nextTick(() => {
          const textarea = this.$refs['partDescription_' + part.uid];
          if (textarea && textarea[0] && !this.partEditors[part.uid] && window.initTinyMDE) {
            this.partEditors[part.uid] = window.initTinyMDE(textarea[0], () => {
              part.description = this.partEditors[part.uid].value();
            });
          }
        });
      },
      destroyPartEditor(part) {
        if (this.partEditors[part.uid]) {
          this.partEditors[part.uid]._stopEditorObserver?.();
          this.partEditors[part.uid].toTextArea();
          delete this.partEditors[part.uid];
        }
      },
      initAllPartEditors() {
        // A description that has text shows; an empty one waits behind its link.
        this.eventParts.forEach(part => {
          if (part.description) {
            this.partDescOpen[part.uid] = true;
          }
        });
        if (this.agendaShowDescription) {
          this.eventParts.forEach(part => this.initPartEditor(part));
        }
      },
      destroyAllPartEditors() {
        this.eventParts.forEach(part => this.destroyPartEditor(part));
      },
      parsePartsFromImage(event) {
        const file = event.target.files[0];
        if (!file) return;
        this.parsingParts = true;
        this.partsImportError = '';
        const formData = new FormData();
        formData.append('parts_image', file);
        formData.append('ai_prompt', this.partsAiPrompt);
        formData.append('save_ai_prompt_default', this.savePartsAiPromptDefault ? '1' : '0');
        formData.append('save_agenda_image', this.saveAgendaImage ? '1' : '0');
        @if($event->exists)
        formData.append('event_id', '{{ \App\Utils\UrlUtils::encodeId($event->id) }}');
        @endif
        formData.append('_token', '{{ csrf_token() }}');
        fetch('{{ url('/' . $subdomain . '/parse-event-parts') }}', {
          method: 'POST',
          body: formData,
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => {
          if (r.status === 429) throw new Error(@json(__('messages.ai_rate_limit')));
          // What the server said, when it said something: every refusal used to read "Request failed".
          if (!r.ok) {
            return r.json().catch(() => ({})).then(body => {
              throw new Error(body.error || body.message || @json(__('messages.something_went_wrong')));
            });
          }
          return r.json();
        })
        .then(data => {
          this.parsingParts = false;
          event.target.value = '';
          if (data.error) {
            this.partsImportError = data.error;
          } else {
            const parts = data.parts || [];
            if (parts.length > 0) {
              this.parsedPartsPreview = parts;
              this.showPartsPreview = true;
            } else {
              this.partsImportError = this.tabLabels.import_found_nothing;
            }
            if (data.agenda_image_url) {
              this.agendaImageUrl = data.agenda_image_url;
              this.agendaImageFullUrl = data.agenda_image_full_url;
            }
          }
        })
        .catch(err => {
          this.parsingParts = false;
          event.target.value = '';
          this.partsImportError = err.message || @json(__('messages.something_went_wrong'));
        });
      },
      parsePartsFromText() {
        if (!this.partsText) return;
        this.parsingParts = true;
        this.partsImportError = '';
        const formData = new FormData();
        formData.append('parts_text', this.partsText);
        formData.append('ai_prompt', this.partsAiPrompt);
        formData.append('save_ai_prompt_default', this.savePartsAiPromptDefault ? '1' : '0');
        @if($event->exists)
        formData.append('event_id', '{{ \App\Utils\UrlUtils::encodeId($event->id) }}');
        @endif
        formData.append('_token', '{{ csrf_token() }}');
        fetch('{{ url('/' . $subdomain . '/parse-event-parts') }}', {
          method: 'POST',
          body: formData,
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => {
          if (r.status === 429) throw new Error(@json(__('messages.ai_rate_limit')));
          // What the server said, when it said something: every refusal used to read "Request failed".
          if (!r.ok) {
            return r.json().catch(() => ({})).then(body => {
              throw new Error(body.error || body.message || @json(__('messages.something_went_wrong')));
            });
          }
          return r.json();
        })
        .then(data => {
          this.parsingParts = false;
          if (data.error) {
            this.partsImportError = data.error;
          } else {
            const parts = data.parts || [];
            if (parts.length > 0) {
              this.parsedPartsPreview = parts;
              this.showPartsPreview = true;
            } else {
              this.partsImportError = this.tabLabels.import_found_nothing;
            }
          }
        })
        .catch(err => {
          this.parsingParts = false;
          this.partsImportError = err.message || @json(__('messages.something_went_wrong'));
        });
      },
      acceptParsedParts() {
        this.parsedPartsPreview.forEach(part => {
          const added = {
            uid: this.partUidCounter++,
            id: '',
            name: part.name || '',
            description: part.description || '',
            start_time: part.start_time || '',
            end_time: part.end_time || '',
          };
          this.eventParts.push(added);
          // As a part added by hand is: its description gets its editor, and shows when it has text.
          if (added.description) {
            this.partDescOpen[added.uid] = true;
          }
          this.initPartEditor(added);
        });
        this.parsedPartsPreview = [];
        this.showPartsPreview = false;
        this.showPartsTextInput = false;
        this.partsText = '';
        this.partsImportError = '';
      },
      deleteAgendaImage() {
        if (!confirm(@json(__('messages.are_you_sure')))) return;
        @if($event->exists)
        fetch('{{ route('event.delete_image', ['subdomain' => $subdomain]) }}' + '?hash={{ \App\Utils\UrlUtils::encodeId($event->id) }}&image_type=agenda', {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
          }
        }).then(response => {
          if (response.ok) {
            this.agendaImageUrl = '';
            this.agendaImageFullUrl = '';
          } else {
            alert(@json(__('messages.failed_to_delete_image')));
          }
        });
        @else
        this.agendaImageUrl = '';
        this.agendaImageFullUrl = '';
        @endif
      },
      onChangeVenueType(type) {
        if (type === 'in_person' && !this.isInPerson && !this.roleIsVenue) {
            this.venueType = '{{ (count($venues) > 0 ? 'use_existing' : 'create_new'); }}';
            this.selectedVenue = '';
            this.venueName = '';
            this.venueEmail = '';
            this.venueAddress1 = '';
            this.venueCity = '';
            this.venueState = '';
            this.venuePostalCode = '';
            this.venueWebsite = '';
        }

        this.savePreferences();
      },
      savePreferences() {
        // The two selling flags are no longer written: nothing reads them (see loadPreferences),
        // and leaving a stale ticketsEnabled in localStorage is what a future `?? false` would pick
        // up again. The per-schedule carry-over in EventController::create() owns that decision.
        localStorage.setItem('eventPreferences', JSON.stringify({
          isInPerson: this.isInPerson,
          isOnline: this.isOnline
        }));
      },
      loadPreferences() {
        const preferences = JSON.parse(localStorage.getItem('eventPreferences'));
        // Only the in-person / online toggles. This used to set tickets_enabled, rsvp_enabled and
        // ticketMode too, and doing so was wrong three separate ways:
        //
        //   1. It CLOBBERED the server-side carry-over. EventController::create() reads the
        //      schedule's own last event and seeds both flags from it; this then overwrote them on
        //      every new event, so the fix meant to lift the ticket-type rate never applied past
        //      the first event created in a given browser.
        //   2. `?? false` meant stale localStorage - written before ticketsEnabled existed as a key
        //      - actively turned selling OFF rather than leaving the server value alone. That hit
        //      every tier, not just free.
        //   3. The non-Pro arm hard-forced `tickets_enabled = false`. Do NOT reinstate it: free
        //      schedules still use Tickets mode for $0 rows, and unlimited free registration is a
        //      promise on every tier. The PAID rows are gated in Event::canSellPaidTickets().
        //
        // These two toggles stay because they have no server-side equivalent. The mode does: the
        // carry-over is per SCHEDULE, while localStorage is per BROWSER across every schedule an
        // account manages, so the server value is strictly the better answer.
        @if (! $event->exists && $selectedVenue)
        this.isInPerson = true;
        if (preferences) {
          this.isOnline = preferences.isOnline;
        }
        @else
        if (preferences) {
          this.isInPerson = preferences.isInPerson;
          this.isOnline = preferences.isOnline;
        }
        @endif
      },
      addGalleryFromStrip(e) {
        const added = this.galleryStore.addFiles(e.target.files);
        e.target.value = '';
        // Straight to the section, so the uploads are seen happening where they can be arranged.
        if (added) {
          this.openGallerySection();
        }
      },
      openGallerySection() {
        if (window.showEventSection) {
          window.showEventSection('section-gallery');
        }
        const section = document.getElementById('section-gallery');
        if (section) {
          section.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      },
      publishEvent() {
        this.event.is_draft = false;
        this.event.is_internal = false;
        this.event.is_private = false;
        this.event.event_password = '';
        this.$nextTick(() => {
          window._skipUnsavedWarning = true;
          document.getElementById('edit-form').requestSubmit();
        });
      },
      // The modal component is Alpine; this is the same window event its
      // x-on:open-modal.window listener expects (see role/show-admin.blade.php).
      openUpgrade(name) {
        window.dispatchEvent(new CustomEvent('open-modal', { detail: name }));
      },
      // Same event as openUpgrade(), named for the modals that are not an upgrade prompt.
      openModal(name) {
        window.dispatchEvent(new CustomEvent('open-modal', { detail: name }));
      },
      // Template expressions cannot reach window.alert, so the header's static notices go
      // through here.
      showMessage(message) {
        alert(message);
      },
      // The date and time are checked here rather than by the browser (the inputs are not
      // `required`: the date is a flatpickr), so this does what the native invalid handler
      // below does for a required field - open Details, mark it, focus the field - and says what
      // is missing under the fields instead of in an alert() that is gone once dismissed.
      showDateTimeError(message, field) {
        this.dateTimeError = message;
        this.dateTimeErrorField = field;
        this.markDateTimeFields();

        if (window.showEventSection) window.showEventSection('section-details');
        if (window.highlightEventSectionError) window.highlightEventSectionError('section-details');

        // A required field that is ALSO empty (the name) is focused by the native submit handler,
        // which runs after this one; focusing a date as well would fight it for the caret.
        var form = document.getElementById('edit-form');
        if (form && ! form.checkValidity()) return;

        var id = field === 'start' ? 'start_time' : (field === 'end_date' ? 'event_end_date' : 'event_date');
        var el = document.getElementById(id);
        var target = el && el._flatpickr && el._flatpickr.altInput ? el._flatpickr.altInput : el;
        setTimeout(function () {
          if (target) {
            target.focus();
            target.scrollIntoView({ behavior: 'smooth', block: 'center' });
          }
        }, 100);
      },
      // flatpickr draws its own altInput outside Vue, so the error border is set by hand.
      markDateTimeFields() {
        var pick = function (id) {
          var el = document.getElementById(id);
          if (! el) return null;
          return { input: el._flatpickr && el._flatpickr.altInput ? el._flatpickr.altInput : el, value: el.value };
        };
        var fields = [pick('event_date'), pick('start_time')];
        if (this.isMultiDay) fields.push(pick('event_end_date'));
        var show = !! this.dateTimeError;
        fields.forEach(function (f) {
          if (! f) return;
          var missing = show && ! f.value;
          f.input.classList.toggle('border-red-500', missing);
          f.input.classList.toggle('dark:border-red-400', missing);
          if (missing) {
            f.input.setAttribute('aria-invalid', 'true');
          } else {
            f.input.removeAttribute('aria-invalid');
          }
        });
      },
      // Called as the date and times are filled in, so the message goes once it is answered.
      refreshDateTimeError() {
        if (! this.dateTimeError) return;
        var filled = function (id) { var el = document.getElementById(id); return !! (el && el.value); };
        var done = filled('event_date') && filled('start_time') && (! this.isMultiDay || filled('event_end_date'));
        if (done) {
          this.dateTimeError = '';
          this.dateTimeErrorField = '';
        }
        this.markDateTimeFields();
      },
      validateForm(event) {
        // Already on its way: a second press, or Ctrl+S after the button, sends nothing more.
        if (this.isSaving) {
            event.preventDefault();
            return;
        }
        this.formSubmitAttempted = true;

        // A participant typed into the add form but not added was left out of the save without a
        // word. One with a name is added now, and the save goes a tick later, once its fields are
        // on the page: that covers the button, Ctrl+S and every scripted submit alike. One with
        // no name is shown, with the caret where the name goes.
        if (this.pendingMember) {
          event.preventDefault();
          if (this.memberName.trim() && this.addMember()) {
            this.$nextTick(() => document.getElementById('edit-form').requestSubmit());
          } else {
            this.goToTab('section-participants');
            this.$nextTick(() => {
              var name = document.getElementById('member_name');
              if (name) { name.focus(); }
            });
          }
          return;
        }

        // The same for a sponsor: added if it can be (it has its logo, or is an edit), shown if not.
        if (this.pendingSponsor) {
          event.preventDefault();
          if (this.addOrSaveEventSponsor()) {
            this.$nextTick(() => document.getElementById('edit-form').requestSubmit());
          } else {
            this.sponsorFormOpen = true;
            this.goToTab('section-event-settings');
          }
          return;
        }

        // A poll that is not finished would be dropped by the save. Its row opens on it instead.
        if (this.unfinishedPollIndex > -1) {
          event.preventDefault();
          this.pollError = this.tabLabels.engagement.poll_unfinished;
          this.pollMessage = '';
          this.activeEngagementTab = 'polls';
          this.goToTab('section-engagement');
          return;
        }

        var dateVal = document.getElementById('event_date').value;
        var startVal = document.getElementById('start_time').value;
        if (!dateVal || !startVal) {
          event.preventDefault();
          this.showDateTimeError(@json(__('messages.date_and_time_required')), dateVal ? 'start' : 'date');
          return;
        }

        if (this.isMultiDay) {
          var endDateVal = document.getElementById('event_end_date').value;
          if (!endDateVal) {
            event.preventDefault();
            this.showDateTimeError(@json(__('messages.end_date_required')), 'end_date');
            return;
          }
        }

        this.dateTimeError = '';
        this.dateTimeErrorField = '';
        this.markDateTimeFields();

        // Check custom fields if tickets are enabled
        if (this.event.tickets_enabled) {
          const hasInvalidEventFields = Object.values(this.eventCustomFields || {}).some(field => !field.name);
          const hasInvalidTicketFields = this.tickets.some(ticket =>
            Object.values(ticket.custom_fields || {}).some(field => !field.name)
          );
          const hasInvalidTicketTypes = this.tickets.length > 1 && this.tickets.some(ticket => !ticket.type);

          if (hasInvalidEventFields || hasInvalidTicketFields) {
            event.preventDefault();
            this.activeTicketTab = hasInvalidEventFields ? 'options' : 'tickets';
            this.goToTab('section-tickets');
            alert(@json(__('messages.please_fill_in_custom_field_names')));
            return;
          }

          if (hasInvalidTicketTypes) {
            event.preventDefault();
            this.activeTicketTab = 'tickets';
            this.goToTab('section-tickets');
            alert(@json(__('messages.please_fill_in_ticket_types')));
            return;
          }

          // A configured pass must not save silently broken (mirrors EventRepo::validatePassConfiguration)
          let passError = null;
          for (const t of this.tickets) {
            if (!t.is_pass) continue;
            if (t.pass_usage_type === 'total' && !(t.pass_max_uses > 0)) { passError = @json(__('messages.pass_max_uses_required')); break; }
            if (t.pass_usage_type !== 'per_occurrence') {
              if (t.pass_scope === 'sub_schedule' && !t.pass_scope_group_id) { passError = @json(__('messages.select_sub_schedule')); break; }
              if (t.pass_scope === 'specific_events' && (!t.pass_event_ids || t.pass_event_ids.length === 0)) { passError = @json(__('messages.pass_no_events_warning')); break; }
            }
          }
          if (passError) {
            event.preventDefault();
            this.activeTicketTab = 'tickets';
            this.goToTab('section-tickets');
            alert(passError);
            return;
          }
        }

        // Attendee change notifications (issue #94): if a key detail changed and the event has
        // notifiable registrants, confirm before saving. The chosen answer sets notify_attendees and
        // re-submits (notifyConfirmed short-circuits this branch on the second pass).
        if (!this.notifyConfirmed && this.shouldPromptNotify()) {
          event.preventDefault();
          this.showNotifyModal = true;
          this.$nextTick(() => { if (this.$refs.notifyMessageField) this.$refs.notifyMessageField.focus(); });
          return;
        }

        // Photos still uploading would be missing from the gallery this save commits, so the save
        // waits for them and then goes through the same way it was started. Last, after every
        // other check, so a missing date or a notify question is answered before the wait rather
        // than after it. The Publish and notify paths switch the leave-page warning off before
        // submitting; it comes back on while the uploads finish, and returns to what it was.
        if (this.galleryStore.pendingCount() > 0) {
          event.preventDefault();
          if (!this.galleryWaiting) {
            const skipBefore = window._skipUnsavedWarning;
            window._skipUnsavedWarning = false;
            this.galleryWaiting = true;
            this.galleryStore.whenIdle().then(() => {
              this.galleryWaiting = false;
              // Some photos did not make it: saving now would leave them out without a word.
              if (this.galleryStore.failedCount() > 0) {
                this.galleryStore.reportFailedBeforeSave();
                this.openGallerySection();
                return;
              }
              window._skipUnsavedWarning = skipBefore;
              document.getElementById('edit-form').requestSubmit();
            });
          }
          return;
        }

        // A photo whose upload failed would be left out of the gallery this save commits. The wait
        // above checks for that when it ends; this is the save with nothing left to wait for.
        if (this.galleryStore.failedCount() > 0) {
          event.preventDefault();
          this.galleryStore.reportFailedBeforeSave();
          this.openGallerySection();
          return;
        }

        this.isSaving = true;
        this.isDirty = false;
      },
      normalizeUrl(u) {
        return (u || '').toString().trim().toLowerCase().replace(/\/+$/, '');
      },
      hasKeyChange() {
        // Date/time, non-recurring only (mirrors the server's boot-hook boundary).
        if (!this.isRecurring && this.startsAt !== this.origStartsAt) {
          return true;
        }
        // Duration (end time / multi-day end date) is a date change too; compare numerically so
        // "2.00" vs "2" formatting differences don't register as a change.
        if (!this.isRecurring && parseFloat(this.currentDuration || 0) !== parseFloat(this.origDuration || 0)) {
          return true;
        }
        const venueId = this.selectedVenue ? this.selectedVenue.id : null;
        if (this.isInPerson && venueId !== this.origVenueId) {
          return true;
        }
        if (this.isOnline !== this.origIsOnline) {
          return true;
        }
        if (this.isOnline && this.normalizeUrl(this.event.event_url) !== this.origEventUrl) {
          return true;
        }
        return false;
      },
      shouldPromptNotify() {
        // Either audience is reason to ask. The interest list is deliberately NOT behind
        // scheduleHasEmailSettings: EventChangeNotifier applies that gate to the sales half
        // internally, where it belongs, and applying it here as well is what made the interest
        // half unreachable for every schedule on the platform mailer - which is most of them.
        return this.someoneToNotify()
          && !this.event.is_cancelled
          && !this.event.is_draft
          && this.hasKeyChange();
      },
      someoneToNotify() {
        return (this.registrantCount > 0 && this.scheduleHasEmailSettings)
          || this.interestedCount > 0;
      },
      recentlyNotifiedMinutes() {
        if (!this.attendeesNotifiedAt) return null;
        const mins = Math.floor((Date.now() - new Date(this.attendeesNotifiedAt).getTime()) / 60000);
        return (mins >= 0 && mins < 10) ? mins : null;
      },
      recentlyNotifiedCaption() {
        return @json(__('messages.notify_recently_caption')).replace(':minutes', this.recentlyNotifiedMinutes());
      },
      confirmNotify(notify) {
        this.notifyAttendees = !!notify;
        this.notifyConfirmed = true;
        this.showNotifyModal = false;
        this.$nextTick(() => {
          window._skipUnsavedWarning = true;
          document.getElementById('edit-form').requestSubmit();
        });
      },
      closeNotifyModal() {
        this.showNotifyModal = false;
      },
      openCancelModal() {
        this.showCancelModal = true;
        this.$nextTick(() => { if (this.$refs.cancelMessageField) this.$refs.cancelMessageField.focus(); });
      },
      closeCancelModal() {
        this.showCancelModal = false;
      },
      cancelWillNotify() {
        return this.someoneToNotify();
      },
      submitCancel() {
        if (this.isSubmittingCancel) return;
        const form = document.getElementById('event-cancel-form');
        if (!form) return;
        this.isSubmittingCancel = true;
        const notify = this.cancelWillNotify() ? 1 : 0;
        const setVal = (n, v) => { const el = form.querySelector('[name="' + n + '"]'); if (el) el.value = v; };
        setVal('notify_attendees', notify);
        setVal('notify_message', this.cancelMessage || '');
        window._skipUnsavedWarning = true;
        form.submit();
      },
      submitRestore() {
        if (this.isSubmittingRestore) return;
        const form = document.getElementById('event-restore-form');
        if (!form) return;
        this.isSubmittingRestore = true;
        window._skipUnsavedWarning = true;
        form.submit();
      },
      openNotifyPreview() {
        const form = document.getElementById('notify-preview-form');
        if (!form) return;
        const set = (n, v) => { const el = form.querySelector('[name="' + n + '"]'); if (el) el.value = v; };
        set('notify_message', this.notifyMessage || '');
        set('name', this.event.name || '');
        set('event_date', document.getElementById('event_date') ? document.getElementById('event_date').value : '');
        set('start_time', document.getElementById('start_time') ? document.getElementById('start_time').value : '');
        set('duration', document.getElementById('duration') ? document.getElementById('duration').value : (this.event.duration || ''));
        set('event_url', this.isOnline ? (this.event.event_url || '') : '');
        set('venue_name', (this.isInPerson && this.selectedVenue) ? (this.selectedVenue.name || '') : '');
        set('venue_id', (this.isInPerson && this.selectedVenue) ? (this.selectedVenue.id || '') : '');
        form.submit();
      },
      notifyBody() {
        return this.notifyAudienceBody(@json(__('messages.notify_attendees_body')));
      },
      cancelBody() {
        return this.notifyAudienceBody(@json(__('messages.cancel_event_body')));
      },
      /**
       * Both bodies name ":count registered attendees". With an interest list and no sales that
       * reads "the 0 registered attendees", so the two audiences are named separately and only
       * when they exist.
       */
      notifyAudienceBody(template) {
        const attendees = this.scheduleHasEmailSettings ? this.registrantCount : 0;

        if (attendees > 0 && this.interestedCount > 0) {
          return template.replace(':count', attendees) + ' '
            + @json(__('messages.notify_also_interested')).replace(':count', this.interestedCount);
        }

        if (attendees === 0 && this.interestedCount > 0) {
          return @json(__('messages.notify_interested_only')).replace(':count', this.interestedCount);
        }

        return template.replace(':count', attendees);
      },
      changeSummary() {
        const out = [];
        if (!this.isRecurring && (this.startsAt !== this.origStartsAt || parseFloat(this.currentDuration || 0) !== parseFloat(this.origDuration || 0))) {
          out.push(@json(__('messages.event_changed_date_label')));
        }
        const venueId = this.selectedVenue ? this.selectedVenue.id : null;
        const locationChanged = (this.isInPerson && venueId !== this.origVenueId)
          || (this.isOnline !== this.origIsOnline)
          || (this.isOnline && this.normalizeUrl(this.event.event_url) !== this.origEventUrl);
        if (locationChanged) {
          out.push(@json(__('messages.event_changed_location_label')));
        }
        return out.join(', ');
      },
      toggleTicketPass(index) {
        const t = this.tickets[index];
        // Passes are Pro. The switch is already disabled below Pro, so this only catches a
        // programmatic call - but turning one ON here would be reset by EventRepo::saveEvent()
        // anyway, and a control that moves and then silently snaps back is worse than one that
        // does not move. Turning an existing pass OFF stays allowed on any plan.
        if (! this.isPro && ! t.is_pass) {
          return;
        }
        t.is_pass = !t.is_pass;
        if (t.is_pass) {
          // Default to a season pass on recurring events, otherwise a cross-event subscription.
          if (!t.pass_usage_type || (t.pass_usage_type === 'per_occurrence' && !this.isRecurring)) {
            t.pass_usage_type = this.isRecurring ? 'per_occurrence' : 'total';
          }
          this.normalizePassScope(t);
        }
      },
      normalizePassScope(ticket) {
        if (ticket.pass_usage_type === 'per_occurrence') {
          ticket.pass_scope = 'this_event';
        } else if (!ticket.pass_scope || ticket.pass_scope === 'this_event') {
          ticket.pass_scope = 'all_events';
        }
      },
      passTypeHelp(type) {
        const map = {
          total: @json(__('messages.pass_type_visit_pass_help')),
          unlimited: @json(__('messages.pass_type_membership_help')),
          per_event: @json(__('messages.pass_type_festival_help')),
          per_occurrence: @json(__('messages.pass_type_season_help')),
        };
        return map[type] || '';
      },
      passCancelHoursLabel(hours) {
        return @json(__('messages.pass_cancel_hours_before')).replace(':hours', String(hours));
      },
      getFilteredPassEvents(index) {
        const q = (this.passEventSearch[index] || '').toLowerCase().trim();
        if (!q) return this.passEvents;
        return this.passEvents.filter(e => (e.name || '').toLowerCase().includes(q));
      },
      addTicket() {
        var newIndex = this.tickets.length;
        this.tickets.push({
            uid: this.ticketUidCounter++,
            id: null,
            type: '',
            quantity: null,
            max_per_order: null,
            price: null,
            description: '',
            custom_fields: {},
            volume_discount: null,
            is_pass: false,
            pass_usage_type: 'per_occurrence',
            pass_max_uses: null,
            pass_valid_days: null,
            pass_scope: 'this_event',
            pass_allow_booking: false,
            pass_seats_per_occurrence: null,
            pass_cancel_cutoff_hours: '',
            pass_late_cancel_policy: 'forfeit',
            pass_admits_per_event: 1,
            pass_scope_group_id: '',
            pass_event_ids: [],
            sales_start_at_date: '',
            sales_start_at_time: '',
            sales_end_at_date: '',
            sales_end_at_time: '',
        });
        if (this.showSalesDates) {
          this.$nextTick(() => {
            this.$nextTick(() => {
              this.initTicketSalesStartDatePicker(newIndex);
              this.initTicketSalesEndDatePicker(newIndex);
            });
          });
        }
      },
      removeTicket(index) {
        // Destroy flatpickr instances before removing
        document.querySelectorAll('.datepicker-ticket-sales-start').forEach(input => {
          if (input._flatpickr) input._flatpickr.destroy();
        });
        document.querySelectorAll('.datepicker-ticket-sales-end').forEach(input => {
          if (input._flatpickr) input._flatpickr.destroy();
        });
        // Dispose the removed row's markdown editor so it isn't orphaned
        const removedDesc = document.querySelector('textarea[name="tickets[' + index + '][description]"]');
        if (removedDesc && removedDesc._easyMDE) {
          removedDesc._easyMDE._stopEditorObserver?.();
          removedDesc._easyMDE.toTextArea();
        }
        this.tickets.splice(index, 1);
        if (this.tickets.length > 0) {
          this.$nextTick(() => {
            this.initAllTicketSalesStartDatePickers();
            this.initAllTicketSalesEndDatePickers();
          });
        }
      },
      addPromoCode() {
        this.promoCodes.push({
            id: null,
            code: '',
            type: 'percentage',
            value: null,
            max_uses: null,
            times_used: 0,
            expires_at_date: '',
            expires_at_time: '',
            is_active: true,
            ticket_ids: [],
        });
        this.$nextTick(() => {
          this.$nextTick(() => {
            this.initPromoDatePicker(this.promoCodes.length - 1);
          });
        });
      },
      removePromoCode(index) {
        // Destroy flatpickr instances before removing
        document.querySelectorAll('.datepicker-promo-date').forEach(input => {
          if (input._flatpickr) input._flatpickr.destroy();
        });
        this.promoCodes.splice(index, 1);
        if (this.promoCodes.length > 0) {
          this.$nextTick(() => { this.initAllPromoDatePickers(); });
        }
      },
      addAddon() {
        this.addons.push({
          id: null,
          _key: 'new_' + Date.now() + '_' + this.addons.length,
          type: '',
          quantity: null,
          max_per_order: null,
          price: null,
          description: '',
          image_url: null,
          url: '',
          remove_image: false,
        });
      },
      removeAddon(index) {
        this.addons.splice(index, 1);
      },
      addAddonMaxPerOrder(addonIndex) {
        this.addons[addonIndex].max_per_order = 1;
      },
      removeAddonMaxPerOrder(addonIndex) {
        this.addons[addonIndex].max_per_order = null;
      },
      removeAddonImage(aIndex) {
        if (!confirm(@json(__('messages.are_you_sure')))) return;
        this.addons[aIndex].remove_image = true;
        this.addons[aIndex].image_url = null;
        var ref = this.$refs['addon_image_' + aIndex];
        var fileInput = Array.isArray(ref) ? ref[0] : ref;
        if (fileInput) fileInput.value = '';
      },
      onAddonFileChange(aIndex, event) {
        var file = event.target.files[0];
        if (file) {
          this.addons[aIndex].remove_image = false;

          var reader = new FileReader();
          var self = this;
          reader.onload = (e) => {
            var img = new Image();
            img.onload = () => {
              var maxDim = 1200;
              var w = img.width;
              var h = img.height;
              if (w > maxDim || h > maxDim) {
                if (w > h) {
                  h = Math.round(h * maxDim / w);
                  w = maxDim;
                } else {
                  w = Math.round(w * maxDim / h);
                  h = maxDim;
                }
              }
              var canvas = document.createElement('canvas');
              canvas.width = w;
              canvas.height = h;
              canvas.getContext('2d').drawImage(img, 0, 0, w, h);
              self.addons[aIndex].image_url = canvas.toDataURL('image/jpeg', 0.85);
            };
            img.src = e.target.result;
          };
          reader.readAsDataURL(file);
        }
      },
      copyPromoLink(promoCode) {
        if (!promoCode.code) return;
        if (!this.promoLinkBaseUrl) {
            alert(@json(__('messages.save_event_first')));
            return;
        }
        const url = this.promoLinkBaseUrl + '?promo=' + encodeURIComponent(promoCode.code.trim().toUpperCase());
        navigator.clipboard.writeText(url);
        promoCode._copied = true;
        setTimeout(() => { promoCode._copied = false; }, 2000);
      },
      initPromoDatePicker(pcIndex) {
        this.$nextTick(() => {
          var inputs = document.querySelectorAll('.datepicker-promo-date');
          var input = null;
          inputs.forEach(function(el) {
            if (el.getAttribute('data-pc-index') == pcIndex) input = el;
          });
          if (!input || input._flatpickr) return;
          var defaultDate = this.promoCodes[pcIndex].expires_at_date || null;
          if (defaultDate) input.removeAttribute('value');
          var fpLocale = window.flatpickrLocales ? window.flatpickrLocales[window.appLocale] : null;
          var localeConfig = fpLocale ? { locale: fpLocale } : {};
          var self = this;
          var f = flatpickr(input, Object.assign({
            allowInput: true,
            enableTime: false,
            altInput: true,
            altFormat: "M j, Y",
            dateFormat: "Y-m-d",
            defaultDate: defaultDate,
            onChange: function(selectedDates, dateStr) {
              self.promoCodes[pcIndex].expires_at_date = dateStr;
            },
          }, localeConfig));
          if (f._input) f._input.onkeydown = () => false;
        });
      },
      initAllPromoDatePickers() {
        for (var i = 0; i < this.promoCodes.length; i++) {
          this.initPromoDatePicker(i);
        }
      },
      initPromoTimePickerOnFocus(event, pcIndex) {
        var inputEl = event.target;
        var dropdownRef = 'promo_time_dropdown_' + pcIndex;
        var dropdownEl = this.$refs[dropdownRef];
        if (Array.isArray(dropdownEl)) dropdownEl = dropdownEl[0];
        if (dropdownEl && !inputEl._timepickerInit) {
          initPartTimePicker(inputEl, dropdownEl);
        }
      },
      onPromoTimeChange(pcIndex, event) {
        var minutes = parseTimeToMinutes(event.target.value);
        if (minutes !== null) {
          var h = Math.floor(minutes / 60);
          var m = minutes % 60;
          this.promoCodes[pcIndex].expires_at_time = (h < 10 ? '0' : '') + h + ':' + (m < 10 ? '0' : '') + m;
          event.target.value = formatMinutesToTime(minutes);
        } else {
          this.promoCodes[pcIndex].expires_at_time = '';
        }
      },
      initTicketSalesStartDatePicker(ticketIndex) {
        this.$nextTick(() => {
          var inputs = document.querySelectorAll('.datepicker-ticket-sales-start');
          var input = null;
          inputs.forEach(function(el) {
            if (el.getAttribute('data-ticket-index') == ticketIndex) input = el;
          });
          if (!input || input._flatpickr) return;
          var defaultDate = this.tickets[ticketIndex].sales_start_at_date || null;
          if (defaultDate) input.removeAttribute('value');
          var fpLocale = window.flatpickrLocales ? window.flatpickrLocales[window.appLocale] : null;
          var localeConfig = fpLocale ? { locale: fpLocale } : {};
          var self = this;
          var f = flatpickr(input, Object.assign({
            allowInput: true,
            enableTime: false,
            altInput: true,
            altFormat: "M j, Y",
            dateFormat: "Y-m-d",
            defaultDate: defaultDate,
            onChange: function(selectedDates, dateStr) {
              self.tickets[ticketIndex].sales_start_at_date = dateStr;
            },
          }, localeConfig));
          if (f._input) f._input.onkeydown = () => false;
        });
      },
      initAllTicketSalesStartDatePickers() {
        for (var i = 0; i < this.tickets.length; i++) {
          this.initTicketSalesStartDatePicker(i);
        }
      },
      initTicketSalesStartTimePickerOnFocus(event, ticketIndex) {
        var inputEl = event.target;
        var dropdownRef = 'ticket_sales_start_time_dropdown_' + ticketIndex;
        var dropdownEl = this.$refs[dropdownRef];
        if (Array.isArray(dropdownEl)) dropdownEl = dropdownEl[0];
        if (dropdownEl && !inputEl._timepickerInit) {
          initPartTimePicker(inputEl, dropdownEl);
        }
      },
      onTicketSalesStartTimeChange(ticketIndex, event) {
        var minutes = parseTimeToMinutes(event.target.value);
        if (minutes !== null) {
          var h = Math.floor(minutes / 60);
          var m = minutes % 60;
          this.tickets[ticketIndex].sales_start_at_time = (h < 10 ? '0' : '') + h + ':' + (m < 10 ? '0' : '') + m;
          event.target.value = formatMinutesToTime(minutes);
        } else {
          this.tickets[ticketIndex].sales_start_at_time = '';
        }
      },
      initTicketSalesEndDatePicker(ticketIndex) {
        this.$nextTick(() => {
          var inputs = document.querySelectorAll('.datepicker-ticket-sales-end');
          var input = null;
          inputs.forEach(function(el) {
            if (el.getAttribute('data-ticket-index') == ticketIndex) input = el;
          });
          if (!input || input._flatpickr) return;
          var defaultDate = this.tickets[ticketIndex].sales_end_at_date || null;
          if (defaultDate) input.removeAttribute('value');
          var fpLocale = window.flatpickrLocales ? window.flatpickrLocales[window.appLocale] : null;
          var localeConfig = fpLocale ? { locale: fpLocale } : {};
          var self = this;
          var f = flatpickr(input, Object.assign({
            allowInput: true,
            enableTime: false,
            altInput: true,
            altFormat: "M j, Y",
            dateFormat: "Y-m-d",
            defaultDate: defaultDate,
            onChange: function(selectedDates, dateStr) {
              self.tickets[ticketIndex].sales_end_at_date = dateStr;
            },
          }, localeConfig));
          if (f._input) f._input.onkeydown = () => false;
        });
      },
      initAllTicketSalesEndDatePickers() {
        for (var i = 0; i < this.tickets.length; i++) {
          this.initTicketSalesEndDatePicker(i);
        }
      },
      initTicketSalesEndTimePickerOnFocus(event, ticketIndex) {
        var inputEl = event.target;
        var dropdownRef = 'ticket_sales_end_time_dropdown_' + ticketIndex;
        var dropdownEl = this.$refs[dropdownRef];
        if (Array.isArray(dropdownEl)) dropdownEl = dropdownEl[0];
        if (dropdownEl && !inputEl._timepickerInit) {
          initPartTimePicker(inputEl, dropdownEl);
        }
      },
      onTicketSalesEndTimeChange(ticketIndex, event) {
        var minutes = parseTimeToMinutes(event.target.value);
        if (minutes !== null) {
          var h = Math.floor(minutes / 60);
          var m = minutes % 60;
          this.tickets[ticketIndex].sales_end_at_time = (h < 10 ? '0' : '') + h + ':' + (m < 10 ? '0' : '') + m;
          event.target.value = formatMinutesToTime(minutes);
        } else {
          this.tickets[ticketIndex].sales_end_at_time = '';
        }
      },
      togglePoll(poll) {
        this.pollSubmitting = true;
        this.pollMessage = '';
        this.pollError = '';

        const url = '{{ $event->exists ? route("event.toggle_poll", ["subdomain" => $subdomain, "event_hash" => \App\Utils\UrlUtils::encodeId($event->id), "poll_hash" => "POLL_HASH"]) : "" }}'.replace('POLL_HASH', poll.hash);

        fetch(url, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
          },
        })
        .then(response => {
          if (!response.ok) return response.json().then(data => { throw data; });
          return response.json();
        })
        .then(data => {
          poll.is_active = data.is_active;
          this.pollMessage = data.message;
        })
        .catch(err => {
          this.pollError = err.error || err.message || @json(__("messages.error"));
        })
        .finally(() => {
          this.pollSubmitting = false;
        });
      },
      deletePoll(poll, index) {
        if (!confirm(@json(__("messages.delete_poll") . '?'))) return;

        this.pollSubmitting = true;
        this.pollMessage = '';
        this.pollError = '';

        const url = '{{ $event->exists ? route("event.delete_poll", ["subdomain" => $subdomain, "event_hash" => \App\Utils\UrlUtils::encodeId($event->id), "poll_hash" => "POLL_HASH"]) : "" }}'.replace('POLL_HASH', poll.hash);

        fetch(url, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
          },
        })
        .then(response => {
          if (!response.ok) return response.json().then(data => { throw data; });
          return response.json();
        })
        .then(data => {
          this.polls.splice(index, 1);
          this.pollMessage = data.message;
        })
        .catch(err => {
          this.pollError = err.error || err.message || @json(__("messages.error"));
        })
        .finally(() => {
          this.pollSubmitting = false;
        });
      },
      pollOptionCount(poll, idx) {
        return poll.results[idx] || 0;
      },
      pollOptionPct(poll, idx) {
        const count = poll.results[idx] || 0;
        return poll.votes_count > 0 ? Math.round(count / poll.votes_count * 100) : 0;
      },
      pollBarStyle(poll, idx) {
        const count = poll.results[idx] || 0;
        const pct = poll.votes_count > 0 ? Math.round(count / poll.votes_count * 100) : 0;
        const maxCount = poll.votes_count > 0 ? Math.max(...poll.options.map((_, i) => poll.results[i] || 0)) : 0;
        const width = Math.max(pct, poll.votes_count > 0 ? 2 : 0);
        const color = (count === maxCount && poll.votes_count > 0) ? 'var(--brand-blue)' : '#9ca3af';
        return { width: width + '%', backgroundColor: color };
      },
      approvePollOption(poll, pendingIdx) {
        this.pollSubmitting = true;
        this.pollMessage = '';
        this.pollError = '';

        const url = '{{ $event->exists ? route("event.approve_poll_option", ["subdomain" => $subdomain, "event_hash" => \App\Utils\UrlUtils::encodeId($event->id), "poll_hash" => "POLL_HASH"]) : "" }}'.replace('POLL_HASH', poll.hash);

        fetch(url, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json',
            'Accept': 'application/json',
          },
          body: JSON.stringify({ option_index: pendingIdx }),
        })
        .then(response => {
          if (!response.ok) return response.json().then(data => { throw data; });
          return response.json();
        })
        .then(data => {
          poll.options = data.options;
          poll.pending_options = data.pending_options;
          this.pollMessage = data.message;
        })
        .catch(err => {
          this.pollError = err.error || err.message || @json(__("messages.error"));
        })
        .finally(() => {
          this.pollSubmitting = false;
        });
      },
      rejectPollOption(poll, pendingIdx) {
        this.pollSubmitting = true;
        this.pollMessage = '';
        this.pollError = '';

        const url = '{{ $event->exists ? route("event.reject_poll_option", ["subdomain" => $subdomain, "event_hash" => \App\Utils\UrlUtils::encodeId($event->id), "poll_hash" => "POLL_HASH"]) : "" }}'.replace('POLL_HASH', poll.hash);

        fetch(url, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json',
            'Accept': 'application/json',
          },
          body: JSON.stringify({ option_index: pendingIdx }),
        })
        .then(response => {
          if (!response.ok) return response.json().then(data => { throw data; });
          return response.json();
        })
        .then(data => {
          poll.pending_options = data.pending_options;
          this.pollMessage = data.message;
        })
        .catch(err => {
          this.pollError = err.error || err.message || @json(__("messages.error"));
        })
        .finally(() => {
          this.pollSubmitting = false;
        });
      },
      getNextAvailableEventFieldIndex() {
        const usedIndices = Object.values(this.eventCustomFields || {}).map(f => f.index).filter(i => i);
        for (let i = 1; i <= 10; i++) {
          if (!usedIndices.includes(i)) {
            return i;
          }
        }
        return null;
      },
      addEventCustomField() {
        const fieldCount = Object.keys(this.eventCustomFields || {}).length;
        if (fieldCount >= 10) return;
        const fieldKey = 'field' + Date.now();
        const fieldIndex = this.getNextAvailableEventFieldIndex();
        this.eventCustomFields = {
          ...this.eventCustomFields,
          [fieldKey]: { name: '', name_en: '', type: 'string', required: false, index: fieldIndex }
        };
      },
      removeEventCustomField(fieldKey) {
        const newFields = { ...this.eventCustomFields };
        delete newFields[fieldKey];
        this.eventCustomFields = newFields;
      },
      getEventCustomFieldCount() {
        return Object.keys(this.eventCustomFields || {}).length;
      },
      getNextAvailableTicketFieldIndex(ticket) {
        const usedIndices = Object.values(ticket.custom_fields || {}).map(f => f.index).filter(i => i);
        for (let i = 1; i <= 10; i++) {
          if (!usedIndices.includes(i)) {
            return i;
          }
        }
        return null;
      },
      addTicketVolumeDiscount(ticketIndex) {
        const ticket = this.tickets[ticketIndex];
        ticket.volume_discount = {
          min_quantity: 2,
          type: 'percentage',
          value: 10,
        };
      },
      removeTicketVolumeDiscount(ticketIndex) {
        this.tickets[ticketIndex].volume_discount = null;
      },
      addTicketMaxPerOrder(ticketIndex) {
        this.tickets[ticketIndex].max_per_order = 1;
      },
      removeTicketMaxPerOrder(ticketIndex) {
        this.tickets[ticketIndex].max_per_order = null;
      },
      addTicketCustomField(ticketIndex) {
        const ticket = this.tickets[ticketIndex];
        if (!ticket.custom_fields) {
          ticket.custom_fields = {};
        }
        const fieldCount = Object.keys(ticket.custom_fields).length;
        if (fieldCount >= 10) return;
        const fieldKey = 'field' + Date.now();
        const fieldIndex = this.getNextAvailableTicketFieldIndex(ticket);
        ticket.custom_fields = {
          ...ticket.custom_fields,
          [fieldKey]: { name: '', name_en: '', type: 'string', required: false, index: fieldIndex }
        };
      },
      removeTicketCustomField(ticketIndex, fieldKey) {
        const ticket = this.tickets[ticketIndex];
        const newFields = { ...ticket.custom_fields };
        delete newFields[fieldKey];
        ticket.custom_fields = newFields;
      },
      reorderEventCustomFields(container) {
        const items = container.querySelectorAll('[data-event-field-key]');
        const newFields = {};
        items.forEach(item => {
          const key = item.dataset.eventFieldKey;
          if (this.eventCustomFields[key]) {
            newFields[key] = this.eventCustomFields[key];
          }
        });
        this.eventCustomFields = newFields;
      },
      reorderTicketCustomFields(ticketIndex) {
        const container = document.querySelector(`#ticket-${ticketIndex}-custom-fields`);
        if (!container) return;
        const items = container.querySelectorAll('[data-ticket-field-key]');
        const ticket = this.tickets[ticketIndex];
        const newFields = {};
        items.forEach(item => {
          const key = item.dataset.ticketFieldKey;
          if (ticket.custom_fields[key]) {
            newFields[key] = ticket.custom_fields[key];
          }
        });
        ticket.custom_fields = newFields;
      },
      initCustomFieldsSortable() {
        const self = this;
        if (typeof Sortable === 'undefined') return;

        const eventFieldsContainer = document.querySelector('#event-custom-fields-sortable');
        if (eventFieldsContainer && !eventFieldsContainer._sortableInitialized) {
          Sortable.create(eventFieldsContainer, {
            handle: '.custom-field-drag-handle',
            animation: 150,
            ghostClass: 'opacity-50',
            onEnd: () => self.reorderEventCustomFields(eventFieldsContainer),
          });
          eventFieldsContainer._sortableInitialized = true;
        }

        this.tickets.forEach((ticket, index) => {
          const ticketContainer = document.querySelector(`#ticket-${index}-custom-fields`);
          if (ticketContainer && !ticketContainer._sortableInitialized) {
            Sortable.create(ticketContainer, {
              handle: '.custom-field-drag-handle',
              animation: 150,
              ghostClass: 'opacity-50',
              onEnd: () => self.reorderTicketCustomFields(index),
            });
            ticketContainer._sortableInitialized = true;
          }
        });
      },
      getTicketCustomFieldCount(ticketIndex) {
        const ticket = this.tickets[ticketIndex];
        return Object.keys(ticket.custom_fields || {}).length;
      },
      onToggleSalesDates() {
        if (this.showSalesDates) {
          this.$nextTick(() => {
            this.initAllTicketSalesStartDatePickers();
            this.initAllTicketSalesEndDatePickers();
          });
        } else {
          this.tickets.forEach(ticket => {
            ticket.sales_start_at_date = '';
            ticket.sales_start_at_time = '';
            ticket.sales_end_at_date = '';
            ticket.sales_end_at_time = '';
          });
        }
      },
      toggleExpireUnpaid() {
        if (! this.event.expire_unpaid_tickets) {
          this.event.expire_unpaid_tickets = 24;
        } else {
          this.event.expire_unpaid_tickets = 0;
        }
      },
      toggleCuratorGroupSelection(curatorId) {
        const groupSelection = document.getElementById(`curator_group_${curatorId}`);
        if (groupSelection) {
          const checkbox = document.getElementById(`curator_${curatorId}`);
          if (checkbox && checkbox.checked) {
            groupSelection.style.display = 'block';
          } else {
            groupSelection.style.display = 'none';
          }
        }
      },
      initializeCuratorGroupSelections() {
        // Show group selection for curators that are already checked
        const curatorCheckboxes = document.querySelectorAll('input[name="curators[]"]');
        curatorCheckboxes.forEach(checkbox => {
          if (checkbox.checked) {
            const curatorId = checkbox.value;
            this.toggleCuratorGroupSelection(curatorId);
          }
        });
      },
      addIncludeDate() {
        this.recurringIncludeDates.push('');
        this.$nextTick(() => {
          this.$nextTick(() => {
            const items = document.querySelectorAll('#recurring-include-dates-items .datepicker-include-date');
            const lastInput = items[items.length - 1];
            if (lastInput && !lastInput._flatpickr) {
              this.initDatePickerOnInput(lastInput, 'include', this.recurringIncludeDates.length - 1);
            }
          });
        });
      },
      removeIncludeDate(index) {
        document.querySelectorAll('#recurring-include-dates-items .datepicker-include-date').forEach(input => {
          if (input._flatpickr) input._flatpickr.destroy();
        });
        this.recurringIncludeDates.splice(index, 1);
        if (this.recurringIncludeDates.length > 0) {
          this.initAllRecurringDatePickers();
        }
      },
      addExcludeDate() {
        this.recurringExcludeDates.push('');
        this.$nextTick(() => {
          this.$nextTick(() => {
            const items = document.querySelectorAll('#recurring-exclude-dates-items .datepicker-exclude-date');
            const lastInput = items[items.length - 1];
            if (lastInput && !lastInput._flatpickr) {
              this.initDatePickerOnInput(lastInput, 'exclude', this.recurringExcludeDates.length - 1);
            }
          });
        });
      },
      removeExcludeDate(index) {
        document.querySelectorAll('#recurring-exclude-dates-items .datepicker-exclude-date').forEach(input => {
          if (input._flatpickr) input._flatpickr.destroy();
        });
        this.recurringExcludeDates.splice(index, 1);
        if (this.recurringExcludeDates.length > 0) {
          this.initAllRecurringDatePickers();
        }
      },
      initDatePickerOnInput(input, type, index) {
        const arr = type === 'include' ? this.recurringIncludeDates : this.recurringExcludeDates;
        const defaultDate = arr[index] || null;

        if (defaultDate) {
          input.removeAttribute('value');
        }

        var f = flatpickr(input, {
          allowInput: true,
          enableTime: false,
          altInput: true,
          altFormat: "M j, Y",
          dateFormat: "Y-m-d",
          defaultDate: defaultDate,
          onChange: (selectedDates, dateStr) => {
            arr[index] = dateStr;
          }
        });

        if (f._input) {
          f._input.onkeydown = () => false;
        }

        if (!defaultDate) {
          f.open();
        }
      },
      initAllRecurringDatePickers() {
        this.$nextTick(() => {
          this.$nextTick(() => {
            document.querySelectorAll('#recurring-include-dates-items .datepicker-include-date').forEach((input, i) => {
              if (!input._flatpickr) {
                this.initDatePickerOnInput(input, 'include', i);
              }
            });
            document.querySelectorAll('#recurring-exclude-dates-items .datepicker-exclude-date').forEach((input, i) => {
              if (!input._flatpickr) {
                this.initDatePickerOnInput(input, 'exclude', i);
              }
            });
          });
        });
      },
      initializeRecurringEndDatePicker() {
        // Use multiple nextTick calls to ensure Vue has fully rendered the v-if element
        this.$nextTick(() => {
          this.$nextTick(() => {
            const endDateInput = document.querySelector('.datepicker-end-date');
            // Make sure element exists and is not already initialized
            if (endDateInput && !endDateInput._flatpickr) {
              // Get the value from Vue model or input's value attribute
              const inputValue = this.event.recurring_end_value || endDateInput.value || null;
              
              // Clear the value attribute - flatpickr will set it via defaultDate
              if (inputValue) {
                endDateInput.removeAttribute('value');
              }
              
              var f = flatpickr(endDateInput, {
                allowInput: true,
                enableTime: false,
                altInput: true,
                altFormat: "M j, Y",
                dateFormat: "Y-m-d",
                defaultDate: inputValue,
                onChange: (selectedDates, dateStr, instance) => {
                  // Update Vue model with the actual value (YYYY-MM-DD format)
                  this.event.recurring_end_value = dateStr;
                }
              });
              
              // https://github.com/flatpickr/flatpickr/issues/892#issuecomment-604387030
              if (f._input) {
                f._input.onkeydown = () => false;
              }
            }
          });
        });
      },
    },
    computed: {
      ticketsNeedPro() {
        return this.cannotSellPaid
          && this.event.tickets_enabled
          && this.tickets.some(ticket => parseFloat(ticket.price) > 0);
      },
      galleryRecurringHint() {
        return this.isRecurring ? this.galleryRecurringText : '';
      },
      recentVenues() {
        return this.recentVenueIds.map((id) => this.venues.find(function (venue) { return venue.id === id; })).filter(Boolean);
      },
      // Saved venues whose name holds what is being typed as a new one.
      venueNameMatches() {
        var typed = (this.venueName || '').trim().toLowerCase();
        if (this.selectedVenue || this.venueType !== 'create_new' || typed.length < 2) {
          return [];
        }
        return this.venues.filter(function (venue) {
          return (venue.name || '').toLowerCase().indexOf(typed) !== -1;
        }).slice(0, 3);
      },
      // Which of the three ticket choices is on, read from the Tickets tab's own mode.
      ticketChoice() {
        if (this.ticketMode === 'tickets' || this.ticketMode === 'rsvp') {
          return this.ticketMode;
        }
        // "Tickets elsewhere" is on because it was saved with something in it or because its tile
        // was pressed, never because of what its fields hold right now: clearing the link to
        // retype it must not make the field disappear.
        return this.externalChosen ? 'external' : null;
      },
      // "Sold: 42/120" beside the tab's title, once anything has sold.
      ticketSoldLine() {
        var self = this;
        if (! this.event.tickets_enabled) { return ''; }
        var sold = this.tickets.reduce(function (n, t) { return n + self.soldCount(t); }, 0);
        if (! sold) { return ''; }
        var capped = ! this.isRecurring && this.tickets.length > 0 && this.tickets.every(function (t) { return parseInt(t.quantity, 10) > 0; });
        var total = this.tickets.reduce(function (n, t) { return n + (parseInt(t.quantity, 10) || 0); }, 0);
        return this.tabLabels.sold + ': ' + sold + (capped ? '/' + total : '');
      },
      // One line for each row of the Tickets tab, from what is on screen.
      ticketRows() {
        var e = this.event;
        var names = function (list, key) {
          return list.map(function (item) { return (item[key] || '').trim(); }).filter(Boolean).join(', ');
        };
        var payment;
        if (this.anyTicketPriced && this.paymentWarning) {
          payment = { text: this.paymentWarning, warn: true };
        } else {
          payment = { text: [this.paymentLabels[e.payment_method], e.ticket_currency_code].filter(Boolean).join(' \u00b7 '), empty: false };
        }
        // The switches that are on, by their own labels.
        var O = this.tabLabels.options;
        var on = [];
        if (e.ask_phone) { on.push(O.ask_phone); }
        if (e.individual_tickets) { on.push(O.individual_tickets); }
        if (e.tickets_enabled && e.sell_after_start) { on.push(O.sell_after_start); }
        if (e.tickets_enabled && this.showSalesDates) { on.push(O.sales_dates); }
        if (e.tickets_enabled && e.show_unavailable_tickets) { on.push(O.show_unavailable); }
        if (this.eventCustomFields && Object.keys(this.eventCustomFields).length) { on.push(O.custom_fields); }
        if ((e.ticket_notes || '').trim()) { on.push(e.tickets_enabled ? O.ticket_notes : O.registration_notes); }
        if (e.tickets_enabled && (e.terms_url || '').trim()) { on.push(O.terms_url); }
        return {
          payment: payment,
          // A row with nothing in it says "None": naming what could be in it ("Phone number, custom
          // fields, notes") read as though those were on.
          options: on.length ? { text: on.join(', '), empty: false } : { text: this.tabLabels.none, empty: true },
          promo_codes: names(this.promoCodes, 'code') ? { text: names(this.promoCodes, 'code'), empty: false } : { text: this.tabLabels.none, empty: true },
          add_ons: names(this.addons, 'type') ? { text: names(this.addons, 'type'), empty: false } : { text: this.tabLabels.none, empty: true },
        };
      },
      anyTicketPriced() {
        return this.tickets.some(function (ticket) { return parseFloat(ticket.price) > 0; });
      },
      // One line per tab, from what is on screen now and not from what was saved.
      tabSummaries() {
        var L = this.tabLabels;
        var self = this;
        var names = function (list, key) {
          return list.map(function (item) { return (item[key] || '').trim(); }).filter(Boolean).join(', ');
        };

        var tickets;
        if (this.event.tickets_enabled) {
          var sold = this.tickets.reduce(function (n, t) { return n + self.soldCount(t); }, 0);
          // "42/120" only where there is one pool to be out of: a recurring event sells each date
          // on its own.
          var capped = ! this.isRecurring && this.tickets.length > 0 && this.tickets.every(function (t) { return parseInt(t.quantity, 10) > 0; });
          var total = this.tickets.reduce(function (n, t) { return n + (parseInt(t.quantity, 10) || 0); }, 0);
          var parts = [];
          if (sold > 0) { parts.push(L.sold + ': ' + sold + (capped ? '/' + total : '')); }
          // One unnamed type is the usual first ticket; say what it costs to get in, not "Tickets".
          var allFree = this.tickets.every(function (t) { return ! (parseFloat(t.price) > 0); });
          parts.push(names(this.tickets, 'type') || (allFree ? L.free : L.tickets));
          tickets = { text: parts.join(' \u00b7 '), empty: false };
        } else if (this.event.rsvp_enabled) {
          tickets = { text: L.registration + (this.event.rsvp_limit ? ' \u00b7 ' + L.limit + ': ' + this.event.rsvp_limit : ''), empty: false };
        } else if ((this.event.registration_url || '').trim()) {
          var link = this.event.registration_url.trim();
          try { link = new URL(link).hostname.replace(/^www\./, ''); } catch (e) {}
          tickets = { text: link, empty: false };
        } else {
          tickets = { text: L.no_tickets, empty: true };
        }

        var listing = [L.visibility[this.visibility], this.categoryLabel].concat(this.alsoListedOn).filter(Boolean).join(' \u00b7 ');

        var engagement = [];
        if (this.fanContentPending > 0) { engagement.push(L.to_review + ': ' + this.fanContentPending); }
        if (this.polls && this.polls.length) { engagement.push(L.polls + ': ' + this.polls.length); }

        var sponsors;
        if (this.event.sponsor_mode === 'none') {
          sponsors = { text: L.no_sponsors, empty: true };
        } else if (this.event.sponsor_mode === 'custom' && this.eventSponsors.length) {
          sponsors = { text: names(this.eventSponsors, 'name') || L.sponsors, empty: false };
        } else if (this.event.sponsor_mode === 'custom') {
          // Its own list, with nobody in it yet: the event's page shows none.
          sponsors = { text: L.no_sponsors, empty: true };
        } else {
          sponsors = { text: L.same_as_schedule, empty: true };
        }

        var photos = this.galleryStore.state.images.length;

        return {
          'section-details': { text: this.whenLabel, empty: ! this.whenLabel },
          'section-tickets': tickets,
          'section-participants': this.selectedMembers.length
            ? { text: names(this.selectedMembers, 'name'), empty: false }
            : { text: L.participants_prompt, empty: true },
          'section-agenda': this.eventParts.length
            ? { text: names(this.eventParts, 'name') || L.tabs['section-agenda'], empty: false }
            : { text: L.agenda_prompt, empty: true },
          'section-gallery': photos ? { text: this.galleryStripCount, empty: false } : { text: L.gallery_prompt, empty: true },
          'section-listing': { text: listing, empty: false },
          'section-calendar-sync': { text: this.calendarSummary, empty: false },
          'section-engagement': engagement.length ? { text: engagement.join(' \u00b7 '), empty: false } : { text: L.engagement_prompt, empty: true },
          'section-event-settings': sponsors,
        };
      },
      // What the save bar says. It answers one question: what will Save do, or what is in its way.
      barStatus() {
        var L = this.tabLabels;
        var self = this;
        var asTabs = function (ids) {
          return ids.filter(function (id) { return L.tabs[id]; }).map(function (id) { return { id: id, label: L.tabs[id] }; });
        };

        if (this.heldNotice) {
          return { kind: 'text', text: L.save_first, strong: true };
        }
        if (this.sectionErrors.length) {
          return { kind: 'tabs', label: L.check, tabs: asTabs(this.sectionErrors) };
        }
        // What saving will remove. Said before anything else that is not an error, and all of it.
        var removes = [];
        if (this.savedTicketMode === 'tickets' && this.savedTicketTypes > 0 && this.ticketMode !== 'tickets') {
          removes.push(L.saving_removes_tickets);
        }
        if (this.savedTicketMode === 'tickets' && this.savedAddonCount > 0 && this.ticketMode !== 'tickets') {
          removes.push(L.saving_removes_addons);
        }
        if (! this.agendaShowTimes && this.eventParts.some(function (part) { return part.start_time || part.end_time; })) {
          removes.push(L.saving_removes_times);
        }
        if (this.savedSponsorCount > 0 && this.event.sponsor_mode !== 'custom') {
          removes.push(L.saving_removes_sponsors);
        }
        if (removes.length) {
          return { kind: 'text', text: removes.join(' '), strong: true };
        }
        if (! this.eventIsSaved) {
          return {
            kind: 'new',
            visibility: L.visibility[this.visibility],
          };
        }
        if (this.visibility !== this.savedVisibility && (this.visibility === 'public' || this.savedVisibility === 'public')) {
          return { kind: 'text', text: this.visibility === 'public' ? L.saving_publishes : L.saving_hides };
        }
        var dirty = Object.keys(this.sectionDirty).filter(function (id) { return self.sectionDirty[id]; });
        if (dirty.length) {
          return { kind: 'tabs', label: L.unsaved, tabs: asTabs(dirty) };
        }
        // Unsaved, with no tab to name (a change made by script, or to something outside the tabs):
        // still never "No unsaved changes".
        if (this.isDirty) {
          return { kind: 'text', text: L.unsaved_changes };
        }
        return { kind: 'text', text: L.no_unsaved, quiet: true };
      },
      saveLabel() {
        if (this.visibility === 'draft' || this.visibility === 'internal') {
          return this.tabLabels.save_draft;
        }
        // The first save of a public event puts it in front of people: the button says so, where
        // only a quiet "Visibility: Public" in the bar did.
        return ! this.eventIsSaved && this.visibility === 'public' ? this.tabLabels.publish : this.tabLabels.save;
      },
      // A chosen venue on one line: its name, street and city, whichever it has.
      pickedVenueLine() {
        return [this.venueName, this.venueAddress1, this.venueCity].filter(Boolean).join(', ');
      },
      galleryStripCount() {
        const count = this.galleryStore.state.images.length;
        return count === 1 ? this.galleryStripLabels.one : this.galleryStripLabels.many.replace(':count', count);
      },
      galleryFinishingText() {
        return this.galleryFinishingLabel.replace(':count', this.galleryStore.pendingCount());
      },
      /**
       * The bands offered by the plan currently attached to this event.
       *
       * Empty when no plan is selected, which is what hides the per-ticket band select entirely -
       * an event with no seat map should look exactly as it did before this feature existed.
       */
      seatingBands() {
        const id = this.event.seating_plan_id;
        if (!id) return [];
        const plan = (this.seatingPlanOptions || []).find(p => String(p.id) === String(id));
        return plan ? plan.bands : [];
      },

      /** The whole option row for the attached plan, or null. */
      selectedSeatingPlan() {
        const id = this.event.seating_plan_id;
        if (!id) return null;

        return (this.seatingPlanOptions || []).find(p => String(p.id) === String(id)) || null;
      },

      /**
       * Bands of the attached plan that no ticket prices.
       *
       * Those sections exist, hold seats, and are unsellable - the docs call this "the usual reason
       * a freshly attached plan shows fewer seats than expected", and until now the only surface
       * that ever said so was a box-office refusal at the counter.
       */
      unmappedSeatingBands() {
        if (!this.selectedSeatingPlan) return [];

        const priced = new Set((this.tickets || []).filter(t => t.seating_band).map(t => String(t.seating_band)));

        return this.seatingBands.filter(band => !priced.has(String(band)));
      },

      unmappedBandWarning() {
        const plan = this.selectedSeatingPlan;
        const bands = this.unmappedSeatingBands;
        const seats = bands.reduce((n, b) => n + ((plan && plan.bandCounts && plan.bandCounts[b]) || 0), 0);

        return @json(__('messages.seating_bands_unmapped'))
          .replace(':bands', bands.join(', '))
          .replace(':count', String(seats));
      },

      /**
       * Does the payment schedule finish with enough runway before the event?
       *
       * Returns null when there is nothing to judge (no start date yet), true when it fits and
       * false when the last payment would land too late. Mirrors
       * InstallmentService::scheduleFitsBeforeEvent(); the server re-checks regardless, since the
       * API and the importers never see this form.
       */
      installmentSchedulePreview() {
        if (! this.event.installments_enabled || ! this.event.starts_at) return null;

        const count = parseInt(this.event.installment_count) || 0;
        if (count < 2) return null;

        const start = new Date(this.event.starts_at.replace(' ', 'T') + 'Z');
        if (isNaN(start.getTime())) return null;

        // Month-end clamping, matching addMonthsNoOverflow: 31 Jan + 1 month is 28 Feb, and the
        // step is always measured from the original day so it does not accumulate drift.
        const today = new Date();
        const addMonths = (d, n) => {
          const day = d.getUTCDate();
          const target = new Date(Date.UTC(d.getUTCFullYear(), d.getUTCMonth() + n, 1));
          const lastDay = new Date(Date.UTC(target.getUTCFullYear(), target.getUTCMonth() + 1, 0)).getUTCDate();
          target.setUTCDate(Math.min(day, lastDay));
          return target;
        };

        const last = addMonths(today, count - 1);
        const days = Math.max(7, parseInt(this.event.installment_final_days_before) || 14);
        const deadline = new Date(start.getTime() - days * 86400000);

        return { last: last, deadline: deadline, fits: last <= deadline, start: start, count: count };
      },
      installmentPreviewFits() {
        const p = this.installmentSchedulePreview;
        return p === null ? null : p.fits;
      },
      installmentPreviewText() {
        const p = this.installmentSchedulePreview;
        if (p === null) return '';

        const fmt = (d) => d.toLocaleDateString(undefined, { day: 'numeric', month: 'short', year: 'numeric', timeZone: 'UTC' });

        if (! p.fits) {
          return @json(__('messages.installments_does_not_fit'))
            .replace(':count', p.count)
            .replace(':last', fmt(p.last))
            .replace(':event', fmt(p.start));
        }

        return @json(__('messages.installment_preview'))
          .replace(':first', @json(__('messages.due_today')))
          .replace(':count', p.count - 1)
          .replace(':last', fmt(p.last));
      },
      visibility: {
        get() {
          if (this.event.is_internal) return 'internal';
          if (this.event.is_draft) return 'draft';
          if (this.event.is_private) return 'unlisted';
          return 'public';
        },
        set(v) {
          this.event.is_internal = (v === 'internal');
          this.event.is_private = (v === 'unlisted');
          this.event.is_draft = (v === 'draft' || v === 'internal');
          if (v !== 'unlisted') this.event.event_password = '';
        },
      },
      boostCanEnable() {
        return this.eventName && this.startsAt && new Date(this.startsAt) > new Date();
      },
      boostDynamicReason() {
        if (!this.eventName || !this.startsAt) return @json(__('messages.boost_requires_name_and_date'));
        if (new Date(this.startsAt) <= new Date()) return @json(__('messages.boost_past_event'));
        return '';
      },
      filteredMembers() {
        return this.members.filter(member => !this.selectedMembers.some(selected => selected.id === member.id));
      },
      hasLimitedPaidTickets() {
        return this.tickets.some(ticket => ticket.price > 0 && ticket.quantity > 0);
      },
      hasSameTicketQuantities() {
        // Passes don't define seat capacity; judge combined mode on seat tickets only
        // (mirrors Event::hasSameTicketQuantities() on the server).
        const seatTickets = this.tickets.filter(ticket => !ticket.is_pass);
        if (seatTickets.length <= 1) {
          return false;
        }

        // Check that all seat tickets have quantities set
        const ticketsWithQuantities = seatTickets.filter(ticket => ticket.quantity > 0);
        if (ticketsWithQuantities.length !== seatTickets.length) {
          return false;
        }

        // Check that all quantities are the same
        const quantities = ticketsWithQuantities.map(ticket => ticket.quantity);
        return new Set(quantities).size === 1;
      },
      getSameTicketQuantity() {
        if (!this.hasSameTicketQuantities) {
          return null;
        }
        return this.tickets.find(ticket => !ticket.is_pass && ticket.quantity > 0).quantity;
      },
      getTotalTicketQuantity() {
        // Always return the sum of all seat-ticket quantities for display purposes
        return this.tickets.filter(ticket => !ticket.is_pass).reduce((total, ticket) => total + (ticket.quantity || 0), 0);
      },
      getCombinedTotalQuantity() {
        // For combined mode, the total should be the same as the individual quantity
        // since we're treating it as a single pool of tickets
        if (this.hasSameTicketQuantities) {
          return this.getSameTicketQuantity;
        }
        return this.tickets.filter(ticket => !ticket.is_pass).reduce((total, ticket) => total + (ticket.quantity || 0), 0);
      },
      // One line for each row of the Engagement tab: the setting, not a description of the feature.
      engagementRows() {
        var L = this.tabLabels, E = L.engagement, s = this.engagementSettings;
        var fan = E.fan_prompt, feedback = E.feedback_prompt;
        if (E.settings_on_plan) {
          // What is in force, then whether that is the schedule's doing: "Same as schedule" alone
          // did not say whether comments were on.
          var inherited = this.engagementInherited;
          var inForce = function (key) { return s[key] === '' ? !! inherited[key] : s[key] === '1'; };
          var kinds = Object.keys(E.names).filter(inForce).map(function (key) { return E.names[key]; });
          var allInherited = Object.keys(E.names).every(function (key) { return s[key] === ''; });
          fan = (kinds.length ? kinds.join(', ') : E.disabled) + (allInherited ? ' \u00b7 ' + L.same_as_schedule : '');
          feedback = (inForce('feedback_enabled') ? E.enabled : E.disabled) + (s.feedback_enabled === '' ? ' \u00b7 ' + L.same_as_schedule : '');
        }
        if (this.fanContentPending > 0) {
          fan = L.to_review + ': ' + this.fanContentPending + ' \u00b7 ' + fan;
        }
        var polls = E.polls_prompt;
        if (this.polls.length === 1 && (this.polls[0].question || '').trim()) {
          polls = this.polls[0].question;
        } else if (this.polls.length) {
          polls = L.polls + ': ' + this.polls.length;
        }
        return { polls: polls, fan_content: fan, feedback: feedback };
      },
      // The first poll Save would drop: the server skips one with no question or fewer than two
      // options, and used to do it without a word.
      unfinishedPollIndex() {
        return this.polls.findIndex(function (poll) {
          if (poll.votes_count > 0) { return false; }
          var options = (poll.options || []).filter(function (option) { return (option || '').trim(); });
          return ! (poll.question || '').trim() || options.length < 2;
        });
      },
      // A sponsor typed into the form and not added (or an edit not confirmed): Save would post
      // nothing of it.
      pendingSponsor() {
        return this.event.sponsor_mode === 'custom'
          && !! ((this.sponsorForm.name || '').trim() || (this.sponsorForm.url || '').trim() || this.sponsorLogoFile);
      },
      // Something typed into the add form and not added yet. The form shows for "someone new" and
      // also when there is nobody to pick from, which leaves memberType on its first value.
      pendingMember() {
        return !! (this.showMemberTypeRadio &&
          (this.memberType === 'create_new' || this.filteredMembers.length === 0) &&
          (this.memberName.trim() || this.memberEmail.trim() || this.memberPhone.trim() || this.memberYoutubeUrl.trim()));
      },
    },
    watch: {
      // Growth funnel: the first time this person is shown the paid-ticket paywall. Immediate,
      // so a banner that is already showing on load counts too. Fire-and-forget: the stamp is
      // write-once on the server, and a failed beacon must never get in the way of the form.
      ticketsNeedPro: {
        immediate: true,
        handler(shown) {
          if (! shown || this.paywallSeenSent) {
            return;
          }
          this.paywallSeenSent = true;
          fetch(this.paywallSeenUrl, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-CSRF-TOKEN': @json(csrf_token()),
            },
            body: '{}',
          }).catch(() => {});
        },
      },
      'tickets.length'() {
        this.$nextTick(() => {
          if (typeof window.initHtmlEditors === 'function') {
            window.initHtmlEditors();
          }
        });
      },
      venueType() {
        this.venueEmail = "";
        this.venuePhone = "";
        this.venueSearchEmail = "";
        this.venueSearchResults = [];
        this.destroyPhoneInput('venue_phone_input');

        this.$nextTick(() => {
            var defaultCountry = "{{ $role && $role->country_code ? $role->country_code : '' }}";
            if (document.getElementById('venue_country_code_tel')) {
                if (typeof window.destroyCountryInput === 'function') {
                    window.destroyCountryInput('venue_country_code');
                }
                var ci = window.initCountryInput('venue_country_code', defaultCountry);
                if (ci) ci.setCountry(defaultCountry);
            }
            this.bindVenueCountryChange();
            this.initPhoneInput('venue_phone_input', (number) => { this.venuePhone = number; });
        });
      },
      memberType() {
        this.memberSearchResults = [];
        this.setFocusBasedOnMemberType();
      },
      memberPhone(newValue) {
        clearTimeout(this._phoneSearchTimeout);
        if (newValue && newValue.length >= 8) {
          this._phoneSearchTimeout = setTimeout(() => {
            this.searchByPhone();
          }, 400);
        }
      },
      venuePhone(newValue) {
        clearTimeout(this._venuePhoneSearchTimeout);
        if (newValue && newValue.length >= 8 && this.venueType === 'create_new') {
          this._venuePhoneSearchTimeout = setTimeout(() => {
            this.searchVenueByPhone();
          }, 400);
        }
      },
      selectedVenue() {
        this.venueName = this.selectedVenue ? this.selectedVenue.name : "";
        this.venueEmail = this.selectedVenue ? this.selectedVenue.email : "";
        this.venuePhone = this.selectedVenue ? (this.selectedVenue.phone || '') : "";
        this.venueAddress1 = this.selectedVenue ? this.selectedVenue.address1 : "";
        this.venueCity = this.selectedVenue ? this.selectedVenue.city : "";
        this.venueState = this.selectedVenue ? this.selectedVenue.state : "";
        this.venuePostalCode = this.selectedVenue ? this.selectedVenue.postal_code : "";
        this.venueCountryCode = this.selectedVenue ? this.selectedVenue.country_code : "";
        this.venueWebsite = this.selectedVenue ? this.selectedVenue.website : "";
        this.venueSearchEmail = "";
        this.venueSearchResults = [];
        this.destroyPhoneInput('venue_phone_input');
        this.$nextTick(() => {
          this.initPhoneInput('venue_phone_input', (number) => { this.venuePhone = number; }, this.venuePhone);
          if (document.getElementById('venue_country_code_tel')) {
            if (typeof window.destroyCountryInput === 'function') {
              window.destroyCountryInput('venue_country_code');
            }
            var ci = window.initCountryInput('venue_country_code', this.venueCountryCode);
            if (ci) ci.setCountry(this.venueCountryCode);
            this.bindVenueCountryChange();
          }
        });
      },
      isInPerson(newValue) {
        this.savePreferences();
        if (newValue) {
          this.$nextTick(() => {
            if (document.getElementById('venue_country_code_tel')) {
              if (typeof window.destroyCountryInput === 'function') {
                window.destroyCountryInput('venue_country_code');
              }
              window.initCountryInput('venue_country_code', this.venueCountryCode);
              this.bindVenueCountryChange();
            }
            this.initPhoneInput('venue_phone_input', (number) => { this.venuePhone = number; }, this.venuePhone);
          });
        } else {
          this.destroyPhoneInput('venue_phone_input');
        }
      },
      isOnline(newValue) {
        if (!newValue) {
          this.clearEventUrl();
        }
        this.savePreferences();
      },
      selectedMembers: {
        handler(newValue, oldValue) {
          if (!this.eventName && newValue.length === 1) {
            this.eventName = newValue[0].name;
          }
          
          // Clean up sendEmailToMembers/sendSmsToMembers for removed members
          if (oldValue && Array.isArray(oldValue)) {
            oldValue.forEach(oldMember => {
              const newMember = newValue.find(m => m.id === oldMember.id);
              if (!newMember) {
                if (oldMember.email && this.sendEmailToMembers[oldMember.email] !== undefined) {
                  delete this.sendEmailToMembers[oldMember.email];
                }
                if (oldMember.phone && this.sendSmsToMembers[oldMember.phone] !== undefined) {
                  delete this.sendSmsToMembers[oldMember.phone];
                }
              }
            });
          }
        },
        deep: true
      },
      ticketMode(newValue) {
        this.event.tickets_enabled = (newValue === 'tickets');
        this.event.rsvp_enabled = (newValue === 'rsvp');
        this.activeTicketTab = 'tickets';
        this.savePreferences();
      },
      'event.recurring_frequency'() {
        updateRecurringFieldVisibility();
      },
      isRecurring(newValue) {
        if (newValue && (this.recurringIncludeDates.length > 0 || this.recurringExcludeDates.length > 0)) {
          this.initAllRecurringDatePickers();
        }
      },
      'event.recurring_end_type'(newValue, oldValue) {
        // Clear the value when switching between types
        if (oldValue && newValue !== oldValue) {
          this.event.recurring_end_value = null;
        }

        if (newValue === 'on_date') {
          this.initializeRecurringEndDatePicker();
        }
      },
      agendaShowDescription(newVal) {
        if (newVal) {
          this.initAllPartEditors();
        } else {
          this.destroyAllPartEditors();
        }
      },
      'event.sponsor_mode'(newVal) {
        if (newVal === 'custom') {
          this.$nextTick(() => this.initEventSponsorSortable());
        }
        if (newVal !== 'custom') {
          this.cancelEditEventSponsor();
        }
      },
    },
    mounted() {
      this.showMemberTypeRadio = this.selectedMembers.length === 0;
      // The add form's phone field, wired once: its element is always on the page.
      this.$nextTick(() => this.initPhoneInput('member_phone_input', (number) => { this.memberPhone = number; }));
      this.$nextTick(() => updateRecurringFieldVisibility());
      this.$nextTick(() => this.initEventSponsorSortable());

      const isCloned = @json($isCloned ?? false);

      if (this.event.id) {
        // Existing event - use event data
        this.isInPerson = !!this.event.venue || !!this.selectedVenue;
        this.isOnline = !!this.event.event_url;
        // Neither: show the venue fields, as a new event does. Two unpressed pills said nothing.
        if (! this.isInPerson && ! this.isOnline) {
          this.isInPerson = true;
        }
      } else if (isCloned) {
        // Cloned event - use cloned data, don't load from localStorage
        this.isInPerson = !!this.selectedVenue;
        this.isOnline = !!this.event.event_url;
      } else {
        // New event - load from localStorage
        this.loadPreferences();

        if (!this.isInPerson && !this.isOnline) {
          this.isInPerson = true;
        }
      }

      // Snapshot original key values so we can detect a material change on save (issue #94). Captured
      // after init watchers settle; mirrors the server's comparison (venue by id, url normalized).
      this.$nextTick(() => {
        this.origStartsAt = this.startsAt;
        this.origDuration = this.currentDuration;
        this.origVenueId = this.eventIsSaved ? this.savedVenueId : (this.selectedVenue ? this.selectedVenue.id : null);
        this.origEventUrl = this.normalizeUrl(this.event.event_url);
        this.origIsOnline = this.isOnline;
        this.origIsInPerson = this.isInPerson;
      });

      if (this.refusedEventName !== null) {
        this.eventName = this.refusedEventName;
      } else if (this.event.id) {
        this.eventName = this.event.name;
      } else if (isCloned && this.event.name) {
        // Cloned event - preserve the cloned event name
        this.eventName = this.event.name;
      } else if (this.selectedMembers.length === 1) {
        // New event with single member - use member name
        this.eventName = this.selectedMembers[0].name;
      }

      // Initialize curator group selections
      this.initializeCuratorGroupSelections();

      // Initialize part description editors
      this.$nextTick(() => {
        this.initAllPartEditors();
      });

      // Initialize date pickers after Vite module loads (modules execute before DOMContentLoaded)
      var self = this;
      document.addEventListener('DOMContentLoaded', function() {
        if (self.event.recurring_end_type === 'on_date') {
          self.initializeRecurringEndDatePicker();
        }
        if (self.isRecurring && (self.recurringIncludeDates.length > 0 || self.recurringExcludeDates.length > 0)) {
          self.initAllRecurringDatePickers();
        }
        if (self.promoCodes.length > 0) {
          self.initAllPromoDatePickers();
        }
        self.initAllTicketSalesStartDatePickers();
        self.initAllTicketSalesEndDatePickers();
      });
      
      // Initialize venue phone input if venue section is visible
      if (this.isInPerson) {
        this.$nextTick(() => {
          this.initPhoneInput('venue_phone_input', (number) => { this.venuePhone = number; }, this.venuePhone);
          if (document.getElementById('venue_country_code_tel')) {
            window.initCountryInput('venue_country_code', this.venueCountryCode);
          }
          this.bindVenueCountryChange();
        });
      }

      // Initialize sendEmailToMembers/sendSmsToMembers for existing selectedMembers
      this.selectedMembers.forEach(member => {
        if ((member.id && member.id.toString().startsWith('new_')) || !member.user_id) {
          if (member.email) {
            this.sendEmailToMembers[member.email] = false;
          } else if (member.phone) {
            this.sendSmsToMembers[member.phone] = false;
          }
        }
      });

      // Unsaved changes warning
      var dirtyForm = document.querySelector('form[enctype]');
      if (dirtyForm) {
          dirtyForm.addEventListener('input', (e) => { this.markDirty(e.target); this.readPlainFields(); });
          dirtyForm.addEventListener('change', (e) => { this.markDirty(e.target); this.readPlainFields(); });
      }
      this.readPlainFields();
      this.moreTabNames = (window.eventFoldedSections || []).filter(function (id) {
        return document.getElementById(id) !== null;
      }).map((id) => this.tabLabels.tabs[id]).filter(Boolean).join(', ');

      // Changes that are not typing: a ticket type removed, a participant added, a venue picked.
      // None of these fires an input event, so until now none of them counted as a change at all
      // and the page could be left without a warning. Armed after the form has finished setting
      // itself up, which also writes to these lists.
      var listTabs = {
        tickets: 'section-tickets', promoCodes: 'section-tickets', addons: 'section-tickets', ticketMode: 'section-tickets',
        eventCustomFields: 'section-tickets', 'event.installments_enabled': 'section-tickets',
        selectedMembers: 'section-participants', eventParts: 'section-agenda', eventSponsors: 'section-event-settings',
        selectedVenue: 'section-details', isInPerson: 'section-details', isOnline: 'section-details', isRecurring: 'section-details',
        visibility: 'section-listing',
      };
      Object.keys(listTabs).forEach((key) => {
        this.$watch(key, () => this.markTabDirty(listTabs[key]), { deep: true });
      });
      setTimeout(() => { this.dirtyArmed = true; }, 400);
      window.addEventListener('beforeunload', (e) => {
          if (this.isDirty && !window._skipUnsavedWarning) { e.preventDefault(); e.returnValue = ''; }
      });

      this.$nextTick(() => this.initCustomFieldsSortable());
    },
    updated() {
      this.$nextTick(() => this.initCustomFieldsSortable());
    }
  });
  app.component('gallery-editor', window.EsGallery.component);
  const vueInstance = app.mount('#app');

  // Store reference for section navigation
  window.vueApp = vueInstance;

  // Initialize EasyMDE on .html-editor textareas inside #app. app.js's own
  // DOMContentLoaded handler skips these because Vue's template compile would
  // clobber the EasyMDE wrapper; defer to DOMContentLoaded so the deferred
  // app.js module has loaded and window.initHtmlEditors is defined.
  document.addEventListener('DOMContentLoaded', () => {
    if (typeof window.initHtmlEditors === 'function') {
      window.initHtmlEditors();
    }
  });

  // --- Migrated inline event handlers ---

  // Delete event form confirmation (line 828 originally)
  var deleteForm = document.getElementById('event-delete-form');
  if (deleteForm) {
    deleteForm.addEventListener('submit', function(e) {
      if (!confirm(@json(__('messages.are_you_sure')))) {
        e.preventDefault();
      }
    });
  }

  // Copy event URL button (line 1013 originally)
  var copyUrlBtn = document.getElementById('copy-event-url-btn');
  if (copyUrlBtn) {
    copyUrlBtn.addEventListener('click', function() {
      copyEventUrl(this);
    });
  }

  // Edit/Cancel slug buttons
  var editSlugBtn = document.getElementById('edit-slug-btn');
  if (editSlugBtn) {
    editSlugBtn.addEventListener('click', function() {
      toggleEventSlugEdit();
    });
  }
  var cancelSlugBtn = document.getElementById('cancel-slug-btn');
  if (cancelSlugBtn) {
    cancelSlugBtn.addEventListener('click', function() {
      toggleEventSlugEdit();
    });
  }

  // Flyer image file input change (line 1105 originally)
  var flyerImageInput = document.getElementById('flyer_image');
  if (flyerImageInput) {
    flyerImageInput.addEventListener('change', function() {
      previewImage(this);
    });
  }

  // Choose file button for flyer (line 1107 originally)
  var flyerChooseBtn = document.getElementById('flyer-choose-btn');
  if (flyerChooseBtn) {
    flyerChooseBtn.addEventListener('click', function() {
      var fi = document.getElementById('flyer_image');
      fi.value = null;
      fi.click();
    });
  }

  // Clear flyer preview button (line 1123 originally)
  var clearFlyerPreviewBtn = document.getElementById('clear-flyer-preview-btn');
  if (clearFlyerPreviewBtn) {
    clearFlyerPreviewBtn.addEventListener('click', function() {
      clearFileInput('flyer_image');
    });
  }

  // Delete existing flyer button (line 1130 originally)
  var deleteFlyerBtn = document.getElementById('delete-flyer-btn');
  if (deleteFlyerBtn) {
    deleteFlyerBtn.addEventListener('click', function() {
      deleteFlyer(this.dataset.url, this.dataset.hash, this.dataset.token, this.parentElement);
    });
  }

  // Schedule type radio buttons (lines 1635, 1641 originally)
  var oneTimeRadio = document.getElementById('one_time');
  if (oneTimeRadio) {
    oneTimeRadio.addEventListener('change', function() {
      onChangeDateType();
    });
  }
  var recurringRadio = document.getElementById('recurring');
  if (recurringRadio) {
    recurringRadio.addEventListener('change', function() {
      onChangeDateType();
    });
  }

  // Unsync from Google Calendar button (line 2523 originally)
  var unsyncBtn = document.getElementById('unsync-event-btn');
  if (unsyncBtn) {
    unsyncBtn.addEventListener('click', function() {
      unsyncEvent(this.dataset.subdomain, parseInt(this.dataset.eventId));
    });
  }

  // Sync to Google Calendar button (line 2533 originally)
  var syncBtn = document.getElementById('sync-event-btn');
  if (syncBtn) {
    syncBtn.addEventListener('click', function() {
      syncEvent(this.dataset.subdomain, parseInt(this.dataset.eventId));
    });
  }

  // Unsync from Outlook Calendar button
  var microsoftUnsyncBtn = document.getElementById('microsoft-unsync-event-btn');
  if (microsoftUnsyncBtn) {
    microsoftUnsyncBtn.addEventListener('click', function() {
      microsoftUnsyncEvent(this.dataset.subdomain, parseInt(this.dataset.eventId));
    });
  }

  // Sync to Outlook Calendar button
  var microsoftSyncBtn = document.getElementById('microsoft-sync-event-btn');
  if (microsoftSyncBtn) {
    microsoftSyncBtn.addEventListener('click', function() {
      microsoftSyncEvent(this.dataset.subdomain, parseInt(this.dataset.eventId));
    });
  }

  // The four calendar buttons (sync and remove, for Google and for Outlook) are one request each,
  // made at once and not with the save. While it runs its button is off, so a second press does
  // not send a second request; and what went wrong is said in the calendar's own row, where four
  // alert() boxes used to say it (one of them the raw "HTTP 500: ..." text).
  function calendarAction(button, url, method, statusId, errorId, confirmText) {
    if (confirmText && ! confirm(confirmText)) {
      return;
    }
    const status = document.getElementById(statusId);
    const error = document.getElementById(errorId);
    const failed = function (message) {
      if (status) { status.classList.add('hidden'); }
      if (button) { button.disabled = false; }
      if (error) {
        error.textContent = message || @json(__('messages.something_went_wrong'));
        error.classList.remove('hidden');
      }
    };
    if (button) { button.disabled = true; }
    if (error) { error.classList.add('hidden'); }
    if (status) { status.classList.remove('hidden'); }

    fetch(url, {
      method: method,
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
    })
    .then(response => response.json().catch(() => ({})).then(data => ({ ok: response.ok, data: data })))
    .then(result => {
      if (! result.ok || result.data.error) {
        failed(result.data.error);
        return;
      }
      location.reload();
    })
    .catch(() => failed());
  }

  function microsoftSyncEvent(subdomain, eventId) {
    calendarAction(document.getElementById('microsoft-sync-event-btn'), `{{ url('/microsoft-calendar/sync-event') }}/${subdomain}/${eventId}`, 'POST',
      `microsoft-sync-status-${eventId}`, `microsoft-sync-error-${eventId}`);
  }

  function microsoftUnsyncEvent(subdomain, eventId) {
    calendarAction(document.getElementById('microsoft-unsync-event-btn'), `{{ url('/microsoft-calendar/unsync-event') }}/${subdomain}/${eventId}`, 'DELETE',
      `microsoft-sync-status-${eventId}`, `microsoft-sync-error-${eventId}`, @json(__('messages.confirm_remove_microsoft_calendar')));
  }

  function syncEvent(subdomain, eventId) {
    calendarAction(document.getElementById('sync-event-btn'), `{{ url('/google-calendar/sync-event') }}/${subdomain}/${eventId}`, 'POST',
      `sync-status-${eventId}`, `sync-error-${eventId}`);
  }

  function unsyncEvent(subdomain, eventId) {
    calendarAction(document.getElementById('unsync-event-btn'), `{{ url('/google-calendar/unsync-event') }}/${subdomain}/${eventId}`, 'DELETE',
      `sync-status-${eventId}`, `sync-error-${eventId}`, @json(__('messages.confirm_remove_google_calendar')));
  }

// Actions that reload the page wait while the form has unsaved changes. Capture phase, so this
// runs before the button's own handler and before a data-confirm prompt asks about an action that
// is not going to happen. Deleting the event is let through: it discards the form anyway.
document.addEventListener('click', function (e) {
    var app = window.vueApp;
    if (! app || ! app.isDirty || ! e.target.closest) {
        return;
    }
    var button = e.target.closest('button[form], #sync-event-btn, #unsync-event-btn, #microsoft-sync-event-btn, #microsoft-unsync-event-btn');
    if (! button || ['edit-form', 'event-delete-form'].indexOf(button.getAttribute('form')) !== -1) {
        return;
    }
    e.preventDefault();
    e.stopImmediatePropagation();
    app.holdAction();
}, true);

// A flyer dropped on Basics or pasted from the clipboard goes into the same file input the
// Choose File button fills, so everything after that (preview, size warning, upload) is unchanged.
// An event that has a saved flyer keeps it until it is removed, as before.
(function () {
    function imageIn(list) {
        return Array.prototype.find.call(list || [], function (file) {
            return file && ['image/png', 'image/jpeg'].indexOf(file.type) !== -1;
        });
    }
    function takeFlyer(file) {
        var input = document.getElementById('flyer_image');
        if (! input || ! file || document.getElementById('flyer_image_existing') || typeof DataTransfer === 'undefined') {
            return false;
        }
        var transfer = new DataTransfer();
        transfer.items.add(file);
        input.files = transfer.files;
        input.dispatchEvent(new Event('change', { bubbles: true }));
        return true;
    }
    function hasFiles(e) {
        return e.dataTransfer && Array.prototype.indexOf.call(e.dataTransfer.types || [], 'Files') !== -1;
    }
    document.addEventListener('DOMContentLoaded', function () {
        var basics = document.getElementById('event-basics');
        if (! basics) {
            return;
        }
        basics.addEventListener('dragover', function (e) {
            if (hasFiles(e)) {
                e.preventDefault();
                basics.classList.add('is-dropping');
            }
        });
        basics.addEventListener('dragleave', function (e) {
            if (! basics.contains(e.relatedTarget)) {
                basics.classList.remove('is-dropping');
            }
        });
        basics.addEventListener('drop', function (e) {
            basics.classList.remove('is-dropping');
            if (hasFiles(e)) {
                e.preventDefault();
                takeFlyer(imageIn(e.dataTransfer.files));
            }
        });
    });
    document.addEventListener('paste', function (e) {
        var details = document.getElementById('section-details');
        var file = e.clipboardData ? imageIn(e.clipboardData.files) : null;
        // Only while the Event tab is the one on screen: a pasted image anywhere else is not a flyer.
        if (! file || ! details || details.offsetParent === null) {
            return;
        }
        if (takeFlyer(file)) {
            e.preventDefault();
        }
    });
    window.takeEventFlyer = takeFlyer;
})();

// Ctrl+S / Cmd+S saves, through the same submit the Save button makes.
document.addEventListener('keydown', function (e) {
    if ((e.ctrlKey || e.metaKey) && ! e.altKey && (e.key === 's' || e.key === 'S')) {
        var form = document.getElementById('edit-form');
        if (form && form.requestSubmit) {
            e.preventDefault();
            // A key held down repeats, and a save already on its way must not be sent again: on a
            // new event that made two events.
            if (e.repeat || (window.vueApp && window.vueApp.isSaving)) {
                return;
            }
            form.requestSubmit();
        }
    }
});

// A page the browser brings back whole (Back, after a save) is the page as it was left: still
// "saving", so Save was disabled and Ctrl+S did nothing until a reload, and with the warning about
// unsaved changes still switched off.
window.addEventListener('pageshow', function (e) {
    if (e.persisted && window.vueApp) {
        window.vueApp.isSaving = false;
        window._skipUnsavedWarning = false;
    }
});

// Section navigation functionality
document.addEventListener('DOMContentLoaded', function() {
    const sectionLinks = document.querySelectorAll('.section-nav-link');
    const sections = document.querySelectorAll('.section-content');

    // Save the initial hash before anti-scroll logic strips it
    const initialHash = window.resolveEventSectionHash(window.location.hash, window.location.search).section;

    // Prevent browser from scrolling to hash on page load
    if (window.location.hash) {
        // Temporarily remove hash to prevent auto-scroll
        const hash = window.location.hash;
        history.replaceState(null, null, ' ');
        window.scrollTo(0, 0);
        // Restore hash without scrolling
        setTimeout(function() {
            if (history.pushState) {
                history.replaceState(null, null, hash);
            }
            window.scrollTo(0, 0);
        }, 0);
    }
    
    const mobileHeaders = document.querySelectorAll('.mobile-section-header');

    // Track current section for navigation blocking
    let currentSectionId = null;

    // Function to sync mobile accordion headers
    function syncMobileHeaders(sectionId) {
        mobileHeaders.forEach(header => {
            if (header.getAttribute('data-section') === sectionId) {
                header.classList.add('active');
            } else {
                header.classList.remove('active');
            }
        });
    }

    // Function to show a specific section and hide others
    function showSection(sectionId, preventScroll = false) {
        // Leaving Participants with its add form open used to block the move (a shake of the Done
        // button and nothing said) or throw away what was typed. It does neither: the form stays as
        // it is, and Save adds a participant that has a name (validateForm).

        // Track current section
        currentSectionId = sectionId;

        // A section folded behind "More options" on a first event opens the fold when anything
        // navigates to it (a #hash, an invalid field), or its header would stay hidden while its
        // content shows.
        if (window.vueApp && ! window.vueApp.showMoreSections
            && window.eventFoldedSections.includes(sectionId)) {
            window.vueApp.showMoreSections = true;
        }

        sections.forEach(section => {
            if (section.id === sectionId) {
                section.style.display = 'block';
            } else {
                section.style.display = 'none';
            }
        });

        // Update active link
        sectionLinks.forEach(link => {
            if (link.getAttribute('data-section') === sectionId) {
                link.classList.add('nav-active');
            } else {
                link.classList.remove('nav-active');
            }
        });

        // Sync mobile accordion headers
        syncMobileHeaders(sectionId);

        // Update URL hash
        if (history.replaceState) {
            history.replaceState(null, null, '#' + sectionId);
        } else {
            window.location.hash = sectionId;
        }

        // Prevent scroll if requested
        if (preventScroll) {
            window.scrollTo(0, 0);
        }
    }

    // Reset to first tab if the section is already active
    // Pressing the tab you are already on. Its rows are left as they are: this used to press the
    // first [data-tab] in the section, which was the first of a strip of inner tabs when there were
    // strips, and became "open the Payment row" once they were rows.
    function alreadyOn(sectionId) {
        const section = document.getElementById(sectionId);
        return !! (section && section.style.display === 'block');
    }

    // Handle navigation link clicks
    sectionLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const sectionId = this.getAttribute('data-section');
            if (!alreadyOn(sectionId)) {
                showSection(sectionId);
            }
        });
    });

    // Handle mobile accordion header clicks
    mobileHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const sectionId = this.getAttribute('data-section');
            if (!alreadyOn(sectionId)) {
                showSection(sectionId);
            }
        });
    });

    // Check if we're on a large screen
    function isLargeScreen() {
        return window.matchMedia('(min-width: 1024px)').matches;
    }

    // Initialize: show section based on hash or first section
    function initializeSections() {
        // Use saved initialHash since anti-scroll logic may have stripped it
        var errorTabs = (window.vueApp && window.vueApp.sectionErrors) || [];
        errorTabs.forEach(function (id) {
            if (document.getElementById(id)) { highlightSectionError(id); }
        });
        if (initialHash && document.getElementById(initialHash)) {
            showSection(initialHash, true); // Prevent scroll on initial load
        } else if (errorTabs.length && document.getElementById(errorTabs[0])) {
            // A refused save: open the first tab that has something wrong on it. Until now the
            // form came back on its first tab whatever the errors were about.
            showSection(errorTabs[0], true);
        } else {
            // Show first section
            const firstSection = sections[0];
            if (firstSection) {
                showSection(firstSection.id, true); // Prevent scroll on initial load
            }
        }
    }

    // Handle hash changes
    window.addEventListener('hashchange', function() {
        const target = window.resolveEventSectionHash(window.location.hash, window.location.search);
        if (target.section && document.getElementById(target.section)) {
            if ((window.eventSectionAliases[window.location.hash.replace('#', '')] || [])[1] && window.vueApp) {
                window.vueApp.activeEngagementTab = target.engagementTab;
            }
            showSection(target.section);
        }
    });
    
    // Initialize on page load
    initializeSections();

    // For validateForm() in the Vue app, which runs outside this closure.
    window.showEventSection = showSection;

    // Mark a tab as holding an error, on the sidebar and on a phone.
    //
    // Declared here and not inside the block below: initializeSections() calls it, and runs before
    // that block does. A function declared in a block does not exist outside it until the block has
    // run, so a page coming back from a refused save threw "highlightSectionError is not a
    // function" there, before it had opened the tab the save was refused on.
    function highlightSectionError(sectionId) {
        if (!sectionId) return;

        const sectionLink = document.querySelector(`.section-nav-link[data-section="${sectionId}"]`);
        if (sectionLink) {
            sectionLink.classList.add('validation-error');
        }
        const mobileHeader = document.querySelector(`.mobile-section-header[data-section="${sectionId}"]`);
        if (mobileHeader) {
            mobileHeader.classList.add('validation-error');
        }
    }
    window.highlightEventSectionError = highlightSectionError;

    // Form validation error handling
    const form = document.getElementById('edit-form');
    if (form) {

        // Function to clear section error highlight by section ID
        function clearSectionErrorById(sectionId) {
            if (!sectionId) return;

            const sectionLink = document.querySelector(`.section-nav-link[data-section="${sectionId}"]`);
            if (sectionLink) {
                sectionLink.classList.remove('validation-error');
            }
            const mobileHeader = document.querySelector(`.mobile-section-header[data-section="${sectionId}"]`);
            if (mobileHeader) {
                mobileHeader.classList.remove('validation-error');
            }
        }

        // Highlight sections with server-side validation errors
        document.querySelectorAll('.section-content').forEach(section => {
            const hasErrors = section.querySelectorAll('ul.text-red-600, ul.text-red-400').length > 0;
            if (hasErrors) {
                highlightSectionError(section.id);

                // A red nav link inside the folded "More options" group is invisible, so open the
                // fold rather than leave the error unreachable after a refused first-event save.
                if (window.vueApp && ! window.vueApp.showMoreSections
                    && window.eventFoldedSections.includes(section.id)) {
                    window.vueApp.showMoreSections = true;
                }
            }
        });

        // Handle invalid event on ANY required field (including custom fields)
        form.addEventListener('invalid', function(e) {
            const field = e.target;
            const sectionContent = field.closest('.section-content');
            if (sectionContent) {
                highlightSectionError(sectionContent.id);
            }
        }, true); // Use capture phase since invalid doesn't bubble

        // Form submit handler - check validity before submission
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();

                if (window.vueApp) {
                    window.vueApp.isSaving = false;
                }

                // Find first invalid field across ALL form elements
                let firstInvalidField = null;
                let firstInvalidSection = null;
                const allFields = form.querySelectorAll('input, select, textarea');

                for (const field of allFields) {
                    if (!field.checkValidity()) {
                        if (!firstInvalidField) {
                            firstInvalidField = field;
                            const sectionContent = field.closest('.section-content');
                            firstInvalidSection = sectionContent ? sectionContent.id : null;
                        }
                    }
                }

                if (firstInvalidField && firstInvalidSection) {
                    if (window.vueApp && window.vueApp.revealField) {
                        window.vueApp.revealField(firstInvalidField);
                        // validateForm() ran first and cleared this, expecting the page to leave.
                        window.vueApp.isDirty = true;
                    }
                    showSection(firstInvalidSection);
                    highlightSectionError(firstInvalidSection);

                    setTimeout(() => {
                        firstInvalidField.focus();
                        firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        firstInvalidField.reportValidity();
                    }, 100);
                }
            }
        });

        // Clear error state when any required field becomes valid
        const allRequiredFields = form.querySelectorAll('[required]');
        allRequiredFields.forEach(field => {
            const handler = function() {
                if (this.checkValidity()) {
                    const sectionContent = this.closest('.section-content');
                    if (sectionContent) {
                        const sectionFields = sectionContent.querySelectorAll('[required]');
                        const allValid = Array.from(sectionFields).every(f => f.checkValidity());
                        if (allValid) {
                            clearSectionErrorById(sectionContent.id);
                        }
                    }
                }
            };
            field.addEventListener('input', handler);
            field.addEventListener('change', handler);
        });
    }
    
});

window.scrollTo(0, 0);
var isNewEvent = {{ $event->exists ? 'false' : 'true' }};
if (isNewEvent) {
    var nameField = document.getElementById('event_name');
    if (nameField) {
        nameField.focus({ preventScroll: true });
    }
}

function deleteFlyer(url, hash, token, element) {
    if (!confirm(@json(__('messages.are_you_sure')))) {
        return;
    }

    var aiInput = document.getElementById('ai_flyer_image');
    if (aiInput) {
        var img = document.getElementById('flyer_preview');
        if (img && img.dataset.originalSrc) {
            img.src = img.dataset.originalSrc;
            img.setAttribute('data-lightbox-src', img.dataset.originalLightboxSrc || img.dataset.originalSrc);
            delete img.dataset.originalSrc;
            delete img.dataset.originalLightboxSrc;
        } else {
            element.remove();
            var chooseSection = document.getElementById('flyer_image_choose');
            if (chooseSection) chooseSection.style.display = '';
        }
        aiInput.remove();
        return;
    }

    if (!hash) {
        element.remove();
        var aiInput = document.getElementById('ai_flyer_image');
        if (aiInput) aiInput.remove();
        var cloneInput = document.getElementById('clone_flyer_image');
        if (cloneInput) cloneInput.remove();
        var chooseSection = document.getElementById('flyer_image_choose');
        if (chooseSection) chooseSection.style.display = '';
        return;
    }

    fetch(url + '?hash=' + hash + '&image_type=flyer', {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json'
        }
    }).then(response => {
        if (response.ok) {
            element.remove();
            var chooseSection = document.getElementById('flyer_image_choose');
            if (chooseSection) chooseSection.style.display = '';
        } else {
            alert(@json(__('messages.failed_to_delete_image')));
        }
    });
}
</script>

@if(config('app.hosted'))
<x-upgrade-modal name="upgrade-polls" tier="pro" :subdomain="$subdomain" :learnMoreUrl="marketing_url('/features/polls')">
    {{ __('messages.polls_pro_only') }}
</x-upgrade-modal>

<x-upgrade-modal name="upgrade-feedback" tier="pro" :subdomain="$subdomain" :learnMoreUrl="marketing_url('/features/feedback')">
    {{ __('messages.feedback_pro_only') }}
</x-upgrade-modal>

<x-upgrade-modal name="upgrade-boost" tier="pro" :subdomain="$subdomain" :learnMoreUrl="marketing_url('/features/boost')">
    {{ __('messages.upgrade_feature_description_boost') }}
</x-upgrade-modal>

{{-- Declared here for years with nothing dispatching it. Now the "See what Pro adds" link in the
     paid-ticket plan gate opens it, and it names what Pro adds rather than saying
     "Pro Feature". --}}
<x-upgrade-modal name="upgrade-tickets" tier="pro" :subdomain="$subdomain"
    :learnMoreUrl="marketing_url('/features/ticketing')"
    :title="__('messages.upgrade_feature_title_tickets')"
    :bullets="[
        __('messages.ticket_pro_bullet_selling'),
        __('messages.ticket_pro_bullet_checkin'),
        __('messages.ticket_pro_bullet_promo'),
        __('messages.ticket_pro_bullet_waitlist'),
        __('messages.ticket_pro_bullet_passes'),
        __('messages.ticket_pro_bullet_export'),
    ]">
    {{ __('messages.upgrade_feature_description_tickets') }}
</x-upgrade-modal>

<x-upgrade-modal name="upgrade-privacy" tier="enterprise" :subdomain="$subdomain" :learnMoreUrl="marketing_url('/features/private-events')">
    {{ __('messages.upgrade_feature_description_privacy') }}
</x-upgrade-modal>
@endif

@if ((config('services.google.gemini_key') || config('services.openai.api_key')) && !is_demo_mode() && $role->isEnterprise())
@php
    $aiFields = [
        ['key' => 'category_id', 'label' => __('messages.category'), 'has_value' => (bool)$event->category_id],
        ['key' => 'flyer_image', 'label' => __('messages.flyer_image'), 'has_value' => (bool)$event->flyer_image_url],
        ['key' => 'short_description', 'label' => __('messages.short_description'), 'has_value' => (bool)$event->short_description],
        ['key' => 'description', 'label' => __('messages.description'), 'has_value' => (bool)$event->description],
    ];
@endphp
<x-ai-generate-modal
    name="ai-event-details"
    :title="__('messages.ai_generator')"
    :description="__('messages.ai_details_description')"
    :fields="$aiFields"
    endpoint="{{ url('/'.$subdomain.'/generate-event-details') }}"
    imageEndpoint="{{ url('/'.$subdomain.'/generate-flyer') }}"
    :imageElements="['flyer_image']"
    successCallback="handleAiEventDetailsResults"
    extraDataCallback="getEventDetailsExtraData"
    checkValuesCallback="getEventDetailsCurrentValues"
    :showInstructions="true"
    :errorMessage="__('messages.ai_details_generation_failed')"
    :promptEndpoint="url('/'.$subdomain.'/get-event-details-prompt')"
    :instructionsLabel="__('messages.ai_additional_instructions')"
    :instructionsPlaceholder="__('messages.ai_additional_instructions_placeholder')"
    savedInstructions="{{ $role->ai_content_instructions }}"
    saveInstructionsField="ai_content_instructions"
    :previewConfig="[
        'category_id' => ['type' => 'category', 'data_key' => 'category_id'],
        'flyer_image' => ['type' => 'image', 'data_key' => 'flyer_image_url', 'aspect' => '3:4'],
        'short_description' => ['type' => 'text', 'data_key' => 'short_description'],
        'description' => ['type' => 'markdown', 'data_key' => 'description'],
    ]"
    :categoryMap="$event_categories"
/>

<script {!! nonce_attr() !!}>
window.getEventDetailsExtraData = function() {
    var descTextarea = document.getElementById('description');
    var descValue = descTextarea && descTextarea._easyMDE ? descTextarea._easyMDE.value() : (descTextarea ? descTextarea.value : '');
    var startsAt = document.getElementById('starts_at');
    var duration = document.getElementById('duration');
    var categoryId = document.getElementById('category_id');
    return {
        name: document.getElementById('event_name').value,
        short_description: document.getElementById('short_description').value,
        schedule_name: @json($role->name),
        schedule_type: @json($role->type),
        description: descValue,
        event_id: @json($event->exists ? \App\Utils\UrlUtils::encodeId($event->id) : ''),
        starts_at: startsAt ? startsAt.value : '',
        duration: duration ? duration.value : '',
        category_id: categoryId ? categoryId.value : '',
        venue_name: document.querySelector('input[name="venue_name"]')?.value || '',
        venue_address1: document.querySelector('input[name="venue_address1"]')?.value || '',
        venue_city: document.querySelector('input[name="venue_city"]')?.value || ''
    };
};

window.getEventDetailsCurrentValues = function() {
    var values = [];
    var categorySelect = document.getElementById('category_id');
    if (categorySelect && categorySelect.value) values.push('category_id');
    var shortDesc = document.getElementById('short_description');
    if (shortDesc && shortDesc.value.trim()) values.push('short_description');
    var descTextarea = document.getElementById('description');
    var descValue = descTextarea && descTextarea._easyMDE ? descTextarea._easyMDE.value() : (descTextarea ? descTextarea.value : '');
    if (descValue.trim()) values.push('description');
    var flyerExisting = document.getElementById('flyer_image_existing');
    var flyerFileInput = document.getElementById('flyer_image');
    if (flyerExisting || (flyerFileInput && flyerFileInput.files && flyerFileInput.files.length > 0)) values.push('flyer_image');
    return values;
};

window.handleAiEventDetailsResults = function(data) {
    if (data.category_id) {
        var select = document.getElementById('category_id');
        select.value = data.category_id;
        select.dispatchEvent(new Event('change', { bubbles: true }));
    }
    if (data.short_description) {
        var el = document.getElementById('short_description');
        el.value = data.short_description;
        el.dispatchEvent(new Event('input', { bubbles: true }));
    }
    if (data.description) {
        var textarea = document.getElementById('description');
        if (textarea._easyMDE) {
            textarea._easyMDE.value(data.description);
        } else {
            textarea.value = data.description;
        }
    }
    if (data.flyer_image_url) {
        var existingDiv = document.getElementById('flyer_image_existing');
        if (existingDiv) {
            var img = document.getElementById('flyer_preview');
            if (img) {
                if (!img.dataset.originalSrc) {
                    img.dataset.originalSrc = img.src;
                    img.dataset.originalLightboxSrc = img.getAttribute('data-lightbox-src') || '';
                }
                img.src = data.flyer_image_url;
                img.setAttribute('data-lightbox-src', data.flyer_image_url);
            }
        } else {
            var container = document.createElement('div');
            container.id = 'flyer_image_existing';
            container.className = 'relative inline-block mt-4 pt-1';

            var img = document.createElement('img');
            img.src = data.flyer_image_url;
            img.alt = @json(__('messages.flyer_image'));
            img.style.maxHeight = '120px';
            img.className = 'rounded-lg border border-gray-200 dark:border-gray-600';
            img.id = 'flyer_preview';
            container.appendChild(img);

            var deleteBtn = document.createElement('button');
            deleteBtn.type = 'button';
            deleteBtn.id = 'delete-flyer-btn';
            deleteBtn.dataset.url = data.delete_url;
            deleteBtn.dataset.hash = @json($event->exists ? \App\Utils\UrlUtils::encodeId($event->id) : '');
            deleteBtn.dataset.token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            deleteBtn.style.cssText = 'width: 20px; height: 20px; min-width: 20px; min-height: 20px;';
            deleteBtn.className = 'absolute -top-2 -right-2 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center';
            deleteBtn.innerHTML = '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';
            deleteBtn.addEventListener('click', function() {
                deleteFlyer(this.dataset.url, this.dataset.hash, this.dataset.token, this.parentElement);
            });
            container.appendChild(deleteBtn);

            var flyerSection = document.getElementById('flyer_image_choose');
            if (flyerSection) {
                flyerSection.parentNode.insertBefore(container, flyerSection.nextSibling);
            }
        }
        var chooseDiv = document.getElementById('flyer_image_choose');
        if (chooseDiv) chooseDiv.style.display = 'none';

        if (data.flyer_image_filename) {
            var existingInput = document.getElementById('ai_flyer_image');
            if (existingInput) existingInput.remove();
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ai_flyer_image';
            input.id = 'ai_flyer_image';
            input.value = data.flyer_image_filename;
            document.getElementById('edit-form').appendChild(input);
        }
    }
};
</script>
@endif

@if(config('app.hosted'))
<x-upgrade-modal name="upgrade-ai-details" tier="enterprise" :subdomain="$subdomain" :learnMoreUrl="marketing_url('/features/ai')">
    {{ __('messages.upgrade_feature_description_ai_details') }}
</x-upgrade-modal>
@endif

@if ($event->exists && $role->isPro() && !$event->is_private)
    @include('components.embed-ticket-modal')
@endif

</x-app-admin-layout>