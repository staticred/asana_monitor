<?php
  
  class Asana {
    
    public $bearer_token;
    public $workspace;
    
    private function __construct() {
      global $cfg;
      $this->bearer_token = $this->load_token();
      $this->workspace = isset($cfg->asana->workspace) ? $cfg->asana->workspace : null;
    }
    
    
    public function get_projects($starttime=false, $endtime=FALSE) {
      $method = "/workspaces/{$cfg->asana_workspace}/projects";
      $payload = [
        'archived' => "false",
        'is_template' => "false",
      ];
      $projects = apicall($method, $payload);
      
      return json_encode($projects->data);
      
    }
    


    private function apicall($method, $payload=FALSE){

      global $cfg;
      $args = ""; 
      if ($payload) {
        $args = http_build_query($payload);        
      }
      $url = $cfg->asana_url . $method . "?" . $args;
    
      $headers = [
        "Authorization: Bearer {$this->bearer_token}",
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
          curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
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
     
     
    private function load_token() {
      global $cfg;
      $this->bearer_token = $cfg->asana->token;
    }
    
    
  }