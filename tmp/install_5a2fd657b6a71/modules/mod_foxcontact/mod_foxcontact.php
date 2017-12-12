<?php defined("_JEXEC") or die(file_get_contents("index.html"));

// Program: Fox Contact for Joomla
// Copyright (C): 2011 Demis Palma
// Documentation: http://www.fox.ra.it/forum/2-documentation.html
// License: Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html

// Avoid multiple instances of the same module when called by both template and content (using loadposition)
if (isset($GLOBALS["foxcontact_mid_" . $module->id])) return;
else $GLOBALS["foxcontact_mid_" . $module->id] = true;

// Turns off the cache when rendered as a module
$cache = JFactory::getCache("com_modules", "");
$cache->setCaching(false);

// Turns off the cache when rendered within an article using {loadposition}
$cache = @JFactory::getCache("com_content", "view");
// Muted due to msg: PHP Strict Standards:  Declaration of JCacheControllerView::get() should be compatible with that of JCacheController::get() in /var/www/fc20/libraries/joomla/cache/controller/view.php on line 137
$cache->setCaching(false);

$GLOBALS["ext_name"] = basename(__FILE__);
$GLOBALS["com_name"] = realpath(dirname(__FILE__) . "/../../components");
$GLOBALS["mod_name"] = dirname(__FILE__);
$GLOBALS["EXT_NAME"] = strtoupper($GLOBALS["ext_name"]);
$GLOBALS["COM_NAME"] = strtoupper($GLOBALS["com_name"]);
$GLOBALS["MOD_NAME"] = strtoupper($GLOBALS["mod_name"]);
$GLOBALS["left"] = false;
$GLOBALS["right"] = true;
$app->owner = "module";
$app->oid = $module->id;
$app->cid = 0;
$app->mid = $module->id;
$app->submitted = (bool)count($_POST) && isset($_POST["mid_$app->mid"]);
$me = basename(__FILE__);
$name = substr($me, 0, strrpos($me, '.'));
include(realpath(dirname(__FILE__) . "/" . $name . ".inc"));

$helpdir = JPATH_BASE . "/components/com_foxcontact/helpers/";
require_once($helpdir . 'fieldsbuilder.php');
include_once($helpdir . 'fsubmitter.php');
include_once($helpdir . 'fajaxuploader.php');
include_once($helpdir . 'fcaptcha.php');
include_once($helpdir . 'fantispam.php');
require_once($helpdir . "fnewsletter.php");
require_once($helpdir . "acymailing.php");
require_once($helpdir . "jnews.php");
require_once($helpdir . "messageboard.php");
require_once($helpdir . "document.php");

// Dispatchers
$dispatchers_dir = $helpdir . "dispatchers/";

require_once $dispatchers_dir . "fadminmailer.php";
require_once $dispatchers_dir . "fsubmittermailer.php";
require_once $dispatchers_dir . "fjmessenger.php";
require_once $dispatchers_dir . "database.php";

$libsdir = JPATH_BASE . "/components/com_foxcontact/lib/";
include_once($libsdir . 'functions.php');

// Disable EmailCloak plugin. It only hurts when loaded using loadposition. Successful tested with Modules Anywhere.
// $scope is set in libraries/cms/module/helper.php
if (isset($scope) && $scope == "com_content")
{
	echo("<!--{emailcloak=off}-->");
}

$foxDocument = FoxDocument::getInstance();

// User interface stylesheet
$foxDocument->addResource(array("root" => "media", "filename" => "foxcontact", "type" => "css"));

// User selected stylesheet
$stylesheet = $params->get("css", "bootstrap.css");
$stylesheet = preg_replace("/\\.[^.\\s]{3,4}$/", "", $stylesheet);
$foxDocument->addResource(array("root" => "components", "filename" => $stylesheet, "type" => "css"));

//$link = FGetLink(NULL, "#mid_" . $module->id);
// FGetLink doesn't work for blog view -> article, because active page is always blog view even if you are into an article
$action = $app->input->server->get("REQUEST_URI", "", "string") . "#mid_" . $module->id;

// Load component language in addition
$language = JFactory::getLanguage();
// Reload the default language (en-GB)
$language->load($GLOBALS["com_name"], JPATH_SITE, $language->getDefault(), true);
// Reload current language, overwriting nearly all the strings, but keeping the english version for untranslated strings
$language->load($GLOBALS["com_name"], JPATH_SITE, null, true);

$body = JResponse::getBody();
// JResponse::getBody() must be an empty string
if (!empty($body))
{
	// This module has been called by the onAfterRender event using nested modules
	// We can't add further resources like css / js
	echo
		JText::_("COM_FOXCONTACT_ADDITIONAL_SETTINGS_REQUIRED") .
		' <a href="http://www.fox.ra.it/forum/22-how-to/10274-nested-modules.html">' .
		JText::_("COM_FOXCONTACT_SEE_DOCUMENTATION") .
		"</a>";
	return;
}

// Fields properties
$page_subheading = $params->get("page_subheading", "");

// Module xml
$xml = JFactory::getXML(JPATH_SITE . '/modules/' . $app->scope . "/" . $app->scope . '.xml');

$messageboard = new FoxMessageBoard();
$submitter = new FSubmitter($params, $messageboard);
$fieldsBuilder = new FieldsBuilder($params, $messageboard);
$ajax_uploader = new FAjaxUploader($params, $messageboard);
$fcaptcha = new FCaptcha($params, $messageboard);
$antispam = new FAntispam($params, $messageboard, $fieldsBuilder);
$jMessenger = new FJMessenger($params, $messageboard, $fieldsBuilder);
$DatabaseDispatcher = new DatabaseDispatcher($params, $messageboard, $fieldsBuilder);
$newsletter = new FNewsletter($params, $messageboard, $fieldsBuilder);
$acymailing = new FAcyMailing($params, $messageboard, $fieldsBuilder);
$jnews = new FJNewsSubscriber($params, $messageboard, $fieldsBuilder);

$adminMailer = new FAdminMailer($params, $messageboard, $fieldsBuilder);
$submitterMailer = new FSubmitterMailer($params, $messageboard, $fieldsBuilder);

// Build $FormText
$form_text = "";
$form_text .= $fieldsBuilder->Show();
$form_text .= $ajax_uploader->Show();
$form_text .= $acymailing->Show();
$form_text .= $jnews->Show();
$form_text .= $fcaptcha->Show();
$form_text .= $antispam->Show();
// Usually we want the submit button at the bottom
$form_text .= $submitter->Show();

// Build $TopText and $BottomText
switch (0)
{
	case $submitter->IsValid():
		break;
	case $fieldsBuilder->IsValid():
		break;
	case $ajax_uploader->IsValid():
		break;
	case $fcaptcha->IsValid():
		break;
	case $antispam->IsValid():
		break;
	// Spam check passed or disabled
	case $jMessenger->Process():
		break;
	case $DatabaseDispatcher->Process():
		break;
	case $newsletter->Process():
		break;
	case $acymailing->Process():
		break;
	case $jnews->Process():
		break;

	case $adminMailer->Process():
		break;
	case $submitterMailer->Process():
		break;
	default: // None of the previous checks are failed
		// Avoid to show the Form and the button again
		$form_text = "";

		// Reset the solution of the captcha in the session after read, avoiding further (ab)uses of the same valid session
		$jsession = JFactory::getSession();
		$namespace = "foxcontact_module_" . $module->id;
		$jsession->clear("captcha_answer", $namespace);

		HeaderRedirect($params);
}

require(JModuleHelper::getLayoutPath($app->scope, $params->get('layout', 'default')));
