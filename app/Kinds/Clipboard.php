<?php

namespace Zync\Kinds;

class Clipboard {

    private $kind = "clipboard";

    private $columns = [
        'user' => 'integer',
        'time' => 'integer',
        'sha256' => 'string',
        'md5' => 'string',
        'encrypted' => 'bool',
    ];

}