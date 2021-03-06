<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Championship;
use App\Models\Team;
use App\Models\Game;
use App\Models\Table;
use App\Models\Statistic;

class ChampionshipController extends Controller
{
    public function campeonatos() {
        //$championships = Championship::all();

        $user = auth()->user();
        $championships = $user->championships;    

        return view('app.campeonatos', ['championships' => $championships]);
    }

    public function painel(Request $request, $id) {
        $user = auth()->user();
        
        $championship = Championship::findOrFail($id);
        $teams = $championship->teams;
        
        $request->session()->put('championship', $championship);

        return view('app.painel', ['championship' => $championship, 'teams' => $teams]);
    }

    public function campeonato($id) {
        $user = auth()->user();
        //$teams = $user->teams;

        $championship = Championship::findOrFail($id);
        $teams = $championship->teams;
        $games = Game::with('team1','team2','result')->where('championship_id', '=', $id)->get();
        $tables = Table::with('team')
                    ->where('Championship_id', '=', $id)
                    ->orderBy('points', 'desc')
                    ->orderBy('victory', 'desc')
                    ->orderBy('sg', 'desc')
                    ->orderBy('id', 'asc')
                    ->get();
        $gols = Statistic::selectRaw('count(*) as qtd_gols, player_id')
                    ->with('player')
                    ->where('championship_id', '=', $id)
                    ->where('type', '=', 'gol')
                    ->groupBy('player_id')
                    ->orderByDesc('qtd_gols')
                    ->get();
        $amarelos = Statistic::selectRaw('count(*) as qtd_amarelos, player_id')
                    ->with('player')
                    ->where('championship_id', '=', $id)
                    ->where('type', '=', 'amarelo')
                    ->groupBy('player_id')
                    ->orderByDesc('qtd_amarelos')
                    ->get();
        $vermelhos = Statistic::selectRaw('count(*) as qtd_vermelhos, player_id')
                    ->with('player')
                    ->where('championship_id', '=', $id)
                    ->where('type', '=', 'vermelho')
                    ->groupBy('player_id')
                    ->orderByDesc('qtd_vermelhos')
                    ->get();

        return view('site.campeonato', 
                    [
                        'championship' => $championship, 
                        'teams' => $teams, 
                        'games' => $games, 
                        'tables' => $tables, 
                        'gols' => $gols, 
                        'amarelos' => $amarelos, 
                        'vermelhos' => $vermelhos
                    ]);
    }

    public function store(Request $request) {
        $championship = new Championship;
        $forma = $request->forma;
        
        $championship->title = $request->title;
        $championship->localization = $request->localization;
        if ($request->award == '') {
            $championship->award = '';
        }else {
            $championship->award = $request->award;
        }
        $championship->modality = $request->modality;
        /*
        if ($forma == '1') {
            $championship->mata_mata = '1';
            $championship->groups = '0';
            $championship->running_stitches = '0';
        }elseif ($forma == '2') {
            $championship->groups = '1';
            $championship->mata_mata = '0';
            $championship->running_stitches = '0';
        }elseif ($forma == '3') {
            $championship->groups = '0';
            $championship->mata_mata = '0';
            $championship->running_stitches = '1';
        }
        */
        $championship->groups = '0';
        $championship->mata_mata = '0';
        $championship->running_stitches = '1';
        
        $championship->return = $request->return;
        $championship->initiated = '0';
        $championship->start = $request->start;
        $championship->end = $request->end;

        $user = auth()->user();
        $championship->user_id = $user->id;

        $championship->save();

        return redirect('/app/campeonatos');
    }

    public function destroy($id) {
        Championship::findOrFail($id)->delete();

        return redirect('/app/campeonatos');
    }

    public function update(Request $request){
        Championship::findOrFail($request->id)->update($request->all());

        return redirect('/app/campeonatos');
    }

    public function iniciar($id){
        $championship = Championship::where('id', '=', $id)->firstOrFail();
        $championship->initiated = '1';
        $championship->save();
        session()->put('championship', $championship);

        return redirect()->route('games', ['id' => $id]);
    }

    public function ultimosCampeonatos() {
        $championships = Championship::with('user')->where('initiated', '=', 1)->orderByDesc('id')->limit(6)->get();   

        return view('home', ['championships' => $championships]);
    }

}
