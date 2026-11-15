<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use App\Models\Movie;
use App\Services\AuditLogger;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    public function index(Request $request)
    {
        $movies = Movie::with('genres')
            ->withCount('showtimes')
            ->search($request->query('search'))
            ->orderBy('title')
            ->paginate(10)
            ->withQueryString();

        return view('admin.movies.index', compact('movies'));
    }

    public function create()
    {
        $genres = Genre::orderBy('name')->get();

        return view('admin.movies.form', ['movie' => new Movie(), 'genres' => $genres]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        if ($request->hasFile('poster')) {
            $data['poster_url'] = \Illuminate\Support\Facades\Storage::disk('public')
                ->url($request->file('poster')->store('posters', 'public'));
        }

        $movie = Movie::create($data);
        $movie->genres()->sync($request->input('genres', []));

        AuditLogger::log('movie.created', $movie, [], $movie->only(['title', 'duration_minutes', 'rating']));

        return redirect()->route('admin.movies.index')->with('success', 'Movie created.');
    }

    public function edit(Movie $movie)
    {
        $genres = Genre::orderBy('name')->get();
        $movie->load('genres');

        return view('admin.movies.form', compact('movie', 'genres'));
    }

    public function update(Request $request, Movie $movie)
    {
        $data = $this->validated($request);

        if ($request->hasFile('poster')) {
            $data['poster_url'] = \Illuminate\Support\Facades\Storage::disk('public')
                ->url($request->file('poster')->store('posters', 'public'));
        }

        $before = $movie->only(['title', 'duration_minutes', 'rating', 'is_featured']);

        $movie->update($data);
        $movie->genres()->sync($request->input('genres', []));

        AuditLogger::log('movie.updated', $movie, $before, $movie->only(['title', 'duration_minutes', 'rating', 'is_featured']));

        return redirect()->route('admin.movies.index')->with('success', 'Movie updated.');
    }

    public function destroy(Movie $movie)
    {
        if ($movie->showtimes()->exists()) {
            return back()->with('error', 'This movie has showtimes. Remove its showtimes first.');
        }

        $before = $movie->only(['title', 'duration_minutes', 'rating']);

        $movie->genres()->detach();
        $movie->delete();

        AuditLogger::log('movie.deleted', null, $before, [], ['movie_id' => $movie->id]);

        return redirect()->route('admin.movies.index')->with('success', 'Movie deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'synopsis' => ['nullable', 'string', 'max:2000'],
            'duration_minutes' => ['required', 'integer', 'min:20', 'max:400'],
            'rating' => ['required', 'string', 'max:12'],
            'poster_url' => ['nullable', 'url', 'max:500'],
            'trailer_url' => ['nullable', 'url', 'max:500'],
            'poster' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'is_featured' => ['nullable', 'boolean'],
            'genres' => ['nullable', 'array'],
            'genres.*' => ['integer', 'exists:genres,id'],
        ]);

        $data['is_featured'] = $request->boolean('is_featured');

        return $data;
    }
}
