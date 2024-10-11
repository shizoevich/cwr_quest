<?php

namespace App\Channels\Messages;

class RingcentralMessage
{
    public $content;
    
    public function content($content)
    {
        $this->content = $content;
        
        return $this;
    }
}