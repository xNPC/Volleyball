<?php

namespace App\Http\Controllers;

use App\Models\LegalDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LegalDocumentController extends Controller
{
    public function consent(): View
    {
        $document = LegalDocument::findBySlug(LegalDocument::CONSENT);

        return view('legal.consent', compact('document'));
    }

    public function terms(): View
    {
        $document = LegalDocument::findBySlug(LegalDocument::TERMS);

        return view('legal.terms', compact('document'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'personal_data_consent_confirm' => ['required', 'accepted'],
        ]);

        $user = $request->user();

        if ($user !== null && $user->personal_data_consent_at === null) {
            $document = LegalDocument::findBySlug(LegalDocument::CONSENT);

            $user->forceFill([
                'personal_data_consent_at' => now(),
                'personal_data_consent_ip' => $request->ip(),
                'personal_data_consent_user_agent' => $request->userAgent(),
                'personal_data_consent_doc_version' => $document?->updated_at,
            ])->save();
        }

        return redirect()->intended(route('dashboard'));
    }
}
