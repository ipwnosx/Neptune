<?php
	/*
		Neptune Content Management System
		Index File - /index.php

		This file is basically a loader for the rest of the Neptune CMS. It
		loads all of the core functions, loads the configuration, initializes
		the Neptune CMS, loads and initializes all modules, runs any modules
		or functions required to satisfy the request, then passes the output
		to the templating engine.
	*/
	
	$time=microtime();
	$starttime=substr($time,11).substr($time,1,9);
	
	// Defining NEPNEP for security purposes
	define('NEPNEP', true, true);
	
	// Create the global array that will be used in many system functions to
	// store state data.
	global $Neptune;
	
	// Load the core class file
	require_once('system/core/main.php');
	// Making the core objects accessable
	global $NeptuneCore;
	global $NeptuneSQL;
	if(!isset($NeptuneCore)) {
		$NeptuneCore = new NeptuneCore();
	}
	
	// Loading the rest of the core files. 
	require_once('system/core/bbcode.php');
	require_once("system/core/tidy.php");
	require_once('system/core/parseconf.php');
	parseconf('system/config/core.php');

	require_once("system/core/useraccounts.php");

	require_once("system/core/init.php");


	// After this, we will take the query string and extract all of the data
	// from it. This is intentionally done in a way that makes $_GET impossible
	// to use. We want to keep all URLs clean.

	// Prevent PHP from displaying a warning if there is no query string.
	if (isset($_SERVER["QUERY_STRING"]) && !empty($_SERVER["QUERY_STRING"])) {
		// Take each part of the query string, and split it into an array. The
		// first value (0) is how functions hook themselves to requests.
		$NeptuneCore->var_set("system","query",explode("/",$_SERVER["QUERY_STRING"]));
	} else {
		// If there is no query string, use the default function hook instead.
		$NeptuneCore->var_set("system","query",array($NeptuneCore->var_get("config","defaultact")));
	}

	
	// Enumerate modules. 
	if ($handle = opendir('modules')) { 
		while (false !== ($dir = readdir($handle))) { 
			if ($dir != "." && $dir != ".." && is_dir("modules/" . $dir)) { 
				include("modules/$dir/module.php"); 
			} 
		} 
		closedir($handle); 
	}
		
	// Run whatever function is hooked to the current request.
	$NeptuneCore->hook_run($NeptuneCore->var_get("system","query"));
	
	if ($NeptuneCore->var_get("output","body") != "") {
		$NeptuneCore->var_set("output","body", clean_html_code($NeptuneCore->var_get("output","body")));
	} else {
		$NeptuneCore->var_set("output","body","");
	}
	
	require("theme/layout.php");
?>