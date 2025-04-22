<?php
/**
 * Table Definition for album
 */
require_once 'DB/DataObject.php';

class DataObjects_Album extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'album';               // table name
    public $id;                             // int(11) not_null primary_key auto_increment group_by
    public $ownerId;                        // int(11) not_null group_by
    public $title;                          // varchar(800) not_null
    public $description;                    // blob(262140) not_null blob
    public $creationTime;                   // bigint(20) group_by
    public $originalFrom;                   // bigint(20) group_by
    public $originalTill;                   // bigint(20) group_by
    public $rights;                         // int(11) not_null group_by

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
