<?php
namespace NetaTrack\Controllers\Admin;

use NetaTrack\Core\Controller;
use NetaTrack\Models\Leader;
use NetaTrack\Models\Promise;
use NetaTrack\Models\Project;
use NetaTrack\Models\PublicReport;
use NetaTrack\Models\User;

/**
 * NetaTrack India - Admin Dashboard Controller
 */
class DashboardController extends Controller
{
    public function index(): void
    {
        $leaderModel  = new Leader();
        $promiseModel = new Promise();
        $projectModel = new Project();
        $reportModel  = new PublicReport();
        $userModel    = new User();

        $this->adminView('dashboard', [
            'page_title'      => 'Admin Dashboard - NetaTrack India',
            'leaderStats'     => $leaderModel->getStats(),
            'promiseStats'    => $promiseModel->getPlatformStats(),
            'projectStats'    => $projectModel->getPlatformStats(),
            'reportStats'     => $reportModel->getPlatformStats(),
            'userStats'       => $userModel->getStats(),
            'pendingReports'  => $reportModel->getPending(1, 10)['data'],
            'topLeaders'      => $leaderModel->getTopLeaders(5),
            'delayedProjects' => $projectModel->getDelayed(),
        ]);
    }
}
