<?php

namespace Bcismariu\Laravel\Payments\Processors;

class Response
{
    public $raw;
    public $message;
    public $status;

    public function isSuccessful()
    {
        return $this->status == 'success';
    }
}