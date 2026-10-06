<?php

namespace App\Services;

use App\Models\SubjectEntry;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SubjectOutline
{
    /** Build numbered parent groups from one query; hidden ancestors hide their branches. */
    public function groups(bool $public = true): Collection
    {
        $groups = SubjectEntry::orderBy('display_order')->orderBy('id')->get()->groupBy(fn ($entry) => $entry->parent_id ?? 0);
        $result = collect();
        $queue = [[0, '', true]];
        for ($i = 0; $i < count($queue); $i++) {
            [$parentId,$prefix,$parentVisible] = $queue[$i];
            $number = 0;
            $siblings = collect();
            foreach ($groups->get($parentId, collect()) as $entry) {
                $visible = $parentVisible && $entry->status;
                if ($public && ! $visible) {
                    continue;
                }
                $number++;
                $entry->setAttribute('outline_number', $prefix.$number);
                $entry->setAttribute('public_visible', $visible);
                $siblings->push($entry);
                $queue[] = [$entry->id, $entry->outline_number.'.', $visible];
            }
            if ($siblings->isNotEmpty()) {
                $result->put($parentId, $siblings);
            }
        }

        return $result;
    }

    public function parents(SubjectEntry $entry): Collection
    {
        $all = SubjectEntry::orderBy('title')->get();
        $groups = $all->groupBy(fn ($item) => $item->parent_id ?? 0);
        $excluded = $entry->exists ? [$entry->id => true] : [];
        $queue = $entry->exists ? [$entry->id] : [];
        for ($i = 0; $i < count($queue); $i++) {
            foreach ($groups->get($queue[$i], collect()) as $child) {
                if (! isset($excluded[$child->id])) {
                    $excluded[$child->id] = true;
                    $queue[] = $child->id;
                }
            }
        }

        return $all->reject(fn ($item) => isset($excluded[$item->id]));
    }

    public function save(SubjectEntry $entry, array $data): void
    {
        DB::transaction(function () use ($entry, $data) {
            $all = SubjectEntry::orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $cursor = $data['parent_id'] ?? null;
            $seen = [];
            while ($cursor) {
                if ((int) $cursor === $entry->id || isset($seen[$cursor])) {
                    throw ValidationException::withMessages(['parent_id' => 'Choose a parent outside this entry’s own branch.']);
                }
                if (! $all->has($cursor)) {
                    throw ValidationException::withMessages(['parent_id' => 'This parent no longer exists. Choose another parent.']);
                }
                $seen[$cursor] = true;
                $cursor = $all->get($cursor)->parent_id;
            }
            $entry->fill($data)->save();
        });
    }

    public function delete(SubjectEntry $entry): void
    {
        DB::transaction(function () use ($entry) {
            SubjectEntry::orderBy('id')->lockForUpdate()->get();
            if ($entry->children()->exists()) {
                throw ValidationException::withMessages(['entry' => 'Move or delete child entries before deleting this parent.']);
            }
            $entry->delete();
        });
    }
}
