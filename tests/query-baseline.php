<?php

/*
 * N+1 queries the suite already knows about (tests/Support/QueryGuard.php).
 *
 * A key is `rule|file that asked|what it asked for`:
 *  - lazy:   an Eloquent relation lazy loaded on two or more models of one result
 *  - repeat: the same SELECT more than QueryGuard::REPEAT_LIMIT times in one request, job or command
 *
 * A finding whose key is not here fails the test that ran it. This list only shrinks: fix an entry
 * and take it off (CI fails a push that changed the file an entry names when no test hits it any
 * more). The value says why. "baseline YYYY-MM-DD" means it was here when the guard arrived and
 * nobody has looked yet; an entry added on purpose says in a sentence why it cannot be one query.
 */

return [
    'lazy|app/Console/Commands/SyncGoogleCalendars.php|App\Models\Role::user|app/Console/Commands/SyncGoogleCalendars.php' => 'baseline 2026-10-22: Tests\Feature\CalendarReReadTest::test_the_command_gives_a_few_schedules_their_full_read_a_run',
    'lazy|app/Models/Event.php|App\Models\Event::creatorRole|-' => 'baseline 2026-10-22: GET admin.private_calendars.show, Tests\Feature\BlockedAccountsHiddenTest::test_a_blocked_accounts_day_leaves_the_activity_feed_and_the_sign_ups_card, Tests\Feature\BlockedAccountsHiddenTest::test_a_blocked_account_and_the_views_of_its_pages_leave_the_visitors_and_the_figures',
    'lazy|app/Models/Event.php|App\Models\Event::creatorRole|app/Http/Controllers/EventController.php' => 'baseline 2026-10-22: POST event.import_undo',
    'lazy|app/Models/Event.php|App\Models\Event::creatorRole|app/Http/Controllers/GraphicController.php' => 'baseline 2026-10-22: GET event.generate_graphic_data',
    'lazy|app/Models/Event.php|App\Models\Event::creatorRole|app/Http/Controllers/RoleController.php' => 'baseline 2026-10-22: GET role.search_events',
    'lazy|app/Models/Event.php|App\Models\Event::parts|app/Http/Controllers/EventController.php' => 'baseline 2026-10-22: POST event.import_undo',
    'lazy|app/Models/Event.php|App\Models\Event::roles|-' => 'baseline 2026-10-22: Tests\Feature\FeedImporterTest::test_a_read_that_would_take_a_large_share_of_the_events_is_held_whole',
    'lazy|app/Models/Event.php|App\Models\Event::roles|app/Http/Controllers/EventController.php' => 'baseline 2026-10-22: POST event.import_undo, GET event.scan_agenda',
    'lazy|app/Models/Event.php|App\Models\Event::tickets|app/Http/Controllers/RoleController.php' => 'baseline 2026-10-22: GET role.view_guest',
    'lazy|app/Models/Sale.php|App\Models\Sale::event|-' => 'baseline 2026-10-22: Tests\Feature\MultiEventOrderLifecycleTest::test_a_cancelled_order_returns_the_promo_code_it_took, Tests\Feature\SaleOrderCascadeTest::test_cancelling_the_order_primary_cascades_to_every_leg_and_guest',
    'lazy|app/Models/Sale.php|App\Models\Sale::event|app/Console/Commands/ReleaseTickets.php' => 'baseline 2026-10-22: Tests\Feature\GiftCardRedemptionTest::test_release_tickets_restores_balance_when_gift_lands_only_on_guest_rows, Tests\Feature\GiftCardRedemptionTest::test_release_tickets_restores_exact_face_value_when_primary_and_guest_both_carry_shares, Tests\Feature\SaleOrderCascadeTest::test_an_order_expires_as_a_unit_when_the_shortest_leg_window_elapses',
    'lazy|app/Models/Sale.php|App\Models\Sale::installmentPlan|-' => 'baseline 2026-10-22: Tests\Feature\MultiEventOrderLifecycleTest::test_a_cancelled_order_returns_the_promo_code_it_took, Tests\Feature\SaleOrderCascadeTest::test_cancelling_the_order_primary_cascades_to_every_leg_and_guest',
    'lazy|app/Models/Sale.php|App\Models\Sale::installmentPlan|app/Console/Commands/ReleaseTickets.php' => 'baseline 2026-10-22: Tests\Feature\GiftCardRedemptionTest::test_release_tickets_restores_balance_when_gift_lands_only_on_guest_rows, Tests\Feature\GiftCardRedemptionTest::test_release_tickets_restores_exact_face_value_when_primary_and_guest_both_carry_shares, Tests\Feature\SaleOrderCascadeTest::test_an_order_expires_as_a_unit_when_the_shortest_leg_window_elapses',
    'lazy|app/Models/Sale.php|App\Models\Sale::saleTickets|-' => 'baseline 2026-10-22: Tests\Feature\MultiEventOrderLifecycleTest::test_a_cancelled_order_returns_the_promo_code_it_took, Tests\Feature\SaleOrderCascadeTest::test_cancelling_the_order_primary_cascades_to_every_leg_and_guest',
    'lazy|app/Models/Sale.php|App\Models\Sale::saleTickets|app/Console/Commands/ReleaseTickets.php' => 'baseline 2026-10-22: Tests\Feature\GiftCardRedemptionTest::test_release_tickets_restores_balance_when_gift_lands_only_on_guest_rows, Tests\Feature\GiftCardRedemptionTest::test_release_tickets_restores_exact_face_value_when_primary_and_guest_both_carry_shares, Tests\Feature\SaleOrderCascadeTest::test_an_order_expires_as_a_unit_when_the_shortest_leg_window_elapses',
    'lazy|app/Models/Sale.php|App\Models\SaleTicket::ticket|app/Http/Controllers/TicketController.php' => 'baseline 2026-10-22: POST event.checkout',
    'lazy|app/Models/SaleInstallmentPlan.php|App\Models\SaleInstallmentPlan::installments|app/Console/Commands/ChargeInstallments.php' => 'baseline 2026-10-22: Tests\Feature\ChargeInstallmentsTest::test_many_buyers_produce_one_organizer_digest',
    'lazy|app/Models/Ticket.php|App\Models\Ticket::event|-' => 'baseline 2026-10-22: Tests\Feature\SeatingPassTest::test_pass_booking_claims_a_real_seat_and_records_it_on_the_usage, Tests\Feature\SeatingPassTest::test_pass_bookings_stop_when_the_house_is_physically_full, Tests\Feature\SeatingPassTest::test_pass_and_regular_buyers_compete_for_the_same_seats',
    'lazy|app/Models/Ticket.php|App\Models\Ticket::event|app/Http/Controllers/Api/ApiEventController.php' => 'baseline 2026-10-22: PUT /api/events/{id}',
    'lazy|app/Models/Ticket.php|App\Models\Ticket::event|app/Http/Controllers/TicketController.php' => 'baseline 2026-10-22: POST event.checkout, GET ticket.view',
    'lazy|app/Services/DemoService.php|App\Models\Event::tickets|-' => 'baseline 2026-10-22: Tests\Feature\DemoResetTest::test_a_real_demo_prefixed_schedule_survives_with_its_sales_and_analytics, Tests\Feature\DemoResetTest::test_an_ownerless_demo_prefixed_placeholder_is_left_alone, Tests\Feature\DemoResetTest::test_a_real_event_curated_into_the_demo_keeps_its_sales',
    'lazy|app/Services/EmailService.php|App\Models\Sale::event|-' => 'baseline 2026-10-22: Tests\Feature\MultiEventCartCheckoutTest::test_a_multi_event_order_sends_a_ticket_email_for_every_leg, Tests\Feature\SaleSettlementServiceTest::test_a_leg_already_paid_by_hand_is_not_credited_twice',
    'lazy|app/Services/EmailService.php|App\Models\Sale::event|app/Http/Controllers/AdminController.php' => 'baseline 2026-10-22: POST admin.sale.approve',
    'lazy|app/Services/EmailService.php|App\Models\Sale::event|app/Http/Controllers/PaymentGatewayController.php' => 'baseline 2026-10-22: GET payments.return',
    'lazy|app/Services/EmailService.php|App\Models\Sale::event|app/Http/Controllers/StripeController.php' => 'baseline 2026-10-22: POST stripe.webhook',
    'lazy|app/Services/EmailService.php|App\Models\Sale::event|app/Http/Controllers/TicketController.php' => 'baseline 2026-10-22: POST event.checkout, POST sales.action',
    'lazy|app/Services/SaleRefundService.php|App\Models\SaleInstallment::plan|-' => 'baseline 2026-10-22: Tests\Feature\SaleRefundTest::test_an_installment_leg_is_scaled_by_the_plans_own_currency, Tests\Feature\SaleRefundTest::test_an_installment_plan_is_refunded_leg_by_leg',
    'lazy|app/Traits/HasFanSubmitter.php|App\Models\EventComment::user|app/Http/Controllers/Api/ApiFeedbackController.php' => 'baseline 2026-10-22: GET /api/fan-content',
    'lazy|app/Traits/HasFanSubmitter.php|App\Models\EventPhoto::user|app/Http/Controllers/Api/ApiFeedbackController.php' => 'baseline 2026-10-22: GET /api/fan-content',
    'lazy|app/Traits/HasFanSubmitter.php|App\Models\EventVideo::user|app/Http/Controllers/Api/ApiFeedbackController.php' => 'baseline 2026-10-22: GET /api/fan-content',
    'lazy|app/Traits/RoleBillable.php|App\Models\Role::subscriptions|-' => 'baseline 2026-10-22: GET admin.feeds, Tests\Feature\FeedWriterTest::test_the_venue_moves_with_the_source_until_the_owner_moves_it, Tests\Feature\FreePlanLimitsTest::test_an_addon_follows_the_creator_schedule_not_any_attached_pro_one',
    'lazy|app/Traits/RoleBillable.php|App\Models\Role::subscriptions|app/Http/Controllers/Api/ApiEventController.php' => 'baseline 2026-10-22: POST /api/events/{id}/cancel, PUT /api/events/{id}, POST /api/events/{subdomain}',
    'lazy|app/Traits/RoleBillable.php|App\Models\Role::subscriptions|app/Http/Controllers/CheckInController.php' => 'baseline 2026-10-22: GET checkin.index',
    'lazy|app/Traits/RoleBillable.php|App\Models\Role::subscriptions|app/Http/Controllers/EventController.php' => 'baseline 2026-10-22: POST event.store, PUT event.update',
    'lazy|app/helpers.php|App\Models\Role::user|app/Http/Controllers/RoleController.php' => 'baseline 2026-10-22: GET event.view_guest_with_id',
    'repeat|app/Models/User.php|roles|-' => 'baseline 2026-10-22: GET event.edit',
    'repeat|app/Services/CalendarArrival.php|calendar_import_holds|app/Http/Controllers/EventFeedController.php' => 'baseline 2026-10-22: POST role.feeds.publish_all',
    'repeat|app/Services/WebhookService.php|roles|app/Http/Controllers/EventFeedController.php' => 'baseline 2026-10-22: POST role.feeds.publish_all',
    'repeat|app/Services/WebhookService.php|webhooks|app/Http/Controllers/EventFeedController.php' => 'baseline 2026-10-22: POST role.feeds.publish_all',
];
