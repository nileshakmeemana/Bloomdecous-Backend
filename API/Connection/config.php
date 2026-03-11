<?php
	// $conn = mysqli_connect("localhost", "root", "", "db_bloomdecous");
	// $conn = mysqli_connect("localhost", "mdrizxbowc_bloomdeco", "I~mqQ26*~URT", "mdrizxbowc_db_bloomdecous");

	$conn = mysqli_connect("localhost", "mdrizxbowc_bloomdeco_uat", "anJ*f$72[R_#", "mdrizxbowc_db_bloomdecous_uat");

	if(!$conn){
		die("Error: Failed to connect to database!");
	}

	// TinyMCE API Key UAT
	define("TINYMCE_API_KEY", "9lf9h735jucnqfgf4ugu8egij1icgzsrgbcmsk5tg44cjba8");
	
	// TinyMCE API Key PROD
	// define("TINYMCE_API_KEY", "2d97jyc4gvuspgz5colxqhp9cfvv2ob07mrjmmtat4v9cd7c");
?>