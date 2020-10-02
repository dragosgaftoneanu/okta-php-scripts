# PHP SCIM Server
PHP SCIM Server is a sample SCIM 2.0 server written in PHP that supports /Users and /Groups endpoint. This application was created based on [Andrei Hava](https://github.com/andreihava-okta)'s [NodeJS SCIM server](https://github.com/andreihava-okta/sample-node-scim-server) in order to test SCIM capabilities with Okta SCIM enabled applications.

:information_source: **Disclaimer:** This SCIM server was built in order to troubleshoot different SCIM use-cases and not to be used in production. The script is provided AS IS without warranty of any kind. Okta disclaims all implied warranties including, without limitation, any implied warranties of fitness for a particular purpose. We highly recommend testing scripts in a preview environment if possible.

## Requirements
* An Okta account, called an _organization_ (you can sign up for a free [developer organization](https://developer.okta.com/signup/))
* A local web server that runs PHP 7.0 with MySQLi extension and mod_rewrite module
* [ngrok](https://ngrok.com/) in order to inspect the requests and responses

## Installation
* Download the PHP SCIM Server and upload it in the document root of your local web server
* Create a MySQL database and add the configuration details in configuration.php
* Use `ngrok http 80` to put the web server online and link the ngrok URL with Okta

## /Users endpoint operations
### Creating a User
User creation can be done through a POST request to `${SCIM_Base_Url}/SCIM/v2/Users`. The SCIM server will automatically generate a universally unique identifier.

```
POST /SCIM/v2/Users HTTP/1.1
Accept: application/scim+json
Content-Type: application/scim+json; charset=utf-8
Authorization: Bearer <token>

{
    "schemas": ["urn:ietf:params:scim:schemas:core:2.0:User"],
    "userName": "test.user@okta.local",
    "name": {
        "givenName": "Test",
        "middleName": "van",
        "familyName": "User"
    },
    "emails": [{
        "primary": true,
        "value": "test.user@okta.local",
        "type": "work"
    }],
    "displayName": "Test User",
    "groups": [],
    "active": true
}
```

### Listing Users with pagination 
Users listing can be done through a GET request to `${SCIM_Base_Url}/SCIM/v2/Users`. The server ONLY supports user listing through `startIndex` and `count` variables.

```
GET /SCIM/v2/Users HTTP/1.1
Accept: application/scim+json
Content-Type: application/scim+json; charset=utf-8
Authorization: Bearer <token>
```

### Listing Users by filter
Users listing by filter can be done using "eq" operation in filter GET attribute. Currently, filtering can be done ONLY on `id`, `active`, `userName`, `givenName`, `middleName`, `familyName` or `email`.

```
GET /SCIM/v2/Users?filter=userName+eq+"test.user@okta.local" HTTP/1.1
Accept: application/scim+json
Content-Type: application/scim+json; charset=utf-8
Authorization: Bearer <token>
```

### Listing a User By ID
User listing by ID is done in the standard format by doing a GET request to `${SCIM_Base_Url}/SCIM/v2/Users/${User_ID}`.

```
GET /SCIM/v2/Users/3896ca1f-9cf5-4a52-8454-6e3f0a37c4af HTTP/1.1
Accept: application/scim+json
Content-Type: application/scim+json; charset=utf-8
Authorization: Bearer <token>
```

### Updating/Deactivating a User through PATCH 
User modification/deactivation is done through a PATCH request to `${SCIM_Base_Url}/SCIM/v2/Users/${UserId}`.

```
PATCH /SCIM/v2/Users/3896ca1f-9cf5-4a52-8454-6e3f0a37c4af HTTP/1.1
Accept: application/scim+json
Content-Type: application/scim+json; charset=utf-8
Authorization: Bearer <token>

{
    "schemas": ["urn:ietf:params:scim:api:messages:2.0:PatchOp"],
    "Operations": [{
        "op": "replace",
        "value": {
            "active": false
        }
    }]
}
```

### Updating a User through PUT
SCIM Provisioning feature for applications created through Application Integration Wizard update user details through PUT requests. 

```
PUT /scim/v2/Users/3896ca1f-9cf5-4a52-8454-6e3f0a37c4af HTTP/1.1
Accept: application/scim+json
Content-Type: application/scim+json; charset=utf-8
Authorization: Bearer <token>

{
    "schemas": ["urn:ietf:params:scim:schemas:core:2.0:User"],
    "id": "3896ca1f-9cf5-4a52-8454-6e3f0a37c4af",
    "userName": "test.user@okta.local",
    "name": {
        "givenName": "Test",
        "familyName": "User"
    },
    "emails": [{
        "primary": true,
        "value": "test.user@okta.local",
        "type": "work"
    }],
    "active": true,
    "groups": []
}
```

## /Groups endpoint operations
### Creating a Group
Group creation can be done through a POST request to `${SCIM_Base_Url}/SCIM/v2/Groups`. The SCIM server will automatically generate a universally unique identifier.

```
POST /SCIM/v2/Groups HTTP/1.1
Accept: application/scim+json
Content-Type: application/scim+json; charset=utf-8
Authorization: Bearer <token>

{
    "schemas": ["urn:ietf:params:scim:schemas:core:2.0:Group"],
    "displayName": "test group",
    "members": []
}
```

### Listing Groups with pagination
Groups listing can be done through a GET request to `${SCIM_Base_Url}/SCIM/v2/Groups`. The server ONLY supports user listing through `startIndex` and `count` variables.

```
GET /SCIM/v2/Groups HTTP/1.1
Accept: application/scim+json
Content-Type: application/scim+json; charset=utf-8
Authorization: Bearer <token>
```

### Listing Groups by filter
Groups listing by filter can be done using "eq" operation in filter GET attribute. Currently, filtering can be done ONLY on `id` or `displayName`.

```
GET /SCIM/v2/Groups?filter=displayName+eq+"test group" HTTP/1.1
Accept: application/scim+json
Content-Type: application/scim+json; charset=utf-8
Authorization: Bearer <token>
```

### Listing a Group By ID
Group listing by ID is done in the standard format by doing a GET request to `${SCIM_Base_Url}/SCIM/v2/Groups/${Group_ID}`.

```
GET /SCIM/v2/Groups/35a7636a-00c5-4257-b19b-d11ef0638b66 HTTP/1.1
Accept: application/scim+json
Content-Type: application/scim+json; charset=utf-8
Authorization: Bearer <token>
```

### Updating Group Name
Group name update is done through a PATCH request to `${SCIM_Base_Url}/SCIM/v2/Groups/${Group_ID}`.

```
PATCH /SCIM/v2/Groups/35a7636a-00c5-4257-b19b-d11ef0638b66 HTTP/1.1
Accept: application/scim+json
Content-Type: application/scim+json; charset=utf-8
Authorization: Bearer <token>

{
    "schemas": ["urn:ietf:params:scim:api:messages:2.0:PatchOp"],
    "Operations": [{
        "op": "replace",
        "value": {
            "id": "35a7636a-00c5-4257-b19b-d11ef0638b66",
            "displayName": "new group name"
        }
    }]
}
```

### Adding a User To a Group
Adding users to the group is done through a PATCH request to `${SCIM_Base_Url}/SCIM/v2/Groups/${Group_ID}`.

```
PATCH /SCIM/v2/Groups/35a7636a-00c5-4257-b19b-d11ef0638b66 HTTP/1.1
Accept: application/scim+json
Content-Type: application/scim+json; charset=utf-8
Authorization: Bearer <token>

{
    "schemas": ["urn:ietf:params:scim:api:messages:2.0:PatchOp"],
    "Operations": [{
        "op": "add",
        "path": "members",
        "value": [{
            "value": "3896ca1f-9cf5-4a52-8454-6e3f0a37c4af",
            "display": "test.user@okta.local"
        }]
    }]
}
```

### Updating a Group Through PUT
SCIM Provisioning feature for applications created through Application Integration Wizard update group details through PUT requests. 

```
PUT /SCIM/v2/Groups/35a7636a-00c5-4257-b19b-d11ef0638b66 HTTP/1.1
Accept: application/scim+json
Content-Type: application/scim+json; charset=utf-8
Authorization: Bearer <token>

{
    "schemas": ["urn:ietf:params:scim:schemas:core:2.0:Group"],
    "id": "35a7636a-00c5-4257-b19b-d11ef0638b66",
    "displayName": "new group name",
    "members": []
}
```

### Delete Group
Deleting a group is done through a DELETE request to `${SCIM_Base_Url}/SCIM/v2/Groups/${Group_ID}`.

```
DELETE /SCIM/v2/Groups/35a7636a-00c5-4257-b19b-d11ef0638b66 HTTP/1.1
Accept: application/scim+json
Content-Type: application/scim+json; charset=utf-8
Authorization: Bearer <token>
```