<?php

class Result {

    private bool $succeeded;

    private string $message;

    function __construct(bool $succeeded, string $message) {
        $this->succeeded = $succeeded;
        $this->message = $message;
    }

    function getSucceeded(): bool {
        return $this->succeeded;
    }

    function getMessage(): string {
        return $this->message;
    }
}

?>