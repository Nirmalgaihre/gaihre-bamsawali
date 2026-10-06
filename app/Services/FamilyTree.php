<?php

namespace App\Services;

use App\Models\BansawaliMember;
use App\Models\SubjectEntry;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FamilyTree
{
    public function members(bool $public = true): Collection
    {
        $all = BansawaliMember::orderBy('display_order')->orderBy('id')->get();
        if (! $public) {
            return $all;
        }
        $groups = $all->groupBy(fn ($member) => $member->parent_id ?? 0);
        $visible = collect();
        $queue = [0];
        for ($i = 0; $i < count($queue); $i++) {
            foreach ($groups->get($queue[$i], collect()) as $member) {
                if ($member->status) {
                    $visible->push($member);
                    $queue[] = $member->id;
                }
            }
        }

        return $visible;
    }

    /** Select an existing ancestor and their visible descendants without changing lineage. */
    public function branch(Collection $members, ?int $rootId): Collection
    {
        if (! $rootId || ! $members->contains('id', $rootId)) {
            return collect();
        }
        $byId = $members->keyBy('id');
        $children = $members->groupBy('parent_id');
        $queue = [$rootId];
        $result = collect();
        for ($i = 0; $i < count($queue); $i++) {
            $result->push($byId[$queue[$i]]);
            foreach ($children->get($queue[$i], collect()) as $child) {
                $queue[] = $child->id;
            }
        }

        return $result;
    }

    /** Make the selected ancestor a visual root while retaining their actual parent. */
    public function groups(Collection $members): Collection
    {
        $ids = $members->keyBy('id');

        return $members->groupBy(fn ($member) => $ids->has($member->parent_id) ? $member->parent_id : 0);
    }

    /** Flatten the complete family hierarchy in parent-first order with stable outline numbers. */
    public function numberedMembers(): Collection
    {
        $all = $this->members(false);
        $groups = $this->groups($all);
        $byId = $all->keyBy('id');
        $stack = [];
        foreach ($groups->get(0, collect())->values() as $index => $root) {
            $stack[] = [$root, (string) ($index + 1), 0, true];
        }
        $stack = array_reverse($stack);
        $result = collect();
        while ($stack !== []) {
            [$member, $serial, $depth, $parentVisible] = array_pop($stack);
            $children = $groups->get($member->id, collect())->values();
            $member->setRelation('parent', $byId->get($member->parent_id));
            $member->setAttribute('serial_number', $serial);
            $member->setAttribute('outline_depth', $depth);
            $member->setAttribute('children_count', $children->count());
            $member->setAttribute('public_visible', $parentVisible && $member->status);
            $result->push($member);
            for ($index = $children->count() - 1; $index >= 0; $index--) {
                $stack[] = [$children[$index], $serial.'.'.($index + 1), $depth + 1, $member->public_visible];
            }
        }

        return $result;
    }

    public function save(BansawaliMember $member, array $data): BansawaliMember
    {
        return DB::transaction(function () use ($member, $data) {
            $all = BansawaliMember::orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $parentId = $data['parent_id'] ?? null;
            $cursor = $parentId;
            $visited = [];
            while ($cursor) {
                if ((int) $cursor === $member->id || isset($visited[$cursor])) {
                    throw ValidationException::withMessages(['parent_id' => 'A member cannot be their own ancestor. Choose another parent.']);
                }
                if (! $all->has($cursor)) {
                    throw ValidationException::withMessages(['parent_id' => 'The selected parent no longer exists.']);
                }
                $visited[$cursor] = true;
                $cursor = $all->get($cursor)?->parent_id;
            }
            $data['root_generation'] = $data['root_generation'] ?? $member->root_generation ?? 1;
            $data['generation'] = $parentId ? $all->get($parentId)->generation + 1 : (int) $data['root_generation'];
            $member->fill($data)->save();
            $groups = $all->groupBy(fn ($item) => $item->parent_id ?? 0);
            $queue = [[$member->id, $member->generation]];
            for ($i = 0; $i < count($queue); $i++) {
                [$id,$generation] = $queue[$i];
                foreach ($groups->get($id, collect()) as $child) {
                    $child->update(['generation' => $generation + 1]);
                    $queue[] = [$child->id, $generation + 1];
                }
            }

            return $member;
        });
    }

    public function delete(BansawaliMember $member): void
    {
        DB::transaction(function () use ($member) {
            BansawaliMember::orderBy('id')->lockForUpdate()->get();
            if ($member->children()->exists()) {
                throw ValidationException::withMessages(['member' => 'This member has children. Move or remove the children first.']);
            }
            if (SubjectEntry::where('root_member_id', $member->id)->exists()) {
                throw ValidationException::withMessages(['member' => 'This ancestor is linked to a contents entry. Change or remove that entry’s starting ancestor first.']);
            }
            $member->delete();
        });
    }
}
