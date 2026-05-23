<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApplicationResource;
use App\Models\Application;
use App\Models\Call;
use App\Notifications\ApplicationReceivedNotification;
use App\Notifications\ApplicationStatusNotification;
use App\Notifications\CallAnnouncementNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApplicationController extends Controller
{
    /**
     * Youth user applies to a call (one click).
     */
    public function store(Request $request, Call $call)
    {
        abort_if($call->status !== Call::STATUS_ACTIVE, 422, 'This call is no longer accepting applications.');
        abort_if($call->application_deadline->isPast(), 422, 'The application deadline has passed.');

        $data = $request->validate([
            'message' => ['nullable', 'string', 'max:1000'],
        ]);

        $application = Application::firstOrCreate(
            ['call_id' => $call->id, 'user_id' => $request->user()->id],
            ['status' => Application::STATUS_PENDING, 'message' => $data['message'] ?? null]
        );

        if ($application->wasRecentlyCreated) {
            $request->user()->notify(new ApplicationReceivedNotification($call));
        }

        return (new ApplicationResource($application->load('call')))
            ->response()
            ->setStatusCode($application->wasRecentlyCreated ? 201 : 200);
    }

    /**
     * Youth user cancels their application.
     */
    public function destroy(Request $request, Call $call)
    {
        Application::where('call_id', $call->id)
            ->where('user_id', $request->user()->id)
            ->delete();

        return response()->json(['message' => 'Application withdrawn.']);
    }

    /**
     * Applications of the authenticated youth user (with statuses).
     */
    public function myApplications(Request $request)
    {
        $applications = Application::query()
            ->where('user_id', $request->user()->id)
            ->with(['call.categories', 'call.nvo.nvo'])
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return ApplicationResource::collection($applications);
    }

    /**
     * Applicants for a call owned by the authenticated NVO,
     * with optional status filter, search and sorting.
     */
    public function applicants(Request $request, Call $call)
    {
        $this->authorizeOwner($request, $call);

        $applications = $this->applicantsQuery($request, $call)
            ->paginate($request->integer('per_page', 25))
            ->withQueryString();

        return ApplicationResource::collection($applications);
    }

    /**
     * Export the applicants of a call as a CSV download.
     */
    public function exportApplicants(Request $request, Call $call): StreamedResponse
    {
        $this->authorizeOwner($request, $call);

        $applications = $this->applicantsQuery($request, $call)->get();
        $filename = 'applicants-call-'.$call->id.'-'.now()->format('Ymd').'.csv';

        return response()->streamDownload(function () use ($applications) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Name', 'Email', 'Age', 'City', 'Education', 'Phone', 'Status', 'Applied at']);
            foreach ($applications as $app) {
                fputcsv($out, [
                    $app->user->name,
                    $app->user->email,
                    $app->user->age ?? '',
                    $app->user->city ?? '',
                    $app->user->education_level ?? '',
                    $app->user->phone ?? '',
                    $app->status,
                    $app->created_at->toDateTimeString(),
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /**
     * Shared query builder for applicants listing + export.
     */
    private function applicantsQuery(Request $request, Call $call)
    {
        $query = Application::query()
            ->where('call_id', $call->id)
            ->with('user.interests')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%'.$request->string('search').'%';
                $q->whereHas('user', fn ($sub) => $sub->where('name', 'like', $term)->orWhere('email', 'like', $term));
            });

        // Sorting. Age sorts via the applicant's date of birth.
        return match ($request->string('sort')->value()) {
            'oldest' => $query->oldest(),
            'name' => $query->join('users', 'users.id', '=', 'applications.user_id')
                ->orderBy('users.name')->select('applications.*'),
            'age' => $query->join('users', 'users.id', '=', 'applications.user_id')
                ->orderBy('users.date_of_birth')->select('applications.*'),
            'status' => $query->orderBy('status'),
            default => $query->latest(),
        };
    }

    /**
     * NVO accepts / rejects / completes an application.
     */
    public function updateStatus(Request $request, Application $application)
    {
        $this->authorizeOwner($request, $application->call);

        $data = $request->validate([
            'status' => ['required', Rule::in([
                Application::STATUS_PENDING,
                Application::STATUS_ACCEPTED,
                Application::STATUS_REJECTED,
                Application::STATUS_COMPLETED,
            ])],
        ]);

        $application->update($data);
        $application->user->notify(new ApplicationStatusNotification($application->load('call')));

        return new ApplicationResource($application->load('user.interests'));
    }

    /**
     * NVO sends an email announcement to every applicant of a call.
     */
    public function announce(Request $request, Call $call)
    {
        $this->authorizeOwner($request, $call);

        $data = $request->validate([
            'subject' => ['required', 'string', 'max:150'],
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $recipients = $call->applications()->with('user')->get()->pluck('user');

        Notification::send(
            $recipients,
            new CallAnnouncementNotification($call, $data['subject'], $data['body'])
        );

        return response()->json([
            'message' => 'Announcement queued for '.$recipients->count().' applicant(s).',
        ]);
    }

    private function authorizeOwner(Request $request, Call $call): void
    {
        abort_unless(
            $call->user_id === $request->user()->id || $request->user()->isAdmin(),
            403,
            'You do not own this call.'
        );
    }
}
