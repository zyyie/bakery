# SMS Gateway Configuration Guide

This guide explains where to configure local server addresses for SMS Gateway and SMS Forwarder.

## 📍 Configuration Locations

### 1. **SMS Gateway URL** (Where PHP sends SMS TO the gateway)

**File:** `backend/config/sms.php`

**What to configure:**
- `gateway_url`: The address where your SMS Gateway software is running
- This is where your PHP application sends outgoing SMS messages

**Current configuration:**
```php
'gateway_url' => 'http://10.54.202.176:8080',
```

**How to find your SMS Gateway address:**
1. If SMS Gateway is running on the **same computer** as your PHP server:
   - Use `http://localhost:8080` or `http://127.0.0.1:8080`
   - Or use your computer's local IP: `http://192.168.x.x:8080` (check with `ipconfig` on Windows)

2. If SMS Gateway is running on a **different computer** on your network:
   - Use that computer's local IP: `http://192.168.x.x:8080` or `http://10.x.x.x:8080`
   - Make sure both computers are on the same network

3. If SMS Gateway is running on a **remote server**:
   - Use the remote server's IP or domain: `http://your-server-ip:8080`

**Example:**
```php
// If SMS Gateway is on same computer (default port 8080)
'gateway_url' => 'http://localhost:8080',

// If SMS Gateway is on different computer with IP 192.168.1.100
'gateway_url' => 'http://192.168.1.100:8080',

// If SMS Gateway is on different computer with IP 10.54.202.176
'gateway_url' => 'http://10.54.202.176:8080',
```

---

### 2. **Webhook URL** (Where SMS Gateway sends incoming messages TO PHP)

**File to configure in SMS Gateway Software (not PHP):**

Your SMS Gateway software (SMSGate/SMSForwarder) needs to be configured to send incoming messages to this URL:

```
http://YOUR_SERVER_IP/bakery/backend/api/sms/sms_webhook.php
```

**How to find your PHP server address:**

1. **Find your local IP address:**
   - **Windows:** Open Command Prompt and run `ipconfig`
   - Look for "IPv4 Address" (usually 192.168.x.x or 10.x.x.x)

2. **Example webhook URLs:**

   - **If PHP server is on same computer as SMS Gateway:**
     ```
     http://localhost/bakery/backend/api/sms/sms_webhook.php
     ```
   
   - **If PHP server is on different computer (e.g., IP: 192.168.1.50):**
     ```
     http://192.168.1.50/bakery/backend/api/sms/sms_webhook.php
     ```
   
   - **If PHP server is on different computer (e.g., IP: 10.54.202.176):**
     ```
     http://10.54.202.176/bakery/backend/api/sms/sms_webhook.php
     ```

3. **Where to configure in SMS Gateway:**
   - Open your SMS Gateway software (SMSGate/SMSForwarder)
   - Look for "Webhook", "HTTP Callback", "Incoming SMS URL", or "Forward URL" settings
   - Enter the webhook URL above
   - Make sure to enable webhook/callback functionality

---

## 🔧 Quick Setup Steps

### Step 1: Configure SMS Gateway URL in PHP

1. Open `backend/config/sms.php`
2. Find `gateway_url`
3. Change it to your SMS Gateway address:
   ```php
   'gateway_url' => 'http://YOUR_GATEWAY_IP:8080',
   ```

### Step 2: Configure Webhook in SMS Gateway Software

1. Find your PHP server's IP address (run `ipconfig` on Windows)
2. Open your SMS Gateway software
3. Find webhook/callback settings
4. Enter: `http://YOUR_PHP_SERVER_IP/bakery/backend/api/sms/sms_webhook.php`
5. Save settings

### Step 3: Test the Connection

**Test sending SMS (PHP → Gateway):**
- Try sending an SMS from the admin panel
- Check SMS Gateway logs to see if message was received

**Test receiving SMS (Gateway → PHP):**
- Send an SMS to your receive number (`+639493380766`)
- Check `backend/logs/sms_log.txt` for incoming messages
- Check admin panel → Messages → SMS Messages

---

## 🔍 Troubleshooting

### Issue: SMS sending fails

**Check:**
1. Is SMS Gateway software running?
2. Is `gateway_url` correct in `backend/config/sms.php`?
3. Can you access `http://YOUR_GATEWAY_IP:8080` from a browser?
4. Check firewall settings (port 8080 should be open)

### Issue: Incoming SMS not appearing

**Check:**
1. Is webhook URL correctly configured in SMS Gateway?
2. Can SMS Gateway reach your PHP server? (test by accessing the webhook URL in browser)
3. Check `backend/logs/sms_log.txt` for error messages
4. Verify `receive_number` in `backend/config/sms.php` matches the number you're sending to

### Issue: Connection refused

**Solutions:**
- If using `localhost` and it doesn't work, try `127.0.0.1`
- If using local IP, make sure both devices are on same network
- Check if port 8080 is correct (may vary by SMS Gateway software)
- Disable firewall temporarily to test

---

## 📝 Current Configuration Summary

Based on your `backend/config/sms.php`:

- **SMS Gateway URL:** `http://10.54.202.176:8080` (where PHP sends SMS)
- **Webhook URL (configure in SMS Gateway):** `http://YOUR_PHP_SERVER_IP/bakery/backend/api/sms/sms_webhook.php`
- **Receive Number:** `+639493380766`

**Note:** Replace `YOUR_PHP_SERVER_IP` with your actual PHP server's IP address (find it with `ipconfig`).
