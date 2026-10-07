<?php

namespace App\Support;

use Orchid\Attachment\Models\Attachment;

class DocumentName
{
    public static function of(Attachment $attachment): string
    {
        $name = $attachment->original_name ?: $attachment->name.'.'.$attachment->extension;

        $extension = $attachment->extension;

        if ($extension === null || pathinfo($name, PATHINFO_EXTENSION) !== '') {
            return $name;
        }

        return $name.'.'.$extension;
    }
}