<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BansawaliMember;
use App\Models\BlogPost;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', ['stats' => ['Members' => BansawaliMember::count(), 'Generations' => BansawaliMember::max('generation') ?? 0, 'Articles' => BlogPost::count(), 'Published' => BlogPost::published()->count(), 'Drafts' => BlogPost::where('status', 'draft')->count()], 'members' => BansawaliMember::latest()->limit(5)->get(), 'posts' => BlogPost::latest()->limit(5)->get()]);
    }
}
