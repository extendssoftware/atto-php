<?php
declare(strict_types=1);

namespace ExtendsSoftware\AttoPHP;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Throwable;
use function xdebug_get_headers;

/**
 * Test of class AttoPHP.
 *
 * @package ExtendsSoftware\AttoPHP
 * @author  Vincent van Dijk <vincent@extends.nl>
 * @version 0.2.0
 * @see     https://github.com/extendssoftware/atto-php
 */
class AttoPHPTest extends TestCase
{
    /**
     * Test get/set start callback.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::start()
     */
    public function testStartCallback(): void
    {
        $closure = static function () {
        };

        $atto = new AttoPHP();
        $atto->start($closure);

        $this->assertSame($closure, $atto->start());
    }

    /**
     * Test get/set before callback.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::before()
     */
    public function testBeforeCallback(): void
    {
        $closure = static function () {
        };

        $atto = new AttoPHP();
        $atto->before($closure);

        $this->assertSame($closure, $atto->before());
    }

    /**
     * Test get/set after callback.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::after()
     */
    public function testAfterCallback(): void
    {
        $closure = static function () {
        };

        $atto = new AttoPHP();
        $atto->after($closure);

        $this->assertSame($closure, $atto->after());
    }

    /**
     * Test get/set finish callback.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::finish()
     */
    public function testFinishCallback(): void
    {
        $closure = static function () {
        };

        $atto = new AttoPHP();
        $atto->finish($closure);

        $this->assertSame($closure, $atto->finish());
    }

    /**
     * Test get/set error callback.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::error()
     */
    public function testErrorCallback(): void
    {
        $closure = static function () {
        };

        $atto = new AttoPHP();
        $atto->error($closure);

        $this->assertSame($closure, $atto->error());
    }

    /**
     * Test get/set config.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::config()
     */
    public function testConfig(): void
    {
        $atto = new AttoPHP();
        $this->assertNull($atto->config());

        $atto->config(__DIR__ . '/config/*.config.php');
        $this->assertSame(__DIR__ . '/config/*.config.php', $atto->config());
    }

    /**
     * Test get/set translation.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::translation()
     */
    public function testTranslation(): void
    {
        $atto = new AttoPHP();
        $this->assertNull($atto->translation());

        $atto->translation(__DIR__ . '/translations/*.php');
        $this->assertSame(__DIR__ . '/translations/*.php', $atto->translation());
    }

    /**
     * Test get/set root.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::root()
     */
    public function testRoot(): void
    {
        $atto = new AttoPHP();
        $this->assertNull($atto->root());

        $atto->root(__DIR__ . '/render');
        $this->assertSame(__DIR__ . '/render', $atto->root());
    }

    /**
     * Test get/set view.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::view()
     */
    public function testView(): void
    {
        $atto = new AttoPHP();
        $this->assertNull($atto->view());

        $atto->view(__DIR__ . '/view.phtml');
        $this->assertSame(__DIR__ . '/view.phtml', $atto->view());
    }

    /**
     * Test get/set layout.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::layout()
     */
    public function testLayout(): void
    {
        $atto = new AttoPHP();
        $this->assertNull($atto->layout());

        $atto->layout(__DIR__ . '/layout.phtml');
        $this->assertSame(__DIR__ . '/layout.phtml', $atto->layout());
    }

    /**
     * Test get/set locale.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::locale()
     */
    public function testLocale(): void
    {
        $atto = new AttoPHP();
        $this->assertNull($atto->locale());

        $atto->locale('nl-nl');
        $this->assertSame('nl-nl', $atto->locale());
    }

    /**
     * Test get/set data.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::data()
     */
    public function testData(): void
    {
        $atto = new AttoPHP();
        $atto
            ->data('layout.title', 'New website!')
            ->data('layout.description', 'Fancy description.');

        $this->assertSame('New website!', $atto->data('layout.title'));
        $this->assertNull($atto->data('blog.title'));
        $this->assertNull($atto->data('layout.title.first'));
        $this->assertSame([
            'layout' => [
                'title' => 'New website!',
                'description' => 'Fancy description.',
            ],
        ], $atto->data());
    }

    /**
     * Test get/set data with invalid path notation.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::data()
     */
    public function testDataInvalidPath(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Path ".path.to.set" is not a valid dot notation. Please fix the notation. The ' .
            'colon (:), dot (.) and slash (/) characters can be used as separator. The can be used interchangeably. ' .
            'The characters between the separator can only consist of a-z and 0-9, case insensitive.');

        $atto = new AttoPHP();
        $atto->data('.path.to.set');
    }

    /**
     * Test get/set route.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::route()
     */
    public function testRoute(): void
    {
        $closure = static function () {
        };

        $atto = new AttoPHP();
        $atto
            ->route('blog', 'GET|POST /blog/:page<\d+>')
            ->route('blog-post', '/blog/:subject', __DIR__ . '/blog-post.phtml', $closure)
            ->route('products', '/products?page=<\d+>&search=&latest');

        $this->assertSame([
            'name' => 'blog',
            'pattern' => '/blog/:page',
            'methods' => [
                'GET',
                'POST',
            ],
            'constraints' => [
                'path' => [
                    'page' => '\d+',
                ],
                'query' => [],
            ],
            'restricted' => false,
            'view' => null,
            'callback' => null,
        ], $atto->route('blog'));

        $this->assertSame([
            'name' => 'blog-post',
            'pattern' => '/blog/:subject',
            'methods' => [
                'GET',
            ],
            'constraints' => [
                'path' => [
                    'subject' => '[^/]+',
                ],
                'query' => [],
            ],
            'restricted' => false,
            'view' => __DIR__ . '/blog-post.phtml',
            'callback' => $closure,
        ], $atto->route('blog-post'));

        $this->assertSame([
            'name' => 'products',
            'pattern' => '/products',
            'methods' => [
                'GET',
            ],
            'constraints' => [
                'path' => [],
                'query' => [
                    'page' => '\d+',
                    'search' => '.*',
                    'latest' => '.*',
                ],
            ],
            'restricted' => true,
            'view' => null,
            'callback' => null,
        ], $atto->route('products'));

        $this->assertNull($atto->route('home'));
    }

    /**
     * Test get/set task.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::task()
     */
    public function testTask(): void
    {
        $closure = static function () {
        };

        $atto = new AttoPHP();
        $atto
            ->task('queue', 'process queue <limit>')
            ->task('import', 'import stuff <limit>', __DIR__ . '/queue.php', $closure);

        $this->assertSame([
            'name' => 'queue',
            'command' => 'process queue <limit>',
            'script' => null,
            'callback' => null,
        ], $atto->task('queue'));

        $this->assertSame([
            'name' => 'import',
            'command' => 'import stuff <limit>',
            'script' => __DIR__ . '/queue.php',
            'callback' => $closure,
        ], $atto->task('import'));

        $this->assertNull($atto->task('nop'));
    }

    /**
     * Test redirect to URL.
     *
     * @runInSeparateProcess
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::redirect()
     */
    public function testRedirectToUrl(): void
    {
        $atto = new AttoPHP();
        $atto->redirect('/blog', null, false);

        $this->assertContains('Location: /blog', xdebug_get_headers());
    }

    /**
     * Test assemble static URL.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::assemble()
     */
    public function testAssembleStaticUrl(): void
    {
        $atto = new AttoPHP();
        $atto->route('contact', '/contact');

        $this->assertSame('/contact', $atto->assemble('contact'));
    }

    /**
     * Test assemble with required parameter.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::assemble()
     */
    public function testAssembleRequiredParameter(): void
    {
        $atto = new AttoPHP();
        $atto->route('help', '/help/:subject');

        $this->assertSame('/help/create-new-post', $atto->assemble('help', ['subject' => 'create-new-post']));
    }

    /**
     * Test assemble with optional parameters.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::assemble()
     */
    public function testAssembleOptionalParameter(): void
    {
        $atto = new AttoPHP();
        $atto->route('blog-post', '/blog[/:slug<[a-z-]+>[/comments/:page<\d+>]]');

        $this->assertSame('/blog/new-post', $atto->assemble('blog-post', ['slug' => 'new-post']));
        $this->assertSame('/blog/new-post/comments/4', $atto->assemble('blog-post', [
            'slug' => 'new-post',
            'page' => 4,
        ]));
    }

    /**
     * Test assemble parameter with valid constraint value.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::assemble()
     */
    public function testAssembleValidParameterValue(): void
    {
        $atto = new AttoPHP();
        $atto->route('blog', '/blog/:page<\d+>');

        $this->assertSame('/blog/4', $atto->assemble('blog', ['page' => '4']));
    }

    /**
     * Test assemble translation parameter.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::assemble()
     */
    public function testAssembleTranslationParameter(): void
    {
        $atto = new AttoPHP();
        $atto
            ->translation(__DIR__ . '/translations/*.php')
            ->locale('nl-nl')
            ->route('blog', '/blog/{page}/:page<\d+>?{order}=')
            ->run('/', 'GET');

        $this->assertSame('/blog/pagina/4?sortering=desc', $atto->assemble('blog', ['page' => '4', 'order' => 'desc']));
    }

    /**
     * Test assemble asterisk route.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::assemble()
     */
    public function testAssembleAsteriskRoute(): void
    {
        $atto = new AttoPHP();
        $atto->route('asterisk', '/foo*');

        $this->assertSame('/foo', $atto->assemble('asterisk'));
    }

    /**
     * Test assemble matched route.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::assemble()
     */
    public function testAssembleMatchedRoute(): void
    {
        $atto = new AttoPHP();
        $atto
            ->route('blog', '/blog[/:page]')
            ->run('/blog/3', 'GET');

        $this->assertSame('/blog/3', $atto->assemble());
    }

    /**
     * Test assemble route with matched route parameters.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::assemble()
     */
    public function testAssembleWithMatchedRouteMatches(): void
    {
        $atto = new AttoPHP();
        $atto
            ->route('blog-view', '/blog/:slug?page')
            ->route('blog-comments', '/blog/:slug/comments?page')
            ->run('/blog/new-blog-title/comments?page=3', 'GET');

        $this->assertSame('/blog/new-blog-title?page=3', $atto->assemble('blog-view'));
    }

    /**
     * Test assemble route without matched route parameters.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::assemble()
     */
    public function testAssembleWithoutMatchedRouteMatches(): void
    {
        $atto = new AttoPHP();
        $atto
            ->route('blog-view', '/blog/:slug?page')
            ->route('blog-comments', '/blog/:slug/comments?page')
            ->run('/blog/new-blog-title/comments?page=3', 'GET');

        $this->assertSame('/blog/slug', $atto->assemble('blog-view', ['slug' => 'slug'], false));
    }

    /**
     * Test assemble matched asterisk route.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::assemble()
     */
    public function testAssembleMatchedAsteriskRoute(): void
    {
        $atto = new AttoPHP();
        $atto
            ->route('asterisk', '/*')
            ->run('/blog/3', 'GET');

        $this->assertSame('/', $atto->assemble());
    }

    /**
     * Test assemble required parameter with invalid constraint value.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::assemble()
     */
    public function testAssembleInvalidRequiredParameterValue(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Value "a" for parameter "page" is not allowed by constraint "[\d]+" for route ' .
            'with name "blog". Please give a valid value.');

        $atto = new AttoPHP();
        $atto
            ->route('blog', '/blog/:page<[\d]+>')
            ->assemble('blog', ['page' => 'a']);
    }

    /**
     * Test assemble optional parameter with invalid constraint value.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::assemble()
     */
    public function testAssembleInvalidOptionalParameterValue(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Value "a" for parameter "page" is not allowed by constraint "\d+" for route ' .
            'with name "blog". Please give a valid value.');

        $atto = new AttoPHP();
        $atto
            ->route('blog', '/blog[/:page<\d+>]')
            ->assemble('blog', ['page' => 'a']);
    }

    /**
     * Test assemble with missing required parameter.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::assemble()
     */
    public function testAssembleMissingRequiredParameter(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Required parameter "subject" for route name "help" is missing. Please give ' .
            'the required parameter or change the route URL.');

        $atto = new AttoPHP();
        $atto
            ->route('help', '/help/:subject')
            ->assemble('help');
    }

    /**
     * Test assemble non-existing route.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::assemble()
     */
    public function testAssembleNonExistingRoute(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No route found with name "help". Please check the name of the route or give ' .
            'a new route with the same name.');

        $atto = new AttoPHP();
        $atto->assemble('help');
    }

    /**
     * Test assemble without route name and matched route.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::assemble()
     */
    public function testAssembleWithoutNameAndMatchedRoute(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Route without name can only be assembled when a route is matched.');

        $atto = new AttoPHP();
        $atto->assemble();
    }

    /**
     * Test assemble query string.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::assemble()
     */
    public function testAssembleQueryString(): void
    {
        $atto = new AttoPHP();
        $atto->route('blog', '/blog?page=<\d+>&limit=&sort&filter');

        $this->assertSame('/blog?page=3&limit=20&sort=', $atto->assemble('blog', [
            'page' => '3',
            'limit' => 20,
            'sort' => '',
            'filter' => null,
        ]));
    }

    /**
     * Test assemble query string with invalid parameter value.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::assemble()
     */
    public function testAssembleQueryStringWithInvalidParameterValue(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Value "a" for query string parameter "page" is not allowed by constraint ' .
            '"\d+" for route with name "blog". Please give a valid value.');

        $atto = new AttoPHP();
        $atto
            ->route('blog', '/blog?page=<\d+>')
            ->assemble('blog', ['page' => 'a']);
    }

    /**
     * Test assemble with same parameter in path and query string.
     */
    public function testAssembleWithSameParameterInPathAndQueryString(): void
    {
        $atto = new AttoPHP();
        $atto->route('blog', '/blog/:page?page=<\d+>');

        $this->assertSame('/blog/30', $atto->assemble('blog', ['page' => 30]));
    }

    /**
     * Test assemble with parameters for path and query string.
     */
    public function testAssembleWithParametersForPathAndQueryString(): void
    {
        $atto = new AttoPHP();
        $atto->route('blog', '/blog/:slug?page=<\d+>');

        $this->assertSame('/blog/slug?page=2', $atto->assemble('blog', ['slug' => 'slug', 'page' => 2]));
    }

    /**
     * Test match URL path to route.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::match()
     */
    public function testMatchStaticUrl(): void
    {
        $atto = new AttoPHP();
        $atto->route('blog', 'GET|POST /blog');

        $this->assertSame('blog', $atto->match('/blog', 'GET')['name']);
    }

    /**
     * Test match URL path to route with required parameter.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::match()
     */
    public function testMatchRequiredParameter(): void
    {
        $atto = new AttoPHP();
        $atto->route('blog', '/blog/:page');

        $this->assertSame('blog', $atto->match('/blog/4', 'GET')['name']);
    }

    /**
     * Test match URL path to route with optional parameters.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::match()
     */
    public function testMatchOptionalParameters(): void
    {
        $atto = new AttoPHP();
        $atto->route('blog-post', '/blog/:slug[/comments[/:page]]');

        $this->assertSame('blog-post', $atto->match('/blog/new-post', 'GET')['name']);
        $this->assertSame('blog-post', $atto->match('/blog/new-post/comments', 'GET')['name']);
        $this->assertSame('blog-post', $atto->match('/blog/new-post/comments/4', 'GET')['name']);
    }

    /**
     * Test match URL path to required and optional parameter with constraints.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::match()
     */
    public function testMatchParameterConstraint(): void
    {
        $atto = new AttoPHP();
        $atto->route('blog', '/blog/:slug<[a-z\-]+>[/comments/:page<\d+>]');

        $this->assertSame('blog', $atto->match('/blog/foo-bar', 'GET')['name']);
        $this->assertSame('blog', $atto->match('/blog/foo-bar/comments/4', 'GET')['name']);
        $this->assertNull($atto->match('/blog/foo+bar/comments/4', 'GET'));
        $this->assertNull($atto->match('/blog/foo-bar/comments/4a', 'GET'));
    }

    /**
     * Test match URL path to route with asterisk.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::match()
     */
    public function testMatchAsterisk(): void
    {
        $atto = new AttoPHP();
        $atto
            ->route('foo', '/foo*')
            ->route('bar', '/bar/*/foo')
            ->route('baz', '*/baz')
            ->route('catch-all', '*');

        $this->assertSame('catch-all', $atto->match('/blog/new-post', 'GET')['name']);
        $this->assertSame('catch-all', $atto->match('/help/create-new-post', 'GET')['name']);
        $this->assertSame('foo', $atto->match('/foo/bar/baz', 'GET')['name']);
        $this->assertSame('bar', $atto->match('/bar/baz/foo', 'GET')['name']);
        $this->assertSame('baz', $atto->match('/bar/foo/baz', 'GET')['name']);
    }

    /**
     * Test match URL path to route with translation match.
     *
     * @return void
     */
    public function testMatchWithTranslationMatch(): void
    {
        $atto = new AttoPHP();
        $atto
            ->translation(__DIR__ . '/translations/*.php')
            ->locale('nl-nl')
            ->route('blog', '/blog[/{page}/:page]')
            ->run('/blog/pagina/3', 'GET');

        $this->assertSame('blog', $atto->route()['name']);
    }

    /**
     * Test match URL path to route without translation match.
     *
     * @return void
     */
    public function testMatchWithoutTranslationMatch(): void
    {
        $atto = new AttoPHP();
        $atto
            ->translation(__DIR__ . '/translations/*.php')
            ->locale('nl-nl')
            ->route('blog', '/blog[/{pages}/:page]')
            ->run('/blog/pagina/3', 'GET', [], 'en-us');

        $this->assertNull($atto->route());
    }

    /**
     * Test match query string parameter with value constraint.
     *
     * @return void
     */
    public function testMatchQueryStringParameterWithConstraint(): void
    {
        $atto = new AttoPHP();
        $atto->route('blog-overview', '/blog?page=<\d+>');

        $this->assertSame('blog-overview', $atto->match('/blog?page=3', 'GET')['name']);
        $this->assertNull($atto->match('/blog?page=a', 'GET'));
        $this->assertNull($atto->match('/blog?page=', 'GET'));
        $this->assertNull($atto->match('/blog?page', 'GET'));
    }

    /**
     * Test match query string parameter without value constraint.
     *
     * @return void
     */
    public function testMatchQueryStringParameterWithoutConstraint(): void
    {
        $atto = new AttoPHP();
        $atto->route('blog-overview', '/blog?page=');

        $this->assertSame('blog-overview', $atto->match('/blog?page=3', 'GET')['name']);
        $this->assertSame('blog-overview', $atto->match('/blog?page=a', 'GET')['name']);
        $this->assertSame('blog-overview', $atto->match('/blog?page=', 'GET')['name']);
        $this->assertSame('blog-overview', $atto->match('/blog?page', 'GET')['name']);
    }

    /**
     * Test match query string parameter with translation.
     *
     * @return void
     */
    public function testMatchQueryStringParameterWithTranslation(): void
    {
        $atto = new AttoPHP();
        $atto
            ->translation(__DIR__ . '/translations/*.php')
            ->locale('nl-nl')
            ->route('blog-overview', '/blog?{page}=')
            ->run('/blog?pagina=3', 'GET');

        $this->assertSame('blog-overview', $atto->route()['name']);
        $this->assertSame(['page' => '3'], $atto->route()['matches']);
    }

    /**
     * Test match query string parameter without translation.
     *
     * @return void
     */
    public function testMatchQueryStringParameterWithoutTranslation(): void
    {
        $atto = new AttoPHP();
        $atto
            ->translation(__DIR__ . '/translations/*.php')
            ->locale('nl-nl')
            ->route('blog-overview', '/blog?{pages}=')
            ->run('/blog?pagina=3', 'GET', [], 'en-us');

        $this->assertNull($atto->route());
    }

    /**
     * Test match query string without parameters.
     *
     * @return void
     */
    public function testMatchQueryStringWithoutParameters(): void
    {
        $atto = new AttoPHP();
        $atto->route('blog-overview', '/blog');

        $this->assertSame('blog-overview', $atto->match('/blog?page=3', 'GET')['name']);
        $this->assertSame('blog-overview', $atto->match('/blog?page=a', 'GET')['name']);
        $this->assertSame('blog-overview', $atto->match('/blog?page=', 'GET')['name']);
        $this->assertSame('blog-overview', $atto->match('/blog?page', 'GET')['name']);
    }

    /**
     * Test match query string not allowed.
     *
     * @return void
     */
    public function testMatchQueryStringNotAllowed(): void
    {
        $atto = new AttoPHP();
        $atto->route('blog-overview', '/blog?!');

        $this->assertNull($atto->match('/blog?page=3', 'GET'));
        $this->assertNull($atto->match('/blog?page=a', 'GET'));
        $this->assertNull($atto->match('/blog?page', 'GET'));
    }

    /**
     * Test match URL path to no route.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::match()
     */
    public function testMatchNoMatch(): void
    {
        $atto = new AttoPHP();
        $atto->route('blog', '/blog');

        $this->assertNull($atto->match('/blog/new-post', 'GET'));
    }

    /**
     * Test match URL path with request method.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::match()
     */
    public function testMatchRequestMethods(): void
    {
        $atto = new AttoPHP();
        $atto
            ->route('blog', 'POST|DELETE /blog')
            ->route('blog-post', '/blog/:slug');

        $this->assertNull($atto->match('/blog', 'GET'));
        $this->assertNull($atto->match('/blog/foo-bar', 'POST'));

        $this->assertSame('blog', $atto->match('/blog', 'POST')['name']);
        $this->assertSame('blog', $atto->match('/blog', 'DELETE')['name']);
    }

    /**
     * Test match URL and unmatched parameter as null value.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::match()
     */
    public function testMatchUnmatchedParameterAsNull(): void
    {
        $atto = new AttoPHP();
        $atto->route('blog-post', '/blog/:slug[/comments[/:page]]');

        $route = $atto->match('/blog/new-blog', 'GET');
        $matches = $route['matches'];

        $this->assertSame('new-blog', $matches['slug']);
        $this->assertNull($matches['page']);
    }

    /**
     * Test parse static part.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::parse()
     */
    public function testParseStaticPart(): void
    {
        $atto = new AttoPHP();
        $atto->task('import', 'import feed');

        $this->assertNull($atto->parse(['index.php', 'process', 'queue']));

        $this->assertSame('import', $atto->parse(['index.php', 'import', 'feed'])['name']);
    }

    /**
     * Test parse required part.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::parse()
     */
    public function testParseRequiredPart(): void
    {
        $atto = new AttoPHP();
        $atto->task('import', 'import feed <id>');

        $this->assertNull($atto->parse(['index.php', 'import', 'feed']));

        $this->assertSame('import', $atto->parse(['index.php', 'import', 'feed', '15'])['name']);
    }

    /**
     * Test parse optional part.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::parse()
     */
    public function testParseOptionalPart(): void
    {
        $atto = new AttoPHP();
        $atto->task('import', 'import feed <id> [<limit>]');

        $this->assertSame('import', $atto->parse(['index.php', 'import', 'feed', '15'])['name']);
        $this->assertSame('import', $atto->parse(['index.php', 'import', 'feed', '15', '20'])['name']);
    }

    /**
     * Test parse with more arguments than defined
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::parse()
     */
    public function testParseWithMoreArgumentsThanDefined(): void
    {
        $atto = new AttoPHP();
        $atto->task('import', 'import feed <id>');

        $this->assertNull($atto->parse(['index.php', 'import', 'feed', '15', '20']));
    }

    /**
     * Test parse with malformed command.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::parse()
     */
    public function testParseMalformedCommand(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to parse command. Part "<id" is not a valid static word "word", a ' .
            'required parameter "<parameter>" or an optional parameter "[<parameter>]".');

        $atto = new AttoPHP();
        $atto
            ->task('import', 'import feed <id')
            ->parse(['index.php', 'import', 'feed', '15']);
    }

    /**
     * Test render file.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::render()
     */
    public function testRenderFile(): void
    {
        $atto = new AttoPHP();
        $atto->data('title', 'Homepage');

        $this->assertSame('<h1>Homepage</h1>', $atto->render(__DIR__ . '/templates/view.phtml'));
    }

    /**
     * Test render file with nested render.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::render()
     */
    public function testRenderFileWithNestedRender(): void
    {
        $atto = new AttoPHP();
        $atto->root(__DIR__ . '/templates/');

        $this->assertSame('<div>1<div>2</div></div>', $atto->render('render/render1.phtml'));
    }

    /**
     * Test render file without output buffer.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::render()
     */
    public function testRenderFileWithoutOutputBuffer(): void
    {
        $atto = new AttoPHP();
        $atto->data('title', 'Homepage');

        ob_start();
        $this->assertSame(null, $atto->render(__DIR__ . '/templates/view.phtml', null, false));
        $this->assertSame('<h1>Homepage</h1>', ob_get_clean());
    }

    /**
     * Test render file without output buffer and missing file.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::render()
     */
    public function testRenderFileWithoutOutputBufferAndMissingFile(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('File "unknown-script.php" not found as an absolute path or in the root ' .
            'directory.');

        $atto = new AttoPHP();
        $atto->render('unknown-script.php', null, false);
    }

    /**
     * Test render file with root set.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::render()
     */
    public function testRenderFileWithRoot(): void
    {
        $atto = new AttoPHP();
        $atto
            ->root(__DIR__ . '/templates/')
            ->data('title', 'Homepage');

        $this->assertSame('<h1>Homepage</h1>', $atto->render('view.phtml'));
    }

    /**
     * Test render file.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::render()
     */
    public function testRenderString(): void
    {
        $atto = new AttoPHP();

        $this->assertSame('<h1>Homepage</h1>', $atto->render('<h1>Homepage</h1>'));
    }

    /**
     * Test render throwable inside included file.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::render()
     */
    public function testRenderCallbackThrowable(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Thrown inside included file.');

        $atto = new AttoPHP();
        $atto->render(__DIR__ . '/templates/throwable.phtml');
    }

    /**
     * Test closure call.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::call()
     */
    public function testCall(): void
    {
        $newThis = $this;
        $closure = function (string $arg1, ?int $arg2, string $arg3 = 'foo', string $arg4 = null) use ($newThis) {
            $newThis::assertSame($this, $newThis);
            $newThis::assertSame('foo', $arg1);
            $newThis::assertNull($arg2);
            $newThis::assertSame('foo', $arg3);
            $newThis::assertNull($arg4);
        };

        $atto = new AttoPHP();
        $atto->call($closure, $newThis, [
            'arg1' => 'foo',
        ]);
    }

    /**
     * Test closure call with missing required argument.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::call()
     */
    public function testCallRequiredArgumentMissing(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Required argument "arg1" for callback is not provided in the arguments array, ' .
            'does not has a default value and is not nullable. Please give the missing argument or give it a default ' .
            'value.');

        $closure = static function (string $arg1) {
        };

        $atto = new AttoPHP();
        $atto->call($closure, $this);
    }

    /**
     * Test run with expected flow.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::run()
     */
    public function testRun(): void
    {
        $atto = new AttoPHP();
        $atto
            ->layout(__DIR__ . '/templates/layout.phtml')
            ->route('blog', '/blog', __DIR__ . '/templates/view.phtml', function () {
                /** @var AttoPHPInterface $this */
                $this->data('title', 'Homepage');
            });

        $this->assertSame('<div><h1>Homepage</h1></div>', $atto->run('/blog', 'GET'));
    }

    /**
     * Test run with route view overwriting earlier set view.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::run()
     */
    public function testRunViewOverwrite(): void
    {
        $atto = new AttoPHP();
        $atto
            ->view(__DIR__ . '/templates/throwable.phtml')
            ->layout(__DIR__ . '/templates/layout.phtml')
            ->route('blog', '/blog', __DIR__ . '/templates/view.phtml', function () {
                /** @var AttoPHPInterface $this */
                $this->data('title', 'Homepage');
            });

        $this->assertSame('<div><h1>Homepage</h1></div>', $atto->run('/blog', 'GET'));
    }

    /**
     * Test run with short circuit from start callback.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::run()
     */
    public function testRunWithReturnFromStartCallback(): void
    {
        $atto = new AttoPHP();
        $atto->start(function () {
            return 'short circuit';
        });

        $this->assertSame('short circuit', $atto->run('/', 'GET'));
    }

    /**
     * Test run with short circuit from before callback.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::run()
     */
    public function testRunWithReturnFromBeforeCallback(): void
    {
        $atto = new AttoPHP();
        $atto
            ->route('blog', '/blog/:id')
            ->before(function (int $id) {
                return 'blog ' . $id;
            });

        $this->assertSame('blog 5', $atto->run('/blog/5', 'GET'));
    }

    /**
     * Test run with short circuit from after callback.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::run()
     */
    public function testRunWithReturnFromAfterCallback(): void
    {
        $atto = new AttoPHP();
        $atto
            ->route('blog', '/blog/:id')
            ->after(function (int $id) {
                return 'blog ' . $id;
            });

        $this->assertSame('blog 5', $atto->run('/blog/5', 'GET'));
    }

    /**
     * Test run with loaded config for start callback.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::run()
     */
    public function testRunWithConfig(): void
    {
        $phpUnit = $this;

        $atto = new AttoPHP();
        $atto
            ->config(__DIR__ . '/config/*.config.php')
            ->start(function (array $config) use ($phpUnit) {
                $phpUnit->assertSame([
                    'foo' => 'bar',
                    'bar' => 'qux',
                    'qux' => 'fred',
                ], $config);
            })
            ->run('/blog', 'GET');
    }

    /**
     * Test run and translate without locale.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::run()
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::translate()
     */
    public function testRunAndTranslateWithoutLocale(): void
    {
        $atto = new AttoPHP();
        $atto
            ->translation(__DIR__ . '/translations/*.php')
            ->run('/', 'GET');

        $this->assertSame('Hello', $atto->translate('Hello'));
    }

    /**
     * Test run and translate with global locale.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::run()
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::translate()
     */
    public function testRunAndTranslateWithGlobalLocale(): void
    {
        $atto = new AttoPHP();
        $atto
            ->locale('nl-nl')
            ->translation(__DIR__ . '/translations/*.php')
            ->run('/', 'GET');

        $this->assertSame('Hallo', $atto->translate('Hello'));
    }

    /**
     * Test run and translate while finding the best locale match.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::run()
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::translate()
     */
    public function testRunAndTranslateWithFallbackLocale(): void
    {
        $atto = new AttoPHP();
        $atto
            ->translation(__DIR__ . '/translations/*.php')
            ->run('/', 'GET');

        $this->assertSame('Doei', $atto->translate('Bye', 'nl-be')); // In nl-be translation.
        $this->assertSame('Hallo', $atto->translate('Hello', 'nl-be')); // In nl translation.
        $this->assertSame('Bienvenue', $atto->translate('Welcome', 'fr-be')); // In fr-be translation.
        $this->assertSame('Hello', $atto->translate('Hello', 'fr-be')); // No matching translation.
    }

    /**
     * Test run and translate with different locale parameters.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::run()
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::translate()
     */
    public function testRunAndTranslateWithDifferentLocaleParameters(): void
    {
        $atto = new AttoPHP();
        $atto
            ->translation(__DIR__ . '/translations/*.php')
            ->run('/', 'GET');

        $this->assertSame('Hello', $atto->translate('Hello'));
        $this->assertSame('Hallo', $atto->translate('Hello', 'nl'));
        $this->assertSame('Hallo', $atto->translate('Hello', 'nl-nl'));
        $this->assertSame('Hallo', $atto->translate('Hello', 'nl-NL'));
        $this->assertSame('Hallo', $atto->translate('Hello', 'nl_NL'));
    }

    /**
     * Test run with short circuit from finish callback.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::run()
     */
    public function testRunWithReturnFromFinishCallback(): void
    {
        $atto = new AttoPHP();
        $atto->finish(function () {
            return 'short circuit';
        });

        $this->assertSame('short circuit', $atto->run('/', 'GET'));
    }

    /**
     * Test run with short circuit from error callback.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::run()
     */
    public function testRunWithReturnFromErrorCallback(): void
    {
        $atto = new AttoPHP();
        $atto
            ->error(function (Throwable $throwable) {
                return $throwable->getMessage() . ' 2';
            })
            ->route('blog', '/blog', null, function () {
                throw new RuntimeException('short circuit');
            });

        $this->assertSame('short circuit 2', $atto->run('/blog', 'GET'));
    }

    /**
     * Test run with caught Throwable from route callback.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::run()
     */
    public function testRunWithReturnFromCaughtException(): void
    {
        $atto = new AttoPHP();
        $atto->route('blog', '/blog', null, function () {
            throw new RuntimeException('short circuit');
        });

        $this->assertSame('short circuit', $atto->run('/blog', 'GET'));
    }

    /**
     * Test run with caught Throwable from error callback.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::run()
     */
    public function testRunWithReturnFromCaughtErrorCallback(): void
    {
        $atto = new AttoPHP();
        $atto
            ->error(function () {
                throw new RuntimeException('short circuit 2');
            })
            ->route('blog', '/blog', null, function () {
                throw new RuntimeException('short circuit 1');
            });

        $this->assertSame('short circuit 2', $atto->run('/blog', 'GET'));
    }

    /**
     * Test run with short circuit from matched route callback.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::run()
     */
    public function testRunWithReturnFromRoute(): void
    {
        $atto = new AttoPHP();
        $atto->route('blog', '/blog', null, function () {
            return 'short circuit';
        });

        $this->assertSame('short circuit', $atto->run('/blog', 'GET'));
    }

    /**
     * Test run and matched route will be returned with matched and unmatched path and query string parameters.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::run()
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::match()
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::route()
     */
    public function testRunMatchedRoute(): void
    {
        $atto = new AttoPHP();
        $atto
            ->route('blog', '/blog/:id/comments[/:commentId]?page=&limit=')
            ->route('news', '/news');

        $atto->run('/blog/5/comments?page=1', 'GET');
        $this->assertSame('blog', $atto->route()['name']);
        $this->assertSame([
            'id' => '5',
            'commentId' => null,
            'page' => '1',
            'limit' => null,
        ], $atto->data('atto.route'));

        $atto->run('/news?page=1', 'GET');
        $this->assertSame('news', $atto->route()['name']);
        $this->assertEmpty($atto->data('atto.route'));
    }

    /**
     * Test run and parsed task callback will be called.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::run()
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::parse()
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::task()
     */
    public function testRunTaskCallback(): void
    {
        $atto = new AttoPHP();
        $return = $atto
            ->task('import', 'import feed <id>', null, function () {
                return 'foo';
            })
            ->run(null, null, ['index.php', 'import', 'feed', '15']);

        $this->assertSame('foo', $return);
    }

    /**
     * Test run and parsed task script will be called.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::run()
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::parse()
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::task()
     */
    public function testRunTaskScript(): void
    {
        ob_start();
        $atto = new AttoPHP();
        $atto
            ->task('import', 'import feed <id>', __DIR__ . '/scripts/import.php', function (string $id) {
                /** @var AttoPHPInterface $this */
                $this->data('id', $id);
            })
            ->run(null, null, ['index.php', 'import', 'feed', '15']);

        $this->assertSame('15', ob_get_clean());
    }

    /**
     * Test run without command and console usage will be shown with tasks.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::run()
     */
    public function testRunConsoleUsageWithoutCommand(): void
    {
        $atto = new AttoPHP();
        $atto
            ->task('queue', 'process queue <limit>')
            ->task('import', 'import stuff <limit>');

        $this->assertSame(
            implode(PHP_EOL, [
                'AttoPHP Console (version 0.2.0)',
                '',
                'Tasks (command <required> [<optional>]):',
                ' - process queue <limit>',
                ' - import stuff <limit>',
                '',
                '',
            ]),
            $atto->run(null, null, ['index.php'])
        );
    }

    /**
     * Test run without command and console usage will be shown without tasks.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::run()
     */
    public function testRunConsoleUsageWithoutTasks(): void
    {
        $atto = new AttoPHP();

        $this->assertSame(
            implode(PHP_EOL, [
                'AttoPHP Console (version 0.2.0)',
                '',
                'No tasks available.',
                '',
                '',
            ]),
            $atto->run(null, null, ['index.php'])
        );
    }

    /**
     * Test run with unknown command and console usage will be shown with tasks.
     *
     * @covers \ExtendsSoftware\AttoPHP\AttoPHP::run()
     */
    public function testRunConsoleUsageUnknownCommand(): void
    {
        $atto = new AttoPHP();
        $atto
            ->task('queue', 'process queue <limit>')
            ->task('import', 'import stuff <limit>');

        $this->assertSame(
            implode(PHP_EOL, [
                'AttoPHP Console (version 0.2.0)',
                '',
                "\033[31mNo task found for command.\033[0m",
                '',
                'Tasks (command <required> [<optional>]):',
                ' - process queue <limit>',
                ' - import stuff <limit>',
                '',
                '',
            ]),
            $atto->run(null, null, ['index.php', 'unknown', 'command'])
        );
    }
}
