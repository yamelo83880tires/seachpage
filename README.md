# 🎬 Movie Search Admin Panel

A complete admin panel system for managing movie search keywords with PHP, MySQL, and modern responsive design.

## 🚀 Features

### 🔐 Security
- **Secure Login System** with PHP sessions
- **Password hashing** using PHP's `password_hash()`
- **CSRF protection** on all forms
- **SQL injection protection** with prepared statements
- **Session management** with proper cleanup

### 📊 Dashboard
- **Real-time statistics** with auto-refresh
- **Interactive dashboard** with keyword management
- **Search and filter** functionality
- **Responsive design** for all devices

### 🔧 Keyword Management
- **Add new keywords** with validation
- **Edit existing keywords** with preview
- **Delete keywords** with confirmation
- **Status management** (Active/Inactive)
- **Bulk operations** support

### 🎨 Modern UI/UX
- **Bootstrap-style design** (custom CSS)
- **Mobile-responsive** layout
- **Interactive elements** with smooth animations
- **Toast notifications** and alerts
- **Clean, professional** interface

## 📁 File Structure

```
/
├── index.html              # Original static page
├── index.php               # Dynamic page with database keywords
├── admin/
│   ├── config.php          # Database configuration
│   ├── login.php           # Admin login page
│   ├── dashboard.php       # Main admin dashboard
│   ├── add_keyword.php     # Add new keyword
│   ├── edit_keyword.php    # Edit existing keyword
│   ├── delete_keyword.php  # Delete keyword (with confirmation)
│   ├── logout.php          # Secure logout
│   ├── keywords.sql        # Database structure & sample data
│   ├── style.css           # Admin panel styles
│   └── scripts.js          # JavaScript functionality
└── README.md               # This file
```

## 🛠️ Installation & Setup

### Prerequisites
- **PHP 7.4+** with PDO MySQL extension
- **MySQL 5.7+** or MariaDB
- **Web server** (Apache/Nginx)

### Step 1: Database Setup
1. Create a new MySQL database:
   ```sql
   CREATE DATABASE admin_panel;
   ```

2. Import the database structure:
   ```bash
   mysql -u your_username -p admin_panel < admin/keywords.sql
   ```

### Step 2: Configuration
1. Edit `admin/config.php` and update database credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'admin_panel');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   ```

2. **IMPORTANT**: Change default admin credentials in `config.php`:
   ```php
   define('ADMIN_USERNAME', 'your_admin_username');
   define('ADMIN_PASSWORD_HASH', password_hash('your_secure_password', PASSWORD_DEFAULT));
   ```

### Step 3: Upload Files
1. Upload all files to your web server
2. Ensure proper file permissions (755 for directories, 644 for files)
3. Make sure PHP has read/write access to the files

### Step 4: Access the Admin Panel
1. Visit: `https://yourdomain.com/admin/login.php`
2. Login with your admin credentials
3. Start managing keywords!

## 🔑 Default Login

**⚠️ CHANGE THESE IMMEDIATELY AFTER INSTALLATION:**
- **Username:** `admin`
- **Password:** `admin123`

## 📖 Usage Guide

### Adding Keywords
1. Go to **Dashboard** → **Add Keyword**
2. Enter keyword text (e.g., "bollywood movies")
3. Add optional description for reference
4. Set status (Active/Inactive)
5. Click **Add Keyword**

### Managing Keywords
1. View all keywords in the **Dashboard**
2. Use the **search box** to filter keywords
3. Click **Edit** to modify existing keywords
4. Click **Delete** to remove keywords (with confirmation)
5. Copy keywords to clipboard using **Copy** button

### Viewing Statistics
- **Total Keywords:** All keywords in database
- **Active Keywords:** Keywords visible on the site
- **Inactive Keywords:** Hidden keywords
- **Auto-refresh** every 30 seconds

## 🔧 Configuration Options

### Database Settings
Edit `admin/config.php`:
```php
define('DB_HOST', 'localhost');     // Database host
define('DB_NAME', 'admin_panel');   // Database name
define('DB_USER', 'username');      // Database username
define('DB_PASS', 'password');      // Database password
```

### Admin Credentials
```php
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD_HASH', password_hash('your_password', PASSWORD_DEFAULT));
```

### Session Security
Sessions are automatically configured with secure settings:
- CSRF protection on all forms
- Session regeneration on login
- Secure session cleanup on logout

## 🎨 Customization

### Styling
- Edit `admin/style.css` to customize the admin panel appearance
- All styles are custom CSS (no external frameworks)
- Fully responsive design included

### Functionality
- Modify `admin/scripts.js` for additional JavaScript features
- Add new admin pages by following the existing structure
- Extend database schema in `admin/keywords.sql`

## 🔒 Security Features

### Authentication
- **Password hashing** with PHP's `password_hash()`
- **Session-based** authentication
- **Automatic logout** after inactivity
- **Login attempt** protection

### Data Protection
- **SQL injection** prevention with prepared statements
- **XSS protection** with `htmlspecialchars()`
- **CSRF tokens** on all forms
- **Input validation** and sanitization

### Session Management
- **Secure session** configuration
- **Session regeneration** on login
- **Proper cleanup** on logout
- **Session timeout** handling

## 🌐 Dynamic Frontend

The `index.php` file dynamically displays keywords from the database:
- Randomly selects an **active keyword** from the database
- Falls back to default keyword if database is unavailable
- Updates automatically when keywords are modified in admin panel

## 📱 Mobile Responsive

The admin panel is fully responsive and works on:
- **Desktop** computers
- **Tablets** (iPad, Android tablets)
- **Mobile phones** (iPhone, Android)
- **All screen sizes** with adaptive layout

## 🆘 Troubleshooting

### Database Connection Issues
1. Check database credentials in `config.php`
2. Ensure MySQL service is running
3. Verify database and table exist
4. Check PHP PDO MySQL extension is installed

### Login Issues
1. Verify admin credentials in `config.php`
2. Check session configuration
3. Clear browser cookies/cache
4. Ensure proper file permissions

### Keyword Display Issues
1. Check if database contains active keywords
2. Verify database connection
3. Check for PHP errors in server logs
4. Ensure proper SQL syntax

## 🔄 Updates & Maintenance

### Regular Tasks
1. **Backup database** regularly
2. **Monitor logs** for errors
3. **Update passwords** periodically
4. **Review keywords** for relevance

### Security Updates
1. Keep **PHP updated** to latest version
2. **Monitor** for security vulnerabilities
3. **Review access logs** regularly
4. **Change default** credentials immediately

## 🤝 Support

### Common Issues
- Database connection problems
- Login/logout issues
- Keyword management
- Mobile display issues

### Best Practices
1. **Regular backups** of database
2. **Strong passwords** for admin access
3. **Monitor** keyword performance
4. **Test** on different devices

## 📄 License

This project is designed for educational and commercial use. Feel free to modify and distribute according to your needs.

---

**Made with ❤️ for easy movie keyword management**

🎬 **Happy keyword managing!** 🎬