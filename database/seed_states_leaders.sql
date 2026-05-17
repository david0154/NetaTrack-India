-- NetaTrack India — Seed: All Indian States + UTs + Current Key Leaders (May 2026)

SET NAMES utf8mb4;
SET foreign_key_checks = 0;

-- ============================================================
-- 1. PARTIES
-- ============================================================
INSERT IGNORE INTO parties (id, name, abbreviation, color, founded_year, ideology) VALUES
(1,  'Bharatiya Janata Party',          'BJP',    '#FF9933', 1980, 'Hindu nationalism, conservatism'),
(2,  'Indian National Congress',        'INC',    '#138808', 1885, 'Social democracy, secularism'),
(3,  'Aam Aadmi Party',                 'AAP',    '#00BFFF', 2012, 'Anti-corruption, social liberalism'),
(4,  'Samajwadi Party',                 'SP',     '#FF0000', 1992, 'Socialism, secularism'),
(5,  'Bahujan Samaj Party',             'BSP',    '#0000FF', 1984, 'Ambedkarism, social justice'),
(6,  'Trinamool Congress',              'TMC',    '#1C6BA0', 1998, 'Populism, secularism'),
(7,  'Dravida Munnetra Kazhagam',       'DMK',    '#E51E25', 1949, 'Dravidian politics, social justice'),
(8,  'All India Anna DMK',              'AIADMK', '#000000', 1972, 'Dravidian politics, populism'),
(9,  'Telugu Desam Party',              'TDP',    '#FFDE00', 1982, 'Telugu sub-nationalism'),
(10, 'YSR Congress Party',              'YSRCP',  '#1877F2', 2011, 'Populism, welfarism'),
(11, 'Biju Janata Dal',                 'BJD',    '#00FF00', 1997, 'Regional, centrist'),
(12, 'Shiv Sena (UBT)',                 'SHS',    '#FF8C00', 1966, 'Marathi sub-nationalism'),
(13, 'Nationalist Congress Party',      'NCP',    '#0080FF', 1999, 'Social democracy'),
(14, 'Jharkhand Mukti Morcha',          'JMM',    '#008000', 1972, 'Tribal rights, regionalism'),
(15, 'Communist Party of India (M)',    'CPI(M)', '#FF0000', 1964, 'Marxism, communism'),
(16, 'Indian Union Muslim League',      'IUML',   '#006400', 1948, 'Muslim interests, secularism'),
(17, 'Janata Dal (United)',             'JDU',    '#00CCFF', 2003, 'Socialism, secularism'),
(18, 'Rashtriya Janata Dal',            'RJD',    '#228B22', 1997, 'Socialism, OBC politics'),
(19, 'National Conference',             'NC',     '#FF6600', 1932, 'Kashmiri autonomy, secularism'),
(20, 'Peoples Democratic Party',        'PDP',    '#800080', 1999, 'Kashmiri self-rule'),
(21, 'Sikkim Krantikari Morcha',        'SKM',    '#FF69B4', 2013, 'Sikkimese interests'),
(22, 'Mizo National Front',             'MNF',    '#228B22', 1961, 'Mizo identity'),
(23, 'National Peoples Party',          'NPP',    '#FF4500', 2013, 'Meghalaya/Northeast interests'),
(24, 'Zoram People Movement',           'ZPM',    '#4169E1', 2017, 'Mizo people'),
(25, 'Independent',                     'IND',    '#808080', NULL, NULL);

-- ============================================================
-- 2. STATES AND UNION TERRITORIES
-- ============================================================
INSERT IGNORE INTO states (id, name, code, capital, region, total_seats, type) VALUES
(1,  'Andhra Pradesh',                          'AP', 'Amaravati',          'South',    175, 'state'),
(2,  'Arunachal Pradesh',                       'AR', 'Itanagar',           'Northeast', 60, 'state'),
(3,  'Assam',                                   'AS', 'Dispur',             'Northeast',126, 'state'),
(4,  'Bihar',                                   'BR', 'Patna',              'East',     243, 'state'),
(5,  'Chhattisgarh',                            'CG', 'Raipur',             'Central',   90, 'state'),
(6,  'Goa',                                     'GA', 'Panaji',             'West',      40, 'state'),
(7,  'Gujarat',                                 'GJ', 'Gandhinagar',        'West',     182, 'state'),
(8,  'Haryana',                                 'HR', 'Chandigarh',         'North',     90, 'state'),
(9,  'Himachal Pradesh',                        'HP', 'Shimla',             'North',     68, 'state'),
(10, 'Jharkhand',                               'JH', 'Ranchi',             'East',      81, 'state'),
(11, 'Karnataka',                               'KA', 'Bengaluru',          'South',    224, 'state'),
(12, 'Kerala',                                  'KL', 'Thiruvananthapuram', 'South',    140, 'state'),
(13, 'Madhya Pradesh',                          'MP', 'Bhopal',             'Central',  230, 'state'),
(14, 'Maharashtra',                             'MH', 'Mumbai',             'West',     288, 'state'),
(15, 'Manipur',                                 'MN', 'Imphal',             'Northeast', 60, 'state'),
(16, 'Meghalaya',                               'ML', 'Shillong',           'Northeast', 60, 'state'),
(17, 'Mizoram',                                 'MZ', 'Aizawl',             'Northeast', 40, 'state'),
(18, 'Nagaland',                                'NL', 'Kohima',             'Northeast', 60, 'state'),
(19, 'Odisha',                                  'OD', 'Bhubaneswar',        'East',     147, 'state'),
(20, 'Punjab',                                  'PB', 'Chandigarh',         'North',    117, 'state'),
(21, 'Rajasthan',                               'RJ', 'Jaipur',             'West',     200, 'state'),
(22, 'Sikkim',                                  'SK', 'Gangtok',            'Northeast', 32, 'state'),
(23, 'Tamil Nadu',                              'TN', 'Chennai',            'South',    234, 'state'),
(24, 'Telangana',                               'TS', 'Hyderabad',          'South',    119, 'state'),
(25, 'Tripura',                                 'TR', 'Agartala',           'Northeast', 60, 'state'),
(26, 'Uttar Pradesh',                           'UP', 'Lucknow',            'North',    403, 'state'),
(27, 'Uttarakhand',                             'UK', 'Dehradun',           'North',     70, 'state'),
(28, 'West Bengal',                             'WB', 'Kolkata',            'East',     294, 'state'),
(29, 'Andaman and Nicobar Islands',             'AN', 'Port Blair',         'Islands',   30, 'ut'),
(30, 'Chandigarh',                              'CH', 'Chandigarh',         'North',      1, 'ut'),
(31, 'Dadra & Nagar Haveli and Daman & Diu',    'DN', 'Daman',              'West',       2, 'ut'),
(32, 'Delhi',                                   'DL', 'New Delhi',          'North',     70, 'ut'),
(33, 'Jammu and Kashmir',                       'JK', 'Srinagar/Jammu',     'North',     90, 'ut'),
(34, 'Ladakh',                                  'LA', 'Leh',                'North',      0, 'ut'),
(35, 'Lakshadweep',                             'LD', 'Kavaratti',          'Islands',    1, 'ut'),
(36, 'Puducherry',                              'PY', 'Puducherry',         'South',     30, 'ut');

-- ============================================================
-- 3. LEADERS
-- slug = lowercase name with spaces replaced by hyphens
-- ============================================================
INSERT IGNORE INTO leaders
  (id, name, slug, party_id, state_id, constituency, role, photo_url,
   dob, education, total_score, attendance_score,
   promise_score, criminal_score, fund_score, transparency_score,
   verified, status, created_at)
VALUES
(1,  'Narendra Modi',         'narendra-modi',         1, 7,  'Vadodara (LS) / PM India',    'Prime Minister of India',                      'https://upload.wikimedia.org/wikipedia/commons/thumb/4/41/Official_portrait_of_Narendra_Modi.jpg/400px-Official_portrait_of_Narendra_Modi.jpg',         '1950-09-17','BA Political Science',             74,85,68,80,70,72,1,'active',NOW()),
(2,  'Amit Shah',             'amit-shah',             1, 7,  'Gandhinagar',                 'Union Home Minister',                          'https://upload.wikimedia.org/wikipedia/commons/thumb/4/4e/Amit_Shah_official_portrait.jpg/400px-Amit_Shah_official_portrait.jpg',                 '1964-10-22','BCom Commerce',                    68,78,60,72,65,62,1,'active',NOW()),
(3,  'Rajnath Singh',         'rajnath-singh',         1, 26, 'Lucknow',                     'Union Defence Minister',                       'https://upload.wikimedia.org/wikipedia/commons/thumb/1/11/Rajnath_Singh_official_portrait.jpg/400px-Rajnath_Singh_official_portrait.jpg',            '1951-07-10','MSc Physics',                      71,82,65,85,68,70,1,'active',NOW()),
(4,  'S. Jaishankar',         's-jaishankar',          1, 7,  'Rajya Sabha (Gujarat)',        'Union External Affairs Minister',              'https://upload.wikimedia.org/wikipedia/commons/thumb/7/73/S._Jaishankar_official_portrait.jpg/400px-S._Jaishankar_official_portrait.jpg',             '1955-01-09','PhD International Relations',       78,88,72,90,75,80,1,'active',NOW()),
(5,  'Nirmala Sitharaman',    'nirmala-sitharaman',    1, 23, 'Rajya Sabha (Karnataka)',      'Union Finance Minister',                       'https://upload.wikimedia.org/wikipedia/commons/thumb/6/6e/Nirmala_Sitharaman_2019.jpg/400px-Nirmala_Sitharaman_2019.jpg',                       '1959-08-18','MA Economics JNU',                 72,85,65,88,70,73,1,'active',NOW()),
(6,  'Rahul Gandhi',          'rahul-gandhi',          2, 12, 'Wayanad & Rae Bareli',        'Leader of Opposition (Lok Sabha)',             'https://upload.wikimedia.org/wikipedia/commons/thumb/d/d4/Official_portrait_of_Rahul_Gandhi.jpg/400px-Official_portrait_of_Rahul_Gandhi.jpg',         '1970-06-19','MPhil Development Studies Cambridge',55,60,50,78,48,52,1,'active',NOW()),
(7,  'Mallikarjun Kharge',    'mallikarjun-kharge',    2, 11, 'Rajya Sabha',                 'President of Indian National Congress',        'https://upload.wikimedia.org/wikipedia/commons/thumb/4/44/Mallikarjun_Kharge_official_portrait.jpg/400px-Mallikarjun_Kharge_official_portrait.jpg',   '1942-07-21','LLB',                              60,70,55,82,55,58,1,'active',NOW()),
(8,  'Arvind Kejriwal',       'arvind-kejriwal',       3, 32, 'New Delhi',                   'National Convenor AAP',                        'https://upload.wikimedia.org/wikipedia/commons/thumb/0/05/Arvind_Kejriwal_official_portrait.jpg/400px-Arvind_Kejriwal_official_portrait.jpg',         '1968-08-16','BTech IIT Kharagpur',               58,72,55,55,60,56,1,'active',NOW()),
(9,  'N. Chandrababu Naidu',  'n-chandrababu-naidu',   9, 1,  'Kuppam',                      'Chief Minister of Andhra Pradesh',             'https://upload.wikimedia.org/wikipedia/commons/thumb/3/3d/N._Chandrababu_Naidu_official_portrait.jpg/400px-N._Chandrababu_Naidu_official_portrait.jpg',  '1950-04-20','MA Economics',                     65,75,60,72,62,63,1,'active',NOW()),
(10, 'Pema Khandu',           'pema-khandu',           1, 2,  'Mukto',                       'Chief Minister of Arunachal Pradesh',          'https://upload.wikimedia.org/wikipedia/commons/thumb/4/4f/Pema_Khandu_official_portrait.jpg/400px-Pema_Khandu_official_portrait.jpg',                '1979-08-21','BA',                               60,70,55,78,58,57,1,'active',NOW()),
(11, 'Himanta Biswa Sarma',   'himanta-biswa-sarma',   1, 3,  'Jalukbari',                   'Chief Minister of Assam',                      'https://upload.wikimedia.org/wikipedia/commons/thumb/9/9d/Himanta_Biswa_Sarma_official_portrait.jpg/400px-Himanta_Biswa_Sarma_official_portrait.jpg',   '1969-02-01','LLM',                              62,78,58,62,60,60,1,'active',NOW()),
(12, 'Nitish Kumar',          'nitish-kumar',          17,4,  'Rajya Sabha (Bihar)',          'Chief Minister of Bihar',                      'https://upload.wikimedia.org/wikipedia/commons/thumb/2/2b/Nitish_Kumar_official_portrait.jpg/400px-Nitish_Kumar_official_portrait.jpg',              '1951-03-08','BE Mechanical Engineering',         63,74,60,70,62,62,1,'active',NOW()),
(13, 'Vishnu Deo Sai',        'vishnu-deo-sai',        1, 5,  'Kunkuri',                     'Chief Minister of Chhattisgarh',               'https://upload.wikimedia.org/wikipedia/commons/thumb/2/26/Vishnu_Deo_Sai_official_portrait.jpg/400px-Vishnu_Deo_Sai_official_portrait.jpg',            '1964-02-21','BA',                               57,68,52,75,55,55,1,'active',NOW()),
(14, 'Pramod Sawant',         'pramod-sawant',         1, 6,  'Sanquelim',                   'Chief Minister of Goa',                        'https://upload.wikimedia.org/wikipedia/commons/thumb/a/a3/Pramod_Sawant_official_portrait.jpg/400px-Pramod_Sawant_official_portrait.jpg',              '1973-07-24','BAMS Ayurveda',                     60,72,56,76,58,58,1,'active',NOW()),
(15, 'Bhupendra Patel',       'bhupendra-patel',       1, 7,  'Ghatlodia',                   'Chief Minister of Gujarat',                    'https://upload.wikimedia.org/wikipedia/commons/thumb/9/9e/Bhupendra_Patel_official_portrait.jpg/400px-Bhupendra_Patel_official_portrait.jpg',            '1962-07-15','Diploma Civil Engineering',         64,75,60,80,62,62,1,'active',NOW()),
(16, 'Nayab Singh Saini',     'nayab-singh-saini',     1, 8,  'Ladwa',                       'Chief Minister of Haryana',                    'https://upload.wikimedia.org/wikipedia/commons/thumb/5/58/Nayab_Singh_Saini.jpg/400px-Nayab_Singh_Saini.jpg',                                        '1970-01-25','MA',                               55,68,50,74,53,53,1,'active',NOW()),
(17, 'Sukhvinder Singh Sukhu','sukhvinder-singh-sukhu',2, 9,  'Nadaun',                      'Chief Minister of Himachal Pradesh',           'https://upload.wikimedia.org/wikipedia/commons/thumb/6/6d/Sukhvinder_Singh_Sukhu_official.jpg/400px-Sukhvinder_Singh_Sukhu_official.jpg',             '1964-03-14','BA',                               58,70,54,73,55,56,1,'active',NOW()),
(18, 'Hemant Soren',          'hemant-soren',          14,10, 'Barhait',                     'Chief Minister of Jharkhand',                  'https://upload.wikimedia.org/wikipedia/commons/thumb/d/d3/Hemant_Soren_official_portrait.jpg/400px-Hemant_Soren_official_portrait.jpg',                '1975-08-10','Dropout Engineering',               56,68,52,60,55,54,1,'active',NOW()),
(19, 'Siddaramaiah',          'siddaramaiah',          2, 11, 'Varuna',                      'Chief Minister of Karnataka',                  'https://upload.wikimedia.org/wikipedia/commons/thumb/7/71/Siddaramaiah_official_portrait.jpg/400px-Siddaramaiah_official_portrait.jpg',                '1948-08-03','LLB',                              60,72,56,68,58,58,1,'active',NOW()),
(20, 'Pinarayi Vijayan',      'pinarayi-vijayan',      15,12, 'Dharmadom',                   'Chief Minister of Kerala',                     'https://upload.wikimedia.org/wikipedia/commons/thumb/9/94/Pinarayi_Vijayan_official_portrait.jpg/400px-Pinarayi_Vijayan_official_portrait.jpg',          '1944-05-24','Diploma Engineering',               62,74,58,70,62,62,1,'active',NOW()),
(21, 'Mohan Yadav',           'mohan-yadav',           1, 13, 'Ujjain South',                'Chief Minister of Madhya Pradesh',             'https://upload.wikimedia.org/wikipedia/commons/thumb/8/86/Mohan_Yadav_official_portrait.jpg/400px-Mohan_Yadav_official_portrait.jpg',                '1965-05-09','PhD',                               58,70,54,74,56,56,1,'active',NOW()),
(22, 'Devendra Fadnavis',     'devendra-fadnavis',     1, 14, 'Nagpur South West',           'Chief Minister of Maharashtra',                'https://upload.wikimedia.org/wikipedia/commons/thumb/0/04/Devendra_Fadnavis_official_portrait.jpg/400px-Devendra_Fadnavis_official_portrait.jpg',        '1970-07-22','LLB',                              62,73,58,70,60,60,1,'active',NOW()),
(23, 'N. Biren Singh',        'n-biren-singh',         1, 15, 'Heingang',                    'Chief Minister of Manipur',                    'https://upload.wikimedia.org/wikipedia/commons/thumb/0/0e/N._Biren_Singh_official_portrait.jpg/400px-N._Biren_Singh_official_portrait.jpg',              '1961-01-01','BA',                               48,60,42,55,46,45,1,'active',NOW()),
(24, 'Conrad Sangma',         'conrad-sangma',         23,16, 'South Tura',                  'Chief Minister of Meghalaya',                  'https://upload.wikimedia.org/wikipedia/commons/thumb/2/24/Conrad_Sangma_official_portrait.jpg/400px-Conrad_Sangma_official_portrait.jpg',              '1978-01-27','Economics LSE',                     62,73,57,78,60,60,1,'active',NOW()),
(25, 'Lalduhoma',             'lalduhoma',             24,17, 'Serchhip',                    'Chief Minister of Mizoram',                    'https://upload.wikimedia.org/wikipedia/commons/thumb/6/6e/Lalduhoma_official.jpg/400px-Lalduhoma_official.jpg',                                        '1952-10-14','IPS Officer (retd)',                60,72,56,80,58,58,1,'active',NOW()),
(26, 'Neiphiu Rio',           'neiphiu-rio',           1, 18, 'Northern Angami-II',          'Chief Minister of Nagaland',                   'https://upload.wikimedia.org/wikipedia/commons/thumb/7/72/Neiphiu_Rio_official.jpg/400px-Neiphiu_Rio_official.jpg',                                      '1950-07-11','BA',                               58,68,53,74,56,55,1,'active',NOW()),
(27, 'Mohan Majhi',           'mohan-majhi',           1, 19, 'Keonjhar',                    'Chief Minister of Odisha',                     'https://upload.wikimedia.org/wikipedia/commons/thumb/4/47/Mohan_Majhi_official.jpg/400px-Mohan_Majhi_official.jpg',                                      '1973-01-01','BA',                               55,66,50,72,53,52,1,'active',NOW()),
(28, 'Bhagwant Mann',         'bhagwant-mann',         3, 20, 'Dhuri',                       'Chief Minister of Punjab',                     'https://upload.wikimedia.org/wikipedia/commons/thumb/d/d0/Bhagwant_Mann_official_portrait.jpg/400px-Bhagwant_Mann_official_portrait.jpg',              '1973-10-18','BA Economics',                     62,74,58,70,60,60,1,'active',NOW()),
(29, 'Bhajan Lal Sharma',     'bhajan-lal-sharma',     1, 21, 'Sanganer',                    'Chief Minister of Rajasthan',                  'https://upload.wikimedia.org/wikipedia/commons/thumb/3/32/Bhajan_Lal_Sharma_official.jpg/400px-Bhajan_Lal_Sharma_official.jpg',                      '1966-10-21','MA',                               56,68,52,74,54,54,1,'active',NOW()),
(30, 'P. S. Tamang (Golay)',  'ps-tamang-golay',       21,22, 'Sikkim Krantikari Morcha',    'Chief Minister of Sikkim',                     'https://upload.wikimedia.org/wikipedia/commons/thumb/9/9f/Prem_Singh_Tamang_official.jpg/400px-Prem_Singh_Tamang_official.jpg',                        '1968-05-05','BA',                               60,70,55,78,58,57,1,'active',NOW()),
(31, 'M. K. Stalin',          'mk-stalin',             7, 23, 'Kolathur',                    'Chief Minister of Tamil Nadu',                 'https://upload.wikimedia.org/wikipedia/commons/thumb/3/37/M._K._Stalin_official_portrait.jpg/400px-M._K._Stalin_official_portrait.jpg',                '1953-03-01','BA History',                        64,76,60,72,62,62,1,'active',NOW()),
(32, 'A. Revanth Reddy',      'a-revanth-reddy',       2, 24, 'Kodangal',                    'Chief Minister of Telangana',                  'https://upload.wikimedia.org/wikipedia/commons/thumb/3/3f/Revanth_Reddy_official.jpg/400px-Revanth_Reddy_official.jpg',                                '1969-11-19','BSc',                               58,70,54,65,56,55,1,'active',NOW()),
(33, 'Manik Saha',            'manik-saha',            1, 25, 'Town Bordowali',              'Chief Minister of Tripura',                    'https://upload.wikimedia.org/wikipedia/commons/thumb/5/50/Manik_Saha_official.jpg/400px-Manik_Saha_official.jpg',                                      '1953-09-25','BDS MDS Dentistry',                57,68,52,74,55,54,1,'active',NOW()),
(34, 'Yogi Adityanath',       'yogi-adityanath',       1, 26, 'Gorakhpur',                   'Chief Minister of Uttar Pradesh',              'https://upload.wikimedia.org/wikipedia/commons/thumb/7/72/Yogi_Adityanath_official_portrait.jpg/400px-Yogi_Adityanath_official_portrait.jpg',          '1972-06-05','BSc Maths',                         62,73,58,60,60,60,1,'active',NOW()),
(35, 'Pushkar Singh Dhami',   'pushkar-singh-dhami',   1, 27, 'Champawat',                   'Chief Minister of Uttarakhand',                'https://upload.wikimedia.org/wikipedia/commons/thumb/5/55/Pushkar_Singh_Dhami_official.jpg/400px-Pushkar_Singh_Dhami_official.jpg',                    '1975-09-16','LLB',                              58,70,54,73,56,56,1,'active',NOW()),
(36, 'Mamata Banerjee',       'mamata-banerjee',       6, 28, 'Bhowanipore',                 'Chief Minister of West Bengal',                'https://upload.wikimedia.org/wikipedia/commons/thumb/a/a4/Mamata_Banerjee_official_portrait.jpg/400px-Mamata_Banerjee_official_portrait.jpg',          '1955-01-05','LLB',                              60,70,56,62,58,57,1,'active',NOW()),
(37, 'Rekha Gupta',           'rekha-gupta',           1, 32, 'Shalimar Bagh',               'Chief Minister of Delhi',                      'https://upload.wikimedia.org/wikipedia/commons/thumb/8/8c/Rekha_Gupta_official.jpg/400px-Rekha_Gupta_official.jpg',                                    '1972-01-01','BA',                               53,65,48,70,51,51,1,'active',NOW()),
(38, 'Omar Abdullah',         'omar-abdullah',         19,33, 'Ganderbal',                   'Chief Minister of Jammu and Kashmir',          'https://upload.wikimedia.org/wikipedia/commons/thumb/e/e5/Omar_Abdullah_official_portrait.jpg/400px-Omar_Abdullah_official_portrait.jpg',              '1970-03-10','Commerce',                         57,68,52,72,55,55,1,'active',NOW()),
(39, 'N. Rangasamy',          'n-rangasamy',           1, 36, 'Yanam',                       'Chief Minister of Puducherry',                 'https://upload.wikimedia.org/wikipedia/commons/thumb/6/62/N._Rangasamy_official.jpg/400px-N._Rangasamy_official.jpg',                                  '1950-06-06','BA',                               55,65,50,72,53,52,1,'active',NOW()),
(40, 'Akhilesh Yadav',        'akhilesh-yadav',        4, 26, 'Kannauj',                     'President Samajwadi Party & MP',               'https://upload.wikimedia.org/wikipedia/commons/thumb/6/64/Akhilesh_Yadav_official_portrait.jpg/400px-Akhilesh_Yadav_official_portrait.jpg',            '1973-07-01','ME Environmental Engineering',      58,68,54,62,56,55,1,'active',NOW()),
(41, 'Mayawati',              'mayawati',              5, 26, 'Rajya Sabha',                 'President Bahujan Samaj Party',                'https://upload.wikimedia.org/wikipedia/commons/thumb/9/9b/Mayawati_official_portrait.jpg/400px-Mayawati_official_portrait.jpg',                        '1956-01-15','LLB',                              52,58,48,60,50,50,1,'active',NOW()),
(42, 'Sharad Pawar',          'sharad-pawar',          13,14, 'Rajya Sabha',                 'President NCP (SP)',                            'https://upload.wikimedia.org/wikipedia/commons/thumb/0/05/Sharad_Pawar_official_portrait.jpg/400px-Sharad_Pawar_official_portrait.jpg',                '1940-12-12','BSc',                               60,68,55,65,58,57,1,'active',NOW()),
(43, 'Uddhav Thackeray',      'uddhav-thackeray',      12,14, 'Rajya Sabha',                 'President Shiv Sena (UBT)',                    'https://upload.wikimedia.org/wikipedia/commons/thumb/5/58/Uddhav_Thackeray_official_portrait.jpg/400px-Uddhav_Thackeray_official_portrait.jpg',          '1960-07-27','Diploma Photography',               55,65,50,68,53,52,1,'active',NOW()),
(44, 'Smriti Irani',          'smriti-irani',          1, 26, 'Amethi',                      'Former Union Minister & MP',                   'https://upload.wikimedia.org/wikipedia/commons/thumb/b/bd/Smriti_Irani_official_portrait.jpg/400px-Smriti_Irani_official_portrait.jpg',                '1976-03-23','BA (incomplete)',                   58,70,54,72,56,56,1,'active',NOW());

SET foreign_key_checks = 1;
