<?php

namespace GitLog;

use DateTimeInterface;

class FileDiff
{
    private $filename;
    private $additions;
    private $deletions;

    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    public function setAdditions($amount)
    {
        $this->additions = $amount;
        return $this;
    }

    public function setDeletions($amount)
    {
        $this->deletions = $amount;
        return $this;
    }

    public function getFileName()
    {
        return $this->filename;
    }

    public function getAdditions()
    {
        return $this->additions;
    }

    public function getDeletions()
    {
        return $this->deletions;
    }
}
