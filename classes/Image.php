<?php
/**
 * Table Definition for image
 */
require_once 'DB/DataObject.php';

class DataObjects_Image extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'image';               // table name
    public $id;                             // int(11) not_null primary_key auto_increment group_by
    public $albumId;                        // int(11) not_null group_by
    public $ownerId;                        // int(11) not_null group_by
    public $title;                          // varchar(800) not_null
    public $mimeType;                       // varchar(200) not_null
    public $uploadTime;                     // bigint(20) group_by
    public $originalTime;                   // bigint(20) group_by
    public $viewCounter;                    // bigint(20) group_by
    public $lastViewed;                     // bigint(20) group_by
    public $category;                       // varchar(800) not_null
    public $rights;                         // int(11) not_null group_by

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
