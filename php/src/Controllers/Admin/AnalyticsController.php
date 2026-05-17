<?php
namespace NetaTrack\Controllers\Admin;

use NetaTrack\Core\Controller;
use NetaTrack\Core\Database;

class AnalyticsController extends Controller {
    public function index(): void {
        $this->requireAdmin();

        $daily = Database::fetchAll("SELECT DATE(created_at) as d, COUNT(*) as total FROM analytics GROUP BY DATE(created_at) ORDER BY d DESC LIMIT 30");
        $popular = Database::fetchAll("SELECT page, COUNT(*) as total FROM analytics GROUP BY page ORDER BY total DESC LIMIT 20");

        $this->view('admin.analytics.index', [
            'title' => 'Analytics',
            'daily' => $daily,
            'popular' => $popular,
        ], 'admin');
    }
}
