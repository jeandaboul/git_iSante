<?php
/**
 * New account page
 *
 * @author	Svend Sorensen
 * @version	$Id: new.php 143 2006-10-25 21:05:15Z svends $
 */

require_once ('./include/app.php');

require_once ('isante-authorize.php');

function show_form($account, $errors = '')
{
	$title = gettext("New account");

	// Make strings HTML safe
	$form['username'] = htmlspecialchars($account->username);
	$form['firstname'] = htmlspecialchars($account->givenname);
	$form['lastname'] = htmlspecialchars($account->surname);
	$form['mail'] = htmlspecialchars($account->mail);
	$form['org'] = htmlspecialchars($account->organizationName);
	$form['phone'] = htmlspecialchars($account->telephoneNumber);
	$form['comments'] = htmlspecialchars($account->description);

	$submit_url = 'new.php?site=' . $_GET['site'] . '&lang=' . $_GET['lang'] . '&lastPid=' . $_GET['lastPid'];

	$cancel_url = '../LDAPform.php?site=' . $_GET['site'] . '&lang=' . $_GET['lang'] . '&lastPid=' . $_GET['lastPid'];

	include ('./include/new.tmpl.php');
}

function validate_form($account, $accounts, $password) {
	// Check password for errors
	$errors = $account->validate_pass();

	// Check account for errors
	$errors = array_merge($errors, $account->validate());

	// Check if sumitted passwords match
	if (!$account->check_password($password)) {
		$errors[] = gettext("Passwords do not match");
	}

	// Check if username is unique
	if ($accounts->find($account->username)) {
		$errors[] = gettext("Username exists");
	}

	return $errors;
}

// Attempt to create account, then go to db privilege page
function process_form($account, $accounts) {
	if ($accounts->create($account)) {
		//$_SESSION['messages']['info'][] = gettext("Account created");
		isanteAuthorize($account->username);
	} else {
		$_SESSION['messages']['error'][] = gettext("Create failed");
	}
	// Redirect to dbPrivilege page
	header ("Location: ../setPrivs.php?selUser=" . $account->username . "&lang=" . ((empty ($_GET['lang'])) ? $GLOBALS['def_lang'] : $_GET['lang']) . "&lastPid=" . $_GET['lastPid']);
	
}

// Create new account
$account = $accounts->new_account();

if (!$account) {
	// Unable to create new account
	$_SESSION['messages']['error'][] = gettext("Create failed");

	// Redirect back to list page
	header("Location: ../LDAPform.php?site=" . $_GET['site'] . "&lang=" . ((empty ($_GET['lang'])) ? $GLOBALS['def_lang'] : $_GET['lang']) . "&lastPid=" . $_GET['lastPid']);
}

if (isset($_POST['_submit_check'])) {
	// Form data has been submitted

	// Override account with form entries
	$account->password = $_POST['password'];
	$account->username = $_POST['username'];

	// The commonName is generated by concatenating the first and last names
	$account->commonName = sprintf('%s %s', $_POST['firstname'], $_POST['lastname']);
	$account->surname = $_POST['lastname'];
	$account->givenname = $_POST['firstname'];
	$account->mail = $_POST['mail'];
	$account->organizationName = $_POST['org'];
	$account->telephoneNumber = $_POST['phone'];
	$account->description = $_POST['comments'];
	$account->password = $_POST['password'];

	$password = $_POST['password2'];

	// If validate_form() returns errors, pass them to show_form(), else
	// process the form
	if ($errors = validate_form($account, $accounts, $password)) {
       	 	show_form($account, $errors);
	} else {
		process_form($account, $accounts);
	}
} else {
	// Form is being displayed for the first time

	show_form($account);
}
?>
