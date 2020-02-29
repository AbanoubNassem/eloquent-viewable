<?php

declare(strict_types=1);

namespace CyrildeWit\EloquentViewable;

use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class VisitorCookieRepository
{
    /**
     * The visitor cookie key.
     *
     * @var string
     */
    protected $key;

    /**
     * Create a new view session history instance.
     *
     * @return void
     */
    public function __construct(ConfigRepository $config)
    {
        $this->key = $config['eloquent-viewable']['visitor_cookie_key'];
    }

    /**
     * Get the visitor's unique key.
     *
     * @return string
     */
    public function get()
    {
        if (! Cookie::has($this->key)) {
            Cookie::queue($this->key, $uniqueString = $this->generateUniqueString(), $this->expirationInMinutes());

            return $uniqueString;
        }

        return Cookie::get($this->key);
    }

    /**
     * Generate a unique visitor string.
     *
     * @return string
     */
    protected function generateUniqueString(): string
    {
        return Str::random(80);
    }

    /**
     * Get the expiration in minutes.
     *
     * @return int
     */
    protected function expirationInMinutes(): int
    {
        return 2628000; // aka 5 years
    }
}
