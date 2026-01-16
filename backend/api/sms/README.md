# SMS API Documentation

This SMS integration allows you to receive and reply to SMS messages via your SMS gateway, in addition to the existing OTP functionality.

## Setup

### 1. Create Database Table

Run the migration to create the `sms_messages` table:

```sql
-- Run this SQL in your database
SOURCE backend/migrations/create_sms_messages_table.sql;
```

Or manually execute the SQL from `backend/migrations/create_sms_messages_table.sql`.

### 2. Configure Webhook URL

Configure your SMS gateway to send incoming messages to:
```
http://your-domain/bakery/backend/api/sms_webhook.php
```

## API Endpoints

### 1. Send SMS (General Purpose)

**Endpoint:** `POST /backend/api/send-sms.php`

Send an SMS message to any phone number.

**Request Body (JSON):**
```json
{
  "phone": "+1234567890",
  "message": "Hello, this is a test message"
}
```

**Response:**
```json
{
  "ok": true,
  "phone": "+1234567890",
  "message": "Hello, this is a test message",
  "messageId": "msg_123456",
  "status": "sent",
  "statusLine": "HTTP/1.1 200 OK",
  "response": {...},
  "stored": true
}
```

### 2. Reply to SMS

**Endpoint:** `POST /backend/api/reply-sms.php`

Reply to an incoming SMS. Automatically marks all inbound messages from that number as read.

**Request Body (JSON):**
```json
{
  "phone": "+1234567890",
  "message": "Thank you for your message!"
}
```

**Response:**
```json
{
  "ok": true,
  "phone": "+1234567890",
  "message": "Thank you for your message!",
  "messageId": "msg_123456",
  "status": "sent",
  "stored": true
}
```

### 3. Get Messages

**Endpoint:** `GET /backend/api/get-messages.php`

Retrieve SMS messages with optional filtering.

**Query Parameters:**
- `phone` (optional): Filter by phone number
- `direction` (optional): `inbound` or `outbound`
- `limit` (optional): Number of messages to return (default: 50)
- `offset` (optional): Pagination offset (default: 0)
- `unread` (optional): `true` to get only unread inbound messages

**Example:**
```
GET /backend/api/get-messages.php?phone=+1234567890&direction=inbound&limit=20
```

**Response:**
```json
{
  "ok": true,
  "messages": [
    {
      "id": 1,
      "phoneNumber": "+1234567890",
      "message": "Hello",
      "direction": "inbound",
      "status": "received",
      "messageId": "msg_123",
      "error": null,
      "createdAt": "2026-01-12 10:30:00",
      "readAt": null,
      "isRead": false
    }
  ],
  "total": 1,
  "limit": 50,
  "offset": 0
}
```

### 4. Get Conversations

**Endpoint:** `GET /backend/api/get-conversations.php`

Get a list of all phone numbers with message history (conversations).

**Query Parameters:**
- `limit` (optional): Number of conversations to return (default: 20)
- `offset` (optional): Pagination offset (default: 0)

**Example:**
```
GET /backend/api/get-conversations.php?limit=10
```

**Response:**
```json
{
  "ok": true,
  "conversations": [
    {
      "phoneNumber": "+1234567890",
      "lastMessageAt": "2026-01-12 10:30:00",
      "messageCount": 5,
      "unreadCount": 2,
      "latestMessage": {
        "message": "Hello",
        "direction": "inbound",
        "createdAt": "2026-01-12 10:30:00"
      }
    }
  ],
  "total": 1,
  "limit": 20,
  "offset": 0
}
```

### 5. Mark Messages as Read

**Endpoint:** `POST /backend/api/mark-read-sms.php`

Mark inbound messages as read.

**Request Body (JSON):**
```json
{
  "phone": "+1234567890"
}
```

Or mark a specific message:
```json
{
  "messageId": 123
}
```

**Response:**
```json
{
  "ok": true,
  "updated": 3
}
```

## Webhook (Incoming Messages)

The webhook at `/backend/api/sms_webhook.php` automatically processes incoming SMS messages and stores them in the database.

**Expected Webhook Format:**
Your SMS gateway should send POST requests with JSON data in one of these formats:

```json
{
  "from": "+1234567890",
  "message": "Hello"
}
```

Or:
```json
{
  "phoneNumber": "+1234567890",
  "textMessage": {
    "text": "Hello"
  }
}
```

The webhook will automatically detect and process the format.

## Usage Examples

### JavaScript/Fetch Example

```javascript
// Send SMS
fetch('/bakery/backend/api/send-sms.php', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    phone: '+1234567890',
    message: 'Hello from bakery!'
  })
})
.then(res => res.json())
.then(data => console.log(data));

// Get messages
fetch('/bakery/backend/api/get-messages.php?direction=inbound&unread=true')
.then(res => res.json())
.then(data => console.log(data.messages));

// Reply to SMS
fetch('/bakery/backend/api/reply-sms.php', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    phone: '+1234567890',
    message: 'Thank you for contacting us!'
  })
})
.then(res => res.json())
.then(data => console.log(data));
```

### PHP cURL Example

```php
// Send SMS
$ch = curl_init('http://localhost/bakery/backend/api/send-sms.php');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'phone' => '+1234567890',
    'message' => 'Hello from bakery!'
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);
$data = json_decode($response, true);
```

## Database Schema

The `sms_messages` table stores all SMS messages:

- `smsID`: Primary key
- `phoneNumber`: Phone number (with + prefix)
- `message`: Message text
- `direction`: `inbound` or `outbound`
- `status`: Message status (sent, received, failed, etc.)
- `messageID`: Gateway message ID
- `error`: Error message if failed
- `created_at`: Timestamp
- `read_at`: When message was read (for inbound messages)

## Notes

- All phone numbers are automatically formatted with a `+` prefix
- Incoming messages are automatically stored when received via webhook
- Outgoing messages are stored after sending
- The webhook logs all activity to `backend/logs/sms_log.txt`
- OTP functionality remains separate and unchanged
