<?php
declare(strict_types=1);

namespace ExtendsSoftware\AttoPHP;

use Closure;
use InvalidArgumentException;
use Throwable;

/**
 * AttoPHP Interface.
 *
 * AttoPHP is a tool based on the builder pattern to configure, route and render a website in no time.
 *
 * @package ExtendsSoftware\AttoPHP
 * @author  Vincent van Dijk <vincent@extends.nl>
 * @version 0.2.0
 * @see     https://github.com/extendssoftware/atto-php
 */
interface AttoPHPInterface
{
    const VERSION = '0.2.0';

    /**
     * Get/set start callback.
     *
     * @param Closure|null $callback
     *
     * @return AttoPHPInterface|Closure|null The callback when found, null or AttoPHPInterface for method chaining.
     */
    public function start(Closure $callback = null): AttoPHPInterface|Closure|null;

    /**
     * Get/set before callback.
     *
     * @param Closure|null $callback
     *
     * @return AttoPHPInterface|Closure|null The callback when found, null or AttoPHPInterface for method chaining.
     */
    public function before(Closure $callback = null): AttoPHPInterface|Closure|null;

    /**
     * Get/set after callback.
     *
     * @param Closure|null $callback
     *
     * @return AttoPHPInterface|Closure|null The callback when found, null or AttoPHPInterface for method chaining.
     */
    public function after(Closure $callback = null): AttoPHPInterface|Closure|null;

    /**
     * Get/set finish callback.
     *
     * @param Closure|null $callback
     *
     * @return AttoPHPInterface|Closure|null The callback when found, null or AttoPHPInterface for method chaining.
     */
    public function finish(Closure $callback = null): AttoPHPInterface|Closure|null;

    /**
     * Get/set error callback.
     *
     * @param Closure|null $callback
     *
     * @return AttoPHPInterface|Closure|null The callback when found, null or AttoPHPInterface for method chaining.
     */
    public function error(Closure $callback = null): AttoPHPInterface|Closure|null;

    /**
     * Get/set config path pattern.
     *
     * @param string|null $pattern Pattern to set.
     *
     * @return AttoPHPInterface|string|null The config pattern when set, null or AttoPHPInterface for method chaining.
     */
    public function config(string $pattern = null): AttoPHPInterface|string|null;

    /**
     * Get/set translation path.
     *
     * @param string|null $path Path to the translation directory.
     *
     * @return AttoPHPInterface|string|null The translation path when set, null or AttoPHPInterface for method chaining.
     */
    public function translation(string $path = null): AttoPHPInterface|string|null;

    /**
     * Get/set root template path.
     *
     * @param string|null $path Path to the template directory.
     *
     * @return AttoPHPInterface|string|null The root template path when set, null or AttoPHPInterface for method
     *                                      chaining.
     */
    public function root(string $path = null): AttoPHPInterface|string|null;

    /**
     * Get/set view file.
     *
     * @param string|null $filename Filename to set.
     *
     * @return AttoPHPInterface|string|null The view filename when set, null or AttoPHPInterface for method chaining.
     */
    public function view(string $filename = null): AttoPHPInterface|string|null;

    /**
     * Get/set layout file.
     *
     * @param string|null $filename Filename to set.
     *
     * @return AttoPHPInterface|string|null The layout filename when set, null or AttoPHPInterface for method chaining.
     */
    public function layout(string $filename = null): AttoPHPInterface|string|null;

    /**
     * Get/set locale.
     *
     * @param string|null $locale Locale to set.
     *
     * @return AttoPHPInterface|string|null The locale when set, null or AttoPHPInterface for method chaining.
     */
    public function locale(string $locale = null): AttoPHPInterface|string|null;

    /**
     * Get/set data from/to the container.
     *
     * @param string|null $path  Dot notation path to get/set data for.
     * @param mixed       $value Value to set.
     *
     * @return AttoPHPInterface|mixed|null Data for name when found, all data, null or AttoPHPInterface for method
     *                                     chaining.
     * @throws InvalidArgumentException When path dot notation is wrong.
     */
    public function data(string $path = null, mixed $value = null): mixed;

    /**
     * Get/set HTTP route.
     *
     * @param string|null  $name     Name of the route.
     * @param string|null  $pattern  Pattern to match.
     * @param string|null  $view     Filename to the view file.
     * @param Closure|null $callback Callback to call when route is matched.
     *
     * @return AttoPHPInterface|array|null The route when found, null or AttoPHPInterface for method chaining.
     */
    public function route(
        string  $name = null,
        string  $pattern = null,
        string  $view = null,
        Closure $callback = null
    ): AttoPHPInterface|array|null;

    /**
     * Get/set console task.
     *
     * @param string       $name     Name of the task.
     * @param string|null  $command  Command to execute task.
     * @param string|null  $script   Filename to the script file.
     * @param Closure|null $callback Callback to call when command is parsed.
     *
     * @return AttoPHPInterface|array|null The task when found, null or AttoPHPInterface for method chaining.
     */
    public function task(
        string  $name,
        string  $command = null,
        string  $script = null,
        Closure $callback = null
    ): AttoPHPInterface|array|null;

    /**
     * Redirect to URL.
     *
     * @param string    $url    URL to redirect to.
     * @param int|null  $status HTTP status code to use. Default is 301.
     * @param bool|null $exit   Clear output buffers and terminate script, default is true.
     *
     * @return void
     */
    public function redirect(string $url, int $status = null, bool $exit = null): void;

    /**
     * Translate text.
     *
     * AttoPHP will keep searching for best matching locale and translated text.
     *
     * @param string      $text   Text to translate.
     * @param string|null $locale Locale to use for translation. AttoPHP will use global locale when null.
     *
     * @return string The translated text. If locale or text found, unaltered text will be returned.`
     */
    public function translate(string $text, string $locale = null): string;

    /**
     * Assemble URL.
     *
     * @param string|null $name       Name of the route or null for the matched route.
     * @param array|null  $parameters Route parameters for path and query string.
     * @param bool|null   $reuse      Reuse parameters from matched route. Default is true.
     * @param string|null $locale     Locale to be passed to translate method.
     *
     * @return string Assembled URL for route.
     * @throws Throwable When route with name is not found, when a required parameter for the route is not provided or
     *                   when a constraint fails.
     */
    public function assemble(
        string $name = null,
        array  $parameters = null,
        bool   $reuse = null,
        string $locale = null
    ): string;

    /**
     * Match route for URL path.
     *
     * @param string      $path   URL path to find matching route for.
     * @param string      $method Request method.
     * @param string|null $locale Locale to be passed to translate method.
     *
     * @return array|null Matched route or null when no route can be matched.
     */
    public function match(string $path, string $method, string $locale = null): ?array;

    /**
     * Match task for CLI arguments.
     *
     * @param array $arguments Command line script arguments.
     *
     * @return array|null Matched task or null when no task can be matched.
     */
    public function parse(array $arguments): ?array;

    /**
     * Render file with PHP include.
     *
     * @param string      $filename Filename to render or string to return.
     * @param object|null $newThis  New current object for the included file.
     * @param bool|null   $buffer   Whether to buffer and return output, default is true.
     *
     * @return string|null Rendered content from the file or the string when not a file when output buffer enabled.
     * @throws Throwable When the file throws a Throwable.
     */
    public function render(string $filename, object $newThis = null, bool $buffer = null): ?string;

    /**
     * Call a callback.
     *
     * @param Closure    $callback  Callback to call.
     * @param object     $newThis   Current object for the callback.
     * @param array|null $arguments Callback arguments with the key matching the name of the argument.
     *
     * @return mixed Result of the callback.
     * @throws Throwable When callback reflection fails or a required argument is missing.
     */
    public function call(Closure $callback, object $newThis, array $arguments = null): mixed;

    /**
     * Run AttoPHP in four steps.
     *
     * @param string|null $path      URL path to match. Default is REQUEST_URI from the server environment.
     * @param string|null $method    Request method. Default is REQUEST_METHOD from the server environment.
     * @param array|null  $arguments CLI arguments. Default is argv from the server environment.
     * @param string|null $locale    Locale to be passed to match method.
     *
     * @return string Rendered content. Or the Throwable message on error.
     */
    public function run(
        string $path = null,
        string $method = null,
        array  $arguments = null,
        string $locale = null
    ): string;
}
