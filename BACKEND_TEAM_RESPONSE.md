# Backend Team Response: Flyer Image URLs

**Date:** December 16, 2025  
**Issue:** Event flyer images not displaying in iOS app  
**Status:** ✅ Backend verified and ready

---

## Investigation Complete

We've completed a thorough investigation of the backend implementation. Here's what we found:

### ✅ Backend Implementation is Correct

1. **Database Schema** - All fields properly configured:
   - `flyer_image_url` VARCHAR(255) NULLABLE ✅
   - `flyer_image_id` BIGINT NULLABLE (FK to images) ✅

2. **Model Configuration** - Event.php properly set up:
   - Both fields in `$fillable` array ✅
   - Relationship `flyerImage()` defined ✅
   - `toApiData()` includes `flyer_image_url` ✅

3. **API Endpoint** - Upload handler working correctly:
   - Endpoint: `POST /api/events/flyer/{event_id}` ✅
   - Saves both `flyer_image_id` and `flyer_image_url` ✅
   - Returns updated event via `toApiData()` ✅

4. **API Response** - Events list includes field:
   - `GET /api/events` returns `flyer_image_url` ✅
   - Field is always present in response (may be NULL) ✅

---

## What We've Provided

### 1. Verification Scripts

Two scripts to test the implementation:

#### PHP Verification Script
```bash
php tests/verify_flyer_images.php
```

This checks:
- Database schema
- Model configuration
- API serialization
- Data integrity
- Sample events with flyers

#### API Test Script
```bash
export API_KEY='your-api-key'
export EVENT_ID='encoded-event-id'
export IMAGE_PATH='path/to/test-image.jpg'
./tests/test_flyer_api.sh
```

This tests:
- API connectivity
- Event data before upload
- Flyer upload
- Data persistence
- Image URL accessibility

### 2. Investigation Document

See [BACKEND_FLYER_IMAGE_INVESTIGATION.md](BACKEND_FLYER_IMAGE_INVESTIGATION.md) for:
- Complete code review results
- Database verification queries
- Testing procedures
- Troubleshooting steps

---

## For iOS Team: What You Need to Know

### API Response Format

The `/api/events` endpoint returns events with this structure:

```json
{
  "data": [
    {
      "id": "abc123xyz",
      "name": "Sample Event",
      "description": "...",
      "starts_at": "2025-12-20 19:00:00",
      "flyer_image_url": "/storage/flyer_xyz789.jpg",
      "venue": { ... },
      ...
    }
  ],
  "meta": { ... }
}
```

### Important: URL Format

The `flyer_image_url` field contains a **relative path**, not an absolute URL:

```
Returned value: "/storage/flyer_xyz789.jpg"
```

### iOS App Must Convert to Absolute URL

To display the image, you need to construct the full URL:

```swift
// 1. Get your base URL (without /api)
let baseURL = "https://testevents.fior.es"  // Note: no /api suffix

// 2. Get the relative path from API
let relativePath = event.flyer_image_url  // "/storage/flyer_xyz789.jpg"

// 3. Combine them
let fullURL = baseURL + relativePath
// Result: "https://testevents.fior.es/storage/flyer_xyz789.jpg"

// 4. Load the image
AsyncImage(url: URL(string: fullURL))
```

### Example Implementation

```swift
struct EventRow: View {
    let event: Event
    let baseURL: String  // e.g., "https://testevents.fior.es"
    
    var flyerImageURL: URL? {
        guard let relativePath = event.flyer_image_url,
              !relativePath.isEmpty else {
            return nil
        }
        
        // Construct absolute URL
        let urlString = baseURL + relativePath
        return URL(string: urlString)
    }
    
    var body: some View {
        HStack {
            if let imageURL = flyerImageURL {
                AsyncImage(url: imageURL) { image in
                    image
                        .resizable()
                        .aspectRatio(contentMode: .fill)
                        .frame(width: 60, height: 60)
                        .clipped()
                } placeholder: {
                    Rectangle()
                        .fill(Color.gray.opacity(0.3))
                        .frame(width: 60, height: 60)
                }
            } else {
                // Default placeholder when no flyer exists
                Image(systemName: "photo")
                    .frame(width: 60, height: 60)
            }
            
            VStack(alignment: .leading) {
                Text(event.name)
                Text(event.starts_at)
            }
        }
    }
}
```

### Field Availability

The `flyer_image_url` field is:
- ✅ Always present in the response
- ✅ Will be `null` if no flyer uploaded
- ✅ Will be a string path if flyer exists
- ✅ Persists after app restart (it's in the API, not local storage)

### Testing Steps

1. **Verify API Response**:
   ```bash
   curl https://testevents.fior.es/api/events \
     -H "X-API-Key: YOUR_KEY" | jq '.data[0].flyer_image_url'
   ```
   
   Should return either `null` or a path like `"/storage/flyer_xyz.jpg"`

2. **Test Image URL**:
   ```bash
   # If API returns: /storage/flyer_xyz.jpg
   # Test this URL in your browser:
   https://testevents.fior.es/storage/flyer_xyz.jpg
   ```

3. **Upload Test**:
   - Create/select an event in your iOS app
   - Upload a flyer image
   - Verify the API response includes `flyer_image_url`
   - Refresh the events list
   - Confirm the image displays

---

## Common Issues & Solutions

### Issue 1: `flyer_image_url` is NULL for All Events

**Cause:** No flyer images have been uploaded yet.

**Solution:** Upload a flyer via the iOS app or test script.

### Issue 2: Image URLs Return 404

**Possible causes:**
1. Storage disk not configured correctly (backend issue)
2. Files not accessible via web server (backend issue)
3. URL construction incorrect (iOS app issue)

**Test:** Try accessing the URL directly in a browser using the path from the API.

### Issue 3: Images Display on First Load But Disappear

**Cause:** iOS app might be using cached API responses that don't include the field.

**Solution:** Clear app cache/data and re-fetch from API.

### Issue 4: Field Not Present in API Response

**This should not happen.** Our code review confirms the field is included. If you see this:
1. Verify you're calling the correct endpoint (`/api/events`)
2. Check that you're not using a cached/mock response
3. Send us the actual API response for investigation

---

## Testing Checklist for iOS Team

- [ ] API returns `flyer_image_url` field in response (even if NULL)
- [ ] iOS app correctly extracts the field from JSON
- [ ] iOS app converts relative path to absolute URL
- [ ] iOS app displays image in list view
- [ ] iOS app displays image in detail view
- [ ] Upload flow works: select event → upload image → see image
- [ ] Images persist after app restart
- [ ] Images update when switching between events
- [ ] Placeholder shown when `flyer_image_url` is NULL
- [ ] No errors/crashes when field is NULL
- [ ] Images clear properly when switching servers

---

## Next Steps

1. **Run Verification Scripts** (Optional but recommended):
   ```bash
   php tests/verify_flyer_images.php
   ./tests/test_flyer_api.sh
   ```

2. **Test API Directly**:
   - Use Postman, curl, or browser to verify the API returns the field
   - Upload a test image and check the response

3. **Implement iOS Changes**:
   - Parse `flyer_image_url` from API response
   - Convert relative path to absolute URL
   - Display image using standard iOS image loading

4. **Test End-to-End**:
   - Upload flyers for multiple events
   - Verify they display correctly
   - Test edge cases (NULL values, network errors, etc.)

---

## Support

If you encounter any issues:

1. **Check the field is in the API response**:
   ```bash
   curl https://testevents.fior.es/api/events \
     -H "X-API-Key: YOUR_KEY" | jq '.data[0] | keys'
   ```
   You should see `flyer_image_url` in the list.

2. **Verify the URL is accessible**:
   - Take the `flyer_image_url` value from the API
   - Add your base URL (without /api)
   - Try accessing it in a browser

3. **Check our investigation document**:
   - See [BACKEND_FLYER_IMAGE_INVESTIGATION.md](BACKEND_FLYER_IMAGE_INVESTIGATION.md)
   - Contains detailed verification queries and troubleshooting

4. **Share API response**:
   - If the field is missing or format is wrong, share the actual API response
   - We'll investigate further

---

## Summary

✅ **Backend is ready and working correctly**

The `flyer_image_url` field:
- ✅ Exists in database
- ✅ Is saved during upload
- ✅ Is returned in API responses
- ✅ Is always present (may be NULL)

**iOS app needs to:**
1. Parse the field from JSON
2. Convert relative path → absolute URL
3. Display the image

**Format: `baseURL + flyer_image_url`**

Example: `"https://testevents.fior.es" + "/storage/flyer_xyz.jpg"`

Let us know if you need any clarification or encounter issues! 🚀
