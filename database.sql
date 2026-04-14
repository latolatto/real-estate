CREATE DATABASE IF NOT EXISTS real_estate_agency CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE real_estate_agency;

CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(180) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('super_admin', 'admin') NOT NULL DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS site_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS properties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    title VARCHAR(180) NOT NULL,
    slug VARCHAR(190) NOT NULL UNIQUE,
    price DECIMAL(12, 2) NOT NULL,
    property_type VARCHAR(50) NOT NULL,
    status VARCHAR(50) NOT NULL,
    bedrooms INT NOT NULL,
    toilets DECIMAL(4, 1) NOT NULL,
    building_area_sqm DECIMAL(10, 2) NOT NULL,
    total_area_sqm DECIMAL(10, 2) NOT NULL,
    address VARCHAR(255) NOT NULL,
    city VARCHAR(120) NOT NULL,
    state VARCHAR(120) NOT NULL,
    zip_code VARCHAR(20) DEFAULT '',
    description TEXT NOT NULL,
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_properties_admin_id (admin_id),
    INDEX idx_properties_created_at (created_at)
);

CREATE TABLE IF NOT EXISTS property_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    sort_order INT NOT NULL DEFAULT 1,
    is_primary TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_property_images_property_id (property_id)
);

INSERT INTO admins (name, email, password_hash, role)
VALUES ('Agency Admin', 'admin@harborhomes.com', '$2y$10$x5SZBp37dkYZvrPNIsIzUe7kc1i1t.J4PkpuSy5IwEpyC4dHjDbNS', 'super_admin')
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    password_hash = VALUES(password_hash),
    role = VALUES(role);

SET @default_admin_id = (SELECT id FROM admins WHERE email = 'admin@harborhomes.com' LIMIT 1);

INSERT INTO site_settings (setting_key, setting_value) VALUES
('hero_eyebrow', 'Premier Real Estate Agency'),
('hero_title', 'Find a home that feels effortless from the first tour to the final signature.'),
('hero_subtitle', 'We market exceptional homes, guide smart investments, and give buyers a calm, informed experience in every neighborhood we serve.'),
('about_title', 'Sharp marketing, honest advice, and service that stays personal.'),
('about_body', 'From staging guidance and photography to negotiation strategy and closing support, we handle the details that make listings stand out and transactions move smoothly.'),
('service_regions', 'Miami, Fort Lauderdale, Palm Beach'),
('years_experience', '12+'),
('office_phone', '(305) 555-0189'),
('office_email', 'hello@harborhomes.com'),
('office_address', '18 Harbor Avenue, Miami, FL'),
('office_hours', 'Mon - Sat: 9:00 AM - 6:00 PM'),
('footer_blurb', 'Boutique real estate guidance for buyers, sellers, and investors who want thoughtful service and standout listings.')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

INSERT INTO properties (
    admin_id, title, slug, price, property_type, status, bedrooms, toilets,
    building_area_sqm, total_area_sqm, address, city, state, zip_code, description, is_featured
) VALUES
(
    @default_admin_id,
    'Waterfront Modern Retreat',
    'waterfront-modern-retreat',
    2450000,
    'Villa',
    'For Sale',
    4,
    4.0,
    357.7,
    520.0,
    '274 Ocean Crest Drive',
    'Miami Beach',
    'FL',
    '33139',
    'A bright waterfront villa with open-plan living, oversized terraces, and a private dock designed for indoor-outdoor entertaining.',
    1
),
(
    @default_admin_id,
    'Brickell Skyline Residence',
    'brickell-skyline-residence',
    1185000,
    'Condo',
    'For Sale',
    2,
    2.0,
    150.5,
    150.5,
    '1021 SE 8th Street',
    'Miami',
    'FL',
    '33131',
    'High-floor condo with panoramic city views, a gourmet kitchen, resort amenities, and walkable access to dining and nightlife.',
    1
),
(
    @default_admin_id,
    'Palm Grove Family Home',
    'palm-grove-family-home',
    879000,
    'House',
    'For Sale',
    3,
    2.0,
    198.8,
    420.0,
    '66 Palm Grove Lane',
    'Fort Lauderdale',
    'FL',
    '33301',
    'Inviting family home with updated finishes, a landscaped backyard, and easy access to top-rated schools and waterfront parks.',
    0
),
(
    @default_admin_id,
    'Downtown Loft Lease',
    'downtown-loft-lease',
    4200,
    'Apartment',
    'For Rent',
    1,
    1.0,
    91.0,
    91.0,
    '11 Harbor Point Avenue',
    'West Palm Beach',
    'FL',
    '33401',
    'Stylish loft rental with polished concrete floors, skyline views, and immediate access to downtown dining, transit, and culture.',
    0
)
ON DUPLICATE KEY UPDATE
    admin_id = VALUES(admin_id),
    title = VALUES(title),
    price = VALUES(price),
    property_type = VALUES(property_type),
    status = VALUES(status),
    bedrooms = VALUES(bedrooms),
    toilets = VALUES(toilets),
    building_area_sqm = VALUES(building_area_sqm),
    total_area_sqm = VALUES(total_area_sqm),
    address = VALUES(address),
    city = VALUES(city),
    state = VALUES(state),
    zip_code = VALUES(zip_code),
    description = VALUES(description),
    is_featured = VALUES(is_featured);

SET @waterfront_id = (SELECT id FROM properties WHERE slug = 'waterfront-modern-retreat' LIMIT 1);
SET @brickell_id = (SELECT id FROM properties WHERE slug = 'brickell-skyline-residence' LIMIT 1);
SET @palm_id = (SELECT id FROM properties WHERE slug = 'palm-grove-family-home' LIMIT 1);
SET @loft_id = (SELECT id FROM properties WHERE slug = 'downtown-loft-lease' LIMIT 1);

DELETE FROM property_images
WHERE property_id IN (@waterfront_id, @brickell_id, @palm_id, @loft_id);

INSERT INTO property_images (property_id, image_path, sort_order, is_primary) VALUES
(@waterfront_id, 'https://images.unsplash.com/photo-1600047509807-ba8f99d2cdde?auto=format&fit=crop&w=1200&q=80', 1, 1),
(@waterfront_id, 'https://images.unsplash.com/photo-1600607687644-c7171b42498f?auto=format&fit=crop&w=1200&q=80', 2, 0),
(@waterfront_id, 'https://images.unsplash.com/photo-1600585154526-990dced4db0d?auto=format&fit=crop&w=1200&q=80', 3, 0),
(@brickell_id, 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&w=1200&q=80', 1, 1),
(@brickell_id, 'https://images.unsplash.com/photo-1600121848594-d8644e57abab?auto=format&fit=crop&w=1200&q=80', 2, 0),
(@palm_id, 'https://images.unsplash.com/photo-1570129477492-45c003edd2be?auto=format&fit=crop&w=1200&q=80', 1, 1),
(@palm_id, 'https://images.unsplash.com/photo-1598228723793-52759bba239c?auto=format&fit=crop&w=1200&q=80', 2, 0),
(@loft_id, 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=1200&q=80', 1, 1),
(@loft_id, 'https://images.unsplash.com/photo-1494526585095-c41746248156?auto=format&fit=crop&w=1200&q=80', 2, 0);
