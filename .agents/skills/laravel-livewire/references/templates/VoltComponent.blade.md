---
name: VoltComponent
description: Volt single-file components (functional and class-based)
file-type: blade
---

# Volt Components

## Functional API - Counter

```php
<?php // resources/views/livewire/counter.blade.php
use function Livewire\Volt\{state, computed, mount};

state(['count' => 0, 'step' => 1]);

$increment = fn() => $this->count += $this->step;
$decrement = fn() => $this->count -= $this->step;
$reset = fn() => $this->count = 0;

$doubleCount = computed(fn() => $this->count * 2);

$mount = function() {
    $this->count = session('count', 0);
};

$updated = function($property) {
    if ($property === 'count') {
        session(['count' => $this->count]);
    }
};
?>

<div class="p-4 bg-white rounded shadow">
    <h2 class="text-xl font-bold">Counter</h2>

    <p class="text-3xl my-4">{{ $count }}</p>
    <p class="text-gray-500">Double: {{ $this->doubleCount }}</p>

    <div class="flex gap-2 mt-4">
        <button wire:click="decrement" class="bg-red-500 text-white px-4 py-2 rounded">
            -{{ $step }}
        </button>
        <button wire:click="increment" class="bg-green-500 text-white px-4 py-2 rounded">
            +{{ $step }}
        </button>
        <button wire:click="reset" class="bg-gray-500 text-white px-4 py-2 rounded">
            Reset
        </button>
    </div>

    <div class="mt-4">
        <label class="text-sm">Step:</label>
        <input type="number" wire:model.live="step" class="w-20 border rounded px-2 py-1" min="1">
    </div>
</div>
```

## Class-Based Volt - User Profile

```php
<?php // resources/views/livewire/user-profile.blade.php
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new class extends Component {
    public User $user;

    #[Validate('required|min:2|max:100')]
    public string $name = '';

    #[Validate('required|email')]
    public string $email = '';

    #[Validate('nullable|url')]
    public ?string $website = null;

    public function mount(User $user): void
    {
        $this->user = $user;
        $this->fill($user->only(['name', 'email', 'website']));
    }

    #[Computed]
    public function postsCount(): int
    {
        return $this->user->posts()->count();
    }

    public function save(): void
    {
        $validated = $this->validate();

        $this->user->update($validated);

        session()->flash('message', 'Profile updated!');
    }

    public function with(): array
    {
        return [
            'recentPosts' => $this->user->posts()->latest()->take(5)->get(),
        ];
    }
};
?>

<div class="max-w-lg mx-auto p-6">
    <h1 class="text-2xl font-bold mb-6">Edit Profile</h1>

    @if (session()->has('message'))
        <div class="bg-green-100 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('message') }}
        </div>
    @endif

    <form wire:submit="save" class="space-y-4">
        <div>
            <label class="block text-sm font-medium">Name</label>
            <input type="text" wire:model.blur="name" class="mt-1 block w-full rounded border-gray-300">
            @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium">Email</label>
            <input type="email" wire:model.blur="email" class="mt-1 block w-full rounded border-gray-300">
            @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium">Website</label>
            <input type="url" wire:model.blur="website" class="mt-1 block w-full rounded border-gray-300">
            @error('website') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">
            Save Profile
        </button>
    </form>

    <div class="mt-8">
        <h2 class="text-lg font-semibold">Recent Posts ({{ $this->postsCount }})</h2>
        <ul class="mt-2 space-y-2">
            @foreach ($recentPosts as $post)
                <li wire:key="post-{{ $post->id }}" class="border-b pb-2">
                    <a href="{{ route('posts.show', $post) }}" class="text-blue-600 hover:underline">
                        {{ $post->title }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</div>
```

## @volt Inline Directive

```blade
{{-- resources/views/dashboard.blade.php --}}
<x-layouts.app>
    <h1 class="text-2xl font-bold mb-6">Dashboard</h1>

    <div class="grid grid-cols-3 gap-4">
        @volt('dashboard.users-count')
            <?php
            use function Livewire\Volt\{state};
            use App\Models\User;

            state(['count' => fn() => User::count()]);
            ?>

            <div class="bg-white p-4 rounded shadow">
                <h3 class="text-gray-500">Total Users</h3>
                <p class="text-3xl font-bold">{{ $count }}</p>
            </div>
        @endvolt

        @volt('dashboard.posts-count')
            <?php
            use App\Models\Post;
            state(['count' => fn() => Post::published()->count()]);
            ?>

            <div class="bg-white p-4 rounded shadow">
                <h3 class="text-gray-500">Published Posts</h3>
                <p class="text-3xl font-bold">{{ $count }}</p>
            </div>
        @endvolt

        @volt('dashboard.revenue')
            <?php
            use App\Models\Order;
            state(['amount' => fn() => Order::sum('total')]);
            ?>

            <div class="bg-white p-4 rounded shadow">
                <h3 class="text-gray-500">Total Revenue</h3>
                <p class="text-3xl font-bold">${{ number_format($amount, 2) }}</p>
            </div>
        @endvolt
    </div>
</x-layouts.app>
```

## Volt Routes

```php
// routes/web.php
use Livewire\Volt\Volt;

Volt::route('/', 'home');
Volt::route('/counter', 'counter');
Volt::route('/users/{user}', 'user-profile')
    ->middleware(['auth'])
    ->name('profile');
```
