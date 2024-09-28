<?php

namespace App\Enums;

enum StatusCode: int
{
    //status code 2xx
    case OK = 200;
    case CREATED = 201;
    case NO_CONTENT = 204;
    
    //status code 4xx
    case BAD_REQUEST = 400;
    case UNAUTHORIZED = 401;
    case FORBIDDEN = 403;
    case NOT_FOUND = 404;
    case METHOD_NOT_ALLOWED = 405;
    case CONFLICT = 409;
    case UNPROCESSABLE_ENTITY = 422;

    //status code 5xx
    case INTERNAL_SERVER_ERROR = 500;
}
