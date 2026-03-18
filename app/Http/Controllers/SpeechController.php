<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Speech;

class SpeechController extends Controller
{
    public function getSpeeches(Request $request)
    {
        $sortKey = $request->input('sort_key', 'id');
        $sortDirection = $request->input('sort_direction', 'asc');

        if (!in_array(strtolower($sortDirection), ['asc', 'desc'])) {
            return response()->json(['message' => 'Invalid sort direction. Use "asc" or "desc".'], 400);
        }

        $data = Speech::select('*')
            ->orderBy($sortKey, $sortDirection)
            ->get();


        return response()->json($data, 200);
    }

    public function addSpeech(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'date' => 'required|string|max:255',
            'kalaka_id' => 'required|string|max:255',
            'language' => 'required|string|max:255',
            'mobile' => 'required|string|max:255',
            'origin' => 'required|string|max:255',
            'recordfile' => 'required|file|mimes:wav,mp3,wma,3gp',
            'textgrid' => 'nullable|file',
            'textcontent' => 'nullable|string',
            'speakerno' => 'required|numeric',
            'public' => 'required|numeric',
        ]);

        $audioDir = public_path('audios');
        $textgridDir = public_path('textgrid');
        if (!file_exists($audioDir)) {
            mkdir($audioDir, 0755, true);
        }
        if (!file_exists($textgridDir)) {
            mkdir($textgridDir, 0755, true);
        }

        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $uid = '';
        for ($i = 0; $i < 5; $i++) {
            $uid .= $characters[rand(0, strlen($characters) - 1)];
        }

        $audioFile = $request->file('recordfile');
        $audioFilename = $uid . ' ' . $audioFile->getClientOriginalName();
        $audioFile->move($audioDir, $audioFilename);

        $textgridFilename = null;
        if ($request->hasFile('textgrid')) {
            $tgFile = $request->file('textgrid');
            $ext = pathinfo($tgFile->getClientOriginalName(), PATHINFO_EXTENSION);
            $textgridFilename = substr($audioFilename, 0, strrpos($audioFilename, '.')) . '.' . $ext;
            $tgFile->move($textgridDir, $textgridFilename);
        }

        $data = new Speech();
        $data->name = $request->name;
        $data->category = $request->category;
        $data->date = $request->date;
        $data->kalaka_id = $request->kalaka_id;
        $data->language = $request->language;
        $data->mobile = $request->mobile;
        $data->origin = $request->origin;
        $data->recordfile = $audioFilename;
        $data->textgrid = $textgridFilename;
        $data->textcontent = $request->textcontent;
        $data->speakerno = $request->speakerno;
        $data->public = $request->public;
        $data->save();

        return response()->json([
            'message' => 'Speech created successfully!',
            'data' => $data,
        ], 201);
    }

    public function getSpeech($id)
    {
        $data = Speech::find($id);

        if (!$data) {
            return response()->json(['message' => 'Speech not found.'], 404);
        }


        return response()->json([
            'message' => 'Speech retrieved successfully.',
            'data' => $data
        ], 200);
    }

    public function editSpeech(Request $request, $id)
    {

        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'date' => 'required|string|max:255',
            'kalaka_id' => 'required|string|max:255',
            'language' => 'required|string|max:255',
            'mobile' => 'required|string|max:255',
            'origin' => 'required|string|max:255',
            'recordfile' => 'required|string|max:255',
            'textgrid' => 'nullable|string|max:255',
            'rttm' => 'nullable|string|max:255',
            'textcontent' => 'nullable|string',
            'speakerno' => 'required|numeric',
            'public' => 'required|numeric',
        ]);

        $data = Speech::find($id);

        if (!$data) {
            return response()->json(['message' => 'Speech not found.'], 404);
        }


        $data->name = $request->name;
        $data->category = $request->category;
        $data->date = $request->date;
        $data->kalaka_id = $request->kalaka_id;
        $data->language = $request->language;
        $data->mobile = $request->mobile;
        $data->origin = $request->origin;
        $data->recordfile = $request->recordfile;
        $data->textgrid = $request->textgrid;
        $data->rttm = $request->rttm;
        $data->textcontent = $request->textcontent;
        $data->speakerno = $request->speakerno;
        $data->public = $request->public;


        $data->save();


        return response()->json([
            'message' => 'Speech updated successfully.',
            'data' => $data
        ], 200);
    }

    public function removeSpeech($id)
    {
        $data = Speech::find($id);

        if (!$data) {
            return response()->json(['message' => 'Speech not found.'], 404);
        }

        $audioDir = public_path('audios');
        $textgridDir = public_path('textgrid');

        if ($data->recordfile) {
            $audioPath = $audioDir . DIRECTORY_SEPARATOR . $data->recordfile;
            if (file_exists($audioPath)) {
                unlink($audioPath);
            }
        }

        if ($data->textgrid) {
            $textgridPath = $textgridDir . DIRECTORY_SEPARATOR . $data->textgrid;
            if (file_exists($textgridPath)) {
                unlink($textgridPath);
            }
        }

        $data->delete();

        return response()->json(['message' => 'Speech removed successfully.'], 200);
    }


    public function stream($filename)
    {
        $path = public_path("audios/{$filename}");

        if (!file_exists($path)) {
            abort(404);
        }

        $fileContent = file_get_contents($path);
        $mimeType = mime_content_type($path);

        return response($fileContent, 200)
            ->header('Content-Type', $mimeType);
    }
}
