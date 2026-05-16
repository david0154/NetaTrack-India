-- NetaTrack India — Complete Database Schema
-- Phase 1: All 18 production tables

SET FOREIGN_KEY_CHECKS = 0;

-- ─────────────────────────────────────────
-- ROLES
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(60) NOT NULL UNIQUE,
    permissions JSON NOT NULL DEFAULT ('[]'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT IGNORE INTO roles (name, permissions) VALUES
  ('super_admin', '["all"]'),
  ('admin', '["content","reports","leaders"]'),
  ('moderator', '["reports"]'),
  ('viewer', '["read"]');

-- ─────────────────────────────────────────
-- USERS
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role_id INT NOT NULL DEFAULT 4,
    credibility_score INT NOT NULL DEFAULT 50,
    is_banned TINYINT(1) NOT NULL DEFAULT 0,
    ban_reason VARCHAR(255) DEFAULT NULL,
    email_verified TINYINT(1) NOT NULL DEFAULT 0,
    verification_token VARCHAR(100) DEFAULT NULL,
    reset_token VARCHAR(100) DEFAULT NULL,
    reset_token_expires TIMESTAMP NULL DEFAULT NULL,
    last_login TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id)
);

-- ─────────────────────────────────────────
-- STATES
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS states (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    code VARCHAR(10) NOT NULL UNIQUE,
    region VARCHAR(80) DEFAULT NULL,
    population BIGINT DEFAULT NULL,
    capital VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT IGNORE INTO states (name, code, region) VALUES
  ('Andhra Pradesh','AP','South'),('Arunachal Pradesh','AR','Northeast'),
  ('Assam','AS','Northeast'),('Bihar','BR','East'),
  ('Chhattisgarh','CG','Central'),('Goa','GA','West'),
  ('Gujarat','GJ','West'),('Haryana','HR','North'),
  ('Himachal Pradesh','HP','North'),('Jharkhand','JH','East'),
  ('Karnataka','KA','South'),('Kerala','KL','South'),
  ('Madhya Pradesh','MP','Central'),('Maharashtra','MH','West'),
  ('Manipur','MN','Northeast'),('Meghalaya','ML','Northeast'),
  ('Mizoram','MZ','Northeast'),('Nagaland','NL','Northeast'),
  ('Odisha','OD','East'),('Punjab','PB','North'),
  ('Rajasthan','RJ','North'),('Sikkim','SK','Northeast'),
  ('Tamil Nadu','TN','South'),('Telangana','TS','South'),
  ('Tripura','TR','Northeast'),('Uttar Pradesh','UP','North'),
  ('Uttarakhand','UK','North'),('West Bengal','WB','East'),
  ('Delhi','DL','North'),('Jammu & Kashmir','JK','North'),
  ('Ladakh','LA','North'),('Puducherry','PY','South'),
  ('Chandigarh','CH','North'),('National','IN','All');

-- ─────────────────────────────────────────
-- PARTIES
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS parties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL UNIQUE,
    abbreviation VARCHAR(20) NOT NULL,
    symbol_url VARCHAR(500) DEFAULT NULL,
    founded_year INT DEFAULT NULL,
    ideology VARCHAR(255) DEFAULT NULL,
    headquarters VARCHAR(255) DEFAULT NULL,
    website_url VARCHAR(500) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT IGNORE INTO parties (name, abbreviation, ideology) VALUES
  ('Bharatiya Janata Party','BJP','Right-wing nationalism'),
  ('Indian National Congress','INC','Centre-left secularism'),
  ('Aam Aadmi Party','AAP','Centre populism'),
  ('All India Trinamool Congress','TMC','Centre-left'),
  ('Dravida Munnetra Kazhagam','DMK','Dravidian regionalism'),
  ('All India Anna Dravida Munnetra Kazhagam','AIADMK','Dravidian regionalism'),
  ('Telugu Desam Party','TDP','Regional Telugu nationalism'),
  ('YSR Congress Party','YSRCP','Regional Andhra'),
  ('Samajwadi Party','SP','Left social democracy'),
  ('Bahujan Samaj Party','BSP','Dalit rights'),
  ('Shiv Sena','SS','Hindutva regionalism'),
  ('Nationalist Congress Party','NCP','Centre nationalism'),
  ('Communist Party of India (Marxist)','CPI(M)','Marxism'),
  ('Janata Dal (United)','JD(U)','Centre regional'),
  ('Rashtriya Janata Dal','RJD','Left social justice'),
  ('Biju Janata Dal','BJD','Regional Odisha'),
  ('Independent','IND','Independent'),
  ('Other','OTH','Varies');

-- ─────────────────────────────────────────
-- LEADERS
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS leaders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(200) NOT NULL UNIQUE,
    name VARCHAR(200) NOT NULL,
    party_id INT DEFAULT NULL,
    state_id INT DEFAULT NULL,
    position VARCHAR(200) DEFAULT NULL,
    photo_url VARCHAR(500) DEFAULT NULL,
    birth_date DATE DEFAULT NULL,
    education TEXT DEFAULT NULL,
    bio TEXT DEFAULT NULL,
    social_twitter VARCHAR(255) DEFAULT NULL,
    social_facebook VARCHAR(255) DEFAULT NULL,
    official_website VARCHAR(500) DEFAULT NULL,
    promise_score DECIMAL(5,2) DEFAULT 0,
    project_score DECIMAL(5,2) DEFAULT 0,
    transparency_score DECIMAL(5,2) DEFAULT 0,
    public_score DECIMAL(5,2) DEFAULT 0,
    final_score DECIMAL(5,2) DEFAULT 0,
    rank_label VARCHAR(20) DEFAULT 'Unranked',
    corruption_level INT DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (party_id) REFERENCES parties(id),
    FOREIGN KEY (state_id) REFERENCES states(id)
);

-- ─────────────────────────────────────────
-- PROJECTS
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(200) NOT NULL UNIQUE,
    title VARCHAR(300) NOT NULL,
    description LONGTEXT DEFAULT NULL,
    category VARCHAR(120) DEFAULT NULL,
    state_id INT DEFAULT NULL,
    leader_id INT DEFAULT NULL,
    budget DECIMAL(20,2) DEFAULT NULL,
    spent DECIMAL(20,2) DEFAULT 0,
    start_date DATE DEFAULT NULL,
    expected_end_date DATE DEFAULT NULL,
    actual_end_date DATE DEFAULT NULL,
    status ENUM('not_started','in_progress','completed','delayed','cancelled') DEFAULT 'not_started',
    progress_percent INT DEFAULT 0,
    tender_id VARCHAR(200) DEFAULT NULL,
    source_url VARCHAR(500) DEFAULT NULL,
    verification_score INT DEFAULT 0,
    created_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (state_id) REFERENCES states(id),
    FOREIGN KEY (leader_id) REFERENCES leaders(id)
);

-- ─────────────────────────────────────────
-- PROMISES
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS promises (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(200) NOT NULL UNIQUE,
    title VARCHAR(300) NOT NULL,
    description LONGTEXT NOT NULL,
    leader_id INT DEFAULT NULL,
    state_id INT DEFAULT NULL,
    category VARCHAR(120) NOT NULL,
    budget DECIMAL(20,2) DEFAULT NULL,
    deadline DATE DEFAULT NULL,
    promise_date DATE DEFAULT NULL,
    status ENUM('pending','in_progress','completed','failed','delayed','fake') DEFAULT 'pending',
    verification_score INT DEFAULT 0,
    ai_confidence INT DEFAULT 0,
    source_name VARCHAR(200) NOT NULL,
    source_url VARCHAR(500) NOT NULL,
    fact_check_notes TEXT DEFAULT NULL,
    created_by INT DEFAULT NULL,
    approved_by INT DEFAULT NULL,
    approved_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (leader_id) REFERENCES leaders(id),
    FOREIGN KEY (state_id) REFERENCES states(id)
);

-- ─────────────────────────────────────────
-- PUBLIC SUBMISSIONS
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS public_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(300) NOT NULL,
    leader_name VARCHAR(200) NOT NULL,
    leader_id INT DEFAULT NULL,
    state_id INT DEFAULT NULL,
    state VARCHAR(120) NOT NULL,
    description LONGTEXT NOT NULL,
    submission_type ENUM('corruption','fake_claim','complaint','infrastructure','project_update','other') DEFAULT 'other',
    source_link VARCHAR(500) DEFAULT NULL,
    media_paths JSON DEFAULT NULL,
    ai_spam_score INT DEFAULT 0,
    ai_fake_score INT DEFAULT 0,
    ai_verified TINYINT(1) DEFAULT 0,
    duplicate_of INT DEFAULT NULL,
    status ENUM('pending','approved','rejected','duplicate','under_review') DEFAULT 'pending',
    review_notes TEXT DEFAULT NULL,
    submitted_by INT DEFAULT NULL,
    reviewed_by INT DEFAULT NULL,
    reviewed_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (leader_id) REFERENCES leaders(id)
);

-- ─────────────────────────────────────────
-- MEDIA
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS media (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(300) NOT NULL,
    original_name VARCHAR(300) NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    size_bytes BIGINT NOT NULL,
    storage_path VARCHAR(600) NOT NULL,
    public_url VARCHAR(600) DEFAULT NULL,
    related_type ENUM('submission','leader','project','promise','other') DEFAULT 'other',
    related_id INT DEFAULT NULL,
    is_fake TINYINT(1) DEFAULT 0,
    ai_analysis JSON DEFAULT NULL,
    uploaded_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ─────────────────────────────────────────
-- VERIFICATION LOGS
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS verification_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entity_type ENUM('promise','project','submission','leader','ai_data') NOT NULL,
    entity_id INT NOT NULL,
    action ENUM('ai_check','admin_approve','admin_reject','fact_check','duplicate_check','spam_check') NOT NULL,
    result VARCHAR(50) DEFAULT NULL,
    confidence INT DEFAULT 0,
    notes TEXT DEFAULT NULL,
    performed_by INT DEFAULT NULL,
    ai_model VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ─────────────────────────────────────────
-- AI COLLECTED DATA (Scraper output)
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS ai_collected_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    source_name VARCHAR(200) NOT NULL,
    source_url VARCHAR(600) DEFAULT NULL,
    title VARCHAR(300) NOT NULL,
    content LONGTEXT DEFAULT NULL,
    extracted_leader VARCHAR(200) DEFAULT NULL,
    extracted_state VARCHAR(120) DEFAULT NULL,
    extracted_category VARCHAR(120) DEFAULT NULL,
    ai_score INT DEFAULT 0,
    ai_model VARCHAR(100) DEFAULT NULL,
    is_duplicate TINYINT(1) DEFAULT 0,
    status ENUM('pending_review','approved','rejected','failed_ai','duplicate') DEFAULT 'pending_review',
    scraper_run_id VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ─────────────────────────────────────────
-- SCRAPER SOURCES
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS scraper_sources (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    url VARCHAR(600) NOT NULL,
    scraper_type ENUM('static','dynamic','pdf','social','rss','api') DEFAULT 'static',
    category VARCHAR(120) DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    last_scraped TIMESTAMP NULL DEFAULT NULL,
    scrape_interval_hours INT DEFAULT 24,
    success_count INT DEFAULT 0,
    fail_count INT DEFAULT 0,
    config JSON DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT IGNORE INTO scraper_sources (name, url, scraper_type, category) VALUES
  ('PIB India', 'https://pib.gov.in', 'static', 'Government'),
  ('Rajya Sabha TV', 'https://rajyasabhatv.nic.in', 'static', 'Parliament'),
  ('Election Commission', 'https://eci.gov.in', 'static', 'Elections'),
  ('India Budget', 'https://www.indiabudget.gov.in', 'pdf', 'Budget'),
  ('PRS Legislative Research', 'https://prsindia.org', 'static', 'Parliament');

-- ─────────────────────────────────────────
-- CORRUPTION CASES
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS corruption_cases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    leader_id INT DEFAULT NULL,
    title VARCHAR(300) NOT NULL,
    description LONGTEXT DEFAULT NULL,
    agency ENUM('CBI','ED','IT','ACB','Court','Media','RTI','Other') DEFAULT 'Other',
    case_number VARCHAR(200) DEFAULT NULL,
    amount_crore DECIMAL(20,2) DEFAULT NULL,
    status ENUM('alleged','under_investigation','chargesheeted','convicted','acquitted','closed') DEFAULT 'alleged',
    severity ENUM('low','medium','high','critical') DEFAULT 'low',
    source_url VARCHAR(600) DEFAULT NULL,
    court_docs_url VARCHAR(600) DEFAULT NULL,
    reported_date DATE DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (leader_id) REFERENCES leaders(id)
);

-- ─────────────────────────────────────────
-- ANALYTICS
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_type VARCHAR(100) NOT NULL,
    entity_type VARCHAR(60) DEFAULT NULL,
    entity_id INT DEFAULT NULL,
    user_id INT DEFAULT NULL,
    ip_hash VARCHAR(64) DEFAULT NULL,
    user_agent VARCHAR(500) DEFAULT NULL,
    referrer VARCHAR(500) DEFAULT NULL,
    meta JSON DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_event (event_type),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_created (created_at)
);

-- ─────────────────────────────────────────
-- SETTINGS (CMS)
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(100) NOT NULL UNIQUE,
    value LONGTEXT DEFAULT NULL,
    type ENUM('string','json','boolean','integer') DEFAULT 'string',
    group_name VARCHAR(60) DEFAULT 'general',
    label VARCHAR(200) DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT IGNORE INTO settings (`key`, value, type, group_name, label) VALUES
  ('site_name','NetaTrack India','string','general','Site Name'),
  ('site_tagline','Political Transparency Platform','string','general','Tagline'),
  ('site_logo','','string','general','Logo URL'),
  ('site_favicon','','string','general','Favicon URL'),
  ('site_email','admin@netatrack.in','string','general','Admin Email'),
  ('maintenance_mode','false','boolean','general','Maintenance Mode'),
  ('google_analytics_id','','string','analytics','Google Analytics ID'),
  ('meta_pixel_id','','string','analytics','Meta Pixel ID'),
  ('smtp_host','','string','smtp','SMTP Host'),
  ('smtp_port','587','integer','smtp','SMTP Port'),
  ('smtp_user','','string','smtp','SMTP Username'),
  ('smtp_pass','','string','smtp','SMTP Password'),
  ('smtp_from','noreply@netatrack.in','string','smtp','From Email'),
  ('google_ads_client','','string','ads','Google Ads Client ID'),
  ('google_ads_slot_home','','string','ads','Home Page Ad Slot'),
  ('gemini_api_key','','string','ai','Gemini API Key'),
  ('openai_api_key','','string','ai','OpenAI API Key'),
  ('openrouter_api_key','','string','ai','OpenRouter API Key'),
  ('ai_confidence_threshold','75','integer','ai','Min AI Confidence to Auto-Queue'),
  ('theme_primary_color','#f97316','string','theme','Primary Color'),
  ('dark_mode_default','true','boolean','theme','Dark Mode Default');

-- ─────────────────────────────────────────
-- SPONSORS / ADVERTISEMENTS
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS sponsors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    logo_url VARCHAR(500) DEFAULT NULL,
    website_url VARCHAR(500) DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS advertisements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sponsor_id INT DEFAULT NULL,
    title VARCHAR(200) NOT NULL,
    ad_type ENUM('banner','sidebar','inline','video','google_adsense') DEFAULT 'banner',
    placement VARCHAR(100) DEFAULT NULL,
    image_url VARCHAR(500) DEFAULT NULL,
    target_url VARCHAR(500) DEFAULT NULL,
    ad_code LONGTEXT DEFAULT NULL,
    impressions INT DEFAULT 0,
    clicks INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    start_date DATE DEFAULT NULL,
    end_date DATE DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sponsor_id) REFERENCES sponsors(id)
);

-- ─────────────────────────────────────────
-- SEO SETTINGS
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS seo_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    page_path VARCHAR(300) NOT NULL UNIQUE,
    title VARCHAR(300) DEFAULT NULL,
    description VARCHAR(500) DEFAULT NULL,
    keywords TEXT DEFAULT NULL,
    og_title VARCHAR(300) DEFAULT NULL,
    og_description VARCHAR(500) DEFAULT NULL,
    og_image VARCHAR(500) DEFAULT NULL,
    canonical_url VARCHAR(500) DEFAULT NULL,
    no_index TINYINT(1) DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT IGNORE INTO seo_settings (page_path, title, description) VALUES
  ('/','NetaTrack India — Political Transparency Platform','Track promises, projects, and corruption allegations across all Indian states'),
  ('/leaders','Leaders | NetaTrack India','Browse and track political leaders performance scores across India'),
  ('/promises','Promises | NetaTrack India','Track political promises and their fulfilment status'),
  ('/submit','Submit Report | NetaTrack India','Submit corruption reports and public complaints for verification');

-- ─────────────────────────────────────────
-- API KEYS (for external integrations)
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS api_keys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    key_hash VARCHAR(255) NOT NULL UNIQUE,
    permissions JSON DEFAULT NULL,
    user_id INT DEFAULT NULL,
    last_used TIMESTAMP NULL DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

SET FOREIGN_KEY_CHECKS = 1;
