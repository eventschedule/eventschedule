# Ticket Scanning API - Complete Integration Guide for iOS Team

## Overview
The ticket scanning API allows iOS apps to scan QR codes and mark tickets as used. The API works exactly like the web-based ticket scanning:
- Ticket MUST be for today's date
- Ticket MUST be paid (not unpaid, cancelled, or refunded)
- User must be authorized to manage the event

When these requirements are met, the ticket is marked as used and the full sale details are returned.

---

## Authentication

**Required Header:** `X-API-Key`

All API requests must include an `X-API-Key` header with a valid API key.

```
X-API-Key: your-api-key-here
```

**How to get API Key:**
- API keys are generated per user in the system
- Contact the backend team or admin to generate/retrieve your API key
- Store it securely in your app (e.g., Keychain on iOS)

**Rate Limiting:**
- 60 requests per minute per IP address
- 10 failed authentication attempts trigger 15-minute block
- Add 250ms delay between failed attempts

---

## Primary Endpoint: Scan by Ticket Code

### Endpoint: `POST /api/tickets/scan`

This is the main endpoint for iOS scanning. It accepts the ticket code from a QR code and performs the scan.

### URL
```
POST https://your-domain.com/api/tickets/scan
```

### Validation Requirements

Before a ticket can be scanned, it must meet ALL of these requirements:

| Requirement | Status | Error Code | Error Message |
|-------------|--------|-----------|--------------|
| Ticket code must exist | Required | 404 | "Ticket not found" |
| Ticket must not be deleted | Required | 404 | "Ticket not found" |
| User must manage event | Required | 403 | "You are not authorized to scan this ticket" |
| **Ticket must be TODAY's date** | **Required** | **400** | **"This ticket is not valid for today"** |
| **Ticket status must be "paid"** | **Required** | **400** | **"This ticket is not paid"** / "This ticket is cancelled" / "This ticket is refunded" |

**Important:** The date and payment status checks are critical for event security. Tickets can only be scanned on the day of the event and only if payment has been completed.

### Headers
```
X-API-Key: your-api-key-here
Content-Type: application/json
```

### Request Body

**Required:**
```json
{
  "ticket_code": "wk8wfyzjrbrdv5rxvjxjzpx9ggum6uxl"
}
```

**Optional:**
```json
{
  "ticket_code": "wk8wfyzjrbrdv5rxvjxjzpx9ggum6uxl",
  "sale_ticket_id": 789,
  "seat_number": "A12"
}
```

**Parameters:**
- `ticket_code` (required, string): The code extracted from the QR code. This is the unique identifier for the ticket sale.
- `sale_ticket_id` (optional, integer): Specific ticket to scan within the sale. If omitted, the first ticket is scanned.
- `seat_number` (optional, string, max 255): Seat or location information for the ticket entry.

### Success Response (201 Created)

```json
{
  "data": {
    "sale_id": 123,
    "entry_id": 1001,
    "scanned_at": "2025-12-17T14:30:00.000000Z",
    "sale": {
      "id": 123,
      "status": "paid",
      "name": "John Doe",
      "email": "john@example.com",
      "event_id": 456,
      "event": {
        "id": 456,
        "name": "Concert 2025",
        "starts_at": "2025-12-20T19:00:00Z",
        "ends_at": "2025-12-20T22:00:00Z"
      },
      "tickets": [
        {
          "id": 789,
          "ticket_id": 101,
          "quantity": 2,
          "usage_status": "used"
        }
      ]
    }
  }
}
```

**Response Fields:**
- `sale_id`: The ID of the ticket sale
- `entry_id`: The ID of the scanned entry (useful for tracking individual scans)
- `scanned_at`: ISO 8601 timestamp of when the scan occurred
- `sale.status`: Payment status - can be `paid`, `unpaid`, `cancelled`, `refunded`, `expired`, `deleted`
- `sale.tickets`: Array of tickets in this sale with `usage_status` of `used` or `unused`

---

## Error Responses

### 400 Bad Request
Invalid request format, missing required fields, or validation failure.

**Missing field:**
```json
{
  "error": "The ticket code field is required."
}
```

**Ticket not for today:**
```json
{
  "error": "This ticket is not valid for today"
}
```

**Ticket not paid:**
```json
{
  "error": "This ticket is not paid"
}
```

**Ticket cancelled:**
```json
{
  "error": "This ticket is cancelled"
}
```

**Ticket refunded:**
```json
{
  "error": "This ticket is refunded"
}
```

### 401 Unauthorized
Missing or invalid API key.
```json
{
  "error": "API key is required"
}
```
or
```json
{
  "error": "Invalid API key"
}
```

### 403 Forbidden
The authenticated user does not manage the event for this ticket.
```json
{
  "error": "Unauthorized"
}
```

### 404 Not Found
The ticket code was not found or has been deleted.
```json
{
  "error": "Ticket not found"
}
```

Special cases:
```json
{
  "error": "Sale ticket not found"
}
```
```json
{
  "error": "No tickets found in this sale"
}
```

### 423 Locked
API key has been temporarily blocked due to too many failed attempts.
```json
{
  "error": "API key temporarily blocked"
}
```

### 429 Too Many Requests
Rate limit exceeded (60 requests per minute).
```json
{
  "error": "Rate limit exceeded"
}
```

---

## Common Issues & Troubleshooting

### Issue: "Ticket not found" (404)

**Causes:**
1. **Ticket code doesn't exist**: The code extracted from the QR was wrong
2. **Ticket was deleted**: The sale has been soft-deleted
3. **Wrong environment**: QR code is from production but you're hitting staging API (or vice versa)
4. **Code formatting**: The QR code value includes extra whitespace or special characters

**Solutions:**
- Verify the extracted code matches exactly (case-sensitive)
- Check that you're using the correct API base URL
- Trim whitespace: `code = code.trimmingCharacters(in: .whitespacesAndNewlines)`
- Test with a known valid ticket code in your test environment

### Issue: "This ticket is not valid for today" (400)

**Cause:**
The ticket is for a different date than today.

**Why:**
For security and audit purposes, tickets can only be scanned on the day of the event. This prevents accidental scanning of tickets for other dates.

**Solutions:**
- Verify the device date/time is correct
- Only scan tickets on their event date
- Check the sale's `event_date` field to see when the event is scheduled

### Issue: "This ticket is not paid" (400)

**Cause:**
The ticket sale is in unpaid status.

**Why:**
Only paid tickets should be scanned at events. Unpaid tickets haven't completed payment and shouldn't grant entry.

**Solutions:**
- Collect payment before scanning
- Use the dashboard to mark the sale as paid if payment was received offline
- Check the sale status in the admin panel

### Issue: "This ticket is cancelled" or "This ticket is refunded" (400)

**Cause:**
The ticket has been cancelled or refunded.

**Why:**
Cancelled or refunded tickets are no longer valid and should not be scanned.

**Solutions:**
- Verify the correct ticket code was scanned
- Check with the attendee about the ticket status
- Review the ticket in the admin dashboard

### Issue: "You are not authorized to scan this ticket" (403)

**Cause:**
The authenticated user (via API key) doesn't have permission to scan tickets for this event.

**Why:**
Only event organizers and team members with scanning permissions can check in tickets.

**Solutions:**
- Verify the API key belongs to the correct event organizer/team member
- Ensure the user hasn't been removed from the event
- Check user permissions in the admin panel

### Issue: "Invalid API key" (401)

**Causes:**
1. API key is missing from the request
2. API key is incorrect
3. API key doesn't exist in the system

**Solutions:**
- Ensure `X-API-Key` header is included in every request
- Double-check the API key value (copy-paste carefully, watch for leading/trailing spaces)
- Request a new API key from the system admin if the current one is lost

### Issue: "Rate limit exceeded" (429)

**Causes:**
1. More than 60 requests per minute from your IP address

**Solutions:**
- Implement exponential backoff retry logic
- Cache results when possible to reduce API calls
- Spread requests over time instead of rapid-fire

---

## Example Implementation (Swift/iOS)

```swift
import Foundation

class TicketScanService {
    let baseURL = "https://your-domain.com/api"
    let apiKey = "your-api-key-here"
    
    func scanTicket(code: String, saleTicketId: Int? = nil, seatNumber: String? = nil, completion: @escaping (Result<TicketScanResponse, Error>) -> Void) {
        var urlComponent = URLComponents(string: "\(baseURL)/tickets/scan")!
        let url = urlComponent.url!
        
        var request = URLRequest(url: url)
        request.httpMethod = "POST"
        request.addValue(apiKey, forHTTPHeaderField: "X-API-Key")
        request.addValue("application/json", forHTTPHeaderField: "Content-Type")
        
        var body: [String: Any] = ["ticket_code": code]
        if let saleTicketId = saleTicketId {
            body["sale_ticket_id"] = saleTicketId
        }
        if let seatNumber = seatNumber {
            body["seat_number"] = seatNumber
        }
        
        request.httpBody = try? JSONSerialization.data(withJSONObject: body)
        
        URLSession.shared.dataTask(with: request) { data, response, error in
            if let error = error {
                completion(.failure(error))
                return
            }
            
            guard let data = data else {
                completion(.failure(NSError(domain: "TicketScan", code: -1, userInfo: nil)))
                return
            }
            
            do {
                let response = try JSONDecoder().decode(TicketScanResponse.self, from: data)
                completion(.success(response))
            } catch {
                completion(.failure(error))
            }
        }.resume()
    }
}

struct TicketScanResponse: Codable {
    struct Data: Codable {
        let sale_id: Int
        let entry_id: Int
        let scanned_at: String
        let sale: SaleData
    }
    
    struct SaleData: Codable {
        let id: Int
        let status: String
        let name: String
        let email: String
        let event_id: Int
        let event: EventData
        let tickets: [TicketData]
    }
    
    struct EventData: Codable {
        let id: Int
        let name: String
        let starts_at: String
        let ends_at: String?
    }
    
    struct TicketData: Codable {
        let id: Int
        let ticket_id: Int
        let quantity: Int
        let usage_status: String  // "used" or "unused"
    }
    
    let data: Data
}
```

**Usage:**
```swift
let service = TicketScanService()
service.scanTicket(code: "wk8wfyzjrbrdv5rxvjxjzpx9ggum6uxl") { result in
    switch result {
    case .success(let response):
        print("Ticket scanned successfully!")
        print("Buyer: \(response.data.sale.name)")
        print("Tickets: \(response.data.sale.tickets)")
    case .failure(let error):
        print("Scan failed: \(error)")
    }
}
```

---

## QR Code Format

The QR codes embedded in tickets contain the ticket secret code in plain text.

**Example QR code value:**
```
wk8wfyzjrbrdv5rxvjxjzpx9ggum6uxl
```

**When scanning:**
1. Extract the raw string from the QR code
2. Trim any whitespace: `code.trimmingCharacters(in: .whitespacesAndNewlines)`
3. Pass directly to `ticket_code` parameter in the API

---

## Alternative Endpoint: Scan by Sale ID

If you have the sale ID available, you can use this endpoint:

### Endpoint: `POST /api/tickets/{sale_id}/scan`

**URL:**
```
POST https://your-domain.com/api/tickets/123/scan
```

**Headers:**
```
X-API-Key: your-api-key-here
Content-Type: application/json
```

**Request Body:**
```json
{
  "sale_ticket_id": 789,
  "seat_number": "A12"
}
```

**Response (201 Created):**
```json
{
  "data": {
    "entry_id": 1001,
    "scanned_at": "2025-12-17T14:30:00.000000Z"
  }
}
```

**Note:** This endpoint requires the sale ID in the URL, which you may not have directly from a QR code. Use the primary endpoint (`POST /api/tickets/scan`) instead.

---

## Best Practices

1. **Understand ticket validation requirements**
   - Ticket must be for TODAY's date (your device date/time must be correct)
   - Ticket must be PAID status
   - Your API key must belong to the event organizer/team member
   - A single ticket can only be scanned once per day

2. **Always validate ticket_code before sending**
   - Trim whitespace: `code.trimmingCharacters(in: .whitespacesAndNewlines)`
   - Check for minimum length (usually 32+ characters)
   - Handle QR decode errors gracefully

3. **Implement retry logic with exponential backoff**
   - Network errors can happen; retry with delays
   - Don't retry on 400/401/403 (validation/auth errors)
   - Do retry on 429, 5xx, and network errors

4. **Cache user/event data locally**
   - Reduce API calls by storing event info locally
   - Sync when you have good connectivity
   - Allow offline mode when reasonable

5. **Log scanning activity**
   - Track successful scans with timestamps
   - Log errors for debugging
   - Report to backend for audit trail

6. **Provide user feedback**
   - Show success confirmation immediately
   - Display buyer name and ticket count
   - Show error details (unpaid, wrong date, already scanned, etc.)
   - Provide helpful error messages for non-technical staff

7. **Handle error responses properly**
   ```swift
   if let httpResponse = response as? HTTPURLResponse {
       switch httpResponse.statusCode {
       case 201:
           // Success - process response
           break
       case 401, 403:
           // Auth error - check API key
           break
       case 404:
           // Ticket not found - maybe it's invalid or deleted
           break
       case 429:
           // Rate limited - back off and retry
           break
       default:
           // Other error
           break
       }
   }
   ```

---

## Support & Questions

If you encounter any issues:

1. **Check the error message carefully** - it usually indicates the problem
2. **Verify API key and authentication**
3. **Confirm ticket codes are being extracted correctly**
4. **Test with a known good ticket code**
5. **Contact the backend team** with:
   - The exact error message and HTTP status code
   - The ticket code you're trying to scan
   - Timestamp of the attempt
   - Your API key (if safe to share in secure channel)

---

## API Response Field Reference

| Field | Type | Example | Description |
|-------|------|---------|-------------|
| sale_id | integer | 123 | Unique ID of the ticket sale |
| entry_id | integer | 1001 | Unique ID of the scanned entry (for audit) |
| scanned_at | string (ISO 8601) | 2025-12-17T14:30:00Z | Timestamp when ticket was scanned |
| status | string | "paid" | Payment status: paid, unpaid, cancelled, refunded, expired, deleted |
| name | string | "John Doe" | Buyer name |
| email | string | "john@example.com" | Buyer email |
| event_id | integer | 456 | Event ID |
| event.id | integer | 456 | Event ID (repeated) |
| event.name | string | "Concert 2025" | Event name |
| event.starts_at | string (ISO 8601) | 2025-12-20T19:00:00Z | Event start time |
| event.ends_at | string (ISO 8601) | 2025-12-20T22:00:00Z | Event end time (nullable) |
| tickets[].id | integer | 789 | Sale ticket ID |
| tickets[].ticket_id | integer | 101 | Ticket type ID |
| tickets[].quantity | integer | 2 | Number of tickets in this sale |
| tickets[].usage_status | string | "used" | "used" or "unused" |

---

## Version History

- **v2.0** (2025-12-17): Updated to match web-based scanning with date validation and payment status checks
- **v1.0** (2025-12-17): Initial release with ticket scanning by code and ID endpoints
