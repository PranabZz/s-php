<?php

namespace Sphp\Core;

class Request
{
    private array $headers = [];
    private string $method;
    private array $params = [];
    private array $data = []; // To store arbitrary request attributes

    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $this->parseHeaders();
        $this->parseParams();
    }

    private function parseHeaders()
    {
        if (function_exists('apache_request_headers')) {
            $this->headers = apache_request_headers();
        } else {
            foreach ($_SERVER as $name => $value) {
                if (substr($name, 0, 5) == 'HTTP_') {
                    $this->headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                }
            }
        }
    }

    private function parseParams()
    {
        // Always include GET parameters
        $this->params = $_GET;

        // Read raw input for POST, PUT, PATCH, DELETE
        $input = file_get_contents('php://input');
        $contentType = $this->getHeader('Content-Type');

        if ($this->method === 'POST') {
            // For POST, $_POST usually contains x-www-form-urlencoded or form-data
            // If it's a JSON POST, we'll handle it below
            $this->params = array_merge($this->params, $_POST);
        }

        if ($contentType && strpos($contentType, 'application/json') !== false) {
            $decodedInput = json_decode($input, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $this->params = array_merge($this->params, $decodedInput);
            }
        } elseif ($contentType && strpos($contentType, 'application/x-www-form-urlencoded') !== false) {
            // Parse application/x-www-form-urlencoded for PUT/PATCH/DELETE or non-multipart POST
            parse_str($input, $formData);
            $this->params = array_merge($this->params, $formData);
        }
    }

    /**
     * Get a specific header from the request.
     * @param string $name The name of the header.
     * @return string|null The header value, or null if not found.
     */
    public function getHeader(string $name): ?string
    {
        $name = str_replace(' ', '-', ucwords(strtolower(str_replace('-', ' ', $name))));
        return $this->headers[$name] ?? null;
    }

    /**
     * Get the HTTP method of the request.
     * @return string The HTTP method (e.g., 'GET', 'POST').
     */
    public function method(): string
    {
        return $this->method;
    }

    /**
     * Get a request parameter (from GET, POST, or JSON/form-urlencoded body).
     * @param string $key The key of the parameter.
     * @param mixed $default Default value if the parameter is not found.
     * @return mixed The parameter value.
     */
    public function param(string $key, $default = null)
    {
        return $this->params[$key] ?? $default;
    }

    /**
     * Set a request attribute.
     * @param string $key The key for the attribute.
     * @param mixed $value The value of the attribute.
     */
    public function setAttribute(string $key, $value): void
    {
        $this->data[$key] = $value;
    }

    /**
     * Get a request attribute.
     * @param string $key The key for the attribute.
     * @param mixed $default Default value if the attribute is not found.
     * @return mixed The attribute value.
     */
    public function getAttribute(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }
}