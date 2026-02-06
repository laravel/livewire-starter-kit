---
name: file-uploads
description: Livewire file upload handling
when-to-use: Uploading files, images, multiple files, progress tracking
keywords: WithFileUploads, upload, temporary, preview, progress
---

# File Uploads

## Decision Tree

```
Upload type?
├── Single file → use WithFileUploads + $file
├── Multiple files → $files array + multiple
├── Image preview → $file->temporaryUrl()
├── Progress bar → wire:loading with target
└── Validation → #[Validate] with file rules
```

## Setup

| Requirement | Implementation |
|-------------|----------------|
| Trait | `use WithFileUploads` |
| Property | `public $photo` |
| Input | `<input type="file" wire:model="photo">` |

## Validation Rules

| Rule | Purpose |
|------|---------|
| `image` | Must be image |
| `mimes:jpg,png` | Specific types |
| `max:2048` | Max KB (2MB) |
| `dimensions:min_width=100` | Image dimensions |

## Temporary Files

| Method | Returns |
|--------|---------|
| `$file->temporaryUrl()` | Preview URL |
| `$file->getClientOriginalName()` | Original name |
| `$file->getSize()` | File size |
| `$file->getMimeType()` | MIME type |

## Storage

| Method | Result |
|--------|--------|
| `$file->store('path')` | Store in default disk |
| `$file->store('path', 'public')` | Store in public disk |
| `$file->storeAs('path', 'name')` | Custom filename |

## Multiple Files

| Setup | Usage |
|-------|-------|
| `public $photos = []` | Array property |
| `<input type="file" multiple>` | Multiple input |
| `@foreach($photos as $photo)` | Loop files |

## Progress Tracking

| Directive | Shows |
|-----------|-------|
| `wire:loading` | While uploading |
| `wire:target="photo"` | Specific field |
| JavaScript | `$wire.$upload()` with callbacks |

## Chunk Upload

| Config | Purpose |
|--------|---------|
| `livewire.temporary_file_upload.rules` | Default rules |
| Chunked | Large files automatic |

## Cleanup

| Method | Purpose |
|--------|---------|
| `$this->reset('photo')` | Clear upload |
| `$this->photo = null` | Remove file |
| Automatic | Temp files auto-cleanup |

## Best Practices

| DO | DON'T |
|----|-------|
| Validate file type | Accept any file |
| Use temporary preview | Store before validation |
| Set max size | Allow unlimited |
| Reset after save | Keep temp files |

→ **See template**: [FileUploadComponent.php.md](templates/FileUploadComponent.php.md)
