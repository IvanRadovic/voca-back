<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CallResource;
use App\Models\Call;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CallController extends Controller
{
    /**
     * Public listing with filters, search and pagination.
     */
    public function index(Request $request)
    {
        $query = $this->baseQuery($request);

        // Browse only shows active calls by default.
        $query->when(
            $request->filled('status'),
            fn ($q) => $q->where('status', $request->string('status')),
            fn ($q) => $q->where('status', Call::STATUS_ACTIVE),
        );

        $this->applyFilters($query, $request);

        $calls = $query->latest()->paginate($request->integer('per_page', 12))->withQueryString();

        return CallResource::collection($calls);
    }

    /**
     * Personalized feed: calls matching the authenticated user's interests.
     */
    public function recommendations(Request $request)
    {
        $user = $request->user();
        $interestIds = $user->interests()->pluck('categories.id');

        $query = $this->baseQuery($request)->where('status', Call::STATUS_ACTIVE);

        if ($interestIds->isNotEmpty()) {
            $query->whereHas('categories', fn ($q) => $q->whereIn('categories.id', $interestIds));
        }

        $this->applyFilters($query, $request);

        $calls = $query->latest()->paginate($request->integer('per_page', 12))->withQueryString();

        return CallResource::collection($calls);
    }

    public function show(Request $request, Call $call)
    {
        $call->increment('views');

        $call->load([
            'categories',
            'nvo.nvo',
            'feedbacks.user',
            'savedByUsers' => fn ($q) => $q->select('users.id'),
            'applications' => fn ($q) => $q->select('id', 'call_id', 'user_id', 'status'),
        ])->loadCount('applications')->loadAvg('feedbacks', 'rating');

        return new CallResource($call);
    }

    /**
     * Similar calls by shared categories.
     */
    public function similar(Call $call)
    {
        $categoryIds = $call->categories()->pluck('categories.id');

        $similar = Call::query()
            ->active()
            ->where('id', '!=', $call->id)
            ->when($categoryIds->isNotEmpty(), fn ($q) => $q->whereHas(
                'categories',
                fn ($sub) => $sub->whereIn('categories.id', $categoryIds)
            ))
            ->with(['categories', 'nvo.nvo'])
            ->withAvg('feedbacks', 'rating')
            ->limit(4)
            ->get();

        return CallResource::collection($similar);
    }

    public function store(Request $request)
    {
        $data = $this->validateCall($request);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('calls', 'public');
        }

        $data['user_id'] = $request->user()->id;
        $categories = $data['categories'] ?? [];
        unset($data['categories']);

        $call = Call::create($data);
        $call->categories()->sync($categories);

        return new CallResource($call->load(['categories', 'nvo.nvo']));
    }

    public function update(Request $request, Call $call)
    {
        $this->authorizeOwner($request, $call);

        $data = $this->validateCall($request, $call);

        if ($request->hasFile('image')) {
            if ($call->image) {
                Storage::disk('public')->delete($call->image);
            }
            $data['image'] = $request->file('image')->store('calls', 'public');
        } elseif ($request->boolean('remove_image')) {
            if ($call->image) {
                Storage::disk('public')->delete($call->image);
            }
            $data['image'] = null;
        }

        $categories = $data['categories'] ?? null;
        unset($data['categories']);

        $call->update($data);

        if ($categories !== null) {
            $call->categories()->sync($categories);
        }

        return new CallResource($call->fresh(['categories', 'nvo.nvo']));
    }

    public function destroy(Request $request, Call $call)
    {
        $this->authorizeOwner($request, $call);

        if ($call->image) {
            Storage::disk('public')->delete($call->image);
        }

        $call->delete();

        return response()->json(['message' => 'Call deleted.']);
    }

    /**
     * Calls owned by the authenticated NVO.
     */
    public function myCalls(Request $request)
    {
        $calls = Call::query()
            ->where('user_id', $request->user()->id)
            ->with('categories')
            ->withCount('applications')
            ->withAvg('feedbacks', 'rating')
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return CallResource::collection($calls);
    }

    /* -------------------- helpers -------------------- */

    private function baseQuery(Request $request)
    {
        return Call::query()
            ->with(['categories', 'nvo.nvo'])
            ->withCount('applications')
            ->withAvg('feedbacks', 'rating')
            ->when($request->user(), function ($q) use ($request) {
                $q->with([
                    'savedByUsers' => fn ($sub) => $sub->where('users.id', $request->user()->id)->select('users.id'),
                    'applications' => fn ($sub) => $sub->where('user_id', $request->user()->id),
                ]);
            });
    }

    private function applyFilters($query, Request $request): void
    {
        $query
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->string('type')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%'.$request->string('search').'%';
                $q->where(fn ($sub) => $sub->where('title', 'like', $term)
                    ->orWhere('subtitle', 'like', $term)
                    ->orWhere('description', 'like', $term));
            })
            ->when($request->filled('online'), function ($q) use ($request) {
                $q->where('is_online', $request->boolean('online'));
            })
            ->when($request->filled('category'), function ($q) use ($request) {
                // Accepts a category slug or id.
                $value = $request->string('category');
                $q->whereHas('categories', function ($sub) use ($value) {
                    $sub->where('categories.slug', $value)->orWhere('categories.id', $value);
                });
            })
            ->when($request->filled('from'), fn ($q) => $q->whereDate('start_date', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('start_date', '<=', $request->date('to')));
    }

    private function authorizeOwner(Request $request, Call $call): void
    {
        abort_unless(
            $call->user_id === $request->user()->id || $request->user()->isAdmin(),
            403,
            'You do not own this call.'
        );
    }

    private function validateCall(Request $request, ?Call $call = null): array
    {
        $required = $call ? 'sometimes' : 'required';

        return $request->validate([
            'title' => [$required, 'string', 'max:100'],
            'subtitle' => ['nullable', 'string', 'max:150'],
            'description' => [$required, 'string'],
            'image' => ['nullable', 'image', 'max:5120'], // 5MB
            'type' => [$required, Rule::in(Call::TYPES)],
            'application_deadline' => [$required, 'date'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'location' => ['nullable', 'string', 'max:255'],
            'is_online' => ['boolean'],
            'max_participants' => ['nullable', 'integer', 'min:1'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'prerequisites' => ['nullable', 'array'],
            'prerequisites.*' => ['string', Rule::in(['none', 'english', 'age', 'skills'])],
            'status' => ['sometimes', Rule::in([Call::STATUS_ACTIVE, Call::STATUS_FINISHED, Call::STATUS_CANCELLED])],
            'categories' => [$required, 'array', 'min:1'],
            'categories.*' => ['integer', 'exists:categories,id'],
        ]);
    }
}
