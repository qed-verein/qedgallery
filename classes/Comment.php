<?php
/**
 * Table Definition for comment
 */
require_once 'DB/DataObject.php';

class DataObjects_Comment extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'comment';             // table name
    public $id;                             // int(11) not_null primary_key auto_increment group_by
    public $imageId;                        // int(11) not_null group_by
    public $ownerId;                        // int(11) not_null group_by
    public $content;                        // blob(262140) not_null blob
    public $creationTime;                   // bigint(20) not_null group_by

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
