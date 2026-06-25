<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\Call;
use App\Models\Category;
use App\Models\Certificate;
use App\Models\Story;
use App\Models\Feedback;
use App\Models\Mentor;
use App\Models\Nvo;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Hand-picked Unsplash stock photos (free, no attribution required).
        $img = fn (string $id) => "https://images.unsplash.com/photo-{$id}?auto=format&fit=crop&w=1200&h=675&q=70";

        $this->call(CategorySeeder::class);

        $categories = Category::all()->keyBy('slug');

        // ---- Admin ----
        User::updateOrCreate(
            ['email' => 'admin@voca.test'],
            [
                'name' => 'Platform Admin',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
                'email_verified_at' => now(),
            ]
        );

        // ---- NVOs + their calls ----
        $nvoBlueprints = [
            [
                'name' => 'Tech Youth Hub',
                'email' => 'nvo@voca.test',
                'organization' => 'Centar za mlade i tehnologiju',
                'calls' => [
                    [
                        'title' => 'Bootcamp za web development',
                        'subtitle' => 'Nauči React i Laravel za 6 nedjelja',
                        'type' => 'course',
                        'cats' => ['it', 'programming', 'design'],
                        'is_online' => true,
                        'location' => 'Online',
                        'price' => 0,
                        'image' => $img('1461749280684-dccba630e2f6'),
                    ],
                    [
                        'title' => 'Startup vikend Podgorica',
                        'subtitle' => 'Napravi MVP za 54 sata',
                        'type' => 'competition',
                        'cats' => ['entrepreneurship', 'business', 'it'],
                        'is_online' => false,
                        'location' => 'Podgorica, Crna Gora',
                        'price' => 15,
                        'image' => $img('1522071820081-009f0129c71c'),
                    ],
                ],
            ],
            [
                'name' => 'Zelena Budućnost',
                'email' => 'green@voca.test',
                'organization' => 'Zelena Budućnost',
                'calls' => [
                    [
                        'title' => 'Kamp za čišćenje primorja',
                        'subtitle' => 'Nedjelja volontiranja na obali',
                        'type' => 'camp',
                        'cats' => ['ecology', 'volunteering', 'travel'],
                        'is_online' => false,
                        'location' => 'Ulcinj, Crna Gora',
                        'price' => 0,
                        'image' => $img('1618477388954-7852f32655ec'),
                    ],
                    [
                        'title' => 'Seminar o održivosti',
                        'subtitle' => 'Klimatska akcija za mlade lidere',
                        'type' => 'seminar',
                        'cats' => ['ecology', 'personal-development'],
                        'is_online' => true,
                        'location' => 'Online',
                        'price' => 0,
                        'image' => $img('1441974231531-c6227db76b6e'),
                    ],
                ],
            ],
            [
                'name' => 'Kolektiv kreativnih umjetnosti',
                'email' => 'arts@voca.test',
                'organization' => 'Kolektiv kreativnih umjetnosti',
                'calls' => [
                    [
                        'title' => 'Radionica fotografije',
                        'subtitle' => 'Ovladaj portretima u prirodnom svjetlu',
                        'type' => 'workshop',
                        'cats' => ['photography', 'art', 'design'],
                        'is_online' => false,
                        'location' => 'Kotor, Crna Gora',
                        'price' => 25,
                        'image' => $img('1452587925148-ce544e77e70d'),
                    ],
                    [
                        'title' => 'Mentorstvo kreativnog pisanja',
                        'subtitle' => 'Tromjesečni vođeni program',
                        'type' => 'mentorship',
                        'cats' => ['writing', 'art', 'personal-development'],
                        'is_online' => true,
                        'location' => 'Online',
                        'price' => 0,
                        'image' => $img('1455390582262-044cdead277a'),
                    ],
                ],
            ],
        ];

        $allCalls = collect();

        foreach ($nvoBlueprints as $blueprint) {
            $nvoUser = User::updateOrCreate(
                ['email' => $blueprint['email']],
                [
                    'name' => $blueprint['name'],
                    'password' => Hash::make('password'),
                    'role' => User::ROLE_NVO,
                    'email_verified_at' => now(),
                ]
            );

            Nvo::updateOrCreate(
                ['user_id' => $nvoUser->id],
                [
                    'organization_name' => $blueprint['organization'],
                    'website' => 'https://example.org',
                    'description' => 'Osnažujemo mlade kroz praktične prilike.',
                    'intro_message' => 'Dobrodošli u '.$blueprint['organization'].'! Pogledaj naše otvorene pozive ispod.',
                    'verified' => true,
                ]
            );

            foreach ($blueprint['calls'] as $i => $c) {
                $call = Call::updateOrCreate(
                    ['user_id' => $nvoUser->id, 'title' => $c['title']],
                    [
                        'subtitle' => $c['subtitle'],
                        'description' => "<p>{$c['subtitle']}.</p><p>Ova prilika je otvorena za motivisane mlade od 15 do 30 godina. Broj mjesta je ograničen, prijavi se rano!</p><ul><li>Praktične sesije</li><li>Sertifikat o učešću</li><li>Umrežavanje sa vršnjacima</li></ul>",
                        'image' => $c['image'] ?? null,
                        'type' => $c['type'],
                        'application_deadline' => Carbon::now()->addDays(10 + $i * 5),
                        'start_date' => Carbon::now()->addDays(20 + $i * 5),
                        'end_date' => Carbon::now()->addDays(22 + $i * 5),
                        'location' => $c['location'],
                        'is_online' => $c['is_online'],
                        'max_participants' => 30,
                        'price' => $c['price'],
                        'prerequisites' => $c['price'] > 0 ? ['english'] : ['none'],
                        'status' => Call::STATUS_ACTIVE,
                        'views' => rand(20, 400),
                    ]
                );

                $call->categories()->sync(
                    collect($c['cats'])->map(fn ($slug) => $categories[$slug]->id ?? null)->filter()->all()
                );

                $allCalls->push($call);
            }
        }

        // A finished call to demonstrate feedback.
        $finishedCall = $allCalls->first();
        $finishedCall->update([
            'status' => Call::STATUS_FINISHED,
            'application_deadline' => Carbon::now()->subDays(20),
            'start_date' => Carbon::now()->subDays(15),
            'end_date' => Carbon::now()->subDays(13),
        ]);

        // ---- Youth users ----
        $youthBlueprints = [
            ['name' => 'Ana Petrović', 'email' => 'ana@voca.test', 'city' => 'Podgorica', 'interests' => ['it', 'programming', 'design']],
            ['name' => 'Marko Nikolić', 'email' => 'marko@voca.test', 'city' => 'Nikšić', 'interests' => ['entrepreneurship', 'business', 'it']],
            ['name' => 'Jelena Vuković', 'email' => 'jelena@voca.test', 'city' => 'Kotor', 'interests' => ['photography', 'art', 'writing']],
            ['name' => 'Stefan Radović', 'email' => 'stefan@voca.test', 'city' => 'Bar', 'interests' => ['ecology', 'volunteering', 'travel']],
        ];

        $youths = collect();
        foreach ($youthBlueprints as $y) {
            $user = User::updateOrCreate(
                ['email' => $y['email']],
                [
                    'name' => $y['name'],
                    'password' => Hash::make('password'),
                    'role' => User::ROLE_YOUTH,
                    'city' => $y['city'],
                    'date_of_birth' => Carbon::now()->subYears(rand(16, 29)),
                    'education_level' => collect(['high_school', 'undergraduate', 'bachelor', 'master'])->random(),
                    'gender' => collect(['male', 'female', 'undisclosed'])->random(),
                    'headline' => 'Motivisana mlada osoba iz grada '.$y['city'],
                    'about' => 'Strastveno učim nove stvari i doprinosim svojoj zajednici. Uvijek u potrazi za prilikama za rast.',
                    'education' => 'Fakultet političkih nauka, Univerzitet Crne Gore (u toku).',
                    'work_experience' => 'Volonter u lokalnom omladinskom centru; part-time praksa u malom timu.',
                    'skills' => 'Timski rad, komunikacija, engleski (B2), osnovno upravljanje projektima.',
                    'linkedin' => 'https://www.linkedin.com/in/example',
                    'email_verified_at' => now(),
                ]
            );

            $user->interests()->sync(
                collect($y['interests'])->map(fn ($slug) => $categories[$slug]->id ?? null)->filter()->all()
            );

            $youths->push($user);
        }

        // ---- Applications ----
        foreach ($youths as $youth) {
            // Apply to a couple of random active calls.
            $allCalls->where('status', Call::STATUS_ACTIVE)->random(min(2, $allCalls->count()))
                ->each(function (Call $call) use ($youth) {
                    Application::updateOrCreate(
                        ['call_id' => $call->id, 'user_id' => $youth->id],
                        ['status' => collect([
                            Application::STATUS_PENDING,
                            Application::STATUS_ACCEPTED,
                        ])->random()]
                    );
                });

            // Completed application + feedback on the finished call.
            Application::updateOrCreate(
                ['call_id' => $finishedCall->id, 'user_id' => $youth->id],
                ['status' => Application::STATUS_COMPLETED]
            );

            Certificate::issueFor($youth->id, $finishedCall->id);

            Story::updateOrCreate(
                ['user_id' => $youth->id, 'call_id' => $finishedCall->id],
                ['body' => 'Nezaboravno iskustvo! Naučio/la sam puno, upoznao/la sjajne ljude i stekao/la nove vještine. Preporučujem svakom mladom čovjeku.']
            );

            Feedback::updateOrCreate(
                ['call_id' => $finishedCall->id, 'user_id' => $youth->id],
                ['rating' => rand(4, 5), 'comment' => 'Odlično iskustvo, naučio sam puno i upoznao divne ljude!']
            );
        }

        // ---- Editorial content + mentors ----
        $this->call([
            ContentSeeder::class,
            MentorSeeder::class,
        ]);
    }
}
