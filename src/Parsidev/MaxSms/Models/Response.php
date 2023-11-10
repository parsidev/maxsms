<?php

namespace Parsidev\MaxSms\Models;


class Response extends Base
{
    /**
     * http status code
     * @var int
     */
    public $status = 200;

    /**
     * response code
     * @var string
     */
    public $code = "";

    /**
     * response data
     * @var mixed
     */
    public $data = Null;

    /**
     * response data
     * @var mixed
     */
    public $errorMessage = Null;

    /**
     * meta data
     * @var Null|array|PaginationInfo
     */
    public $meta = Null;
}