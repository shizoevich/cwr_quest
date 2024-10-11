<?php

namespace App\Http\Controllers;

use App\Answer;
use App\Question;
use App\Training;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ExamsController extends Controller
{
    /**
     * Display Exams page
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = \Auth::user();

        $training = Training::create([
            'user_id' => $user->id,
        ]);

        return response()->view("exams.main", [
            "questions" => Question::orderByRaw('RAND()')->take(14)->with('answers')->get()->toArray(),
            "training_id" => $training->id
        ]);
    }

    /**
     * Display Exam results page
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function showResult(Request $request)
    {
        $answers = $request->input('answers');

        $result = 0;
        if ($answers) {
            foreach ($answers as $answer) {
                if (Answer::find($answer)->is_correct) {
                    $result++;
                }
            }
        }

        Training::query()
            ->findOrFail($request->input('training'))
            ->update($result > 10 ? [
                'certificate_number' => Str::random(16),
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addYear(),
                'score' => $result
            ] : [
                'score' => $result
            ]);

        return response()->view("exams.result", [
            "result" => $result
        ]);
    }

    /**
     * Save exam progress
     *
     * @param $result
     * @param $training_id
     */
    protected function saveTraining($result, $training_id)
    {
        $model = Training::findOrFail($training_id);
        $model->score = $result;
        $model->save();
    }
}
