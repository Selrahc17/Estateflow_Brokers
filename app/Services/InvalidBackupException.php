<?php

namespace App\Services;

use RuntimeException;

/**
 * The uploaded file is not an unedited backup made by this EstateFlow installation.
 */
class InvalidBackupException extends RuntimeException
{
}
