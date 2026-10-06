<?php

namespace Tests\Feature;

use App\Models\BansawaliMember;
use App\Models\SubjectEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookGenealogyTest extends TestCase
{
    use RefreshDatabase;

    public function test_book_reference_and_linked_ancestor_render_on_entry_page(): void
    {
        $this->seed();
        $ancestor = BansawaliMember::factory()->create(['name_ne' => 'Book Ancestor']);
        $entry = SubjectEntry::factory()->create(['book_page' => 42, 'root_member_id' => $ancestor->id]);
        $this->get(route('subjects.show', $entry))->assertOk()->assertSee('पुस्तक पृष्ठ 42')->assertSee('Book Ancestor');
        $this->get(route('subjects'))->assertOk()->assertSee($entry->title)->assertSee('42');
    }
}
