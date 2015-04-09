<?php

trait PermissionChecker
{

    public static function GetSubjectByQuery(array &$request)
    {
        $result = FALSE;
        if (isset($request['cookies']['sessionId'])) {
            //$sessionId = session_id();
            $sessionId = $request['cookies']['sessionId'];
            //$infos['header']['authrozition'];
            $session = sessions::IsPrimaryKey($sessionId);
            if ($session) {
                $session->setLastOperationTime(time());
                $session->Update();
                $result = users::SelectById($session->getUserId());
            }
        }
        return $result;
    }

    public static function CheckPermission(users $subject, $operation, $dataTypeId, array $attributeBag)
    {
        $result = FALSE;
        $permission = permissions::GetByUserAndOperationDataTypeIdAttributeBag($subject, $operation, $dataTypeId, $attributeBag);
        if ($permission) {
            $result = $permission->getRegionExpression();
        }
        if (!$result) {
            $roles = roles::GetByUser($subject);
            foreach ($roles as $role) {
                $permission = permissions::GetByRoleAndOperationDataTypeIdAttributeBag($role, $operation, $dataTypeId, $attributeBag);
                $result = $permission->getRegionExpression();
                break;
            }
        }
        return $result;
    }

}

