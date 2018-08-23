<?php
namespace GameX\Core\Auth\Permissions;

class Manager {
    const GROUP_USER = 'user';
    const GROUP_ADMIN = 'admin';

    const ACCESS_LIST= 1;
    const ACCESS_VIEW = 2;
    const ACCESS_CREATE = 4;
    const ACCESS_EDIT = 8;
    const ACCESS_DELETE = 16;
}