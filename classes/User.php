<?php
/**
 * Table Definition for user
 */
require_once 'DB/DataObject.php';

class DataObjects_User extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'user';                // table name
    public $id;                             // int(11) not_null primary_key auto_increment group_by
    public $username;                       // varchar(200) not_null
    public $password;                       // varchar(200) not_null
    public $rank;                           // int(11) not_null group_by

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
