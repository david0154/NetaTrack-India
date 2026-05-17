<?php
namespace NetaTrack\Controllers\Admin;

use NetaTrack\Core\Controller;
use NetaTrack\Core\Database;
use NetaTrack\Models\Leader;
use NetaTrack\Models\State;
use NetaTrack\Models\Project;
use NetaTrack\Models\Promise;

class LeaderController extends Controller {
    public function index(): void {
        $this->requireAdmin();
        $leaders = Database::fetchAll("SELECT l.*, p.name as party_name, s.name as state_name FROM leaders l LEFT JOIN parties p ON p.id=l.party_id LEFT JOIN states s ON s.id=l.state_id ORDER BY l.id DESC");
        $this->view('admin.leaders.index', ['title' => 'Manage Leaders', 'leaders' => $leaders], 'admin');
    }

    public function create(): void {
        $this->requireAdmin();
        $states = State::all('name ASC');
        $parties = Database::fetchAll("SELECT * FROM parties ORDER BY name ASC");
        $this->view('admin.leaders.create', ['title' => 'Create Leader', 'states' => $states, 'parties' => $parties], 'admin');
    }

    public function store(): void {
        $this->requireAdmin();
        if (!$this->verifyCsrf()) { $this->redirect('/admin/leaders/create'); }

        $name = trim((string)$this->input('name'));
        if (!$name) { $this->redirect('/admin/leaders/create'); }

        Leader::create([
            'name' => $name,
            'slug' => strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-')),
            'party_id' => $this->input('party_id') ?: null,
            'state_id' => $this->input('state_id') ?: null,
            'constituency' => $this->input('constituency'),
            'position' => $this->input('position'),
            'bio' => $this->input('bio'),
            'promise_completion_rate' => (float)$this->input('promise_completion_rate', 0),
            'project_delivery_rate' => (float)$this->input('project_delivery_rate', 0),
            'budget_efficiency' => (float)$this->input('budget_efficiency', 0),
            'public_satisfaction' => (float)$this->input('public_satisfaction', 0),
            'transparency_score' => (float)$this->input('transparency_score', 0),
            'verification_trust' => (float)$this->input('verification_trust', 0),
            'corruption_score' => (float)$this->input('corruption_score', 0),
            'overall_score' => (float)$this->input('overall_score', 0),
            'rank' => $this->input('rank', 'Average'),
            'status' => $this->input('status', 'active'),
        ]);

        $this->redirect('/admin/leaders');
    }

    public function edit($id): void {
        $this->requireAdmin();
        $leader = Leader::find((int)$id);
        $states = State::all('name ASC');
        $parties = Database::fetchAll("SELECT * FROM parties ORDER BY name ASC");
        $this->view('admin.leaders.edit', ['title' => 'Edit Leader', 'leader' => $leader, 'states' => $states, 'parties' => $parties], 'admin');
    }

    public function update($id): void {
        $this->requireAdmin();
        if (!$this->verifyCsrf()) { $this->redirect('/admin/leaders'); }

        $name = trim((string)$this->input('name'));
        Leader::update((int)$id, [
            'name' => $name,
            'slug' => strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-')),
            'party_id' => $this->input('party_id') ?: null,
            'state_id' => $this->input('state_id') ?: null,
            'constituency' => $this->input('constituency'),
            'position' => $this->input('position'),
            'bio' => $this->input('bio'),
            'promise_completion_rate' => (float)$this->input('promise_completion_rate', 0),
            'project_delivery_rate' => (float)$this->input('project_delivery_rate', 0),
            'budget_efficiency' => (float)$this->input('budget_efficiency', 0),
            'public_satisfaction' => (float)$this->input('public_satisfaction', 0),
            'transparency_score' => (float)$this->input('transparency_score', 0),
            'verification_trust' => (float)$this->input('verification_trust', 0),
            'corruption_score' => (float)$this->input('corruption_score', 0),
            'overall_score' => (float)$this->input('overall_score', 0),
            'rank' => $this->input('rank', 'Average'),
            'status' => $this->input('status', 'active'),
        ]);

        $this->redirect('/admin/leaders');
    }

    public function delete($id): void {
        $this->requireAdmin();
        if ($this->verifyCsrf()) {
            Leader::delete((int)$id);
        }
        $this->redirect('/admin/leaders');
    }
}
