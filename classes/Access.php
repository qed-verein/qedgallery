<?php
/**
 * Table Definition for access
 */
require_once 'DB/DataObject.php';

class DataObjects_Access extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'access';              // table name
    public $userId;                         // int(11) not_null group_by
    public $requestUri;                     // varchar(1024) not_null
    public $requestTime;                    // bigint(20) not_null group_by
    public $requestIp;                      // varchar(128) not_null

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
