-- ============================================================
-- CREATE TABLES - All tables prefixed with "p_"
-- Generated for: Property Management System
-- ============================================================

-- 1. Users
CREATE TABLE `p_users` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `uuid` CHAR(36) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(20) NULL,
    `password` VARCHAR(255) NOT NULL,
    `avatar` VARCHAR(255) NULL,
    `is_super_admin` TINYINT(1) NOT NULL DEFAULT 0,
    `email_verified_at` TIMESTAMP NULL,
    `phone_verified_at` TIMESTAMP NULL,
    `last_login_at` TIMESTAMP NULL,
    `status` ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
    `remember_token` VARCHAR(100) NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    `deleted_at` TIMESTAMP NULL,
    UNIQUE KEY `p_users_uuid_unique` (`uuid`),
    UNIQUE KEY `p_users_email_unique` (`email`),
    UNIQUE KEY `p_users_phone_unique` (`phone`),
    INDEX `p_users_status_index` (`status`),
    INDEX `p_users_is_super_admin_index` (`is_super_admin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Password Reset Tokens
CREATE TABLE `p_password_reset_tokens` (
    `email` VARCHAR(255) NOT NULL,
    `token` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP NULL,
    PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Sessions
CREATE TABLE `p_sessions` (
    `id` VARCHAR(255) NOT NULL,
    `user_id` BIGINT UNSIGNED NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` TEXT NULL,
    `payload` LONGTEXT NOT NULL,
    `last_activity` INT NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `p_sessions_user_id_index` (`user_id`),
    INDEX `p_sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Companies
CREATE TABLE `p_companies` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `uuid` CHAR(36) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `legal_name` VARCHAR(255) NULL,
    `registration_number` VARCHAR(255) NULL,
    `tax_id` VARCHAR(255) NULL,
    `type` VARCHAR(50) NULL,
    `email` VARCHAR(255) NULL,
    `phone` VARCHAR(20) NULL,
    `website` VARCHAR(255) NULL,
    `address` VARCHAR(255) NULL,
    `city` VARCHAR(100) NULL,
    `state` VARCHAR(100) NULL,
    `country` VARCHAR(100) NULL,
    `postal_code` VARCHAR(20) NULL,
    `logo` VARCHAR(255) NULL,
    `currency` VARCHAR(10) NOT NULL DEFAULT 'BDT',
    `fiscal_year_start_month` TINYINT NULL,
    `status` ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
    `settings` JSON NULL,
    `founded_at` DATE NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    `deleted_at` TIMESTAMP NULL,
    UNIQUE KEY `p_companies_uuid_unique` (`uuid`),
    INDEX `p_companies_status_index` (`status`),
    INDEX `p_companies_type_index` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Roles
CREATE TABLE `p_roles` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `company_id` BIGINT UNSIGNED NULL,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `description` VARCHAR(255) NULL,
    `is_system` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    UNIQUE KEY `p_roles_company_id_slug_unique` (`company_id`, `slug`),
    INDEX `p_roles_is_system_index` (`is_system`),
    CONSTRAINT `p_roles_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `p_companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Permissions
CREATE TABLE `p_permissions` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `group` VARCHAR(50) NOT NULL,
    `description` VARCHAR(255) NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    UNIQUE KEY `p_permissions_slug_unique` (`slug`),
    INDEX `p_permissions_group_index` (`group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Role Permissions (Pivot)
CREATE TABLE `p_role_permissions` (
    `role_id` BIGINT UNSIGNED NOT NULL,
    `permission_id` BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (`role_id`, `permission_id`),
    CONSTRAINT `p_role_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `p_roles` (`id`) ON DELETE CASCADE,
    CONSTRAINT `p_role_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `p_permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Company Memberships
CREATE TABLE `p_company_memberships` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `company_id` BIGINT UNSIGNED NOT NULL,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `role_id` BIGINT UNSIGNED NULL,
    `title` VARCHAR(255) NULL,
    `department` VARCHAR(100) NULL,
    `is_owner` TINYINT(1) NOT NULL DEFAULT 0,
    `joined_at` DATE NULL,
    `left_at` DATE NULL,
    `status` ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    UNIQUE KEY `p_company_memberships_company_id_user_id_unique` (`company_id`, `user_id`),
    INDEX `p_company_memberships_user_id_status_index` (`user_id`, `status`),
    INDEX `p_company_memberships_company_id_status_index` (`company_id`, `status`),
    CONSTRAINT `p_company_memberships_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `p_companies` (`id`) ON DELETE CASCADE,
    CONSTRAINT `p_company_memberships_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `p_users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `p_company_memberships_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `p_roles` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Shareholders
CREATE TABLE `p_shareholders` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `uuid` CHAR(36) NOT NULL,
    `company_id` BIGINT UNSIGNED NOT NULL,
    `user_id` BIGINT UNSIGNED NULL,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NULL,
    `phone` VARCHAR(20) NULL,
    `share_percentage` DECIMAL(8,4) NOT NULL,
    `share_amount` DECIMAL(15,2) NULL,
    `share_type` ENUM('common', 'preferred', 'convertible') NOT NULL DEFAULT 'common',
    `acquired_at` DATE NULL,
    `notes` TEXT NULL,
    `status` ENUM('active', 'inactive', 'transferred') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    `deleted_at` TIMESTAMP NULL,
    UNIQUE KEY `p_shareholders_uuid_unique` (`uuid`),
    INDEX `p_shareholders_company_id_status_index` (`company_id`, `status`),
    INDEX `p_shareholders_company_id_user_id_index` (`company_id`, `user_id`),
    CONSTRAINT `p_shareholders_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `p_companies` (`id`) ON DELETE CASCADE,
    CONSTRAINT `p_shareholders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `p_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Projects
CREATE TABLE `p_projects` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `uuid` CHAR(36) NOT NULL,
    `company_id` BIGINT UNSIGNED NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `type` VARCHAR(50) NULL,
    `budget` DECIMAL(15,2) NULL,
    `start_date` DATE NULL,
    `end_date` DATE NULL,
    `location` VARCHAR(255) NULL,
    `address` VARCHAR(255) NULL,
    `city` VARCHAR(100) NULL,
    `status` ENUM('planning', 'active', 'on_hold', 'completed', 'cancelled') NOT NULL DEFAULT 'planning',
    `settings` JSON NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    `deleted_at` TIMESTAMP NULL,
    UNIQUE KEY `p_projects_uuid_unique` (`uuid`),
    UNIQUE KEY `p_projects_company_id_slug_unique` (`company_id`, `slug`),
    INDEX `p_projects_company_id_status_index` (`company_id`, `status`),
    INDEX `p_projects_type_index` (`type`),
    CONSTRAINT `p_projects_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `p_companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Project Investors
CREATE TABLE `p_project_investors` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `uuid` CHAR(36) NOT NULL,
    `project_id` BIGINT UNSIGNED NOT NULL,
    `user_id` BIGINT UNSIGNED NULL,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NULL,
    `phone` VARCHAR(20) NULL,
    `investment_amount` DECIMAL(15,2) NOT NULL,
    `investment_percentage` DECIMAL(8,4) NULL,
    `investment_type` ENUM('equity', 'debt', 'convertible') NOT NULL DEFAULT 'equity',
    `invested_at` DATE NULL,
    `expected_return` DECIMAL(15,2) NULL,
    `notes` TEXT NULL,
    `status` ENUM('active', 'inactive', 'withdrawn') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    `deleted_at` TIMESTAMP NULL,
    UNIQUE KEY `p_project_investors_uuid_unique` (`uuid`),
    INDEX `p_project_investors_project_id_status_index` (`project_id`, `status`),
    INDEX `p_project_investors_project_id_user_id_index` (`project_id`, `user_id`),
    CONSTRAINT `p_project_investors_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `p_projects` (`id`) ON DELETE CASCADE,
    CONSTRAINT `p_project_investors_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `p_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Employees
CREATE TABLE `p_employees` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `uuid` CHAR(36) NOT NULL,
    `company_id` BIGINT UNSIGNED NOT NULL,
    `user_id` BIGINT UNSIGNED NULL,
    `membership_id` BIGINT UNSIGNED NULL,
    `employee_id_number` VARCHAR(255) NULL,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NULL,
    `phone` VARCHAR(20) NULL,
    `department` VARCHAR(100) NULL,
    `designation` VARCHAR(100) NULL,
    `date_of_birth` DATE NULL,
    `date_of_joining` DATE NOT NULL,
    `date_of_leaving` DATE NULL,
    `salary` DECIMAL(12,2) NULL,
    `salary_type` ENUM('monthly', 'hourly', 'daily', 'weekly') NOT NULL DEFAULT 'monthly',
    `bank_name` VARCHAR(255) NULL,
    `bank_account_number` VARCHAR(255) NULL,
    `emergency_contact_name` VARCHAR(255) NULL,
    `emergency_contact_phone` VARCHAR(20) NULL,
    `address` TEXT NULL,
    `status` ENUM('active', 'on_leave', 'resigned', 'terminated') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    `deleted_at` TIMESTAMP NULL,
    UNIQUE KEY `p_employees_uuid_unique` (`uuid`),
    INDEX `p_employees_company_id_status_index` (`company_id`, `status`),
    INDEX `p_employees_company_id_department_index` (`company_id`, `department`),
    INDEX `p_employees_company_id_employee_id_number_index` (`company_id`, `employee_id_number`),
    CONSTRAINT `p_employees_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `p_companies` (`id`) ON DELETE CASCADE,
    CONSTRAINT `p_employees_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `p_users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `p_employees_membership_id_foreign` FOREIGN KEY (`membership_id`) REFERENCES `p_company_memberships` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. Customers
CREATE TABLE `p_customers` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `uuid` CHAR(36) NOT NULL,
    `company_id` BIGINT UNSIGNED NOT NULL,
    `user_id` BIGINT UNSIGNED NULL,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NULL,
    `phone` VARCHAR(20) NULL,
    `company_name` VARCHAR(255) NULL,
    `tax_id` VARCHAR(255) NULL,
    `address` TEXT NULL,
    `city` VARCHAR(100) NULL,
    `state` VARCHAR(100) NULL,
    `country` VARCHAR(100) NULL,
    `postal_code` VARCHAR(20) NULL,
    `type` ENUM('individual', 'business') NOT NULL DEFAULT 'individual',
    `credit_limit` DECIMAL(12,2) NULL,
    `notes` TEXT NULL,
    `status` ENUM('active', 'inactive', 'blacklisted') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    `deleted_at` TIMESTAMP NULL,
    UNIQUE KEY `p_customers_uuid_unique` (`uuid`),
    INDEX `p_customers_company_id_status_index` (`company_id`, `status`),
    INDEX `p_customers_company_id_phone_index` (`company_id`, `phone`),
    INDEX `p_customers_company_id_email_index` (`company_id`, `email`),
    INDEX `p_customers_company_id_type_index` (`company_id`, `type`),
    CONSTRAINT `p_customers_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `p_companies` (`id`) ON DELETE CASCADE,
    CONSTRAINT `p_customers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `p_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. Document Categories
CREATE TABLE `p_document_categories` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `company_id` BIGINT UNSIGNED NULL,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `description` VARCHAR(255) NULL,
    `parent_id` BIGINT UNSIGNED NULL,
    `sort_order` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    UNIQUE KEY `p_document_categories_company_id_slug_parent_id_unique` (`company_id`, `slug`, `parent_id`),
    INDEX `p_document_categories_company_id_parent_id_index` (`company_id`, `parent_id`),
    CONSTRAINT `p_document_categories_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `p_companies` (`id`) ON DELETE CASCADE,
    CONSTRAINT `p_document_categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `p_document_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. Document Folders
CREATE TABLE `p_document_folders` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `uuid` CHAR(36) NOT NULL,
    `company_id` BIGINT UNSIGNED NOT NULL,
    `parent_id` BIGINT UNSIGNED NULL,
    `name` VARCHAR(255) NOT NULL,
    `path` VARCHAR(255) NOT NULL COMMENT 'Full materialized path for fast lookups',
    `created_by` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    `deleted_at` TIMESTAMP NULL,
    UNIQUE KEY `p_document_folders_uuid_unique` (`uuid`),
    INDEX `p_document_folders_company_id_parent_id_index` (`company_id`, `parent_id`),
    INDEX `p_document_folders_company_id_path_index` (`company_id`, `path`),
    CONSTRAINT `p_document_folders_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `p_companies` (`id`) ON DELETE CASCADE,
    CONSTRAINT `p_document_folders_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `p_document_folders` (`id`) ON DELETE CASCADE,
    CONSTRAINT `p_document_folders_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `p_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. Documents
CREATE TABLE `p_documents` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `uuid` CHAR(36) NOT NULL,
    `company_id` BIGINT UNSIGNED NOT NULL,
    `category_id` BIGINT UNSIGNED NULL,
    `folder_id` BIGINT UNSIGNED NULL,
    `documentable_type` VARCHAR(255) NULL,
    `documentable_id` BIGINT UNSIGNED NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `file_name` VARCHAR(255) NOT NULL,
    `file_path` VARCHAR(255) NOT NULL,
    `file_size` BIGINT UNSIGNED NOT NULL,
    `mime_type` VARCHAR(100) NOT NULL,
    `disk` VARCHAR(20) NOT NULL DEFAULT 'local',
    `uploaded_by` BIGINT UNSIGNED NULL,
    `is_public` TINYINT(1) NOT NULL DEFAULT 0,
    `metadata` JSON NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    `deleted_at` TIMESTAMP NULL,
    UNIQUE KEY `p_documents_uuid_unique` (`uuid`),
    INDEX `p_documents_company_id_category_id_index` (`company_id`, `category_id`),
    INDEX `p_documents_company_id_folder_id_index` (`company_id`, `folder_id`),
    INDEX `p_documents_documentable_type_documentable_id_index` (`documentable_type`, `documentable_id`),
    CONSTRAINT `p_documents_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `p_companies` (`id`) ON DELETE CASCADE,
    CONSTRAINT `p_documents_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `p_document_categories` (`id`) ON DELETE SET NULL,
    CONSTRAINT `p_documents_folder_id_foreign` FOREIGN KEY (`folder_id`) REFERENCES `p_document_folders` (`id`) ON DELETE SET NULL,
    CONSTRAINT `p_documents_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `p_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 17. Document Versions
CREATE TABLE `p_document_versions` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `document_id` BIGINT UNSIGNED NOT NULL,
    `version_number` INT UNSIGNED NOT NULL,
    `file_name` VARCHAR(255) NOT NULL,
    `file_path` VARCHAR(255) NOT NULL,
    `file_size` BIGINT UNSIGNED NOT NULL,
    `mime_type` VARCHAR(100) NOT NULL,
    `changes_summary` TEXT NULL,
    `uploaded_by` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    UNIQUE KEY `p_document_versions_document_id_version_number_unique` (`document_id`, `version_number`),
    CONSTRAINT `p_document_versions_document_id_foreign` FOREIGN KEY (`document_id`) REFERENCES `p_documents` (`id`) ON DELETE CASCADE,
    CONSTRAINT `p_document_versions_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `p_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 18. Activity Logs
CREATE TABLE `p_activity_logs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `company_id` BIGINT UNSIGNED NULL,
    `user_id` BIGINT UNSIGNED NULL,
    `subject_type` VARCHAR(255) NULL,
    `subject_id` BIGINT UNSIGNED NULL,
    `action` VARCHAR(50) NOT NULL,
    `description` TEXT NULL,
    `properties` JSON NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` VARCHAR(255) NULL,
    `created_at` TIMESTAMP NULL,
    INDEX `p_activity_logs_company_id_created_at_index` (`company_id`, `created_at`),
    INDEX `p_activity_logs_user_id_created_at_index` (`user_id`, `created_at`),
    INDEX `p_activity_logs_subject_type_subject_id_index` (`subject_type`, `subject_id`),
    CONSTRAINT `p_activity_logs_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `p_companies` (`id`) ON DELETE CASCADE,
    CONSTRAINT `p_activity_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `p_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 19. Settings
CREATE TABLE `p_settings` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `company_id` BIGINT UNSIGNED NULL,
    `group` VARCHAR(50) NOT NULL,
    `key` VARCHAR(100) NOT NULL,
    `value` TEXT NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    UNIQUE KEY `p_settings_company_id_group_key_unique` (`company_id`, `group`, `key`),
    INDEX `p_settings_company_id_group_index` (`company_id`, `group`),
    CONSTRAINT `p_settings_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `p_companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 20. Unit Types
CREATE TABLE `p_unit_types` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `uuid` CHAR(36) NOT NULL,
    `company_id` BIGINT UNSIGNED NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    `deleted_at` TIMESTAMP NULL,
    UNIQUE KEY `p_unit_types_uuid_unique` (`uuid`),
    UNIQUE KEY `p_unit_types_company_id_slug_unique` (`company_id`, `slug`),
    INDEX `p_unit_types_company_id_index` (`company_id`),
    CONSTRAINT `p_unit_types_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `p_companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 21. Buildings
CREATE TABLE `p_buildings` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `uuid` CHAR(36) NOT NULL,
    `company_id` BIGINT UNSIGNED NOT NULL,
    `project_id` BIGINT UNSIGNED NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `total_floors` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `total_units` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `address` VARCHAR(255) NULL,
    `status` ENUM('planning', 'under_construction', 'completed', 'handed_over') NOT NULL DEFAULT 'planning',
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    `deleted_at` TIMESTAMP NULL,
    UNIQUE KEY `p_buildings_uuid_unique` (`uuid`),
    UNIQUE KEY `p_buildings_project_id_slug_unique` (`project_id`, `slug`),
    INDEX `p_buildings_project_id_index` (`project_id`),
    INDEX `p_buildings_company_id_index` (`company_id`),
    INDEX `p_buildings_status_index` (`status`),
    CONSTRAINT `p_buildings_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `p_companies` (`id`) ON DELETE CASCADE,
    CONSTRAINT `p_buildings_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `p_projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 22. Floors
CREATE TABLE `p_floors` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `uuid` CHAR(36) NOT NULL,
    `company_id` BIGINT UNSIGNED NOT NULL,
    `project_id` BIGINT UNSIGNED NOT NULL,
    `building_id` BIGINT UNSIGNED NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `floor_number` SMALLINT UNSIGNED NOT NULL,
    `description` TEXT NULL,
    `total_units` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    `deleted_at` TIMESTAMP NULL,
    UNIQUE KEY `p_floors_uuid_unique` (`uuid`),
    UNIQUE KEY `p_floors_building_id_floor_number_unique` (`building_id`, `floor_number`),
    INDEX `p_floors_building_id_index` (`building_id`),
    INDEX `p_floors_project_id_index` (`project_id`),
    INDEX `p_floors_company_id_index` (`company_id`),
    CONSTRAINT `p_floors_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `p_companies` (`id`) ON DELETE CASCADE,
    CONSTRAINT `p_floors_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `p_projects` (`id`) ON DELETE CASCADE,
    CONSTRAINT `p_floors_building_id_foreign` FOREIGN KEY (`building_id`) REFERENCES `p_buildings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 23. Units
CREATE TABLE `p_units` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `uuid` CHAR(36) NOT NULL,
    `company_id` BIGINT UNSIGNED NOT NULL,
    `project_id` BIGINT UNSIGNED NOT NULL,
    `building_id` BIGINT UNSIGNED NOT NULL,
    `floor_id` BIGINT UNSIGNED NOT NULL,
    `unit_type_id` BIGINT UNSIGNED NULL,
    `unit_number` VARCHAR(255) NOT NULL,
    `size` DECIMAL(10,2) NULL,
    `price` DECIMAL(15,2) NULL,
    `facing` VARCHAR(50) NULL,
    `status` ENUM('available', 'reserved', 'booked', 'sold', 'handovered') NOT NULL DEFAULT 'available',
    `description` TEXT NULL,
    `meta` JSON NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    `deleted_at` TIMESTAMP NULL,
    UNIQUE KEY `p_units_uuid_unique` (`uuid`),
    UNIQUE KEY `p_units_building_id_unit_number_unique` (`building_id`, `unit_number`),
    INDEX `p_units_project_id_index` (`project_id`),
    INDEX `p_units_building_id_index` (`building_id`),
    INDEX `p_units_floor_id_index` (`floor_id`),
    INDEX `p_units_status_index` (`status`),
    INDEX `p_units_unit_type_id_index` (`unit_type_id`),
    INDEX `p_units_company_id_index` (`company_id`),
    CONSTRAINT `p_units_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `p_companies` (`id`) ON DELETE CASCADE,
    CONSTRAINT `p_units_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `p_projects` (`id`) ON DELETE CASCADE,
    CONSTRAINT `p_units_building_id_foreign` FOREIGN KEY (`building_id`) REFERENCES `p_buildings` (`id`) ON DELETE CASCADE,
    CONSTRAINT `p_units_floor_id_foreign` FOREIGN KEY (`floor_id`) REFERENCES `p_floors` (`id`) ON DELETE CASCADE,
    CONSTRAINT `p_units_unit_type_id_foreign` FOREIGN KEY (`unit_type_id`) REFERENCES `p_unit_types` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 24. Framework Tables
CREATE TABLE `p_failed_jobs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `uuid` VARCHAR(255) NOT NULL,
    `connection` TEXT NOT NULL,
    `queue` TEXT NOT NULL,
    `payload` LONGTEXT NOT NULL,
    `exception` LONGTEXT NOT NULL,
    `failed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `p_failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `p_personal_access_tokens` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `tokenable_type` VARCHAR(255) NOT NULL,
    `tokenable_id` BIGINT UNSIGNED NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `token` VARCHAR(64) NOT NULL,
    `abilities` TEXT NULL,
    `last_used_at` TIMESTAMP NULL,
    `expires_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    UNIQUE KEY `p_personal_access_tokens_token_unique` (`token`),
    INDEX `p_personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`, `tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `p_notifications` (
    `id` CHAR(36) NOT NULL,
    `type` VARCHAR(255) NOT NULL,
    `notifiable_type` VARCHAR(255) NOT NULL,
    `notifiable_id` BIGINT UNSIGNED NOT NULL,
    `data` TEXT NOT NULL,
    `read_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    INDEX `p_notifications_notifiable_type_notifiable_id_index` (`notifiable_type`, `notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `p_jobs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `queue` VARCHAR(255) NOT NULL,
    `payload` LONGTEXT NOT NULL,
    `attempts` TINYINT UNSIGNED NOT NULL,
    `reserved_at` INT UNSIGNED NULL,
    `available_at` INT UNSIGNED NOT NULL,
    `created_at` INT UNSIGNED NOT NULL,
    INDEX `p_jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `p_job_batches` (
    `id` VARCHAR(255) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `total_jobs` INT NOT NULL,
    `pending_jobs` INT NOT NULL,
    `failed_jobs` INT NOT NULL,
    `failed_job_ids` LONGTEXT NOT NULL,
    `options` MEDIUMTEXT NULL,
    `cancelled_at` INT NULL,
    `created_at` INT NOT NULL,
    `finished_at` INT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `p_cache` (
    `key` VARCHAR(255) NOT NULL,
    `value` MEDIUMTEXT NOT NULL,
    `expiration` INT NOT NULL,
    PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `p_cache_locks` (
    `key` VARCHAR(255) NOT NULL,
    `owner` VARCHAR(255) NOT NULL,
    `expiration` INT NOT NULL,
    PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
