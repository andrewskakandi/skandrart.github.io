<?php defined("_JEXEC") or die(file_get_contents("index.html"));

// Program: Fox Contact for Joomla
// Copyright (C): 2011 Demis Palma
// Documentation: http://www.fox.ra.it/forum/2-documentation.html
// License: Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html

// ------------------  standard plugin initialize function - don't change ---------------------------
global $sh_LANG;
$sefConfig = & shRouter::shGetConfig();  
$shLangName = '';
$shLangIso = '';
$title = array();
$shItemidString = '';
$dosef = shInitializePlugin( $lang, $shLangName, $shLangIso, $option);
if ($dosef == false) return;
// ------------------  standard plugin initialize function - don't change ---------------------------

shRemoveFromGETVarsList('Itemid');
shRemoveFromGETVarsList('lang');
shRemoveFromGETVarsList('option');

isset($task) || $task = null;
isset($Itemid) || $Itemid = null;
isset($view) || $view = null;

// We need to keep the view to be able to use the loader, but only for the loader itself
if ($view == "foxcontact")
{
	// In the default view, we want the view parameter to be removed.
	// We don't want an URL like http://localhost/Fox.html?view=foxcontact
	shRemoveFromGETVarsList("view");
}

$title[] = getMenuTitle($option, $task, $Itemid, null, $shLangName);
  
// ------------------  standard plugin finalize function - don't change ---------------------------  
if ($dosef){
   $string = shFinalizePlugin( $string, $title, $shAppendString, $shItemidString, 
      (isset($limit) ? @$limit : null), (isset($limitstart) ? @$limitstart : null), 
      (isset($shLangName) ? @$shLangName : null));
}      
// ------------------  standard plugin finalize function - don't change ---------------------------
  
