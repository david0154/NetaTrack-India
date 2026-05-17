<?php
namespace NetaTrack\Models;

use NetaTrack\Core\Model;

/**
 * NetaTrack India - Political Promise Model
 */
class Promise extends Model
{
    protected string $table = 'promises';
    protected array $fillable = [
        'leader_id','title','description','category',
        'source_url','source_type','promised_date',
        'deadline','status','completion_percent',
        'ai_verified','ai_confidence','admin_verified',
        'fake_claim','fake_reason','evidence_url',
        'state_id','party_id','created_at','updated_at'
    ];

    // status: pending | kept | broken | partial | in_progress | fake

    public function getByLeader(int $leaderId, int $page = 1, int $perPage = 20): array
    {
        return $this->paginate($page, $perPage, 'leader_id=?', [$leaderId]);
    }

    public function getStats(int $leaderId): array
    {
        $rows = $this->db->fetchAll(
            'SELECT status, COUNT(*) as cnt FROM promises WHERE leader_id=? GROUP BY status',
            [$leaderId]
        );
        $stats = ['total'=>0,'kept'=>0,'broken'=>0,'partial'=>0,'in_progress'=>0,'fake'=>0,'pending'=>0];
        foreach ($rows as $r) {
            $stats[$r['status']] = (int)$r['cnt'];
            $stats['total'] += (int)$r['cnt'];
        }
        if ($stats['total'] > 0) {
            $stats['completion_rate'] = round(($stats['kept'] / $stats['total']) * 100);
        } else {
            $stats['completion_rate'] = 0;
        }
        return $stats;
    }

    public function getPlatformStats(): array
    {
        $total = $this->count();
        $kept  = $this->count("status='kept'");
        $fake  = $this->count("status='fake' OR fake_claim=1");
        return ['total'=>$total,'kept'=>$kept,'fake'=>$fake,
                'completion_rate'=> $total > 0 ? round(($kept/$total)*100) : 0];
    }
}
