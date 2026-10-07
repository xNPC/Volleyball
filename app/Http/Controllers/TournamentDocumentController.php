<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use App\Support\DocumentName;
use Orchid\Attachment\Models\Attachment;
use Illuminate\Support\Facades\Storage;

class TournamentDocumentController extends Controller
{
    public function download(Tournament $tournament, Attachment $attachment)
    {
        $attachment = $tournament->attachments()->whereKey($attachment->id)->firstOrFail();

        return Storage::disk($attachment->disk)->download($attachment->physicalPath(), DocumentName::of($attachment));
    }
}