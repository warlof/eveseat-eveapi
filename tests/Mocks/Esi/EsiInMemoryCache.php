<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015 to 2021 Leon Jacobs
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

namespace Seat\Eveapi\Tests\Mocks\Esi;

use Seat\Eseye\Cache\CacheInterface;
use Seat\Eseye\Cache\HashesStrings;
use Seat\Eseye\Containers\EsiResponse;

/**
 * Class EsiInMemoryCache.
 * @package Seat\Eveapi\Tests\Mocks\Esi
 */
class EsiInMemoryCache implements CacheInterface
{
    use HashesStrings;

    /**
     * @var array
     */
    private static $database = [];

    /**
     * @var \Seat\Eveapi\Tests\Mocks\Esi\EsiInMemoryCache
     */
    private static $instance;

    /**
     * EsiInMemoryCache constructor.
     */
    public function __construct()
    {
        self::$instance = $this;
    }

    /**
     * @return static
     */
    public static function getInstance(): self
    {
        return self::$instance;
    }

    /**
     * Prune cache database.
     */
    public static function clear()
    {
        self::$database = [];
    }

    /**
     * @param string $uri
     * @param string $query
     * @param \Seat\Eseye\Containers\EsiResponse $data
     *
     * @return void
     */
    public function set(string $uri, string $query, EsiResponse $data)
    {
        $key = $this->getCacheKey($uri, $query);

        self::$database[$key] = $data;
    }

    /**
     * @param string $uri
     * @param string $query
     *
     * @return \Seat\Eseye\Containers\EsiResponse|bool
     */
    public function get(string $uri, string $query = '')
    {
        $key = $this->getCacheKey($uri, $query);

        if (! array_key_exists($key, self::$database))
            return false;

        $data = self::$database[$key];

        if ($data->expired() && ! $data->hasHeader('ETag')) {
            $this->forget($uri, $query);

            return false;
        }

        return $data;
    }

    /**
     * @param string $uri
     * @param string $query
     *
     * @return void
     */
    public function forget(string $uri, string $query = '')
    {
        $key = $this->getCacheKey($uri, $query);

        if (array_key_exists($key, self::$database))
            unset(self::$database[$key]);
    }

    /**
     * @param string $uri
     * @param string $query
     *
     * @return bool|mixed
     */
    public function has(string $uri, string $query = ''): bool
    {
        $key = $this->getCacheKey($uri, $query);

        return array_key_exists($key, self::$database);
    }

    private function getCacheKey(string $uri, string $query = '')
    {
        return $this->buildHashPath($this->safePath($uri), $query);
    }

    /**
     * @param string $uri
     *
     * @return string
     */
    private function safePath(string $uri): string
    {

        return preg_replace('/[^A-Za-z0-9\/]/', '', $uri);
    }

    /**
     * @param string $path
     * @param string $query
     *
     * @return string
     */
    private function buildHashPath(string $path, string $query = ''): string
    {

        // If the query string has data, hash it.
        if ($query != '')
            $query = $this->hashString($query);

        return strtr($path, ['/' => '_']) . $query;
    }
}