<?php

namespace B2P\Common;

use B2P\Models\Parameters\Sector;

/**
 * Объект конфигурации SDK
 *
 * Используется для хранения и передачи общих данных, таких как номер сектора, пароль, и другие настройки модуля.
 * Это позволит очень удобно и легко хранить их в едином месте и быстро получать из любого места SDK.
 */
class ConfigManager
{
    protected Sector $sector;
    protected string $pass;
    protected bool $testMode = false;
    protected bool $sha256;

    protected static self $instance;
    protected string $test_url;
    protected string $prod_url;

    protected function __construct()
    {
    }

    public static function getInstance(): ConfigManager
    {
        if (isset(static::$instance))
            return static::$instance;

        static::$instance = new static();
        static::$instance->setUrl();
        return static::$instance;
    }

    /* -- getters -- */

    /**
     * @return Sector
     */
    public function getSector(): Sector
    {
        return $this->sector;
    }

    /**
     * @return string
     */
    public function getPass(): string
    {
        return $this->pass;
    }

    /**
     * @return bool
     */
    public function isTestMode(): bool
    {
        return $this->testMode;
    }

    /**
     * @return string URL для отправки Запросов
     */
    public function getUrl(): string
    {
        return $this->testMode ? $this->test_url : $this->prod_url;
    }

    /* -- setters -- */

    /**
     * @return bool
     */
    public function setUrl(): bool
    {
        if (file_exists(__DIR__ . '/../../.env')) {
            $env = parse_ini_file(__DIR__ . '/../../.env');
        } else if (file_exists(__DIR__ . '/../../../.env')) {
            $env = parse_ini_file(__DIR__ . '/../../../.env');
        } else throw new \LogicException('API server addresses are not specified or the configuration file (.env) is missing [1]');
        if (!$env || !isset($env['test_api_url']) || !isset($env['prod_api_url'])) {
            throw new \LogicException('API server addresses are not specified or the configuration file (.env) is missing [2]');
        }

        $this->test_url = $env['test_api_url'];
        $this->prod_url = $env['prod_api_url'];
        return true;
    }

    /**
     * @param int $sector
     * @return ConfigManager
     */
    public function setSector(int $sector): ConfigManager
    {
        $this->sector = new Sector($sector);
        return $this;
    }

    /**
     * @param string $pass
     * @return ConfigManager
     */
    public function setPass(string $pass): ConfigManager
    {
        $this->pass = $pass;
        return $this;
    }

    /**
     * @param bool $testMode
     * @return ConfigManager
     */
    public function setTestMode(bool $testMode): ConfigManager
    {
        $this->testMode = $testMode;
        return $this;
    }

    /**
     * @return string
     */
    public function isSHAseted(): string
    {
        return ($this->sha256) ?? false;
    }

    public function setSHA256(): ConfigManager
    {
        $this->sha256 = true;
        return $this;
    }

}