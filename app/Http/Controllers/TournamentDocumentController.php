<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use Orchid\Attachment\Models\Attachment;

class TournamentDocumentController extends Controller
{
    public function download(Tournament $tournament, Attachment $attachment)
    {
        $attachment = $tournament->attachments()->whereKey($attachment->id)->firstOrFail();

        return $attachment->download();
    }
}