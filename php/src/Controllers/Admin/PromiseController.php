<?php
namespace NetaTrack\Controllers\Admin;

use NetaTrack\Core\Controller;
use NetaTrack\Core\Database;
use NetaTrack\Models\Promise;

class PromiseController extends Controller {
    public function index(): void {
        $this->requireAdmin();
        $promises = Database::fetchAll("SELECT p.*, l.name as leader_name FROM promises p LEFT JOIN leaders l ON l.id=p.leader_id ORDER BY p.id DESC");
        $leaders = Database::fetchAll("SELECT id, name FROM leaders ORDER BY name ASC");
        $this->view('admin.promises.index', ['title' => 'Manage Promises', 'promises' => $promises, 'leaders' => $leaders], 'admin');
    }

    public function store(): void {
        $this->requireAdmin();
        if (!$this->verifyCsrf()) { $this->redirect('/admin/promises'); }

        Promise::create([
            'leader_id' => (int)$this->input('leader_id'),
            'title' => trim((string)$this->input('title')),
            'description' => $this->input('description'),
            'category' => $this->input('category'),
            'made_on' => $this->input('made_on') ?: null,
            'deadline' => $this->input('deadline') ?: null,
            'status' => $this->input('status', 'pending'),
            'source_url' => $this->input('source_url'),
            'proof_url' => $this->input('proof_url'),
            'ai_verified' => 0,
            'ai_confidence' => 0,
        ]);

        $this->redirect('/admin/promises');
    }
}
