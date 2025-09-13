<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('returns_a_successful_response', function (): void {
    $response = $this->get(route('home'));

    $response->assertStatus(200);
});
