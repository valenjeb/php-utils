<?php

/**
 * phpcs:disable Squiz.Classes.ClassFileName.NoMatch
 * phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
 */

declare(strict_types=1);

namespace Devly\Exceptions;

use Exception;
use LogicException;
use RuntimeException;

/**
 * The exception that is thrown when an I/O error occurs.
 */
class IOException extends RuntimeException
{
}

/**
 * The exception that is thrown when accessing a file that does not exist on disk.
 */
class FileNotFoundException extends IOException
{
}

/**
 * The exception that is thrown when part of a file or directory cannot be found.
 */
class DirectoryNotFoundException extends IOException
{
}

class ValidationException extends Exception
{
}

class NotSupportedException extends LogicException
{
}
