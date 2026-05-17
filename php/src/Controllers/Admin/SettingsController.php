<?php
namespace NetaTrack\Controllers\Admin;

use NetaTrack\Core\Controller;
use NetaTrack\Models\Setting;

class SettingsController extends Controller {
    public function index(): void {
        $this->requireAdmin();
        $settings = Setting::getAll();
        $this->view('admin.settings.index', ['title' => 'Website Settings', 'settings' => $settings], 'admin');
    }

    public function update(): void {
        $this->requireAdmin();
        if (!$this->verifyCsrf()) {
            $this->redirect('/admin/settings');
        }

        $keys = [
            'site_name','site_tagline','site_email','site_phone','primary_color','footer_text',
            'google_analytics','meta_pixel','smtp_host','smtp_port','smtp_username','smtp_from',
            'social_twitter','social_facebook'
        ];

        foreach ($keys as $key) {
            Setting::set($key, (string)$this->input($key, ''));
        }

        $_SESSION['flash_success'] = 'Settings updated successfully.';
        $this->redirect('/admin/settings');
    }
}
