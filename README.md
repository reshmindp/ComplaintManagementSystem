Complaints Management System
A comprehensive Laravel-based API system for managing customer complaints with role-based access control, real-time notifications, and complete complaint lifecycle management.

Features
•	Role-Based Access Control: Admin, Technician, and User roles with specific permissions
•	Complete Complaint Lifecycle: Creation, Assignment, Resolution, and Closure
•	File Attachments: Support for multiple file uploads per complaint
•	Real-time Notifications: Email and database notifications for complaint updates
•	Advanced Filtering: Search and filter complaints by status, priority, category, and assignment
•	Dashboard Analytics: Role-specific statistics and metrics
•	API-First Design: RESTful API with comprehensive endpoints
•	Performance Optimized: Query optimization, caching, and N+1 prevention
•	Comprehensive Testing: Feature and unit tests with high coverage

Architecture & Design Choices
Clean Architecture Principles
•	Service Layer: Business logic separated from controllers using dedicated service classes
•	Repository Pattern: Data access abstraction through Eloquent models with optimized relationships
•	Policy-Based Authorization: Fine-grained permissions using Laravel Policies
•	Event-Driven Architecture: Complaint lifecycle events trigger automated notifications

Database Design
•	Normalized Structure: Separate tables for complaints, statuses, assignments, and resolutions
•	Optimized Indexing: Strategic indexes on frequently queried columns
•	Audit Trail: Complete history tracking through assignment and resolution tables
•	Flexible Metadata: JSON fields for extensibility without schema changes

Security & Performance
•	Laravel Sanctum: Token-based API authentication
•	Form Request Validation: Centralized validation with custom rules
•	Query Optimization: Eager loading, select optimization, and caching strategies
•	Rate Limiting: API endpoint protection against abuse
•	File Security: Secure file upload handling with validation

Technology Stack
•	Backend: Laravel 11 with PHP 8.2+
•	Database: MySQL/PostgreSQL with migration-based schema management
•	Authentication: Laravel Sanctum for API tokens
•	Permissions: Spatie Laravel Permission package
•	Queue System: Database-based job queuing for notifications
•	Testing: PHPUnit with feature and unit test coverage

Requirements
•	PHP 8.2 or higher
•	Composer
•	MySQL 8.0+ or PostgreSQL 13+
•	Node.js 16+ (for asset compilation, if needed)

Installation & Setup
1. Clone and Install Dependencies
git clone <repository-url>
cd complaints-management-system
composer install

2. Environment Configuration
cp .env.example .env
php artisan key:generate

3. Database Configuration
Update your .env file with database credentials:
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=complaints_management
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Mail Configuration (for notifications)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_FROM_ADDRESS="noreply@complaints.com"
MAIL_FROM_NAME="${APP_NAME}"

# Queue Configuration
QUEUE_CONNECTION=database

4. Database Setup & Seeding
# Seed with sample data
php artisan db:seed

# Create storage symlink
php artisan storage:link

5. Start the Application
# Start development server
php artisan serve

# Start queue worker (separate terminal)
php artisan queue:work

Test Users & Seeding Information
The system comes pre-seeded with test users for each role:
Admin User
•	Email: admin@example.com
•	Password: password
•	Permissions: Full system access, user management, complaint oversight
Technician Users
•	Email: tech1@example.com / tech2@example.com
•	Password: password
•	Permissions: View all complaints, resolve assigned complaints, update complaint status
Regular Users
•	Count: 10 users created via factory
•	Pattern: user1@example.com, user2@example.com, etc.
•	Password: password
•	Permissions: Create/view own complaints, update unresolved complaints
Sample Data Created
•	Complaint Statuses: Open, In Progress, Pending Customer, Resolved, Closed
•	Roles & Permissions: Complete RBAC setup with granular permissions
•	Sample Complaints: Various priorities and categories for testing
Database Seeding Details
# Individual seeders available:
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=ComplaintStatusSeeder
php artisan db:seed --class=UserSeeder

# Reset and reseed everything:
php artisan migrate:fresh --seed

Testing
Run Test Suite
# Run all tests
php artisan test

# Run specific test classes
php artisan test --filter=ComplaintManagementTest
php artisan test --filter=ComplaintServiceTest

# Run with coverage (requires Xdebug)
php artisan test --coverage

# Run tests with verbose output
php artisan test --verbose

API Documentation
Authentication Endpoints
POST /api/auth/login          # User login
POST /api/auth/register       # User registration
POST /api/auth/logout         # Logout (requires token)
GET  /api/auth/user          # Get authenticated user

Complaint Management
GET    /api/complaints              # List complaints (with filters)
POST   /api/complaints              # Create new complaint
GET    /api/complaints/{id}         # View specific complaint
PUT    /api/complaints/{id}         # Update complaint
DELETE /api/complaints/{id}         # Delete complaint
POST   /api/complaints/{id}/assign  # Assign to technician
POST   /api/complaints/{id}/resolve # Resolve complaint

System Endpoints
GET /api/dashboard/stats        # Role-specific dashboard statistics
GET /api/users/technicians      # List available technicians
GET /api/complaint-statuses     # Available complaint statuses

API Usage Examples
1. Login
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"admin@example.com","password":"password"}'

2. Create Complaint
POST /api/complaints
Headers:
  Authorization: Bearer YOUR_TOKEN
  Content-Type: application/json
  Accept: application/json

Body:
{
    "title": "System Error - Payment Processing",
    "description": "Users are reporting payment processing failures when trying to complete transactions. The error occurs at the final confirmation step.",
    "priority": "critical",
    "category": "billing"
}

3. Assign Complaint
POST /api/complaints/{id}/assign
Headers:
  Authorization: Bearer YOUR_TOKEN
  Content-Type: application/json

Body:
{
    "assigned_to": 2,
    "notes": "Assigning to senior technician for urgent resolution"
}

4. Resolve Complaint
POST /api/complaints/{id}/resolve
Headers:
  Authorization: Bearer YOUR_TOKEN
  Content-Type: application/json

Body:
{
    "resolution_notes": "Issue resolved by updating payment gateway configuration",
    "resolution_type": "resolved",
    "internal_notes": "Updated timeout settings from 30s to 60s"
}


Valid Field Values
Priority Options:
•	"low" - Minor issues, can be addressed in standard timeframe
•	"medium" - Standard priority for most complaints
•	"high" - Important issues requiring faster resolution
•	"critical" - Urgent issues affecting system functionality
Category Options:
•	"technical" - System, software, or technical issues
•	"billing" - Payment, invoicing, or financial concerns
•	"service" - Customer service or support related
•	"product" - Product functionality or feature requests
•	"other" - Miscellaneous complaints
Resolution Types:
•	"resolved" - Issue completely fixed
•	"closed" - Complaint closed without resolution
•	"escalated" - Moved to higher level support
•	"duplicate" - Duplicate of existing complaint

Configuration
Key Configuration Files
•	config/auth.php - Authentication guards and providers
•	config/permission.php - Role and permission settings
•	config/queue.php - Queue driver configuration
•	config/mail.php - Email notification settings

Monitoring & Maintenance
Log Files
•	Application logs: storage/logs/laravel.log
•	Queue worker logs: storage/logs/worker.log

Performance Optimization

Database Optimization
•	Indexes on frequently queried columns
•	Eager loading relationships to prevent N+1 queries
•	Query result caching for dashboard statistics

Caching Strategy
•	Route caching: php artisan route:cache
•	Configuration caching: php artisan config:cache
•	View caching: php artisan view:cache
•	Redis for session and cache storage in production

Security Considerations
API Security
•	Token-based authentication with Laravel Sanctum
•	CORS configuration for web applications
•	Input validation and sanitization
Data Protection
•	Secure file upload handling
•	SQL injection prevention through Eloquent ORM
•	XSS protection with proper output escaping


