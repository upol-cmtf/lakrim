<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Traits\AuthenticatesUsers;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected function getRedirectPath(): string
    {
        return '/admin/dashboard';
    }
}
