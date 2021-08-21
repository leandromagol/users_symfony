<?php

namespace App\Document;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document
 */
class ApiToken
{

    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $token;

    /**
     * @MongoDB\Field(type="date_immutable")
     */
    protected $expires_at;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param mixed $token
     */
    public function setToken($token): void
    {
        $this->token = $token;
    }

    /**
     * @return mixed
     */
    public function getExpiresAt()
    {
        return $this->expires_at->format('Y-m-d H:i:s');
    }

    /**
     * @param mixed $expires_at
     */
    public function setExpiresAt($expires_at): void
    {
        $this->expires_at = $expires_at;
    }

}