<?php
/**
 * Access permissions module
 */
return [
    /**
     * Admin permissions
     */
    'admin_access_permissions' => [
        'index' => 3,
        'registered' => 3,
        'guest' => 3,
        'userTypes' => 3,
        'saveSubscriptionType' => 3,
        'loadSubscriptionForm' => 3,
        'deleteSubscription' => 3,
        'editSubscription' => 3,
        'statusSubscription' => 3,
        'loadPermissionsList' => 3,
        'editPermissions' => 3,
        'loadPeriodForm' => 3,
        'editPeriod' => 3,
        'periodDelete' => 3,
    ],
    /**
     * API permissions
     */
    'api_access_permissions' => [
        'index' => 2,
    ],
    /**
     * Users permissions (guests and authorized)
     */
    'access_permissions' => [
        'index' => 2,
        'groupPage' => 2,
        'selectedPeriod' => 2,
        'paymentForm' => 2,
        'payment' => 2,
        'group' => 2,
    ],
];
