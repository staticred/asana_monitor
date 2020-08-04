<!doctype html>
<html>
  <head>
    <style type="text/css">

      #content {
        width: 90vw;
        margin: auto;
      }

      .nav {
        width: 90vw;
        margin: auto;
        font-size: 2em;
      }

      h1 {
        margin: 0px;
        margin-top: 1em;
        padding: 0px;
        font-size: 1.8em;
      }

      .card {
      width: 275px;
      border-radius: 5px;
      border: 1px solid black;
      margin: 5px;
      padding: 10px;
      min-height: 400px;
      max-height: 400px;
      overflow: scroll;
      float: left;
      }

      .card a {
      display: block;
      color: black;
      font-size: 1em;
      margin-bottom: 10px;
      }

      .card:hover {
        color: black !important;
        background: rgb(250,250,250);
      }

      .card:hover A {
        color: black !important;
        background: transparent !important;
      }

      .incomplete {
        background-color: rgb(239, 245, 239);
      }
      .incomplete:hover {
        background-color: rgb(224, 235, 224);
      }



      .completed {
        color: black;
        background-color: rgb(200,220,200);
      }

      .completed:hover {
        color: black;
        background-color: rgb(177, 205, 177);
}

      .nodue:hover {
        background-color: blue;
      }


      .nodue {
        background-color: rgb(255, 198, 179);
      }
      .nodue:hover {
        background-color: rgb(225, 168, 149) !important;
      }


      .blocked {
        background-color: orange !important;
      }
      .blocked:hover {
        background-color: rgb(230, 149, 01) !important;
      }


      .overdue div.completed {
        color: red;
      }
      .incomplete.overdue, .incomplete.overdue A {
        background-color: rgb(204, 0, 0);
        color: rgb(200,200,200);
      }
      .incomplete.overdue:hover,.incomplete.overdue:hover A  {
        background-color: rgb(153, 0, 0);
        color: rgb(200,200,200) !important;
      }


      .incomplete.overdue:before {
        margin-top: 1em;
        content: "⚠️ Overdue";
        display: block;
      }

      .incomplete.nodue:before {
        margin-top: 1em;
        content:"🗓 No due date";
        display: block;"
      }

      .incomplete.blocked:before {
        margin-top: 1em;
        content: "⛔️️ Blocked";
        display: block;
      }

      .completed:before {
        margin-top: 1em;
        content: "✅ Completed";
        display: block;
      }

      .title {
        margin-top: 2em;
        margin-bottom: 2em;
        font-size: 2em;
      }

    </style>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <script>

        //
        //   $('#blockonly').click( function() {
        //     console.log("hi");
        //     $('.card').hide(100);
        //     $('.blocked').show(400);
        //   })
        $( document ).ready(function() {
          console.log("loaded");
          $( "#blockonly" ).on('click', function() {
            console.log("clicked");
            $(".card").hide("fast", function() {
              // animation
            });
            $(".blocked").show("fast", function() {
              // animation
            });
            return false;
          });

          $( "#overdueonly" ).on('click', function() {
            console.log("clicked");
            $(".card").hide("fast", function() {
              // animation
            });
            $(".overdue").show("fast", function() {
              // animation
            });
            $('.completed').hide("fast", function() {
              // animation
            });
            return false;

          });

          $( "#nodueonly" ).on('click', function() {
            console.log("clicked");
            $(".card").hide("fast", function() {
              // animation
            });
            $(".nodue").show("fast", function() {
              // animation
            });
            $('.completed').hide("fast", function() {
              // animation
            });
            return false;
            
          });


          $( "#incompleteonly" ).on('click', function() {
            console.log("clicked");
            $(".card").hide("fast", function() {
              // animation
            });
            $(".incomplete").show("fast", function() {
              // animation
            });
            return false;

          });


          $( "#all" ).on('click', function() {
              console.log("clicked");
              $(".card").show("fast", function() {
                // animation
              });
            });
            return false;


      });



    </script>






  </head>
  <body>
    <div class="nav">
    <a href="index.php">&lt; back</a>
    <p>
      Show: <a href="#" id="all">All</a> | <a href="#" id="blockonly">Blocked</a> | <a href="#" id="nodueonly">No due date</a> | <a href="#" id="overdueonly">Overdue</a> | <a href="#" id="incompleteonly">Incomplete</a>
    </p>
    </div>
    <div id="content">

<?php

// load config
require_once "config.php";
require_once "lib/asana.php";


$project_id = isset($_REQUEST['projectid']) ? $_REQUEST['projectid'] : null;
if (is_null($project_id)) {
 die("No project id");
}

$projects = json_decode(file_get_contents("projects.json"));
$name = $projects->$project_id->name;

print "<div class='content title'><h1>{$name}</h1></div>";



$filename = "data/{$project_id}_tasks.json";
$tasks = json_decode(file_get_contents($filename));

if (empty($tasks)) {
  $since = date("Y-m-d", strtotime("-6 months"));
  print "No task data for this project since {$since} ";
  die();
}

// List projects


foreach ($tasks as $task) {
  $tl[$task->data->modified_at] = $task;
}

krsort($tl);
$tasks = $tl;



foreach ($tasks as $task) {

  $gid = $task->data->gid;
  $name = $task->data->name;

  // Get task info


  $method = "/tasks/{$gid}";
//   $task_details = apicall($method, $payload);

  // print "<hr>";
  // print json_encode($task_details, JSON_PRETTY_PRINT);

  $completed = isset($task->data->completed_at) ? date("Y-m-d", strtotime($task->data->completed_at)) : null;
  $assigned = !empty($task->data->assignee->name) ? $task->data->assignee->name : "Nobody";
  if (!empty($completed))
  {
    $class[] = "completed";
  } else {
    $class[] = "incomplete";
  }

  if ($task->data->due_on <> "" && $task->data->due_on < date("Y-m-d")) {
    $class[] = "overdue";
  }

  if (empty($task->data->due_on)) {
    $class[] = "nodue";
  }


  $owner = $task->data->followers[0]->name;

  // var_dump($task->data->custom_fields);

  $custom_fields = $task->data->custom_fields;

  foreach ($custom_fields as $field) {
    // print "<hr>";
    // var_dump($field);
    // print "<hr>";

    switch ($field->name) {
      case 'Estimated Hours':
      case 'Estimated Hours (Total)':

        $estimate = $field->number_value;
        break;

      case 'Status':

        $status = $field->enum_value->name;
        if ($status == "Blocked") {
          $class[] = "blocked";
        }
        if ($status == "On Hold") {
          $class[] = "blocked";
        }
    }


  }

  if ($task->data->memberships[0]->section->name == "Blocked") {
    $class[] = "blocked";
  }

  $output = sprintf(
      "<div class='card %s'><h1><a href='https://app.asana.com/0/%s/%s' target='_asana'>%s</a></h1>
      <p>
        <div class='created'>Created: %s</div>
        <div class='mod'><strong>Last modified:  %s </strong></div>
        <div class='due'>Due: %s </div>
        <div class='assigned'>Assigned to: %s</div>
        <div class='completion'>Completed: %s </div>
        <div class='owner'>Owner: %s</div>
        <div class='status'>Assignee Status: %s</div>
        <div class='estimate'>Estimate: %s</div>
        <div class='status'>Status: %s</div>
        <div class='column'>Column: %s</div>
      </p></div>
        ",
      implode(" ", $class),
      $project_id,
      $task->data->gid,
      $name,
      date("Y-m-d", strtotime($task->data->created_at)),
      date("Y-m-d", strtotime($task->data->modified_at)),
      $task->data->due_on,
      $assigned,
      $completed,
      $owner,
      $task->data->assignee_status,
      $estimate,
      $status,
      $task->data->memberships[0]->section->name
    );




  print $output;
  unset($class);
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

?>
    </div>
  </body>
</html>
