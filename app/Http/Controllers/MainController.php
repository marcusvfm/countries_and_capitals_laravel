<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class MainController extends Controller
{
    private $appData;

    public function __construct()
    {
        $this->appData = require(app_path("app_data.php"));
    }

    public function startGame(): View
    {
        return view('home');
    }

    public function prepareGame(Request $request)
    {
        // validate request
        $request->validate(
            [
                'total_questions' => 'required|integer|min:3|max:30'
            ],
            [
                'total_questions.required' => 'O número de questões é obrigatório',
                'total_questions.integer' => 'O número de questões deve ser um valor inteiro',
                'total_questions.min' => 'O número minímo de questões é :min',
                'total_questions.max' => 'O número máximo de questões é :max',
            ]
        );

        // get total questions
        $totalQuestions = intval($request->input('total_questions'));

        // prepare all the quiz structure
        $quiz = $this->prepareQuiz($totalQuestions);

        // store the quiz in session
        session()->put([
            'quiz' => $quiz,
            'total_questions' => $totalQuestions,
            'current_question' => 1,
            'correct_answers' => 0,
            'wrong_answers' => 0
        ]);

        return redirect()->route('game');
    }

    private function prepareQuiz(int $totalQuestions)
    {
        $questions = [];
        $totalCountries = count($this->appData);

        // create countries index for unique questions
        $indexes = range(0, $totalCountries - 1);
        shuffle($indexes);
        $indexes = array_slice($indexes, 0, $totalQuestions);

        // create array of questions
        $questionNumber = 1;
        foreach ($indexes as $index) {
            $question['question_number'] = $questionNumber;
            $question['country'] = $this->appData[$index]['country'];
            $question['correct_answer'] = $this->appData[$index]['capital'];

            // wrong answers
            $otherCapitals = array_column($this->appData, 'capital');

            // remove correct answer
            $otherCapitals = array_diff($otherCapitals, [$question['correct_answer']]);

            // shuffle the wrong answers
            shuffle($otherCapitals);
            $question['wrong_answers'] = array_slice($otherCapitals, 0, 3);

            // store answer result
            $question['correct'] = null;

            $questions[] = $question;
            $questionNumber++;
        }

        return $questions;
    }

    public function game(): View
    {
        $quiz = session('quiz');
        $totalQuestions = session('total_questions');
        $currentQuestion = session('current_question') - 1;

        // prepare answers to show in view
        $answers = $quiz[$currentQuestion]['wrong_answers'];
        $answers[] =  $quiz[$currentQuestion]['correct_answer'];
        shuffle($answers);

        return view('game')->with([
            'country' => $quiz[$currentQuestion]['country'],
            'totalQuestions' => $totalQuestions,
            'currentQuestion' => $currentQuestion,
            'answers' => $answers
        ]);
    }
}
