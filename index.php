<!doctype html>
<html>
  <head>
    <style type="text/css">
      .card {
        width: 300px;
        border-radius: 5px;
        border: 1px solid black;
        margin: 5px;
        padding: 10px;
        min-height: 20vw;
        float: left;
        max-height: 350px;
        overflow: scroll;
      }

      .card a {
        display: block;
        font-size: 2em;
        margin-bottom: 10px;
      }

      .card:hover {
        background: rgb(250,250,250);
      }
    </style>
  </head>
  <body>
<?php

// load config
require_once "config.php";
require_once "lib/asana.php";



$projects = json_decode(file_get_contents("projects.json"));


foreach ($projects as $project) {
   //print gettype($project);

   switch ($project->name) {
    case stristr($project->name, "Welcome to Redstamp"):
    case stristr($project->name, "Onboarding"):
    case stristr($project->name, "Employee Offboarding"):
    case stristr($project->name, "Employee Onboarding"):
    case stristr($project->name, "Contractor Offboarding"):
    case stristr($project->name, "Contractor Onboarding"):
      break;


    default:
      $pl[$project->modified_at] = $project;
      break;
   }


}


krsort($pl);
$projects = $pl;


foreach ($projects as $project) {

   if (date("Y-m-d", strtotime($project->modified_at)) > date("Y-m-d", strtotime("-3 months"))) {

   $custom_fields = json_decode($project->custom_fields);


   $current_status = json_decode($project->current_status);

   $status = $current_status->text;
   $status_author = $current_status->author->name;
   $status_date = date("Y-m-d", strtotime($current_status->modified_at));


   foreach ($custom_fields as $field) {
     switch ($field->name) {

        case 'Project Stage':
          $stage = $field->enum_value->name;

     }
   }

   $output = sprintf("<div class='card'><a href='project.php?projectid=%s'>%s</a>
   <div class='due'>Due: %s</div>
   <div class='lastmod'><b>Last modified: %s</b></div>
   <div class='stage'>Stage: %s</div>
   <div class='status'>Status: [%s] | Updated by:  %s</div>
   <div class='status_text'> %s</div>
    </div>", $project->gid, $project->name, $project->due_date, date("Y-m-d", strtotime($project->modified_at)), $stage, $status_date, $status_author, $status );

   print $output;

  }
//   var_dump($project);
  // print $project['data']['name'];
}



function apicall($method, $payload = null, $calltype = "GET") {
  global $cfg;

  $args = "";
  if ($payload) {
    $args = http_build_query($payload);
  }

  $url = $cfg->asana_url . $method;

  if ($calltype == "GET") {
    $url .=  "?" . $args;
  }



  $headers = [
    "Authorization: Bearer {$cfg->asana_token}",
    "User-Agent: Redstamp (darren@redstamp.com)",
    "Content-type: application/x-www-form-urlencoded",
  ];


  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
  curl_setopt($ch, CURLOPT_HEADER, false);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

  if ($calltype == "POST") {
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
  }

  if ($calltype=="PATCH") {
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
  }

  $ch_response = curl_exec($ch);
  $errors = curl_error($ch);
  if ($errors) {
    error_log(json_encode($errors));
    return FALSE;
  }

  $details = json_decode($ch_response);

  curl_close($ch);
  return $details;





}
