<?php
    
    $cfg = new stdClass;
    ini_set('date.timezone','America/Vancouver');
    
   
    // Let's get a working path we can live with. 
    $pathinfo = pathinfo(realpath("config.php"));
    $cfg->basepath = $pathinfo['dirname'];
    
    
    // and set some other paths.
    $cfg->libraries = $cfg->basepath . "/lib";
    $cfg->imgdir = $cfg->basepath . "/img";


    // Third-party API accounts.

    $cfg->asana_token = "";
    $cfg->asana_workspace = "";
    $cfg->asana_url = "https://app.asana.com/api/1.0";

    
// create a data directory
if (!file_exists($cfg->basepath . "/data")) {
  mkdir($cfg->basepath . "/data");
}

