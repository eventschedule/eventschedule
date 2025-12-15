# API Examples for iOS

Set `X-API-Key` header on all requests.

Example: Create a sale (checkout)

curl:
```bash
curl -X POST https://api.example.com/api/events/my-event-subdomain/checkout \
  -H "X-API-Key: <API_KEY>" \
  -H "Content-Type: application/json" \
  -d '{"name":"Jane Doe","email":"jane@example.com","event_date":"2025-12-20","tickets":{"abc123":2}}'
```

Swift (URLSession):
```swift
let url = URL(string: "https://api.example.com/api/events/my-event-subdomain/checkout")!
var req = URLRequest(url: url)
req.httpMethod = "POST"
req.setValue("<API_KEY>", forHTTPHeaderField: "X-API-Key")
req.setValue("application/json", forHTTPHeaderField: "Content-Type")
let body: [String: Any] = ["name": "Jane Doe", "email": "jane@example.com", "event_date": "2025-12-20", "tickets": ["abc123": 2]]
req.httpBody = try! JSONSerialization.data(withJSONObject: body)
URLSession.shared.dataTask(with: req) { data, resp, err in
  // handle response
}.resume()
```

Example: Create Stripe checkout session for a sale

curl:
```bash
curl -X POST https://api.example.com/api/tickets/<SALE_ID>/checkout \
  -H "X-API-Key: <API_KEY>"
```

Swift:
```swift
let url = URL(string: "https://api.example.com/api/tickets/<SALE_ID>/checkout")!
var req = URLRequest(url: url)
req.httpMethod = "POST"
req.setValue("<API_KEY>", forHTTPHeaderField: "X-API-Key")
URLSession.shared.dataTask(with: req) { data, resp, err in
  // parse JSON and open `data.url` in SFSafariViewController
}.resume()
```

Example: Upload media asset (multipart)

curl:
```bash
curl -X POST https://api.example.com/api/media \
  -H "X-API-Key: <API_KEY>" \
  -F "file=@/path/to/image.jpg" \
  -F "tags[]=1" \
  -F "folder=logos"
```

Swift: use `URLRequest` with multipart form data (recommended to use Alamofire or similar for multipart uploads).
