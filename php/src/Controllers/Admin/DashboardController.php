<?php
namespace NetaTrack\Controllers\Admin;

use NetaTrack\Core\Controller;
use NetaTrack\Core\Database;
use NetaTrack\Models\Leader;
use NetaTrack\Models\Promise;
use NetaTrack\Models\Project;
use NetaTrack\Models\PublicReport;
use NetaTrack\Models\Corruption;
use NetaTrack\Models\Setting;
use NetaTrack\Models\User;

class DashboardController extends Controller {
    public function index(): void {
        $this->requireAdmin();

        $stats = [
            'leaders' => Leader::count(),
            'promises' => Promise::count(),
            'projects' => Project::count(),
            'reports_pending' => PublicReport::count('status = ?', ['pending']),
            'reports_approved' => PublicReport::count('status = ?', ['approved']),
            'corruption_cases' => Corruption::count(),
            'users' => User::count(),
            'settings' => count(Setting::getAll()),
        ];

        $recentReports = Database::fetchAll("SELECT pr.*, u.name AS user_name FROM public_reports pr LEFT JOIN users u ON u.id = pr.user_id ORDER BY pr.created_at DESC LIMIT 8");
        $topLeaders = Database::fetchAll("SELECT id, name, overall_score, rank, photo FROM leaders ORDER BY overall_score DESC LIMIT 8");
        $delayedProjects = Database::fetchAll("SELECT id, title, progress_pct, status, expected_end_date FROM projects WHERE status = 'delayed' ORDER BY updated_at DESC LIMIT 8");
        $aiQueue = Database::fetchAll("SELECT id, title, source_type, ai_confidence, status FROM ai_collected_data ORDER BY scraped_at DESC LIMIT 8");

        $traffic = [
            'today' => (int)(Database::fetch("SELECT COUNT(*) AS c FROM analytics WHERE DATE(created_at)=CURDATE()")['c'] ?? 0),
            'week' => (int)(Database::fetch("SELECT COUNT(*) AS c FROM analytics WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")['c'] ?? 0),
            'month' => (int)(Database::fetch("SELECT COUNT(*) AS c FROM analytics WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")['c'] ?? 0),
        ];

        $this->view('admin.dashboard.index', [
            'title' => 'Admin Dashboard',
            'stats' => $stats,
            'recentReports' => $recentReports,
            'topLeaders' => $topLeaders,
            'delayedProjects' => $delayedProjects,
            'aiQueue' => $aiQueue,
            'traffic' => $traffic,
        ], 'admin');
    }
}
