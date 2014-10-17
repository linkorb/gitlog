<?php

namespace GitLog;

use DateTimeInterface;
use InvalidArgumentException;

class FileDiff
{
    private $filename;
    private $additions;
    private $deletions;

    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    private function checkValidAmount($amount)
    {
        if (!is_int($amount)) {
            throw new InvalidArgumentException('Amount must be type integer');
        }
        if ($amount<0) {
            throw new InvalidArgumentException('Amount can not be negative');
        }
    }

    public function setAdditions($amount)
    {
        $this->checkValidAmount($amount);
        $this->additions = $amount;
        return $this;
    }

    public function setDeletions($amount)
    {
        $this->checkValidAmount($amount);
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
