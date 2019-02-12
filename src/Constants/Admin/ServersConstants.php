<?php

namespace GameX\Constants\Admin;

class ServersConstants
{
    const ROUTE_LIST = 'admin_servers_list';
    const ROUTE_VIEW = 'admin_servers_view';
    const ROUTE_CREATE = 'admin_servers_create';
    const ROUTE_EDIT = 'admin_servers_edit';
    const ROUTE_DELETE = 'admin_servers_delete';
    const ROUTE_TOKEN = 'admin_servers_token';
    
    const PERMISSIONS_GROUP = 'admin';
    
    const PERMISSION_GROUP = 'admin';
    const PERMISSION_TYPE = null;
    const PERMISSION_KEY = 'server';
    
    const PERMISSION_TOKEN_GROUP = 'admin';
    const PERMISSION_TOKEN_TYPE = 'server';
    const PERMISSION_TOKEN_KEY = 'server_token';
}
