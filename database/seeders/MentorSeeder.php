<?php

namespace Database\Seeders;

use App\Models\Mentor;
use Illuminate\Database\Seeder;

class MentorSeeder extends Seeder
{
    /**
     * Seeds the mentor directory. Idempotent (keyed by name).
     */
    public function run(): void
    {
        $mentors = [
            ['name' => 'Milena Đukanović', 'title' => 'Software Engineer', 'expertise' => 'IT, Programming, Career',
                'bio' => 'Senior engineer with 8+ years of experience. Happy to guide students entering tech and preparing for their first job.'],
            ['name' => 'Nikola Vujović', 'title' => 'Startup Founder', 'expertise' => 'Entrepreneurship, Business, Product',
                'bio' => 'Built two startups from scratch. Mentors young founders on validating ideas and building an MVP.'],
            ['name' => 'Sara Popović', 'title' => 'Marketing Lead', 'expertise' => 'Marketing, Design, Branding',
                'bio' => 'Helps young people break into marketing, build a personal brand and grow on social media.'],
            ['name' => 'Ivan Marković', 'title' => 'NGO Program Manager', 'expertise' => 'Volunteering, Ecology, Projects',
                'bio' => 'Guides volunteers and project leaders across the region; experienced with EU youth programs.'],
            ['name' => 'Ana Knežević', 'title' => 'UX Designer', 'expertise' => 'Design, Product, Photography',
                'bio' => 'Designer at a product company. Mentors students building their first portfolio and case studies.'],
            ['name' => 'Marko Lazović', 'title' => 'Data Analyst', 'expertise' => 'IT, Business, Languages',
                'bio' => 'Turns data into decisions. Helps youth get started with analytics, spreadsheets and SQL.'],
        ];

        foreach ($mentors as $m) {
            Mentor::updateOrCreate(
                ['name' => $m['name']],
                [
                    'title' => $m['title'],
                    'expertise' => $m['expertise'],
                    'bio' => $m['bio'],
                    'linkedin' => 'https://www.linkedin.com/in/example',
                    'is_active' => true,
                ]
            );
        }
    }
}
