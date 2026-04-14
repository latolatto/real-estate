USE real_estate_agency;

ALTER TABLE admins
    ADD COLUMN IF NOT EXISTS role ENUM('super_admin', 'admin') NOT NULL DEFAULT 'admin' AFTER password_hash,
    ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

UPDATE admins
SET role = 'super_admin'
WHERE email = 'admin@harborhomes.com';

UPDATE admins
SET role = 'super_admin'
WHERE id = (
    SELECT promoted.id
    FROM (
        SELECT id
        FROM admins
        ORDER BY id ASC
        LIMIT 1
    ) AS promoted
)
AND NOT EXISTS (
    SELECT 1
    FROM (
        SELECT id
        FROM admins
        WHERE role = 'super_admin'
    ) AS existing_super_admin
);

ALTER TABLE properties
    ADD COLUMN IF NOT EXISTS admin_id INT NULL AFTER id,
    ADD COLUMN IF NOT EXISTS toilets DECIMAL(4, 1) NULL AFTER bedrooms,
    ADD COLUMN IF NOT EXISTS building_area_sqm DECIMAL(10, 2) NULL AFTER toilets,
    ADD COLUMN IF NOT EXISTS total_area_sqm DECIMAL(10, 2) NULL AFTER building_area_sqm;

UPDATE properties
SET admin_id = COALESCE(
    admin_id,
    (
        SELECT owner_id
        FROM (
            SELECT id AS owner_id
            FROM admins
            ORDER BY CASE WHEN role = 'super_admin' THEN 0 ELSE 1 END, id ASC
            LIMIT 1
        ) AS owner_source
    )
);

UPDATE properties
SET toilets = COALESCE(toilets, bathrooms, 1);

UPDATE properties
SET building_area_sqm = COALESCE(building_area_sqm, ROUND(area_sqft * 0.092903, 2));

UPDATE properties
SET total_area_sqm = COALESCE(total_area_sqm, ROUND(area_sqft * 0.092903, 2));

ALTER TABLE properties
    MODIFY COLUMN admin_id INT NOT NULL,
    MODIFY COLUMN toilets DECIMAL(4, 1) NOT NULL,
    MODIFY COLUMN building_area_sqm DECIMAL(10, 2) NOT NULL,
    MODIFY COLUMN total_area_sqm DECIMAL(10, 2) NOT NULL;

CREATE TABLE IF NOT EXISTS property_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    sort_order INT NOT NULL DEFAULT 1,
    is_primary TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_property_images_property_id (property_id)
);

INSERT INTO property_images (property_id, image_path, sort_order, is_primary)
SELECT p.id, p.image_url, 1, 1
FROM properties p
WHERE COALESCE(p.image_url, '') <> ''
AND NOT EXISTS (
    SELECT 1
    FROM property_images pi
    WHERE pi.property_id = p.id
);
