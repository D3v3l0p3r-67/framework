<?php

chdir(__DIR__ . '/../server');

require_once './core/ResponseFactory.php';

function assertSameValue(mixed $expected, mixed $actual, string $message): void
{
    if ($expected !== $actual) {
        throw new RuntimeException(
            $message . sprintf(' (expected %s, got %s)', var_export($expected, true), var_export($actual, true))
        );
    }
}

$factories = [
    'CreateBadRequest' => 400,
    'CreateUnauthorized' => 401,
    'CreateForbiden' => 403,
    'CreateNotFound' => 404,
    'CreateMethodNotAllowed' => 405,
    'CreateConflict' => 409,
    'CreateInternalServerError' => 500,
];

foreach ($factories as $factory => $expectedStatus) {
    $response = ResponseFactory::$factory(data: ['test' => true], toCache: true);
    $serialized = $response->jsonSerialize();

    assertSameValue($expectedStatus, $serialized['statusCode'], "$factory returned an unexpected status code");
    assertSameValue(['test' => true], $serialized['data'], "$factory returned unexpected data");
    assertSameValue('', $serialized['form'], "$factory placed the cache flag in the form field");
}

$responseWithoutMessages = ResponseFactory::CreateError(code: 500);
assertSameValue([], $responseWithoutMessages->jsonSerialize()['messages']->getArrayCopy(), 'Missing messages were not normalized');

ob_start();
ResponseFactory::CreateBadRequest()->SendAsJson();
ob_end_clean();
assertSameValue(400, http_response_code(), 'The response did not set its real HTTP status code');

echo "ResponseFactory tests passed.\n";
