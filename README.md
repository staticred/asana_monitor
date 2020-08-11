# Asana Monitor

This is a set of scripts that creates a holistic view of changes to an entire portfolio of Asana projects. 


# Installing

Asana Monitor is intended to be run as a web service. 

1. Copy `config-dist.php` to `config.php`. 
2. Set your timezone (if applicable).
3. Add your Asana API token to `$cfg->asana_token`.
4. Add your Asana Workspace ID to `$cfg->asana_workspace`. 
5. Save `config.php` with the new values. 

# Updating data
The Asana monitor tool comes with two update scripts:
* `update_projects.php` -- this updates the list of all projects associated with your Asana workspace. I recommend running this daily. 
* `update_tasks.php` -- this script updates tasks within projects modified within the last 3 months. For performance reasons, the script only updates tasks modified within the last 7 days. 

I recommend running both scripts daily, between the end of the work day and the start of the next work day. Depending on the number of active projects in your organization, it may take several minutes to an hour to complete. 

# Accessing the report
To access the report, point your browser to the subdomain or directory you have installed asana monitor on. For example, https://example.com/asana-monitor/

You will be presented with a tabular grid of active projects. You can click on each project to see individual tasks. 

# **IMPORTANT**
This script has been created for a specific organization's use, and references custom fields that may or may not exist within your own Asana instance. 
