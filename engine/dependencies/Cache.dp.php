<?php
/*
 * engine/dependencies/Cache.dp.php
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
 * @file engine/dependencies/Cache.dp.php
 * @package Husky/php/base
 * @author Dr.NP <np@bsgroup.org>
 * @since 06/04/2018
 * @version 0.0.1
 */

function _cache_driver_redis($config)
{
    $redis = new \Redis();
    $redis->connect(
        \V($config, 'host', 'localhost'),
        \V($config, 'port', 6379)
    );
    if ($redis)
    {
        $auth = \V($config, 'auth');
        $db = \V($config, 'db');
        if ($auth)
        {
            $redis->auth($auth);
        }

        $redis->select(\intval($db));
    }

    $cache = new \Doctrine\Common\Cache\RedisCache();
    $cache->setRedis($redis);

    return $cache;
}

function _cache_driver_apc($config)
{
    $cache = new \Doctrine\Common\Cache\ApcCache();

    return $cache;
}

function _cache_driver_memcached($config)
{
    $mc = new \Memcached();
    $mc->addServer(
        \V($config, 'host', 'localhost'),
        \V($config, 'port', 11211)
    );

    $cache = new \Doctrine\Common\Cache\MemcachedCache();
    $cache->setMemcached($mc);

    return $cache;
}

$dp_Cache = function($c) {
    // Doctrine cache
    try
    {
        $config = $c->get('settings')['runtime']['dependencies']['Cache'];
        $driver = \V($config, 'driver');
        $ins_func = '_cache_driver_' . $driver;
        if (!\function_exists($ins_func))
        {
            die('Cache driver <' . $driver . '> does not exists');
        }

        $cache = $ins_func($config);
    }
    catch (\Exception $e)
    {
        die('Doctrine cache error : ' . $e->getMessage());
    }

    return $cache;
};

return $dp_Cache;

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: sw=4 ts=4 fdm=marker
 * vim<600: sw=4 ts=4
 */
