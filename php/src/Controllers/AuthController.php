<?php
namespace NetaTrack\Controllers;

use NetaTrack\Core\Controller;
use NetaTrack\Core\Database;
use NetaTrack\Models\Leader;
use NetaTrack\Models\Promise;
use NetaTrack\Models\Project;
use NetaTrack\Models\PublicReport;
use NetaTrack\Models\Corruption;
use NetaTrack\Models\User;

class AuthController extends Controller {
    public function loginForm(): void {
        $this->view('auth.login', ['title' => 'Admin Login'], 'auth');
    }

    public function login(): void {
        if (!$this->verifyCsrf()) {
            $this->view('auth.login', ['title' => 'Admin Login', 'error' => 'Invalid CSRF token.'], 'auth');
            return;
        }

        $email = trim((string)$this->input('email'));
        $password = (string)$this->input('password');

        $user = User::verify($email, $password);
        if (!$user) {
            $this->view('auth.login', ['title' => 'Admin Login', 'error' => 'Invalid credentials.'], 'auth');
            return;
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_email'] = $user['email'];

        $this->redirect('/admin');
    }

    public function registerForm(): void {
        $this->view('auth.register', ['title' => 'Create Account'], 'auth');
    }

    public function register(): void {
        if (!$this->verifyCsrf()) {
            $this->view('auth.register', ['title' => 'Create Account', 'error' => 'Invalid CSRF token.'], 'auth');
            return;
        }

        $name = trim((string)$this->input('name'));
        $email = trim((string)$this->input('email'));
        $password = (string)$this->input('password');

        if (!$name || !$email || !$password) {
            $this->view('auth.register', ['title' => 'Create Account', 'error' => 'All fields are required.'], 'auth');
            return;
        }

        if (User::findByEmail($email)) {
            $this->view('auth.register', ['title' => 'Create Account', 'error' => 'Email already exists.'], 'auth');
            return;
        }

        $id = User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => 'admin'
        ]);

        $user = User::find($id);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_email'] = $user['email'];

        $this->redirect('/admin');
    }

    public function logout(): void {
        $_SESSION = [];
        session_destroy();
        $this->redirect('/login');
    }
}
