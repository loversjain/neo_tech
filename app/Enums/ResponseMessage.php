<?php

namespace App\Enums;

enum ResponseMessage: string
{
    //user messages
    case USER_CREATED = 'User created successfully.';
    case USER_STATUS_UPDATED = 'User status updated successfully.';
    case USER_NOT_FOUND = 'User not found.';

    //order message
    case ORDER_CREATED = 'Order created successfully.';
    case ORDER_UPDATED = 'Order updated successfully.';
    case ORDER_DELETED = 'Order deleted successfully.';
    case ORDERS_FETCHED = 'Orders fetched successfully.';
    case ORDER_NOT_FOUND = 'Order not found.';
}
