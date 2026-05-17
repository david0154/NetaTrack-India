<?php
namespace NetaTrack\Controllers\Admin;

use NetaTrack\Core\Controller;
use NetaTrack\Models\User;

class UserController extends Controller {
    public function index(): void {
        $this->requireAdmin();
        $users = User::all('id DESC');
        $this->view('admin.users.index', ['title' => 'Manage Users', 'users' => $users], 'admin');
    }
}
