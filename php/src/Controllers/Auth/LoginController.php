<?php
namespace NetaTrack\Controllers\Auth;

use NetaTrack\Core\Controller;
use NetaTrack\Core\Auth;

/**
 * NetaTrack India - Public User Login Controller
 */
class LoginController extends Controller
{
    public function showLogin(): void
    {
        if (Auth::getInstance()->check()) {
            $this->redirect(url('/'));
        }
        $this->view('auth.login', [], 'layouts/auth');
    }

    public function login(): void
    {
        $this->verifyCsrf();
        $email    = $this->input('email', '');
        $password = $this->input('password', '');

        if (Auth::getInstance()->attempt($email, $password)) {
            flash('success', 'Logged in successfully.');
            $this->redirect($this->input('redirect', url('/')));
        } else {
            flash('error', 'Invalid email or password.');
            $this->redirect(url('auth/login'));
        }
    }

    public function logout(): void
    {
        Auth::getInstance()->logout();
        $this->redirect(url('/'));
    }
}
