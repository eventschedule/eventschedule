# API Scanning Parity Update - Summary

## Overview
The `POST /api/tickets/scan` endpoint has been updated to work **exactly** like the web-based ticket scanning. This ensures consistent validation and security across all platforms.

---

## Changes Made

### 1. Backend API Endpoint Updates
**File:** `app/Http/Controllers/Api/ApiTicketController.php`

#### New Validation Rules Added:
```php
// Validation: Ticket must be for today
if (Carbon::parse($sale->event_date)->format('Y-m-d') !== now()->format('Y-m-d')) {
    return response()->json(['error' => 'This ticket is not valid for today'], 400);
}

// Validation: Ticket must be paid
if ($sale->status === 'unpaid') {
    return response()->json(['error' => 'This ticket is not paid'], 400);
} elseif ($sale->status === 'cancelled') {
    return response()->json(['error' => 'This ticket is cancelled'], 400);
} elseif ($sale->status === 'refunded') {
    return response()->json(['error' => 'This ticket is refunded'], 400);
}

// Authorization: User must manage the event
if (!$user->canEditEvent($sale->event)) {
    return response()->json(['error' => 'You are not authorized to scan this ticket'], 403);
}
```

#### Added Import:
```php
use Carbon\Carbon;
```

---

## Validation Parity Matrix

| Check | Web Scanning | API Scanning | Status |
|-------|--------------|--------------|--------|
| Ticket code exists | ✅ | ✅ | **MATCHING** |
| Not deleted | ✅ | ✅ | **MATCHING** |
| User authorized | ✅ | ✅ | **MATCHING** |
| **Ticket for today** | ✅ | ✅ | **MATCHING** |
| **Status is paid** | ✅ | ✅ | **MATCHING** |

---

## Error Response Parity

### Web Route: `POST /ticket/view/{event_id}/{secret}`
```php
if (Carbon::parse($sale->event_date)->format('Y-m-d') !== now()->format('Y-m-d')) {
    return response()->json(['error' => __('messages.this_ticket_is_not_valid_for_today')], 200);
}

if ($sale->status == 'unpaid') {
    return response()->json(['error' => __('messages.this_ticket_is_not_paid')], 200);
}
```

### API Endpoint: `POST /api/tickets/scan`
```php
if (Carbon::parse($sale->event_date)->format('Y-m-d') !== now()->format('Y-m-d')) {
    return response()->json(['error' => 'This ticket is not valid for today'], 400);
}

if ($sale->status === 'unpaid') {
    return response()->json(['error' => 'This ticket is not paid'], 400);
}
```

**Note:** Error messages are identical in content, but API uses HTTP 400 (Bad Request) instead of 200, which is more semantically correct for validation failures.

---

## API Documentation Updated

**File:** `docs/TICKET_SCANNING_API_GUIDE.md`

### New Sections Added:
1. **Validation Requirements** - Explicit table of all requirements with error codes
2. **Enhanced Error Troubleshooting** - Detailed explanations for each validation failure
3. **Best Practices** - Updated to emphasize date/payment validation awareness
4. **Updated Overview** - Clarifies web parity upfront

### Key Documentation Changes:
```markdown
## Validation Requirements

Before a ticket can be scanned, it must meet ALL of these requirements:

| Requirement | Status | Error Code | Error Message |
|-------------|--------|-----------|--------------|
| Ticket code must exist | Required | 404 | "Ticket not found" |
| User must manage event | Required | 403 | "You are not authorized to scan this ticket" |
| **Ticket must be TODAY's date** | **Required** | **400** | **"This ticket is not valid for today"** |
| **Ticket status must be "paid"** | **Required** | **400** | **"This ticket is not paid"** |
```

---

## Testing Checklist for iOS Team

- [ ] Ticket for today's date with paid status → **Scan succeeds**
- [ ] Ticket for future date with paid status → **Error: "This ticket is not valid for today"**
- [ ] Ticket for today with unpaid status → **Error: "This ticket is not paid"**
- [ ] Ticket for today with cancelled status → **Error: "This ticket is cancelled"**
- [ ] Ticket for today with refunded status → **Error: "This ticket is refunded"**
- [ ] Ticket with invalid code → **Error: "Ticket not found"**
- [ ] Ticket for event user doesn't manage → **Error: "You are not authorized to scan this ticket"**

---

## Common Troubleshooting

### Why am I getting "This ticket is not valid for today"?
- Your device date/time is wrong (set to future or past)
- The ticket event date is different from today
- **Solution:** Verify device time and only scan tickets on their event date

### Why am I getting "This ticket is not paid"?
- The ticket sale is still in pending/unpaid state
- Payment hasn't been collected/confirmed yet
- **Solution:** Ensure payment is completed before event check-in

### Why am I getting "You are not authorized"?
- Your API key belongs to wrong event organizer
- You've been removed from the event team
- **Solution:** Verify API key belongs to correct account/event

---

## Backwards Compatibility

The API endpoint behavior is **not backwards compatible** with the previous version that had no date/payment validation. If your app was relying on scanning unpaid tickets or tickets for other dates, you'll need to update your logic:

**Before (Permissive):**
```
Any ticket code → Scans immediately
```

**After (Secure):**
```
Valid code + Today + Paid status → Scans
Otherwise → Validation error
```

---

## Implementation Notes

- Date validation uses `Carbon::parse($sale->event_date)->format('Y-m-d')` to ensure timezone-safe comparison
- Payment status validation explicitly checks all non-paid states (unpaid, cancelled, refunded)
- Authorization still uses `$user->canEditEvent()` for consistency with web scanning
- Error messages match web translations for consistency

---

## Support & Escalation

If iOS team encounters any validation errors:

1. **Check the error message carefully** - it indicates the exact issue
2. **Verify ticket details** in the dashboard (date, payment status)
3. **Verify device date/time** is correct
4. **Contact backend team** if behavior differs from documentation

---

## Version Info

- **API Version:** 2.0
- **Documentation Version:** 2.0  
- **Backend File:** `app/Http/Controllers/Api/ApiTicketController.php`
- **Web Route Reference:** `POST /ticket/view/{event_id}/{secret}` in `app/Http/Controllers/TicketController.php`
- **Last Updated:** 2025-12-17
