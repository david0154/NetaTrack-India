<?php
namespace NetaTrack\Controllers;

use NetaTrack\Core\{Controller, Request, Response};
use NetaTrack\Models\{Report, Leader, Setting};
use NetaTrack\Services\AIService;

class ReportController extends Controller
{
    private Report  $reports;
    private Leader  $leaders;
    private Setting $setting;

    public function __construct()
    {
        $this->reports = new Report();
        $this->leaders = new Leader();
        $this->setting = new Setting();
    }

    // GET /report
    public function showForm(Request $req, Response $res): void
    {
        $res->view('public/report', [
            'leaders' => $this->getLeaderList(),
            'states'  => $this->getStates(),
        ]);
    }

    // POST /report
    public function submit(Request $req, Response $res): void
    {
        if (!$req->verifyCsrf()) { $res->redirect('report'); return; }

        $title       = trim($req->post('title', ''));
        $description = trim($req->post('description', ''));
        $type        = $req->post('type', 'other');

        // Validate
        $errors = [];
        if (strlen($title) < 10)       $errors[] = 'Title must be at least 10 characters.';
        if (strlen($description) < 30) $errors[] = 'Description must be at least 30 characters.';
        if (!in_array($type, ['corruption','fake_claim','project_delay','promise_broken','positive','other'])) {
            $errors[] = 'Invalid report type.';
        }

        if ($errors) {
            flash('error', implode(' ', $errors));
            $res->redirect('report');
            return;
        }

        // AI confidence check
        $aiConfidence = 50; // default
        if ((bool)$this->setting->get('ai_enabled', false)) {
            try {
                $ai = new AIService($this->setting->get('gemini_api_key',''));
                $aiConfidence = $ai->analyzeReport($title, $description);
            } catch (\Exception $e) {
                // silently fail, use default
            }
        }

        $data = [
            'title'          => $title,
            'description'    => $description,
            'type'           => $type,
            'leader_id'      => $req->post('leader_id') ?: null,
            'state_id'       => $req->post('state_id')  ?: null,
            'evidence_urls'  => $req->post('evidence_urls', ''),
            'reporter_name'  => $req->post('reporter_name', 'Anonymous'),
            'reporter_email' => $req->post('reporter_email', ''),
            'ai_confidence'  => $aiConfidence,
            'status'         => 'pending',
            'ip_address'     => $_SERVER['REMOTE_ADDR'] ?? '',
        ];

        $this->reports->create($data);
        flash('success', 'Your report has been submitted and is under review. Thank you!');
        $res->redirect('report');
    }
}
