# SilkSerenity Project Structure

## Organized Folder Structure

```
SilkSerenity/
├── config/                 # Configuration files
│   ├── connection.php      # Database connection
│   └── email_utils.php     # Email configuration
│
├── views/                  # HTML view files
│   ├── home.html
│   ├── login.html
│   ├── signup.html
│   ├── account.html
│   ├── gallery.html
│   └── admin_login.html
│
├── pages/                  # PHP pages that render content
│   ├── services.php
│   ├── appointments.php
│   ├── reviews.php
│   ├── contact.php
│   └── write.php
│
├── api/                    # API endpoints (JSON responses)
│   ├── book_appointment.php
│   ├── submit_review.php
│   ├── update_status.php
│   ├── check_email.php
│   ├── get_userdata.php
│   ├── get_account_data.php
│   ├── get_appointments.php
│   ├── get_user_appointments.php
│   ├── get_admin_appointments.php
│   ├── get_booked_slots.php
│   └── get_dashboard_data.php
│
├── admin/                  # Admin panel files
│   ├── admin_dashboard.php
│   ├── admin_users.php
│   ├── admin_services.php
│   ├── admin_transactions.php
│   ├── admin_analytics.php
│   ├── admin_manage.php
│   └── admin_login.php
│
├── auth/                   # Authentication files
│   ├── authentication.php
│   ├── admin_auth.php
│   ├── register.php
│   ├── logout.php
│   └── admin_logout.php
│
├── assets/                 # Static assets
│   ├── css/
│   │   ├── styles.css
│   │   ├── admin_styles.css
│   │   └── mediaqueries.css
│   ├── js/
│   │   ├── script.js
│   │   ├── appointments.js
│   │   ├── admin_login.js
│   │   └── admin_dashboard.js
│   └── images/
│       ├── *.svg (all SVG files)
│       └── (model images, icons, etc.)
│
├── includes/               # Utility/helper files
│   └── populate_services.php
│
├── vendor/                 # Composer dependencies
│   └── phpmailer/
│
├── composer.json
├── composer.lock
└── README.md
```

## Path Reference Guide

### From Root Directory:
- Config files: `config/connection.php`
- Views: `views/home.html`
- Pages: `pages/services.php`
- API: `api/book_appointment.php`
- Admin: `admin/admin_dashboard.php`
- Auth: `auth/authentication.php`
- Assets: `assets/css/styles.css`, `assets/js/script.js`, `assets/images/*.svg`

### From `pages/` directory:
- Config: `../config/connection.php`
- Views: `../views/home.html`
- Assets: `../assets/css/styles.css`
- API: `../api/book_appointment.php`

### From `api/` directory:
- Config: `../config/connection.php`
- Email utils: `../config/email_utils.php`

### From `admin/` directory:
- Config: `../config/connection.php`
- Views: `../views/admin_login.html`
- API: `../api/update_status.php`
- Assets: `../assets/css/admin_styles.css`

### From `auth/` directory:
- Config: `../config/connection.php`

### From `views/` directory:
- Assets: `../assets/css/styles.css`
- Pages: `../pages/services.php`
- API: `../api/book_appointment.php`

## Notes

- All file paths have been updated to reflect the new folder structure
- CSS and JavaScript files are now in `assets/css/` and `assets/js/` respectively
- All images/SVG files are in `assets/images/`
- API endpoints are separated in the `api/` folder
- Admin files are organized in the `admin/` folder
- Authentication files are in the `auth/` folder
- Configuration files are centralized in `config/`
