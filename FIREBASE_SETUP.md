# Firebase Authentication Setup Guide

## Quick Setup (3 minutes)

### Step 1: Create Firebase Project
1. Go to [Firebase Console](https://console.firebase.google.com/)
2. Click "Add project" 
3. Enter project name: `bakery-website`
4. Click "Continue" and "Create project"

### Step 2: Enable Google Authentication
1. In your project, go to **Authentication** → **Sign-in method**
2. Enable **Google** sign-in
3. Leave all settings as default for testing
4. Click **Save**

### Step 3: Get Your Firebase Config
1. Go to **Project Settings** (gear icon)
2. Scroll down to "Firebase SDK snippet"
3. Copy the config object
4. Replace placeholder config in `frontend/js/firebase-auth.js`

### Step 4: Update Firebase Config
Edit `frontend/js/firebase-auth.js` and replace:

```javascript
const firebaseConfig = {
    apiKey: "YOUR_API_KEY",           // ← Replace this
    authDomain: "your-project.firebaseapp.com",  // ← Replace this  
    projectId: "your-project-id",      // ← Replace this
    storageBucket: "your-project.appspot.com",  // ← Replace this
    messagingSenderId: "123456789",   // ← Replace this
    appId: "1:123456789:web:abcdef123456"  // ← Replace this
};
```

### Step 5: Test It!
1. Open `backend/login.php` in your browser
2. Click "Continue with Google"
3. It should work immediately!

## Benefits of Firebase vs OAuth

✅ **No redirect URI configuration needed**
✅ **No OAuth console setup**  
✅ **Works in 3 minutes**
✅ **Free tier available**
✅ **Handles all security for you**
✅ **Google only - much simpler**

## That's It!

Your Google login should now work without any complex OAuth setup. Firebase handles everything for you!

---

**Note**: Facebook login has been removed to keep things simple. You can always add it later if needed.
