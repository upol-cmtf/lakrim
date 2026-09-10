<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuizEvent;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            // názvy událostí (kurzů) pro filtr exportu; stejný název může mít víc hashů
            'eventNames' => QuizEvent::query()->distinct()->orderBy('name')->pluck('name'),
        ]);
    }
}
