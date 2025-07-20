<?php

namespace Hyvor\Internal\Auth;

enum AuthMethod: string
{

    case HYVOR = 'hyvor';
    case OPENID = 'openid';

}