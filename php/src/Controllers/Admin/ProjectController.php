<?php
namespace NetaTrack\Controllers\Admin;

use NetaTrack\Core\Controller;
use NetaTrack\Core\Database;
use NetaTrack\Models\Project;

class ProjectController extends Controller {
    public function index(): void {
        $this->requireAdmin();
        $projects = Database::fetchAll("SELECT pj.*, s.name as state_name, l.name as leader_name FROM projects pj LEFT JOIN states s ON s.id=pj.state_id LEFT JOIN leaders l ON l.id=pj.leader_id ORDER BY pj.id DESC");
        $leaders = Database::fetchAll("SELECT id, name FROM leaders ORDER BY name ASC");
        $states = Database::fetchAll("SELECT id, name FROM states ORDER BY name ASC");
        $this->view('admin.projects.index', ['title' => 'Manage Projects', 'projects' => $projects, 'leaders' => $leaders, 'states' => $states], 'admin');
    }

    public function store(): void {
        $this->requireAdmin();
        if (!$this->verifyCsrf()) { $this->redirect('/admin/projects'); }

        Project::create([
            'title' => trim((string)$this->input('title')),
            'description' => $this->input('description'),
            'leader_id' => $this->input('leader_id') ?: null,
            'state_id' => $this->input('state_id') ?: null,
            'category' => $this->input('category'),
            'allocated_budget' => (float)$this->input('allocated_budget', 0),
            'spent_budget' => (float)$this->input('spent_budget', 0),
            'start_date' => $this->input('start_date') ?: null,
            'expected_end_date' => $this->input('expected_end_date') ?: null,
            'progress_pct' => (int)$this->input('progress_pct', 0),
            'status' => $this->input('status', 'planned'),
            'contractor' => $this->input('contractor'),
            'tender_url' => $this->input('tender_url'),
            'source_url' => $this->input('source_url'),
            'is_verified' => 0,
        ]);

        $this->redirect('/admin/projects');
    }
}
