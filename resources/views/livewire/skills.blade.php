<?php

use Livewire\Volt\Component;
use App\Models\Skill;
use App\Livewire\Forms\ContactForm;
use Livewire\Attributes\Validate;
use Livewire\Attributes\Url;
use Illuminate\Support\Collection;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    #[Validate('required|string|max:100')]
    public string $skillGroupName = '';

    #[Validate('required|string|max:100|unique:skills,skill')]
    public string $skillName = '';

    #[Validate('required|string|max:100')]
    public string $skillDescription = '';

    #[Validate('required|numeric|digits_between:1,5')]
    public int $skillLevel = 0;

    #[Url]
    public $sortBy = '';

    #[Url]
    public $sortDirection = 'desc';

    public function sort($column)
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function with(): array
    {
        return [
            'skills' => $this->skills,
        ];
    }

    #[\Livewire\Attributes\Computed]
    public function skills(): \Illuminate\Pagination\LengthAwarePaginator
    {
        return Skill::query()->tap(fn($query) => $this->sortBy ? $query->orderBy($this->sortBy, $this->sortDirection) : $query)->paginate(5);
    }

    public function addSkill()
    {
        $this->validate();

        Skill::create([
            'group' => $this->skillGroupName,
            'skill' => $this->skillName,
            'description' => $this->skillDescription,
            'level' => $this->skillLevel,
        ]);

        $this->modal('add-skill')->close();

        $this->close();
    }

    public function close()
    {
        $this->reset();
        $this->resetValidation();
    }
};
?>
<flux:container>
    <flux:row>
        <flux:heading size="lg">Skills</flux:heading>
        <flux:spacer />
        <flux:modal.trigger name="add-skill">
            <flux:button>Add Skill Group</flux:button>
        </flux:modal.trigger>
    </flux:row>
    <flux:separator />
    <flux:table :paginate="$skills">
        <flux:table.header>
            <flux:table.header.row>
                <flux:table.header.cell sortable :sorted="$sortBy === 'group'" :direction="$sortDirection"
                    wire:click="sort('group')">Skill Group</flux:table.header.cell>
                <flux:table.header.cell sortable :sorted="$sortBy === 'skill'" :direction="$sortDirection"
                    wire:click="sort('skill')">Skill</flux:table.header.cell>
                <flux:table.header.cell sortable :sorted="$sortBy === 'description'" :direction="$sortDirection"
                    wire:click="sort('description')">Description</flux:table.header.cell>
                <flux:table.header.cell sortable :sorted="$sortBy === 'level'" :direction="$sortDirection"
                    wire:click="sort('level')">Level</flux:table.header.cell>
            </flux:table.header.row>
        </flux:table.header>
        <flux:table.body>
            @foreach ($skills as $skill)
                <flux:table.row>
                    <flux:table.body.cell>{{ $skill->group }}</flux:table.body.cell>
                    <flux:table.body.cell>{{ $skill->skill }}</flux:table.body.cell>
                    <flux:table.body.cell>{{ $skill->description }}</flux:table.body.cell>
                    <flux:table.body.cell>{{ $skill->level }}</flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.body>
    </flux:table>
    <flux:modal wire:close="close" name="add-skill" class="md:w-128">
        <form wire:submit.prevent="addSkill">
            <flux:heading size="lg">Add New Skill Group</flux:heading>
            <flux:subheading>Shown like Front End Development: Javascript</flux:subheading>
            <flux:separator class="mb-6" />
            <flux:grid>
                <flux:select placeholder="Select a skill group" wire:model="skillGroupName">
                    @foreach (\App\Enums\SkillGroup::cases() as $value)
                        <flux:select.option value="{{ $value }}">{{ $value }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:input wire:model="skillName" label="Skill Name" placeholder="Laravel" />
                <flux:input wire:model="skillDescription" label="Skill Description"
                    placeholder="Laravel is a PHP web framework" />
                <flux:input wire:model="skillLevel" label="Skill Level" placeholder="0-9" />
                <flux:row no-width>
                    <flux:spacer />
                    <flux:button type="submit" variant="primary">Save changes</flux:button>
                </flux:row>
            </flux:grid>
        </form>
    </flux:modal>
</flux:container>
