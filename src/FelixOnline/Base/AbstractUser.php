<?php
namespace FelixOnline\Base;

abstract class AbstractUser extends BaseDB
{
    public function __construct($fields, $uname)
    {
        parent::__construct($fields, $uname);
    }

    abstract public function getUsername();
    abstract public function getPasswordHash();

    abstract public function setUsername($username);
    abstract public function setPasswordHash($hash);

    // Set password and return hash.
    public function hashPassword(string $password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    // Check hash
    public function verifyPassword(string $password)
    {
        if (password_verify($password, $this->getPasswordHash())) {
            if (password_needs_rehash($password, $this->getPasswordHash())) {
                $this->setPasswordHash($this->hashPassword($password));
                $this->save();

                return true;
            }
        } else {
            return false;
        }
    }
}
