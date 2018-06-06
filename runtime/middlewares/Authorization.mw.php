<?php
/*
 * runtime/middlewares/Authorization.mw.php
 *
 * Copyright (C) 2016 Dr.NP <np@bsgroup.org>
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
 * @file runtime/middlewares/Authorization.mw.php
 * @package Husky/php/base
 * @author Dr.NP <np@bsgroup.org>
 * @since 05/30/2018
 * @version 0.0.1
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$mw_Authorization = function(Request $request, Response $response, $next) use ($container) {
    $config = $container->get('settings')['runtime']['middlewares']['Authorization'];
    $authorization_user = $request->getHeader('HTTP_AUTHORIZATION');
    if (\is_array($authorization_user) && isset($authorization_user[0]) && \is_string($authorization_user[0]))
    {
        $parts = \explode(' ', \trim($authorization_user[0]));
        if (!\is_array($parts) || sizeof($parts) < 2)
        {
            // Invalid header
            $container['result_message'] = 'Invalid authorization header';
            $container['result_code'] = \HuskyResult::HTTP_AUTHORIZATION_FAILED;
            $container['result_http_code'] = 403;

            return $response;
        }

        list($method, $value) = $parts;
        switch ($method)
        {
            case 'basic' :
                // U / P basic authorization
                $raw = @\base64_decode(\trim($value));
                if ($raw)
                {
                    list($user, $pass) = \explode(':', $raw, 2);
                    if ($user && $pass)
                    {
                        $container['http_auth_type'] = 'basic';
                        $container['http_auth_user'] = \trim($user);
                        $container['http_auth_pass'] = \trim($pass);
                    }
                }

                break;
            case 'bearer' :
                // Access token
                $token = \trim($value);
                if ($token)
                {
                    $container['http_auth_type'] = 'bearer';
                    $container['http_auth_token'] = $token;
                }

                break;
            default :
                // Not supported
                break;
        }
    }

    // +++ NEXT +++
    $response = $next($request, $response);
    // --- NEXT ---

    switch ($container->get('http_auth_status'))
    {
        case \HuskyAuth::NEED :
            $container['result_code'] = \HuskyResult::ERROR_AUTHORIZATION_NEEDED;
            $container['result_http_code'] = 401;
            $container['result_message'] = 'HTTP authorization needed';
            $container['result'] = [];
            break;
        case \HuskyAuth::INVALID :
            $container['result_code'] = \HuskyResult::ERROR_AUTHORIZATION_INVALID;
            $container['result_http_code'] = 403;
            $container['result_message'] = 'HTTP authorization invalid';
            $container['result'] = [];
            break;
        case \HuskyAuth::PERMISSION :
            $container['result_code'] = \HuskyResult::ERROR_AUTHORIZATION_PERMISSION;
            $container['result_http_code'] = 403;
            $container['result_message'] = 'HTTP authorization permission denied';
            $container['result'] = [];
            break;
        case \HuskyAuth::OK :
        case \HuskyAuth::NO_NEED :
        default :
            break;
    }

    return $response;
};

return $mw_Authorization;

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: sw=4 ts=4 fdm=marker
 * vim<600: sw=4 ts=4
 */