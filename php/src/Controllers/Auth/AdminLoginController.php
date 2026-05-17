<?php
namespace NetaTrack\Controllers\Auth;

use NetaTrack\Core\Controller;
use NetaTrack\Core\Auth;

/**
 * NetaTrack India - Admin Login Controller
 */
class AdminLoginController extends Controller
{
    public function show(): void
    {
        if (Auth::getInstance()->isAdmin()) {
            $this->redirect(url('admin'));
        }
        $this->view('auth.admin-login', [], 'layouts/auth');
    }

    public function login(): void
    {
        $this->verifyCsrf();

        $email    = $this->input('email', '');
        $password = $this->input('password', '');
        $auth     = Auth::getInstance();

        // Login attempt tracking
        $key     = 'login_attempts_' . md5($email);
        $attempts= $_SESSION[$key]['count'] ?? 0;
        $lockedUntil = $_SESSION[$key]['locked_until'] ?? 0;

        if ($lockedUntil > time()) {
            $mins = ceil(($lockedUntil - time()) / 60);
            flash('error', "Too many attempts. Try again in {$mins} minutes.");
            $this->redirect(url('admin/login'));
        }

        if ($auth->attempt($email, $password) && $auth->isAdmin()) {
            $_SESSION[$key] = ['count' => 0, 'locked_until' => 0];
            flash('success', 'Welcome back, ' . $auth->user()['name'] . '!');
            $this->redirect(url('admin'));
        } else {
            $attempts++;
            if ($attempts >= 5) {
                $_SESSION[$key] = ['count' => $attempts, 'locked_until' => time() + 900];
                flash('error', 'Account locked for 15 minutes due to too many failed attempts.');
            } else {
                $_SESSION[$key] = ['count' => $attempts, 'locked_until' => 0];
                flash('error', 'Invalid credentials or insufficient permissions. (' . (5 - $attempts) . ' attempts left)');
            }
            $this->redirect(url('admin/login'));
        }
    }

    public function logout(): void
    {
        Auth::getInstance()->logout();
        flash('success', 'Logged out successfully.');
        $this->redirect(url('admin/login'));
    }
}
