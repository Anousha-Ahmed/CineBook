<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GenreController extends Controller
{
    public function index()
    {
        $genres = Genre::withCount('movies')->orderBy('name')->get();

        return view('admin.genres.index', compact('genres'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:60', 'unique:genres,name'],
        ]);

        $genre = Genre::create($data);

        AuditLogger::log('genre.created', $genre, [], $genre->only(['name']));

        return back()->with('success', 'Genre added.');
    }

    public function update(Request $request, Genre $genre)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:60', Rule::unique('genres', 'name')->ignore($genre->id)],
        ]);

        $genre->update($data);

        AuditLogger::log('genre.updated', $genre, [], $genre->only(['name']));

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
