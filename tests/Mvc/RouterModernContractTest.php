<?php
namespace Merlin\Tests\Mvc;

require_once __DIR__ . '/../../vendor/autoload.php';

use Merlin\Mvc\Router;
use PHPUnit\Framework\TestCase;

class RouterModernContractTest extends TestCase
{
    public function testNamedWildcardIsCapturedWithoutSpecialName(): void
    {
        $router = new Router();
        $router->add('GET', '/{controller}/{action}/{args:*}', null);

        $result = $router->match('/user/view/foo/bar');

        $this->assertNotNull($result);
        $this->assertEquals('user', $result['vars']['controller']);
        $this->assertEquals('view', $result['vars']['action']);
        $this->assertEquals(['foo', 'bar'], $result['vars']['args']);

        $this->assertArrayNotHasKey('namespace', $result);
        $this->assertArrayNotHasKey('controller', $result);
        $this->assertArrayNotHasKey('action', $result);
        $this->assertArrayNotHasKey('params', $result);
    }

    public function testOptionalTypedSegmentMatchesWithAndWithoutValue(): void
    {
        $router = new Router();
        $router->add('GET', '/users/{id?:int}', null);

        $withoutId = $router->match('/users');
        $withId = $router->match('/users/42');

        $this->assertNotNull($withoutId);
        $this->assertArrayNotHasKey('id', $withoutId['vars']);

        $this->assertNotNull($withId);
        $this->assertSame(42, $withId['vars']['id']);
    }

    public function testOptionalTypedSegmentRejectsInvalidValue(): void
    {
        $router = new Router();
        $router->add('GET', '/users/{id?:int}', null);

        $result = $router->match('/users/abc');

        $this->assertNull($result);
    }
}
