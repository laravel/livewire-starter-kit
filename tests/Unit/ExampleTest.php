<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('that_true_is_true', function (): void {
    $this->assertTrue(true);
});
