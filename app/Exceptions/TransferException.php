<?php

namespace App\Exceptions;

use DomainException;

class TransferException extends DomainException
{
    public static function sameAccount(): self
    {
        return new self('no se puede transferir a la misma cuenta');
    }

    public static function inactiveAccount(): self
    {
        return new self('un de las cuentas no está activa');
    }

    public static function insufficientFunds(): self
    {
        return new self('saldo insuficiente');
    }
}
