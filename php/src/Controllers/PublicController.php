<?php
namespace NetaTrack\Controllers;

use NetaTrack\Core\{Controller, Request, Response};
use NetaTrack\Models\{Leader, Promise, Project, Report, Setting};

class PublicController extends Controller
{
    private Leader  $leaders;
    private Promise $promises;
    private Project $projects;
    private Report  $reports;
    private Setting $setting;

    public function __construct()
    {
        $this->leaders  = new Leader();
        $this->promises = new Promise();
        $this->projects = new Project();
        $this->reports  = new Report();
        $this->setting  = new Setting();
    }

    // GET /
    public function home(Request $req, Response $res): void
    {
        $leaderStats  = $this->leaders->stats();
        $promiseStats = $this->promises->stats();
        $projectStats = $this->projects->stats();
        $reportStats  = $this->reports->stats();

        $stats = [
            'leaders'          => $leaderStats['total'],
            'promises'         => $promiseStats['total'],
            'promises_kept'    => $promiseStats['kept'],
            'promise_kept_pct' => $promiseStats['total'] > 0
                                    ? round(($promiseStats['kept'] / $promiseStats['total']) * 100)
                                    : 0,
            'projects'         => $projectStats['total'],
            'projects_delayed' => $projectStats['delayed'] ?? 0,
            'reports'          => $reportStats['total'],
        ];

        $res->view('public/home', [
            'stats'          => $stats,
            'topLeaders'     => $this->leaders->topRanked(8),
            'recentPromises' => $this->promises->recent(6),
        ]);
    }

    // GET /leaders
    public function leaders(Request $req, Response $res): void
    {
        $page = max(1, (int)($req->get('page', 1)));
        $filters = [
            'q'     => $req->get('q'),
            'state' => $req->get('state'),
            'party' => $req->get('party'),
            'rank'  => $req->get('rank'),
            'sort'  => $req->get('sort','score'),
            'status'=> 'active',
        ];

        $res->view('public/leaders', [
            'leaders' => $this->leaders->all($filters, $page, 20),
            'states'  => $this->getStates(),
            'parties' => $this->getParties(),
            'filters' => $filters,
        ]);
    }

    // GET /leaders/{slug}
    public function leaderProfile(Request $req, Response $res, string $slug): void
    {
        $leader = $this->leaders->findBySlug($slug);
        if (!$leader) { $res->notFound(); return; }

        $page = max(1, (int)$req->get('page', 1));

        $res->view('public/leader-profile', [
            'leader'   => $leader,
            'promises' => $this->promises->byLeader($leader['id'], $page, 12),
            'projects' => $this->projects->byLeader($leader['id']),
            'reports'  => $this->reports->byLeader($leader['id'], 15),
        ]);
    }

    // GET /promises
    public function promises(Request $req, Response $res): void
    {
        $page = max(1, (int)$req->get('page', 1));
        $filters = [
            'q'         => $req->get('q'),
            'status'    => $req->get('status'),
            'leader_id' => $req->get('leader_id'),
            'state_id'  => $req->get('state_id'),
        ];

        $res->view('public/promises', [
            'promises' => $this->promises->all($filters, $page, 18),
            'stats'    => $this->promises->stats(),
            'leaders'  => $this->getLeaderList(),
            'states'   => $this->getStates(),
        ]);
    }

    // GET /projects
    public function projects(Request $req, Response $res): void
    {
        $page = max(1, (int)$req->get('page', 1));
        $filters = [
            'q'        => $req->get('q'),
            'status'   => $req->get('status'),
            'state_id' => $req->get('state_id'),
        ];

        $res->view('public/projects', [
            'projects' => $this->projects->all($filters, $page, 18),
            'stats'    => $this->projects->stats(),
            'states'   => $this->getStates(),
        ]);
    }

    // GET /corruption
    public function corruption(Request $req, Response $res): void
    {
        $filters = [
            'state_id' => $req->get('state_id'),
            'sort'     => $req->get('sort', 'cases'),
        ];

        $res->view('public/corruption', [
            'leaders' => $this->leaders->corruptionRanked($filters, 50),
            'stats'   => $this->reports->stats(),
            'states'  => $this->getStates(),
        ]);
    }

    // ---- helpers ----
    private function getStates(): array
    {
        static $states = null;
        if ($states === null) {
            $states = \NetaTrack\Core\Database::getInstance()->getConnection()
                        ->query("SELECT id, name, slug FROM states ORDER BY name")
                        ->fetchAll(\PDO::FETCH_ASSOC);
        }
        return $states;
    }

    private function getParties(): array
    {
        static $parties = null;
        if ($parties === null) {
            $parties = \NetaTrack\Core\Database::getInstance()->getConnection()
                         ->query("SELECT id, name, abbreviation, color FROM parties ORDER BY name")
                         ->fetchAll(\PDO::FETCH_ASSOC);
        }
        return $parties;
    }

    private function getLeaderList(): array
    {
        return \NetaTrack\Core\Database::getInstance()->getConnection()
                 ->query("SELECT id, name, party_id FROM leaders WHERE status='active' ORDER BY name")
                 ->fetchAll(\PDO::FETCH_ASSOC);
    }
}
