<?php
namespace NetaTrack\Controllers;

use NetaTrack\Core\Controller;
use NetaTrack\Models\Leader;
use NetaTrack\Models\Promise;
use NetaTrack\Models\Project;
use NetaTrack\Models\PublicReport;
use NetaTrack\Models\Setting;

/**
 * NetaTrack India - Homepage Controller
 */
class HomeController extends Controller
{
    public function index(): void
    {
        $leaderModel  = new Leader();
        $promiseModel = new Promise();
        $projectModel = new Project();
        $reportModel  = new PublicReport();
        $settings     = new Setting();

        $this->view('public.home', [
            'topLeaders'      => $leaderModel->getTopLeaders(6),
            'promiseStats'    => $promiseModel->getPlatformStats(),
            'projectStats'    => $projectModel->getPlatformStats(),
            'reportStats'     => $reportModel->getPlatformStats(),
            'leaderStats'     => $leaderModel->getStats(),
            'recentReports'   => $reportModel->getApproved(1, 5)['data'],
            'siteName'        => $settings->get('site_name', 'NetaTrack India'),
            'page_title'      => 'NetaTrack India - Political Transparency Platform',
        ], 'layouts/main');
    }
}
