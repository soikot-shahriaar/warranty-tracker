# 🛡️ Warranty Tracker CMS

A comprehensive Content Management System for tracking product warranties, built with modern web technologies. Keep track of your product warranties, receive expiry alerts, and never miss an important warranty deadline again.

## 🚀 Technologies Used

### Backend Technologies
- **PHP 7.4+** - Server-side scripting language
- **MySQL 5.7+ / MariaDB 10.3+** - Relational database management
- **PDO** - Database abstraction layer for secure operations
- **Apache/Nginx** - Web server support

### Frontend Technologies
- **HTML5** - Semantic markup structure
- **CSS3** - Modern styling with Flexbox and Grid
- **JavaScript (Vanilla)** - Interactive functionality
- **Responsive Design** - Mobile-first approach

### Security & Performance
- **Password Hashing** - Bcrypt algorithm for secure authentication
- **CSRF Protection** - Cross-site request forgery prevention
- **Input Sanitization** - XSS and injection attack prevention
- **File Upload Security** - Type and size validation

## 📋 Project Overview

Warranty Tracker CMS is a web-based application designed to help individuals and businesses manage their product warranties efficiently. The system provides a centralized platform for storing warranty information, tracking expiration dates, and receiving timely alerts about upcoming warranty expirations.

### Core Purpose
- **Centralized Management**: Store all warranty information in one place
- **Expiry Tracking**: Never miss a warranty expiration date
- **Document Storage**: Keep receipts and product images organized
- **User Management**: Secure access for multiple users
- **Reporting**: Generate warranty status reports and analytics

## ✨ Key Features

### 🔐 Authentication & Security
- **User Registration & Login**: Secure account creation and authentication
- **Password Management**: Strong password requirements with secure hashing
- **Session Management**: Secure session handling with automatic logout
- **CSRF Protection**: Form submission security

### 📊 Warranty Management
- **CRUD Operations**: Create, Read, Update, Delete warranty records
- **Auto-calculation**: Automatic warranty expiry date calculation
- **File Attachments**: Upload receipts, product images, and documents
- **Search & Filter**: Advanced search and filtering capabilities
- **Bulk Operations**: Manage multiple warranties efficiently

### 🎯 Dashboard & Analytics
- **Overview Dashboard**: Quick statistics and warranty status
- **Visual Indicators**: Color-coded status for active, expiring, and expired warranties
- **Expiry Alerts**: Proactive notifications for upcoming expirations
- **Statistics**: Comprehensive warranty analytics and reporting

### 📱 User Experience
- **Responsive Design**: Works seamlessly on all devices
- **Modern Interface**: Clean, intuitive user interface
- **Accessibility**: WCAG compliant design principles
- **Performance**: Fast loading and smooth interactions

## 👥 User Roles

### 🔑 Administrator
- **Full Access**: Complete control over the system
- **User Management**: Create, edit, and delete user accounts
- **System Configuration**: Modify system settings and preferences
- **Database Management**: Access to all warranty records

### 👤 Regular User
- **Personal Warranties**: Manage their own warranty records
- **Profile Management**: Update personal information and settings
- **File Uploads**: Upload and manage warranty documents
- **Dashboard Access**: View personal warranty statistics

### 📊 Guest User (Future Implementation)
- **Limited Access**: View-only access to public warranty information
- **Search Capabilities**: Search through public warranty database
- **No Modifications**: Cannot create or edit warranty records

## 🏗️ Project Structure

```
warranty-tracker/
├── 📁 assets/                 # Static assets and user uploads
│   ├── 📁 css/
│   │   └── style.css         # Main stylesheet with responsive design
│   ├── 📁 images/            # Static images and icons
│   ├── 📁 js/                # JavaScript functionality
│   └── 📁 uploads/           # User uploaded files (receipts, images)
├── 📁 config/                 # Configuration files
│   └── database.php          # Database connection settings
├── 📁 includes/               # Reusable components and functions
│   ├── functions.php         # Common utility functions
│   ├── header.php            # Page header template
│   └── footer.php            # Page footer template
├── 📁 pages/                  # Main application pages
│   ├── dashboard.php         # Main dashboard with statistics
│   ├── login.php             # User authentication
│   ├── register.php          # User registration
│   ├── profile.php           # User profile management
│   ├── warranties.php        # Warranty listing and search
│   ├── warranty-add.php      # Add new warranty form
│   ├── warranty-edit.php     # Edit existing warranty
│   ├── warranty-view.php     # Detailed warranty view
│   ├── warranty-delete.php   # Warranty deletion handler
│   └── logout.php            # User logout handler
├── 📁 sql/                    # Database schema and setup
│   └── setup.sql             # Complete database structure
├── 📄 index.php               # Application entry point
├── 📄 .htaccess              # Apache configuration
└── 📄 README.md              # Project documentation
```

## 🚀 Setup Instructions

### Prerequisites
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **PHP**: Version 7.4 or higher
- **Database**: MySQL 5.7+ or MariaDB 10.3+
- **Extensions**: PDO, GD, FileInfo, OpenSSL

### Step 1: Download & Extract
```bash
# Clone the repository
git clone https://github.com/soikot-shahriaar/warranty-tracker
cd warranty-tracker

# Or download and extract ZIP file
# Extract to your web server directory
```

### Step 2: Database Setup
1. **Create Database**:
```sql
CREATE DATABASE warranty_tracker;
```

2. **Import Schema**:
```bash
mysql -u username -p warranty_tracker < sql/setup.sql
```

### Step 3: Configuration
1. **Database Settings**:
```php
// Edit config/database.php
define('DB_HOST', 'localhost');
define('DB_NAME', 'warranty_tracker');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

2. **File Permissions**:
```bash
chmod 755 assets/uploads/
chmod 644 config/database.php
```

### Step 4: Web Server Configuration

#### Apache Configuration
The `.htaccess` file is already configured with the necessary settings. Make sure `mod_rewrite` is enabled:

```bash
# Enable mod_rewrite (Ubuntu/Debian)
sudo a2enmod rewrite
sudo systemctl restart apache2

# Enable mod_rewrite (CentOS/RHEL)
# Edit /etc/httpd/conf/httpd.conf and ensure AllowOverride is set to All
```

#### Access URL
- **Local Development**: `http://localhost/warranty-tracker` (or your project directory name)
- **Production**: `https://yourdomain.com` (or `https://yourdomain.com/project-name` if in subdirectory)

**Note**: The application automatically detects the base path and handles redirects correctly. All redirects use relative paths to avoid port number issues (e.g., `localhost:8080`). The application will work correctly when accessed via `http://localhost/project_name` without requiring port numbers.

### Step 5: Verification
1. Access your installation URL (e.g., `http://localhost/warranty-tracker`)
2. Verify the application loads correctly
3. Create your first user account
4. Test warranty creation and management

## 🔑 Demo Credentials

After installation, you can use these default demo credentials to test the system:

### Default Admin Account
- **Username**: `admin`
- **Password**: `admin123`
- **Email**: `admin@example.com`

### Demo Data
The system comes pre-loaded with sample warranty data including:
- Laptop Computer (Dell XPS 13)
- Smartphone (Apple iPhone 15)
- Washing Machine (Samsung WF45R6100AP)

**⚠️ Security Note**: Remember to change the default admin password after your first login for production use.

## 📖 Usage Guide

### Getting Started
1. **Access Application**: Navigate to your installation URL (e.g., `http://localhost/warranty-tracker`)
   - The application automatically handles the base path, so it works correctly whether installed in the root directory or a subdirectory
   - All redirects use relative paths to ensure compatibility across different server configurations
2. **Create Account**: Click "Sign up here" on the login page
3. **First Login**: Use your credentials to access the dashboard
4. **Add Warranties**: Start by adding your first product warranty

### Managing Warranties

#### Adding a New Warranty
1. Navigate to "Add New Warranty" from dashboard
2. Fill in required fields:
   - Product name, brand, model
   - Purchase date and warranty period
   - Store/vendor information
   - Upload receipt or product image
3. Click "Add Warranty" to save

#### Viewing & Editing
- **Dashboard**: Overview with recent warranties and alerts
- **All Warranties**: Complete list with search and filters
- **Individual View**: Detailed warranty information
- **Edit Mode**: Modify warranty details and attachments

#### Search & Filter
- **Text Search**: Find by product name, brand, or model
- **Status Filter**: View active, expiring, or expired warranties
- **Date Range**: Filter by purchase or expiry dates
- **Combined Search**: Use multiple criteria for precise results

### Understanding Status Indicators
- 🟢 **Active**: Warranty is valid and active
- 🟡 **Expiring Soon**: Expires within 30 days
- 🔴 **Expired**: Warranty has expired

### Profile Management
- Update personal information
- Change password securely
- View account statistics
- Manage notification preferences

## 🎯 Intended Use

### Personal Use
- **Individual Consumers**: Track personal product warranties
- **Home Management**: Organize household appliance warranties
- **Personal Finance**: Monitor warranty coverage for valuable items

### Business Applications
- **Small Businesses**: Manage equipment and tool warranties
- **IT Departments**: Track software and hardware warranties
- **Facility Management**: Monitor building system warranties
- **Fleet Management**: Track vehicle and equipment warranties

### Educational & Training
- **Learning Resource**: Study modern web development practices
- **Code Examples**: Reference for PHP and MySQL development
- **Security Implementation**: Learn authentication and security best practices

### Development & Customization
- **Base Template**: Starting point for custom warranty applications
- **Feature Extension**: Add advanced features like email notifications
- **API Development**: Integrate with mobile applications
- **Multi-tenant**: Extend for multiple organizations

## 📄 License

**License for RiverTheme**

RiverTheme makes this project available for demo, instructional, and personal use. You can ask for or buy a license from [RiverTheme.com](https://RiverTheme.com) if you want a pro website, sophisticated features, or expert setup and assistance. A Pro license is needed for production deployments, customizations, and commercial use.

**Disclaimer**

The free version is offered "as is" with no warranty and might not function on all devices or browsers. It might also have some coding or security flaws. For additional information or to get a Pro license, please get in touch with [RiverTheme.com](https://RiverTheme.com).

---