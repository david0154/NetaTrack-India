-- NetaTrack India: Phase 1 Migration 002
-- States and Parties Tables

CREATE TABLE IF NOT EXISTS `states` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`        VARCHAR(100)  NOT NULL,
    `slug`        VARCHAR(120)  NOT NULL UNIQUE,
    `code`        VARCHAR(5)    NOT NULL UNIQUE,
    `region`      VARCHAR(100)  DEFAULT NULL,
    `capital`     VARCHAR(100)  DEFAULT NULL,
    `cm_name`     VARCHAR(150)  DEFAULT NULL,
    `cm_party`    VARCHAR(100)  DEFAULT NULL,
    `total_mlas`  SMALLINT      DEFAULT 0,
    `total_mps`   SMALLINT      DEFAULT 0,
    `latitude`    DECIMAL(10,7) DEFAULT NULL,
    `longitude`   DECIMAL(10,7) DEFAULT NULL,
    `map_color`   VARCHAR(20)   DEFAULT '#3b82f6',
    `is_ut`       TINYINT(1)    DEFAULT 0,
    `status`      ENUM('active','inactive') DEFAULT 'active',
    `created_at`  DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `parties` (
    `id`                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`              VARCHAR(200)  NOT NULL,
    `slug`              VARCHAR(220)  NOT NULL UNIQUE,
    `abbreviation`      VARCHAR(20)   DEFAULT NULL,
    `founded_year`      YEAR          DEFAULT NULL,
    `ideology`          VARCHAR(200)  DEFAULT NULL,
    `logo`              VARCHAR(500)  DEFAULT NULL,
    `color_code`        VARCHAR(20)   DEFAULT '#3b82f6',
    `website`           VARCHAR(300)  DEFAULT NULL,
    `national_or_state` ENUM('national','state','regional') DEFAULT 'state',
    `status`            ENUM('active','inactive') DEFAULT 'active',
    `created_at`        DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed: All Indian States & UTs
INSERT INTO `states` (`name`,`slug`,`code`,`capital`,`is_ut`,`latitude`,`longitude`,`map_color`) VALUES
('Andhra Pradesh','andhra-pradesh','AP','Amaravati',0,15.9129,79.7400,'#f97316'),
('Arunachal Pradesh','arunachal-pradesh','AR','Itanagar',0,28.2180,94.7278,'#84cc16'),
('Assam','assam','AS','Dispur',0,26.2006,92.9376,'#22c55e'),
('Bihar','bihar','BR','Patna',0,25.0961,85.3131,'#eab308'),
('Chhattisgarh','chhattisgarh','CG','Raipur',0,21.2787,81.8661,'#f59e0b'),
('Goa','goa','GA','Panaji',0,15.2993,74.1240,'#06b6d4'),
('Gujarat','gujarat','GJ','Gandhinagar',0,22.2587,71.1924,'#8b5cf6'),
('Haryana','haryana','HR','Chandigarh',0,29.0588,76.0856,'#ec4899'),
('Himachal Pradesh','himachal-pradesh','HP','Shimla',0,31.1048,77.1734,'#14b8a6'),
('Jharkhand','jharkhand','JH','Ranchi',0,23.6102,85.2799,'#f43f5e'),
('Karnataka','karnataka','KA','Bengaluru',0,15.3173,75.7139,'#a855f7'),
('Kerala','kerala','KL','Thiruvananthapuram',0,10.8505,76.2711,'#10b981'),
('Madhya Pradesh','madhya-pradesh','MP','Bhopal',0,22.9734,78.6569,'#3b82f6'),
('Maharashtra','maharashtra','MH','Mumbai',0,19.7515,75.7139,'#f97316'),
('Manipur','manipur','MN','Imphal',0,24.6637,93.9063,'#6366f1'),
('Meghalaya','meghalaya','ML','Shillong',0,25.4670,91.3662,'#0ea5e9'),
('Mizoram','mizoram','MZ','Aizawl',0,23.1645,92.9376,'#d946ef'),
('Nagaland','nagaland','NL','Kohima',0,26.1584,94.5624,'#f59e0b'),
('Odisha','odisha','OD','Bhubaneswar',0,20.9517,85.0985,'#22d3ee'),
('Punjab','punjab','PB','Chandigarh',0,31.1471,75.3412,'#4ade80'),
('Rajasthan','rajasthan','RJ','Jaipur',0,27.0238,74.2179,'#fb923c'),
('Sikkim','sikkim','SK','Gangtok',0,27.5330,88.5122,'#a78bfa'),
('Tamil Nadu','tamil-nadu','TN','Chennai',0,11.1271,78.6569,'#2dd4bf'),
('Telangana','telangana','TS','Hyderabad',0,18.1124,79.0193,'#e879f9'),
('Tripura','tripura','TR','Agartala',0,23.9408,91.9882,'#fbbf24'),
('Uttar Pradesh','uttar-pradesh','UP','Lucknow',0,26.8467,80.9462,'#60a5fa'),
('Uttarakhand','uttarakhand','UK','Dehradun',0,30.0668,79.0193,'#4ade80'),
('West Bengal','west-bengal','WB','Kolkata',0,22.9868,87.8550,'#f87171'),
('Delhi','delhi','DL','New Delhi',1,28.7041,77.1025,'#c084fc'),
('Jammu & Kashmir','jammu-kashmir','JK','Srinagar',1,33.7782,76.5762,'#38bdf8'),
('Ladakh','ladakh','LA','Leh',1,34.1526,77.5770,'#fb7185'),
('Chandigarh','chandigarh','CH','Chandigarh',1,30.7333,76.7794,'#a3e635'),
('Puducherry','puducherry','PY','Puducherry',1,11.9416,79.8083,'#f472b6'),
('Andaman & Nicobar','andaman-nicobar','AN','Port Blair',1,11.7401,92.6586,'#34d399'),
('Dadra & Nagar Haveli','dadra-nagar-haveli','DN','Silvassa',1,20.1809,73.0169,'#fcd34d'),
('Daman & Diu','daman-diu','DD','Daman',1,20.4283,72.8397,'#67e8f9'),
('Lakshadweep','lakshadweep','LD','Kavaratti',1,10.5667,72.6417,'#86efac')
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Seed: Major National/State Parties
INSERT INTO `parties` (`name`,`slug`,`abbreviation`,`founded_year`,`color_code`,`national_or_state`) VALUES
('Bharatiya Janata Party','bharatiya-janata-party','BJP',1980,'#f97316','national'),
('Indian National Congress','indian-national-congress','INC',1885,'#22c55e','national'),
('Aam Aadmi Party','aam-aadmi-party','AAP',2012,'#3b82f6','national'),
('Bahujan Samaj Party','bahujan-samaj-party','BSP',1984,'#3b82f6','national'),
('Samajwadi Party','samajwadi-party','SP',1992,'#ef4444','state'),
('Trinamool Congress','trinamool-congress','TMC',1998,'#22c55e','state'),
('Dravida Munnetra Kazhagam','dravida-munnetra-kazhagam','DMK',1949,'#ef4444','state'),
('All India Anna Dravida Munnetra Kazhagam','aiadmk','AIADMK',1972,'#f97316','state'),
('Telugu Desam Party','telugu-desam-party','TDP',1982,'#eab308','state'),
('Shiv Sena','shiv-sena','SS',1966,'#f97316','state'),
('Nationalist Congress Party','nationalist-congress-party','NCP',1999,'#3b82f6','state'),
('Janata Dal (United)','janata-dal-united','JDU',2003,'#22c55e','state'),
('Rashtriya Janata Dal','rashtriya-janata-dal','RJD',1997,'#22c55e','state'),
('Communist Party of India (Marxist)','cpi-marxist','CPM',1964,'#ef4444','national'),
('Biju Janata Dal','biju-janata-dal','BJD',1997,'#22c55e','state'),
('YSR Congress Party','ysr-congress-party','YSRCP',2011,'#3b82f6','state')
ON DUPLICATE KEY UPDATE name=VALUES(name);
