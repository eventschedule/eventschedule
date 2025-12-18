# iOS Team - Ticket Scanning API Complete Setup

## Status: ✅ Complete
The API now works **exactly** like the web-based ticket scanning with full validation parity.

---

## What Changed

The `POST /api/tickets/scan` endpoint now requires:
1. ✅ Ticket code exists
2. ✅ User manages the event  
3. ✅ **Ticket is for TODAY** (new requirement)
4. ✅ **Ticket status is PAID** (new requirement)

Previously, requirements #3 and #4 were missing, causing scan failures because the API was accepting unpaid/future-dated tickets that the web app would have rejected.

---

## Files Updated

### Backend
- **`app/Http/Controllers/Api/ApiTicketController.php`**
  - Added `Carbon` import for date comparison
  - Updated `scanByCode()` method with validation checks
  - Now matches web scanning behavior exactly

### Documentation (New Files)
- **`docs/TICKET_SCANNING_API_GUIDE.md`** (v2.0)
  - Complete guide with all error scenarios
  - Best practices and troubleshooting
  - Swift example code

- **`docs/API_SCANNING_PARITY_SUMMARY.md`** (New)
  - Technical summary of changes
  - Validation parity matrix
  - Testing checklist

- **`docs/TICKET_SCANNING_QUICK_REFERENCE.md`** (New)
  - One-page quick reference
  - Error codes and causes
  - Copy-paste Swift code

---

## For iOS Team: What to Do Now

### 1. Test the New Validation

Test these scenarios:
```
✅ Today's date + Paid status → Scan succeeds
❌ Tomorrow's date + Paid status → Error: "This ticket is not valid for today"
❌ Today's date + Unpaid status → Error: "This ticket is not paid"
❌ Invalid code → Error: "Ticket not found"
```

### 2. Ensure Device Date is Correct
The API checks if ticket's event_date matches today:
```
if (Carbon::parse($sale->event_date)->format('Y-m-d') !== now()->format('Y-m-d')) {
    return error: 'This ticket is not valid for today'
}
```

**Critical:** Test device's date/time is synced correctly.

### 3. Update Error Handling

You'll now receive:
- **400** for validation failures (date, payment status)
- **403** for authorization failures
- **404** for not found
- **401** for auth issues

Example error response:
```json
{
  "error": "This ticket is not valid for today"
}
```

### 4. Use the Updated Documentation

Share with your team:
1. **Quick Ref:** `docs/TICKET_SCANNING_QUICK_REFERENCE.md` (1 page)
2. **Full Guide:** `docs/TICKET_SCANNING_API_GUIDE.md` (complete reference)
3. **Summary:** `docs/API_SCANNING_PARITY_SUMMARY.md` (technical details)

---

## API Endpoint Reference

**Endpoint:** `POST /api/tickets/scan`

**Request:**
```json
{
  "ticket_code": "wk8wfyzjrbrdv5rxvjxjzpx9ggum6uxl"
}
```

**Success (201):**
```json
{
  "data": {
    "sale_id": 123,
    "entry_id": 1001,
    "scanned_at": "2025-12-17T14:30:00Z",
    "sale": {
      "name": "John Doe",
      "status": "paid",
      "tickets": [{"quantity": 2, "usage_status": "used"}]
    }
  }
}
```

**Errors:**
- 400: Validation failure (date/payment)
- 401: Invalid API key
- 403: Not authorized for event
- 404: Ticket not found

---

## Validation Flow Diagram

```
QR Code Scanned
     ↓
Extract Code → Trim Whitespace
     ↓
POST /api/tickets/scan + Code + API-Key
     ↓
[API Validation]
├─ Code exists? → NO → 404 "Ticket not found"
├─ User manages event? → NO → 403 "Not authorized"
├─ Event is today? → NO → 400 "Not valid for today"
└─ Status is paid? → NO → 400 "Not paid" / "Cancelled" / "Refunded"
     ↓
SUCCESS (201)
├─ Create scan entry
├─ Return sale details
└─ Update UI: "Scanned: John Doe - 2 tickets"
```

---

## Common Issues & Fixes

| Problem | Cause | Fix |
|---------|-------|-----|
| "This ticket is not valid for today" | Device date is wrong | Check device date/time settings |
| "This ticket is not paid" | Ticket unpaid | Collect payment first |
| "You are not authorized" | Wrong API key | Verify API key for correct account |
| "Ticket not found" | Bad QR code extract | Verify code trimming |

---

## Testing Checklist

Before going to production:

- [ ] Device date/time is correct
- [ ] API key is set correctly in app
- [ ] Trim whitespace from QR code: `code.trimmingCharacters(in: .whitespacesAndNewlines)`
- [ ] Test with paid ticket for today → succeeds
- [ ] Test with unpaid ticket for today → gets 400 error
- [ ] Test with paid ticket for tomorrow → gets 400 error
- [ ] Error messages display clearly to users
- [ ] Retry logic handles rate limits (429)
- [ ] No retry on 400/401/403 (validation errors)

---

## Deployment Notes

- Backend changes are in `ApiTicketController`
- Route is already defined: `Route::post('/tickets/scan', [ApiTicketController::class, 'scanByCode'])`
- No database migrations needed
- No configuration changes needed
- Compatible with existing API authentication

---

## Next Steps

1. **Pull the latest code** from the repository
2. **Run tests** with the validation checklist above
3. **Review the documentation** files provided
4. **Contact backend team** with any questions

---

## Support

If you encounter any issues:

1. Check the Quick Reference guide first
2. Verify ticket details in dashboard (date, payment)
3. Test with a known-good ticket code
4. Check device date/time is correct
5. Contact backend team with exact error message

---

**API Version:** 2.0  
**Last Updated:** 2025-12-17  
**Status:** ✅ Ready for iOS Integration
