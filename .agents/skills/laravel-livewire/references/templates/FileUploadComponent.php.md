---
name: FileUploadComponent
description: Livewire file upload with preview and progress
file-type: php
---

# File Upload Component

## app/Livewire/UploadAvatar.php

```php
<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class UploadAvatar extends Component
{
    use WithFileUploads;

    #[Validate('nullable|image|max:2048')] // 2MB max
    public $photo;

    public string $currentAvatar = '';

    public function mount(): void
    {
        $this->currentAvatar = auth()->user()->avatar_url ?? '';
    }

    /**
     * Save avatar.
     */
    public function save(): void
    {
        $this->validate();

        if (!$this->photo) {
            return;
        }

        // Delete old avatar
        if (auth()->user()->avatar) {
            Storage::disk('public')->delete(auth()->user()->avatar);
        }

        // Store new avatar
        $path = $this->photo->store('avatars', 'public');

        auth()->user()->update(['avatar' => $path]);

        $this->currentAvatar = Storage::disk('public')->url($path);
        $this->reset('photo');

        session()->flash('message', 'Avatar updated successfully!');
    }

    /**
     * Remove current avatar.
     */
    public function removeAvatar(): void
    {
        if (auth()->user()->avatar) {
            Storage::disk('public')->delete(auth()->user()->avatar);
            auth()->user()->update(['avatar' => null]);
            $this->currentAvatar = '';

            session()->flash('message', 'Avatar removed.');
        }
    }

    /**
     * Cancel upload.
     */
    public function cancelUpload(): void
    {
        $this->reset('photo');
    }

    public function render()
    {
        return view('livewire.upload-avatar');
    }
}
```

## resources/views/livewire/upload-avatar.blade.php

```blade
<div class="max-w-md">
    @if (session()->has('message'))
        <div class="bg-green-100 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('message') }}
        </div>
    @endif

    <div class="flex items-start gap-6">
        {{-- Current Avatar --}}
        <div class="flex-shrink-0">
            @if ($photo)
                <img
                    src="{{ $photo->temporaryUrl() }}"
                    alt="Preview"
                    class="w-24 h-24 rounded-full object-cover border-4 border-blue-500"
                >
            @elseif ($currentAvatar)
                <img
                    src="{{ $currentAvatar }}"
                    alt="Current avatar"
                    class="w-24 h-24 rounded-full object-cover"
                >
            @else
                <div class="w-24 h-24 rounded-full bg-gray-200 flex items-center justify-center">
                    <span class="text-gray-400 text-2xl">?</span>
                </div>
            @endif
        </div>

        {{-- Upload Form --}}
        <div class="flex-1">
            <form wire:submit="save" class="space-y-4">
                {{-- File Input --}}
                <div>
                    <label class="block text-sm font-medium mb-2">
                        Choose new avatar
                    </label>
                    <input
                        type="file"
                        wire:model="photo"
                        accept="image/*"
                        class="block w-full text-sm text-gray-500
                               file:mr-4 file:py-2 file:px-4
                               file:rounded file:border-0
                               file:text-sm file:font-semibold
                               file:bg-blue-50 file:text-blue-700
                               hover:file:bg-blue-100"
                    >
                    @error('photo')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Upload Progress --}}
                <div wire:loading wire:target="photo" class="w-full">
                    <div class="bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-500 h-2 rounded-full animate-pulse w-1/2"></div>
                    </div>
                    <p class="text-sm text-gray-500 mt-1">Uploading...</p>
                </div>

                {{-- Actions --}}
                <div class="flex gap-2">
                    @if ($photo)
                        <button
                            type="submit"
                            class="bg-blue-500 text-white px-4 py-2 rounded text-sm"
                            wire:loading.attr="disabled"
                            wire:target="save"
                        >
                            <span wire:loading.remove wire:target="save">Save Avatar</span>
                            <span wire:loading wire:target="save">Saving...</span>
                        </button>
                        <button
                            type="button"
                            wire:click="cancelUpload"
                            class="bg-gray-200 text-gray-700 px-4 py-2 rounded text-sm"
                        >
                            Cancel
                        </button>
                    @endif

                    @if ($currentAvatar && !$photo)
                        <button
                            type="button"
                            wire:click="removeAvatar"
                            wire:confirm="Remove your avatar?"
                            class="bg-red-500 text-white px-4 py-2 rounded text-sm"
                        >
                            Remove Avatar
                        </button>
                    @endif
                </div>
            </form>

            <p class="text-xs text-gray-500 mt-4">
                Max file size: 2MB. Formats: JPG, PNG, GIF
            </p>
        </div>
    </div>
</div>
```

## Multiple File Upload

```php
<?php // app/Livewire/UploadGallery.php

namespace App\Livewire;

use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class UploadGallery extends Component
{
    use WithFileUploads;

    #[Validate(['photos.*' => 'image|max:2048'])]
    public array $photos = [];

    public array $uploaded = [];

    public function updatedPhotos(): void
    {
        $this->validate();
    }

    public function removePhoto(int $index): void
    {
        array_splice($this->photos, $index, 1);
    }

    public function save(): void
    {
        foreach ($this->photos as $photo) {
            $path = $photo->store('gallery', 'public');
            $this->uploaded[] = $path;
        }

        $this->reset('photos');
        session()->flash('message', count($this->uploaded) . ' photos uploaded!');
    }

    public function render()
    {
        return view('livewire.upload-gallery');
    }
}
```

```blade
{{-- resources/views/livewire/upload-gallery.blade.php --}}
<div>
    <input type="file" wire:model="photos" multiple accept="image/*">

    {{-- Previews --}}
    <div class="grid grid-cols-4 gap-4 mt-4">
        @foreach ($photos as $index => $photo)
            <div wire:key="photo-{{ $index }}" class="relative">
                <img src="{{ $photo->temporaryUrl() }}" class="w-full h-24 object-cover rounded">
                <button
                    wire:click="removePhoto({{ $index }})"
                    class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6"
                >
                    ×
                </button>
            </div>
        @endforeach
    </div>

    @if (count($photos) > 0)
        <button wire:click="save" class="mt-4 bg-blue-500 text-white px-4 py-2 rounded">
            Upload {{ count($photos) }} Photos
        </button>
    @endif
</div>
```
