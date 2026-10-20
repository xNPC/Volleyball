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

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'personal_data_consent_confirm' => ['required', 'accepted'],
        ]);

        $user = $request->user();

        if ($user !== null && $user->personal_data_consent_at === null) {
            $user->forceFill(['personal_data_consent_at' => now()])->save();
        }

        return redirect()->intended(route('dashboard'));
    }
}
