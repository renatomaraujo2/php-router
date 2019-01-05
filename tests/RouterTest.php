<?php

namespace Buki\Tests;

use Buki\Router;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    protected $router;

    protected $client;

    protected function setUp()
    {
        $this->router = new Router();

        $this->client = new Client();

        // Clear SCRIPT_NAME because bramus/router tries to guess the subfolder the script is run in
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        // Default request method to GET
        $_SERVER['REQUEST_METHOD'] = 'GET';

        // Default SERVER_PROTOCOL method to HTTP/1.1
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
    }

    protected function tearDown()
    {
        // nothing
    }

    public function testGetIndexRoute()
    {
        $request = $this->client->createRequest('GET', 'http://localhost:5000/');
        $response = $this->client->send($request);

        $this->assertSame('Hello World!', (string) $response->getBody());
    }

    /**
     * @expectedException GuzzleHttp\Exception\ClientException
     */
    public function testGetNotFoundRoute()
    {
        $request = $this->client->createRequest('GET', 'http://localhost:5000/not/found');
        $response = $this->client->send($request);
    }

    public function testInit()
    {
        $this->assertInstanceOf('\Buki\Router', new Router());
    }
}
