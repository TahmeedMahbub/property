-- ============================================================
-- INSERT DATA - Seed data for Property Management System
-- Run AFTER create_tables.sql
-- ============================================================

-- ─── Permissions ─────────────────────────────────────────────
INSERT INTO `p_permissions` (`name`, `slug`, `group`, `description`, `created_at`, `updated_at`) VALUES
('View Company', 'company.view', 'company', NULL, NOW(), NOW()),
('Update Company', 'company.update', 'company', NULL, NOW(), NOW()),
('Delete Company', 'company.delete', 'company', NULL, NOW(), NOW()),
('View Members', 'members.view', 'members', NULL, NOW(), NOW()),
('Manage Members', 'members.manage', 'members', NULL, NOW(), NOW()),
('View Shareholders', 'shareholders.view', 'shareholders', NULL, NOW(), NOW()),
('Manage Shareholders', 'shareholders.manage', 'shareholders', NULL, NOW(), NOW()),
('View Projects', 'projects.view', 'projects', NULL, NOW(), NOW()),
('Create Projects', 'projects.create', 'projects', NULL, NOW(), NOW()),
('Update Projects', 'projects.update', 'projects', NULL, NOW(), NOW()),
('Delete Projects', 'projects.delete', 'projects', NULL, NOW(), NOW()),
('View Investors', 'investors.view', 'investors', NULL, NOW(), NOW()),
('Manage Investors', 'investors.manage', 'investors', NULL, NOW(), NOW()),
('View Employees', 'employees.view', 'employees', NULL, NOW(), NOW()),
('Manage Employees', 'employees.manage', 'employees', NULL, NOW(), NOW()),
('View Customers', 'customers.view', 'customers', NULL, NOW(), NOW()),
('Manage Customers', 'customers.manage', 'customers', NULL, NOW(), NOW()),
('View Documents', 'documents.view', 'documents', NULL, NOW(), NOW()),
('Upload Documents', 'documents.upload', 'documents', NULL, NOW(), NOW()),
('Manage Documents', 'documents.manage', 'documents', NULL, NOW(), NOW()),
('View Settings', 'settings.view', 'settings', NULL, NOW(), NOW()),
('Manage Settings', 'settings.manage', 'settings', NULL, NOW(), NOW()),
('View Properties', 'properties.view', 'properties', NULL, NOW(), NOW()),
('Manage Properties', 'properties.manage', 'properties', NULL, NOW(), NOW()),
('Delete Properties', 'properties.delete', 'properties', NULL, NOW(), NOW());

-- ─── Roles (Platform/System Roles) ──────────────────────────
INSERT INTO `p_roles` (`company_id`, `name`, `slug`, `description`, `is_system`, `created_at`, `updated_at`) VALUES
(NULL, 'Admin', 'admin', NULL, 1, NOW(), NOW()),
(NULL, 'Manager', 'manager', NULL, 1, NOW(), NOW()),
(NULL, 'Member', 'member', NULL, 1, NOW(), NOW()),
(NULL, 'Viewer', 'viewer', NULL, 1, NOW(), NOW());

-- ─── Role Permissions ────────────────────────────────────────
-- Admin gets ALL permissions
INSERT INTO `p_role_permissions` (`role_id`, `permission_id`)
SELECT
    (SELECT `id` FROM `p_roles` WHERE `slug` = 'admin' AND `company_id` IS NULL),
    `id`
FROM `p_permissions`;

-- Manager gets everything except company.delete and settings.manage
INSERT INTO `p_role_permissions` (`role_id`, `permission_id`)
SELECT
    (SELECT `id` FROM `p_roles` WHERE `slug` = 'manager' AND `company_id` IS NULL),
    `id`
FROM `p_permissions`
WHERE `slug` NOT IN ('company.delete', 'settings.manage');

-- Member gets all *.view + projects.create, documents.upload, properties.manage
INSERT INTO `p_role_permissions` (`role_id`, `permission_id`)
SELECT
    (SELECT `id` FROM `p_roles` WHERE `slug` = 'member' AND `company_id` IS NULL),
    `id`
FROM `p_permissions`
WHERE `slug` LIKE '%.view'
   OR `slug` IN ('projects.create', 'documents.upload', 'properties.manage');

-- Viewer gets only *.view
INSERT INTO `p_role_permissions` (`role_id`, `permission_id`)
SELECT
    (SELECT `id` FROM `p_roles` WHERE `slug` = 'viewer' AND `company_id` IS NULL),
    `id`
FROM `p_permissions`
WHERE `slug` LIKE '%.view';

-- ─── Users ───────────────────────────────────────────────────
INSERT INTO `p_users` (`uuid`, `name`, `email`, `password`, `is_super_admin`, `email_verified_at`, `status`, `created_at`, `updated_at`) VALUES
(UUID(), 'Super Admin', 'admin@g.c', '$2y$12$sZhLFbGCBmJ/aHD0R28mIOYt9T8L/V0xDXoBzjoyLHBTKJurjO2CO', 1, NOW(), 'active', NOW(), NOW()),
(UUID(), 'Demo Owner', 'owner@g.c', '$2y$12$sZhLFbGCBmJ/aHD0R28mIOYt9T8L/V0xDXoBzjoyLHBTKJurjO2CO', 0, NOW(), 'active', NOW(), NOW());

-- ─── Companies ───────────────────────────────────────────────
INSERT INTO `p_companies` (`uuid`, `name`, `legal_name`, `type`, `email`, `city`, `country`, `currency`, `fiscal_year_start_month`, `status`, `created_at`, `updated_at`) VALUES
(UUID(), 'Demo Property Co.', 'Demo Property Company Ltd.', 'real_estate', 'info@demo-property.test', 'Dhaka', 'Bangladesh', 'BDT', 7, 'active', NOW(), NOW());

-- ─── Company Memberships (Owner assignment) ──────────────────
INSERT INTO `p_company_memberships` (`company_id`, `user_id`, `role_id`, `is_owner`, `joined_at`, `status`, `created_at`, `updated_at`)
SELECT
    (SELECT `id` FROM `p_companies` WHERE `name` = 'Demo Property Co.' LIMIT 1),
    (SELECT `id` FROM `p_users` WHERE `email` = 'owner@g.c' LIMIT 1),
    (SELECT `id` FROM `p_roles` WHERE `slug` = 'admin' AND `company_id` IS NULL LIMIT 1),
    1,
    CURDATE(),
    'active',
    NOW(),
    NOW();
