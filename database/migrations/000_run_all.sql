-- NetaTrack India: Master Migration Runner
-- Run this file to create all tables in order
-- Usage: mysql -u root -p netatrack < 000_run_all.sql

CREATE DATABASE IF NOT EXISTS `netatrack_india` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `netatrack_india`;

SOURCE 001_create_users_table.sql;
SOURCE 002_create_roles_table.sql;
SOURCE 003_create_states_table.sql;
SOURCE 004_create_parties_table.sql;
SOURCE 005_create_leaders_table.sql;
SOURCE 006_create_promises_table.sql;
SOURCE 007_create_projects_table.sql;
SOURCE 008_create_public_reports_table.sql;
SOURCE 009_create_media_table.sql;
SOURCE 010_create_verification_logs_table.sql;
SOURCE 011_create_analytics_table.sql;
SOURCE 012_create_settings_table.sql;
SOURCE 013_create_sponsors_table.sql;
SOURCE 014_create_seo_settings_table.sql;
SOURCE 015_create_smtp_settings_table.sql;
SOURCE 016_create_api_keys_table.sql;
SOURCE 017_create_ai_collected_data_table.sql;
SOURCE 018_create_scraper_sources_table.sql;
SOURCE 019_create_corruption_cases_table.sql;
SOURCE 020_create_admin_logs_table.sql;
SOURCE 021_create_notifications_table.sql;

SELECT 'NetaTrack India database setup complete!' AS status;
