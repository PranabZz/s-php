<?php

namespace Tests\Integration;

use Tests\TestCase;
use Sphp\Core\Request;
use Sphp\Core\Response; // Required for static access
use App\Middleware\Pat; // Required for testing the middleware logic

class PatMiddlewareIntegrationTest extends TestCase
{
    private string $originalApiRouterContent;
    private string $tempApiRouterPath = __DIR__ . '/../../app/router/api.php';

    protected function setUp(): void
    {
        parent::setUp();

        // Store original content of api.php
        $this->originalApiRouterContent = file_get_contents($this->tempApiRouterPath);

        // Modify api.php to use Pat::class for /api/welcome
        $modifiedContent = '<?php

use App\Controllers\Api\LoginController;
use App\Controllers\HomeController;
use App\Middleware\Api;
use App\Middleware\Pat;
use Sphp\Core\Router;

$api = new Router();

$api->get("/api/health", HomeController::class, "health");
$api->post("/api/login", LoginController::class, "login");
$api->get("/api/protected", HomeController::class, "welcome", Pat::class); // Use Pat for a new protected route

$api->dispatch();
';
        file_put_contents($this->tempApiRouterPath, $modifiedContent);
    }

    protected function tearDown(): void
    {
        // Restore original content of api.php
        file_put_contents($this->tempApiRouterPath, $this->originalApiRouterContent);
        parent::tearDown();
    }

    /**
     * Test that an unauthorized request to a PAT-protected route returns 401.
     */
    public function testUnauthorizedAccessReturns401(): void
    {
        // Simulate a request without an Authorization header
        // We'll use file_get_contents with a stream context to make a real HTTP request
        // to our development server (assuming it's running).
        // This is an integration test and requires the server to be running.
        // For a more isolated test, one would mock the Request/Response and Router.

        // Define the URL of the protected endpoint
        $url = 'http://localhost:8000/api/protected'; // Assuming the app runs on localhost:8000

        // Create a stream context without an Authorization header
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'ignore_errors' => true, // Get the actual response code, not just throw an exception
            ],
        ]);

        // Make the HTTP request
        $response = file_get_contents($url, false, $context);

        // Get HTTP headers
        $http_response_header = $http_response_header ?? [];
        $status_line = $http_response_header[0] ?? '';
        preg_match('{HTTP\/\S*\s(\d{3})}', $status_line, $match);
        $statusCode = (int)$match[1];

        // Decode the JSON response body
        $responseData = json_decode($response, true);

        // Assert the status code
        $this->assertEquals(401, $statusCode, 'Expected HTTP 401 status code for unauthorized access.');

        // Assert the error message
        $this->assertArrayHasKey('message', $responseData, 'Response should contain a message key.');
        $this->assertStringContainsString('Unauthorized', $responseData['message'], 'Response message should indicate unauthorized access.');
    }

    /**
     * Test that a request with an invalid token to a PAT-protected route returns 401.
     */
    public function testInvalidTokenAccessReturns401(): void
    {
        // Define the URL of the protected endpoint
        $url = 'http://localhost:8000/api/protected'; // Assuming the app runs on localhost:8000
        $invalidToken = 'invalid.token.string.here';

        // Create a stream context with an invalid Authorization header
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => "Authorization: Bearer " . $invalidToken,
                'ignore_errors' => true,
            ],
        ]);

        // Make the HTTP request
        $response = file_get_contents($url, false, $context);

        // Get HTTP headers
        $http_response_header = $http_response_header ?? [];
        $status_line = $http_response_header[0] ?? '';
        preg_match('{HTTP\/\S*\s(\d{3})}', $status_line, $match);
        $statusCode = (int)$match[1];

        // Decode the JSON response body
        $responseData = json_decode($response, true);

        // Assert the status code
        $this->assertEquals(401, $statusCode, 'Expected HTTP 401 status code for invalid token.');

        // Assert the error message
        $this->assertArrayHasKey('message', $responseData, 'Response should contain a message key.');
        $this->assertStringContainsString('Invalid token', $responseData['message'], 'Response message should indicate invalid token.');
    }

    // A test for authorized access would involve:
    // 1. Creating a user and a valid PAT using the LoginController or directly inserting into DB.
    // 2. Making a request with the valid PAT.
    // 3. Asserting a 200 OK status code.
    // This is more involved and might be a separate test suite if needed.
}
