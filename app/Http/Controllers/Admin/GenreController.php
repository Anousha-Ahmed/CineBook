<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GenreRequest;
use App\Models\Genre;
use App\Services\AuditLogger;

class GenreController extends Controller
{
    public function index()
    {
        $genres = Genre::withCount('movies')->orderBy('name')->get();

        return view('admin.genres.index', compact('genres'));
    }

    public function store(GenreRequest $request)
    {
        $genre = Genre::create($request->validated());

        AuditLogger::log('genre.created', $genre, [], $genre->only(['name']));

        return back()->with('success', 'Genre added.');
    }

    public function update(GenreRequest $request, Genre $genre)
    {
        $before = $genre->only(['name']);

        $genre->update($request->validated());

        AuditLogger::log('genre.updated', $genre, $before, $genre->only(['name']));

        return back()->with('success', 'Genre updated.');
    }

    public function destroy(Genre $genre)
    {
        if ($genre->movies()->exists()) {
            return back()->with('error', 'This genre is still attached to movies. Remove it from them first.');
        }

        $genre->delete();

        AuditLogger::log('genre.deleted', null, ['genre' => $genre->name]);

        return back()->with('success', 'Genre deleted.');
    }
}
