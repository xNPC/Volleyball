<?php

namespace App\Services;

use App\Models\ApplicationRoster;
use App\Models\TournamentApplication;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\TemplateProcessor;

class ApplicationDocumentService
{
    public const TEMPLATE = 'technical_application.docx';

    public function build(TournamentApplication $application): string
    {
        $template = resource_path('templates/'.self::TEMPLATE);

        $processor = new TemplateProcessor($template);

        $processor->setMacroChars('[[', ']]');

        $roster = $application->roster()->with('user')->get();

$this->fillHead($processor, $application, $roster);

        $count = max($roster->count(), 1);

        $processor->cloneRow('номер', $count);

        for ($i = 1; $i <= $count; $i++) {
            /** @var \App\Models\ApplicationRoster|null $entry */
            $entry = $roster->get($i - 1);

            $processor->setValue('номер#'.$i, (string) $i);
            $processor->setValue('фио#'.$i, $entry ? $entry->user->name : '');
            $processor->setValue('игровой_номер#'.$i, $entry && $entry->jersey_number ? $entry->jersey_number : '');
            $processor->setValue('дата_рождения#'.$i, $entry && $entry->user->birthday ? $entry->user->birthday->format('d.m.Y') : '');
            $processor->setValue('амплуа#'.$i, $entry ? $this->position($entry) : '');
            $processor->setValue('примечание#'.$i, $entry && (bool) $entry->is_captain ? 'капитан' : '');
        }

        $path = $this->tempPath($application->id);

        $processor->saveAs($path);

        return $path;
    }

    public function downloadName(TournamentApplication $application): string
    {
        $tournament = $application->tournament ? $this->sanitize($application->tournament->name) : 'турнир';
        $team = $application->team ? $this->sanitize($application->team->name) : 'команда';

        return sprintf('Заявка_%s_%s.docx', $team, $tournament);
    }

    private function fillHead(TemplateProcessor $processor, TournamentApplication $application, $roster): void
    {
        $processor->setValue('турнир', $application->tournament->name ?? '');
        $processor->setValue('организация', $application->tournament->organization->name ?? '');
        $processor->setValue('команда', $application->team->name ?? '');

        $captain = $roster->first(function ($entry) {
            return (bool) $entry->is_captain;
        });

        if ($captain === null && $application->team && $application->team->captain_id) {
            $captain = $roster->firstWhere('user_id', $application->team->captain_id);
        }

        $processor->setValue('капитан_номер', $captain && $captain->jersey_number ? $captain->jersey_number : '');
        $processor->setValue('капитан', $captain && $captain->user ? $captain->user->name : '');

        $processor->setValue('дата', now()->format('d.m.Y'));
        $processor->setValue('год', now()->format('y'));
    }

    private function position($entry): string
    {
        return $entry->position && isset(ApplicationRoster::POSITIONS[$entry->position])
            ? ApplicationRoster::POSITIONS[$entry->position]
            : '';
    }

    private function sanitize(string $value): string
    {
        $value = preg_replace('/[\\\\\/:*?"<>|\r\n\t]+/u', '_', trim($value));

        return Str::limit($value ?: 'неизвестно', 60, '');
    }

    private function tempPath(int $applicationId): string
    {
        $dir = storage_path('app'.DIRECTORY_SEPARATOR.'generate');

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return $dir.DIRECTORY_SEPARATOR.'application_'.$applicationId.'_'.Str::random(8).'.docx';
    }
}