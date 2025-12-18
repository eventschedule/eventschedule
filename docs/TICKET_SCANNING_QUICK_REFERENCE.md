# Quick Reference: Ticket Scanning API for iOS Team

## Endpoint
```
POST https://your-domain.com/api/tickets/scan
```

## Authentication
```
Header: X-API-Key: your-api-key-here
```

## Request
```json
{
  "ticket_code": "extract-from-qr-code-string",
  "seat_number": "optional-A12-format"
}
```

## Success Response (201 Created)
```json
{
  "data": {
    "sale_id": 123,
    "entry_id": 1001,
    "scanned_at": "2025-12-17T14:30:00Z",
    "sale": {
      "id": 123,
      "status": "paid",
      "name": "John Doe",
      "email": "john@example.com",
      "event": {
        "id": 456,
        "name": "Event Name",
        "starts_at": "2025-12-20T19:00:00Z"
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

## Validation Requirements (MUST ALL PASS)

✅ **Ticket code must exist** → (404 if not)
✅ **User must manage event** → (403 if not)
✅ **Ticket must be TODAY** → (400 if not)
✅ **Ticket must be PAID** → (400 if not)

## Common Error Responses

| Status | Error | Cause |
|--------|-------|-------|
| 400 | "This ticket is not valid for today" | Event date ≠ today |
| 400 | "This ticket is not paid" | Status is unpaid |
| 400 | "This ticket is cancelled" | Status is cancelled |
| 400 | "This ticket is refunded" | Status is refunded |
| 403 | "You are not authorized to scan this ticket" | User doesn't manage event |
| 404 | "Ticket not found" | Invalid code or deleted |

## Swift Implementation

```swift
import Foundation

class TicketScanner {
    let baseURL = "https://your-domain.com/api"
    let apiKey = "your-api-key"
    
    func scan(_ qrCode: String, completion: @escaping (Result<ScanResponse, Error>) -> Void) {
        var request = URLRequest(url: URL(string: "\(baseURL)/tickets/scan")!)
        request.httpMethod = "POST"
        request.addValue(apiKey, forHTTPHeaderField: "X-API-Key")
        request.addValue("application/json", forHTTPHeaderField: "Content-Type")
        
        let body: [String: Any] = ["ticket_code": qrCode.trimmingCharacters(in: .whitespacesAndNewlines)]
        request.httpBody = try? JSONSerialization.data(withJSONObject: body)
        
        URLSession.shared.dataTask(with: request) { data, response, error in
            guard let data = data, error == nil else {
                completion(.failure(error ?? NSError(domain: "", code: -1)))
                return
            }
            
            do {
                let response = try JSONDecoder().decode(ScanResponse.self, from: data)
                completion(.success(response))
            } catch {
                completion(.failure(error))
            }
        }.resume()
    }
}

struct ScanResponse: Codable {
    struct Data: Codable {
        let sale_id: Int
        let entry_id: Int
        let scanned_at: String
        let sale: SaleData
    }
    
    struct SaleData: Codable {
        let name: String
        let status: String
        let event: EventData
        let tickets: [TicketData]
    }
    
    struct EventData: Codable {
        let name: String
        let starts_at: String
    }
    
    struct TicketData: Codable {
        let quantity: Int
        let usage_status: String
    }
    
    let data: Data
}
```

## Error Handling Pattern

```swift
scanner.scan(qrCode) { result in
    switch result {
    case .success(let response):
        // Show success: "John Doe - 2 tickets marked used"
        print("Buyer: \(response.data.sale.name)")
        print("Tickets: \(response.data.sale.tickets.count)")
        
    case .failure(let error):
        if let decodingError = error as? DecodingError {
            // Parse JSON error response to get message
            if let data = try? JSONDecoder().decode(ErrorResponse.self, from: originalData) {
                print("Scan failed: \(data.error)")
            }
        } else {
            print("Network error: \(error.localizedDescription)")
        }
    }
}

struct ErrorResponse: Codable {
    let error: String
}
```

## Important Reminders

1. **Always trim QR code** before sending:
   ```swift
   let code = qrCode.trimmingCharacters(in: .whitespacesAndNewlines)
   ```

2. **Check device date/time** - Tickets can only scan on their event date

3. **Check API key** in every request header - it's required

4. **Don't retry 400/401/403** errors - they're validation failures, not temporary issues

5. **Implement exponential backoff** for 429/5xx errors

## Testing

Test with these scenarios:
- ✅ Valid ticket for today → Should scan
- ❌ Ticket for tomorrow → "This ticket is not valid for today"
- ❌ Unpaid ticket → "This ticket is not paid"
- ❌ Invalid code → "Ticket not found"

## Documentation

- Full guide: `docs/TICKET_SCANNING_API_GUIDE.md`
- Parity summary: `docs/API_SCANNING_PARITY_SUMMARY.md`

## Contact

Issues with scanning? Provide:
1. The exact error message returned by the API
2. The ticket code (if safe to share)
3. Timestamp of attempt
4. Event details (date, payment status visible in dashboard)
