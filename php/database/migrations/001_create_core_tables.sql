-- ============================================================
-- NetaTrack India — Database Migrations
-- Migration 001: Core Tables
-- Run with: mysql -u root -p netatrack < 001_create_core_tables.sql
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------
-- States (all 28 states + 8 UTs)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS states (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    slug        VARCHAR(100) NOT NULL UNIQUE,
    code        CHAR(3),
    region      ENUM('North','South','East','West','Northeast','Central') DEFAULT 'North',
    capital     VARCHAR(100),
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO states (name, slug, code, region) VALUES
('Andhra Pradesh','andhra-pradesh','AP','South'),
('Arunachal Pradesh','arunachal-pradesh','AR','Northeast'),
('Assam','assam','AS','Northeast'),
('Bihar','bihar','BR','East'),
('Chhattisgarh','chhattisgarh','CG','Central'),
('Goa','goa','GA','West'),
('Gujarat','gujarat','GJ','West'),
('Haryana','haryana','HR','North'),
('Himachal Pradesh','himachal-pradesh','HP','North'),
('Jharkhand','jharkhand','JH','East'),
('Karnataka','karnataka','KA','South'),
('Kerala','kerala','KL','South'),
('Madhya Pradesh','madhya-pradesh','MP','Central'),
('Maharashtra','maharashtra','MH','West'),
('Manipur','manipur','MN','Northeast'),
('Meghalaya','meghalaya','ML','Northeast'),
('Mizoram','mizoram','MZ','Northeast'),
('Nagaland','nagaland','NL','Northeast'),
('Odisha','odisha','OD','East'),
('Punjab','punjab','PB','North'),
('Rajasthan','rajasthan','RJ','North'),
('Sikkim','sikkim','SK','Northeast'),
('Tamil Nadu','tamil-nadu','TN','South'),
('Telangana','telangana','TS','South'),
('Tripura','tripura','TR','Northeast'),
('Uttar Pradesh','uttar-pradesh','UP','North'),
('Uttarakhand','uttarakhand','UK','North'),
('West Bengal','west-bengal','WB','East'),
-- Union Territories
('Delhi','delhi','DL','North'),
('Jammu & Kashmir','jammu-kashmir','JK','North'),
('Ladakh','ladakh','LA','North'),
('Chandigarh','chandigarh','CH','North'),
('Puducherry','puducherry','PY','South'),
('Andaman & Nicobar','andaman-nicobar','AN','East'),
('Lakshadweep','lakshadweep','LD','South'),
('Dadra & Nagar Haveli','dadra-nagar-haveli','DN','West');

-- ------------------------------------------------------------
-- Parties
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS parties (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(150) NOT NULL,
    abbreviation VARCHAR(20),
    color        VARCHAR(20) DEFAULT '#3b82f6',
    founded_year YEAR,
    ideology     VARCHAR(200),
    created_at   DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO parties (name, abbreviation, color) VALUES
('Bharatiya Janata Party',           'BJP',  '#f97316'),
('Indian National Congress',          'INC',  '#3b82f6'),
('Aam Aadmi Party',                   'AAP',  '#22c55e'),
('Trinamool Congress',                'TMC',  '#06b6d4'),
('Samajwadi Party',                   'SP',   '#ef4444'),
('Bahujan Samaj Party',               'BSP',  '#8b5cf6'),
('Shiv Sena',                         'SS',   '#f59e0b'),
('Nationalist Congress Party',        'NCP',  '#64748b'),
('Communist Party of India (Marxist)','CPM',  '#dc2626'),
('Dravida Munnetra Kazhagam',         'DMK',  '#1e40af'),
('Telugu Desam Party',                'TDP',  '#fbbf24'),
('YSR Congress Party',                'YSRCP','#a855f7'),
('Independent',                       'IND',  '#64748b');

-- ------------------------------------------------------------
-- Users
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(150) NOT NULL,
    email      VARCHAR(191) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    role       ENUM('user','admin','moderator') DEFAULT 'user',
    status     ENUM('active','banned','pending') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role  (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default admin (password: Admin@123)
INSERT IGNORE INTO users (name, email, password, role) VALUES
('NetaTrack Admin', 'admin@netatrack.in',
 '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- ------------------------------------------------------------
-- Leaders
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS leaders (
    id                          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name                        VARCHAR(200) NOT NULL,
    slug                        VARCHAR(220) NOT NULL UNIQUE,
    designation                 VARCHAR(200),
    party_id                    INT UNSIGNED,
    state_id                    INT UNSIGNED,
    constituency                VARCHAR(200),
    bio                         TEXT,
    photo                       VARCHAR(500),
    dob                         DATE,
    gender                      ENUM('Male','Female','Other'),
    email                       VARCHAR(191),
    twitter                     VARCHAR(100),
    facebook                    VARCHAR(300),
    website                     VARCHAR(300),
    criminal_cases              SMALLINT UNSIGNED DEFAULT 0,
    assets_declared             DECIMAL(18,2) DEFAULT 0,
    corruption_reports          INT UNSIGNED DEFAULT 0,
    fake_claims                 INT UNSIGNED DEFAULT 0,
    term_start                  DATE,
    term_end                    DATE,
    -- Score fields (0-100)
    score_promise_completion    TINYINT UNSIGNED DEFAULT 50,
    score_project_delivery      TINYINT UNSIGNED DEFAULT 50,
    score_transparency          TINYINT UNSIGNED DEFAULT 50,
    score_public_satisfaction   TINYINT UNSIGNED DEFAULT 50,
    score_attendance            TINYINT UNSIGNED DEFAULT 50,
    score_criminal_record       TINYINT UNSIGNED DEFAULT 50,
    score_assets_declared       TINYINT UNSIGNED DEFAULT 50,
    score_social_media_activity TINYINT UNSIGNED DEFAULT 50,
    total_score                 TINYINT UNSIGNED DEFAULT 50,
    score_rank                  ENUM('Excellent','Good','Average','Poor') DEFAULT 'Average',
    is_verified                 TINYINT(1) DEFAULT 0,
    status                      ENUM('active','inactive','draft') DEFAULT 'active',
    created_at                  DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at                  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (party_id) REFERENCES parties(id) ON DELETE SET NULL,
    FOREIGN KEY (state_id) REFERENCES states(id)  ON DELETE SET NULL,
    INDEX idx_slug       (slug),
    INDEX idx_score      (total_score),
    INDEX idx_state      (state_id),
    INDEX idx_party      (party_id),
    INDEX idx_rank       (score_rank),
    INDEX idx_status     (status),
    FULLTEXT INDEX ft_search (name, designation, constituency, bio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Promises
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS promises (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    leader_id     INT UNSIGNED NOT NULL,
    title         VARCHAR(500) NOT NULL,
    description   TEXT,
    category      VARCHAR(100),
    status        ENUM('in_progress','kept','broken','partial','expired') DEFAULT 'in_progress',
    deadline      DATE,
    source_url    VARCHAR(500),
    ai_confidence TINYINT UNSIGNED DEFAULT 0,
    made_on       DATE,
    verified_on   DATE,
    created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (leader_id) REFERENCES leaders(id) ON DELETE CASCADE,
    INDEX idx_leader (leader_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Projects
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS projects (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    leader_id      INT UNSIGNED,
    state_id       INT UNSIGNED,
    title          VARCHAR(500) NOT NULL,
    description    TEXT,
    status         ENUM('planned','in_progress','completed','delayed','cancelled') DEFAULT 'planned',
    budget_crore   DECIMAL(14,2) DEFAULT 0,
    completion_pct TINYINT UNSIGNED DEFAULT 0,
    start_date     DATE,
    deadline       DATE,
    completed_on   DATE,
    source_url     VARCHAR(500),
    created_at     DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at     DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (leader_id) REFERENCES leaders(id) ON DELETE SET NULL,
    FOREIGN KEY (state_id)  REFERENCES states(id)  ON DELETE SET NULL,
    INDEX idx_leader (leader_id),
    INDEX idx_state  (state_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Reports
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS reports (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    leader_id      INT UNSIGNED,
    state_id       INT UNSIGNED,
    title          VARCHAR(500) NOT NULL,
    description    TEXT NOT NULL,
    type           ENUM('corruption','fake_claim','project_delay','promise_broken','positive','other') DEFAULT 'other',
    status         ENUM('pending','approved','rejected') DEFAULT 'pending',
    evidence_urls  TEXT,
    reporter_name  VARCHAR(150) DEFAULT 'Anonymous',
    reporter_email VARCHAR(191),
    ip_address     VARCHAR(45),
    ai_confidence  TINYINT UNSIGNED DEFAULT 0,
    admin_note     TEXT,
    created_at     DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at     DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (leader_id) REFERENCES leaders(id) ON DELETE SET NULL,
    FOREIGN KEY (state_id)  REFERENCES states(id)  ON DELETE SET NULL,
    INDEX idx_status    (status),
    INDEX idx_leader    (leader_id),
    INDEX idx_type      (type),
    INDEX idx_created   (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Settings
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS settings (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `key`      VARCHAR(100) NOT NULL UNIQUE,
    `value`    TEXT,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_key (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO settings (`key`, `value`) VALUES
('site_name',             'NetaTrack India'),
('site_tagline',          'Tracking Political Accountability'),
('meta_description',      'Track India\'s political leaders, promises, projects and corruption in real-time.'),
('ai_enabled',            '0'),
('registration_enabled',  '1'),
('maintenance_mode',      '0'),
('gemini_api_key',        ''),
('sarvam_api_key',        ''),
('smtp_host',             ''),
('smtp_port',             '587'),
('smtp_user',             ''),
('smtp_pass',             '');

-- ------------------------------------------------------------
-- Scraper Jobs
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS scraper_jobs (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    source_name VARCHAR(200) NOT NULL,
    source_url  VARCHAR(500) NOT NULL,
    type        ENUM('rss','web','api') DEFAULT 'rss',
    status      ENUM('pending','running','completed','failed') DEFAULT 'pending',
    items_found INT UNSIGNED DEFAULT 0,
    error_msg   TEXT,
    started_at  DATETIME,
    finished_at DATETIME,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
