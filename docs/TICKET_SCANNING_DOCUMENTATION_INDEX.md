# Ticket Scanning API - Complete Documentation Index

## For iOS Team (Start Here)

### 📱 Quick Start
- **[iOS Team Setup Guide](iOS_TEAM_SETUP.md)** ← Start here for complete setup
- **[Quick Reference](TICKET_SCANNING_QUICK_REFERENCE.md)** ← One-page cheat sheet
- **[Full API Guide](TICKET_SCANNING_API_GUIDE.md)** ← Complete reference

### 🔧 Technical Details
- **[API Parity Summary](API_SCANNING_PARITY_SUMMARY.md)** ← How web and API match

---

## Quick Answers

### "Why is scanning failing?"

Check these in order:
1. **Device date correct?** (ticket can only scan today)
2. **API key set?** (header: `X-API-Key: YOUR-KEY`)
3. **Ticket paid?** (unpaid/cancelled/refunded won't scan)
4. **User authorized?** (user must manage the event)
5. **Code trimmed?** (remove whitespace from QR)

### "What validations happen?"

```
Code exists? ✅
User authorized? ✅
Today's date? ✅ (NEW)
Status paid? ✅ (NEW)
```

If ANY fail → Returns error with 400/403/404

### "What errors can I get?"

| Error | Status | Cause |
|-------|--------|-------|
| "This ticket is not valid for today" | 400 | Event date ≠ today |
| "This ticket is not paid" | 400 | Status is unpaid |
| "This ticket is cancelled" | 400 | Status is cancelled |
| "This ticket is refunded" | 400 | Status is refunded |
| "You are not authorized to scan this ticket" | 403 | User doesn't manage event |
| "Ticket not found" | 404 | Code doesn't exist |
| "Invalid API key" | 401 | Bad API key header |

---

## Documentation Map

```
📦 docs/
├─ 📄 iOS_TEAM_SETUP.md
│  └─ Complete setup instructions
├─ 📄 TICKET_SCANNING_QUICK_REFERENCE.md
│  └─ One-page reference
├─ 📄 TICKET_SCANNING_API_GUIDE.md
│  └─ Full technical guide v2.0
├─ 📄 API_SCANNING_PARITY_SUMMARY.md
│  └─ How web and API match
└─ 📄 TICKET_SCANNING_DOCUMENTATION_INDEX.md (this file)
```

---

## Code Changes

### Backend Files Changed
- **`app/Http/Controllers/Api/ApiTicketController.php`**
  - Added Carbon import
  - Updated `scanByCode()` method
  - New validations: date check, payment status check

### Route (Already Exists)
- **`routes/api.php`**
  - `POST /api/tickets/scan` → `ApiTicketController@scanByCode`

---

## Validation Requirements (v2.0)

All of these must pass for scan to succeed:

| Requirement | Check | Error |
|-------------|-------|-------|
| API key provided | Header: `X-API-Key` | 401 |
| API key valid | Against user database | 401 |
| Code provided | Parameter: `ticket_code` | 400 |
| Code exists | In sales table | 404 |
| Not deleted | `is_deleted = false` | 404 |
| User authorized | `user->canEditEvent()` | 403 |
| **Event is today** | `event_date = now()->date()` | **400** |
| **Status is paid** | `status = 'paid'` | **400** |

**Bold = New validation (v2.0)**

---

## Success Response (201)

```json
{
  "data": {
    "sale_id": 123,
    "entry_id": 1001,
    "scanned_at": "2025-12-17T14:30:00Z",
    "sale": {
      "id": 123,
      "name": "John Doe",
      "email": "john@example.com",
      "status": "paid",
      "event": {
        "id": 456,
        "name": "Concert 2025"
      },
      "tickets": [
        {
          "id": 789,
          "quantity": 2,
          "usage_status": "used"
        }
      ]
    }
  }
}
```

---

## Testing Scenarios

✅ = Should succeed (201)
❌ = Should fail with error

| Scenario | Code | Date | Status | Auth | Result | Error |
|----------|------|------|--------|------|--------|-------|
| Valid ticket | ✅ | Today | Paid | ✅ | ✅ 201 | - |
| Wrong date | ✅ | Tomorrow | Paid | ✅ | ❌ 400 | "not valid for today" |
| Wrong status | ✅ | Today | Unpaid | ✅ | ❌ 400 | "not paid" |
| Bad code | ❌ | Today | Paid | ✅ | ❌ 404 | "not found" |
| No auth | ✅ | Today | Paid | ❌ | ❌ 403 | "not authorized" |

---

## Best Practices for Implementation

### 1. QR Code Extraction
```swift
let code = qrCode.trimmingCharacters(in: .whitespacesAndNewlines)
```

### 2. API Request
```swift
var request = URLRequest(url: URL(string: "https://domain/api/tickets/scan")!)
request.httpMethod = "POST"
request.addValue(apiKey, forHTTPHeaderField: "X-API-Key")
request.addValue("application/json", forHTTPHeaderField: "Content-Type")
request.httpBody = try? JSONSerialization.data(withJSONObject: ["ticket_code": code])
```

### 3. Error Handling
```swift
switch statusCode {
case 201:
    // Success - show buyer name and tickets
    print("Scanned: \(response.data.sale.name)")
case 400:
    // Validation error - show user the message
    print("Error: \(error)")  // "not valid for today", "not paid", etc.
case 401, 403:
    // Auth error - check API key
    print("Auth failed")
case 404:
    // Ticket not found
    print("Invalid ticket code")
case 429:
    // Rate limited - retry with backoff
    retry(after: 1.0)
}
```

### 4. User Feedback
Show different messages for different errors:
- ❌ "This ticket is not valid for today" → Check event date
- ❌ "This ticket is not paid" → Collect payment first
- ❌ "You are not authorized" → Check API key/account
- ✅ "Scanned: John Doe (2 tickets)" → Success

---

## Deployment Checklist

Before iOS team uses in production:

- [ ] Code deployed to backend
- [ ] API key generated for user
- [ ] API base URL configured in app
- [ ] Device date/time correct
- [ ] Test with today's paid ticket → succeeds
- [ ] Test with tomorrow's paid ticket → fails correctly
- [ ] Test with today's unpaid ticket → fails correctly
- [ ] Error messages display clearly
- [ ] Retry logic implemented
- [ ] Logging implemented for debugging

---

## Troubleshooting Guide

### Symptom: "This ticket is not valid for today"
**Causes:**
- Device date is in future or past
- Event date is different from today
- Timezone mismatch

**Fix:**
- Verify device date/time is correct
- Check event date in dashboard
- Only scan on event date

### Symptom: "This ticket is not paid"
**Causes:**
- Ticket sale still pending payment
- Payment not confirmed

**Fix:**
- Collect payment through checkout
- Mark as paid in dashboard if offline payment
- Refresh ticket status

### Symptom: "You are not authorized to scan this ticket"
**Causes:**
- API key belongs to different event organizer
- User removed from event team
- Wrong account

**Fix:**
- Verify API key correct
- Check user is event manager/organizer
- Verify user permissions in dashboard

### Symptom: "Ticket not found"
**Causes:**
- Invalid/corrupted QR code
- Code trimming failed
- Ticket doesn't exist
- Wrong environment (staging vs prod)

**Fix:**
- Verify QR code scanned correctly
- Check trim whitespace: `.trimmingCharacters(in: .whitespacesAndNewlines)`
- Test with known-good code
- Verify API URL (staging vs production)

---

## Version History

### v2.0 (2025-12-17) - Current
- ✅ Added date validation (ticket must be today)
- ✅ Added payment status validation (ticket must be paid)
- ✅ API now matches web scanning exactly
- ✅ Complete documentation suite

### v1.0 (2025-12-17) - Previous
- Initial API endpoint release
- No date/payment validation

---

## Getting Help

1. **Check documentation first**
   - Quick Ref: 1 page, main issues
   - Full Guide: Complete reference
   - Setup: Step-by-step

2. **Verify ticket in dashboard**
   - Check event date
   - Check payment status
   - Verify you manage event

3. **Test with known-good ticket**
   - Must be today's date
   - Must be paid
   - Must have valid code

4. **Contact backend team with:**
   - Exact error message from API
   - Ticket code (if safe)
   - Timestamp of attempt
   - API version being used

---

## Summary

The ticket scanning API now works **exactly** like web scanning with full validation:
- ✅ Ticket must exist
- ✅ User must be authorized
- ✅ **Ticket must be today's date** (NEW)
- ✅ **Ticket must be paid** (NEW)

Documentation is comprehensive and ready for implementation.

---

**Status:** ✅ Complete  
**Last Updated:** 2025-12-17  
**API Version:** 2.0
