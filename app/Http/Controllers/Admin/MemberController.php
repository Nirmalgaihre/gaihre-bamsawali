<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MemberRequest;
use App\Models\BansawaliMember;
use App\Services\FamilyTree;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function index(Request $request, FamilyTree $tree): View
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'branch_id' => ['nullable', 'integer', 'exists:bansawali_members,id'],
            'sort' => ['nullable', Rule::in(['hierarchy', 'latest'])],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);
        $all = $tree->numberedMembers();
        $byId = $all->keyBy('id');
        $branch = $request->filled('branch_id') ? $byId->get($request->integer('branch_id')) : null;
        $rows = $branch ? $all->whereIn('id', $tree->branch($all, $branch->id)->pluck('id')) : $all;
        $query = trim((string) $request->query('q', ''));
        $sort = $request->query('sort', 'hierarchy');
        if ($query !== '') {
            $matches = $rows->filter(fn ($member) => mb_stripos($member->name_ne, $query) !== false || mb_stripos($member->name_en ?? '', $query) !== false);
            $included = array_fill_keys($matches->pluck('id')->all(), true);
            if ($sort === 'hierarchy') {
                $scope = $rows->keyBy('id');
                foreach ($matches as $match) {
                    $cursor = $match->parent_id;
                    while ($cursor && $scope->has($cursor)) {
                        $included[$cursor] = true;
                        $cursor = $scope[$cursor]->parent_id;
                    }
                }
            }
            $rows = $rows->filter(fn ($member) => isset($included[$member->id]));
        }
        if ($sort === 'latest') {
            $rows = $rows->sortBy([['created_at', 'desc'], ['id', 'desc']]);
        }
        $rows = $rows->values();
        $members = new LengthAwarePaginator(
            $rows->forPage($request->integer('page', 1), 30)->values(),
            $rows->count(), 30, $request->integer('page', 1),
            ['path' => route('admin.members.index'), 'query' => $request->query()]
        );

        return view('admin.members.index', compact('members', 'branch', 'query', 'sort'));
    }

    public function create(Request $request): View
    {
        $request->validate(['parent_id' => ['nullable', 'integer', 'exists:bansawali_members,id']]);
        $member = new BansawaliMember(['parent_id' => $request->query('parent_id'), 'status' => true, 'display_order' => 0]);

        return view('admin.members.form', ['member' => $member, 'parents' => BansawaliMember::orderBy('name_ne')->get()]);
    }

    public function store(MemberRequest $request, FamilyTree $tree): RedirectResponse
    {
        $tree->save(new BansawaliMember, $request->validated());

        return redirect()->route('admin.members.index')->with('success', 'Member added. The family tree is up to date.');
    }

    public function edit(BansawaliMember $member): View
    {
        return view('admin.members.form', ['member' => $member, 'parents' => BansawaliMember::whereKeyNot($member->id)->orderBy('name_ne')->get()]);
    }

    public function update(MemberRequest $request, BansawaliMember $member, FamilyTree $tree): RedirectResponse
    {
        $tree->save($member, $request->validated());

        return redirect()->route('admin.members.index')->with('success', 'Member and descendant generations updated.');
    }

    public function destroy(BansawaliMember $member, FamilyTree $tree): RedirectResponse
    {
        $tree->delete($member);

        return redirect()->route('admin.members.index')->with('success', 'Member deleted.');
    }

    public function tree(FamilyTree $tree): View
    {
        $members = $tree->members(false);

        return view('admin.members.tree', ['members' => $members, 'groups' => $members->groupBy(fn ($m) => $m->parent_id ?? 0)]);
    }
}
