<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Movie;




class DashboardController extends Controller
{
    public function index()
    {

        // dd(auth()->user());

        $movies = Movie::orderBy('featured', 'DESC')->orderBy('created_at', 'DESC')->get();


        return view('member.dashboard', ['movies' => $movies]);
    }
}
