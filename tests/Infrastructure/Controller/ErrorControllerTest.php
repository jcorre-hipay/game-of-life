<?php

declare(strict_types=1);

namespace GameOfLife\Tests\Infrastructure\Controller;

use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ErrorControllerTest extends WebTestCase
{
    /**
     * @test
     */
    public function itDisplaysThe404ErrorPage()
    {
        $client = static::createClient();

        $client->request('GET', '/nothing-to-see-here');

        $response = $client->getResponse();

        Assert::assertSame(404, $response->getStatusCode());
        Assert::assertStringContainsString('<h1>404</h1>', $response->getContent());
    }

    /**
     * @test
     */
    public function itDisplaysThe405ErrorPage()
    {
        $client = static::createClient();

        $client->request('DELETE', '/colony');

        $response = $client->getResponse();

        Assert::assertSame(405, $response->getStatusCode());
        Assert::assertStringContainsString('<h1>405</h1>', $response->getContent());
    }
}
