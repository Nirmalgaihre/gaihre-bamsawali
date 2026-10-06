<?php

namespace Tests\Feature;

use App\Models\BansawaliMember;
use App\Models\ContentPage;
use App\Models\SubjectEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlaceSectionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_places_are_separate_public_sections_with_editable_introduction(): void
    {
        $place = SubjectEntry::factory()->create(['title' => 'Place A', 'type' => 'location']);
        $other = SubjectEntry::factory()->create(['title' => 'Place B', 'type' => 'location']);
        SubjectEntry::factory()->create(['title' => 'General topic', 'type' => 'topic']);
        ContentPage::where('key', 'places_intro')->update(['title' => 'Our places', 'content' => '<p>Editable introduction</p>']);
        $this->get('/places')->assertOk()->assertSee('Our places')->assertSee('Editable introduction')->assertSee('Place A')->assertSee('Place B')->assertDontSee('General topic');
        $this->get($place->publicUrl())->assertOk()->assertSee('Place A')->assertDontSee('Place B');
        $this->get('/')->assertSee($place->publicUrl())->assertSee($other->publicUrl());
        $this->get('/sitemap.xml')->assertSee($place->publicUrl());
    }

    public function test_place_shows_only_its_own_children_and_selected_family_branch(): void
    {
        $ancestor = BansawaliMember::factory()->create(['name_ne' => 'AncestorAlpha']);
        BansawaliMember::factory()->create(['parent_id' => $ancestor->id, 'name_ne' => 'ChildAlpha', 'generation' => 2]);
        BansawaliMember::factory()->create(['name_ne' => 'UnrelatedBeta']);
        $place = SubjectEntry::factory()->create(['type' => 'location', 'root_member_id' => $ancestor->id]);
        SubjectEntry::factory()->create(['parent_id' => $place->id, 'title' => 'Local history']);
        SubjectEntry::factory()->create(['title' => 'Other history']);
        $this->get($place->publicUrl())->assertOk()->assertSee('AncestorAlpha')->assertSee('ChildAlpha')->assertSee('Local history')->assertDontSee('UnrelatedBeta')->assertDontSee('Other history');
        $this->get($place->publicUrl().'?q=UnrelatedBeta')->assertDontSee('data-focus-member', false);
    }

    public function test_hidden_places_and_descendants_cannot_be_accessed_directly(): void
    {
        $parent = SubjectEntry::factory()->create(['type' => 'location', 'title' => 'HiddenPlace', 'status' => false]);
        $child = SubjectEntry::factory()->create(['type' => 'location', 'title' => 'HiddenSubplace', 'parent_id' => $parent->id]);
        $this->get('/places')->assertDontSee('HiddenPlace')->assertDontSee('HiddenSubplace');
        $this->get($child->publicUrl())->assertNotFound();
        $this->get('/sitemap.xml')->assertDontSee($child->publicUrl());
        $topic = SubjectEntry::factory()->create(['type' => 'topic']);
        $this->get(route('places.show', $topic))->assertNotFound();
        $this->get(route('subjects.show', $topic))->assertOk();
    }

    public function test_admin_can_create_a_place_then_child_sections_and_manage_them(): void
    {
        $this->get('/admin/places')->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $this->get('/admin/subjects/create?type=location')->assertOk()->assertViewHas('entry', fn ($entry) => $entry->type === 'location');
        $data = ['title' => 'New place', 'type' => 'location', 'description' => 'Place description', 'display_order' => 0, 'status' => 1];
        $this->post('/admin/subjects', $data)->assertSessionHasNoErrors();
        $place = SubjectEntry::where('title', 'New place')->firstOrFail();
        $this->post('/admin/subjects', array_merge($data, ['title' => 'History section', 'type' => 'section', 'parent_id' => $place->id]))->assertSessionHasNoErrors();
        $this->get('/admin/places')->assertOk()->assertSee('New place');
        $this->get(route('admin.subjects.show', $place))->assertOk()->assertSee('History section')->assertSee('Add sub-place');
        $this->get($place->publicUrl())->assertSee('Place description')->assertSee('History section');
        $this->put(route('admin.subjects.update', $place), array_merge($data, ['description' => 'Changed description']))->assertSessionHasNoErrors();
        $this->get($place->publicUrl())->assertSee('Changed description');
    }
}
