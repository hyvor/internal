<?php

namespace Hyvor\Internal\Auth;

interface AuthInterface
{

    public function check(string $cookie): false|AuthUser;

}