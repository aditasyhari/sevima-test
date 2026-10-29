<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Dispatch user to their role-specific landing page.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        if ($request->user()->isAdminKeuangan()) {
            return redirect()->route('admin.tagihan.index');
        }

        return redirect()->route('mahasiswa.dashboard');
    }
}
