<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class Roles extends Model
{
    public static $ROLE_ADMIN_USER      = 'ADMINISTRATOR';
    public static $ROLE_DRIVER_USER    = 'DRIVER';
    public static $ROLE_PASSENGER_USER    = 'PASSENGER';
}
