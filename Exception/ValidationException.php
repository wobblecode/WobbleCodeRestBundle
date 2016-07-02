<?php

namespace WobbleCode\RestBundle\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * ValidationException
 *
 * @author Luis Hdez <luis.munoz.hdez@gmail.com
 */
class ValidationException extends HttpException
{
    /**
     * List of errors
     *
     * @var Array
     */
    protected $errors;

    /**
     * Constructor.
     *
     * @param array      $errors   List of mapped errors
     * @param string     $message  The internal exception message
     * @param \Exception $previous The previous exception
     * @param int        $code     The internal exception code
     */
    public function __construct($errors, $message = null, \Exception $previous = null, $code = 0)
    {
        parent::__construct(422, $message, $previous, array(), $code);

        $this->errors = $errors;
    }

    /**
     * getErrors
     *
     * @return array list of mapped errors
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
