<?php

return [

	'application-endpoint'                  => env('CORE_ENDPOINT'),            // CORE HTTPS API endpoint
	'application-identifier'                => env('CORE_APP_IDENTIFIER'),      // Application ID from CORE
	'application-group-base'                => env('CORE_APP_GROUP_BASE'),      // permission namespace for your application in core
	// leave empty to save all groups for a user

	'local-private-key'                     => env('CORE_LOCAL_PRIVATE_KEY'),  // locale private key from key generation
	'remote-public-key'                     => env('CORE_REMOTE_PUBLIC_KEY'),  // Application public key form CORE
];