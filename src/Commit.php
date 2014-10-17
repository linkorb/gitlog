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

    public function setCommitter($name, $email, DateTimeInterface $date)
    {
        $this->committerName = $name;
        $this->committerEmail = $email;
        $this->committerDate = $date;
        return $this;
    }

    public function setBody($body)
    {
        $this->body = $body;
        return $this;
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
