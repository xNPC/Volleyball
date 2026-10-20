<?php

namespace App\Http\Middleware;

use App\Models\LegalDocument;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePersonalDataConsent
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || $user->personal_data_consent_at !== null) {
            return $next($request);
        }

        if (LegalDocument::findBySlug(LegalDocument::CONSENT) === null) {
            return $next($request);
        }

        $request->session()->put('url.intended', $request->fullUrl());

        return redirect()->route('legal.consent');
    }
}
