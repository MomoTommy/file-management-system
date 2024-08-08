<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Exception;

class FileController extends Controller
{
    public function index()
    {
        return File::all();
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|max:10240', // 10MB max
            ]);

            $path = $request->file('file')->store('files', 'public');

            $fileRecord = File::create([
                'name' => $request->file('file')->getClientOriginalName(),
                'path' => $path,
                'type' => $request->file('file')->getMimeType(),
                'size' => $request->file('file')->getSize(),
            ]);

            return response()->json($fileRecord, 201);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show(File $file)
    {
        return $file;
    }

    public function update(Request $request, File $file)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $file->update([
            'name' => $request->name,
        ]);

        return response()->json($file);
    }

    public function destroy(File $file)
    {
        Storage::delete($file->path);
        $file->delete();

        return response()->json(null, 204);
    }
}
