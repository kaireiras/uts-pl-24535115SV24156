<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    // GET: Ambil semua list project
    public function index()
    {
        $projects = Project::latest()->get();
        return response()->json([
            'status' => 'success',
            'data' => $projects
        ]);
    }

    // POST: Tambah project baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'link' => 'nullable|url',
            'is_featured' => 'nullable|boolean'
        ]);

        $project = Project::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Project berhasil ditambahkan',
            'data' => $project
        ], 201);
    }

    // GET: Ambil detail 1 project
    public function show($id)
    {
        $project = Project::find($id);

        if (!$project) {
            return response()->json(['message' => 'Project tidak ditemukan'], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $project
        ]);
    }

    // PUT/PATCH: Update data project
    public function update(Request $request, $id)
    {
        $project = Project::find($id);

        if (!$project) {
            return response()->json(['message' => 'Project tidak ditemukan'], 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'link' => 'nullable|url',
            'is_featured' => 'nullable|boolean'
        ]);

        $project->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Project berhasil diperbarui',
            'data' => $project
        ]);
    }

    // DELETE: Hapus project
    public function destroy($id)
    {
        $project = Project::find($id);

        if (!$project) {
            return response()->json(['message' => 'Project tidak ditemukan'], 404);
        }

        $project->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Project berhasil dihapus'
        ]);
    }
}