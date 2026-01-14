# Fix: Password Reset Link Unreachable

## Problem
The password reset link sent via email is unreachable, especially on mobile devices.

## Solution Steps

### 1. Verify IP Address Configuration
- Your IP address is: **192.168.18.115**
- This is already configured in `backend/config/email.php`
- The reset link format should be: `http://192.168.18.115/bakery/backend/reset-password.php?token=...`

### 2. Check Windows Firewall
The most common issue is Windows Firewall blocking port 80.

**To fix:**
1. Open **Windows Defender Firewall** (search in Start menu)
2. Click **Advanced settings**
3. Click **Inbound Rules** → **New Rule**
4. Select **Port** → **Next**
5. Select **TCP** and enter port **80** → **Next**
6. Select **Allow the connection** → **Next**
7. Check all profiles (Domain, Private, Public) → **Next**
8. Name it "XAMPP Apache HTTP" → **Finish**

**Or use Command Prompt (Run as Administrator):**
```cmd
netsh advfirewall firewall add rule name="XAMPP Apache HTTP" dir=in action=allow protocol=TCP localport=80
```

### 3. Verify XAMPP Apache Configuration
- Apache should be listening on **0.0.0.0:80** (all interfaces)
- Check with: `netstat -an | findstr ":80"`
- Should see: `TCP    0.0.0.0:80             0.0.0.0:0              LISTENING`

### 4. Test the Link
1. **From your computer:**
   - Open: `http://192.168.18.115/bakery/backend/reset-password.php`
   - Should load the reset password page

2. **From mobile device (same WiFi):**
   - Open browser
   - Go to: `http://192.168.18.115/bakery/backend/reset-password.php`
   - If it doesn't work, check:
     - Both devices on same WiFi network
     - Windows Firewall allows port 80
     - XAMPP Apache is running

3. **Use the test page:**
   - Open: `http://192.168.18.115/bakery/backend/test-reset-link.php`
   - This shows the exact link format and configuration

### 5. Verify Reset Link Format
The reset link should be exactly:
```
http://192.168.18.115/bakery/backend/reset-password.php?token=YOUR_TOKEN_HERE
```

**NOT:**
- ❌ `http://localhost/bakery/backend/reset-password.php` (won't work on mobile)
- ❌ `http://192.168.18.115/backend/reset-password.php` (missing /bakery)
- ❌ `http://192.168.18.115/bakery/reset-password.php` (missing /backend)

### 6. Common Issues & Fixes

| Issue | Solution |
|-------|----------|
| "This site can't be reached" | Check Windows Firewall, allow port 80 |
| "Connection timeout" | Verify XAMPP Apache is running |
| Wrong page loads | Check the path includes `/bakery/backend/` |
| Works on computer but not mobile | Ensure both on same WiFi, check firewall |
| IP address changed | Update `base_url` in `backend/config/email.php` |

### 7. Update IP Address (if changed)
If your IP address changes, update it in:
- File: `backend/config/email.php`
- Line 19: `'base_url' => 'http://YOUR_NEW_IP/bakery',`
- Find your IP: Run `ipconfig` in CMD and look for IPv4 Address

### 8. Test Email Link
1. Request password reset: `http://localhost/bakery/backend/forgot-password.php`
2. Check email for reset link
3. The link should start with: `http://192.168.18.115/bakery/backend/reset-password.php?token=...`
4. Click the link on mobile device
5. Should redirect to reset password page

## Debugging Tools

### Test Page
Access: `http://192.168.18.115/bakery/backend/test-reset-link.php`
- Shows current configuration
- Displays exact link format
- Provides testing instructions

### Check Logs
- PHP errors: `backend/logs/php_errors.log`
- Look for: "Password Reset Link Generated"
- Verify the link uses IP address, not localhost

## Still Not Working?

1. **Verify Apache is accessible:**
   ```cmd
   curl http://192.168.18.115/bakery/backend/test-reset-link.php
   ```

2. **Check if port 80 is blocked:**
   ```cmd
   telnet 192.168.18.115 80
   ```

3. **Test from mobile browser:**
   - Try accessing: `http://192.168.18.115/bakery/`
   - If this works, the reset link should also work

4. **Check router settings:**
   - Some routers block device-to-device communication
   - Enable "AP Isolation" or "Client Isolation" if needed
