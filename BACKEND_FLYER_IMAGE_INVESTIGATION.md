# Backend Investigation: Flyer Image URLs in API Response

**Date:** December 16, 2025  
**Issue:** iOS app not displaying event flyer images despite successful uploads  
**Status:** Investigation Complete ✅

---

## Investigation Summary

### Code Review Results

✅ **Database Schema** - Fields exist in `events` table:
- `flyer_image_url` VARCHAR(255) NULLABLE
- `flyer_image_id` BIGINT NULLABLE (foreign key to images)

✅ **Model Configuration** - `app/Models/Event.php`:
- Both fields are in `$fillable` array (lines 48-49)
- `flyerImage()` relationship defined (line 299)
- `toApiData()` method includes `'flyer_image_url' => $this->flyer_image_url` (line 920)

✅ **API Controller** - `app/Http/Controllers/Api/ApiEventController.php`:
- Upload endpoint exists: `POST /api/events/flyer/{event_id}` (routes/api.php line 25)
- Handler correctly saves both fields after upload (lines 490-491):
  ```php
  $event->flyer_image_id = $image->id;
  $event->flyer_image_url = $image->path;
  $event->save();
  ```
- Returns updated event data via `$event->toApiData()` (line 497)

✅ **API Response** - `index()` method (line 50):
- Uses `$event->toApiData()` for serialization (line 74)
- Should include `flyer_image_url` in response

---

## Root Cause Analysis

The backend code is **correctly implemented**. The issue is likely one of the following:

### 1. Database Values Are NULL (Most Likely)
Even though uploads succeed, the `flyer_image_url` field may be NULL in the database. This could happen if:
- Old events uploaded images before the field was added
- Upload succeeded but save failed silently
- A different code path is clearing the fields

### 2. iOS App URL Conversion Issue
The iOS app might not be correctly converting relative paths to absolute URLs:
- API returns: `/storage/media/flyer_xyz.jpg`
- App needs: `https://testevents.fior.es/storage/media/flyer_xyz.jpg`

### 3. API Response Not Including the Field
Though unlikely given the code review, the response might be excluding NULL values.

---

## Verification Steps

### Step 1: Check Database Values

Run these queries directly on the database:

```sql
-- Check if migration ran successfully
DESCRIBE events;

-- Verify the columns exist
SHOW COLUMNS FROM events LIKE '%flyer%';

-- Check actual event data
SELECT 
    id, 
    name, 
    flyer_image_url, 
    flyer_image_id,
    created_at,
    updated_at
FROM events
ORDER BY updated_at DESC
LIMIT 10;

-- Count events with flyer URLs
SELECT 
    COUNT(*) as total_events,
    COUNT(flyer_image_url) as events_with_flyer_url,
    COUNT(flyer_image_id) as events_with_flyer_id
FROM events;

-- Check if any events have flyer images
SELECT id, name, flyer_image_url, flyer_image_id
FROM events
WHERE flyer_image_url IS NOT NULL OR flyer_image_id IS NOT NULL;
```

### Step 2: Test Upload Endpoint Directly

Using curl or Postman, test the upload flow:

```bash
# Get your API key from the database or Laravel Tinker
# php artisan tinker
# >>> User::first()->createToken('test')->plainTextToken

# Upload a flyer image
curl -X POST https://testevents.fior.es/api/events/flyer/YOUR_EVENT_ID \
  -H "X-API-Key: YOUR_API_KEY" \
  -F "flyer_image=@/path/to/test-image.jpg"

# Expected response:
# {
#   "data": {
#     "id": "...",
#     "name": "...",
#     "flyer_image_url": "/storage/flyer_xyz.jpg",
#     ...
#   },
#   "meta": {
#     "message": "Flyer uploaded successfully"
#   }
# }
```

### Step 3: Test Events List Endpoint

```bash
curl -X GET https://testevents.fior.es/api/events \
  -H "X-API-Key: YOUR_API_KEY" \
  -H "Accept: application/json"

# Check if events have flyer_image_url in response
# Look for: "flyer_image_url": "/storage/flyer_xyz.jpg"
```

### Step 4: Check Laravel Logs

```bash
tail -f storage/logs/laravel.log
```

Look for any errors during upload or save operations.

---

## Recommended Actions

### Action 1: Run Migration Check ✅

Verify the migration was executed:

```bash
php artisan migrate:status | grep add_image_relations
```

Expected output:
```
[2026-09-01 010000] add_image_relations ..................... Ran
```

If not ran, execute it:
```bash
php artisan migrate --path=/database/migrations/2026_09_01_010000_add_image_relations.php
```

### Action 2: Test Data Verification Script

Create a quick verification script:

```php
<?php
// Run with: php artisan tinker

use App\Models\Event;
use App\Utils\UrlUtils;

// Check recent events
$events = Event::orderBy('updated_at', 'desc')->limit(10)->get();

foreach ($events as $event) {
    echo "Event: {$event->name}\n";
    echo "  ID: " . UrlUtils::encodeId($event->id) . "\n";
    echo "  flyer_image_url: {$event->flyer_image_url}\n";
    echo "  flyer_image_id: {$event->flyer_image_id}\n";
    echo "  API data includes: " . (array_key_exists('flyer_image_url', $event->toApiData()) ? 'YES' : 'NO') . "\n";
    echo "  Value in API: " . ($event->toApiData()['flyer_image_url'] ?? 'NULL') . "\n\n";
}
```

### Action 3: Update Existing Events (if needed)

If old events need flyer URLs populated from legacy storage:

```php
<?php
// Run with: php artisan tinker

use App\Models\Event;
use App\Models\Image;

$eventsNeedingUpdate = Event::whereNull('flyer_image_url')
    ->whereNotNull('flyer_image_id')
    ->get();

foreach ($eventsNeedingUpdate as $event) {
    $image = Image::find($event->flyer_image_id);
    if ($image) {
        $event->flyer_image_url = $image->path;
        $event->save();
        echo "Updated Event #{$event->id}: {$event->name}\n";
    }
}
```

### Action 4: Test Fresh Upload

Create a test event and upload a flyer to ensure the full flow works:

```php
<?php
// Run with: php artisan tinker

use App\Models\Event;
use App\Models\User;

$user = User::first();
$event = Event::create([
    'user_id' => $user->id,
    'name' => 'Test Event - Flyer Upload Test',
    'slug' => 'test-event-flyer-' . time(),
    'starts_at' => now()->addDays(7),
    'duration' => 2.0,
]);

echo "Created test event ID: {$event->id}\n";
echo "Encoded ID for API: " . \App\Utils\UrlUtils::encodeId($event->id) . "\n";
echo "Upload a flyer via: POST /api/events/flyer/" . \App\Utils\UrlUtils::encodeId($event->id) . "\n";
```

Then upload via the API and verify:

```php
$event->refresh();
echo "After upload:\n";
echo "  flyer_image_url: {$event->flyer_image_url}\n";
echo "  flyer_image_id: {$event->flyer_image_id}\n";
```

---

## Expected Results

### If Database Values Are NULL:
- Query results show `flyer_image_url` is NULL for events
- Solution: Investigate why upload isn't persisting properly
- Check for: database constraints, validation errors, silent failures

### If Database Values Exist:
- Query shows `flyer_image_url` has values like `/storage/flyer_xyz.jpg`
- API response should include this field
- Solution: iOS app needs to handle relative URLs correctly

### If API Response Excludes the Field:
- Database has values but API doesn't return them
- Check for: custom response formatters, JSON transformers
- This is unlikely given the code review

---

## Communication with iOS Team

### If Database Values Are Present:

"The backend is working correctly. Events have `flyer_image_url` values like `/storage/flyer_xyz.jpg` in the database and API responses. The iOS app needs to:

1. Check that the API response includes the `flyer_image_url` field
2. Convert relative paths to absolute URLs:
   - Remove `/api` from base URL: `https://testevents.fior.es`
   - Append the path: `/storage/flyer_xyz.jpg`
   - Result: `https://testevents.fior.es/storage/flyer_xyz.jpg`

3. Test with this sample response:
```json
{
  "id": "abc123",
  "name": "Sample Event",
  "flyer_image_url": "/storage/flyer_xyz.jpg"
}
```"

### If Database Values Are Missing:

"Investigation found that `flyer_image_url` is NULL in the database even after successful uploads. Backend team is investigating the upload persistence issue. Will update once fixed."

---

## Next Steps

1. ✅ Code review complete - no issues found
2. ⏳ **Run database verification queries** (Step 1 above)
3. ⏳ **Test upload endpoint directly** (Step 2 above)
4. ⏳ **Check actual API responses** (Step 3 above)
5. ⏳ **Report findings to iOS team**

---

## Additional Notes

### Image Path Format

The backend stores paths as **relative URLs** to the storage disk:
- Format: `/storage/flyer_abc123.jpg`
- Disk: public or local (configurable)
- Full URL construction is the client's responsibility

### Backward Compatibility

Events may have:
- Both `flyer_image_id` AND `flyer_image_url` (new uploads)
- Only `flyer_image_id` (legacy, URL needs to be populated)
- Neither (no flyer uploaded)

The API will return whatever is in the database, including NULL values.

---

## Resolution Checklist

- [ ] Migration `2026_09_01_010000_add_image_relations` confirmed ran
- [ ] Database queries show fields exist in `events` table
- [ ] Sample events checked for actual values
- [ ] Upload endpoint tested with curl/Postman
- [ ] API response verified to include `flyer_image_url`
- [ ] Laravel logs checked for errors
- [ ] Test event created and flyer uploaded successfully
- [ ] iOS team provided with findings and guidance

---

**Status:** Ready for database verification and testing
