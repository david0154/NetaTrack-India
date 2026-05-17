<?php
namespace NetaTrack\Controllers;

use NetaTrack\Core\Controller;
use NetaTrack\Models\User;

class AuthController extends Controller {
    public function loginForm(): void {
        $this->view('auth.login', ['title' => 'Admin Login'], 'auth');
    }

    public function login(): void {
        if (!$this->verifyCsrf()) {
            $this->flash('error', 'Invalid CSRF token.');
            $this->redirect('/login');
        }

        $email = trim((string)$this->input('email'));
        $password = (string)$this->input('password');

        $user = User::verify($email, $password);
        if (!$user) {
            $this->flash('error', 'Invalid credentials.');
            $this->redirect('/login');
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_email'] = $user['email'];
        session_regenerate_id(true);

        $this->flash('success', 'Welcome back, ' . $user['name'] . '.');
        $this->redirect('/admin');
    }

    public function registerForm(): void {
        $this->view('auth.register', ['title' => 'Create Account'], 'auth');
    }

    public function register(): void {
        if (!$this->verifyCsrf()) {
            $this->flash('error', 'Invalid CSRF token.');
            $this->redirect('/register');
        }

        $name = trim((string)$this->input('name'));
        $email = trim((string)$this->input('email'));
        $password = (string)$this->input('password');

        if (!$name || !$email || !$password) {
            $this->flash('error', 'All fields are required.');
            $this->redirect('/register');
        }

        if (User::findByEmail($email)) {
            $this->flash('error', 'Email already exists.');
            $this->redirect('/register');
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
        session_regenerate_id(true);

        $this->flash('success', 'Admin account created successfully.');
        $this->redirect('/admin');
    }

    public function logout(): void {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', $params['secure'] ?? false, $params['httponly'] ?? true);
        }
        session_destroy();
        session_start();
        $_SESSION['flash']['success'][] = 'Logged out successfully.';
        $this->redirect('/login');
    }
}
