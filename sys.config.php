<?php
date_default_timezone_set('America/Los_Angeles');

@define("DOCUMENT_SUBROOT", "");

//////////////////////////////////////////////////////////////////////
// map of tokens used in nested rules to the table
// that has the matching api_secret, and the field
// containing the id value that the token refers to
// For Example on {:selfCompanyId:} we don't use
// accounting_accounts.id because this would not have
// the --> api_secret <-- that is unique to the user.
// There can be multiple admins. They are just users
// marked with is_admin
//////////////////////////////////////////////////////////////////////
if (!isset($NESTED_RULES_TOKENS)) {
    $NESTED_RULES_TOKENS = array(
    );
}

//////////////////////////////////////////////////////////////////////
// the user's token will match a session with one of these
// user types.  The type is written into the session when
// they log in.  They log in specifying "account", "affiliate",
// "siteAdmin". The authentication code may or may not transform the
// userType to be more specific, eg accountUser or accountAdmin.
// The value is the table that should contain the api_secret matching
// the session's api_secret
//////////////////////////////////////////////////////////////////////
if (!isset($AUTH_ALLOWED_TYPES)) {
    $AUTH_ALLOWED_TYPES = array(
    );
}

////////
// V2
////////

if (!isset($USER_TYPES_TABLES)) {
    $USER_TYPES_TABLES = array(
    );
}

//////////////////////////////////////////////////////////////////////
// for each usertype being requested, this specifies the field
// containing the "username". It is assumed there's a field called
// "password" in all cases
//////////////////////////////////////////////////////////////////////
if (!isset($AUTH_BY_LOGIN)) {
    $AUTH_BY_LOGIN = array(
    );
}

@define("SESSION_DURATION", 840); //seconds before token expires if not refreshed

@define("PASSWORD_BCRYPT", 10);