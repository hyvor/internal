<?php

namespace Hyvor\Internal\Billing\License;

use Hyvor\Internal\Util\Transfer\Serializable;

/**
 * Add license parameters in the constructor.
 * When creating a license, set the limits to that of the trial license.
 * ONLY USE int and bool types in the constructor.
 *
 * int: use the smallest possible type (bytes instead of kb, gb)
 */
abstract class License
{
    use Serializable;
}
