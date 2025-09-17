# Google Calendar Integration Setup

This document explains how to set up and use the Google Calendar integration feature in the EventSchedule application.

## Prerequisites

1. A Google Cloud Console project
2. Google Calendar API enabled
3. OAuth 2.0 credentials configured

## Setup Instructions

### 1. Google Cloud Console Setup

1. Go to the [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select an existing one
3. Enable the Google Calendar API:
   - Go to "APIs & Services" > "Library"
   - Search for "Google Calendar API"
   - Click on it and press "Enable"

### 2. OAuth 2.0 Credentials

1. Go to "APIs & Services" > "Credentials"
2. Click "Create Credentials" > "OAuth 2.0 Client IDs"
3. Choose "Web application" as the application type
4. Add authorized redirect URIs:
   - For development: `http://localhost:8000/google-calendar/callback`
   - For production: `https://yourdomain.com/google-calendar/callback`
5. Save the credentials and note down the Client ID and Client Secret

### 3. Environment Configuration

Add the following environment variables to your `.env` file:

```env
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret
GOOGLE_REDIRECT_URI=https://yourdomain.com/google-calendar/callback
```


## Features

### User Features

1. **Connect Google Calendar**: Users can connect their Google Calendar account from their profile page
2. **Sync All Events**: Users can sync all their events to Google Calendar at once
3. **Individual Event Sync**: Users can sync individual events from the event edit page
4. **Automatic Sync**: Events are automatically synced when created, updated, or deleted (if user has Google Calendar connected)
5. **Bidirectional Sync**: Events added in either Google Calendar or EventSchedule will appear in both places
6. **Calendar Selection**: Choose which Google Calendar to sync with for each role/schedule
7. **Real-time Updates**: Changes in Google Calendar are automatically synced to EventSchedule via webhooks

### Event Information Synced

- Event name
- Event description (with venue and URL information)
- Start and end times
- Location (venue address)
- Event URL

### Sync Status

Events can have three sync statuses:
- **Not Connected**: User hasn't connected their Google Calendar
- **Not Synced**: User has connected Google Calendar but this event isn't synced
- **Synced**: Event is synced to Google Calendar

### Bidirectional Sync

When bidirectional sync is enabled:
- Events created in EventSchedule are automatically added to Google Calendar
- Events created in Google Calendar are automatically added to EventSchedule
- Changes in either system are reflected in the other
- Real-time updates via Google Calendar webhooks

## Usage

### For Users

1. **Connect Google Calendar**:
   - Go to your profile page (`/account`)
   - Scroll to the "Google Calendar Integration" section
   - Click "Connect Google Calendar"
   - Authorize the application in the Google OAuth flow

2. **Sync All Events**:
   - After connecting, click "Sync All Events" in your profile
   - This will sync all your events to your primary Google Calendar

3. **Sync Individual Events**:
   - Go to any event edit page
   - Scroll to the "Google Calendar Sync" section
   - Click "Sync to Google Calendar" or "Remove from Google Calendar"

4. **Enable Bidirectional Sync**:
   - Go to your role/schedule edit page
   - Scroll to the "Google Calendar Integration" section
   - Select which Google Calendar to sync with
   - Click "Enable" for bidirectional sync
   - Events will now sync both ways automatically

5. **Manual Sync Options**:
   - **Sync to Google Calendar**: Push all events from EventSchedule to Google Calendar
   - **Sync from Google Calendar**: Pull all events from Google Calendar to EventSchedule

### For Developers

#### Automatic Sync

Events are automatically synced when:
- Created (if user has Google Calendar connected)
- Updated (if user has Google Calendar connected)
- Deleted (if event was previously synced)

#### Manual Sync

You can manually sync events using the Event model:

```php
// Sync an event for all connected users
$event->syncToGoogleCalendar('create'); // or 'update', 'delete'

// Check sync status
$event->isSyncedToGoogleCalendar();

// Get sync status for a specific user
$event->getGoogleCalendarSyncStatus($user);
```

#### Background Jobs

Sync operations are handled by background jobs (`SyncEventToGoogleCalendar`) to avoid blocking the user interface.

## API Endpoints

- `GET /google-calendar/redirect` - Start OAuth flow
- `GET /google-calendar/callback` - OAuth callback
- `GET /google-calendar/disconnect` - Disconnect Google Calendar
- `GET /google-calendar/calendars` - Get user's calendars
- `POST /google-calendar/sync-events` - Sync all events
- `POST /google-calendar/sync-event/{eventId}` - Sync specific event
- `DELETE /google-calendar/unsync-event/{eventId}` - Remove event from Google Calendar
- `POST /google-calendar/role/{subdomain}` - Update role's Google Calendar selection
- `POST /google-calendar/bidirectional/{subdomain}/enable` - Enable bidirectional sync
- `POST /google-calendar/bidirectional/{subdomain}/disable` - Disable bidirectional sync
- `POST /google-calendar/sync-from-google/{subdomain}` - Sync from Google Calendar to EventSchedule
- `GET /google-calendar/webhook` - Webhook verification (Google Calendar)
- `POST /google-calendar/webhook` - Webhook handler (Google Calendar)

## Troubleshooting

### Common Issues

1. **"Google Calendar not connected" error**:
   - User needs to connect their Google Calendar account first
   - Check if OAuth credentials are correctly configured

2. **"Failed to sync event" error**:
   - Check if Google Calendar API is enabled
   - Verify OAuth credentials are correct
   - Check application logs for detailed error messages

3. **Events not syncing automatically**:
   - Ensure queue workers are running for background jobs
   - Check if user has valid Google Calendar tokens

### Logs

Sync operations are logged in the application logs. Check `storage/logs/laravel.log` for detailed information about sync operations.

## Security Considerations

1. **Token Storage**: Google OAuth tokens are stored encrypted in the database
2. **Scope Limitation**: The integration only requests necessary Google Calendar permissions
3. **User Authorization**: Users can only sync events they have access to
4. **Token Refresh**: Access tokens are automatically refreshed when needed

## Future Enhancements

Potential future improvements:
- Support for multiple Google Calendars per role
- Enhanced conflict resolution for simultaneous edits
- Sync status dashboard with detailed logs
- Bulk sync operations with progress tracking
- Custom field mapping between systems
- Event category/tag synchronization
- Recurring event support improvements
