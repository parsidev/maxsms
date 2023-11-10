<?php

namespace Parsidev\MaxSms\Models;

class PaginationInfo extends Base
{
    /**
     * total count
     * @var int
     */
    public $total = 0;

    /**
     * pagination limit
     * @var int
     */
    public $limit = 0;

    /**
     * current page
     * @var int
     */
    public $page = 0;

    /**
     * total pages
     * @var int
     */
    public $pages = 0;

    /**
     * preview resource
     * @var Null|string
     */
    public $prev = Null;

    /**
     * next resource
     * @var Null|string
     */
    public $next = Null;
}