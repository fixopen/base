<?php

class permissions extends Model
{

    //super-contact-view: contact-table select regionExpression: contact-isPrivate = FALSE AND contact-id in (select contactId from contactGroupMap where groupId = <self-organizationId>)
    //contact-view: contact-table select regionExpression: super-contact-view's regionExpression AND contact-id in (select contactId from userContactMap where userId = <self-id>)

    public static function GetByUserAndOperationDataTypeIdAttributeBag($user, $operation, $dataTypeId, array $attributeBag)
    {
        $result = FALSE;
        $mapFilter = self::ConstructMapFilter('permissionId', 'userPermissionMap', 'userId', $user->getId());
        $operationFilter = self::ConstructNameValueFilter('operation', $operation);
        $dataTypeIdFilter = self::ConstructNameValueFilter('dataTypeId', $dataTypeId);
        $r = self::CustomSelect(' WHERE ' . $mapFilter . ' AND ' . $operationFilter . ' AND ' . $dataTypeIdFilter);
        if (count($r) == 1) {
            $result = $r[0];
            $ab = $result->getAttributeBag();
            foreach ($attributeBag as $attribute) {
                if (!in_array($attribute, $ab)) {
                    $result = FALSE;
                    break;
                }
            }
        }
        return $result;
    }

    public static function GetByRoleAndOperationDataTypeIdAttributeBag($role, $operation, $dataTypeId, array $attributeBag)
    {
        $result = FALSE;
        $mapFilter = self::ConstructMapFilter('permissionId', 'rolePermissionMap', 'roleId', $role->getId());
        $operationFilter = self::ConstructNameValueFilter('operation', $operation);
        $dataTypeIdFilter = self::ConstructNameValueFilter('dataTypeId', $dataTypeId);
        $r = self::CustomSelect(' WHERE ' . $mapFilter . ' AND ' . $operationFilter . ' AND ' . $dataTypeIdFilter);
        if (count($r) == 1) {
            $result = $r[0];
            $ab = $result->getAttributeBag();
            foreach ($attributeBag as $attribute) {
                if (!in_array($attribute, $ab)) {
                    $result = FALSE;
                    break;
                }
            }
        }
        return $result;
    }

}
