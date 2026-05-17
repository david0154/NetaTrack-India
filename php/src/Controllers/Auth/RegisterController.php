<?php
namespace NetaTrack\Controllers\Auth;

use NetaTrack\Core\Controller;
use NetaTrack\Models\User;

/**
 * NetaTrack India - User Registration Controller
 */
class RegisterController extends Controller
{
    public function showForm(): void
    {
        $this->view('auth.register', [], 'layouts/auth');
    }

    public function register(): void
    {
        $this->verifyCsrf();

        $errors = $this->validate([
            'name'     => 'required|min:2|max:100',
            'email'    => 'required|email',
            'password' => 'required|min:8',
        ]);

        if (!empty($errors)) {
            flash('errors', $errors);
            $_SESSION['old'] = $this->inputAll();
            $this->redirect(url('auth/register'));
        }

        $user = new User();
        if ($user->findByEmail($this->input('email'))) {
            flash('error', 'Email already registered.');
            $this->redirect(url('auth/register'));
        }

        $user->createUser([
            'name'     => $this->input('name'),
            'email'    => $this->input('email'),
            'password' => $this->input('password'),
        ]);

        flash('success', 'Account created! Please log in.');
        $this->redirect(url('auth/login'));
    }
}
