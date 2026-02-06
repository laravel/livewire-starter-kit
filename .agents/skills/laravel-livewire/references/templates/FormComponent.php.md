---
name: FormComponent
description: Livewire form with Form Object and validation
file-type: php
---

# Form Component with Form Object

## app/Livewire/Forms/PostForm.php

```php
<?php

namespace App\Livewire\Forms;

use App\Models\Post;
use Livewire\Attributes\Validate;
use Livewire\Form;

class PostForm extends Form
{
    public ?Post $post = null;

    #[Validate('required|min:5|max:255')]
    public string $title = '';

    #[Validate('required|min:10')]
    public string $content = '';

    #[Validate('nullable|exists:categories,id')]
    public ?int $category_id = null;

    #[Validate('nullable|array')]
    public array $tags = [];

    #[Validate('boolean')]
    public bool $published = false;

    /**
     * Set form from existing post.
     */
    public function setPost(Post $post): void
    {
        $this->post = $post;
        $this->fill($post->only([
            'title',
            'content',
            'category_id',
            'tags',
        ]));
        $this->published = $post->published_at !== null;
    }

    /**
     * Create new post.
     */
    public function store(): Post
    {
        $validated = $this->validate();

        return auth()->user()->posts()->create([
            ...$validated,
            'published_at' => $this->published ? now() : null,
        ]);
    }

    /**
     * Update existing post.
     */
    public function update(): Post
    {
        $validated = $this->validate();

        $this->post->update([
            ...$validated,
            'published_at' => $this->published ? now() : null,
        ]);

        return $this->post->fresh();
    }

    /**
     * Hook: Transform title on update.
     */
    public function updatedTitle(string $value): void
    {
        $this->title = ucwords(strtolower($value));
    }
}
```

## app/Livewire/EditPost.php

```php
<?php

namespace App\Livewire;

use App\Livewire\Forms\PostForm;
use App\Models\Category;
use App\Models\Post;
use Livewire\Attributes\Computed;
use Livewire\Component;

class EditPost extends Component
{
    public PostForm $form;

    /**
     * Initialize with post.
     */
    public function mount(Post $post): void
    {
        $this->authorize('update', $post);
        $this->form->setPost($post);
    }

    /**
     * Available categories.
     */
    #[Computed]
    public function categories()
    {
        return Category::orderBy('name')->get();
    }

    /**
     * Save changes.
     */
    public function save(): void
    {
        $this->authorize('update', $this->form->post);

        $post = $this->form->update();

        session()->flash('message', 'Post updated successfully!');

        $this->redirect(route('posts.show', $post));
    }

    /**
     * Delete post.
     */
    public function delete(): void
    {
        $this->authorize('delete', $this->form->post);

        $this->form->post->delete();

        session()->flash('message', 'Post deleted.');

        $this->redirect(route('posts.index'));
    }

    public function render()
    {
        return view('livewire.edit-post');
    }
}
```

## resources/views/livewire/edit-post.blade.php

```blade
<div class="max-w-2xl mx-auto p-6">
    <h1 class="text-2xl font-bold mb-6">Edit Post</h1>

    @if (session()->has('message'))
        <div class="bg-green-100 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('message') }}
        </div>
    @endif

    <form wire:submit="save" class="space-y-4">
        {{-- Title with real-time validation --}}
        <div>
            <label for="title" class="block text-sm font-medium">Title</label>
            <input
                type="text"
                id="title"
                wire:model.blur="form.title"
                class="mt-1 block w-full rounded-md border-gray-300"
            >
            @error('form.title')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        {{-- Content --}}
        <div>
            <label for="content" class="block text-sm font-medium">Content</label>
            <textarea
                id="content"
                wire:model.blur="form.content"
                rows="8"
                class="mt-1 block w-full rounded-md border-gray-300"
            ></textarea>
            @error('form.content')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        {{-- Category --}}
        <div>
            <label for="category" class="block text-sm font-medium">Category</label>
            <select
                id="category"
                wire:model="form.category_id"
                class="mt-1 block w-full rounded-md border-gray-300"
            >
                <option value="">Select category...</option>
                @foreach ($this->categories as $category)
                    <option value="{{ $category->id }}">
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            @error('form.category_id')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        {{-- Published --}}
        <div class="flex items-center">
            <input
                type="checkbox"
                id="published"
                wire:model="form.published"
                class="rounded border-gray-300"
            >
            <label for="published" class="ml-2 text-sm">Published</label>
        </div>

        {{-- Actions --}}
        <div class="flex justify-between">
            <div class="flex gap-4">
                <button
                    type="submit"
                    class="bg-blue-500 text-white px-4 py-2 rounded"
                    wire:loading.attr="disabled"
                >
                    <span wire:loading.remove wire:target="save">Save Changes</span>
                    <span wire:loading wire:target="save">Saving...</span>
                </button>

                <a
                    href="{{ route('posts.index') }}"
                    class="bg-gray-200 px-4 py-2 rounded"
                >
                    Cancel
                </a>
            </div>

            <button
                type="button"
                wire:click="delete"
                wire:confirm="Are you sure you want to delete this post?"
                class="bg-red-500 text-white px-4 py-2 rounded"
            >
                Delete
            </button>
        </div>
    </form>
</div>
```

## routes/web.php

```php
use App\Livewire\EditPost;

Route::livewire('/posts/{post}/edit', EditPost::class)
    ->middleware(['auth'])
    ->name('posts.edit');
```
