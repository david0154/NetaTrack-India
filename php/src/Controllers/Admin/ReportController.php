<?php
namespace NetaTrack\Controllers\Admin;

use NetaTrack\Core\Controller;
use NetaTrack\Core\Database;
use NetaTrack\Models\PublicReport;

class ReportController extends Controller {
    public function index(): void {
        $this->requireAdmin();
        $reports = Database::fetchAll("SELECT pr.*, u.name as user_name, l.name as leader_name, s.name as state_name FROM public_reports pr LEFT JOIN users u ON u.id=pr.user_id LEFT JOIN leaders l ON l.id=pr.leader_id LEFT JOIN states s ON s.id=pr.state_id ORDER BY pr.id DESC");
        $this->view('admin.reports.index', ['title' => 'Public Reports', 'reports' => $reports], 'admin');
    }

    public function approve($id): void {
        $this->requireAdmin();
        if ($this->verifyCsrf()) {
            PublicReport::approve((int)$id, (int)($_SESSION['user_id'] ?? 0));
        }
        $this->redirect('/admin/reports');
    }

    public function reject($id): void {
        $this->requireAdmin();
        if ($this->verifyCsrf()) {
            PublicReport::reject((int)$id, (int)($_SESSION['user_id'] ?? 0), (string)$this->input('admin_notes', 'Rejected by admin'));
        }
        $this->redirect('/admin/reports');
    }
}
