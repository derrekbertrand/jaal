<?php

namespace DialInno\Jaal\Contracts;

use Illuminate\Contracts\Support\Responsable;
use Throwable;

interface Response extends Responsable, Throwable
{
    public function throwResponse();

    public function throwResponseIfErrors();
}
