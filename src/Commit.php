<?php

namespace GitLog;

use DateTimeInterface;

class Commit
{
    private $hash;
    private $subject;
    private $authorName;
    private $authorEmail;
    private $authorDate;
    private $committerName;
    private $committerEmail;
    private $committerDate;
    private $body;
    private $filediffs = array();

    public function _construct($hash)
    {
        $this->hash = $hash;
    }

    public function getHash()
    {
        return $this->hash;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    public function setAuthor($name, $email, DateTimeInterface $date)
    {
        $this->authorName = $name;
        $this->authorEmail = $email;
        $this->authorDate = $date;
        return $this;
    }

    public function getAuthorName()
    {
        return $this->authorName;
    }

    public function getAuthorEmail()
    {
        return $this->authorEmail;
    }

    public function getAuthorDate()
    {
        return $this->authorDate;
    }

    public function setCommitter($name, $email, DateTimeInterface $date)
    {
        $this->committerName = $name;
        $this->committerEmail = $email;
        $this->committerDate = $date;
        return $this;
    }
    
    public function getCommitterName()
    {
        return $this->committerName;
    }
    
    public function getCommitterEmail()
    {
        return $this->committerEmail;
    }
    
    public function getCommitterDate()
    {
        return $this->committerDate;
    }


    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }
    
    public function getBody()
    {
        return $this->body;
    }

    public function addFileDiff(FileDiff $filediff)
    {
        $this->filediffs[] = $filediff;
    }

    public function getFileDiffs()
    {
        return $this->filediffs;
    }
}
