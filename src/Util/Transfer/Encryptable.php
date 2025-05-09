<?php

namespace Hyvor\Internal\Util\Transfer;

use Illuminate\Support\Facades\Crypt;

/**
 * LARAVEL ONLY!
 *
 * This class is used for encrypting objects within the Hyvor\Internal namespace.
 * It is safe since the objects are shared between components
 * Safe to use on public facing objects
 */
trait Encryptable
{

    public function encrypt(): string
    {
        $className = get_class($this);

        assert(str_starts_with($className, 'Hyvor\\Internal\\'), 'Invalid token: expected internal class');

        return Crypt::encrypt($this);
    }

    public static function decrypt(string $token): static
    {
        $object = Crypt::decrypt($token);

        assert(is_object($object), 'Invalid token: expected object');
        assert($object instanceof static, 'Invalid token: expected static');

        return $object;
    }

}
