<?php
/*
 * runtime/middlewares/Result.mw.php
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
 * @file runtime/middlewares/Result.mw.php
 * @package Husky/php/base
 * @author Dr.NP <np@bsgroup.org>
 * @since 05/30/2018
 * @version 0.0.1
 */

namespace Husky\Base\Runtime\Middwares;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$mw_Result = function(Request $request, Response $response, $next) use ($container) {
    $config = $container->get('settings')['runtime']['middlewares']['Result'];
    $result = null;
    $body = '';

    // Content-Type
    $result_content_type_user = \filter_input(\INPUT_GET, '_content_type');
    $named = [];
    if ($result_content_type_user)
    {
        $named[] = $result_content_type_user;
    }
    $ct = $request->getContentType();
    if (\is_array($ct) && isset($ct[0]) && \is_string($ct[0]))
    {
        $cts = \explode(',', $ct[0]);
        foreach ($cts as $ct_entry)
        {
            $pos = \strpos($ct_entry, ';');
            if ($pos !== false)
            {
                $ct_entry = \substr($ct_entry, 0, \strpos($ct_entry, ';'));
            }

            $ct_entry = \trim(\strtolower($ct_entry));
            if ($ct_entry != '*/*')
            {
                $named[] = $ct_entry;
            }
        }
    }

    $container['result_content_type'] = \array_shift($named);

    // +++ NEXT +++
    $response = $next($request, $response);
    // --- NEXT ---

    $enable_envelope = \V($config, 'enable_envelope', false);
    if ($container->get('result_binary') != null)
    {
        // Binary data
        $result = $container->get('result_binary');
    }
    elseif ($enable_envelope)
    {
        // Render envelope
        $result = [
            'code' => $container->get('result_code'),
            'http_code' => $container->get('result_http_code'),
            'message' => $container->get('result_message'),
            'linkes' => $container->get('result_links'),
            'cached' => $container->get('result_cached'),
            'timestamp' => \time(),
            'data' => $container->get('result'),
        ];
    }
    else
    {
        $result = $container->get('result');
    }

    switch ($container->get('result_content_type'))
    {
        case 'application/x-msgpack' :
            //$body = \msgpack_encode($result);
            //break;
        case 'application/x-php' :
            $body = \serialize($result);
            break;
        case 'application/json' :
        default :
            $body = \json_encode($result, \JSON_PRETTY_PRINT);
            break;
    }

    // Test JsonP
    $jsonp = \filter_input(\INPUT_GET, '_jsonp');
    if (\is_string($jsonp) && $request->isGet())
    {
        $response = $response
            ->withHeader('Content-Type', 'application/javascript')
            ->withStatus($container->get('result_http_code'))
            ->write($jsonp . '("' . \addslashes($body) . '")');
    }
    else
    {
        $response = $response
            ->withHeader('Content-Type', $container->get('result_content_type'))
            ->withStatus($container->get('result_http_code'))
            ->write($body);
    }

    return $response;
};

return $mw_Result;

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: sw=4 ts=4 fdm=marker
 * vim<600: sw=4 ts=4
 */
