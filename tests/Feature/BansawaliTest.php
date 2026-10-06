<?php

namespace Tests\Feature;

use App\Models\BansawaliMember;
use App\Models\BlogPost;
use App\Models\ContentPage;
use App\Models\Setting;
use App\Models\User;
use App\Services\FamilyTree;
use App\Services\SafeHtml;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BansawaliTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function memberData(array $extra = []): array
    {
        return array_merge(['name_ne' => 'राम गैह्रे', 'name_en' => 'Ram Gaihre', 'parent_id' => null, 'short_description' => 'Test record', 'display_order' => 0, 'status' => 1], $extra);
    }

    private function postData(array $extra = []): array
    {
        return array_merge(['title' => 'गैह्रे इतिहास', 'slug' => 'gaihre-history', 'content' => '<h2>History</h2><p>Our history.</p>', 'status' => 'published', 'published_at' => now()->subMinute()->format('Y-m-d H:i:s')], $extra);
    }

    public function test_public_pages_and_sitemap_render(): void
    {
        foreach (['/', '/bansawali', '/gaihre-parichaya', '/history', '/about', '/contact', '/blog', '/sitemap.xml', '/admin/login'] as $url) {
            $this->get($url)->assertOk();
        }
        $this->get('/sitemap.xml')->assertHeader('Content-Type', 'application/xml')->assertSee(route('bansawali'));
    }

    public function test_guests_and_non_admins_cannot_manage_content(): void
    {
        $this->get('/admin')->assertRedirect(route('login'));
        $this->post('/admin/members', $this->memberData())->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())->get('/admin')->assertForbidden();
        $this->post('/admin/members', $this->memberData())->assertForbidden();
    }

    public function test_admin_can_login_and_logout_but_regular_users_cannot_login(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'password' => 'CorrectPassword123!']);
        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'CorrectPassword123!'])->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
        $this->post('/admin/logout')->assertRedirect(route('home'));
        $this->assertGuest();
        $user = User::factory()->create(['password' => 'CorrectPassword123!']);
        $this->post('/admin/login', ['email' => $user->email, 'password' => 'CorrectPassword123!'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_attempts_are_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/admin/login', ['email' => 'throttle@example.com', 'password' => 'wrong'])->assertSessionHasErrors('email');
        }
        $this->post('/admin/login', ['email' => 'throttle@example.com', 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->assertStringContainsString('Too many attempts.', session('errors')->first('email'));
    }

    public function test_every_admin_screen_renders(): void
    {
        $this->actingAs($this->admin());
        $member = BansawaliMember::factory()->create();
        $post = BlogPost::factory()->create();
        $page = ContentPage::first();
        foreach (['/admin', '/admin/members', '/admin/members/create', '/admin/members/create?parent_id='.$member->id, '/admin/members/tree', '/admin/members/'.$member->id.'/edit', '/admin/posts', '/admin/posts/create', '/admin/posts/'.$post->id.'/edit', '/admin/content', '/admin/content/'.$page->id.'/edit', '/admin/settings'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_members_can_be_added_moved_and_deleted_with_generations_updated(): void
    {
        $this->actingAs($this->admin());
        $root = BansawaliMember::factory()->create();
        $this->post('/admin/members', $this->memberData(['parent_id' => $root->id]))->assertRedirect(route('admin.members.index'));
        $child = BansawaliMember::where('name_en', 'Ram Gaihre')->firstOrFail();
        $this->assertSame(2, $child->generation);
        $grandchild = app(FamilyTree::class)->save(new BansawaliMember, $this->memberData(['parent_id' => $child->id, 'name_ne' => 'अर्जुन गैह्रे']));
        $this->assertSame(3, $grandchild->generation);
        $this->put('/admin/members/'.$child->id, $this->memberData())->assertSessionHasNoErrors();
        $this->assertSame(1, $child->fresh()->generation);
        $this->assertSame(2, $grandchild->fresh()->generation);
        $this->delete('/admin/members/'.$grandchild->id)->assertSessionHasNoErrors();
        $this->assertModelMissing($grandchild);
    }

    public function test_cycle_and_parent_deletion_are_rejected(): void
    {
        $this->actingAs($this->admin());
        $root = BansawaliMember::factory()->create();
        $child = BansawaliMember::factory()->create(['parent_id' => $root->id, 'generation' => 2]);
        $this->put('/admin/members/'.$root->id, $this->memberData(['parent_id' => $child->id]))->assertSessionHasErrors('parent_id');
        $this->put('/admin/members/'.$root->id, $this->memberData(['parent_id' => $root->id]))->assertSessionHasErrors('parent_id');
        $this->delete('/admin/members/'.$root->id)->assertSessionHasErrors('member');
        $this->assertModelExists($root);
        $this->assertModelExists($child);
    }

    public function test_hiding_parent_hides_entire_branch_and_search_finds_visible_names(): void
    {
        $root = BansawaliMember::factory()->create(['name_ne' => 'हरि गैह्रे']);
        BansawaliMember::factory()->create(['parent_id' => $root->id, 'name_ne' => 'कृष्ण गैह्रे', 'name_en' => 'Krishna Gaihre', 'generation' => 2]);
        $this->get('/bansawali?q=Krishna')->assertOk()->assertSee('कृष्ण गैह्रे')->assertSee('data-focus-member', false);
        $root->update(['status' => false]);
        $this->get('/bansawali?q=Krishna')->assertDontSee('कृष्ण गैह्रे')->assertDontSee('हरि गैह्रे');
    }

    public function test_tree_uses_one_query_and_supports_deep_generations(): void
    {
        $parent = null;
        for ($i = 1; $i <= 40; $i++) {
            $parent = BansawaliMember::factory()->create(['parent_id' => $parent?->id, 'generation' => $i]);
        }
        DB::enableQueryLog();
        app(FamilyTree::class)->members();
        $this->assertCount(1, DB::getQueryLog());
        DB::disableQueryLog();
        $this->get('/bansawali')->assertOk()->assertSee('40 पुस्ता');
    }

    public function test_articles_upload_publish_schedule_and_remove_images(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin());
        $this->post('/admin/posts', $this->postData(['featured_image' => UploadedFile::fake()->image('history.jpg')]))->assertSessionHasNoErrors();
        $post = BlogPost::firstOrFail();
        $image = $post->featured_image;
        Storage::disk('public')->assertExists($image);
        $this->get('/blog/'.$post->slug)->assertOk()->assertSee('गैह्रे इतिहास');
        $this->put('/admin/posts/'.$post->id, $this->postData(['status' => 'draft', 'remove_image' => 1]))->assertSessionHasNoErrors();
        Storage::disk('public')->assertMissing($image);
        $this->get('/blog/'.$post->slug)->assertNotFound();
        $this->put('/admin/posts/'.$post->id, $this->postData(['published_at' => now()->addDay()->format('Y-m-d H:i:s')]))->assertSessionHasNoErrors();
        $this->get('/blog/'.$post->slug)->assertNotFound();
        $this->get('/sitemap.xml')->assertDontSee('/blog/'.$post->slug);
        $this->delete('/admin/posts/'.$post->id)->assertSessionHasNoErrors();
        $this->assertModelMissing($post);
    }

    public function test_post_validation_rejects_duplicate_slug_bad_upload_and_missing_publication_date(): void
    {
        $this->actingAs($this->admin());
        BlogPost::factory()->create(['slug' => 'gaihre-history']);
        $this->post('/admin/posts', $this->postData(['featured_image' => UploadedFile::fake()->create('bad.svg', 1, 'image/svg+xml'), 'published_at' => null]))->assertSessionHasErrors(['slug', 'featured_image', 'published_at']);
        $this->post('/admin/posts', $this->postData(['slug' => '../bad']))->assertSessionHasErrors('slug');
    }

    public function test_content_and_settings_are_dynamic_and_sanitized(): void
    {
        $this->actingAs($this->admin());
        $page = ContentPage::where('key', 'history')->firstOrFail();
        $this->put('/admin/content/'.$page->id, ['title' => 'Updated history', 'content' => '<h2 onclick="alert(1)">Heading</h2><script>alert(2)</script><p>Safe text</p>', 'status' => 1])->assertSessionHasNoErrors();
        $this->get('/history')->assertSee('Updated history')->assertSee('Safe text')->assertDontSee('onclick', false)->assertDontSee('alert(2)', false);
        $data = ['hero_title' => 'जरा सम्झौँ।', 'hero_accent' => 'पुस्ता जोडौँ।', 'site_name' => 'New Archive', 'site_name_ne' => 'नयाँ अभिलेख', 'tagline' => 'Updated tagline', 'footer_text' => 'Updated footer', 'contact_text' => 'Contact the archive.'];
        $this->put('/admin/settings', $data)->assertSessionHasNoErrors();
        $this->get('/')->assertSee('New Archive')->assertSee('Updated tagline');
        $this->put('/admin/content/'.$page->id, ['title' => 'Hidden history', 'content' => 'Private draft', 'status' => 0]);
        $this->get('/history')->assertNotFound();
    }

    public function test_html_sanitizer_removes_unsafe_markup_and_keeps_allowed_formatting(): void
    {
        $html = SafeHtml::clean('<h2>Title</h2><p style="color:red">Text <strong>bold</strong><a href="javascript:alert(1)" onclick="x()">bad</a><a href="https://example.com">safe</a><img src=x onerror=alert(1)><svg><script>x()</script></svg></p>');
        $this->assertStringContainsString('<strong>bold</strong>', $html);
        $this->assertStringContainsString('href="https://example.com"', $html);
        foreach (['javascript:', 'onclick', 'onerror', '<svg', '<script', 'style=', '<img'] as $unsafe) {
            $this->assertStringNotContainsString($unsafe, $html);
        }
    }

    public function test_seeding_preserves_existing_content_and_admin_setup_is_explicit(): void
    {
        Setting::where('key', 'site_name')->update(['value' => 'Custom title']);
        $this->seed();
        $this->assertSame('Custom title', Setting::where('key', 'site_name')->value('value'));
        config(['bansawali.admin_email' => 'admin@example.com', 'bansawali.admin_password' => 'SafePassword123!']);
        $this->artisan('bansawali:admin')->assertSuccessful();
        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $this->assertTrue($admin->is_admin);
        $original = $admin->password;
        config(['bansawali.admin_password' => 'AnotherPassword123!']);
        $this->artisan('bansawali:admin')->assertSuccessful();
        $this->assertSame($original, $admin->fresh()->password);
        $this->artisan('bansawali:admin', ['--reset-password' => true])->assertSuccessful();
        $this->assertNotSame($original, $admin->fresh()->password);
    }
}
