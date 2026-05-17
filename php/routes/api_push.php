<?php
/**
 * NetaTrack India — Push API endpoints
 * Called by Python admin app to sync data to the live website.
 * Route prefix: /api/v1/push/
 * Auth: Bearer token (admin_api_token from settings)
 */

function api_push_auth(): bool {
    $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!str_starts_with($header, 'Bearer ')) return false;
    $token = substr($header, 7);
    $row = db()->fetchOne("SELECT `value` FROM settings WHERE `key`='admin_api_token'");
    return $row && hash_equals($row['value'], $token);
}

function api_push_json($data, int $code = 200): never {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

$endpoint = $router->segment(3); // /api/v1/push/{endpoint}

if (!api_push_auth()) {
    api_push_json(['error' => 'Unauthorized'], 401);
}

$body = json_decode(file_get_contents('php://input'), true) ?? [];

match ($endpoint) {
    'leaders' => api_push_leaders($body['leaders'] ?? []),
    'scores'  => api_push_scores($body['scores'] ?? []),
    'cases'   => api_push_cases($body['cases'] ?? []),
    'announcements' => api_push_announcements($body['announcements'] ?? []),
    'funds'   => api_push_funds($body['funds'] ?? []),
    default   => api_push_json(['error' => 'Unknown endpoint'], 404),
};

function api_push_leaders(array $leaders): never {
    $db = db(); $ok = 0;
    foreach ($leaders as $l) {
        $slug = $l['slug'] ?? '';
        if (!$slug) continue;
        $existing = $db->fetchOne("SELECT id FROM leaders WHERE slug=?", [$slug]);
        if ($existing) {
            $db->execute(
                "UPDATE leaders SET name=?,designation=?,constituency=?,bio=?,photo_url=?,
                 total_score=?,score_rank=?,status=?,is_verified=?,house=?,
                 eci_assets=?,eci_liabilities=?,eci_criminal_cases=? WHERE slug=?",
                [$l['name']??'',$l['designation']??'',$l['constituency']??'',
                 $l['bio']??'',$l['photo_url']??'',$l['total_score']??0,
                 $l['score_rank']??0,$l['status']??'active',$l['is_verified']??0,
                 $l['house']??'',$l['eci_assets']??'',$l['eci_liabilities']??'',
                 $l['eci_criminal_cases']??0,$slug]
            );
        } else {
            $db->execute(
                "INSERT INTO leaders (name,slug,designation,constituency,bio,photo_url,
                 total_score,score_rank,status,is_verified,house,eci_assets,eci_liabilities,eci_criminal_cases)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
                [$l['name']??'',$slug,$l['designation']??'',$l['constituency']??'',
                 $l['bio']??'',$l['photo_url']??'',$l['total_score']??0,
                 $l['score_rank']??0,$l['status']??'active',$l['is_verified']??0,
                 $l['house']??'',$l['eci_assets']??'',$l['eci_liabilities']??'',
                 $l['eci_criminal_cases']??0]
            );
        }
        $ok++;
    }
    api_push_json(['message' => "$ok leaders upserted", 'ok' => $ok]);
}

function api_push_scores(array $scores): never {
    $db = db(); $ok = 0;
    foreach ($scores as $s) {
        if (!isset($s['id'])) continue;
        $db->execute(
            "UPDATE leaders SET total_score=?,score_rank=?,score_promise_completion=?,
             score_project_delivery=?,score_transparency=?,score_criminal_record=?,
             score_fund_utilization=? WHERE id=?",
            [$s['total_score']??0,$s['score_rank']??0,$s['score_promise_completion']??50,
             $s['score_project_delivery']??50,$s['score_transparency']??50,
             $s['score_criminal_record']??50,$s['score_fund_utilization']??50,$s['id']]
        );
        $ok++;
    }
    api_push_json(['message' => "$ok scores updated", 'ok' => $ok]);
}

function api_push_cases(array $cases): never {
    $db = db(); $ok = 0;
    foreach ($cases as $c) {
        $uid = $c['source_uid'] ?? md5(($c['title']??'').($c['leader_id']??''));
        $existing = $db->fetchOne("SELECT id FROM criminal_cases WHERE source_uid=?", [$uid]);
        if (!$existing) {
            $db->execute(
                "INSERT INTO criminal_cases (leader_id,source_uid,title,type,status,is_fake,description,detected_at)
                 VALUES (?,?,?,?,?,?,?,?)",
                [$c['leader_id']??0,$uid,$c['title']??'',$c['type']??'other',
                 $c['status']??'ongoing',$c['is_fake']??0,$c['description']??'',$c['detected_at']??date('Y-m-d H:i:s')]
            );
            $ok++;
        }
    }
    api_push_json(['message' => "$ok cases inserted", 'ok' => $ok]);
}

function api_push_announcements(array $items): never {
    $db = db(); $ok = 0;
    foreach ($items as $a) {
        $uid = $a['source_uid'] ?? md5($a['title']??'');
        $existing = $db->fetchOne("SELECT id FROM announcements WHERE source_uid=?", [$uid]);
        if (!$existing) {
            $db->execute(
                "INSERT INTO announcements (source_uid,source_name,title,description,link,category,leader_id,status,published_at)
                 VALUES (?,?,?,?,?,?,?,'approved',?)",
                [$uid,$a['source_name']??'',$a['title']??'',$a['description']??'',
                 $a['link']??'',$a['category']??'general',$a['leader_id']??null,$a['published_at']??date('Y-m-d H:i:s')]
            );
            $ok++;
        }
    }
    api_push_json(['message' => "$ok announcements inserted", 'ok' => $ok]);
}

function api_push_funds(array $records): never {
    $db = db(); $ok = 0;
    foreach ($records as $f) {
        if (!isset($f['leader_id'])) continue;
        $db->execute(
            "INSERT INTO fund_records (leader_id,allocated_cr,utilized_cr,utilization_pct,
             projects_count,leakage_suspected,summary,recorded_at)
             VALUES (?,?,?,?,?,?,?,NOW())
             ON DUPLICATE KEY UPDATE utilized_cr=?,utilization_pct=?,summary=?",
            [$f['leader_id'],$f['allocated_cr']??null,$f['utilized_cr']??null,
             $f['utilization_pct']??null,$f['projects_count']??null,
             $f['leakage_suspected']??0,$f['summary']??'',
             $f['utilized_cr']??null,$f['utilization_pct']??null,$f['summary']??'']
        );
        $ok++;
    }
    api_push_json(['message' => "$ok fund records upserted", 'ok' => $ok]);
}
