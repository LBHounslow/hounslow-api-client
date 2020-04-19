<?php

namespace Hounslow\ApiClient\Entity;

class AccessToken
{
    /**
     * @var string
     */
    private $token;

    /**
     * @var string
     */
    private $type;

    /**
     * @var \DateTimeImmutable
     */
    private $expiry;

    /**
     * @var string
     */
    private $refreshToken;

    /**
     * @var \DateTimeImmutable
     */
    private $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getExpiry(): \DateTimeImmutable
    {
        return $this->expiry;
    }

    /**
     * @return string
     */
    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return new \DateTimeImmutable() <= $this->getExpiry();
    }

    /**
     * @param array $payload
     * @return $this
     */
    public function hydrate(array $payload)
    {
        $seconds = $payload['expires_in'];
        $this->token = $payload['access_token'];
        $this->type = $payload['token_type'];
        $this->expiry = (new \DateTimeImmutable())->add(new \DateInterval('PT'.$seconds.'S'));
        $this->refreshToken = $payload['refresh_token'];
        return $this;
    }
}