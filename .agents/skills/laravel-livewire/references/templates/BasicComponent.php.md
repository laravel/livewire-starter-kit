---
name: BasicComponent
description: Complete Livewire class component with all features
file-type: php
---

# Basic Livewire Component

## app/Livewire/CreatePost.php

```php
<?php

namespace App\Livewire;

use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

class CreatePost extends Component
{
    #[Locked]
    public int $userId;

    #[Validate('required|min:5|max:255')]
    public string $title = '';

    #[Validate('required|min:10')]
    public string $content = '';

    #[Validate('nullable|array')]
    public array $tags = [];

    public bool $published = false;

    /**
     * Initialize component.
     */
    public function mount(): void
    {
        $this->userId = Auth::id();
    }

    /**
     * Computed property - cached per request.
     */
    #[Computed]
    public function wordCount(): int
    {
        return str_word_count($this->content);
    }

    /**
     * Computed with persistence across requests.
     */
    #[Computed(persist: true)]
    public function userPosts(): int
    {
        return Post::where('user_id', $this->userId)->count();
    }

    /**
     * Property updated hook.
     */
    public function updatedTitle(string $value): void
    {
        $this->title = ucwords($value);
    }

    /**
     * Save post action.
     */
    public function save(): void
    {
        $validated = $this->validate();

        $post = Post::create([
            'user_id' => $this->userId,
            'title' => $validated['title'],
            'content' => $validated['content'],
            'tags' => $validated['tags'],
            'published_at' => $this->published ? now() : null,
        ]);

        $this->dispatch('post-created', postId: $post->id);

        session()->flash('message', 'Post created successfully!');

        $this->redirect(route('posts.show', $post));
    }

    /**
     * Reset form.
     */
    public function resetForm(): void
    {
        $this->reset(['title', 'content', 'tags', 'published']);
        $this->resetValidation();
    }

    /**
     * Listen for external events.
     */
    #[On('tag-selected')]
    public function addTag(string $tag): void
    {
        if (!in_array($tag, $this->tags)) {
            $this->tags[] = $tag;
        }
    }

    public function render()
    {
        return view('livewire.create-post');
    }
}
```

## resources/views/livewire/create-post.blade.php

```blade
<div class="max-w-2xl mx-auto p-6">
    <h1 class="text-2xl font-bold mb-6">Create New Post</h1>

    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('message') }}
        </div>
    @endif

    <form wire:submit="save" class="space-y-4">
        {{-- Title --}}
        <div>
            <label for="title" class="block text-sm font-medium">Title</label>
            <input
                type="text"
                id="title"
                wire:model.blur="title"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                placeholder="Enter post title"
            >
            @error('title')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        {{-- Content --}}
        <div>
            <label for="content" class="block text-sm font-medium">Content</label>
            <textarea
                id="content"
                wire:model.live.debounce.500ms="content"
                rows="6"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                placeholder="Write your post content..."
            ></textarea>
            @error('content')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
            <p class="text-sm text-gray-500 mt-1">
                Word count: {{ $this->wordCount }}
            </p>
        </div>

        {{-- Tags --}}
        <div>
            <label class="block text-sm font-medium">Tags</label>
            <div class="flex flex-wrap gap-2 mt-2">
                @foreach ($tags as $index => $tag)
                    <span
                        wire:key="tag-{{ $index }}"
                        class="bg-blue-100 text-blue-800 px-2 py-1 rounded"
                    >
                        {{ $tag }}
                        <button
                            type="button"
                            wire:click="$set('tags', array_values(array_diff($tags, ['{{ $tag }}'])))"
                            class="ml-1 text-blue-600 hover:text-blue-800"
                        >&times;</button>
                    </span>
                @endforeach
            </div>
        </div>

        {{-- Published --}}
        <div class="flex items-center">
            <input
                type="checkbox"
                id="published"
                wire:model="published"
                class="rounded border-gray-300"
            >
            <label for="published" class="ml-2 text-sm">
                Publish immediately
            </label>
        </div>

        {{-- Actions --}}
        <div class="flex gap-4">
            <button
                type="submit"
                class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 disabled:opacity-50"
                wire:loading.attr="disabled"
                wire:target="save"
            >
                <span wire:loading.remove wire:target="save">Save Post</span>
                <span wire:loading wire:target="save">Saving...</span>
            </button>

            <button
                type="button"
                wire:click="resetForm"
                class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300"
            >
                Reset
            </button>
        </div>
    </form>

    <p class="text-sm text-gray-500 mt-4">
        You have {{ $this->userPosts }} posts
    </p>
</div>
```

## Usage

```blade
{{-- In any Blade view --}}
<livewire:create-post />

{{-- Or as full-page route --}}
// routes/web.php
Route::livewire('/posts/create', CreatePost::class)
    ->middleware(['auth'])
    ->name('posts.create');
```
