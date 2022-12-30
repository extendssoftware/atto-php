# AttoPHP

[![Latest Stable Version](http://poser.pugx.org/extendssoftware/atto-php/v)](https://packagist.org/packages/extendssoftware/atto-php)
[![Total Downloads](http://poser.pugx.org/extendssoftware/atto-php/downloads)](https://packagist.org/packages/extendssoftware/atto-php)
[![Latest Unstable Version](http://poser.pugx.org/extendssoftware/atto-php/v/unstable)](https://packagist.org/packages/extendssoftware/atto-php)
[![License](http://poser.pugx.org/extendssoftware/atto-php/license)](https://packagist.org/packages/extendssoftware/atto-php)
[![PHP Version Require](http://poser.pugx.org/extendssoftware/atto-php/require/php)](https://packagist.org/packages/extendssoftware/atto-php)
[![Build Status](https://img.shields.io/github/actions/workflow/status/extendssoftware/atto-php/build.yml?branch=main)](https://github.com/extendssoftware/atto-php/blob/main/.github/workflows/build.yml)

AttoPHP is a tool based on the [builder pattern](https://en.wikipedia.org/wiki/Builder_pattern) to configure, route and
render a website with ease.

- [1. Introduction](#1-introduction)
- [2. Requirements](#2-requirements)
- [3. Installation](#3-installation)
- [4. Features](#4-features)
- [5. Usage](#5-usage)
    - [5.1 Routes](#51-routes)
        - [5.1.1 Name](#511-name)
        - [5.1.2 Pattern match and assemble](#512-pattern--match-and-assemble-)
        - [5.1.3 Constraints and HTTP methods](#513-constraints-and-http-methods)
        - [5.1.4 View and callback](#514-view-and-callback)
        - [5.1.5 Examples](#515-examples)
    - [5.2 Tasks](#52-tasks)
        - [5.2.1 Name](#521-name)
        - [5.2.2 Command](#522-command)
        - [5.2.3 Script and callback](#523-script-and-callback)
        - [5.2.4 Examples](#524-examples)
    - [5.3 Templates](#53-templates-and-scripts)
        - [5.3.1 Root directory](#531-root-directory)
        - [5.3.2 Inline template](#532-inline-templates)
        - [5.3.3 Layout and view](#533-layout-and-view)
    - [5.4 Data container](#54-data-container)
    - [5.5 Callbacks](#55-callbacks)
        - [5.5.1 Start](#551-start)
        - [5.5.2 Before](#552-before)
        - [5.5.3 Route](#553-route)
        - [5.5.4 After](#554-after)
        - [5.5.5 Finish](#555-finish)
        - [5.5.6 Error](#556-error)
    - [5.6 Config](#56-config)
- [6. Control flow](#6-control-flow)
- [7. Error handling](#7-error-handling)

## 1. Introduction

There are lots of other solutions out there, like [Slim Framework](https://github.com/slimphp/Slim),
[bramus/router](https://github.com/bramus/router), [Twig](https://github.com/twigphp/Twig) and many others. Each with
their own specialties, dependencies and all recommend installing with Composer.

AttoPHP is not as comprehensive as the others, but it is fast and simple to use. Once you understand some core
principles, you are good to go. Some very basic PHP knowledge is preferred, but if you can read and modify some simple
PHP, you will come to an end.

## 2. Requirements

- PHP ^8.1
- [URL rewriting](https://en.wikipedia.org/wiki/Rewrite_engine)

## 3. Installation

It is very easy to install AttoPHP with [Composer](https://getcomposer.org/):

```
$ composer require extendssoftware/atto-php
```

## 4. Features

Not that much, but just enough to get your site started:

- Match and assemble routes
- Redirect to URL
- Callbacks on start, route, finish and error
- Render PHP templates (layout, view and partial) and scripts
- Data container
- Config loading
- Text translation

In every callback, template and script AttoPHP is the current object ```$this```, so whatever you are doing, you can use
these features.

Everybody familiar with [jQuery](https://jquery.com/) knows how a combined get/set method works. Let's consider the
```view``` method. When this method is called without argument, the current set view will be returned, or null when no
view set. When this method is called with a view filename, the view will be set. Normally, when using proper
[OOP](https://en.wikipedia.org/wiki/Object-oriented_programming), this will be two methods, ```getView``` and
```setView```. AttoPHP uses combined methods to keep it compact and fast.

## 5 Usage

After everything is set up, the method ```run``` needs to be called to run AttoPHP and get the rendered content back.

### 5.1 Routes

It all begins with matching a URL to a view and/or callback. A route can have the following properties:

- Name
- Pattern
- View
- Callback

#### 5.1.1 Name

The route name can be anything you like. The route name is required and used to the get the route during assembling, for
example in a template. With the option to assemble routes, there is no need to change a URL in every place of the
website. Just change the URL, and it will change everywhere as long as you keep the name the same. This can also be done
in a script, for example, when rendering a sitemap.

It is possible to assemble the current route by not providing a route name for method ```assemble```. This can only be
done when AttoPHP has run, a route is matched and an HTTP request. Provided parameters will overwrite the parameters
from the matched route. An asterisk in the route pattern is not assembled.

The matched route parameters will be merged with the provided parameters when a named route is being assembled.

#### 5.1.2 Pattern (match and assemble)

The pattern will be used to check if the route matches the URL. Route matching is done for the path of the URL,
everything behind the top-level domain (TLD) ```/foo/bar/baz```. The query string will also be matched when the pattern
consist of a query string ```/foo/bar/baz?foo=```. If not, query string matching will be skipped and every parameter and
value is allowed.

The pattern path can consist of static text, parameters and optional parts. A required parameter starts with a colon
followed by an alphabetical character and can consist of alphanumeric characters, and an underscore ```/blog/:blogId```.
Parameters outside optional parts are required and must be present in the URL in order for the route to match.

Optional path parts are surrounded with square brackets ```/blog[/:page]``` and can be nested
```/blog/:blogId[/comments[/:page]]```. Parameters inside an optional part are not required. An optional part will only
match when all the parameters inside the part are matched. Optional parts processes from the outside in. For the
pattern ```/blog/:blogId[/comments[/:page]]``` the part with page will only match when the page is specified in the URL.
The comments part will match with or without the page part.

This also applies to route assembly. An optional part will only assemble when all the parameters are specified and when
every nested optional part is also assembled. The route part with the parameter baz from the route
```/foo[/:bar[/:baz]]``` will only assemble when the parameter bar is also specified.

Matched path parameters are available in the data container prefixed with ```atto.route```. The path parameter
```blogId``` for route pattern ```/blog/:blogId``` can be accessed with path ```atto.route.blogId```. Unmatched path
parameters are available as null value.

Matched query string parameters are also available in the data container prefixed with ```atto.route```. The query
string parameter ```page``` for route pattern ```/blog?page=``` can be accessed with path ```atto.route.query```.
Unmatched query string parameter are available as null value. Only specified query string parameters are accessible.

Parts inside curly brackets ```{``` and ```}``` will be translated before matching occurs. Before the URL
```/blog/{page}``` will be matched, the part ```{page}``` will be translated to the global or provided locale. This also
applies to query string parameters. The query string parameter ```?{page}=<\d+>``` will be translated before route
matching.

#### 5.1.3 Constraints and HTTP methods

Constraints can be specified for path parameters. A constraint is a
[regular expression](https://en.wikipedia.org/wiki/Regular_expression) without the delimiters ```[a-z0-9-]+```. A
constraint must be added after the parameter name between the ```<``` and ```>``` characters and contain the regular
expression ```/blog/:page<\d+>```. A route will not match when the parameter value does not match the constraint. When
no constraint is specified, the default constraint is ```[^/]+```, match everything till the next forward slash, or the
end of the URL if there is none left.

An asterisk ```*``` can be used in a route pattern to match all characters in the URL. The route ```/foo/*``` will match
```/foo/bar```, but wil also match ```/foo/baz```. A route like ```/*``` will match any URL. An asterisk matches zero to
unlimited characters. It is recommended to add a catch-all route ```/*``` as last route. This route can be used to
redirect to a proper 404 page or show a 404 page for the current URL.

Constraints can also be specified for query string parameters in the same way as path parameters ```/blog?page=<\d+>```.
When no constraint is specified, or the equals sign is omitted, the default constraint is ```.*```. All the query string
parameters and values are allowed when no query string is specified in the route pattern ```/blog```. No query string is
allowed when a combination of the question mark and exclamation mark is specified ```/blog?!```.

When assembling a route, an error will occur when the constraint does not match the parameter value. The letter ```a```
is not allowed when the constraint only allows digits ```\d+```.

[HTTP methods](https://en.wikipedia.org/wiki/Hypertext_Transfer_Protocol#Request_methods) can be added to the beginning
of the route pattern, must be divided by a pipe and have a trailing whitespace character
```POST|DELETE /blog/:blogId```. When no HTTP method is specified, all methods will match the route. The HTTP method for
a route is not included during route assembly. An HTTP method can also be used for a route with an asterisk.

#### 5.1.4 View and callback

The view for a route is optional. When a route matches and has a view set, this view will be set to AttoPHP for later
use.

When you want any of the URL parameters available in the callback, you need to add callback arguments matching the exact
same name as the route parameters. The value will be of type string. When a route parameter is optional, the argument
must have a default value or allow null. For the parameter ```:blogId``` you have to add the argument ```$blogId```. The
order of the arguments does not matter.

To get the matched route in the callback or a template, the method ```route``` must be called without any arguments. The
matched route also contains all the matched URL parameters.

#### 5.1.5 Examples

```
/blog[/:page<\d+>]
```

The URLs ```/blog``` and ```/blog/1``` are allowed. The URL ```/blog/a``` will not match because the constraint only
allows digits. Page parameter is optional. Will match any HTTP method.

```
POST|DELETE /blog/:blogId
```

The HTTP methods ```POST``` and ```DELETE``` are allowed for this route. ```blogId``` will match everything except a
forward slash. It is considered a good practise to always add a constraint to avoid strange behavior and URLs for SEO.

```
/*
```

Catch-all route, will match any URL for any HTTP method.

```
/blog/*.html
```

Will match any route that begins with ```/blog/``` and ends with ```.html```.

### 5.2 Tasks

Background tasks are an import part of a website or web application. A task can have the following properties:

- Name
- Command
- Script and callback
- Examples

#### 5.2.1 Name

The task name can anything you like. The task name is only required to get and set the task. In contrast to a route the
name is not used for other purposes.

#### 5.2.2 Command

The command is used to check if the task matches the command line arguments that are passed without the first argument,
which is the filename.

A task command can contain static words, required parameters and optional parameters. All must start with an
alphabetical character and can consist of alphanumeric characters, and an underscore.

A required parameter is surrounded by angle brackets ```<limit>```. Optional parameters are surrounded with square
brackets and angle brackets ```[<limit>]```. Parameters can not have a constraint. Values with spaces need to be
surrounded by quotes.

#### 5.2.3 Script and callback

The script for a task is optional. When a task matches and has a script set, this script will be directly called and the
output from the script is not buffered and output will be shown immediately.

When you want any of the command parameters available in the callback, you need to add callback arguments matching the
exact same name as the command parameters. The value will be of type string. When a route parameter is optional, the
argument must have a default value or allow null. For the parameter ```:importId``` you have to add the
argument ```$importId```. The order of the arguments does not matter.

#### 5.2.4 Examples

```
import feed <id>
```

Command with required parameter id. Command will match ```import feed 15```, but will not match when required parameter
is omitted ```import feed```.

```
process queue [<limit>]
```

Command with optional parameter limit. Command will match ```process queue``` and ```process queue 10```.

```
generate word cloud <id> latest [<limit>]
```

Command with mixed static words, required parameters and optional parameter. Command will match
```generate word cloud 8 latest 10``` and ```generate word cloud 8 latest```, but not ```generate word cloud 8```.

### 5.3 Templates and scripts

PHP include is used to render a template or script with the ```render``` method. AttoPHP is set as the current
object ```$this``` for the file to render. AttoPHP calls this method for the layout and view, when set. When you call
the ```render``` method manually, you have to specify the current object for the file. When you render a file from a
template, script or a callback, you can pass ```$this``` as the second parameter or your own object.

#### 5.3.1 Root directory

To always set the full path to a filename can be cumbersome, to counter this, the method ```root``` can be called with a
path to the root directory.

When a template or script is rendered, AttoPHP will check if the specified filename is a file. If so, the file will be
rendered and returned. When it is not a file and a root directory is set, the directory is checked if the file exists.
If so, render that file.

#### 5.3.2 Inline templates

A template is considered an inline template when no file can be found at the absolute or relative path. This can be
useful when templates are stored in a database. With this method PHP rendering is not available, and the template will
be directly returned by the ```render``` method. If you want to parse this template, you can use a template parser in
combination with the ```render``` method.

This will not work for scripts, an exception will be thrown when a script can not be found.

#### 5.3.3 Layout and view

AttoPHP has methods to set a layout and a view, ```layout``` and ```view``` respectively. If set, the view will be
rendered before the layout is, if set. The rendered view will be available in the layout as the data container path
```atto.view```.

### 5.4 Data container

AttoPHP has a data container which can be called with the method ```data```. Any data you like can be set to the data
container and used in every callback, template or script. The path to set the data for must use dot notation with a
colon, dot or forward slash as separator. They can be used interchangeably, but it is not recommended. The characters
between the separator can only consist of a-z and 0-9, case-insensitive.

Keep in mind that every separator makes the following key a nested value for the previous key. The value for path
```foo.bar.baz``` will be overwritten when a value for path ```foo.bar``` will be set. When a value for
```foo.bar.qux``` is set, it will be added next to ```baz```.

The advantage of dot notation is the grouping of data. For example, you can group all the data for the layout,
```layout.title``` for the title and ```layout.description``` for the description.

### 5.5 Callbacks

If a callback returns a string value, the value will be short-circuited by the AttoPHP method ```run``` and execution is
stopped afterwards.

The current object ```$this``` for a callback is the AttoPHP class. All the functionality AttoPHP provides is available
in the callback.

#### 5.5.1 Start

This callback is called after config is loaded and has a single argument call ```$config```. It will contain the loaded
config when the config path pattern is set and config files are loaded, or an empty array when no config path pattern is
set.

#### 5.5.2 Before

This callback is called when a route is matched and before the optional callback from the route is called. This callback
gets the same arguments as the route callback.

#### 5.5.3 Route

This callback is called when a route is matched and has a callback specified. The route parameters can be specified as
arguments. For example, the parameter ```:blogId``` will be available as the argument ```$blogId```.

#### 5.5.4 After

This callback is called when a route is matched and after the optional callback from the route is called. This callback
gets the same arguments as the route callback.

#### 5.5.5 Finish

This callback is called after the layout and/or view are rendered. The rendered content will be available as argument
with the name ```$render```.

#### 5.5.6 Error

This callback is called when an error occurs. The occurred error is available as argument with the name
```$throwable```.

### 5.6 Config

When a config path pattern is set with the ```config``` method files will be loaded before the start callback is called.
The PHP method ```glob()``` is being used for loading the config files and the flag ```GLOB_BRACE``` is given. Config
files must be PHP files, return an array and are loaded using ```require```. Directories are ignored and files are
merged non-recursive.

### 5.7 Translation

When a translation path pattern is set with the ```translation``` method, files will be loaded before the start callback
is called. The PHP method ```glob()``` is being used for loading the config files and the flag ```GLOB_BRACE``` is
given. Translation files must be PHP files, return an array and are loaded using ```require```. Directories are ignored.
The filename without the extension will be used as the locale for the loaded file, ```nl-nl.php``` will get the
locale ```nl-nl```.

Text translation can be done with the ```translate``` method when a locale is set using the ```locale``` method.
Additionally, a locale can be passed to the ```translate``` method as a parameter which overrules the global locale set
with the ```locale``` method. It is not required for the global locale or locale parameter to be exact the same as the
filename. AttoPHP uses the PHP method ```locale_lookup``` to find a matching locale. The loaded locale ```nl``` can be,
for example, used for the global or provided locale ```nl-be``` or ```nl-nl```.

When there is no locale set or provided, no matching translation found or the text is not found as array key, the
unaltered text will be returned.

## 6 Control flow

To get a basic idea of how AttoPHP works the [happy path](https://en.wikipedia.org/wiki/Happy_path) is explained here:

- If set, load config files from config path pattern
- If set, load translation files from translation path pattern
- If set, call start callback
    - If truthy return value, return value and stop execution
- Check request type:
    - If HTTP:
        - Find a matching route:
            - If found:
                - Set matched route to Atto
                - If set, set the view to Atto
                - If set, call the route callback
                    - If truthy return value, return value and stop execution
        - If set, render view
            - Set rendered content to data container with path ```atto.view```
        - If set, render layout
    - If console:
        - Check console arguments:
            - If specified:
                - Parse arguments to find a task:
                    - If found:
                        - If set, call the task callback
                            - If truthy return value, return value and stop execution
                        - If set, render script
                    - If not found:
                        - Set error and all available tasks to rendered content
            - If not specified:
                - Set available tasks to rendered content
- If set, call finish callback
    - If truthy return value, return value and stop execution
- Return rendered content

On error:

- If set, call error callback
    - If truthy return value, return value and stop execution
    - If callback error, return error message and stop execution
- Return error message

## 7 Error handling

AttoPHP catches all the errors that occur while running. If there is no callback error, or the callback doesn't return a
truthy value, the error message from the original error will be returned. If the error occurred while rendering a
template or script, the output if cleaned before the error will be returned. So, an error wil never show deeply nested
inside an HTML element for example.