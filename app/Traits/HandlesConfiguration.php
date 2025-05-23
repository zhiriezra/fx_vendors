<?php

namespace App\Traits;

trait HandlesConfiguration
{
    /**
     * Configuration array.
     *
     * @var array
     */
    protected array $config;

    /**
     * Constructor to inject configuration.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Retrieve a configuration value by key.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
/*     protected function getConfig(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    } */

    protected function getConfig(string $key, $default = null)
    {
        if (!isset($this->config[$key])) {
            if ($default === null) {
                throw new \InvalidArgumentException("Missing configuration key: $key");
            }
            return $default;
        }

        return $this->config[$key];
    }
}
