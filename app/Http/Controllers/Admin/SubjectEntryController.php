<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubjectEntryRequest;
use App\Models\BansawaliMember;
use App\Models\SubjectEntry;
use App\Services\FamilyTree;
use App\Services\SubjectOutline;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SubjectEntryController extends Controller
{
    public function index(SubjectOutline $outline): View
    {
        return view('admin.subjects.index', ['groups' => $outline->groups(false)]);
    }

    public function places(SubjectOutline $outline): View
    {
        return view('admin.subjects.places', ['places' => $outline->groups(false)->flatten(1)->where('type', 'location')]);
    }

    public function create(Request $request, SubjectOutline $outline): View
    {
        $request->validate(['parent_id' => ['nullable', 'integer', 'exists:subject_entries,id'], 'type' => ['nullable', Rule::in(array_keys(SubjectEntry::TYPES))]]);
        $entry = new SubjectEntry(['parent_id' => $request->query('parent_id'), 'type' => $request->query('type', 'topic'), 'display_order' => 0, 'status' => true]);

        return view('admin.subjects.form', ['entry' => $entry, 'parents' => $outline->parents($entry), 'ancestors' => BansawaliMember::orderBy('generation')->orderBy('name_ne')->get()]);
    }

    public function store(SubjectEntryRequest $request, SubjectOutline $outline): RedirectResponse
    {
        $outline->save(new SubjectEntry, $request->validated());

        return redirect()->route('admin.subjects.index')->with('success', 'Entry added. Outline numbering updated automatically.');
    }

    public function edit(SubjectEntry $subject, SubjectOutline $outline): View
    {
        return view('admin.subjects.form', ['entry' => $subject, 'parents' => $outline->parents($subject), 'ancestors' => BansawaliMember::orderBy('generation')->orderBy('name_ne')->get()]);
    }

    public function update(SubjectEntryRequest $request, SubjectEntry $subject, SubjectOutline $outline): RedirectResponse
    {
        $outline->save($subject, $request->validated());

        return redirect()->route('admin.subjects.index')->with('success', 'Entry saved. Outline numbering updated automatically.');
    }

    public function show(SubjectEntry $subject, FamilyTree $tree): View
    {
        $members = $tree->branch($tree->members(false), $subject->root_member_id);

        return view('admin.subjects.chart', ['children' => $subject->children()->orderBy('display_order')->orderBy('id')->get(), 'entry' => $subject, 'members' => $members, 'groups' => $tree->groups($members)]);
    }

    public function destroy(SubjectEntry $subject, SubjectOutline $outline): RedirectResponse
    {
        $outline->delete($subject);

        return redirect()->route('admin.subjects.index')->with('success', 'Entry deleted.');
    }
}
