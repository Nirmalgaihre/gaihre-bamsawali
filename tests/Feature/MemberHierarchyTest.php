<?php

namespace Tests\Feature;

use App\Models\BansawaliMember;
use App\Models\User;
use App\Services\FamilyTree;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MemberHierarchyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->actingAs(User::factory()->create(['is_admin' => true]));
    }

    public function test_members_are_numbered_and_displayed_parent_before_descendants(): void
    {
        $root = BansawaliMember::factory()->create(['name_ne' => 'RootAlpha', 'display_order' => 0]);
        $other = BansawaliMember::factory()->create(['name_ne' => 'RootBeta', 'display_order' => 1]);
        $child = BansawaliMember::factory()->create(['name_ne' => 'ChildAlpha', 'parent_id' => $root->id, 'generation' => 2]);
        $grandchild = BansawaliMember::factory()->create(['name_ne' => 'GrandchildAlpha', 'parent_id' => $child->id, 'generation' => 3]);
        $this->get('/admin/members')->assertOk()->assertSee('क्र.सं.')->assertViewHas('members', function ($members) use ($root, $child, $grandchild, $other) {
            return $members->pluck('id')->all() === [$root->id, $child->id, $grandchild->id, $other->id]
                && $members->pluck('serial_number')->all() === ['1', '1.1', '1.1.1', '2'];
        });
        $this->get('/admin/members/create?parent_id='.$child->id)->assertViewHas('member', fn ($member) => (int) $member->parent_id === $child->id);
    }

    public function test_search_keeps_ancestor_context_and_branch_filter_excludes_other_families(): void
    {
        $root = BansawaliMember::factory()->create(['name_ne' => 'AncestorAlpha', 'name_en' => 'Ancestor Alpha']);
        $child = BansawaliMember::factory()->create(['name_ne' => 'SearchTarget', 'name_en' => 'Search Target', 'parent_id' => $root->id]);
        BansawaliMember::factory()->create(['name_ne' => 'OtherFamily', 'name_en' => 'Other Family']);
        $this->get('/admin/members?q=SearchTarget')->assertOk()->assertViewHas('members', fn ($members) => $members->pluck('id')->all() === [$root->id, $child->id]);
        $this->get('/admin/members?branch_id='.$child->id)->assertOk()->assertViewHas('members', fn ($members) => $members->pluck('id')->all() === [$child->id] && $members->first()->serial_number === '1.1');
    }

    public function test_latest_view_sorts_by_creation_without_changing_serial_numbers(): void
    {
        $root = BansawaliMember::factory()->create(['created_at' => now()->subDays(3)]);
        $child = BansawaliMember::factory()->create(['parent_id' => $root->id, 'created_at' => now()->subDay()]);
        $this->get('/admin/members?sort=latest')->assertOk()->assertViewHas('members', fn ($members) => $members->pluck('id')->all() === [$child->id, $root->id] && $members->pluck('serial_number')->all() === ['1.1', '1']);
    }

    public function test_pagination_keeps_numbering_and_actual_parent(): void
    {
        $root = BansawaliMember::factory()->create();
        BansawaliMember::factory()->count(31)->create(['parent_id' => $root->id]);
        $this->get('/admin/members?page=2')->assertOk()->assertViewHas('members', fn ($members) => $members->total() === 32 && $members->first()->serial_number === '1.30' && $members->first()->parent->id === $root->id);
    }

    public function test_reordering_and_moving_recalculate_numbers_and_hidden_ancestor_state(): void
    {
        $first = BansawaliMember::factory()->create(['display_order' => 0, 'status' => false]);
        $second = BansawaliMember::factory()->create(['display_order' => 1]);
        $child = BansawaliMember::factory()->create(['parent_id' => $first->id]);
        $tree = app(FamilyTree::class);
        $this->assertFalse($tree->numberedMembers()->keyBy('id')[$child->id]->public_visible);
        $first->update(['display_order' => 2]);
        $this->assertSame('2.1', $tree->numberedMembers()->keyBy('id')[$child->id]->serial_number);
        $tree->save($child, ['parent_id' => $second->id]);
        $this->assertSame('1.1', $tree->numberedMembers()->keyBy('id')[$child->id]->serial_number);
        $this->assertTrue($tree->numberedMembers()->keyBy('id')[$child->id]->public_visible);
    }

    public function test_hierarchy_loads_in_one_query_and_rejects_invalid_filters(): void
    {
        BansawaliMember::factory()->create();
        DB::enableQueryLog();
        app(FamilyTree::class)->numberedMembers();
        $this->assertCount(1, DB::getQueryLog());
        DB::disableQueryLog();
        $this->get('/admin/members?sort=bad')->assertSessionHasErrors('sort');
        $this->get('/admin/members?branch_id=999999')->assertSessionHasErrors('branch_id');
    }
}
