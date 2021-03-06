<?php
/*
 * engine/bootstrap.php
 *
 * Copyright (C) 2018 Dr.NP <np@bsgroup.org>
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 * 3. Neither the name ``Dr.NP'' nor the name of any other
 *    contributor may be used to endorse or promote products derived
 *    from this software without specific prior written permission.
 *
 * libgmr IS PROVIDED BY Dr.NP ``AS IS'' AND ANY EXPRESS
 * OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED.  IN NO EVENT SHALL Dr.NP OR ANY OTHER CONTRIBUTORS
 * BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR
 * BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
 * WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR
 * OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF
 * ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

/**
 * @file engine/bootstrap.php
 * @package Husky/php/base
 * @author Dr.NP <np@bsgroup.org>
 * @since 05/30/2018
 * @version 0.0.1
 */

require __DIR__ . '/../conf/constants.php';
require __DIR__ . '/utils/misc.php';
require __DIR__ . '/utils/pagination.php';
$default_settings = require __DIR__ . '/../conf/settings.default.php';

// Load framework and other vendors
require __DIR__ . '/../vendor/autoload.php';

// Configurations
if (isset($app_settings) && \is_array($app_settings))
{
    $settings = \M($default_settings, $app_settings);
}
else
{
    $settings = $default_settings;
}

$sub_settings = 'settings_' . $app_env;
if (isset($$sub_settings) && \is_array($$sub_settings))
{
    $settings = \M($settings, $$sub_settings);
}

// Framework instance
$app = new \Slim\App(['settings' => $settings]);
$container = $app->getContainer();
$container['result'] = [];
$container['result_raw'] = false;
$container['result_binary'] = null;
$container['result_content_type'] = \DEFAULT_CONTENT_TYPE;
$container['result_code'] = \HuskyResult::OK;
$container['result_http_code'] = 200;
$container['result_message'] = 'SUCCESS';
$container['result_links'] = [];
$container['result_cached'] = false;
$container['http_auth_type'] = null;
$container['http_auth_user'] = null;
$container['http_auth_pass'] = null;
$container['http_auth_token'] = null;
$container['http_auth_status'] = \HuskyAuth::NO_NEED;
$container['api_version'] = null;

$app_name = \trim($settings['app']['name']);
$container['app_name'] = $app_name;
$container['runtime'] = [];
$container['default_404_page'] = null;

// Awesome here ...
\gc_disable();

// Dependencies
$settings_dependencies = $settings['runtime']['dependencies'];
$ds = [];
foreach ($settings_dependencies as $dependency => $c)
{
    if (\is_string($dependency))
    {
        $file = __DIR__ . '/dependencies/' . $dependency . '.dp.php';
        if (\file_exists($file))
        {
            $fn = require $file;
            $container[$dependency] = $fn;
            $ds[$dependency] = true;
        }
    }
}

// Load middlewares
$settings_middlewares = $settings['runtime']['middlewares'];
$ms = [];
foreach ($settings_middlewares as $middleware => $c)
{
    if (\is_string($middleware))
    {
        $file = __DIR__ . '/middlewares/' . $middleware . '.mw.php';
        if (\file_exists($file))
        {
            $fn = require $file;
            if (\is_callable($fn))
            {
                $app->add($fn);
                $ms[$middleware] = true;
            }
        }
    }
}

$container['runtime'] = [
    'dependencies' => $ds,
    'middlewares' => $ms
];

// Common routes
if ($settings['app']['enable_debug'])
{
    $app->get('/debug:settings', function($request, $response) {
        $this['result'] = $this->get('settings')->all();

        return $response;
    })->setName('Debug::Settings');

    $app->get('/debug:routes', function($request, $response) {
        $routes = $this->get('router')->getRoutes();
        $res = [];
        foreach ($routes as $route)
        {
            $res[] = [
                'name' => $route->getName(),
                'methods' => $route->getMethods(),
                'pattern' => $route->getPattern(),
                'arguments' => $route->getArguments()
            ];
        }
        $this['result'] = $res;
        return $response;
    })->setName('Debug::RoutesList');

    $app->get('/debug:dependencies', function($request, $response) {
        $this['result'] = \V($this->get('runtime'), 'dependencies', []);
        return $response;
    })->setName('Debug::DependenciesList');

    $app->get('/debug:middlewares', function($request, $response) {
        $this['result'] = \V($this->get('runtime'), 'middlewares', []);
        return $response;
    })->setName('Debug::MiddlewaresList');
}

// Error handlers
$container['notFoundHandler'] = function($c) {
    return function($request, $response) use ($c) {
        if (\is_string($c['default_404_page']))
        {
            return $response->withRedirect($c['default_404_page'], 301);
        }

        $c['result_code'] = \HuskyResult::ROUTE_NOT_FOUND;
        $c['result_http_code'] = 404;
        $c['result_message'] = 'Route not found';
        if ($c->get('settings')['app']['enable_debug'])
        {
            $c['result_links'] = [
                'Routes' => '/debug:routes'
            ];
        }

        return $response;
    };
};

$container['notAllowedHandler'] = function($c) {
    return function($request, $response) use ($c) {
        $c['result_code'] = \HuskyResult::METHOD_NOT_ALLOWED;
        $c['result_http_code'] = 405;
        $c['result_message'] = 'Method not allowed';
        if ($c->get('settings')['app']['enable_debug'])
        {
            $c['result_links'] = [
                'Routes' => '/debug:routes'
            ];
        }

        return $response;
    };
};

$container['phpErrorHandler'] = function($c) {
    return function($request, $response, $error) use ($c) {
        $c['result_code'] = \HuskyResult::INTERNAL_ERROR;
        $c['result_http_code'] = 500;
        $c['result_message'] = 'Internal error';
        $c['result'] = $error;

        return $response;
    };
};

return $app;

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: sw=4 ts=4 fdm=marker
 * vim<600: sw=4 ts=4
 */
