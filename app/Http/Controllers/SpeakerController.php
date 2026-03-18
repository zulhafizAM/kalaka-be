<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Speaker;

class SpeakerController extends Controller
{
    public function getSpeakers(Request $request)
    {
        $sortKey = $request->input('sort_key', 'id');
        $sortDirection = $request->input('sort_direction', 'asc');

        if (!in_array(strtolower($sortDirection), ['asc', 'desc'])) {
            return response()->json(['message' => 'Invalid sort direction. Use "asc" or "desc".'], 400);
        }

        $data = Speaker::select('*')
            ->orderBy($sortKey, $sortDirection)
            ->get();


        return response()->json($data, 200);
    }

    public function addSpeaker(Request $request)
    {

        $request->validate([
            'name' => 'required|string|max:255',
            'gender' => 'required|string|max:255',
            'age' => 'required|numeric',
            'kalaka_id' => 'required|string|max:255',
            'language' => 'required|string|max:255',
            'mobile' => 'required|string|max:255',
            'origin' => 'required|string|max:255',
            'recordings' => 'required|numeric',
            'current' => 'nullable|string|max:255',
        ]);

        $data = new Speaker();
        $data->name = $request->name;
        $data->gender = $request->gender;
        $data->age = $request->age;
        $data->kalaka_id = $request->kalaka_id;
        $data->language = $request->language;
        $data->mobile = $request->mobile;
        $data->origin = $request->origin;
        $data->recordings = $request->recordings;
        $data->current = $request->current;
        $data->save();

        return response()->json([
            'message' => 'Speaker created successfully!',
            'data' => $data,
        ], 201);
    }

    public function getSpeaker($id)
    {
        $data = Speaker::find($id);

        if (!$data) {
            return response()->json(['message' => 'Speaker not found.'], 404);
        }


        return response()->json([
            'message' => 'Speaker retrieved successfully.',
            'data' => $data
        ], 200);
    }

    public function editSpeaker(Request $request, $id)
    {

        $request->validate([
            'name' => 'required|string|max:255',
            'gender' => 'required|string|max:255',
            'age' => 'required|numeric',
            'kalaka_id' => 'required|string|max:255',
            'language' => 'required|string|max:255',
            'mobile' => 'required|string|max:255',
            'origin' => 'required|string|max:255',
            'recordings' => 'required|numeric',
            'current' => 'string|max:255',
        ]);

        $data = Speaker::find($id);

        if (!$data) {
            return response()->json(['message' => 'Speaker not found.'], 404);
        }


        $data->name = $request->name;
        $data->gender = $request->gender;
        $data->age = $request->age;
        $data->kalaka_id = $request->kalaka_id;
        $data->language = $request->language;
        $data->mobile = $request->mobile;
        $data->origin = $request->origin;
        $data->recordings = $request->recordings;
        $data->current = $request->current;


        $data->save();


        return response()->json([
            'message' => 'Speaker updated successfully.',
            'data' => $data
        ], 200);
    }

    public function removeSpeaker($id)
    {
        $data = Speaker::find($id);

        if (!$data) {
            return response()->json(['message' => 'Speaker not found.'], 404);
        }


        $data->delete();


        return response()->json(['message' => 'Speaker removed successfully.'], 200);
    }
}
