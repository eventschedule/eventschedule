# Flyer Image Testing Scripts

This directory contains scripts to verify the event flyer image implementation.

---

## Scripts

### 1. verify_flyer_images.php

**Purpose:** Verify database schema, model configuration, and data integrity.

**Usage:**
```bash
php tests/verify_flyer_images.php
```

**What it tests:**
- ✓ Database schema (columns exist)
- ✓ Model fillable fields
- ✓ Model relationship
- ✓ API serialization
- ✓ Data integrity (orphaned references)
- ✓ Sample events with flyers

**When to use:**
- After running migrations
- After model changes
- To verify backend configuration
- To check data consistency

**Output:**
```
✅ ALL TESTS PASSED
The backend is correctly configured to handle flyer images.
```

---

### 2. test_flyer_api.sh

**Purpose:** Test the flyer upload API endpoint end-to-end.

**Usage:**
```bash
# Set environment variables
export API_KEY='your-api-key-here'
export EVENT_ID='encoded-event-id'
export IMAGE_PATH='/path/to/test-image.jpg'

# Run the script
./tests/test_flyer_api.sh
```

**Or inline:**
```bash
API_KEY='xxx' EVENT_ID='yyy' IMAGE_PATH='image.jpg' ./tests/test_flyer_api.sh
```

**What it tests:**
- ✓ API connectivity
- ✓ Event retrieval
- ✓ Image upload
- ✓ Response format
- ✓ Data persistence
- ✓ Image URL accessibility

**When to use:**
- To verify upload flow works
- To test API responses
- To debug image URL issues
- Before iOS app testing

**Output:**
```
✅ ALL TESTS PASSED

Flyer URL: /storage/flyer_xyz.jpg
Full URL: https://testevents.fior.es/storage/flyer_xyz.jpg
```

---

## Getting Your API Key

### Method 1: Laravel Tinker
```bash
php artisan tinker

>>> $user = App\Models\User::first();
>>> $token = $user->createToken('test', ['resources.view', 'resources.manage']);
>>> $token->plainTextToken;
```

### Method 2: Database Query
```sql
-- Get existing tokens
SELECT 
    u.email,
    pat.name,
    pat.token,
    pat.abilities
FROM personal_access_tokens pat
JOIN users u ON pat.tokenable_id = u.id
ORDER BY pat.created_at DESC
LIMIT 5;
```

### Method 3: Create via API
See your application's authentication documentation.

---

## Getting Event ID

### Method 1: From Database
```bash
php artisan tinker

>>> $event = App\Models\Event::first();
>>> App\Utils\UrlUtils::encodeId($event->id);
```

### Method 2: From API
```bash
curl https://testevents.fior.es/api/events \
  -H "X-API-Key: YOUR_KEY" | jq '.data[0].id'
```

The ID should be an encoded string like `"abc123xyz"`.

---

## Example Test Run

```bash
# 1. Get your API credentials
export API_KEY='1|abcd1234...'

# 2. Get an event ID
EVENT_ID=$(curl -s https://testevents.fior.es/api/events \
  -H "X-API-Key: $API_KEY" | jq -r '.data[0].id')

echo "Testing with Event ID: $EVENT_ID"

# 3. Prepare a test image
export IMAGE_PATH="$HOME/Downloads/test-flyer.jpg"

# 4. Run verification
php tests/verify_flyer_images.php

# 5. Run API test
./tests/test_flyer_api.sh

# 6. Verify the result
curl -s https://testevents.fior.es/api/events \
  -H "X-API-Key: $API_KEY" | \
  jq ".data[] | select(.id == \"$EVENT_ID\") | .flyer_image_url"
```

---

## Troubleshooting

### Script Permissions

If you get "Permission denied":
```bash
chmod +x tests/verify_flyer_images.php
chmod +x tests/test_flyer_api.sh
```

### PHP Not Found

Make sure PHP is installed and in your PATH:
```bash
which php
# or
php --version
```

If not installed:
- macOS: `brew install php`
- Ubuntu: `apt-get install php-cli`

### curl/jq Not Found

Install required tools:
- macOS: `brew install curl jq`
- Ubuntu: `apt-get install curl jq`

### API Authentication Errors

Check your API key:
```bash
curl -v https://testevents.fior.es/api/events \
  -H "X-API-Key: YOUR_KEY"
```

Look for HTTP status codes:
- 200: Success
- 401: Invalid/expired token
- 403: Insufficient permissions

### Image Upload Fails

Check:
1. File exists and is readable
2. File is a valid image (JPEG, PNG, GIF, WebP)
3. File size is reasonable (< 10MB recommended)
4. API key has `resources.manage` ability

---

## Integration with CI/CD

### Example GitHub Actions

```yaml
name: Test Flyer Images

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    
    steps:
      - uses: actions/checkout@v2
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
      
      - name: Install Dependencies
        run: composer install
      
      - name: Run Migrations
        run: php artisan migrate --force
      
      - name: Verify Flyer Images
        run: php tests/verify_flyer_images.php
```

---

## See Also

- [BACKEND_TEAM_RESPONSE.md](../BACKEND_TEAM_RESPONSE.md) - iOS implementation guide
- [BACKEND_FLYER_IMAGE_INVESTIGATION.md](../BACKEND_FLYER_IMAGE_INVESTIGATION.md) - Detailed investigation
- [IMAGE_UPLOAD_IMPLEMENTATION_GUIDE.md](../IMAGE_UPLOAD_IMPLEMENTATION_GUIDE.md) - Complete API reference
