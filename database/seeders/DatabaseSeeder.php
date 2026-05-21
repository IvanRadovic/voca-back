<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\Call;
use App\Models\Category;
use App\Models\Feedback;
use App\Models\Nvo;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
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
                'organization' => 'Tech Youth Hub',
                'calls' => [
                    [
                        'title' => 'Web Development Bootcamp',
                        'subtitle' => 'Learn React & Laravel in 6 weeks',
                        'type' => 'course',
                        'cats' => ['it', 'programming', 'design'],
                        'is_online' => true,
                        'location' => 'Online',
                        'price' => 0,
                    ],
                    [
                        'title' => 'Startup Weekend Podgorica',
                        'subtitle' => 'Build an MVP in 54 hours',
                        'type' => 'competition',
                        'cats' => ['entrepreneurship', 'business', 'it'],
                        'is_online' => false,
                        'location' => 'Podgorica, Montenegro',
                        'price' => 15,
                    ],
                ],
            ],
            [
                'name' => 'Green Future NGO',
                'email' => 'green@voca.test',
                'organization' => 'Green Future NGO',
                'calls' => [
                    [
                        'title' => 'Coastal Clean-up Camp',
                        'subtitle' => 'A week of volunteering by the sea',
                        'type' => 'camp',
                        'cats' => ['ecology', 'volunteering', 'travel'],
                        'is_online' => false,
                        'location' => 'Ulcinj, Montenegro',
                        'price' => 0,
                    ],
                    [
                        'title' => 'Sustainability Seminar',
                        'subtitle' => 'Climate action for young leaders',
                        'type' => 'seminar',
                        'cats' => ['ecology', 'personal-development'],
                        'is_online' => true,
                        'location' => 'Online',
                        'price' => 0,
                    ],
                ],
            ],
            [
                'name' => 'Creative Arts Collective',
                'email' => 'arts@voca.test',
                'organization' => 'Creative Arts Collective',
                'calls' => [
                    [
                        'title' => 'Photography Workshop',
                        'subtitle' => 'Master natural light portraits',
                        'type' => 'workshop',
                        'cats' => ['photography', 'art', 'design'],
                        'is_online' => false,
                        'location' => 'Kotor, Montenegro',
                        'price' => 25,
                    ],
                    [
                        'title' => 'Creative Writing Mentorship',
                        'subtitle' => '3-month guided program',
                        'type' => 'mentorship',
                        'cats' => ['writing', 'art', 'personal-development'],
                        'is_online' => true,
                        'location' => 'Online',
                        'price' => 0,
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
                    'description' => 'We empower young people through hands-on opportunities.',
                    'intro_message' => 'Welcome to '.$blueprint['organization'].'! Explore our open calls below.',
                    'verified' => true,
                ]
            );

            foreach ($blueprint['calls'] as $i => $c) {
                $call = Call::updateOrCreate(
                    ['user_id' => $nvoUser->id, 'title' => $c['title']],
                    [
                        'subtitle' => $c['subtitle'],
                        'description' => "<p>{$c['subtitle']}.</p><p>This opportunity is open to motivated young people aged 15-30. Limited spots available, so apply early!</p><ul><li>Hands-on sessions</li><li>Certificate of participation</li><li>Networking with peers</li></ul>",
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

            Feedback::updateOrCreate(
                ['call_id' => $finishedCall->id, 'user_id' => $youth->id],
                ['rating' => rand(4, 5), 'comment' => 'Great experience, learned a lot and met amazing people!']
            );
        }
    }
}
