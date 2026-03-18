<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Location;

class LocationController extends Controller
{
    public function getLocations(Request $request)
    {
        $sortKey = $request->input('sort_key', 'id');
        $sortDirection = $request->input('sort_direction', 'asc');

        if (!in_array(strtolower($sortDirection), ['asc', 'desc'])) {
            return response()->json(['message' => 'Invalid sort direction. Use "asc" or "desc".'], 400);
        }

        $data = Location::select('*')
            ->orderBy($sortKey, $sortDirection)
            ->get();


        return response()->json($data, 200);
    }

    public function addLocation(Request $request)
    {

        $request->validate([
            'name' => 'required|string|max:255',
            'latitude' => 'numeric',
            'longitude' => 'numeric',
        ]);

        $data = new Location();
        $data->name = $request->name;
        $data->latitude = $request->latitude;
        $data->longitude = $request->longitude;
        $data->save();

        return response()->json([
            'message' => 'Location created successfully!',
            'data' => $data,
        ], 201);
    }

    public function getLocation($id)
    {
        $data = Location::find($id);

        if (!$data) {
            return response()->json(['message' => 'Location not found.'], 404);
        }


        return response()->json([
            'message' => 'Location retrieved successfully.',
            'data' => $data
        ], 200);
    }

    public function editLocation(Request $request, $id)
    {

        $request->validate([
            'name' => 'required|string|max:255',
            'latitude' => 'numeric',
            'longitude' => 'numeric',
        ]);

        $data = Location::find($id);

        if (!$data) {
            return response()->json(['message' => 'Location not found.'], 404);
        }


        $data->name = $request->name;
        $data->latitude = $request->latitude;
        $data->longitude = $request->longitude;


        $data->save();


        return response()->json([
            'message' => 'Location updated successfully.',
            'data' => $data
        ], 200);
    }

    public function removeLocation($id)
    {
        $data = Location::find($id);

        if (!$data) {
            return response()->json(['message' => 'Location not found.'], 404);
        }


        $data->delete();


        return response()->json(['message' => 'Location removed successfully.'], 200);
    }
}
