<?php

namespace Database\Seeders;

use App\Models\Mentor;
use Illuminate\Database\Seeder;

class MentorSeeder extends Seeder
{
    /**
     * Seeds the mentor directory in Montenegrin (default) with English translation.
     * Idempotent (keyed by name).
     */
    public function run(): void
    {
        $mentors = [
            [
                'name' => 'Milena Đukanović',
                'title' => 'Softverska inženjerka',
                'title_en' => 'Software Engineer',
                'expertise' => 'IT, Programiranje, Karijera',
                'bio' => 'Senior inženjerka sa preko 8 godina iskustva. Rado vodi mlade koji ulaze u svijet tehnologije i pripremaju se za prvi posao.',
                'bio_en' => 'Senior engineer with 8+ years of experience. Happy to guide students entering tech and preparing for their first job.',
            ],
            [
                'name' => 'Nikola Vujović',
                'title' => 'Osnivač startapa',
                'title_en' => 'Startup Founder',
                'expertise' => 'Preduzetništvo, Biznis, Proizvod',
                'bio' => 'Pokrenuo dva startapa od nule. Mentoriše mlade osnivače u provjeri ideja i izradi MVP-a.',
                'bio_en' => 'Built two startups from scratch. Mentors young founders on validating ideas and building an MVP.',
            ],
            [
                'name' => 'Sara Popović',
                'title' => 'Marketing menadžerka',
                'title_en' => 'Marketing Lead',
                'expertise' => 'Marketing, Dizajn, Brendiranje',
                'bio' => 'Pomaže mladima da uđu u marketing, izgrade lični brend i rastu na društvenim mrežama.',
                'bio_en' => 'Helps young people break into marketing, build a personal brand and grow on social media.',
            ],
            [
                'name' => 'Ivan Marković',
                'title' => 'Programski menadžer u NVO',
                'title_en' => 'NGO Program Manager',
                'expertise' => 'Volontiranje, Ekologija, Projekti',
                'bio' => 'Vodi volontere i nosioce projekata širom regiona; iskusan u EU omladinskim programima.',
                'bio_en' => 'Guides volunteers and project leaders across the region; experienced with EU youth programs.',
            ],
            [
                'name' => 'Ana Knežević',
                'title' => 'UX dizajnerka',
                'title_en' => 'UX Designer',
                'expertise' => 'Dizajn, Proizvod, Fotografija',
                'bio' => 'Dizajnerka u produkt kompaniji. Mentoriše mlade u izradi prvog portfolija i studija slučaja.',
                'bio_en' => 'Designer at a product company. Mentors students building their first portfolio and case studies.',
            ],
            [
                'name' => 'Marko Lazović',
                'title' => 'Analitičar podataka',
                'title_en' => 'Data Analyst',
                'expertise' => 'IT, Biznis, Jezici',
                'bio' => 'Pretvara podatke u odluke. Pomaže mladima da krenu sa analitikom, tabelama i SQL-om.',
                'bio_en' => 'Turns data into decisions. Helps youth get started with analytics, spreadsheets and SQL.',
            ],
        ];

        foreach ($mentors as $m) {
            Mentor::updateOrCreate(
                ['name' => $m['name']],
                [
                    'title' => $m['title'],
                    'title_en' => $m['title_en'],
                    'expertise' => $m['expertise'],
                    'bio' => $m['bio'],
                    'bio_en' => $m['bio_en'],
                    'linkedin' => 'https://www.linkedin.com/in/example',
                    'is_active' => true,
                ]
            );
        }
    }
}
