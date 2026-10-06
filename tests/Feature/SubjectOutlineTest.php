<?php

namespace Tests\Feature;

use App\Models\SubjectEntry;
use App\Models\User;
use App\Services\SubjectOutline;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SubjectOutlineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function data(array $overrides = []): array
    {
        return array_merge(['title' => 'कुजे', 'type' => 'location', 'location' => 'नेपाल', 'description' => 'स्थानको विवरण', 'additional_notes' => 'थप जानकारी', 'parent_id' => null, 'display_order' => 0, 'status' => 1], $overrides);
    }

    public function test_only_admins_can_manage_entries(): void
    {
        $this->get('/admin/subjects')->assertRedirect(route('login'));
        $this->post('/admin/subjects', $this->data())->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())->post('/admin/subjects', $this->data())->assertForbidden();
        $this->assertDatabaseCount('subject_entries', 0);
        $this->get('/bisaya-suchi')->assertOk()->assertSee('विषय सूची');
    }

    public function test_admin_creates_edits_and_deletes_entries_with_preselected_parent(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $this->get('/admin/subjects/create')->assertOk();
        $this->post('/admin/subjects', $this->data())->assertRedirect(route('admin.subjects.index'));
        $parent = SubjectEntry::firstOrFail();
        $this->get('/admin/subjects/create?parent_id='.$parent->id)->assertOk()->assertViewHas('entry', fn ($entry) => (int) $entry->parent_id === $parent->id);
        $this->post('/admin/subjects', $this->data(['title' => 'उपविषय', 'type' => 'topic', 'parent_id' => $parent->id]))->assertSessionHasNoErrors();
        $child = SubjectEntry::where('title', 'उपविषय')->firstOrFail();
        $this->get('/admin/subjects')->assertOk()->assertSee('1.1');
        $this->get('/admin/subjects/'.$child->id.'/edit')->assertOk();
        $this->put('/admin/subjects/'.$child->id, $this->data(['title' => 'Changed', 'parent_id' => null, 'display_order' => 2]))->assertSessionHasNoErrors();
        $this->assertNull($child->fresh()->parent_id);
        $this->get('/bisaya-suchi')->assertSee('Changed')->assertSee('स्थानको विवरण')->assertSee('थप जानकारी');
        $this->delete('/admin/subjects/'.$child->id)->assertSessionHasNoErrors();
        $this->assertModelMissing($child);
    }

    public function test_numbering_follows_sibling_order_and_updates_after_moves(): void
    {
        $first = SubjectEntry::factory()->create(['display_order' => 10]);
        $second = SubjectEntry::factory()->create(['display_order' => 20]);
        $child = SubjectEntry::factory()->create(['parent_id' => $first->id]);
        $grandchild = SubjectEntry::factory()->create(['parent_id' => $child->id]);
        $outline = app(SubjectOutline::class);
        $groups = $outline->groups();
        $this->assertSame('1.1.1', $groups[$child->id]->first()->outline_number);
        $second->update(['display_order' => 0]);
        $this->assertSame('2.1.1', $outline->groups()[$child->id]->first()->outline_number);
        $outline->save($child, $this->data(['parent_id' => $second->id]));
        $this->assertSame('1.1.1', $outline->groups()[$child->id]->first()->outline_number);
        $this->assertSame($child->id, $grandchild->fresh()->parent_id);
    }

    public function test_cycles_and_parent_deletion_are_blocked_and_selector_excludes_descendants(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $root = SubjectEntry::factory()->create();
        $child = SubjectEntry::factory()->create(['parent_id' => $root->id]);
        $grandchild = SubjectEntry::factory()->create(['parent_id' => $child->id]);
        $other = SubjectEntry::factory()->create();
        $this->put('/admin/subjects/'.$root->id, $this->data(['parent_id' => $grandchild->id]))->assertSessionHasErrors('parent_id');
        $this->put('/admin/subjects/'.$root->id, $this->data(['parent_id' => $root->id]))->assertSessionHasErrors('parent_id');
        $this->delete('/admin/subjects/'.$root->id)->assertSessionHasErrors('entry');
        $this->assertModelExists($root);
        $this->assertSame([$other->id], app(SubjectOutline::class)->parents($root)->pluck('id')->values()->all());
    }

    public function test_hidden_branches_are_private_and_public_numbering_is_contiguous(): void
    {
        $root = SubjectEntry::factory()->create(['title' => 'HiddenRoot', 'status' => false]);
        SubjectEntry::factory()->create(['title' => 'HiddenChild', 'parent_id' => $root->id]);
        SubjectEntry::factory()->create(['title' => 'PublicRoot']);
        $this->get('/bisaya-suchi')->assertDontSee('HiddenRoot')->assertDontSee('HiddenChild')->assertSee('PublicRoot');
        $groups = app(SubjectOutline::class)->groups();
        $this->assertSame('1', $groups[0]->first()->outline_number);
        $this->actingAs(User::factory()->create(['is_admin' => true]))->get('/admin/subjects')->assertSee('HiddenChild')->assertSee('Hidden by parent');
    }

    public function test_invalid_input_is_rejected_and_public_text_is_escaped(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $this->post('/admin/subjects', $this->data(['type' => 'bad', 'parent_id' => 99999, 'display_order' => -1, 'title' => '']))->assertSessionHasErrors(['type', 'parent_id', 'display_order', 'title']);
        $this->post('/admin/subjects', $this->data(['description' => '<script>alert(1)</script>']))->assertSessionHasNoErrors();
        $this->get('/bisaya-suchi')->assertDontSee('<script>alert(1)</script>', false)->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
    }

    public function test_deep_outline_uses_one_query_and_is_discoverable(): void
    {
        $parent = null;
        for ($i = 0; $i < 20; $i++) {
            $parent = SubjectEntry::factory()->create(['parent_id' => $parent?->id]);
        }
        DB::enableQueryLog();
        $groups = app(SubjectOutline::class)->groups();
        $this->assertCount(1, DB::getQueryLog());
        DB::disableQueryLog();
        $this->assertSame(implode('.', array_fill(0, 20, '1')), $groups[$parent->parent_id]->first()->outline_number);
        $this->get('/bisaya-suchi')->assertOk()->assertSee($parent->title);
        $this->get('/')->assertSee(route('subjects'));
        $this->get('/sitemap.xml')->assertSee(route('subjects'));
    }
}
