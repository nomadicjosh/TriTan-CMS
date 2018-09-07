<?php
namespace TriTan\Interfaces\User;

interface UserInterface
{
    public function getId(): int;
    
    public function setId(int $id);
    
    public function getLogin();
    
    public function setLogin(string $login);
    
    public function getFname();
    
    public function setFname(string $fname);
    
    public function getLname();
    
    public function setLname(string $lname);
    
    public function getEmail();
    
    public function setEmail(string $email);
    
    public function getPassword();
    
    public function setPassword(string $password);
    
    public function getUrl();
    
    public function setUrl(string $url);
    
    public function getAddedBy(): int;
    
    public function setAddedBy(int $addedby);
    
    public function getRegistered();
    
    public function setRegistered(string $registered);
    
    public function getModified();
    
    public function setModified(string $modified);
    
    public function getActivationKey();
    
    public function setActivationKey(string $activationkey);
}

