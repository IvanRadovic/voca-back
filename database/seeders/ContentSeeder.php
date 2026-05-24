<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ContentSeeder extends Seeder
{
    /**
     * Resources + Blog posts in Montenegrin (default) with English translation.
     * Idempotent: slug is derived from the English title (stable, ASCII).
     */
    public function run(): void
    {
        $author = User::where('role', User::ROLE_ADMIN)->first()
            ?? User::where('role', User::ROLE_NVO)->first()
            ?? User::first();

        if (! $author) {
            $this->command?->warn('ContentSeeder: no users found, skipping posts.');

            return;
        }

        $posts = [
            // ---------- Resources ----------
            [
                'type' => 'resource',
                'title' => 'Kako napisati odličan CV',
                'title_en' => 'How to write a great CV',
                'excerpt' => 'Jednostavan šablon i savjeti za tvoj prvi CV.',
                'excerpt_en' => 'A simple template and tips to build your first CV.',
                'body' => "## Tvoj prvi CV\n\nDobar CV je **jasan i sažet**. Uključi:\n\n- Kontakt podatke\n- Kratak opis o sebi\n- Obrazovanje\n- Iskustvo i volontiranje\n- Vještine i jezike\n\n> Savjet: prilagodi CV svakoj prilici i drži ga na jednoj strani.",
                'body_en' => "## Your first CV\n\nA good CV is **clear and concise**. Include:\n\n- Contact details\n- A short summary about you\n- Education\n- Experience & volunteering\n- Skills & languages\n\n> Tip: tailor your CV to each opportunity and keep it to one page.",
            ],
            [
                'type' => 'resource',
                'title' => 'Pisanje motivacionog pisma',
                'title_en' => 'Writing a motivation letter',
                'excerpt' => 'Struktuiraj pismo u tri kratka pasusa.',
                'excerpt_en' => 'Structure your letter in three short paragraphs.',
                'body' => "## Motivaciono pismo\n\n1. **Zašto baš ova prilika** — pokaži iskreno interesovanje.\n2. **Zašto ti** — tvoje relevantne vještine i iskustvo.\n3. **Šta donosiš** — tvoja motivacija i ciljevi.\n\nNeka bude iskreno, konkretno i kraće od jedne strane.",
                'body_en' => "## Motivation letter\n\n1. **Why this opportunity** — show genuine interest.\n2. **Why you** — your relevant skills and experience.\n3. **What you bring** — your motivation and goals.\n\nKeep it honest, specific and under one page.",
            ],
            [
                'type' => 'resource',
                'title' => 'Prijava za Erasmus+',
                'title_en' => 'Applying for Erasmus+',
                'excerpt' => 'Sve što ti treba da započneš Erasmus+ putovanje.',
                'excerpt_en' => 'Everything you need to start your Erasmus+ journey.',
                'body' => "## Erasmus+\n\nErasmus+ finansira omladinske razmjene, obuke i volontiranje širom Evrope.\n\n- Pronađi **akreditovanu organizaciju**\n- Pripremi CV i motivaciono pismo\n- Pazi na rokove — popularni projekti se brzo popune\n- Većina troškova (put, smještaj) je pokrivena\n\nJedan od najboljih načina da putuješ, učiš i upoznaš vršnjake iz cijele Evrope.",
                'body_en' => "## Erasmus+\n\nErasmus+ funds youth exchanges, training and volunteering across Europe.\n\n- Find an **accredited organization**\n- Prepare your CV and motivation letter\n- Watch the deadlines — popular projects fill up fast\n- Most costs (travel, stay) are covered\n\nOne of the best ways to travel, learn and meet peers from across Europe.",
            ],
            [
                'type' => 'resource',
                'title' => 'Kako se pripremiti za intervju',
                'title_en' => 'How to prepare for an interview',
                'excerpt' => 'Jednostavni koraci da budeš siguran i spreman.',
                'excerpt_en' => 'Simple steps to feel confident and prepared.',
                'body' => "## Priprema za intervju\n\n- Istraži organizaciju\n- Pripremi odgovore na česta pitanja\n- Imaj 2–3 svoja pitanja\n- Vježbaj naglas\n- Dođi (ili se uključi) par minuta ranije\n\n**Zapamti:** intervju je razgovor u oba smjera.",
                'body_en' => "## Interview prep\n\n- Research the organization\n- Prepare answers to common questions\n- Have 2–3 questions ready to ask\n- Practice out loud\n- Arrive (or log in) a few minutes early\n\n**Remember:** an interview is a two-way conversation.",
            ],
            [
                'type' => 'resource',
                'title' => 'Pronalaženje stipendija',
                'title_en' => 'Finding scholarships',
                'excerpt' => 'Gdje i kako tražiti stipendije i grantove.',
                'excerpt_en' => 'Where and how to look for scholarships and grants.',
                'body' => "## Stipendije 101\n\nTraži stipendije kroz:\n\n- Univerzitete i fakultete\n- Državne i ambasadne programe\n- Međunarodne fondacije\n- NVO i kompanije\n\nKreni rano, pripremi dokumente unaprijed i prijavi se na više njih odjednom.",
                'body_en' => "## Scholarships 101\n\nLook for scholarships through:\n\n- Universities and faculties\n- Government and embassy programs\n- International foundations\n- NGOs and companies\n\nStart early, prepare documents in advance, and apply to several at once.",
            ],
            [
                'type' => 'resource',
                'title' => 'Izrada LinkedIn profila',
                'title_en' => 'Building your LinkedIn profile',
                'excerpt' => 'Ostavi snažan prvi utisak online.',
                'excerpt_en' => 'Make a strong first impression online.',
                'body' => "## LinkedIn osnove\n\n- Koristi prijatnu, jasnu fotografiju\n- Napiši naslov koji govori ko si\n- Dodaj obrazovanje, iskustvo i vještine\n- Dijeli ono što učiš i u čemu učestvuješ\n\nDobar profil otvara vrata prilikama i mentorima.",
                'body_en' => "## LinkedIn basics\n\n- Use a friendly, clear photo\n- Write a headline that says who you are\n- Add education, experience and skills\n- Share what you learn and take part in\n\nA good profile opens doors to opportunities and mentors.",
            ],

            // ---------- Blog ----------
            [
                'type' => 'blog',
                'title' => 'Top 5 ljetnjih kampova za mlade u 2026.',
                'title_en' => 'Top 5 summer camps for youth in 2026',
                'excerpt' => 'Naš izbor za nezaboravno i korisno ljeto.',
                'excerpt_en' => 'Our picks for an unforgettable, useful summer.',
                'body' => "## Iskoristi ljeto najbolje\n\nOd programerskih kampova do čišćenja primorja — evo pet iskustava vrijednih tvog vremena ovog ljeta. Svako gradi vještine **i** prijateljstva.\n\n1. Web development kamp\n2. Kamp čišćenja primorja\n3. Radionica fotografije\n4. Startup vikend\n5. Razmjena jezika",
                'body_en' => "## Make the most of summer\n\nFrom coding camps to coastal clean-ups, here are five experiences worth your time this summer. Each one builds skills **and** friendships.\n\n1. Web development bootcamp\n2. Coastal clean-up camp\n3. Photography workshop\n4. Startup weekend\n5. Language exchange",
            ],
            [
                'type' => 'blog',
                'title' => 'Kako mi je radionica promijenila put',
                'title_en' => 'How a workshop changed my path',
                'excerpt' => 'Učesnik dijeli kako mu je jedan vikend otvorio nova vrata.',
                'excerpt_en' => 'A participant shares how one weekend opened new doors.',
                'body' => "## Vikend koji je značio\n\n\"Prijavio sam se na startup vikend bez ikakvog iskustva i otišao sa timom i planom.\"\n\nUpravo ovi trenuci — upoznavanje pravih ljudi, isprobavanje nečeg novog — su razlog zašto Voca postoji.",
                'body_en' => "## A weekend that mattered\n\n\"I joined a startup weekend with zero experience and left with a team and a plan.\"\n\nThese moments — meeting the right people, trying something new — are exactly why Voca exists.",
            ],
            [
                'type' => 'blog',
                'title' => '5 vještina koje svaka mlada osoba treba da razvije',
                'title_en' => '5 skills every young person should build',
                'excerpt' => 'Vještine za budućnost koje možeš učiti već danas.',
                'excerpt_en' => 'Future-proof skills you can start learning today.',
                'body' => "## Vještine za budućnost\n\n- **Komunikacija** — jasno govorenje i pisanje\n- **Digitalna pismenost** — alati i osnove podataka\n- **Timski rad** — saradnja sa drugima\n- **Rješavanje problema** — razlaganje izazova\n- **Prilagodljivost** — neprekidno učenje\n\nSve ovo možeš graditi kroz prilike na Voci.",
                'body_en' => "## Skills for the future\n\n- **Communication** — speaking and writing clearly\n- **Digital literacy** — tools and basic data\n- **Teamwork** — collaborating with others\n- **Problem solving** — breaking down challenges\n- **Adaptability** — learning continuously\n\nYou can build all of these through the opportunities on Voca.",
            ],
            [
                'type' => 'blog',
                'title' => 'Zašto se isplati volontirati',
                'title_en' => 'Why volunteering is worth it',
                'excerpt' => 'Više od jedne stavke u CV-u.',
                'excerpt_en' => 'More than a line on your CV.',
                'body' => "## Volontiranje\n\nVolontiranje pomaže tvojoj zajednici **i** tebi: nove vještine, preporuke, prijatelji i pravi osjećaj svrhe. Počni jednom lokalnom akcijom i raste odatle.",
                'body_en' => "## Volunteering\n\nVolunteering helps your community **and** you: new skills, references, friends, and a real sense of purpose. Start with one local action and grow from there.",
            ],
            [
                'type' => 'blog',
                'title' => 'Od ideje do projekta: kako početi',
                'title_en' => 'From idea to project: getting started',
                'excerpt' => 'Pretvori svoju ideju u nešto stvarno.',
                'excerpt_en' => 'Turn your idea into something real.',
                'body' => "## Počni od malog\n\nSvaki veliki projekat počinje malim korakom.\n\n- Zapiši ideju u jednoj rečenici\n- Nađi jednu ili dvije osobe da ti se pridruže\n- Napravi najmanju moguću verziju\n- Traži povratne informacije i poboljšavaj\n\nPrijavi se za mikro-grant ili mentorstvo da je odvedeš dalje.",
                'body_en' => "## Start small\n\nEvery big project starts with a small step.\n\n- Write your idea in one sentence\n- Find one or two people to join you\n- Make the smallest possible version\n- Ask for feedback and improve\n\nApply for a micro-grant or mentorship to take it further.",
            ],
        ];

        foreach ($posts as $i => $p) {
            Post::updateOrCreate(
                ['slug' => Str::slug($p['title_en'])],
                [
                    'author_id' => $author->id,
                    'type' => $p['type'],
                    'title' => $p['title'],
                    'title_en' => $p['title_en'],
                    'excerpt' => $p['excerpt'],
                    'excerpt_en' => $p['excerpt_en'],
                    'body' => $p['body'],
                    'body_en' => $p['body_en'],
                    'published_at' => now()->subDays($i),
                ]
            );
        }
    }
}
