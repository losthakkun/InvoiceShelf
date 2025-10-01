<?php

use App\Models\Customer;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

beforeEach(function () {
    Artisan::call('db:seed', ['--class' => 'DatabaseSeeder', '--force' => true]);
    Artisan::call('db:seed', ['--class' => 'DemoSeeder', '--force' => true]);

    $customer = Customer::factory()->create();

    Sanctum::actingAs(
        $customer,
        ['*'],
        'customer'
    );
});

// Successful Response 200
test('expected successful response 200', function () {
    $customer = Auth::guard('customer')->user();
    getJson("api/v1/{$customer->company->slug}/customer/dashboard")->assertOk(); // Equivalent to assertStatus(200)
});

// Error Response 302
test('expected error response 302', function () {
    $response = $this->getJson('/admin/dashboard');
    $response->assertRedirect(); // Assert 302 Found
});

// Error Response 401
test('expected error response 401', function () {
    //$customer = Auth::guard('customer')->user();
    $response = getJson("api/v1/customers?display_name=&contact_name=&phone=&orderByField=created_at&orderBy=desc&page=1");
    $response->assertUnauthorized(); // Assert 401 Unauthorized
});

// Error Response 404
test('expected error response 404', function () {
    $response = postJson("api/v1/customer/login", [
        'email' => 'customer@example.com',
        'password' => 'password',
    ]);
    $response->assertNotFound(); // Assert 404 Not Found
});