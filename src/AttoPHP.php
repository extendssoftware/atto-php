<?php
declare(strict_types=1);

namespace ExtendsSoftware\AttoPHP;

use Closure;
use InvalidArgumentException;
use ReflectionFunction;
use RuntimeException;
use Throwable;
use function array_fill_keys;
use function array_filter;
use function array_key_exists;
use function array_keys;
use function array_merge;
use function array_slice;
use function count;
use function explode;
use function glob;
use function header;
use function http_build_query;
use function in_array;
use function is_array;
use function is_file;
use function is_string;
use function ob_end_clean;
use function ob_get_clean;
use function ob_get_level;
use function ob_start;
use function parse_str;
use function parse_url;
use function preg_match;
use function preg_replace;
use function preg_replace_callback;
use function preg_split;
use function sprintf;
use function str_replace;
use function strtok;
use function trim;

/**
 * Implementation of AttoPHPInterface.
 *
 * @package ExtendsSoftware\AttoPHP
 * @author  Vincent van Dijk <vincent@extends.nl>
 * @version 0.1.0
 * @see     https://github.com/extendssoftware/atto-php
 */
class AttoPHP implements AttoPHPInterface
{
    /**
     * Config path pattern.
     *
     * @var string|null
     */
    protected ?string $config = null;

    /**
     * Templates root.
     *
     * @var string|null
     */
    protected ?string $root = null;

    /**
     * Translation root.
     *
     * @var string|null
     */
    protected ?string $translation = null;

    /**
     * Filename for view file.
     *
     * @var string|null
     */
    protected ?string $view = null;

    /**
     * Filename for layout file.
     *
     * @var string|null
     */
    protected ?string $layout = null;

    /**
     * Locale for translations.
     *
     * @var string|null
     */
    protected ?string $locale = null;

    /**
     * Translations per locale.
     *
     * @var array[]
     */
    protected array $translations = [];

    /**
     * Routes in chronological order.
     *
     * @var array[]
     */
    protected array $routes = [];

    /**
     * Tasks in chronological order.
     *
     * @var array[]
     */
    protected array $tasks = [];

    /**
     * Matched route.
     *
     * @var array|null
     */
    protected ?array $matched = null;

    /**
     * Data container.
     *
     * @var array
     */
    protected array $data = [];

    /**
     * Start callback.
     *
     * @var Closure|null
     */
    protected ?Closure $start = null;

    /**
     * Before callback.
     *
     * @var Closure|null
     */
    protected ?Closure $before = null;

    /**
     * After callback.
     *
     * @var Closure|null
     */
    protected ?Closure $after = null;

    /**
     * Finish callback.
     *
     * @var Closure|null
     */
    protected ?Closure $finish = null;

    /**
     * Error callback.
     *
     * @var Closure|null
     */
    protected ?Closure $error = null;

    /**
     * @inheritDoc
     */
    public function start(Closure $callback = null)
    {
        if ($callback === null) {
            return $this->start;
        }

        $this->start = $callback;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function before(Closure $callback = null)
    {
        if ($callback === null) {
            return $this->before;
        }

        $this->before = $callback;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function after(Closure $callback = null)
    {
        if ($callback === null) {
            return $this->after;
        }

        $this->after = $callback;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function finish(Closure $callback = null)
    {
        if ($callback === null) {
            return $this->finish;
        }

        $this->finish = $callback;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function error(Closure $callback = null)
    {
        if ($callback === null) {
            return $this->error;
        }

        $this->error = $callback;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function config(string $pattern = null)
    {
        if ($pattern === null) {
            return $this->config;
        }

        $this->config = $pattern;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function translation(string $path = null)
    {
        if ($path === null) {
            return $this->translation;
        }

        $this->translation = $path;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function root(string $path = null)
    {
        if ($path === null) {
            return $this->root;
        }

        $this->root = $path;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function view(string $filename = null)
    {
        if ($filename === null) {
            return $this->view;
        }

        $this->view = $filename;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function layout(string $filename = null)
    {
        if ($filename === null) {
            return $this->layout;
        }

        $this->layout = $filename;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function locale(string $locale = null)
    {
        if ($locale === null) {
            return $this->locale;
        }

        $this->locale = $locale;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function data(string $path = null, $value = null)
    {
        if ($path === null) {
            return $this->data;
        }

        if (!preg_match('~^(?<path>([a-z0-9]+)((?:\.([a-z0-9]+))*))$~i', $path, $matches)) {
            throw new InvalidArgumentException(sprintf('Path "%s" is not a valid dot notation. Please fix the ' .
                'notation. The colon (:), dot (.) and slash (/) characters can be used as separator. The can be used ' .
                'interchangeably. The characters between the separator can only consist of a-z and 0-9, case ' .
                'insensitive.', $path));
        }

        $reference = &$this->data;
        $nodes = preg_split('~[:./]~', $matches['path']);

        if ($value === null) {
            if (is_array($nodes)) {
                foreach ($nodes as $node) {
                    if (is_array($reference) && array_key_exists($node, $reference)) {
                        $reference = &$reference[$node];
                    } else {
                        return null;
                    }
                }
            }

            return $reference;
        }

        if (is_array($nodes)) {
            foreach ($nodes as $node) {
                if (!array_key_exists($node, $reference) || !is_array($reference[$node])) {
                    $reference[$node] = [];
                }

                $reference = &$reference[$node];
            }
        }

        $reference = $value;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function route(string $name = null, string $pattern = null, string $view = null, Closure $callback = null)
    {
        if ($name === null) {
            return $this->matched;
        }

        if ($pattern === null) {
            return $this->routes[$name] ?? null;
        }

        // Save and replace HTTP methods prefix from pattern.
        $methods = [
            'GET',
        ];
        $pattern = preg_replace_callback(
            '~^(?<methods>\s*([a-z]+(\s*\|\s*[a-z]+)*)\s*)~i',
            static function (array $match) use (&$methods): string {
                $methods = array_map('trim', explode('|', strtoupper($match['methods'])));

                return '';
            },
            $pattern
        );

        // Save and replace path constraints.
        $path = [];
        $pattern = preg_replace_callback(
            '~:(?<parameter>[a-z]\w*)(<(?<constraint>[^>]+)>)?~i',
            static function (array $match) use (&$path) {
                $parameter = $match['parameter'];
                $path[$parameter] = $match['constraint'] ?? '[^/]+';

                return ':' . $parameter;
            },
            $pattern ?: ''
        );

        $pattern = strtok($pattern ?: '', '?');
        $queryString = strtok('?');

        // Save and remove query string constraints.
        $query = [];
        $restricted = false;
        if ($queryString) {
            $restricted = true;

            $queryString = trim($queryString);
            if ($queryString && $queryString !== '!') {
                // Split query string by & which may not be inside < and > to indicate regular expression boundaries.
                $parameters = preg_split('~&(?![^<>]*>)~', $queryString);
                if (is_array($parameters)) {
                    foreach ($parameters as $parameter) {
                        if (preg_match(
                            '~^(?P<name>[^=]+)((?<equals>=)(<(?<constraint>[^>]+)>)?)?$~i',
                            $parameter,
                            $matches
                        )) {
                            $query[$matches['name']] = $matches['constraint'] ?? '.*';
                        }
                    }
                }
            }
        }

        $this->routes[$name] = [
            'name' => $name,
            'pattern' => $pattern,
            'methods' => $methods,
            'constraints' => [
                'path' => $path,
                'query' => $query,
            ],
            'restricted' => $restricted,
            'view' => $view,
            'callback' => $callback,
        ];

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function task(string $name, string $command = null, string $script = null, Closure $callback = null)
    {
        if ($command === null) {
            return $this->tasks[$name] ?? null;
        }

        $this->tasks[$name] = [
            'name' => $name,
            'command' => $command,
            'script' => $script,
            'callback' => $callback,
        ];

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function redirect(string $url, int $status = null, bool $exit = null): void
    {
        header('Location: ' . $url, true, $status ?: 301);

        // @codeCoverageIgnoreStart
        if ($exit ?? true) {
            while (ob_get_level()) {
                ob_end_clean();
            }

            exit;
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * @inheritDoc
     */
    public function translate(string $text, string $locale = null): string
    {
        $locale ??= $this->locale();
        if (is_string($locale)) {
            $key = locale_lookup(array_keys($this->translations), $locale);

            return $this->translations[$key][$text] ?? $text;
        }

        return $text;
    }

    /**
     * @inheritDoc
     */
    public function assemble(string $name = null, array $parameters = null, bool $reuse = null): string
    {
        $route = $this->route($name);
        if (!is_array($route)) {
            if ($name === null) {
                throw new RuntimeException('Route without name can only be assembled when a route is matched.');
            } else {
                throw new RuntimeException(sprintf(
                    'No route found with name "%s". Please check the name of the route or give a new route ' .
                    'with the same name.',
                    $name
                ));
            }
        }

        $matched = $this->route();
        $parameters ??= [];
        $reuse ??= true;

        // Merge matched route parameters with parameters argument.
        if ($reuse && is_array($matched) && isset($matched['matches'])) {
            $parameters = array_merge($matched['matches'], $parameters);
        }

        $url = (string)$route['pattern'];
        $constraints = $route['constraints']['path'];
        do {
            // Match optional parts inside out. Match everything inside brackets except an opening or closing bracket.
            $url = preg_replace_callback(
                '~\[(?<optional>[^\[\]]+)]~',
                static function (array $match) use ($name, &$parameters, $constraints): string {
                    $optional = $match['optional'];

                    // Find all parameters in optional part.
                    if (preg_match_all('~:(?<parameter>[a-z]\w*)~i', $optional, $matches)) {
                        foreach ($matches['parameter'] as $parameter) {
                            if (!isset($parameters[$parameter])) {
                                // Parameter is not specified, skip whole optional part.
                                return '';
                            }

                            $value = (string)$parameters[$parameter];
                            if (isset($constraints[$parameter])) {
                                $constraint = $constraints[$parameter];

                                // Check constraint for parameter value.
                                if (!preg_match('~^' . $constraint . '$~i', $value)) {
                                    throw new RuntimeException(sprintf(
                                        'Value "%s" for parameter "%s" is not allowed by constraint "%s" for ' .
                                        'route with name "%s". Please give a valid value.',
                                        $value,
                                        $parameter,
                                        $constraint,
                                        $name
                                    ));
                                }
                            }

                            // Replace parameter definition with value.
                            $optional = str_replace(':' . $parameter, $value, $optional);

                            // Unset used parameter value.
                            unset($parameters[$parameter]);
                        }
                    }

                    return $optional;
                },
                $url ?: '',
                -1,
                $count
            );
        } while ($count > 0);

        // Find all required parameters.
        $url = preg_replace_callback(
            '~:(?<parameter>[a-z]\w*)~i',
            static function (array $match) use ($name, &$parameters, $constraints): string {
                $parameter = $match['parameter'];
                if (!isset($parameters[$parameter])) {
                    throw new RuntimeException(sprintf(
                        'Required parameter "%s" for route name "%s" is missing. Please give the required ' .
                        'parameter or change the route URL.',
                        $parameter,
                        $name
                    ));
                }

                $value = (string)$parameters[$parameter];
                if (isset($constraints[$parameter])) {
                    $constraint = $constraints[$parameter];

                    // Check constraint for parameter value.
                    if (!preg_match('~^' . $constraint . '$~i', $value)) {
                        throw new RuntimeException(sprintf(
                            'Value "%s" for parameter "%s" is not allowed by constraint "%s" for route with ' .
                            'name "%s". Please give a valid value.',
                            $value,
                            $parameter,
                            $constraint,
                            $name
                        ));
                    }
                }

                // Unset used parameter value.
                unset($parameters[$parameter]);

                return $value;
            },
            $url ?: ''
        );

        // Remove asterisk from URL.
        $url = str_replace('*', '', $url ?: '');

        // Filter null values from query string parameters.
        $parameters = array_filter($parameters, function ($value) {
            return $value !== null;
        });

        $query = [];
        $constraints = $route['constraints']['query'];
        foreach ($constraints as $parameter => $constraint) {
            if (isset($parameters[$parameter])) {
                $value = (string)$parameters[$parameter];
                if (!preg_match('~^' . $constraint . '$~i', $value)) {
                    throw new RuntimeException(sprintf(
                        'Value "%s" for query string parameter "%s" is not allowed by constraint "%s" for ' .
                        'route with name "%s". Please give a valid value.',
                        $value,
                        $parameter,
                        $constraint,
                        $name
                    ));
                }

                $query[$parameter] = $value;
            }
        }

        if (is_string($url) && !empty($query)) {
            $url .= '?' . http_build_query($parameters);
        }

        return $url;
    }

    /**
     * @inheritDoc
     */
    public function match(string $path, string $method, string $locale = null): ?array
    {
        $url = parse_url($path);
        if (is_array($url) && isset($url['path'])) {
            foreach ($this->routes as $route) {
                // Only check for HTTP methods when provided.
                if (!in_array($method, $route['methods'], true)) {
                    continue;
                }

                // Replace asterisk to match a character.
                $pattern = str_replace('*', '(.*)', (string)$route['pattern']);

                // Translate text inside curly brackets.
                $locale ??= $this->locale();
                if (is_string($locale) || is_null($locale)) {
                    do {
                        $pattern = preg_replace_callback(
                            '~{(?<text>[^{}]+)}~i',
                            function (array $match) use ($locale): string {
                                return $this->translate($match['text'], $locale);
                            },
                            $pattern ?: '',
                            -1,
                            $count
                        );
                    } while ($count > 0);
                }

                do {
                    // Replace everything inside brackets with an optional regular expression group inside out.
                    $pattern = preg_replace('~\[([^\[\]]+)]~', '($1)?', $pattern ?: '', -1, $count);
                } while ($count > 0);

                // Replace all parameters with a named regular expression group which will not match a forward slash or
                // the parameter constraint.
                $constraints = $route['constraints']['path'];
                $pattern = preg_replace_callback(
                    '~:(?<parameter>[a-z]\w*)~i',
                    static function (array $match) use ($constraints): string {
                        return sprintf('(?<%s>%s)', $match['parameter'], $constraints[$match['parameter']]);
                    },
                    $pattern ?: ''
                );

                if (preg_match('~^' . $pattern . '$~', $url['path'], $matches, PREG_UNMATCHED_AS_NULL)) {
                    $route['matches'] = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                    // Validate query string when specified in route pattern.
                    parse_str($url['query'] ?? '', $query);
                    if ($route['restricted']) {
                        $constraints = $route['constraints']['query'];

                        foreach ($query as $parameter => $value) {
                            if (!array_key_exists($parameter, $constraints) ||
                                !preg_match('~^' . $constraints[$parameter] . '$~', $value)) {
                                continue 2;
                            }

                            $query[$parameter] = trim($value);
                        }

                        // Set null values for unmatched query string parameters.
                        $route['matches'] = array_merge(
                            $route['matches'],
                            array_fill_keys(array_keys($constraints), null),
                            $query
                        );
                    }

                    return $route;
                }
            }
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function parse(array $arguments): ?array
    {
        $arguments = array_slice($arguments, 1);
        foreach ($this->tasks as $task) {
            $command = explode(' ', $task['command']);

            // Skip if there are more arguments than the command defines.
            if (count($arguments) > count($command)) {
                continue;
            }

            $parsed = [];
            foreach ($command as $index => $part) {
                $argument = $arguments[$index] ?? null;

                // Match static word.
                if (preg_match('/^[a-z]\w*$/i', $part)) {
                    if ($part !== $argument) {
                        continue 2;
                    }

                    continue;
                }

                // Match required parameter.
                if (preg_match('/^<(?<parameter>[a-z]\w*)>$/i', $part, $matches)) {
                    if (!$argument) {
                        continue 2;
                    }

                    $parsed[$matches['parameter']] = $argument;

                    continue;
                }

                // Match optional parameter.
                if (preg_match('/^\[<(?<parameter>[a-z]\w*)>]$/i', $part, $matches)) {
                    if ($argument) {
                        $parsed[$matches['parameter']] = $argument;
                    }

                    continue;
                }

                throw new RuntimeException(sprintf(
                    'Failed to parse command. Part "%s" is not a valid static word "word", a required ' .
                    'parameter "<parameter>" or an optional parameter "[<parameter>]".',
                    $part
                ));
            }

            $task['parsed'] = $parsed;

            return $task;
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function render(string $filename, object $newThis = null, bool $buffer = null): ?string
    {
        $buffer ??= true;
        $closure = function () use ($filename, $buffer) {
            if ($buffer) {
                ob_start();
            }

            try {
                if (is_file($filename)) {
                    include $filename;
                } else {
                    $root = $this->root();
                    if (is_string($root) && is_file($root . $filename)) {
                        include $root . $filename;
                    } else {
                        if (!$buffer) {
                            throw new RuntimeException(sprintf(
                                'File "%s" not found as an absolute path or in the root directory.',
                                $filename
                            ));
                        }

                        echo $filename;
                    }
                }

                if ($buffer) {
                    return ob_get_clean();
                }
            } catch (Throwable $throwable) {
                // Clean any output for only the error message to show.
                if ($buffer) {
                    ob_end_clean();
                }

                throw $throwable;
            }

            return null;
        };

        return $closure->call($newThis ?: $this) ?: null;
    }

    /**
     * @inheritDoc
     */
    public function call(Closure $callback, object $newThis, array $arguments = null)
    {
        $reflection = new ReflectionFunction($callback);
        foreach ($reflection->getParameters() as $parameter) {
            $arguments ??= [];
            $name = $parameter->getName();
            if (!array_key_exists($name, $arguments)) {
                if ($parameter->isDefaultValueAvailable()) {
                    $args[] = $parameter->getDefaultValue();
                } elseif ($parameter->allowsNull()) {
                    $args[] = null;
                } else {
                    throw new RuntimeException(sprintf(
                        'Required argument "%s" for callback is not provided in the arguments array, does not ' .
                        'has a default value and is not nullable. Please give the missing argument or give it a ' .
                        'default value.',
                        $name
                    ));
                }
            } else {
                $args[] = $arguments[$name];
            }
        }

        return $callback->call($newThis, ...$args ?? []);
    }

    /**
     * @inheritDoc
     */
    public function run(
        string $path = null,
        string $method = null,
        array  $arguments = null,
        string $locale = null
    ): string {
        try {
            $config = [];

            $pattern = $this->config();
            if (is_string($pattern)) {
                $filenames = glob($pattern, GLOB_BRACE);
                if (is_array($filenames)) {
                    foreach ($filenames as $filename) {
                        if (is_file($filename)) {
                            $config = array_merge($config, require $filename);
                        }
                    }
                }
            }

            $pattern = $this->translation();
            if (is_string($pattern)) {
                $filenames = glob($pattern, GLOB_BRACE);
                if (is_array($filenames)) {
                    foreach ($filenames as $filename) {
                        if (is_file($filename)) {
                            $this->translations[pathinfo($filename, PATHINFO_FILENAME)] = require $filename;
                        }
                    }
                }
            }

            $start = $this->start();
            if ($start instanceof Closure) {
                $return = $this->call($start, $this, [
                    'config' => $config,
                ]);
                if (is_string($return)) {
                    return $return;
                }
            }

            $render = '';
            if ($path || isset($_SERVER['REQUEST_URI'])) {
                $route = $this->match($path ?: $_SERVER['REQUEST_URI'], $method ?: $_SERVER['REQUEST_METHOD'], $locale);
                if ($route) {
                    $this->matched = $route;
                    $this->data('atto.route', $route['matches']);

                    if ($route['view']) {
                        $this->view($route['view']);
                    }

                    $before = $this->before();
                    if ($before instanceof Closure) {
                        $return = $this->call($before, $this, $route['matches'] ?? []);
                        if (is_string($return)) {
                            return $return;
                        }
                    }

                    $callback = $route['callback'];
                    if ($callback instanceof Closure) {
                        $return = $this->call($callback, $this, $route['matches'] ?? []);
                        if (is_string($return)) {
                            return $return;
                        }
                    }

                    $after = $this->after();
                    if ($after instanceof Closure) {
                        $return = $this->call($after, $this, $route['matches'] ?? []);
                        if (is_string($return)) {
                            return $return;
                        }
                    }
                }

                $view = $this->view();
                if (is_string($view)) {
                    $render = $this->render($view, $this);

                    $this->data('atto.view', $render);
                }

                $layout = $this->layout();
                if (is_string($layout)) {
                    $render = $this->render($layout, $this);
                }
            } elseif ($arguments || isset($_SERVER['argv'])) {
                $task = $this->parse($arguments ?: $_SERVER['argv']);
                if ($task) {
                    if ($task['callback'] instanceof Closure) {
                        $return = $this->call($task['callback'], $this, $task['parsed'] ?? []);
                        if (is_string($return)) {
                            return $return;
                        }
                    }

                    if (is_string($task['script'])) {
                        $this->render($task['script'], $this, false);
                    }
                } else {
                    $render .= sprintf('AttoPHP Console (version %s)', AttoPHPInterface::VERSION) . PHP_EOL;
                    $render .= PHP_EOL;

                    if (count($arguments ?: $_SERVER['argv']) > 1) {
                        $render .= sprintf("\033[31m%s\033[0m", 'No task found for command.') . PHP_EOL;
                        $render .= PHP_EOL;
                    }

                    if ($this->tasks) {
                        $render .= 'Tasks (command <required> [<optional>]):' . PHP_EOL;

                        foreach ($this->tasks as $task) {
                            $render .= ' - ' . $task['command'] . PHP_EOL;
                        }
                    } else {
                        $render .= 'No tasks available.' . PHP_EOL;
                    }

                    $render .= PHP_EOL;
                }
            }

            $finish = $this->finish();
            if ($finish instanceof Closure) {
                $return = $this->call($finish, $this, [
                    'render' => $render,
                ]);
                if (is_string($return)) {
                    return $return;
                }
            }

            return (string)$render;
        } catch (Throwable $throwable) {
            try {
                $error = $this->error();
                if ($error instanceof Closure) {
                    $return = $this->call($error, $this, [
                        'throwable' => $throwable,
                    ]);
                    if (is_string($return)) {
                        return $return;
                    }
                }
            } catch (Throwable $throwable) {
                return $throwable->getMessage();
            }

            return $throwable->getMessage();
        }
    }
}
