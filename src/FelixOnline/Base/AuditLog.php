<?php
namespace FelixOnline\Base;

/*
 * Audit Log class
 */
class AuditLog extends BaseDB
{
    public $dbtable = 'audit_log';

    public function __construct($id = null, $rowData = null)
    {
        $fields = array(
            'timestamp' => new Type\DateTimeField(),
            'table' => new Type\CharField(),
            'key' => new Type\CharField(),
            'user' => new Type\CharField(),
            'action' => new Type\CharField(),
            'fields' => new Type\TextField()
        );

        parent::__construct($fields, $id, null, true, $rowData);
    }
}
