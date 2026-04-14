# Real Estate Agency Manual

This manual explains the important moving parts in the codebase and how to operate the site safely.

## 1. Setup Paths

- Main config: [config/database.php](/C:/Users/User/Documents/New%20project/config/database.php)
- Shared helpers: [includes/functions.php](/C:/Users/User/Documents/New%20project/includes/functions.php)
- Public pages: [index.php](/C:/Users/User/Documents/New%20project/index.php), [properties.php](/C:/Users/User/Documents/New%20project/properties.php), [property.php](/C:/Users/User/Documents/New%20project/property.php)
- Admin area: [admin](/C:/Users/User/Documents/New%20project/admin)
- SQL for fresh install: [database.sql](/C:/Users/User/Documents/New%20project/database.sql)
- SQL for upgrading the earlier version: [database_migration_v2.sql](/C:/Users/User/Documents/New%20project/database_migration_v2.sql)
- Uploaded property images: [uploads/properties](/C:/Users/User/Documents/New%20project/uploads/properties)

## 2. How To Run The Site

1. Make sure MySQL is running.
2. Import either:
   - [database.sql](/C:/Users/User/Documents/New%20project/database.sql) for a fresh install
   - [database_migration_v2.sql](/C:/Users/User/Documents/New%20project/database_migration_v2.sql) if you already imported the old schema
3. Confirm [config/database.php](/C:/Users/User/Documents/New%20project/config/database.php) matches your MySQL credentials.
4. Start the local server from the project root:

```bash
php -S localhost:8000
```

5. Open the site in the browser.

## 3. Admin Roles

There are two roles in the `admins` table:

- `super_admin`
  - This is the major admin
  - Can view, edit, and delete all properties
  - Can add, edit, and remove other admins
  - When deleting another admin, that admin’s properties are reassigned to the current major admin
- `admin`
  - Can create new properties
  - Can edit and delete only their own properties
  - Can update their own email and password from `My Account`

The default major admin is:

- Email: `admin@harborhomes.com`
- Password: `Admin123!`

## 4. Why A Manually Inserted Admin Might Fail To Log In

In the earlier version, the app expected the password in `admins.password_hash` to already be a PHP password hash.

The current code is more forgiving:

- If you insert an admin manually and put a plain-text password into `password_hash`, the app will accept it once and immediately convert it into a secure hash after the first successful login.

Recommended approach:

- Use the `Admins` page inside the dashboard instead of phpMyAdmin whenever possible.

If you still want to insert an admin manually:

1. Add the row in phpMyAdmin
2. Put the password in the `password_hash` column
3. Set `role` to either `admin` or `super_admin`
4. Log in once through the app so the password gets re-hashed

## 5. Property Ownership Rules

Each property belongs to one admin through `properties.admin_id`.

Important behavior:

- Regular admins only see their own properties in the admin area
- Major admins see all properties
- New properties are automatically assigned to the currently signed-in admin

## 6. How Property Images Work

Property images are stored in two places:

- Database table: `property_images`
- Filesystem folder: [uploads/properties](/C:/Users/User/Documents/New%20project/uploads/properties)

How uploads behave:

- Each property can have multiple images
- One image is marked as the primary image
- The primary image is used on listing cards and as the main image on the detail page
- The remaining images appear in the property gallery on the detail page

Admin workflow:

1. Open `Properties`
2. Click `Add Listing` or `Edit`
3. Upload one or more JPG, PNG, or WEBP files
4. On edit, you can:
   - keep existing images
   - remove selected images
   - choose which existing image is the primary one

Important note:

- The `uploads` folder must be writable by PHP or image uploads will fail

## 7. Property Fields You Should Know

The listing form now uses:

- `bedrooms`
- `toilets`
- `building_area_sqm`
- `total_area_sqm`
- `created_at` as the posted date

How they are shown publicly:

- Listing cards show bedrooms, toilets, interior area, and posted date
- Detail pages show bedrooms, toilets, building area, total surface, and posting date

Meaning of the two size fields:

- `building_area_sqm`
  - interior / built space
- `total_area_sqm`
  - full surface area including land around the building

## 8. How To Manage Admin Accounts

Major admin workflow:

1. Sign in as a major admin
2. Open `Admins`
3. Use `Add Admin` to create a new account
4. Use `Edit` to change another admin’s name, email, role, or password
5. Use `Delete` to remove an admin

Deletion rule:

- You cannot delete yourself from the `Admins` list
- You cannot delete the final major admin account

## 9. How Each Admin Changes Their Email Or Password

Every admin can do this from:

- [admin/account.php](/C:/Users/User/Documents/New%20project/admin/account.php)

Behavior:

- changing email requires the current password
- setting a new password requires confirmation
- the app keeps the current signed-in session synced after the change

## 10. Public Pages

- Homepage: [index.php](/C:/Users/User/Documents/New%20project/index.php)
- Listing page: [properties.php](/C:/Users/User/Documents/New%20project/properties.php)
- Detail page: [property.php](/C:/Users/User/Documents/New%20project/property.php)
- Contact page: [contact.php](/C:/Users/User/Documents/New%20project/contact.php)

Important public data sources:

- property listings come from `properties`
- property galleries come from `property_images`
- office text and hero content come from `site_settings`

## 11. Admin Pages

- Dashboard: [admin/index.php](/C:/Users/User/Documents/New%20project/admin/index.php)
- Property list: [admin/properties.php](/C:/Users/User/Documents/New%20project/admin/properties.php)
- Property form: [admin/property_form.php](/C:/Users/User/Documents/New%20project/admin/property_form.php)
- Site content: [admin/settings.php](/C:/Users/User/Documents/New%20project/admin/settings.php)
- Account settings: [admin/account.php](/C:/Users/User/Documents/New%20project/admin/account.php)
- Admin management: [admin/admins.php](/C:/Users/User/Documents/New%20project/admin/admins.php)

## 12. Important Database Tables

- `admins`
  - login accounts and role permissions
- `properties`
  - listing ownership and main property details
- `property_images`
  - multi-image gallery storage
- `site_settings`
  - editable homepage/contact/footer content

## 13. Common Maintenance Tasks

To change office text or homepage copy:

- Go to `Site Content`

To add a listing:

- Go to `Properties`
- Click `Add Listing`
- Upload images

To fix permissions for an admin:

- Sign in as a major admin
- Open `Admins`
- Edit their role

To move a listing under another account:

- At the moment the system auto-assigns new listings to the logged-in admin
- If you need reassignment later, the cleanest current approach is to update `properties.admin_id` in phpMyAdmin

## 14. Important Code Behavior

The most important reusable logic lives in [includes/functions.php](/C:/Users/User/Documents/New%20project/includes/functions.php).

That file controls:

- URL building
- database connection
- flash messages
- authentication
- role checks
- ownership checks
- gallery queries
- file upload handling

If you extend the project further, that is the first file to inspect.
