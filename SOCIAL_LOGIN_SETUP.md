# Social Login Setup Guide - Step by Step

This guide will walk you through setting up Google and Facebook OAuth login for your bakery website.

---

## Part 1: Setting Up Google OAuth Login

### Step 1: Create a Google Cloud Project

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Click on the project dropdown at the top
3. Click **"NEW PROJECT"**
4. Enter project name: `Bakery Website` (or any name you prefer)
5. Click **"CREATE"**
6. Wait for the project to be created, then select it from the dropdown
    
### Step 2: Enable Google+ API

1. In the left sidebar, click **"APIs & Services"** > **"Library"**
2. Search for **"Google+ API"** (or "People API")
3. Click on **"Google+ API"**
4. Click the **"ENABLE"** button
5. Wait for it to enable (usually takes a few seconds)

### Step 3: Configure OAuth Consent Screen

1. In the left sidebar, click **"APIs & Services"** > **"OAuth consent screen"**
2. Select **"External"** user type (unless you have Google Workspace)
3. Click **"CREATE"**
4. Fill in the required information:
   - **App name**: `KARNIEK Bakery` (or your bakery name)
   - **User support email**: Your email address
   - **Developer contact information**: Your email address
5. Click **"SAVE AND CONTINUE"**
6. On **Scopes** page, click **"SAVE AND CONTINUE"** (we'll add scopes later if needed)
7. On **Test users** page, click **"SAVE AND CONTINUE"** (only needed for testing)
8. On **Summary** page, review and click **"BACK TO DASHBOARD"**

### Step 4: Create OAuth 2.0 Credentials

1. In the left sidebar, click **"APIs & Services"** > **"Credentials"**
2. Click **"+ CREATE CREDENTIALS"** at the top
3. Select **"OAuth client ID"**
4. If prompted, choose **"Web application"** as the application type
5. Fill in the form:
   - **Name**: `Bakery Website Login`
   - **Authorized JavaScript origins**:
     - Add: `http://localhost` (for local testing)
     - Add: `https://yourdomain.com` (replace with your actual domain)
   - **Authorized redirect URIs**:
     - Add: `http://localhost/backend/api/social-login.php` (for local testing)
     - Add: `https://yourdomain.com/backend/api/social-login.php` (replace with your actual domain)
6. Click **"CREATE"**
7. **IMPORTANT**: A popup will appear with your credentials:
   - **Client ID**: Copy this (looks like: `123456789-abcdefghijklmnop.apps.googleusercontent.com`)
   - **Client secret**: Copy this (looks like: `GOCSPX-abcdefghijklmnopqrstuvwxyz`)
8. **SAVE THESE CREDENTIALS** - you'll need them in the next part

---

## Part 2: Setting Up Facebook OAuth Login

### Step 1: Create a Facebook App

1. Go to [Facebook Developers](https://developers.facebook.com/)
2. Click **"My Apps"** in the top right
3. Click **"Create App"**
4. Select **"Consumer"** as the app type
5. Click **"Next"**
6. Fill in the form:
   - **App name**: `KARNIEK Bakery` (or your bakery name)
   - **App contact email**: Your email address
7. Click **"Create App"**
8. Complete any security checks (like entering your password)

### Step 2: Add Facebook Login Product

1. In your app dashboard, you'll see **"Add Products to Your App"**
2. Find **"Facebook Login"** in the list
3. Click **"Set Up"** button next to it
4. You'll be taken to the Facebook Login settings

### Step 3: Configure Facebook Login Settings

1. In the left sidebar, click **"Settings"** under **"Facebook Login"**
2. Scroll down to **"Valid OAuth Redirect URIs"**
3. Click **"+ Add URI"** and add:
   - `http://localhost/backend/api/social-login.php` (for local testing)
   - `https://yourdomain.com/backend/api/social-login.php` (replace with your actual domain)
4. Scroll down and click **"Save Changes"**

### Step 4: Get Your App Credentials

1. In the left sidebar, click **"Settings"** > **"Basic"**
2. Find your **App ID** (looks like: `1234567890123456`)
3. Find your **App Secret**:
   - Click **"Show"** next to App Secret
   - Enter your Facebook password if prompted
   - Copy the App Secret (looks like: `abcdef1234567890abcdef1234567890`)
4. **SAVE THESE CREDENTIALS** - you'll need them in the next part

### Step 5: Configure App Domain (Important)

1. Still in **"Settings"** > **"Basic"**
2. Scroll down to **"App Domains"**
3. Add your domain (e.g., `yourdomain.com` - without http/https/www)
4. Click **"Save Changes"**

### Step 6: Set App Privacy Policy URL (Optional but Recommended)

1. In **"Settings"** > **"Basic"**
2. Scroll to **"Privacy Policy URL"**
3. Add your privacy policy URL (if you have one)
4. This is required for making your app public later

---

## Part 3: Configuring Your Website

### Step 1: Create Configuration File

1. Navigate to your project folder: `backend/config/`
2. Copy the example file:
   - Copy `social-login.example.php`
   - Rename the copy to `social-login.php`
   - **IMPORTANT**: Never commit this file to version control (add to .gitignore)

### Step 2: Fill in Your Credentials

1. Open `backend/config/social-login.php` in a text editor
2. Replace the placeholder values:

```php
<?php
// Google OAuth Configuration
define('GOOGLE_CLIENT_ID', 'PASTE_YOUR_GOOGLE_CLIENT_ID_HERE');
define('GOOGLE_CLIENT_SECRET', 'PASTE_YOUR_GOOGLE_CLIENT_SECRET_HERE');

// Facebook OAuth Configuration
define('FACEBOOK_APP_ID', 'PASTE_YOUR_FACEBOOK_APP_ID_HERE');
define('FACEBOOK_APP_SECRET', 'PASTE_YOUR_FACEBOOK_APP_SECRET_HERE');
```

3. Replace:
   - `PASTE_YOUR_GOOGLE_CLIENT_ID_HERE` with your Google Client ID from Part 1, Step 4
   - `PASTE_YOUR_GOOGLE_CLIENT_SECRET_HERE` with your Google Client Secret from Part 1, Step 4
   - `PASTE_YOUR_FACEBOOK_APP_ID_HERE` with your Facebook App ID from Part 2, Step 4
   - `PASTE_YOUR_FACEBOOK_APP_SECRET_HERE` with your Facebook App Secret from Part 2, Step 4

4. Save the file

### Step 3: Update Redirect URIs for Production

**IMPORTANT**: Replace `yourdomain.com` with your actual domain in:

1. **Google Cloud Console**:
   - Go back to **"Credentials"**
   - Edit your OAuth client
   - Update **Authorized JavaScript origins** and **Authorized redirect URIs** with your real domain

2. **Facebook Developers**:
   - Go to **"Facebook Login"** > **"Settings"**
   - Update **Valid OAuth Redirect URIs** with your real domain

---

## Part 4: Testing Social Login

### Step 1: Test on Localhost (Development)

1. Make sure your local server is running
2. Go to `http://localhost/backend/login.php`
3. Click **"Continue with Google"** or **"Continue with Facebook"**
4. You should be redirected to Google/Facebook login page
5. Sign in with your account
6. You should be redirected back and logged in

### Step 2: Test on Production

1. Deploy your code to your production server
2. Make sure `backend/config/social-login.php` is uploaded (but NOT in public repositories)
3. Visit `https://yourdomain.com/backend/login.php`
4. Test both Google and Facebook login

---

## Part 5: Troubleshooting

### Issue: "Invalid Redirect URI"

**Solution**:
- Check that the redirect URI in your OAuth app settings **exactly matches** the URL in `social-login.php`
- Make sure there are no trailing slashes
- Verify HTTP vs HTTPS matches

### Issue: "App Not Setup" (Facebook)

**Solution**:
- Make sure you've added "Facebook Login" product to your app
- Check that App Domain is set correctly
- Verify redirect URIs are added

### Issue: "Access Blocked" (Google)

**Solution**:
- Make sure OAuth consent screen is properly configured
- If testing with a Google account that's not yours, add it as a test user
- Check that Google+ API is enabled

### Issue: "Credentials Not Working"

**Solution**:
- Double-check that you copied the credentials correctly (no extra spaces)
- Make sure `social-login.php` file exists in `backend/config/` folder
- Verify the file has correct PHP syntax (no typos)

### Issue: "CURL Error" or "Network Error"

**Solution**:
- Make sure your server has cURL enabled (most do)
- Check that your server can make outbound HTTPS connections
- Verify firewall settings

---

## Part 6: Making Facebook App Public (When Ready)

**Note**: During development, only you and test users can use Facebook login. To make it public:

1. Go to [Facebook Developers](https://developers.facebook.com/)
2. Select your app
3. Go to **"App Review"** > **"Permissions and Features"**
4. Request approval for `email` and `public_profile` permissions
5. Facebook will review your app (usually takes a few days)
6. Once approved, anyone can use Facebook login

---

## Security Best Practices

1. **Never commit `social-login.php` to version control**
   - Add it to `.gitignore`
   - Keep credentials secret

2. **Use Environment Variables** (Alternative method):
   ```php
   // Instead of define(), you can use:
   $clientId = getenv('GOOGLE_CLIENT_ID');
   ```
   Then set environment variables on your server

3. **Use HTTPS in Production**:
   - OAuth requires HTTPS for security
   - Never use HTTP in production

4. **Regularly Rotate Secrets**:
   - Change your OAuth secrets periodically
   - If compromised, regenerate immediately

---

## Quick Checklist

- [ ] Google Cloud project created
- [ ] Google+ API enabled
- [ ] OAuth consent screen configured
- [ ] Google OAuth credentials created
- [ ] Google Client ID and Secret saved
- [ ] Facebook app created
- [ ] Facebook Login product added
- [ ] Facebook redirect URIs configured
- [ ] Facebook App ID and Secret saved
- [ ] `social-login.php` file created with credentials
- [ ] Tested on localhost
- [ ] Tested on production

---

## Support

If you encounter issues:
1. Check the troubleshooting section above
2. Review error messages in browser console (F12)
3. Check server error logs
4. Verify all credentials are correct
5. Ensure redirect URIs match exactly

---

**Congratulations!** You've successfully set up social login for your bakery website! 🎉

