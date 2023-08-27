<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Movie;
use Illuminate\Support\Facades\Storage;

class MovieController extends Controller
{
    public function index()
    {

        $movies = Movie::all();
        return view('admin.movies', ['movies' => $movies]);
    }

    public function create()
    {
        return view('admin.movie-create');
    }

    public function edit($id)
    {
        $movie = Movie::find($id);

        return view('admin.movie-edit', ['movie' => $movie]);
    }

    public function store(Request $request)
    {
        $data = $request->except('_token');

        $request->validate([
            'title' => 'required|string',
            'small_thumbnail' => 'required|image|mimes:jpeg,jpg,png',
            'large_thumbnail' => 'required|image|mimes:jpeg,jpg,png',
            'trailer' => 'required|url',
            'movie' => 'required|url',
            'casts' => 'required|string',
            'categories' => 'required|string',
            'release_date' => 'required|string',
            'about' => 'required|string',
            'short_about' => 'required|string',
            'duration' => 'required|string',
            'featured' => 'required'
        ]);

        $smallthumbnail = $request->small_thumbnail;
        $largeThumbnail = $request->large_thumbnail;

        $originalSmallThumbnailName = Str::random(10) . $smallthumbnail->getClientOriginalName();
        $originallargeThumbnailName = Str::random(10) . $largeThumbnail->getClientOriginalName();

        $smallthumbnail->storeAs('public/thumbnail', $originalSmallThumbnailName);
        $largeThumbnail->storeAs('public/thumbnail', $originallargeThumbnailName);

        $data['large_thumbnail'] = $originallargeThumbnailName;
        $data['small_thumbnail'] = $originalSmallThumbnailName;

        Movie::create(($data));

        return redirect()->route('admin.movie')->with('success', 'Movie Created');
    }

    public function update(Request $request, $id)
    {
        $data = $request->except('_token');

        $request->validate([
            'title' => 'required|string',
            'small_thumbnail' => 'image|mimes:jpeg,jpg,png',
            'large_thumbnail' => 'image|mimes:jpeg,jpg,png',
            'trailer' => 'required|url',
            'movie' => 'required|url',
            'casts' => 'required|string',
            'categories' => 'required|string',
            'release_date' => 'required|string',
            'about' => 'required|string',
            'short_about' => 'required|string',
            'duration' => 'required|string',
            'featured' => 'required'
        ]);

        $movie = Movie::find($id);

        if ($request->small_thumbnail) {
            //save new image
            $smallthumbnail = $request->small_thumbnail;
            $originalSmallThumbnailName = Str::random(10) . $smallthumbnail->getClientOriginalName();
            $smallthumbnail->storeAs('public/thumbnail', $originalSmallThumbnailName);
            $data['small_thumbnail'] =  $originalSmallThumbnailName;

            //delete old image
            Storage::delete('/public/thumbnail/' . $movie->small_thumbnail);
        }

        if ($request->large_thumbnail) {
            //save new image
            $largeThumbnail = $request->large_thumbnail;
            $originallargeThumbnailName = Str::random(10) . $largeThumbnail->getClientOriginalName();
            $largeThumbnail->storeAs('public/thumbnail', $originallargeThumbnailName);
            $data['large_thumbnail'] =  $originallargeThumbnailName;

            //delete old image
            Storage::delete('/public/thumbnail/' . $movie->large_thumbnail);
        }
        $movie->update($data);

        return redirect()->route('admin.movie')->with('success', 'Movie Updated');
    }

    public function destroy($id)
    {
        Movie::find($id)->delete();

        return redirect()->route('admin.movie')->with('success', 'Movie Deleted');
    }
}
