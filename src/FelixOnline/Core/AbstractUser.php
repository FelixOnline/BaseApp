<?php
namespace FelixOnline\Core;

abstract class AbstractCurrentUser {
    public function __construct() {}

    abstract public function getUsername();
    abstract public function getName();
    abstract public function getEmail();
    abstract public function getPasswordHash();

    abstract public function setUsername();
    abstract public function setName();
    abstract public function setEmail();
    abstract public function setPasswordHash();

    abstract public function save();
    abstract public function delete();

    // Set password and return hash.
    public function hashPassword(string $password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    // Check hash
    public function verifyPassword(string $password) {
        if(password_verify($password, $this->getPasswordHash())) {
            if(password_needs_rehash($password, $this->getPasswordHash())) {
                $this->setPasswordHash($this->hashPassword($password));
                $this->save();

                return true;
            }
        } else {
            return false;
        }
    }
}
