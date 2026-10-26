<?php

namespace App\Http\Controllers;

use App\Models\Duration;
use Illuminate\Http\Request;

class DurationController extends Controller
{
    public function getDurations(){
        try {
            $durations = Duration::all();
            return $this->genericResponse(true, 'Durations fetched successfully', 200, $durations, 'getDurations', []);
        } catch (\Throwable $th) {
            return $this->genericResponse(false, $th->getMessage(), 500, $th, "getDurations", []);
        }
    }

    public function createDuration(Request $request){
        try {
            $duration = Duration::create([
                'name' => $request->name,
                'value' => $request->value,
                'unit' => $request->unit,
                'description' => $request->description
            ]);
            return $this->genericResponse(true, 'Duration created successfully', 201, $duration, 'createDuration', $request);
        } catch (\Throwable $th) {
            return $this->genericResponse(false, $th->getMessage(), 500, $th, "createDuration", $request);
        }
    }

    public function updateDuration(Request $request, $id){
        try {
            $duration = Duration::find($id);
            if (!$duration) {
                return $this->genericResponse(false, 'Duration not found', 404, [], 'updateDuration', $request);
            }
            $duration->update([
                'name' => $request->name,
                'value' => $request->value,
                'unit' => $request->unit,
                'description' => $request->description,
                'status' => $request->status
            ]);
            return $this->genericResponse(true, 'Duration updated successfully', 200, $duration, 'updateDuration', $request);
        } catch (\Throwable $th) {
            return $this->genericResponse(false, $th->getMessage(), 500, $th, "updateDuration", $request);
        }
    }
}
