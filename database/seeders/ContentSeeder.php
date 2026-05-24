<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ContentSeeder extends Seeder
{
    /**
     * Seeds Resources + Blog posts. Idempotent (keyed by slug).
     */
    public function run(): void
    {
        // Pick an author: prefer admin, then any NVO, then any user.
        $author = User::where('role', User::ROLE_ADMIN)->first()
            ?? User::where('role', User::ROLE_NVO)->first()
            ?? User::first();

        if (! $author) {
            $this->command?->warn('ContentSeeder: no users found, skipping posts.');

            return;
        }

        $posts = [
            // ---- Resources ----
            ['type' => 'resource', 'title' => 'How to write a great CV', 'excerpt' => 'A simple template and tips to build your first CV.',
                'body' => "## Your first CV\n\nA good CV is **clear and concise**. Include:\n\n- Contact details\n- A short summary about you\n- Education\n- Experience & volunteering\n- Skills & languages\n\n> Tip: tailor your CV to each opportunity and keep it to one page."],
            ['type' => 'resource', 'title' => 'Writing a motivation letter', 'excerpt' => 'Structure your letter in three short paragraphs.',
                'body' => "## Motivation letter\n\n1. **Why this opportunity** — show genuine interest.\n2. **Why you** — your relevant skills and experience.\n3. **What you bring** — your motivation and goals.\n\nKeep it honest, specific and under one page."],
            ['type' => 'resource', 'title' => 'Applying for Erasmus+', 'excerpt' => 'Everything you need to start your Erasmus+ journey.',
                'body' => "## Erasmus+\n\nErasmus+ funds youth exchanges, training and volunteering across Europe.\n\n- Find an **accredited organization**\n- Prepare your CV and motivation letter\n- Watch the deadlines — popular projects fill up fast\n- Most costs (travel, stay) are covered\n\nIt is one of the best ways to travel, learn and meet peers from across Europe."],
            ['type' => 'resource', 'title' => 'How to prepare for an interview', 'excerpt' => 'Simple steps to feel confident and prepared.',
                'body' => "## Interview prep\n\n- Research the organization\n- Prepare answers to common questions\n- Have 2–3 questions ready to ask\n- Practice out loud\n- Arrive (or log in) a few minutes early\n\n**Remember:** an interview is a two-way conversation."],
            ['type' => 'resource', 'title' => 'Finding scholarships', 'excerpt' => 'Where and how to look for scholarships and grants.',
                'body' => "## Scholarships 101\n\nLook for scholarships through:\n\n- Universities and faculties\n- Government and embassy programs\n- International foundations\n- NGOs and companies\n\nStart early, prepare documents in advance, and apply to several at once."],
            ['type' => 'resource', 'title' => 'Building your LinkedIn profile', 'excerpt' => 'Make a strong first impression online.',
                'body' => "## LinkedIn basics\n\n- Use a friendly, clear photo\n- Write a headline that says who you are\n- Add education, experience and skills\n- Share what you learn and take part in\n\nA good profile opens doors to opportunities and mentors."],

            // ---- Blog ----
            ['type' => 'blog', 'title' => 'Top 5 summer camps for youth in 2026', 'excerpt' => 'Our picks for an unforgettable, useful summer.',
                'body' => "## Make the most of summer\n\nFrom coding camps to coastal clean-ups, here are five experiences worth your time this summer. Each one builds skills **and** friendships.\n\n1. Web development bootcamp\n2. Coastal clean-up camp\n3. Photography workshop\n4. Startup weekend\n5. Language exchange"],
            ['type' => 'blog', 'title' => 'How a workshop changed my path', 'excerpt' => 'A participant shares how one weekend opened new doors.',
                'body' => "## A weekend that mattered\n\n\"I joined a startup weekend with zero experience and left with a team and a plan.\"\n\nThese moments — meeting the right people, trying something new — are exactly why Voca exists."],
            ['type' => 'blog', 'title' => '5 skills every young person should build', 'excerpt' => 'Future-proof skills you can start learning today.',
                'body' => "## Skills for the future\n\n- **Communication** — speaking and writing clearly\n- **Digital literacy** — tools and basic data\n- **Teamwork** — collaborating with others\n- **Problem solving** — breaking down challenges\n- **Adaptability** — learning continuously\n\nYou can build all of these through the opportunities on Voca."],
            ['type' => 'blog', 'title' => 'Why volunteering is worth it', 'excerpt' => 'More than a line on your CV.',
                'body' => "## Volunteering\n\nVolunteering helps your community **and** you: new skills, references, friends, and a real sense of purpose. Start with one local action and grow from there."],
            ['type' => 'blog', 'title' => 'From idea to project: getting started', 'excerpt' => 'Turn your idea into something real.',
                'body' => "## Start small\n\nEvery big project starts with a small step.\n\n- Write your idea in one sentence\n- Find one or two people to join you\n- Make the smallest possible version\n- Ask for feedback and improve\n\nApply for a micro-grant or mentorship to take it further."],
        ];

        foreach ($posts as $i => $p) {
            Post::updateOrCreate(
                ['slug' => Str::slug($p['title'])],
                [
                    'author_id' => $author->id,
                    'type' => $p['type'],
                    'title' => $p['title'],
                    'excerpt' => $p['excerpt'],
                    'body' => $p['body'],
                    'published_at' => now()->subDays($i),
                ]
            );
        }
    }
}
