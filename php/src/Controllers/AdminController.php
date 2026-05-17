<?php
namespace NetaTrack\Controllers;

use NetaTrack\Core\{Controller, Request, Response};
use NetaTrack\Models\{Leader, Promise, Project, Report, User, Setting};

class AdminController extends Controller
{
    private Leader  $leaders;
    private Promise $promises;
    private Project $projects;
    private Report  $reports;
    private User    $users;
    private Setting $setting;

    public function __construct()
    {
        $this->leaders  = new Leader();
        $this->promises = new Promise();
        $this->projects = new Project();
        $this->reports  = new Report();
        $this->users    = new User();
        $this->setting  = new Setting();
    }

    /* ------------------------------------------------------------------ */
    /* DASHBOARD                                                            */
    /* ------------------------------------------------------------------ */
    public function dashboard(Request $req, Response $res): void
    {
        $db = \NetaTrack\Core\Database::getInstance()->getConnection();

        $signups = $db->query(
            "SELECT DATE(created_at) AS d, COUNT(*) AS cnt FROM users
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
             GROUP BY DATE(created_at) ORDER BY d"
        )->fetchAll(\PDO::FETCH_ASSOC);

        $res->view('admin/dashboard/index', [
            'stats' => [
                'leaders'  => $this->leaders->stats()['total'],
                'promises' => $this->promises->stats()['total'],
                'projects' => $this->projects->stats()['total'],
                'reports'  => $this->reports->stats()['total'],
                'users'    => (int)$db->query("SELECT COUNT(*) FROM users")->fetchColumn(),
                'pending'  => (int)$db->query("SELECT COUNT(*) FROM reports WHERE status='pending'")->fetchColumn(),
            ],
            'topLeaders'     => $this->leaders->topRanked(5),
            'pendingReports' => $this->reports->pending(8),
            'signupData'     => $signups,
        ]);
    }

    /* ------------------------------------------------------------------ */
    /* LEADERS                                                              */
    /* ------------------------------------------------------------------ */
    public function leadersList(Request $req, Response $res): void
    {
        $page    = max(1,(int)$req->get('page',1));
        $filters = ['q'=>$req->get('q'),'state'=>$req->get('state'),'party'=>$req->get('party'),'rank'=>$req->get('rank')];
        $res->view('admin/leaders/index', [
            'leaders' => $this->leaders->all($filters,$page,20),
            'states'  => $this->getStates(),
            'parties' => $this->getParties(),
        ]);
    }

    public function leaderCreate(Request $req, Response $res): void
    {
        $res->view('admin/leaders/form', ['leader'=>null,'states'=>$this->getStates(),'parties'=>$this->getParties()]);
    }

    public function leaderStore(Request $req, Response $res): void
    {
        if (!$req->verifyCsrf()) { $res->redirect('admin/leaders'); return; }
        $data = $this->leaderFormData($req);
        $this->leaders->create($data);
        flash('success','Leader created successfully.');
        $res->redirect('admin/leaders');
    }

    public function leaderEdit(Request $req, Response $res, int $id): void
    {
        $leader = $this->leaders->find($id);
        if (!$leader) { $res->notFound(); return; }
        $res->view('admin/leaders/form', ['leader'=>$leader,'states'=>$this->getStates(),'parties'=>$this->getParties()]);
    }

    public function leaderUpdate(Request $req, Response $res, int $id): void
    {
        if (!$req->verifyCsrf()) { $res->redirect('admin/leaders'); return; }
        $data = $this->leaderFormData($req);
        $this->leaders->update($id, $data);
        flash('success','Leader updated successfully.');
        $res->redirect('admin/leaders');
    }

    public function leaderDelete(Request $req, Response $res, int $id): void
    {
        if (!$req->verifyCsrf()) { $res->redirect('admin/leaders'); return; }
        $this->leaders->delete($id);
        flash('success','Leader deleted.');
        $res->redirect('admin/leaders');
    }

    /* ------------------------------------------------------------------ */
    /* REPORTS                                                              */
    /* ------------------------------------------------------------------ */
    public function reportsList(Request $req, Response $res): void
    {
        $page    = max(1,(int)$req->get('page',1));
        $filters = ['status'=>$req->get('status','pending'),'type'=>$req->get('type'),'q'=>$req->get('q')];
        $res->view('admin/reports/index', ['reports'=>$this->reports->all($filters,$page,20)]);
    }

    public function reportApprove(Request $req, Response $res, int $id): void
    {
        if (!$req->verifyCsrf()) { $res->redirect('admin/reports'); return; }
        $this->reports->updateStatus($id,'approved',$req->post('admin_note'));
        flash('success','Report approved.');
        $res->redirect('admin/reports');
    }

    public function reportReject(Request $req, Response $res, int $id): void
    {
        if (!$req->verifyCsrf()) { $res->redirect('admin/reports'); return; }
        $this->reports->updateStatus($id,'rejected',$req->post('admin_note'));
        flash('success','Report rejected.');
        $res->redirect('admin/reports');
    }

    /* ------------------------------------------------------------------ */
    /* USERS                                                                */
    /* ------------------------------------------------------------------ */
    public function usersList(Request $req, Response $res): void
    {
        $page    = max(1,(int)$req->get('page',1));
        $filters = ['q'=>$req->get('q'),'role'=>$req->get('role')];
        $res->view('admin/users/index', ['users'=>$this->users->all($filters,$page,25)]);
    }

    public function userBan(Request $req, Response $res, int $id): void
    {
        if (!$req->verifyCsrf()) { $res->redirect('admin/users'); return; }
        $user = $this->users->find($id);
        if ($user && $user['role'] !== 'admin') {
            $this->users->ban($id);
            flash('success','User banned.');
        } else {
            flash('error','Cannot ban admin accounts.');
        }
        $res->redirect('admin/users');
    }

    /* ------------------------------------------------------------------ */
    /* SETTINGS                                                             */
    /* ------------------------------------------------------------------ */
    public function settings(Request $req, Response $res): void
    {
        $res->view('admin/settings/index', ['settings'=>$this->setting->all()]);
    }

    public function settingsSave(Request $req, Response $res): void
    {
        if (!$req->verifyCsrf()) { $res->redirect('admin/settings'); return; }
        $allowed = ['site_name','site_tagline','meta_description','smtp_host','smtp_port',
                    'smtp_user','smtp_pass','gemini_api_key','sarvam_api_key',
                    'ai_enabled','registration_enabled','maintenance_mode'];
        $data = [];
        foreach ($allowed as $k) {
            if ($req->post($k) !== null) $data[$k] = $req->post($k);
        }
        $this->setting->bulkSet($data);
        flash('success','Settings saved.');
        $res->redirect('admin/settings');
    }

    /* ------------------------------------------------------------------ */
    /* ANALYTICS                                                            */
    /* ------------------------------------------------------------------ */
    public function analytics(Request $req, Response $res): void
    {
        $db = \NetaTrack\Core\Database::getInstance()->getConnection();
        $res->view('admin/analytics/index', [
            'signupData'  => $db->query(
                "SELECT DATE(created_at) d, COUNT(*) cnt FROM users
                 WHERE created_at >= DATE_SUB(NOW(),INTERVAL 30 DAY) GROUP BY d ORDER BY d"
            )->fetchAll(\PDO::FETCH_ASSOC),
            'reportTypes' => $db->query(
                "SELECT type, COUNT(*) cnt FROM reports GROUP BY type"
            )->fetchAll(\PDO::FETCH_ASSOC),
            'topStates'   => $db->query(
                "SELECT s.name, COUNT(r.id) AS cnt FROM reports r
                 JOIN states s ON r.state_id=s.id GROUP BY s.id ORDER BY cnt DESC LIMIT 10"
            )->fetchAll(\PDO::FETCH_ASSOC),
        ]);
    }

    /* ------------------------------------------------------------------ */
    /* PROJECTS                                                             */
    /* ------------------------------------------------------------------ */
    public function projectsList(Request $req, Response $res): void
    {
        $page    = max(1,(int)$req->get('page',1));
        $filters = ['status'=>$req->get('status'),'q'=>$req->get('q')];
        $res->view('admin/projects/index', [
            'projects' => $this->projects->all($filters,$page,20),
            'stats'    => $this->projects->stats(),
        ]);
    }

    /* ------------------------------------------------------------------ */
    /* HELPERS                                                              */
    /* ------------------------------------------------------------------ */
    private function leaderFormData(Request $req): array
    {
        return [
            'name'           => trim($req->post('name','')),
            'slug'           => trim($req->post('slug','')) ?: null,
            'designation'    => $req->post('designation'),
            'party_id'       => $req->post('party_id') ?: null,
            'state_id'       => $req->post('state_id') ?: null,
            'constituency'   => $req->post('constituency'),
            'bio'            => $req->post('bio'),
            'dob'            => $req->post('dob') ?: null,
            'gender'         => $req->post('gender'),
            'email'          => $req->post('leader_email'),
            'twitter'        => $req->post('twitter'),
            'facebook'       => $req->post('facebook'),
            'website'        => $req->post('website'),
            'criminal_cases' => (int)$req->post('criminal_cases',0),
            'assets_declared'=> (float)$req->post('assets_declared',0),
            'is_verified'    => (int)(bool)$req->post('is_verified'),
            'status'         => $req->post('status','active'),
            'score_promise_completion'   => (int)$req->post('score_promise_completion',50),
            'score_project_delivery'     => (int)$req->post('score_project_delivery',50),
            'score_transparency'         => (int)$req->post('score_transparency',50),
            'score_public_satisfaction'  => (int)$req->post('score_public_satisfaction',50),
            'score_attendance'           => (int)$req->post('score_attendance',50),
            'score_criminal_record'      => (int)$req->post('score_criminal_record',50),
            'score_assets_declared'      => (int)$req->post('score_assets_declared',50),
            'score_social_media_activity'=> (int)$req->post('score_social_media_activity',50),
        ];
    }

    private function getStates(): array
    {
        static $s = null;
        if ($s === null) {
            $s = \NetaTrack\Core\Database::getInstance()->getConnection()
                   ->query("SELECT id,name,slug FROM states ORDER BY name")
                   ->fetchAll(\PDO::FETCH_ASSOC);
        }
        return $s;
    }

    private function getParties(): array
    {
        static $p = null;
        if ($p === null) {
            $p = \NetaTrack\Core\Database::getInstance()->getConnection()
                   ->query("SELECT id,name,abbreviation,color FROM parties ORDER BY name")
                   ->fetchAll(\PDO::FETCH_ASSOC);
        }
        return $p;
    }
}
