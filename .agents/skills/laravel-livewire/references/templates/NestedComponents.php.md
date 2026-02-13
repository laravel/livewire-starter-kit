---
name: NestedComponents
description: Parent-child component communication patterns
file-type: php
---

# Nested Components

## Parent Component - TodoList

```php
<?php // app/Livewire/TodoList.php

namespace App\Livewire;

use App\Models\Todo;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class TodoList extends Component
{
    public string $newTodo = '';

    /**
     * Get todos.
     */
    #[Computed]
    public function todos(): Collection
    {
        return Todo::where('user_id', auth()->id())
            ->orderBy('completed')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Add new todo.
     */
    public function addTodo(): void
    {
        $this->validate(['newTodo' => 'required|min:3']);

        Todo::create([
            'user_id' => auth()->id(),
            'title' => $this->newTodo,
            'completed' => false,
        ]);

        $this->reset('newTodo');
        unset($this->todos); // Clear computed cache
    }

    /**
     * Listen for todo completion from child.
     */
    #[On('todo-completed')]
    public function handleTodoCompleted(int $todoId): void
    {
        unset($this->todos); // Refresh list
    }

    /**
     * Listen for todo deletion from child.
     */
    #[On('todo-deleted')]
    public function handleTodoDeleted(int $todoId): void
    {
        unset($this->todos); // Refresh list
    }

    public function render()
    {
        return view('livewire.todo-list');
    }
}
```

## Child Component - TodoItem

```php
<?php // app/Livewire/TodoItem.php

namespace App\Livewire;

use App\Models\Todo;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Modelable;
use Livewire\Component;

class TodoItem extends Component
{
    public Todo $todo;

    public bool $editing = false;

    public string $editTitle = '';

    /**
     * Initialize from parent prop.
     */
    public function mount(Todo $todo): void
    {
        $this->todo = $todo;
        $this->editTitle = $todo->title;
    }

    /**
     * Toggle completion.
     */
    public function toggleComplete(): void
    {
        $this->todo->update(['completed' => !$this->todo->completed]);

        $this->dispatch('todo-completed', todoId: $this->todo->id);
    }

    /**
     * Start editing.
     */
    public function startEditing(): void
    {
        $this->editing = true;
        $this->editTitle = $this->todo->title;
    }

    /**
     * Save edit.
     */
    public function saveEdit(): void
    {
        $this->validate(['editTitle' => 'required|min:3']);

        $this->todo->update(['title' => $this->editTitle]);
        $this->editing = false;
    }

    /**
     * Cancel edit.
     */
    public function cancelEdit(): void
    {
        $this->editing = false;
        $this->editTitle = $this->todo->title;
    }

    /**
     * Delete todo.
     */
    public function delete(): void
    {
        $this->todo->delete();

        $this->dispatch('todo-deleted', todoId: $this->todo->id);
    }

    public function render()
    {
        return view('livewire.todo-item');
    }
}
```

## Parent View - todo-list.blade.php

```blade
<div class="max-w-md mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">Todo List</h1>

    {{-- Add Todo Form --}}
    <form wire:submit="addTodo" class="flex gap-2 mb-6">
        <input
            type="text"
            wire:model="newTodo"
            placeholder="Add a new todo..."
            class="flex-1 rounded border-gray-300"
        >
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">
            Add
        </button>
    </form>
    @error('newTodo')
        <span class="text-red-500 text-sm">{{ $message }}</span>
    @enderror

    {{-- Todo Items --}}
    <div class="space-y-2">
        @foreach ($this->todos as $todo)
            <livewire:todo-item
                :todo="$todo"
                :wire:key="'todo-'.$todo->id"
            />
        @endforeach
    </div>

    {{-- Empty State --}}
    @if ($this->todos->isEmpty())
        <p class="text-gray-500 text-center py-8">
            No todos yet. Add one above!
        </p>
    @endif
</div>
```

## Child View - todo-item.blade.php

```blade
<div @class([
    'flex items-center gap-3 p-3 bg-white rounded shadow',
    'opacity-50' => $todo->completed,
])>
    {{-- Checkbox --}}
    <input
        type="checkbox"
        wire:click="toggleComplete"
        @checked($todo->completed)
        class="rounded border-gray-300"
    >

    {{-- Title or Edit Form --}}
    @if ($editing)
        <form wire:submit="saveEdit" class="flex-1 flex gap-2">
            <input
                type="text"
                wire:model="editTitle"
                class="flex-1 rounded border-gray-300 text-sm"
                autofocus
            >
            <button type="submit" class="text-green-600 text-sm">Save</button>
            <button type="button" wire:click="cancelEdit" class="text-gray-600 text-sm">
                Cancel
            </button>
        </form>
    @else
        <span
            @class(['flex-1', 'line-through' => $todo->completed])
            wire:dblclick="startEditing"
        >
            {{ $todo->title }}
        </span>
    @endif

    {{-- Actions --}}
    @unless ($editing)
        <button
            wire:click="startEditing"
            class="text-blue-600 text-sm hover:underline"
        >
            Edit
        </button>
        <button
            wire:click="delete"
            wire:confirm="Delete this todo?"
            class="text-red-600 text-sm hover:underline"
        >
            Delete
        </button>
    @endunless
</div>
```

## Using $parent

```blade
{{-- Child can call parent methods directly --}}
<button wire:click="$parent.refreshList">
    Refresh
</button>

{{-- Access parent property --}}
<span>Filter: {{ $parent.filter }}</span>
```

## #[Reactive] Props

```php
<?php // Child with reactive props - auto-updates when parent changes
use Livewire\Attributes\Reactive;

class TodoCount extends Component
{
    #[Reactive]
    public array $todos;

    public function render()
    {
        return view('livewire.todo-count', [
            'count' => count($this->todos),
            'completed' => collect($this->todos)->where('completed', true)->count(),
        ]);
    }
}
```

```blade
{{-- In parent --}}
<livewire:todo-count :todos="$this->todos->toArray()" />
```

## #[Modelable] Two-Way Binding

```php
<?php // Child input component
use Livewire\Attributes\Modelable;

class TextInput extends Component
{
    #[Modelable]
    public string $value = '';

    public string $label = '';
    public string $placeholder = '';
}
```

```blade
{{-- Parent can wire:model directly to child --}}
<livewire:text-input
    wire:model="title"
    label="Title"
    placeholder="Enter title..."
/>
```
