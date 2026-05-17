<?php
namespace NetaTrack\Controllers;

use NetaTrack\Core\{Controller, Request, Response};
use NetaTrack\Models\User;

class AuthController extends Controller
{
    private User $users;

    public function __construct()
    {
        $this->users = new User();
    }

    // GET /auth/login
    public function loginForm(Request $req, Response $res): void
    {
        if (auth()->check()) { $res->redirect(''); return; }
        $res->view('auth/login', ['page_title' => 'Login']);
    }

    // POST /auth/login
    public function login(Request $req, Response $res): void
    {
        if (!$req->verifyCsrf()) { $res->redirect('auth/login'); return; }

        $email    = trim($req->post('email',''));
        $password = $req->post('password','');
        $user     = $this->users->findByEmail($email);

        if (!$user || !$this->users->verifyPassword($password, $user['password'])) {
            flash('error', 'Invalid email or password.');
            $res->redirect('auth/login');
            return;
        }

        if ($user['status'] === 'banned') {
            flash('error', 'Your account has been suspended.');
            $res->redirect('auth/login');
            return;
        }

        // Regenerate session
        session_regenerate_id(true);
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_role'] = $user['role'];

        $redirect = $user['role'] === 'admin' ? 'admin' : '';
        $res->redirect($redirect);
    }

    // GET /auth/register
    public function registerForm(Request $req, Response $res): void
    {
        if (auth()->check()) { $res->redirect(''); return; }
        $res->view('auth/register', ['page_title' => 'Register']);
    }

    // POST /auth/register
    public function register(Request $req, Response $res): void
    {
        if (!$req->verifyCsrf()) { $res->redirect('auth/register'); return; }

        $name     = trim($req->post('name',''));
        $email    = trim($req->post('email',''));
        $password = $req->post('password','');
        $confirm  = $req->post('password_confirm','');

        $errors = [];
        if (strlen($name) < 2)      $errors[] = 'Name is too short.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email address.';
        if (strlen($password) < 8)  $errors[] = 'Password must be at least 8 characters.';
        if ($password !== $confirm)  $errors[] = 'Passwords do not match.';
        if ($this->users->findByEmail($email)) $errors[] = 'Email is already registered.';

        if ($errors) {
            flash('error', implode(' ', $errors));
            $res->redirect('auth/register');
            return;
        }

        $id = $this->users->create(['name'=>$name,'email'=>$email,'password'=>$password]);
        session_regenerate_id(true);
        $_SESSION['user_id']   = $id;
        $_SESSION['user_role'] = 'user';
        flash('success', 'Welcome to NetaTrack!');
        $res->redirect('');
    }

    // GET /auth/logout
    public function logout(Request $req, Response $res): void
    {
        session_destroy();
        $res->redirect('auth/login');
    }

    // GET /admin/auth/login  (separate admin login)
    public function adminLoginForm(Request $req, Response $res): void
    {
        if (auth()->isAdmin()) { $res->redirect('admin'); return; }
        $res->view('auth/admin-login', ['page_title' => 'Admin Login']);
    }

    // POST /admin/auth/login
    public function adminLogin(Request $req, Response $res): void
    {
        if (!$req->verifyCsrf()) { $res->redirect('admin/auth/login'); return; }

        $email    = trim($req->post('email',''));
        $password = $req->post('password','');
        $user     = $this->users->findByEmail($email);

        if (!$user || $user['role'] !== 'admin' || !$this->users->verifyPassword($password, $user['password'])) {
            flash('error', 'Invalid credentials or insufficient permissions.');
            $res->redirect('admin/auth/login');
            return;
        }

        session_regenerate_id(true);
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_role'] = 'admin';
        $res->redirect('admin');
    }
}
