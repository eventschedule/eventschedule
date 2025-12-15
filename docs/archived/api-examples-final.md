# API Examples for iOS (final)

Set `X-API-Key` header on all requests.

## Create sale (checkout)

curl:

```bash
curl -X POST "https://api.example.com/events/my-event-subdomain/checkout" \
  -H "X-API-Key: <API_KEY>" \
  -H "Content-Type: application/json" \
  -d '{"name":"Jane Doe","email":"jane@example.com","event_date":"2026-01-15","tickets":[{"ticket_type":"general","quantity":2}]}'
```

Swift (URLSession):

```swift
let url = URL(string: "https://api.example.com/events/my-event-subdomain/checkout")!
var req = URLRequest(url: url)
req.httpMethod = "POST"
req.setValue("<API_KEY>", forHTTPHeaderField: "X-API-Key")
req.setValue("application/json", forHTTPHeaderField: "Content-Type")
let body: [String: Any] = [
  "name": "Jane Doe",
  "email": "jane@example.com",
  "event_date": "2026-01-15",
  "tickets": [["ticket_type":"general","quantity":2]]
]
req.httpBody = try! JSONSerialization.data(withJSONObject: body)
URLSession.shared.dataTask(with: req) { data, resp, err in
  guard let data = data else { return }
  let json = try? JSONSerialization.jsonObject(with: data)
  print(String(describing: json))
}.resume()
```

### Example response (sale created)

```json
{
  "id": 9876,
  "status": "pending",
  "name": "Jane Doe",
  "email": "jane@example.com",
  "total_cents": 4000,
  "currency": "USD",
  "tickets": [{ "id": 1, "ticket_type": "general", "status": "reserved" }]
}
```

---

## Create Stripe Checkout Session for a Sale

curl:

```bash
curl -X POST "https://api.example.com/tickets/<SALE_ID>/checkout" \
  -H "X-API-Key: <API_KEY>"
```

Swift:

```swift
let url = URL(string: "https://api.example.com/tickets/<SALE_ID>/checkout")!
var req = URLRequest(url: url)
req.httpMethod = "POST"
req.setValue("<API_KEY>", forHTTPHeaderField: "X-API-Key")
URLSession.shared.dataTask(with: req) { data, resp, err in
  guard let data = data else { return }
  let json = try? JSONSerialization.jsonObject(with: data)
  print(String(describing: json))
}.resume()
```

### Example response (checkout session)

```json
{
  "data": {
    "url": "https://checkout.stripe.com/pay/cs_test_ABC123",
    "id": "cs_test_ABC123"
  }
}
```

---

## Add role member

curl:

```bash
curl -X POST "https://api.example.com/roles/<ROLE_ID>/members" \
  -H "X-API-Key: <API_KEY>" \
  -H "Content-Type: application/json" \
  -d '{"user_id":123, "email":"new@user.com", "role_label":"volunteer"}'
```

Swift:

```swift
let url = URL(string: "https://api.example.com/roles/<ROLE_ID>/members")!
var req = URLRequest(url: url)
req.httpMethod = "POST"
req.setValue("<API_KEY>", forHTTPHeaderField: "X-API-Key")
req.setValue("application/json", forHTTPHeaderField: "Content-Type")
let body = ["user_id": 123, "email": "new@user.com", "role_label": "volunteer"] as [String : Any]
req.httpBody = try! JSONSerialization.data(withJSONObject: body)
URLSession.shared.dataTask(with: req) { data, resp, err in
  // handle response
}.resume()
```

### Example response (role member created)

```json
{
  "id": 345,
  "user_id": 123,
  "email": "new@user.com",
  "role_label": "volunteer",
  "status": "invited"
}
```

---

## Upload media (multipart)

curl:

```bash
curl -X POST "https://api.example.com/media" \
  -H "X-API-Key: <API_KEY>" \
  -F "file=@/path/to/image.jpg" \
  -F "tags[]=1" -F "tags[]=2"
```

Swift: recommended to use Alamofire or similar for multipart uploads; set the `X-API-Key` header and post multipart form data.

### Example response (media uploaded)

```json
{
  "id": 555,
  "filename": "image.jpg",
  "url": "https://cdn.example.com/media/image.jpg",
  "mime_type": "image/jpeg",
  "size": 123456,
  "variants": [{ "label": "thumb", "url": "https://cdn.example.com/media/image_thumb.jpg" }]
}
```

---

Notes:

- All endpoints require `X-API-Key` header.
- IDs in URLs use the platform's encoded id format (use `UrlUtils::encodeId`/decode as needed).
