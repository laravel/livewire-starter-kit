@props([
    'invitation',
    'action',
])

<div data-test="team-invitation-alert">
    <div class="flex gap-3 rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-900 dark:border-blue-900/50 dark:bg-blue-950/50 dark:text-blue-100">
        <flux:icon name="information-circle" class="mt-0.5 size-4 shrink-0 text-blue-600 dark:text-blue-400" />

        <div>
            {{ __(':action to join the ":team" team.', ['action' => $action, 'team' => $invitation['teamName']]) }}
        </div>
    </div>
</div>
