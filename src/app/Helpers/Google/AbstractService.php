<?php

namespace App\Helpers\Google;

use App\Exceptions\EmptyGoogleAccountException;
use App\Option;

/**
 * Created by PhpStorm.
 * User: eremenko_aa
 * Date: 29.09.2018
 * Time: 19:03
 */
abstract class AbstractService
{
    /**  @var \Google_Client $client */
    private $client;

    /**  @var array $authConfig */
    private $authConfig;

    /**  @var string $subject */
    private $subject;

    /**  @var array $scopes */
    private $scopes;

    public function __construct()
    {
        $this->initConfig();
        $this->scopes = [];
    }

    /**
     * @return \Google_Service
     */
    abstract public function getService(): \Google_Service;

    private function initConfig()
    {
        $this->authConfig = json_decode(Option::getOptionValue('google_service_account_credentials'), true);

        if (empty($this->authConfig)) {
            throw new EmptyGoogleAccountException();
        }

        $this->subject = Option::getOptionValue('google_service_account_subject');
    }

    /**
     * @param bool $createNew
     *
     * @return \Google_Client
     */
    public function getClient($createNew = false)
    {
        if (!isset($this->client) || $createNew) {
            $client = new \Google_Client();
            $client->setAuthConfig($this->authConfig);
            $client->setAccessType('offline');
            $client->setSubject($this->subject);
            $client->setApplicationName(config('app.name'));
            $this->client = $client;
        }

        return $this->client;
    }

    /**
     * @return array
     */
    public function getAuthConfig(): array
    {
        return $this->authConfig;
    }

    /**
     * @param array $authConfig
     *
     * @return $this
     */
    public function setAuthConfig(array $authConfig)
    {
        $this->authConfig = $authConfig;

        return $this;
    }

    /**
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     *
     * @return $this
     */
    public function setSubject(string $subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @return array
     */
    public function getScopes(): array
    {
        return $this->scopes;
    }

    /**
     * @param array $scopes
     *
     * @return $this
     */
    public function setScopes(array $scopes)
    {
        $this->scopes = $scopes;

        return $this;
    }

    /**
     * @param \Google_Client $client
     *
     * @return $this
     */
    public function setClient(\Google_Client $client): AbstractService
    {
        $this->client = $client;

        return $this;
}
}