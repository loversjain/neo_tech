<?php

namespace App\Enums;

enum ValidationMessages: string
{
    // User validation messages
    case EMAIL_NOT_FOUND = 'This email does not exist.';
    case PASSWORD_REQUIRED = 'The password field is required.';
    case PRODUCT_ID_REQUIRED = 'The product ID is required.';
    case PRODUCT_ID_INVALID = 'The product ID must be an integer.';
    case PRODUCT_ID_NOT_FOUND = 'The specified product does not exist.';
    case QUANTITY_REQUIRED = 'The quantity is required.';
    case QUANTITY_INVALID = 'The quantity must be an integer.';
    case QUANTITY_MIN = 'The quantity must be at least 1.';
    case ADDRESS_REQUIRED = 'The address is required.';
    case ADDRESS_INVALID = 'The address must be a string.';
    case ADDRESS_MAX_LENGTH = 'The address must not exceed 255 characters.';

    // Order validation messages
    case ORDER_AMOUNT_REQUIRED = 'The order amount is required.';
    case ORDER_AMOUNT_NUMERIC = 'The order amount must be a number.';
    case ORDER_AMOUNT_MIN = 'The order amount must be at least 0.1.';


    // General user validation messages
    case EMAIL_REQUIRED = 'The email field is required.';
    case EMAIL_INVALID = 'Please provide a valid email address.';
    case EMAIL_TAKEN = 'The email has already been taken.';
    case STATUS_REQUIRED = 'The status field is required.';
    case STATUS_INVALID = 'The selected status is invalid. It must be either active or inactive.';
}
