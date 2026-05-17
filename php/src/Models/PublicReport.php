<?php
namespace NetaTrack\Models;

use NetaTrack\Core\Model;

/**
 * NetaTrack India - Public Submission / Report Model
 */
class PublicReport extends Model
{
    protected string $table = 'public_reports';
    protected array $fillable = [
        'user_id','leader_id','state_id','project_id',
        'type','title','description',
        'evidence_urls','rti_doc_url',
        'location','latitude','longitude',
        'status','ai_spam_score','ai_verified',
        'ai_fake_score','is_duplicate','duplicate_of',
        'admin_notes','reported_at','created_at','updated_at'
    ];

    // type: corruption | fake_claim | complaint | infrastructure | project_update | other
    // status: pending | approved | rejected | under_review | duplicate

    public function getPending(int $page = 1, int $perPage = 20): array
    {
        return $this->paginate($page, $perPage, "status='pending'");
    }

    public function getPlatformStats(): array
    {
        return [
            'total'        => $this->count(),
            'pending'      => $this->count("status='pending'"),
            'approved'     => $this->count("status='approved'"),
            'rejected'     => $this->count("status='rejected'"),
            'fake_detected'=> $this->count('ai_fake_score > 70'),
        ];
    }

    public function getApproved(int $page = 1, int $perPage = 20): array
    {
        return $this->paginate($page, $perPage, "status='approved'");
    }

    public function approve(int $id, ?string $notes = null): void
    {
        $this->db->query(
            "UPDATE public_reports SET status='approved', admin_notes=? WHERE id=?",
            [$notes, $id]
        );
    }

    public function reject(int $id, ?string $notes = null): void
    {
        $this->db->query(
            "UPDATE public_reports SET status='rejected', admin_notes=? WHERE id=?",
            [$notes, $id]
        );
    }
}
