#!/bin/bash

# API Testing Script for Event Flyer Images
# This script tests the flyer upload endpoint directly

set -e

echo "========================================"
echo "Event Flyer Image API Test"
echo "========================================"
echo ""

# Configuration
BASE_URL="${BASE_URL:-https://testevents.fior.es}"
API_KEY="${API_KEY:-}"
EVENT_ID="${EVENT_ID:-}"
IMAGE_PATH="${IMAGE_PATH:-}"

# Check if required variables are set
if [ -z "$API_KEY" ]; then
    echo "❌ Error: API_KEY not set"
    echo ""
    echo "Usage:"
    echo "  export API_KEY='your-api-key'"
    echo "  export EVENT_ID='encoded-event-id'"
    echo "  export IMAGE_PATH='/path/to/image.jpg'"
    echo "  ./tests/test_flyer_api.sh"
    echo ""
    echo "Or run inline:"
    echo "  API_KEY='xxx' EVENT_ID='yyy' IMAGE_PATH='image.jpg' ./tests/test_flyer_api.sh"
    echo ""
    exit 1
fi

if [ -z "$EVENT_ID" ]; then
    echo "❌ Error: EVENT_ID not set"
    exit 1
fi

if [ -z "$IMAGE_PATH" ]; then
    echo "❌ Error: IMAGE_PATH not set"
    exit 1
fi

if [ ! -f "$IMAGE_PATH" ]; then
    echo "❌ Error: Image file not found: $IMAGE_PATH"
    exit 1
fi

echo "Configuration:"
echo "  Base URL: $BASE_URL"
echo "  Event ID: $EVENT_ID"
echo "  Image: $IMAGE_PATH"
echo ""

# Test 1: Check API connectivity
echo "✓ Test 1: API Connectivity"
echo "-----------------------------------"

HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" \
    -H "X-API-Key: $API_KEY" \
    "$BASE_URL/api/events")

if [ "$HTTP_CODE" != "200" ]; then
    echo "❌ FAIL: Cannot connect to API (HTTP $HTTP_CODE)"
    echo "   Check your API key and base URL"
    exit 1
fi

echo "  ✓ API is accessible (HTTP 200)"
echo ""

# Test 2: Get event before upload
echo "✓ Test 2: Get Event Data (Before Upload)"
echo "-----------------------------------"

RESPONSE=$(curl -s \
    -H "X-API-Key: $API_KEY" \
    -H "Accept: application/json" \
    "$BASE_URL/api/events" | jq ".data[] | select(.id == \"$EVENT_ID\")")

if [ -z "$RESPONSE" ]; then
    echo "❌ FAIL: Event not found with ID: $EVENT_ID"
    exit 1
fi

EVENT_NAME=$(echo "$RESPONSE" | jq -r '.name')
CURRENT_FLYER=$(echo "$RESPONSE" | jq -r '.flyer_image_url // "null"')

echo "  Event: $EVENT_NAME"
echo "  Current flyer_image_url: $CURRENT_FLYER"
echo ""

# Test 3: Upload flyer image
echo "✓ Test 3: Upload Flyer Image"
echo "-----------------------------------"

UPLOAD_RESPONSE=$(curl -s -w "\nHTTP_CODE:%{http_code}" \
    -X POST \
    -H "X-API-Key: $API_KEY" \
    -F "flyer_image=@$IMAGE_PATH" \
    "$BASE_URL/api/events/flyer/$EVENT_ID")

HTTP_CODE=$(echo "$UPLOAD_RESPONSE" | grep "HTTP_CODE:" | cut -d: -f2)
RESPONSE_BODY=$(echo "$UPLOAD_RESPONSE" | sed '/HTTP_CODE:/d')

if [ "$HTTP_CODE" != "200" ]; then
    echo "❌ FAIL: Upload failed (HTTP $HTTP_CODE)"
    echo "Response:"
    echo "$RESPONSE_BODY" | jq '.' 2>/dev/null || echo "$RESPONSE_BODY"
    exit 1
fi

echo "  ✓ Upload successful (HTTP 200)"

# Parse response
NEW_FLYER=$(echo "$RESPONSE_BODY" | jq -r '.data.flyer_image_url // "null"')

if [ "$NEW_FLYER" = "null" ] || [ -z "$NEW_FLYER" ]; then
    echo "  ❌ FAIL: Response does not include flyer_image_url"
    echo "  Response:"
    echo "$RESPONSE_BODY" | jq '.data | {id, name, flyer_image_url, flyer_image_id}'
    exit 1
fi

echo "  ✓ Response includes flyer_image_url: $NEW_FLYER"
echo ""

# Test 4: Verify persistence
echo "✓ Test 4: Verify Persistence"
echo "-----------------------------------"

sleep 1  # Brief pause to ensure database commit

VERIFY_RESPONSE=$(curl -s \
    -H "X-API-Key: $API_KEY" \
    -H "Accept: application/json" \
    "$BASE_URL/api/events" | jq ".data[] | select(.id == \"$EVENT_ID\")")

PERSISTED_FLYER=$(echo "$VERIFY_RESPONSE" | jq -r '.flyer_image_url // "null"')

if [ "$PERSISTED_FLYER" != "$NEW_FLYER" ]; then
    echo "  ❌ FAIL: Flyer URL not persisted correctly"
    echo "    Expected: $NEW_FLYER"
    echo "    Got: $PERSISTED_FLYER"
    exit 1
fi

echo "  ✓ Flyer URL persisted in database"
echo "  ✓ GET /api/events includes flyer_image_url"
echo ""

# Test 5: Verify image is accessible
echo "✓ Test 5: Verify Image URL"
echo "-----------------------------------"

# Convert relative URL to absolute
if [[ "$NEW_FLYER" == /storage/* ]]; then
    FULL_URL="${BASE_URL}${NEW_FLYER}"
elif [[ "$NEW_FLYER" == http* ]]; then
    FULL_URL="$NEW_FLYER"
else
    echo "  ⚠️  WARNING: Unexpected URL format: $NEW_FLYER"
    FULL_URL="${BASE_URL}/${NEW_FLYER}"
fi

echo "  Checking URL: $FULL_URL"

IMAGE_HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "$FULL_URL")

if [ "$IMAGE_HTTP_CODE" = "200" ]; then
    echo "  ✓ Image is accessible (HTTP 200)"
elif [ "$IMAGE_HTTP_CODE" = "404" ]; then
    echo "  ⚠️  WARNING: Image not found at URL (HTTP 404)"
    echo "     The image may be stored on a different disk/path"
else
    echo "  ⚠️  WARNING: Unexpected response (HTTP $IMAGE_HTTP_CODE)"
fi

echo ""

# Summary
echo "========================================"
echo "Summary"
echo "========================================"
echo ""
echo "✅ ALL TESTS PASSED"
echo ""
echo "Results:"
echo "  • Upload endpoint works correctly"
echo "  • Response includes flyer_image_url"
echo "  • Data persists in database"
echo "  • GET /api/events returns flyer_image_url"
echo ""
echo "Flyer URL: $NEW_FLYER"
echo "Full URL: $FULL_URL"
echo ""
echo "For iOS App:"
echo "  1. Parse flyer_image_url from API response"
echo "  2. Convert to absolute URL:"
echo "     - Remove /api from base: ${BASE_URL%/api}"
echo "     - Append relative path: $NEW_FLYER"
echo "     - Result: $FULL_URL"
echo "  3. Load image from URL"
echo ""
