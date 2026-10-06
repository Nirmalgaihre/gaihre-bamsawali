<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\ContentPage;
use App\Models\SubjectEntry;
use App\Services\FamilyTree;
use App\Services\SubjectOutline;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PublicController extends Controller
{
    public function home(FamilyTree $tree, SubjectOutline $outline): View
    {
        $members = $tree->members();

        return view('public.home', ['places' => $outline->groups()->flatten(1)->where('type', 'location')->take(6), 'members' => $members, 'groups' => $members->groupBy(fn ($m) => $m->parent_id ?? 0), 'posts' => BlogPost::published()->latest('published_at')->limit(3)->get(), 'pages' => ContentPage::where('status', true)->get()->keyBy('key')]);
    }

    public function tree(Request $request, FamilyTree $tree): View
    {
        $request->validate(['q' => ['nullable', 'string', 'max:100']]);
        $members = $tree->members();
        $query = trim((string) $request->query('q', ''));
        $results = $query !== '' ? $members->filter(fn ($m) => mb_stripos($m->name_ne, $query) !== false || mb_stripos($m->name_en ?? '', $query) !== false) : collect();

        return view('public.tree', ['members' => $members, 'groups' => $members->groupBy(fn ($m) => $m->parent_id ?? 0), 'results' => $results, 'query' => $query, 'page' => ContentPage::where('key', 'bansawali_intro')->where('status', true)->first()]);
    }

    public function page(string $key): View
    {
        return view('public.page', ['page' => ContentPage::where('key', $key)->where('status', true)->firstOrFail()]);
    }

    public function blog(): View
    {
        return view('public.blog', ['posts' => BlogPost::published()->latest('published_at')->paginate(9)]);
    }

    public function post(string $slug): View
    {
        $post = BlogPost::published()->with('author')->where('slug', $slug)->firstOrFail();

        return view('public.post', ['post' => $post, 'related' => BlogPost::published()->whereKeyNot($post->id)->latest('published_at')->limit(3)->get()]);
    }

    public function places(SubjectOutline $outline): View
    {
        return view('public.places', [
            'places' => $outline->groups()->flatten(1)->where('type', 'location'),
            'page' => ContentPage::where('key', 'places_intro')->where('status', true)->first(),
        ]);
    }

    public function place(Request $request, SubjectEntry $subject, SubjectOutline $outline, FamilyTree $tree): View
    {
        abort_unless($subject->type === 'location', 404);

        return $this->subject($request, $subject, $outline, $tree);
    }

    public function subjects(SubjectOutline $outline): View
    {
        return view('public.subjects', ['groups' => $outline->groups()]);
    }

    public function subject(Request $request, SubjectEntry $subject, SubjectOutline $outline, FamilyTree $tree): View
    {
        $request->validate(['q' => ['nullable', 'string', 'max:100']]);
        $subjectGroups = $outline->groups();
        $visible = $subjectGroups->flatten(1)->keyBy('id');
        abort_unless($visible->has($subject->id), 404);
        $entry = $visible[$subject->id];
        $members = $tree->branch($tree->members(), $entry->root_member_id);
        $query = trim((string) $request->query('q', ''));
        $results = $query === '' ? collect() : $members->filter(fn ($member) => mb_stripos($member->name_ne, $query) !== false || mb_stripos($member->name_en ?? '', $query) !== false);
        $breadcrumbs = collect();
        $cursor = $entry->parent_id;
        while ($cursor && $visible->has($cursor)) {
            $breadcrumbs->prepend($visible[$cursor]);
            $cursor = $visible[$cursor]->parent_id;
        }

        return view('public.subject', ['entry' => $entry, 'members' => $members, 'groups' => $tree->groups($members), 'children' => $subjectGroups->get($entry->id, collect()), 'breadcrumbs' => $breadcrumbs, 'query' => $query, 'results' => $results]);
    }

    public function sitemap(SubjectOutline $outline): Response
    {
        $urls = collect([route('home'), route('places'), route('bansawali'), route('subjects'), route('blog')]);
        foreach (['gaihre_intro' => 'gaihre-parichaya', 'history' => 'history', 'about' => 'about', 'contact' => 'contact'] as $key => $route) {
            if (ContentPage::where('key', $key)->where('status', true)->exists()) {
                $urls->push(route($route));
            }
        }
        foreach ($outline->groups()->flatten(1) as $entry) {
            $urls->push($entry->publicUrl());
        }
        foreach (BlogPost::published()->get() as $post) {
            $urls->push(route('blog.show', $post->slug));
        }

        return response()->view('public.sitemap', compact('urls'))->header('Content-Type', 'application/xml');
    }
}
