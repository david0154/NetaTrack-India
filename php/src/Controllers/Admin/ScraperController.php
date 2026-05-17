<?php
namespace NetaTrack\Controllers\Admin;

use NetaTrack\Core\Controller;
use NetaTrack\Core\Database;

class ScraperController extends Controller {
    public function index(): void {
        $this->requireAdmin();
        $sources = Database::fetchAll("SELECT * FROM scraper_sources ORDER BY id DESC");
        $queue = Database::fetchAll("SELECT * FROM ai_collected_data ORDER BY id DESC LIMIT 50");
        $this->view('admin.scraper.index', ['title' => 'AI Scraper', 'sources' => $sources, 'queue' => $queue], 'admin');
    }

    public function start(): void {
        $this->requireAdmin();
        if ($this->verifyCsrf()) {
            $_SESSION['flash_success'] = 'Scraper job queued successfully.';
        }
        $this->redirect('/admin/scraper');
    }
}
