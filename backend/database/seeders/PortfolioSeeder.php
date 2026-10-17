<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class PortfolioSeeder extends Seeder
{
    public function run(): void
    {
        // Isi default About
        Setting::updateOrCreate(['key' => 'about_title'], ['value' => 'About']);
        Setting::updateOrCreate(['key' => 'about_bio'], [
            'value' => 'Creative Web Engineer with experience building interactive websites and desktop UI.'
        ]);

        // Isi default Featured Project
        Project::create([
            'title' => 'Featured Article: Video Transcoder',
            'description' => 'Eksperimen transcode video & cloud storage di browser secara dinamis menggunakan Cloudflare R2.',
            'link' => 'https://github.com',
            'is_featured' => true,
        ]);
    }
}