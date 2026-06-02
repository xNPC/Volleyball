<?php

namespace App\Http\Controllers;

use App\Models\Album;

class GalleryController extends Controller
{
    public function index()
    {
        $albums = Album::with(['photos' => function($query) {
            $query->orderBy('sort_order');
        }])->with(['cover'])
            ->withCount('photos')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('gallery.index', compact('albums'));
    }

    public function show($slug)
    {
        $album = Album::where('slug', $slug)
            ->where('is_active', true)
            ->with(['photos' => function($query) {
                $query->orderBy('sort_order');
            }])
            ->firstOrFail();

        return view('gallery.show', compact('album'));
    }
}
