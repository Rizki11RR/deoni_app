<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(): View
    {
        return view('audit-logs.index', [
            'logs' => AuditLog::with('user')->latest()->paginate(25),
        ]);
    }
}
