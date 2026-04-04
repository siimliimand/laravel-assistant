<?php

use App\DTOs\ApiResponseData;

test('api response data can be instantiated with constructor', function () {
    $data = new ApiResponseData(
        success: true,
        data: ['id' => 123],
        message: 'Success',
        meta: ['conversation_id' => 123],
        statusCode: 201,
    );

    expect($data->success)->toBeTrue();
    expect($data->data)->toBe(['id' => 123]);
    expect($data->message)->toBe('Success');
    expect($data->meta)->toBe(['conversation_id' => 123]);
    expect($data->statusCode)->toBe(201);
});

test('api response data success factory method', function () {
    $data = ApiResponseData::success(
        data: ['id' => 123],
        message: 'Created',
        meta: ['key' => 'value'],
        statusCode: 201,
    );

    expect($data->success)->toBeTrue();
    expect($data->data)->toBe(['id' => 123]);
    expect($data->message)->toBe('Created');
    expect($data->meta)->toBe(['key' => 'value']);
    expect($data->statusCode)->toBe(201);
});

test('api response data error factory method', function () {
    $data = ApiResponseData::error(
        message: 'Something went wrong',
        statusCode: 500,
        data: ['error_code' => 'INTERNAL_ERROR'],
    );

    expect($data->success)->toBeFalse();
    expect($data->message)->toBe('Something went wrong');
    expect($data->statusCode)->toBe(500);
    expect($data->data)->toBe(['error_code' => 'INTERNAL_ERROR']);
});

test('api response data to array includes all fields', function () {
    $data = new ApiResponseData(
        success: true,
        data: ['id' => 123],
        message: 'Success',
        meta: ['key' => 'value'],
    );

    $array = $data->toArray();

    expect($array)->toBe([
        'success' => true,
        'data' => ['id' => 123],
        'message' => 'Success',
        'meta' => ['key' => 'value'],
    ]);
});

test('api response data to array filters null values', function () {
    $data = new ApiResponseData(
        success: true,
        data: ['id' => 123],
    );

    $array = $data->toArray();

    expect($array)->toBe([
        'success' => true,
        'data' => ['id' => 123],
        'meta' => [],
    ]);
    expect($array)->not->toHaveKey('message');
});

test('api response data defaults', function () {
    $data = new ApiResponseData(success: true);

    expect($data->success)->toBeTrue();
    expect($data->data)->toBeNull();
    expect($data->message)->toBeNull();
    expect($data->meta)->toBe([]);
    expect($data->statusCode)->toBe(200);
});

test('api response data is immutable (readonly)', function () {
    $data = ApiResponseData::success(['id' => 1]);

    expect($data)->toHaveProperty('success');
    expect($data)->toHaveProperty('data');
    expect($data)->toHaveProperty('message');
    expect($data)->toHaveProperty('meta');
    expect($data)->toHaveProperty('statusCode');
});

test('api response data is final class', function () {
    $reflection = new ReflectionClass(ApiResponseData::class);

    expect($reflection->isFinal())->toBeTrue();
    expect($reflection->isReadonly())->toBeTrue();
});
