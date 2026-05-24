<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PostController extends Controller
{
    /**
     * Public list of published posts, filterable by type + search.
     */
    public function index(Request $request)
    {
        $posts = Post::query()
            ->published()
            ->with('author')
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->string('type')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%'.$request->string('search').'%';
                $q->where(fn ($sub) => $sub->where('title', 'like', $term)->orWhere('excerpt', 'like', $term));
            })
            ->latest('published_at')
            ->paginate($request->integer('per_page', 9))
            ->withQueryString();

        return PostResource::collection($posts);
    }

    public function show(Post $post)
    {
        abort_if($post->published_at === null, 404);

        return new PostResource($post->load('author'));
    }

    /**
     * Posts authored by the current NVO/admin.
     */
    public function mine(Request $request)
    {
        return PostResource::collection(
            Post::where('author_id', $request->user()->id)->latest()->get()
        );
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['author_id'] = $request->user()->id;
        $data['slug'] = Post::uniqueSlug($data['title']);
        $data['published_at'] = ($data['published'] ?? true) ? now() : null;
        unset($data['published']);

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('posts', 'public');
        }

        $post = Post::create($data);

        return new PostResource($post->load('author'));
    }

    public function update(Request $request, Post $post)
    {
        $this->authorizeOwner($request, $post);
        $data = $this->validateData($request, $post);

        if (array_key_exists('title', $data)) {
            $data['slug'] = Post::uniqueSlug($data['title'], $post->id);
        }
        if (array_key_exists('published', $data)) {
            $data['published_at'] = $data['published'] ? ($post->published_at ?? now()) : null;
            unset($data['published']);
        }

        if ($request->hasFile('cover_image')) {
            if ($post->cover_image) {
                Storage::disk('public')->delete($post->cover_image);
            }
            $data['cover_image'] = $request->file('cover_image')->store('posts', 'public');
        }

        $post->update($data);

        return new PostResource($post->fresh('author'));
    }

    public function destroy(Request $request, Post $post)
    {
        $this->authorizeOwner($request, $post);

        if ($post->cover_image) {
            Storage::disk('public')->delete($post->cover_image);
        }
        $post->delete();

        return response()->json(['message' => 'Post deleted.']);
    }

    private function validateData(Request $request, ?Post $post = null): array
    {
        $required = $post ? 'sometimes' : 'required';

        return $request->validate([
            'type' => [$required, Rule::in(Post::TYPES)],
            'title' => [$required, 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:300'],
            'body' => [$required, 'string'],
            'cover_image' => ['nullable', 'image', 'max:5120'],
            'published' => ['sometimes', 'boolean'],
        ]);
    }

    private function authorizeOwner(Request $request, Post $post): void
    {
        abort_unless(
            $request->user()->id === $post->author_id || $request->user()->isAdmin(),
            403,
            'You cannot manage this post.'
        );
    }
}
