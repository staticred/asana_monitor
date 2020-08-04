<?php
// load config
require_once "config.php";
require_once "lib/asana.php";


// Load project list

$project_id = isset($argv[1]) ? $argv[1] : FALSE;

if ($project_id) {

  print "Updating a single ID\n";

  $method = "/projects/{$project_id}/tasks";
  $payload = [
    'archived' => "false",
    'is_template' => "false",
    'limit' => 100,
    'updated_since' => date("Y-m-d", strtotime("-3 months")),
  ];
  $tasks = apicall($method, $payload);

  foreach ($tasks->data as $task) {
  $gid = $task->gid;
  $name = $task->name;
  // Get task info
  $method = "/tasks/{$gid}";
  $task_details = apicall($method, $payload);


  $created_at = date("Y-m-d", strtotime($task_details->data->created_at));
  $completed_at = date("Y-m-d", strtotime($task_details->data->completed_at));
  $due = date("Y-m-d", strtotime($task_details->data->due_on));
  $date = date("Y-m-d", strtotime("-6 months"));

  print "{$task->name} >> Created: {$created_at} | Due: {$due} | Cutoff: {$date}\n";

  if (($created_at > $date) || ($completed_at > $date)) {
    print "  - Adding {$name}\n";
    $tasklist[$task_details->data->gid] = $task_details;
  }
}


$filename = "data/{$project_id}_tasks.json";
file_put_contents($filename, json_encode($tasklist));


  die();
}


$projects = json_decode(file_get_contents("projects.json"));

if ($projects == "null") {
  $projects = [];
}

// print json_encode($projects, JSON_PRETTY_PRINT);;
// die();



foreach ($projects as $project) {



  $modified = date("Y-m-h", $project->modified_at);
  $modified = substr($project->modified_at, 0, 10);
  print "\n---------------\n";
  print "Amalyzing {$project->name} (Last modified: {$modified}\n";


  $limitdate = date("Y-m-d", strtotime("-7 days"));

  if ($modified <= $limitdate) {
    print ".";
    continue;
  }

  unset($modified);
  unset($limitdate);

  $project_id = $project->gid;


  // List projects

  $method = "/projects/{$project_id}/tasks";
  $payload = [
  'archived' => "false",
  'is_template' => "false",
  'limit' => 100,
  'updated_since' => date("Y-m-d", strtotime("-2 months")),
  ];
  $tasks = apicall($method, $payload);

  foreach ($tasks->data as $task) {

    $gid = $task->gid;
    $name = $task->name;
    // Get task info
    $method = "/tasks/{$gid}";
    $task_details = apicall($method, $payload);

    $task_limit =  date("U", strtotime("-3 months"));
    $task_modified = date("U", strtotime(substr($task_details->data->modified_at, 0, 10)));

    if ($task_modified <= $task_limit) {
      print ".";
      continue;
    }

    unset($task_modified);
    unset($task_limit);


    $created_at = date("Y-m-d", strtotime($task_details->data->created_at));
    $completed_at = date("Y-m-d", strtotime($task_details->data->completed_at));
    $date = date("Y-m-d", strtotime("-6 months"));

    if (($created_at > $date) || ($completed_at > $date)) {
      print "| Adding {$name} ";
      $tasklist[$task_details->data->gid] = $task_details;
    }
  }


  $filename = "data/{$project_id}_tasks.json";
  file_put_contents($filename, json_encode($tasklist));

  unset($tasklist);


}


foreach($tasklist as $project_id => $project) {
  $filename = "data/{$project_id}_tasks.json";

  foreach ($project as $tasks) {
    foreach ($tasks as $task) {
      $project_tasks[] = $task;
    }
  }

  file_put_contents($filename, json_encode($project_tasks));


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

if ($details->next_page) {
  $payload['offset'] = $details->next_page->offset;

  $results = apicall($method, $payload);
  $details->data = (object) array_merge((array) $details->data, (array) $results->data);
}

return $details;





}

?>
</div>
</body>
</html>
