# PHP simple authorization server
This is a simple proof-of-concept PHP authorization server created as an [OpenID Connect Identity Provider for Okta](https://developer.okta.com/docs/guides/add-an-external-idp/openidconnect/configure-idp-in-okta/).

:information_source: **Disclaimer:** This is not an official Okta product and, as such, does not qualify for Okta Support. If you have any questions or you would like to report an issue, please open a new Issue inside this repository.

## Requirements
* An Okta preview account, called an _organization_ (you can sign up for a free [developer organization](https://developer.okta.com/signup/))
* A local web server that runs at least PHP 5.6 with mod_rewrite and openssl modules enabled
* Feature flag `GENERIC_OIDC_IDP` enabled for your Okta organization

## Configuration
* cert.txt needs to be accessible by the user under which php is running; this file contains the private key for signing the JWTs
* The general configuration details are found inside index.php between lines 8-15
* Access token body claims can be configured starting from line 29
* ID token body claims can be configured starting from line 48
* Userinfo claims can be configured starting from line 63

## Endpoints
### /authorize
The authorization endpoint verifies if the client_id and redirect_uri are the ones expected and, if yes, redirects the user back to the callback endpoint with an authorization code.

### /token
The token endpoint verifies if the client_id, client_secret, code, grant_type (which should be always `authorization_code`) and redirect_uri and, if yes, provides the JWTs requested.

### /keys
The keys endpoint returns the modulus and exponent for verifying the ID token and access token locally.

### /userinfo
The userinfo endpoint returns the additional claims for ID token. This endpoint does not verify the bearer token present.

### /admin-genprivkey
The admin-genprivkey endpoint is a non normative endpoint used to generate a new private key inside cert.txt.

### /admin-getall
The admin-getall endpoint is a non normative endpoint used to return the `private key`, `public key`, `exponent`, `modulus`, `access token` and `ID token` present in the authorization server.

## Notes
* The authorization server endpoints are available under any directory starting from the directory where index.php is available in order to provide flexibility in testing
* The best method to test the authorization server is by deploying the authorization server locally and publish it through [ngrok](https://ngrok.com/).
* When exchanging the authorization code for tokens, Okta uses exclusively `client_secret_post` and, as such, the server is designed to accept the credentials only in this format.
* JWTs generated are always signed using SHA-256 algorithm.