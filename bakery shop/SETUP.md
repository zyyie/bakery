# Setup Instructions

## 1. Database Setup

**Gawin ang mga sumusunod:**

1. Buksan ang phpMyAdmin o MySQL command line
2. I-import ang `database.sql` file
   - Ang file na ito ay mag-c-create ng database na `DB_bakery`
   - At lahat ng tables na kailangan

**O kung manual:**
```sql
CREATE DATABASE DB_bakery;
USE DB_bakery;
```
Tapos i-import ang contents ng `database.sql`

## 2. Check MySQL Server

Siguraduhin na naka-on ang MySQL server:
- XAMPP: Start ang MySQL sa Control Panel
- WAMP: Start ang MySQL service
- MAMP: Start ang MySQL

## 3. Restart Web Server

Pagkatapos mag-import ng database:
- I-restart ang Apache/PHP server
- O i-refresh ang browser (Ctrl+F5 para clear cache)

## 4. Verify Database Name

Tingnan ang `connect.php` - dapat `DB_bakery` ang database name:
```php
$db = "DB_bakery";
```

## Troubleshooting

Kung may error pa rin:
1. Check kung naka-on ang MySQL
2. Check kung tama ang database name sa `connect.php`
3. I-restart ang web server
4. Clear browser cache

