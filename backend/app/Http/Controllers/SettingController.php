<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function getPortfolioData()
    {
        $aboutTitle = Setting::where('key', 'about_title')->value('value') ?? 'About Me';
        $aboutBio = Setting::where('key', 'about_bio')->value('value') ?? 'Creative Web Engineer.';
        
        // Ambil URL foto profil (me.jpg)
        $mePhotoPath = Setting::where('key', 'me_photo')->value('value');
        $mePhotoUrl = $mePhotoPath ? url(Storage::url($mePhotoPath)) : null;

        $featuredProject = Project::where('is_featured', true)->latest()->first() 
                            ?? Project::latest()->first();

        return response()->json([
            'about' => [
                'title' => $aboutTitle,
                'bio' => $aboutBio,
            ],
            'me_photo' => $mePhotoUrl,
            'featured_project' => $featuredProject,
            'all_projects' => Project::latest()->get()
        ]);
    }

    public function uploadPhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,webp|max:3072'
        ]);

        // Hapus foto lama jika ada
        $oldPath = Setting::where('key', 'me_photo')->value('value');
        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        // Simpan foto baru ke public/uploads
        $path = $request->file('photo')->store('uploads', 'public');

        Setting::updateOrCreate(
            ['key' => 'me_photo'],
            ['value' => $path]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Foto me.jpg berhasil diperbarui',
            'photo_url' => url(Storage::url($path))
        ]);
    }
}