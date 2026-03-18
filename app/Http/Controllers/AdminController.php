<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Option;
use App\Models\Speech;
use App\Models\Speaker;

class AdminController extends Controller
{
    public function getOptions(Request $request)
    {
        $data = Option::select('*')
            ->get();

        return response()->json($data, 200);
    }

    public function getCounts(Request $request)
    {
        $kalakaId = $request->query('kalaka_id');

        if ($kalakaId) {
            return response()->json([
                'total_speeches' => Speech::where('kalaka_id', $kalakaId)->count(),
                'total_speakers' => Speaker::where('kalaka_id', $kalakaId)->count(),
            ], 200);
        }

        return response()->json([
            'total_speeches' => Speech::count(),
            'total_speakers' => Speaker::count(),
        ], 200);
    }

    public function editOption(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $data = Option::find($id);

        if (!$data) {
            return response()->json(['message' => 'Option not found.'], 404);
        }

        $data->name = $request->input('name');
        $data->content = $request->input('content');

        $data->save();

        return response()->json([
            'message' => 'Option updated successfully.',
            'data' => $data
        ], 200);
    }
}
