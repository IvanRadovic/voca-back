<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StoryResource;
use App\Models\Application;
use App\Models\Call;
use App\Models\Story;
use Illuminate\Http\Request;

class StoryController extends Controller
{
    /**
     * Public stories for a call.
     */
    public function index(Call $call)
    {
        return StoryResource::collection(
            $call->stories()->with('user')->latest()->get()
        );
    }

    /**
     * Recent stories with an image, for the landing page.
     */
    public function recent()
    {
        return StoryResource::collection(
            Story::with(['user', 'call'])->latest()->limit(6)->get()
        );
    }

    /**
     * Stories authored by the current user.
     */
    public function mine(Request $request)
    {
        return StoryResource::collection(
            Story::where('user_id', $request->user()->id)->with('call')->latest()->get()
        );
    }

    /**
     * Share an impression. Only participants who completed the event may post.
     */
    public function store(Request $request, Call $call)
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'max:5120'],
        ]);

        $eligible = Application::where('call_id', $call->id)
            ->where('user_id', $request->user()->id)
            ->where('status', Application::STATUS_COMPLETED)
            ->exists();

        abort_unless($eligible, 403, 'You can only share an impression for events you have completed.');

        $payload = ['body' => $data['body']];
        if ($request->hasFile('image')) {
            $payload['image'] = $request->file('image')->store('stories', 'public');
        }

        $story = Story::updateOrCreate(
            ['user_id' => $request->user()->id, 'call_id' => $call->id],
            $payload
        );

        return new StoryResource($story->load('user'));
    }
}
