<?php
/*
 * engine/utils/misc.php
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
 * @file engine/utils/misc.php
 * @package Husky/php/base
 * @author Dr.NP <np@bsgroup.org>
 * @since 05/30/2018
 * @version 0.0.1
 */

/**
 * Check and return array item
 *
 * @param array $array      Array to check
 * @param string $key       Item key
 * @param mixed @default    Fallback default value
 * @param bool @enable_multi
 *                          If enable multi-dim path of key (a::b::c::d)
 *
 * @return mixed            Value
 */
/* {{{ [utils::misc::V] */
function V($array, string $key, $default = null, $enable_multi = false)
{
    if (!\is_array($array))
    {
        return $default;
    }

    if ($enable_multi)
    {
        // Parse path
        $path = \explode('::', $key);
        $curr = &$array;
        foreach ($path as $k)
        {
            if (\is_array($curr) && isset($curr[$k]))
            {
                $curr = &$curr[$k];
            }
            else
            {
                return $default;
            }
        }

        return $curr;
    }
    elseif (isset($array[$key]))
    {
        return $array[$key];
    }

    return $default;
}

/* }}} */

/**
 * Merge two arrays recursive with overwriting old value
 *
 * @param array $array1     Source array
 * @param array $array2     Array to merge
 *
 * @return array            Merged array
 */
/* {{{ [utils::misc::M] */
function M(array &$array1, array &$array2)
{
    $merged = $array1;
    foreach ($array2 as $key => &$value)
    {
        if (\is_array($value) && isset($merged[$key]) && \is_array($merged[$key]))
        {
            $merged[$key] = M($merged[$key], $value);
        }
        else
        {
            $merged[$key] = $value;
        }
    }

    return $merged;
}

/* }}} */

/**
 * Filter array with template
 *
 * @param array $array      Source array
 * @param array $template   Template array
 *
 * @return array            Filtered array
 */
/* {{{ [utils::misc::F] */
function F(array $array, array $template)
{
    $ret = [];
    foreach ($template as $key => $must)
    {
        $v = \V($array, $key, null);
        if ($must)
        {
            $ret[$key] = $v ? $v : $must;
        }
        elseif ($v)
        {
            $ret[$key] = $v;
        }
    }

    return $ret;
}

/* }}} */

/**
 * Assert array with template
 *
 * @param array $array      Source array
 * @param array $template   Template array
 *
 * @return bool             Assert result
 */
/* {{{ [utils::misc::A] */
function A(array $array, array $template)
{

}

/* }}} */

/**
 * Get IPv4 address of client
 *
 * @param bool $share       Just return REMOTE_ADDR
 *
 * @return string           IPv4
 */
/* {{{ [utils::misc::IP] */
function IP($share = false)
{
    if ($bare)
    {
        return \filter_input(\INPUT_SERVER, 'REMOTE_ADDR');
    }

    $keys = [
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR'
    ];

    $ip_addr = '0.0.0.0';
    foreach ($keys as $key)
    {
        $value = \filter_input(\INPUT_SERVER, $key);
        if ($value)
        {
            $ips = \explode(',', $value);
            foreach ($ips as $ip_addr)
            {
                $ip_addr = \trim($ip_addr);
                if (\filter_var($ip_addr,
                                \FILTER_VALIDATE_IP,
                                [\FILTER_FLAG_NO_PRIV_RANGE, \FILTER_FLAG_NO_RES_RANGE]))
                {
                    break 2;
                }
            }
        }
    }

    return $ip_addr;
}

/* }}} */

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 * vim600: sw=4 ts=4 fdm=marker
 * vim<600: sw=4 ts=4
 */
