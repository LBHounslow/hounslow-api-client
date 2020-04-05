<?php

namespace App\Entity;

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
        return $this->getExpiry() >= new \DateTimeImmutable();
    }

    /**
     * @param array $data
     * @return $this
     */
    public function hydrate(array $data)
    {
        $hours = floor($data['expires_in'] / 3600);
        $this->token = $data['access_token'];
        $this->type = $data['token_type'];
        $this->expiry = (new \DateTimeImmutable())->add(new \DateInterval('PT'.$hours.'H'));
        $this->refreshToken = $data['refresh_token'];
        return $this;
    }
}