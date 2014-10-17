<?php

namespace spec\GitLog;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FileDiffSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith('test.xml');
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('GitLog\FileDiff');
    }

    public function it_can_return_filename()
    {
        $this->beConstructedWith('test.xml');
        $this->getFileName()->shouldReturn('test.xml');
    }

    public function it_can_have_additions_and_deletions()
    {
        $this->setAdditions(5)->shouldReturn($this);
        $this->setDeletions(3)->shouldReturn($this);
        $this->getAdditions()->shouldReturn(5);
        $this->getDeletions()->shouldReturn(3);
    }

    public function it_can_deal_with_funny_things()
    {
        $this->shouldThrow('\InvalidArgumentException')->duringSetAdditions('x');
        $this->shouldThrow('\InvalidArgumentException')->duringSetAdditions(-1);
    }
}
