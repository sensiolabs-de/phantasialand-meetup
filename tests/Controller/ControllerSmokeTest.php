<?php declare(strict_types = 1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ControllerSmokeTest extends WebTestCase
{
    /**
     * @dataProvider providePathsForSmokeTest
     */
    public function test_page_is_accessible(string $path)
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $path);

        $this->assertEquals(200, $client->getResponse()->getStatusCode(), $crawler->filter('title')->text());
    }

    public function providePathsForSmokeTest()
    {
        yield 'Homepage (route: home)' => ['/'];
        yield 'Request group listing (route: group_request)' => ['/group/request-listing'];
    }
}
