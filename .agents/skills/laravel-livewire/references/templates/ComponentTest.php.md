---
name: ComponentTest
description: Complete Livewire component testing patterns
file-type: php
---

# Component Testing

## tests/Feature/Livewire/CreatePostTest.php

```php
<?php

namespace Tests\Feature\Livewire;

use App\Livewire\CreatePost;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CreatePostTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function can_render_component(): void
    {
        $this->actingAs($this->user);

        Livewire::test(CreatePost::class)
            ->assertStatus(200)
            ->assertSee('Create New Post');
    }

    /** @test */
    public function can_create_post(): void
    {
        $this->actingAs($this->user);

        Livewire::test(CreatePost::class)
            ->set('title', 'My Test Post')
            ->set('content', 'This is the content of my test post.')
            ->set('published', true)
            ->call('save')
            ->assertRedirect(route('posts.show', Post::first()));

        $this->assertDatabaseHas('posts', [
            'user_id' => $this->user->id,
            'title' => 'My Test Post',
            'content' => 'This is the content of my test post.',
        ]);

        $this->assertNotNull(Post::first()->published_at);
    }

    /** @test */
    public function validates_required_fields(): void
    {
        $this->actingAs($this->user);

        Livewire::test(CreatePost::class)
            ->set('title', '')
            ->set('content', '')
            ->call('save')
            ->assertHasErrors(['title' => 'required', 'content' => 'required']);
    }

    /** @test */
    public function validates_minimum_length(): void
    {
        $this->actingAs($this->user);

        Livewire::test(CreatePost::class)
            ->set('title', 'Hi')
            ->set('content', 'Short')
            ->call('save')
            ->assertHasErrors([
                'title' => 'min',
                'content' => 'min',
            ]);
    }

    /** @test */
    public function can_reset_form(): void
    {
        $this->actingAs($this->user);

        Livewire::test(CreatePost::class)
            ->set('title', 'My Title')
            ->set('content', 'My Content')
            ->call('resetForm')
            ->assertSet('title', '')
            ->assertSet('content', '')
            ->assertHasNoErrors();
    }

    /** @test */
    public function dispatches_event_on_save(): void
    {
        $this->actingAs($this->user);

        Livewire::test(CreatePost::class)
            ->set('title', 'Event Test Post')
            ->set('content', 'Testing event dispatching.')
            ->call('save')
            ->assertDispatched('post-created');
    }

    /** @test */
    public function listens_to_tag_selected_event(): void
    {
        $this->actingAs($this->user);

        Livewire::test(CreatePost::class)
            ->dispatch('tag-selected', tag: 'laravel')
            ->assertSet('tags', ['laravel']);
    }

    /** @test */
    public function transforms_title_on_update(): void
    {
        $this->actingAs($this->user);

        Livewire::test(CreatePost::class)
            ->set('title', 'my lowercase title')
            ->assertSet('title', 'My Lowercase Title');
    }

    /** @test */
    public function computes_word_count(): void
    {
        $this->actingAs($this->user);

        $component = Livewire::test(CreatePost::class)
            ->set('content', 'This is five words here');

        $this->assertEquals(5, $component->get('wordCount'));
    }

    /** @test */
    public function shows_flash_message_on_success(): void
    {
        $this->actingAs($this->user);

        Livewire::test(CreatePost::class)
            ->set('title', 'Flash Message Test')
            ->set('content', 'Testing flash messages work correctly.')
            ->call('save');

        $this->assertEquals('Post created successfully!', session('message'));
    }
}
```

## tests/Feature/Livewire/VoltTest.php

```php
<?php

namespace Tests\Feature\Livewire;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class VoltTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function counter_increments(): void
    {
        Volt::test('counter')
            ->assertSee('0')
            ->call('increment')
            ->assertSee('1')
            ->call('increment')
            ->assertSee('2');
    }

    /** @test */
    public function counter_can_reset(): void
    {
        Volt::test('counter')
            ->set('count', 10)
            ->call('reset')
            ->assertSet('count', 0);
    }

    /** @test */
    public function user_profile_validates(): void
    {
        $user = User::factory()->create();

        Volt::test('user-profile', ['user' => $user])
            ->set('name', '')
            ->set('email', 'invalid')
            ->call('save')
            ->assertHasErrors([
                'name' => 'required',
                'email' => 'email',
            ]);
    }

    /** @test */
    public function user_profile_updates(): void
    {
        $user = User::factory()->create();

        Volt::test('user-profile', ['user' => $user])
            ->set('name', 'New Name')
            ->set('email', 'new@example.com')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Name',
            'email' => 'new@example.com',
        ]);
    }
}
```

## tests/Feature/Livewire/FileUploadTest.php

```php
<?php

namespace Tests\Feature\Livewire;

use App\Livewire\UploadAvatar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class FileUploadTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_upload_avatar(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $this->actingAs($user);

        $file = UploadedFile::fake()->image('avatar.jpg', 200, 200);

        Livewire::test(UploadAvatar::class)
            ->set('photo', $file)
            ->call('save')
            ->assertHasNoErrors();

        Storage::disk('public')->assertExists('avatars/' . $file->hashName());

        $this->assertNotNull($user->fresh()->avatar);
    }

    /** @test */
    public function validates_file_type(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $this->actingAs($user);

        $file = UploadedFile::fake()->create('document.pdf', 100);

        Livewire::test(UploadAvatar::class)
            ->set('photo', $file)
            ->call('save')
            ->assertHasErrors(['photo' => 'image']);
    }

    /** @test */
    public function validates_file_size(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $this->actingAs($user);

        $file = UploadedFile::fake()->image('large.jpg')->size(3000); // 3MB

        Livewire::test(UploadAvatar::class)
            ->set('photo', $file)
            ->call('save')
            ->assertHasErrors(['photo' => 'max']);
    }

    /** @test */
    public function can_remove_avatar(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['avatar' => 'avatars/old.jpg']);
        Storage::disk('public')->put('avatars/old.jpg', 'content');

        $this->actingAs($user);

        Livewire::test(UploadAvatar::class)
            ->call('removeAvatar');

        Storage::disk('public')->assertMissing('avatars/old.jpg');
        $this->assertNull($user->fresh()->avatar);
    }
}
```

## tests/Feature/Livewire/DataTableTest.php

```php
<?php

namespace Tests\Feature\Livewire;

use App\Livewire\UserTable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DataTableTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_search_users(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin);

        User::factory()->create(['name' => 'John Doe']);
        User::factory()->create(['name' => 'Jane Smith']);

        Livewire::test(UserTable::class)
            ->set('search', 'John')
            ->assertSee('John Doe')
            ->assertDontSee('Jane Smith');
    }

    /** @test */
    public function can_sort_by_column(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin);

        User::factory()->create(['name' => 'Alice']);
        User::factory()->create(['name' => 'Bob']);

        Livewire::test(UserTable::class)
            ->call('sortBy', 'name')
            ->assertSet('sortField', 'name')
            ->assertSet('sortDirection', 'asc');
    }

    /** @test */
    public function can_filter_by_status(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin);

        User::factory()->create(['status' => 'active']);
        User::factory()->create(['status' => 'inactive']);

        Livewire::test(UserTable::class)
            ->set('status', 'active')
            ->assertSee('active')
            ->assertDontSee('inactive');
    }

    /** @test */
    public function can_paginate(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin);

        User::factory()->count(25)->create();

        Livewire::test(UserTable::class)
            ->set('perPage', 10)
            ->assertSee('1')
            ->assertSee('2')
            ->assertSee('3');
    }
}
```
