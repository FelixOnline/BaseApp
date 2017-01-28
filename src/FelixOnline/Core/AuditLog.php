<?php
namespace FelixOnline\Core;
/*
 * Audit Log class
 */
class AuditLog extends BaseDB
{
    public $dbtable = 'audit_log';

    function __construct($id = NULL) {
        $fields = array(
            'timestamp' => new Type\DateTimeField(),
            'table' => new Type\CharField(),
            'key' => new Type\CharField(),
            'user' => new Type\CharField(),
            'action' => new Type\CharField(),
            'fields' => new Type\TextField()
        );

        parent::__construct($fields, $id, null, true);
    }
}
