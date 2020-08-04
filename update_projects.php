<?php

// load config
require_once "config.php";
require_once "lib/asana.php";


// List projects

$method = "/workspaces/{$cfg->asana_workspace}/projects";
$payload = [
'archived' => "false",
'is_template' => "false",
];
$projects = apicall($method, $payload);

// var_dump($projects);


foreach ($projects->data as $project) {
//print gettype($project);

$method = "/projects/{$project->gid}";
$project_details = apicall($method);

$current_status = json_encode($project_details->data->current_status);
$custom_fields = json_encode($project_details->data->custom_fields);
$custom_field_settings = json_encode($project_details->data->custom_field_settings);
$followers = json_encode($project_details->data->followers);
$members = json_encode($project_details->data->members);
$owner = json_encode($project_details->data->owner);
$team = json_encode($project_details->data->team);
$workspace = json_encode($project_details->data->workspace);


$project_list[$project_details->data->gid] = [
  'name' => $project_details->data->name,
  'gid' => $project_details->data->gid,
  'archived' => $project_details->data->archived,
  'color' => $project_details->data->color,
  'created_at' => $project_details->data->created_at,
  'current_status' => $current_status,
  'custom_fields' => $custom_fields,
  'custom_field_settings' =>  $custom_field_settings,
  'due_on' =>  $project_details->data->due_on,
  'due_date' =>  $project_details->data->due_date,
  'followers' => $followers,
  'is_template' =>  $project_details->data->is_template,
  'members' =>  $members,
  'modified_at' => $project_details->data->modified_at,
  'notes' =>  $project_details->data->notes,
  'owner => ', $owner,
  'public' =>  $project_details->data->public,
  'resource_type' => $project_details->data->resource_type,
  'start_on' =>  $project_details->data->start_on,
  'team' =>  $team,
  'workspace' =>  $workspace,
];

$output = "Adding {$project_details->data->name}...\n";
// $output = sprintf("<a href='project.php?projectid=%s'>%s</a>
// <blockquote>Due: %s |  Last modified: %s</blockquote> <br/>", $project_details->data->gid, $project_details->data->name, $project_details->data->due_date, $project_details->data->last_modified);
//

print $output;
//   var_dump($project);
// print $project['data']['name'];
}

file_put_contents("projects.json", json_encode($project_list));




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
