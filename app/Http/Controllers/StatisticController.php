<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Language;
use App\Models\Speech;
use App\Models\Speaker;
use App\Models\Word;
use App\Models\Quiz;

class StatisticController extends Controller
{
    public function getCategories(Request $request)
    {
        $sortKey = $request->input('sort_key', 'id');
        $sortDirection = $request->input('sort_direction', 'asc');

        if (!in_array(strtolower($sortDirection), ['asc', 'desc'])) {
            return response()->json(['message' => 'Invalid sort direction. Use "asc" or "desc".'], 400);
        }

        $data = Category::select('*')
            ->orderBy($sortKey, $sortDirection)
            ->get();


        return response()->json($data, 200);
    }

    public function addCategory(Request $request)
    {

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $data = new Category();
        $data->name = $request->name;
        $data->save();

        return response()->json([
            'message' => 'Category created successfully!',
            'data' => $data,
        ], 201);
    }

    public function getCategory($id)
    {
        $data = Category::find($id);

        if (!$data) {
            return response()->json(['message' => 'Category not found.'], 404);
        }


        return response()->json([
            'message' => 'Category retrieved successfully.',
            'data' => $data
        ], 200);
    }

    public function editCategory(Request $request, $id)
    {

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $data = Category::find($id);

        if (!$data) {
            return response()->json(['message' => 'Category not found.'], 404);
        }


        $data->name = $request->name;


        $data->save();


        return response()->json([
            'message' => 'Category updated successfully.',
            'data' => $data
        ], 200);
    }

    public function removeCategory($id)
    {
        $data = Category::find($id);

        if (!$data) {
            return response()->json(['message' => 'Category not found.'], 404);
        }


        $data->delete();


        return response()->json(['message' => 'Category removed successfully.'], 200);
    }

    public function getLanguages(Request $request)
    {
        $sortKey = $request->input('sort_key', 'id');
        $sortDirection = $request->input('sort_direction', 'asc');

        if (!in_array(strtolower($sortDirection), ['asc', 'desc'])) {
            return response()->json(['message' => 'Invalid sort direction. Use "asc" or "desc".'], 400);
        }

        $data = Language::select('*')
            ->orderBy($sortKey, $sortDirection)
            ->get();


        return response()->json($data, 200);
    }

    public function addLanguage(Request $request)
    {

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $data = new Language();
        $data->name = $request->name;
        $data->save();

        return response()->json([
            'message' => 'Language created successfully!',
            'data' => $data,
        ], 201);
    }

    public function getLanguage($id)
    {
        $data = Language::find($id);

        if (!$data) {
            return response()->json(['message' => 'Language not found.'], 404);
        }


        return response()->json([
            'message' => 'Language retrieved successfully.',
            'data' => $data
        ], 200);
    }

    public function editLanguage(Request $request, $id)
    {

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $data = Language::find($id);

        if (!$data) {
            return response()->json(['message' => 'Language not found.'], 404);
        }


        $data->name = $request->name;


        $data->save();


        return response()->json([
            'message' => 'Language updated successfully.',
            'data' => $data
        ], 200);
    }

    public function removeLanguage($id)
    {
        $data = Language::find($id);

        if (!$data) {
            return response()->json(['message' => 'Language not found.'], 404);
        }


        $data->delete();


        return response()->json(['message' => 'Language removed successfully.'], 200);
    }

    public function getInsights(Request $request)
    {
        $speeches = Speech::all();
        $speakers = Speaker::all()->keyBy('name');

        $byYear = [];

        foreach ($speeches as $speech) {
            $year = $speech->date ? substr($speech->date, 0, 4) : null;
            if (!$year || !is_numeric($year)) continue;

            $speaker = $speakers->get($speech->name);
            $gender = $speaker ? $speaker->gender : null;
            $language = $speech->language ?? 'Unknown';

            if (!isset($byYear[$year][$language])) {
                $byYear[$year][$language] = ['name' => $language, 'count' => 0, 'femaleCount' => 0, 'maleCount' => 0];
            }

            $byYear[$year][$language]['count']++;
            if ($gender === 'Female') $byYear[$year][$language]['femaleCount']++;
            if ($gender === 'Male') $byYear[$year][$language]['maleCount']++;
        }

        $result = [];
        foreach ($byYear as $year => $languages) {
            $result[$year] = array_values($languages);
        }

        $years = array_keys($result);
        rsort($years);

        return response()->json([
            'years' => $years,
            'byYear' => $result,
        ], 200);
    }

    public function getWords()
    {
        return response()->json(Word::orderBy('id')->get(), 200);
    }

    public function addWord(Request $request)
    {
        $request->validate([
            'word'    => 'required|string|max:255',
            'phoneme' => 'required|string|max:255',
            'meaning' => 'required|string',
        ]);

        $data = new Word();
        $data->word    = $request->word;
        $data->phoneme = $request->phoneme;
        $data->meaning = $request->meaning;
        $data->save();

        return response()->json(['message' => 'Word created successfully.', 'data' => $data], 201);
    }

    public function editWord(Request $request, $id)
    {
        $request->validate([
            'word'    => 'required|string|max:255',
            'phoneme' => 'required|string|max:255',
            'meaning' => 'required|string',
        ]);

        $data = Word::find($id);
        if (!$data) return response()->json(['message' => 'Word not found.'], 404);

        $data->word    = $request->word;
        $data->phoneme = $request->phoneme;
        $data->meaning = $request->meaning;
        $data->save();

        return response()->json(['message' => 'Word updated successfully.', 'data' => $data], 200);
    }

    public function removeWord($id)
    {
        $data = Word::find($id);
        if (!$data) return response()->json(['message' => 'Word not found.'], 404);

        $data->delete();
        return response()->json(['message' => 'Word removed successfully.'], 200);
    }

    public function getQuizzes()
    {
        return response()->json(Quiz::orderBy('id')->get(), 200);
    }

    public function addQuiz(Request $request)
    {
        $request->validate([
            'question'       => 'required|string',
            'option_a'       => 'required|string|max:255',
            'option_b'       => 'required|string|max:255',
            'option_c'       => 'required|string|max:255',
            'option_d'       => 'required|string|max:255',
            'correct_answer' => 'required|in:A,B,C,D',
        ]);

        $data = new Quiz();
        $data->question       = $request->question;
        $data->option_a       = $request->option_a;
        $data->option_b       = $request->option_b;
        $data->option_c       = $request->option_c;
        $data->option_d       = $request->option_d;
        $data->correct_answer = $request->correct_answer;
        $data->save();

        return response()->json(['message' => 'Quiz created successfully.', 'data' => $data], 201);
    }

    public function editQuiz(Request $request, $id)
    {
        $request->validate([
            'question'       => 'required|string',
            'option_a'       => 'required|string|max:255',
            'option_b'       => 'required|string|max:255',
            'option_c'       => 'required|string|max:255',
            'option_d'       => 'required|string|max:255',
            'correct_answer' => 'required|in:A,B,C,D',
        ]);

        $data = Quiz::find($id);
        if (!$data) return response()->json(['message' => 'Quiz not found.'], 404);

        $data->question       = $request->question;
        $data->option_a       = $request->option_a;
        $data->option_b       = $request->option_b;
        $data->option_c       = $request->option_c;
        $data->option_d       = $request->option_d;
        $data->correct_answer = $request->correct_answer;
        $data->save();

        return response()->json(['message' => 'Quiz updated successfully.', 'data' => $data], 200);
    }

    public function removeQuiz($id)
    {
        $data = Quiz::find($id);
        if (!$data) return response()->json(['message' => 'Quiz not found.'], 404);

        $data->delete();
        return response()->json(['message' => 'Quiz removed successfully.'], 200);
    }
}
