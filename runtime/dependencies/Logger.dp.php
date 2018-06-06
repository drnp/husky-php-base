<?php
/*
 * runtime/dependencies/Logger.dp.php
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

$dp_Logger = function($c) {
    // Monolog
    try
    {
        $logger = new \Monolog\Logger($app_name);
        $logger->pushProcessor(new \Monolog\Processor\UidProcessor);
        $logger->pushHandler(new \Monolog\Handler\RotatingFileHandler(
            $c->get('settings')['runtime']['dependencies']['logger']['path'],
            0,
            \Monolog\Logger::DEBUG
        ));
    }
    catch (\Exception $e)
    {
        die('Monolog error : ' . $e->getMessage());
    }

    return $logger;
};

/**
 * @file runtime/dependencies/logger.dp.php
 * @package Husky/php/base
 * @author Dr.NP <np@bsgroup.org>
 * @since 06/04/2018
 * @version 0.0.1
 */
