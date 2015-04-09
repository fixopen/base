<?php

class logs extends Model
{
    public function __construct()
    {
        parent::__construct();
        $this->timestamp = time();
    }

    public static function log($userId, $dataTypeId, $dataId, $operation, $description)
    {
        $logItem = new logs();
        $logItem->userId = $userId;
        $logItem->dataTypeId = $dataTypeId;
        $logItem->dataId = $dataId;
        $logItem->operation = $operation;
        $logItem->description = $description;
        $logItem->Insert();
    }
}
