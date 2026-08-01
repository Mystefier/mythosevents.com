-- Add service area columns to the people table
ALTER TABLE people ADD COLUMN service_area_address VARCHAR(255) AFTER message;
ALTER TABLE people ADD COLUMN service_area_latitude DECIMAL(10, 8) AFTER service_area_address;
ALTER TABLE people ADD COLUMN service_area_longitude DECIMAL(11, 8) AFTER service_area_latitude;
ALTER TABLE people ADD COLUMN service_area_radius_miles INT DEFAULT 30 AFTER service_area_longitude;
