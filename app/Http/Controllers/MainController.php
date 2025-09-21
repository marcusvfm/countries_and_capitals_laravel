<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Http\RedirectResponse;

class MainController extends Controller
{
    private $appData;

    public function __construct()
    {
        $this->appData = require(app_path("app_data.php"));
    }

    public function startGame(): View|RedirectResponse
    {
        $token = session('_token');
        session()->flush();
        session(['_token' => $token]);

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
            'wrong_answers' => 0,
            'end_game' => false
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

    public function game(): View|RedirectResponse
    {
        if (!session()->has('quiz')) {
            return redirect()->route('start_game');
        }

        if (session('end_game') == true) {
            return redirect()->route('show_results');
        }

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

    public function answer($encAnswer)
    {
        if (!session()->has('quiz')) {
            return redirect()->route('start_game');
        }

        try {
            $answer = Crypt::decryptString($encAnswer);
        } catch (\Exception $e) {
            return redirect()->route('game');
        }

        // game logic
        $quiz = session('quiz');
        $currentQuestion = session('current_question') - 1;
        $correctAnswer = $quiz[$currentQuestion]['correct_answer'];
        $correctAnswers = session('correct_answers');
        $wrongAnswers = session('wrong_answers');

        if ($answer == $correctAnswer) {
            $correctAnswers++;
            $quiz[$currentQuestion]['correct'] = true;
        } else {
            $wrongAnswers++;
            $quiz[$currentQuestion]['correct'] = false;
        }

        // update session
        session()->put([
            'quiz' => $quiz,
            'correct_answers' => $correctAnswers,
            'wrong_answers' => $wrongAnswers
        ]);

        // prepare data to show correct answer
        $data = [
            'country' => $quiz[$currentQuestion]['country'],
            'correctAnswer' => $correctAnswer,
            'choiceAnswer' => $answer,
            'currentQuestion' => $currentQuestion,
            'totalQuestions' => session('total_questions')
        ];

        return view('answer_result')->with($data);
    }

    public function nextQuestion()
    {
        if (!session()->has('quiz')) {
            return redirect()->route('start_game');
        }

        $currentQuestion = session('current_question');
        $totalQuestions = session('total_questions');

        // check if the game is over
        if ($currentQuestion < $totalQuestions) {
            $currentQuestion++;
            session()->put('current_question', $currentQuestion);
            return redirect()->route('game');
        } else {
            session()->put('end_game', true);
            return redirect()->route('show_results');
        }
    }

    public function showResults(): View|RedirectResponse
    {
        if (!session()->has('quiz')) {
            return redirect()->route('start_game');
        }

        if (session('end_game') == false) {
            return redirect()->route('game');
        }

        $totalQuestions = session('total_questions');
        $correctAnswers = session('correct_answers');
        $wrongAnswers = session('wrong_answers');

        return view('show_results')->with([
            'totalQuestions' => $totalQuestions,
            'correctAnswers' => $correctAnswers,
            'wrongAnswers' => $wrongAnswers,
            'scoreFinal' => round($correctAnswers / $totalQuestions * 100, 2)
        ]);
    }
}
