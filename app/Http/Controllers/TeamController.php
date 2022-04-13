<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Team;

class TeamController extends Controller
{
    public function index(){
        $teams = Team::all();

        return view('app.teams',['teams' => $teams]);
    }

    public function teams() {

        $user = auth()->user();
        $teams = $user->teams;    

        return view('app.teams', ['teams' => $teams]);
    }
}
