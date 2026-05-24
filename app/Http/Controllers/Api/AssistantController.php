<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Call;
use App\Services\AiAssistant;
use Illuminate\Http\Request;

class AssistantController extends Controller
{
    public function __construct(private AiAssistant $ai)
    {
    }

    /**
     * Generate a motivation / cover letter for a specific call.
     */
    public function coverLetter(Request $request)
    {
        $data = $request->validate([
            'call_id' => ['required', 'exists:calls,id'],
            'lang' => ['nullable', 'in:en,cnr'],
        ]);

        $user = $request->user();
        $call = Call::with('nvo.nvo')->findOrFail($data['call_id']);
        $lang = $data['lang'] ?? 'en';
        $org = optional($call->nvo->nvo)->organization_name ?? optional($call->nvo)->name;

        $system = 'You write concise, sincere motivation letters for young people applying to opportunities. '
            .'Keep it to 3 short paragraphs. Respond in '.($lang === 'cnr' ? 'Montenegrin' : 'English').'.';
        $prompt = "Applicant: {$user->name}.\n"
            ."Headline: {$user->headline}\nAbout: {$user->about}\nSkills: {$user->skills}\n"
            ."Education: {$user->education}\nExperience: {$user->work_experience}\n\n"
            ."Opportunity: \"{$call->title}\" by {$org}. Type: {$call->type}.\n"
            .'Write a motivation letter for this application.';

        $text = $this->ai->complete($system, [['role' => 'user', 'content' => $prompt]]);

        if (! $text) {
            $text = $this->coverLetterTemplate($user, $call, $org, $lang);
        }

        return response()->json(['text' => $text, 'source' => $this->ai->enabled() ? 'ai' : 'template']);
    }

    /**
     * Generate a CV from the user's profile.
     */
    public function cv(Request $request)
    {
        $data = $request->validate(['lang' => ['nullable', 'in:en,cnr']]);
        $user = $request->user()->load('interests');
        $lang = $data['lang'] ?? 'en';

        $system = 'You turn a profile into a clean, well-structured CV in plain text with clear section headers. '
            .'Respond in '.($lang === 'cnr' ? 'Montenegrin' : 'English').'.';
        $prompt = "Name: {$user->name}\nHeadline: {$user->headline}\nCity: {$user->city}\n"
            ."About: {$user->about}\nEducation: {$user->education}\nExperience: {$user->work_experience}\n"
            ."Skills: {$user->skills}\nInterests: ".$user->interests->pluck('name')->implode(', ')."\n"
            ."Email: {$user->email}\n\nProduce a CV.";

        $text = $this->ai->complete($system, [['role' => 'user', 'content' => $prompt]], 1000);

        if (! $text) {
            $text = $this->cvTemplate($user, $lang);
        }

        return response()->json(['text' => $text, 'source' => $this->ai->enabled() ? 'ai' : 'template']);
    }

    /**
     * Conversational helper. Falls back to a keyword search over open calls.
     */
    public function chat(Request $request)
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
            'lang' => ['nullable', 'in:en,cnr'],
        ]);
        $lang = $data['lang'] ?? 'en';

        if ($this->ai->enabled()) {
            $system = 'You are BIP TECH\'s friendly assistant helping young people (15-30) find opportunities '
                .'(seminars, workshops, camps, courses, volunteering). Be concise and encouraging. '
                .'Respond in '.($lang === 'cnr' ? 'Montenegrin' : 'English').'.';
            $text = $this->ai->complete($system, [['role' => 'user', 'content' => $data['message']]]);
            if ($text) {
                return response()->json(['text' => $text, 'source' => 'ai', 'matches' => []]);
            }
        }

        // Fallback: keyword search over active calls.
        $matches = $this->searchCalls($data['message']);
        $intro = $lang === 'cnr'
            ? ($matches->isEmpty()
                ? 'Nisam pronašao poklapanja, ali pogledaj sve pozive u sekciji Pozivi.'
                : 'Evo prilika koje bi ti mogle odgovarati:')
            : ($matches->isEmpty()
                ? "I couldn't find a match, but check all opportunities on the Browse page."
                : 'Here are opportunities that might fit:');

        return response()->json([
            'text' => $intro,
            'source' => 'search',
            'matches' => $matches->map(fn ($c) => ['id' => $c->id, 'title' => $c->title, 'type' => $c->type])->values(),
        ]);
    }

    private function searchCalls(string $message)
    {
        $words = collect(preg_split('/\s+/', mb_strtolower($message)))
            ->filter(fn ($w) => mb_strlen($w) >= 4)
            ->take(6);

        return Call::query()->active()
            ->where(function ($q) use ($words) {
                foreach ($words as $w) {
                    $q->orWhere('title', 'like', "%{$w}%")->orWhere('description', 'like', "%{$w}%");
                }
            })
            ->limit(5)
            ->get();
    }

    private function coverLetterTemplate($user, Call $call, ?string $org, string $lang): string
    {
        if ($lang === 'cnr') {
            return "Poštovani,\n\nSa velikim interesovanjem prijavljujem se za \"{$call->title}\""
                .($org ? " u organizaciji {$org}" : '').". "
                .($user->about ? $user->about.' ' : '')
                ."Vjerujem da bih kroz ovu priliku mogao/la dodatno da razvijem svoje vještine"
                .($user->skills ? " ({$user->skills})" : '').".\n\n"
                ."Motivisan/a sam da učim i doprinesem, i radujem se mogućnosti da učestvujem.\n\n"
                ."Srdačan pozdrav,\n{$user->name}";
        }

        return "Dear team,\n\nI am excited to apply for \"{$call->title}\""
            .($org ? " organized by {$org}" : '').". "
            .($user->about ? $user->about.' ' : '')
            ."I believe this opportunity would help me grow my skills"
            .($user->skills ? " ({$user->skills})" : '').".\n\n"
            ."I am motivated to learn and contribute, and I look forward to taking part.\n\n"
            ."Best regards,\n{$user->name}";
    }

    private function cvTemplate($user, string $lang): string
    {
        $L = $lang === 'cnr'
            ? ['about' => 'O MENI', 'edu' => 'OBRAZOVANJE', 'exp' => 'RADNO ISKUSTVO', 'skills' => 'VJEŠTINE', 'interests' => 'INTERESOVANJA']
            : ['about' => 'ABOUT', 'edu' => 'EDUCATION', 'exp' => 'EXPERIENCE', 'skills' => 'SKILLS', 'interests' => 'INTERESTS'];

        $lines = [
            mb_strtoupper($user->name),
            trim(($user->headline ?? '').($user->city ? ' · '.$user->city : '')),
            $user->email,
            '',
        ];
        $section = function ($title, $body) use (&$lines) {
            if (! $body) {
                return;
            }
            $lines[] = $title;
            $lines[] = $body;
            $lines[] = '';
        };
        $section($L['about'], $user->about);
        $section($L['edu'], $user->education);
        $section($L['exp'], $user->work_experience);
        $section($L['skills'], $user->skills);
        $section($L['interests'], $user->interests->pluck('name')->implode(', '));

        return implode("\n", $lines);
    }
}
