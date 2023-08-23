<?php

namespace nvs\api\v1\Config;

class Config
{
    private $apiKeyName;
    private $apiToken;

    public function __construct(array $config)
    {
        $this->apiKeyName = $config['apiKeyName'] ?? null;
        $this->apiToken = $config['token'] ?? null;
    }

    public function getApiToken(): ?string
    {
        return $this->apiToken;
    }

    public function getApiKeyName(): ?string
    {
        return $this->apiKeyName;
    }
}
