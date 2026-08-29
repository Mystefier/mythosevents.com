-- Run this once via group_migration_runner.php, then delete the runner.

CREATE TABLE IF NOT EXISTS `groups` (
  `id`          INT AUTO_INCREMENT PRIMARY KEY,
  `slug`        VARCHAR(64)  NOT NULL UNIQUE,
  `name`        VARCHAR(128) NOT NULL,
  `icon`        VARCHAR(16)  DEFAULT NULL,
  `description` TEXT         DEFAULT NULL,
  `join_url`    VARCHAR(255) DEFAULT NULL,
  `created_at`  DATETIME     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `groups` (slug, name, icon, description, join_url) VALUES
  ('sonlight', 'Sonlight Drama Team', '☀️',
   'A weekly drama team exploring faith and theater together — one Bible question, one theater question, every Sunday.',
   '/sonlight/join.php');

CREATE TABLE IF NOT EXISTS `group_memberships` (
  `id`        INT AUTO_INCREMENT PRIMARY KEY,
  `person_id` INT NOT NULL,
  `group_id`  INT NOT NULL,
  `joined_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_person_group` (`person_id`, `group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Backfill existing Sonlight members from people.roles
INSERT IGNORE INTO group_memberships (person_id, group_id)
SELECT p.id, g.id
FROM people p
JOIN `groups` g ON g.slug = 'sonlight'
WHERE p.roles LIKE '%Sonlight Drama Team%';
