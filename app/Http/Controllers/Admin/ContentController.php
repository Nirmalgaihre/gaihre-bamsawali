<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContentRequest;
use App\Models\ContentPage;
use App\Services\SafeHtml;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ContentController extends Controller
{
    public function index(): View
    {
        return view('admin.content.index', ['pages' => ContentPage::all()]);
    }

    public function edit(ContentPage $page): View
    {
        return view('admin.content.form', compact('page'));
    }

    public function update(ContentRequest $request, ContentPage $page): RedirectResponse
    {
        $data = $request->validated();
        $data['content'] = SafeHtml::clean($data['content']);
        $page->update($data);

        return redirect()->route('admin.content.index')->with('success', 'Page content updated.');
    }
}
