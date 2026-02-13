---
name: DataTableComponent
description: Livewire data table with search, sort, pagination
file-type: php
---

# Data Table Component

## app/Livewire/UserTable.php

```php
<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class UserTable extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $sortField = 'created_at';

    #[Url]
    public string $sortDirection = 'desc';

    #[Url]
    public int $perPage = 10;

    #[Url]
    public string $status = '';

    /**
     * Reset pagination when search changes.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Reset pagination when filters change.
     */
    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    /**
     * Sort by column.
     */
    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    /**
     * Get paginated users.
     */
    #[Computed]
    public function users()
    {
        return User::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                      ->orWhere('email', 'like', "%{$this->search}%");
                });
            })
            ->when($this->status, function ($query) {
                $query->where('status', $this->status);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    /**
     * Delete user.
     */
    public function deleteUser(int $id): void
    {
        $user = User::findOrFail($id);
        $this->authorize('delete', $user);

        $user->delete();

        session()->flash('message', 'User deleted successfully.');
    }

    /**
     * Export users.
     */
    public function export(): void
    {
        // Implementation for export
        $this->dispatch('export-started');
    }

    public function render()
    {
        return view('livewire.user-table');
    }
}
```

## resources/views/livewire/user-table.blade.php

```blade
<div class="bg-white rounded-lg shadow">
    {{-- Header --}}
    <div class="p-4 border-b flex justify-between items-center">
        <h2 class="text-lg font-semibold">Users</h2>

        <button wire:click="export" class="bg-green-500 text-white px-4 py-2 rounded text-sm">
            Export
        </button>
    </div>

    {{-- Filters --}}
    <div class="p-4 border-b bg-gray-50 flex gap-4 items-center">
        {{-- Search --}}
        <div class="flex-1">
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Search users..."
                class="w-full rounded border-gray-300"
            >
        </div>

        {{-- Status Filter --}}
        <select wire:model.live="status" class="rounded border-gray-300">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="pending">Pending</option>
        </select>

        {{-- Per Page --}}
        <select wire:model.live="perPage" class="rounded border-gray-300">
            <option value="10">10 per page</option>
            <option value="25">25 per page</option>
            <option value="50">50 per page</option>
        </select>
    </div>

    {{-- Flash Message --}}
    @if (session()->has('message'))
        <div class="p-4 bg-green-100 text-green-700">
            {{ session('message') }}
        </div>
    @endif

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left">
                        <button wire:click="sortBy('name')" class="flex items-center gap-1 font-semibold">
                            Name
                            @if ($sortField === 'name')
                                <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </button>
                    </th>
                    <th class="px-4 py-3 text-left">
                        <button wire:click="sortBy('email')" class="flex items-center gap-1 font-semibold">
                            Email
                            @if ($sortField === 'email')
                                <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </button>
                    </th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">
                        <button wire:click="sortBy('created_at')" class="flex items-center gap-1 font-semibold">
                            Created
                            @if ($sortField === 'created_at')
                                <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </button>
                    </th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($this->users as $user)
                    <tr wire:key="user-{{ $user->id }}" class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <img
                                    src="{{ $user->avatar_url }}"
                                    alt="{{ $user->name }}"
                                    class="w-8 h-8 rounded-full"
                                >
                                <span class="font-medium">{{ $user->name }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $user->email }}</td>
                        <td class="px-4 py-3">
                            <span @class([
                                'px-2 py-1 rounded text-xs font-medium',
                                'bg-green-100 text-green-800' => $user->status === 'active',
                                'bg-gray-100 text-gray-800' => $user->status === 'inactive',
                                'bg-yellow-100 text-yellow-800' => $user->status === 'pending',
                            ])>
                                {{ ucfirst($user->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ $user->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a
                                href="{{ route('users.edit', $user) }}"
                                class="text-blue-600 hover:underline mr-3"
                            >
                                Edit
                            </a>
                            <button
                                wire:click="deleteUser({{ $user->id }})"
                                wire:confirm="Are you sure you want to delete this user?"
                                class="text-red-600 hover:underline"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                            No users found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="p-4 border-t">
        {{ $this->users->links() }}
    </div>

    {{-- Loading Overlay --}}
    <div
        wire:loading.flex
        wire:target="search, sortBy, perPage, status, deleteUser"
        class="absolute inset-0 bg-white/50 items-center justify-center"
    >
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
    </div>
</div>
```

## Usage

```blade
<livewire:user-table />
```
