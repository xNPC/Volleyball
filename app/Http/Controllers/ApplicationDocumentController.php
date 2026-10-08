<?php

namespace App\Http\Controllers;

use App\Models\TournamentApplication;
use App\Services\ApplicationDocumentService;
use Illuminate\Http\Request;

class ApplicationDocumentController extends Controller
{
    public function download(TournamentApplication $application, ApplicationDocumentService $service, Request $request)
    {
        abort_unless($application->downloadableBy($request->user()), 403);

        $path = $service->build($application);

        return response()->download($path, $service->downloadName($application))->deleteFileAfterSend(true);
    }
}