<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function switch(Request $request, string $locale): RedirectResponse
    {
        if (!in_array($locale, ['en', 'es'], true)) {
            abort(404);
        }

        session(['locale' => $locale]);

        return redirect()
            ->back(fallback: route('calendar.index'))
            ->withCookie(cookie()->forever('locale', $locale, '/', null, false, false));
    }
}
