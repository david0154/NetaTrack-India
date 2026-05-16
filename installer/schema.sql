-- NetaTrack India - Complete Database Schema
-- Phase 1: All 20 tables

CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(60) NOT NULL UNIQUE,
    permissions JSON NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(40) NOT NULL DEFAULT 'user',
    credibility_score INT NOT NULL DEFAULT 0,
    approved_reports INT NOT NULL DEFAULT 0,
    is_banned TINYINT(1) NOT NULL DEFAULT 0,
    ban_reason TEXT DEFAULT NULL,
    email_verified TINYINT(1) NOT NULL DEFAULT 0,
    email_verify_token VARCHAR(100) DEFAULT NULL,
    reset_token VARCHAR(100) DEFAULT NULL,
    reset_token_expires DATETIME DEFAULT NULL,
    last_login DATETIME DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS parties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    abbreviation VARCHAR(20) NOT NULL,
    ideology VARCHAR(255) DEFAULT NULL,
    founded_year INT DEFAULT NULL,
    logo_url VARCHAR(500) DEFAULT NULL,
    website VARCHAR(500) DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS states (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL UNIQUE,
    code VARCHAR(10) NOT NULL UNIQUE,
    region VARCHAR(60) DEFAULT NULL,
    capital VARCHAR(120) DEFAULT NULL,
    population BIGINT DEFAULT NULL,
    current_cm VARCHAR(160) DEFAULT NULL,
    current_party_id INT DEFAULT NULL,
    budget_year VARCHAR(20) DEFAULT NULL,
    budget_amount DECIMAL(20,2) DEFAULT NULL,
    latitude DECIMAL(10,6) DEFAULT NULL,
    longitude DECIMAL(10,6) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (current_party_id) REFERENCES parties(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS leaders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(160) NOT NULL,
    slug VARCHAR(160) NOT NULL UNIQUE,
    party_id INT DEFAULT NULL,
    state_id INT DEFAULT NULL,
    position VARCHAR(160) DEFAULT NULL,
    bio TEXT DEFAULT NULL,
    photo_url VARCHAR(500) DEFAULT NULL,
    date_of_birth DATE DEFAULT NULL,
    education VARCHAR(500) DEFAULT NULL,
    constituency VARCHAR(160) DEFAULT NULL,
    twitter_handle VARCHAR(60) DEFAULT NULL,
    promise_completion_score DECIMAL(5,2) DEFAULT 0,
    project_delivery_score DECIMAL(5,2) DEFAULT 0,
    budget_efficiency_score DECIMAL(5,2) DEFAULT 0,
    public_satisfaction_score DECIMAL(5,2) DEFAULT 0,
    transparency_score DECIMAL(5,2) DEFAULT 0,
    corruption_penalty DECIMAL(5,2) DEFAULT 0,
    fake_claim_penalty DECIMAL(5,2) DEFAULT 0,
    verification_trust_score DECIMAL(5,2) DEFAULT 0,
    final_score DECIMAL(5,2) DEFAULT 0,
    rank_label VARCHAR(20) DEFAULT 'Poor',
    corruption_level VARCHAR(20) DEFAULT 'Very Clean',
    total_promises INT DEFAULT 0,
    completed_promises INT DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (party_id) REFERENCES parties(id) ON DELETE SET NULL,
    FOREIGN KEY (state_id) REFERENCES states(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS promises (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(220) NOT NULL UNIQUE,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    leader_id INT DEFAULT NULL,
    state_id INT DEFAULT NULL,
    party_id INT DEFAULT NULL,
    category VARCHAR(120) NOT NULL,
    budget DECIMAL(20,2) DEFAULT NULL,
    deadline DATE DEFAULT NULL,
    made_on DATE DEFAULT NULL,
    status ENUM('pending','in_progress','completed','delayed','broken','fake') NOT NULL DEFAULT 'pending',
    verification_score INT DEFAULT 0,
    ai_confidence INT DEFAULT 0,
    source_name VARCHAR(160) NOT NULL,
    source_url VARCHAR(500) NOT NULL,
    fact_check_notes TEXT DEFAULT NULL,
    created_by INT DEFAULT NULL,
    approved_by INT DEFAULT NULL,
    approved_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (leader_id) REFERENCES leaders(id) ON DELETE SET NULL,
    FOREIGN KEY (state_id) REFERENCES states(id) ON DELETE SET NULL,
    FOREIGN KEY (party_id) REFERENCES parties(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(220) NOT NULL UNIQUE,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    leader_id INT DEFAULT NULL,
    state_id INT DEFAULT NULL,
    party_id INT DEFAULT NULL,
    category VARCHAR(120) NOT NULL,
    total_budget DECIMAL(20,2) DEFAULT NULL,
    spent_budget DECIMAL(20,2) DEFAULT 0,
    start_date DATE DEFAULT NULL,
    expected_end_date DATE DEFAULT NULL,
    actual_end_date DATE DEFAULT NULL,
    status ENUM('planned','in_progress','completed','delayed','cancelled','stalled') NOT NULL DEFAULT 'planned',
    progress_percent INT DEFAULT 0,
    contractor_name VARCHAR(255) DEFAULT NULL,
    tender_id VARCHAR(120) DEFAULT NULL,
    location_lat DECIMAL(10,6) DEFAULT NULL,
    location_lng DECIMAL(10,6) DEFAULT NULL,
    source_url VARCHAR(500) DEFAULT NULL,
    verification_score INT DEFAULT 0,
    ai_confidence INT DEFAULT 0,
    created_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (leader_id) REFERENCES leaders(id) ON DELETE SET NULL,
    FOREIGN KEY (state_id) REFERENCES states(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS corruption_cases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    leader_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    agency ENUM('ED','CBI','Police','Court','RTI','Media','Public') NOT NULL,
    case_number VARCHAR(120) DEFAULT NULL,
    filed_on DATE DEFAULT NULL,
    current_status ENUM('under_investigation','chargesheeted','acquitted','convicted','pending','closed') NOT NULL DEFAULT 'pending',
    severity ENUM('minor','moderate','serious','critical') NOT NULL DEFAULT 'moderate',
    amount_involved DECIMAL(20,2) DEFAULT NULL,
    source_url VARCHAR(500) DEFAULT NULL,
    ai_confidence INT DEFAULT 0,
    verified TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (leader_id) REFERENCES leaders(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS public_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    type ENUM('corruption','fake_claim','complaint','infrastructure','project_update','other') NOT NULL DEFAULT 'other',
    title VARCHAR(255) NOT NULL,
    leader_name VARCHAR(160) NOT NULL,
    leader_id INT DEFAULT NULL,
    state_id INT DEFAULT NULL,
    description TEXT NOT NULL,
    source_link VARCHAR(500) DEFAULT NULL,
    media_urls JSON DEFAULT NULL,
    ai_spam_score INT DEFAULT 0,
    ai_verification_score INT DEFAULT 0,
    duplicate_of INT DEFAULT NULL,
    status ENUM('pending','ai_review','admin_review','approved','rejected','duplicate','investigating') NOT NULL DEFAULT 'pending',
    rejection_reason TEXT DEFAULT NULL,
    admin_notes TEXT DEFAULT NULL,
    reviewed_by INT DEFAULT NULL,
    reviewed_at DATETIME DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (leader_id) REFERENCES leaders(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS media (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ref_type ENUM('leader','promise','project','corruption','submission','party') NOT NULL,
    ref_id INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_type VARCHAR(60) NOT NULL,
    file_size INT DEFAULT NULL,
    is_deepfake_checked TINYINT(1) DEFAULT 0,
    deepfake_score INT DEFAULT NULL,
    uploaded_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS verification_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ref_type ENUM('promise','project','submission','leader','corruption') NOT NULL,
    ref_id INT NOT NULL,
    action VARCHAR(60) NOT NULL,
    performed_by INT DEFAULT NULL,
    performed_by_type ENUM('admin','ai','system','public') NOT NULL DEFAULT 'system',
    notes TEXT DEFAULT NULL,
    score_before INT DEFAULT NULL,
    score_after INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS ai_collected_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    source_name VARCHAR(160) NOT NULL,
    source_url VARCHAR(500) DEFAULT NULL,
    source_type ENUM('government','party','news','social','election','tender','parliament','other') NOT NULL DEFAULT 'other',
    title VARCHAR(255) NOT NULL,
    content LONGTEXT,
    extracted_entities JSON DEFAULT NULL,
    ai_confidence INT DEFAULT 0,
    ai_provider VARCHAR(60) DEFAULT NULL,
    duplicate_hash VARCHAR(64) DEFAULT NULL,
    status ENUM('pending_review','approved','rejected','duplicate','failed_ai') NOT NULL DEFAULT 'pending_review',
    reviewed_by INT DEFAULT NULL,
    reviewed_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_type VARCHAR(80) NOT NULL,
    ref_type VARCHAR(40) DEFAULT NULL,
    ref_id INT DEFAULT NULL,
    user_id INT DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent VARCHAR(500) DEFAULT NULL,
    metadata JSON DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    key_name VARCHAR(100) NOT NULL UNIQUE,
    value LONGTEXT,
    type ENUM('text','json','bool','number','html') NOT NULL DEFAULT 'text',
    group_name VARCHAR(60) DEFAULT 'general',
    label VARCHAR(160) DEFAULT NULL,
    updated_by INT DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS sponsors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    logo_url VARCHAR(500) DEFAULT NULL,
    website VARCHAR(500) DEFAULT NULL,
    tier ENUM('platinum','gold','silver','bronze') NOT NULL DEFAULT 'bronze',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS advertisements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(160) NOT NULL,
    type ENUM('banner','video','text','sponsored_content','google_adsense') NOT NULL,
    position VARCHAR(60) NOT NULL,
    html_code LONGTEXT DEFAULT NULL,
    image_url VARCHAR(500) DEFAULT NULL,
    target_url VARCHAR(500) DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    impressions INT DEFAULT 0,
    clicks INT DEFAULT 0,
    start_date DATE DEFAULT NULL,
    end_date DATE DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS seo_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    page_path VARCHAR(255) NOT NULL UNIQUE,
    meta_title VARCHAR(255) DEFAULT NULL,
    meta_description VARCHAR(500) DEFAULT NULL,
    meta_keywords TEXT DEFAULT NULL,
    og_image VARCHAR(500) DEFAULT NULL,
    canonical_url VARCHAR(500) DEFAULT NULL,
    no_index TINYINT(1) DEFAULT 0,
    schema_markup LONGTEXT DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS smtp_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    provider ENUM('gmail','custom','sendgrid','mailgun') NOT NULL DEFAULT 'custom',
    host VARCHAR(255) NOT NULL,
    port INT NOT NULL DEFAULT 587,
    username VARCHAR(255) NOT NULL,
    password_encrypted VARCHAR(500) NOT NULL,
    from_email VARCHAR(150) NOT NULL,
    from_name VARCHAR(120) NOT NULL,
    encryption ENUM('tls','ssl','none') NOT NULL DEFAULT 'tls',
    is_active TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS api_keys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    key_hash VARCHAR(255) NOT NULL UNIQUE,
    permissions JSON DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    rate_limit INT DEFAULT 1000,
    calls_today INT DEFAULT 0,
    last_used DATETIME DEFAULT NULL,
    expires_at DATETIME DEFAULT NULL,
    created_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS scraper_sources (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(160) NOT NULL,
    url VARCHAR(500) NOT NULL,
    type ENUM('static','dynamic','social','pdf','rss','api') NOT NULL DEFAULT 'static',
    category ENUM('government','party','news','election','tender','parliament','other') NOT NULL DEFAULT 'news',
    scrape_interval_hours INT DEFAULT 24,
    last_scraped DATETIME DEFAULT NULL,
    last_status ENUM('success','failed','pending') DEFAULT 'pending',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    selector_config JSON DEFAULT NULL,
    ai_confidence_threshold INT DEFAULT 70,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Default data
INSERT IGNORE INTO roles (name, permissions) VALUES
  ('superadmin', '{"all": true}'),
  ('admin', '{"content": true, "reports": true, "scraper": true}'),
  ('moderator', '{"reports": true}'),
  ('user', '{"submit": true}');

INSERT IGNORE INTO settings (key_name, value, type, group_name, label) VALUES
  ('site_name', 'NetaTrack India', 'text', 'general', 'Site Name'),
  ('site_tagline', 'Political Transparency & Public Accountability', 'text', 'general', 'Tagline'),
  ('site_logo', '', 'text', 'general', 'Logo URL'),
  ('site_favicon', '', 'text', 'general', 'Favicon URL'),
  ('dark_mode_default', 'true', 'bool', 'theme', 'Dark Mode Default'),
  ('primary_color', '#f97316', 'text', 'theme', 'Primary Color'),
  ('google_analytics_id', '', 'text', 'analytics', 'Google Analytics ID'),
  ('meta_pixel_id', '', 'text', 'analytics', 'Meta Pixel ID'),
  ('footer_text', '© 2026 NetaTrack India. Empowering Citizens.', 'text', 'general', 'Footer Text'),
  ('ai_auto_approve_threshold', '90', 'number', 'ai', 'AI Auto-Approve Threshold'),
  ('submission_require_login', 'false', 'bool', 'submissions', 'Require Login to Submit'),
  ('maintenance_mode', 'false', 'bool', 'general', 'Maintenance Mode');
