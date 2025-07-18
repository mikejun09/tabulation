<?php

namespace App\Livewire;

use App\Models\Log;
use App\Models\RefCriteria;
use Livewire\Component;
use App\Models\RefParticipant;
use App\Models\RefJudge;
use App\Models\Poster as PosterModel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Poster extends Component
{
    public $search = '', $base64pdf = '', $judge_id;
    public function render()
    {
        $participants = RefParticipant::where('participant_no', 'like', '%' . $this->search . '%')->where('category', 'poster')->get();
        $judges = RefJudge::where('id', 'like', '%' . $this->judge_id . '%')->where('category', 'poster')->get();
        $criterias = RefCriteria::where('category', 'poster')->get();
        $part = RefParticipant::where('category', 'poster')->get();
        $jud  = RefJudge::where('category', 'poster')->get();
        return view('livewire.poster', compact('participants', 'judges', 'criterias', 'part', 'jud'));
    }
    public function saveScore($participant_id, $criteria_id, $judge_id, $score)
    {
        $poster = PosterModel::where('participant_id', $participant_id)->where('criteria_id', $criteria_id)->where('judge_id', $judge_id)->first();
        if (!$poster) {
            $poster = new PosterModel();
            $poster->participant_id = $participant_id;
            $poster->criteria_id = $criteria_id;
            $poster->judge_id = $judge_id;
        }
        Log::create([
            'user_id' => Auth::user()->id,
            'activity' => 'Poster id ' . $poster->id . ' Score has been updated from ' . $poster->score . ' to ' . $score,
        ]);
        $poster->score = $score ? $score : 0;
        $poster->save();
    }
    public function generateReport()
    {
        $paper = array(0, 0, 1400, 850);
        $judges = RefJudge::where('category', 'poster')->get();
        $participants = RefParticipant::where('category', 'poster')
            ->leftjoin('posters', 'ref_participants.id', '=', 'posters.participant_id')
            ->groupBy('ref_participants.id')
            ->orderByRaw('SUM(posters.score) DESC')
            ->select('ref_participants.*',  DB::raw('SUM(posters.score) as total_score'))
            ->get();
        $poster = PosterModel::all();
        $criterias = RefCriteria::where('category', 'poster')->get();
        $pdf = Pdf::loadView('generated_pdf.poster', compact('participants', 'poster', 'criterias', 'judges'))->setPaper('letter', 'portrait');
        $this->base64pdf = base64_encode($pdf->output());
        $this->dispatch('openModal');
    }
}
