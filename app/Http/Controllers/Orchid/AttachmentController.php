<?php

namespace App\Http\Controllers\Orchid;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Orchid\Attachment\Models\Attachment;
use Orchid\Platform\Dashboard;
use Orchid\Platform\Http\Controllers\AttachmentController as BaseAttachmentController;

class AttachmentController extends BaseAttachmentController
{
    public const TEAM_LOGO_GROUP = 'team_logo';

    public function __construct()
    {
        $this->attachment = Dashboard::modelClass(Attachment::class);

        $this->middleware(function ($request, $next) {
            if (Auth::user()?->hasAccess('platform.systems.attachment')) {
                return $next($request);
            }

            $method = $request->route()?->getActionMethod();

            if ($method === 'upload' && $request->input('group') === self::TEAM_LOGO_GROUP) {
                return $next($request);
            }

            if (in_array($method, ['destroy', 'update'], true)) {
                $attachment = $this->attachment->find($request->route('id'));

                if ($attachment && $attachment->group === self::TEAM_LOGO_GROUP) {
                    return $next($request);
                }
            }

            abort(403);
        });
    }

    public function upload(Request $request): JsonResponse
    {
        if ($request->input('group') === self::TEAM_LOGO_GROUP) {
            $request->validate([
                'files.*' => 'image|mimes:jpeg,png,webp|max:5120',
            ], [
                'files.*.image' => 'Логотип должен быть изображением.',
                'files.*.mimes' => 'Допустимые форматы логотипа: JPEG, PNG, WebP.',
                'files.*.max' => 'Размер логотипа не должен превышать 5 МБ.',
            ]);
        }

        return parent::upload($request);
    }
}
