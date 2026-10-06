<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostRequest;
use App\Models\BlogPost;
use App\Services\SafeHtml;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(): View
    {
        return view('admin.posts.index', ['posts' => BlogPost::with('author')->latest()->paginate(15)]);
    }

    public function create(): View
    {
        return view('admin.posts.form', ['post' => new BlogPost(['status' => 'draft', 'published_at' => now()])]);
    }

    public function edit(BlogPost $post): View
    {
        return view('admin.posts.form', compact('post'));
    }

    public function store(PostRequest $request): RedirectResponse
    {
        return $this->persist($request, new BlogPost);
    }

    public function update(PostRequest $request, BlogPost $post): RedirectResponse
    {
        return $this->persist($request, $post);
    }

    private function persist(PostRequest $request, BlogPost $post): RedirectResponse
    {
        $data = $request->safe()->except(['featured_image', 'remove_image']);
        $data['content'] = SafeHtml::clean($data['content']);
        if (! $post->exists) {
            $data['author_id'] = $request->user()->id;
        }
        $old = $post->featured_image;
        $new = null;
        if ($request->hasFile('featured_image')) {
            $new = $request->file('featured_image')->store('blog', 'public');
            $data['featured_image'] = $new;
        } elseif ($request->boolean('remove_image')) {
            $data['featured_image'] = null;
        }
        try {
            $post->fill($data)->save();
        } catch (\Throwable $error) {
            if ($new) {
                Storage::disk('public')->delete($new);
            } throw $error;
        }
        if ($old && $old !== $post->featured_image) {
            Storage::disk('public')->delete($old);
        }

        return redirect()->route('admin.posts.index')->with('success', 'Article saved.');
    }

    public function destroy(BlogPost $post): RedirectResponse
    {
        $image = $post->featured_image;
        $post->delete();
        if ($image) {
            Storage::disk('public')->delete($image);
        }

        return redirect()->route('admin.posts.index')->with('success', 'Article deleted.');
    }
}
