# NetaTrack India — Deployment Guide

## 🚀 Shared Hosting (Hostinger / cPanel / GoDaddy)

### Step 1 — Upload Files

Upload the **contents of `php/public/`** directly into `public_html/`:

```
public_html/
  index.php          ← from php/public/index.php
  .htaccess          ← from php/public/.htaccess
  install.php        ← from php/public/install.php
  assets/
    css/app.css
    css/admin.css
    css/installer.css
    js/app.js
  logo.png
```

Upload the **rest of the app** (one level above `public_html/`):
```
home/
  youruser/
    netatrack/         ← upload php/ folder contents here
      bootstrap.php
      composer.json
      routes/
      src/
      views/
      database/
    public_html/       ← only php/public/ contents go here
```

### Step 2 — Edit `public_html/index.php`

Change the ROOT_PATH line to match your server path:

```php
define('ROOT_PATH', '/home/yourusername/netatrack');
```

### Step 3 — Run Installer

Visit: `https://yoursite.com/install.php`

Fill in:
- **DB Host**: `localhost` (shared hosting)
- **DB Name**: e.g. `u123456789_netatrack`
- **DB User**: e.g. `u123456789_admin`
- **DB Pass**: your DB password

### Step 4 — Copy API Token

After install, copy the API token shown → paste into Python admin app Settings tab.

### Step 5 — Delete installer

```bash
rm public_html/install.php
```

---

## ⚠️ Common Issues

| Problem | Fix |
|---------|-----|
| 404 on homepage | Make sure `index.php` + `.htaccess` are in `public_html/` |
| 500 error | Check `ROOT_PATH` in `index.php` matches your server path |
| DB connection refused | Use `localhost` not `127.0.0.1` on shared hosting |
| Assets not loading | Upload `assets/` folder inside `public_html/` |
| `.htaccess` not working | Enable mod_rewrite in cPanel or contact host |
