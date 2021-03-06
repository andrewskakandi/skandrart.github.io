<?php
/**
 * @version     2.0.0
 * @package     plg_jhackguard
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Valeri Markov <val@jhackguard.com> - http://www.jhackguard.com/
 */

defined('_JEXEC') or die;

/**
 * JHackGuard system plugin
 *
 * @package     plg_jhackguard
 */
class PlgSystemJhackguard extends JPlugin
{
    
    public $params; // Holds the com_jhackguard params
    public $log_level = 0; // 0 = none, 1 = standard, 2 = debug
    
    //IP Address check variables
    private $ip_checked = 0;
    private $ip_found = 0;
    private $ip_type = 'bl';
    
    //Botscout check variables
    private $bot_scout_result = 0;
    
    public function __construct(&$subject, $config = array())
    {
        parent::__construct($subject, $config);
        
        //Since 2.0 version the plugin itself has no params
        //Instead we fetch the component parameters.
        $this->params = JComponentHelper::getParams('com_jhackguard');
        $this->log_level = $this->params->get('log_level',1);
    }
    
    protected function add_log($message, $severity)
    {
        /* Compare the system log level and the log request level*/
        $log_this = FALSE;
        switch($severity)
        {
            case "debug":
                if($this->log_level == 2)
                    $log_this = TRUE;
                    break;
            case "standard":
                if($this->log_level > 0)
                    $log_this = TRUE;
                    break;
        } 
        //Shall we log?
        if(!$log_this)
        {
            unset($log_this);
            return FALSE;
        }
        unset($log_this);
        //We shall log.
        $log_entry = new stdClass();
        $log_entry->message = $message;
        $log_entry->severity = ucfirst($severity);
        $log_entry->ip_address = $_SERVER['REMOTE_ADDR']; //TODO: Shouldn't we check this value first?
        
        return JFactory::getDbo()->insertObject('#__jhackguard_logs', $log_entry);
    }

    function onAfterRender() {

        //Run output filters. We do not wish these to be applied to the administrator section however
        //since replacing the data will break the functionality of the output filters option itself.
        $app = JFactory::getApplication();
        if ($app->isAdmin())
        {
            return true;
        }

        $output = JResponse::getBody();
        
        // Get a db connection.
        $db = JFactory::getDbo();
        
        //Fetch the output filters from the database.
        $query = $db->getQuery(true);
        $query->select($db->quoteName(array('name','regex','replacement')));
        $query->from($db->quoteName('#__jhackguard_output_filters'));
        $query->where($db->quoteName('state') . ' = 1');
        $query->order('ordering ASC');
        $db->setQuery($query);
        $list = $db->loadObjectList();

        foreach($list as $item)
        {
            //This holds the number of replacements.
            $count = 0;

            //The actual replacement...
            $poutput = preg_replace($item->regex, $item->replacement, $output, -1, $count);
            if($poutput != NULL)
            {
                $output = $poutput;
                unset($poutput);
            }

            
            //Check if we did any replacement.
            if($count > 0)
            {
                $this->add_log('Output filter "'.$item->name.'" matched '.$count.' time(s).','debug');
            }
        }
        //Set the changed body of the document.
        JResponse::setBody($output);
    }
    
    public function onContentPrepareData($context, $data)
    {
        //Should we try and use BotScout for user registrations?
        if(!$this->params->get('botscout_user_registration',0))
        {
            return $data;
        }

        //We only need the com_users.registration page.
        if($context !== "com_users.registration")
        {
            return $data;
        }

        //And we only need to fire the trigger if we are on the registration task.
        if(is_array($_POST) AND isset($_POST['task']) AND $_POST['task'] == "registration.register")
        {

            //Yup.. right here.
            //We also need to extract the data out of it.
            $username = "";
            $email = "";

            if(isset($data->username) and strlen($data->username) >0)
            {
                $username = $data->username;
            }

            if(isset($data->email1) AND isset($data->email2) AND strlen($data->email1) > 0)
            {
                if($data->email1 == $data->email2)
                {
                    $email = $data->email1;
                }

            }
            //Verify this...
            if(strlen($username) > 0 and strlen($email) > 0)
            {
                if(strlen($this->params->get('botscout_api','')) < 2)
                {
                    $this->add_log('No botscout API key present, but botscout checks turned on.','debug');
                    return $data;
                }
                
                //Build the test string.
                $test_string = "http://botscout.com/test/?multi&mail=".urlencode($email)."&name=".urlencode($username)."&ip=".$_SERVER['REMOTE_ADDR']."&key=".$this->params->get('botscout_api','');

                if(function_exists('file_get_contents')){
                    $returned_data = file_get_contents($test_string);
                }else{
                    $ch = curl_init($test_string);
                    curl_setopt($ch, CURLOPT_HEADER, 0);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    $returned_data = curl_exec($ch);
                    curl_close($ch);
                }
                $botdata = explode('|', $returned_data); 
                //Check for errors 
                if($botdata[0] == "!")
                {
                    //This is bad. There is an error.
                    $this->add_log('Botscout returned an error: '.$returned_data,'debug');
                    return $data;
                }
                //Check for positive answer.
                if($botdata[0] == "Y")
                {
                    //Hit.. 
                    JFactory::getApplication()->enqueueMessage("Registration failed. Data matched in botscout.com database.");
                    JFactory::getApplication()->redirect(JRoute::_('index.php?option=com_users&view=registration', false));
                    //Well.... die!
                    die();
                }
            } 
            //Perhaps it wasn't the users registration page... Silently ignore this problem.
            return $data;
        }
        
    }

    public function onAfterInitialise($args=array())
    {
        //Are we enabled?
        if(!$this->params->get('enabled',1))
        {
            return 0;
        }

        //DB item.
        $db = JFactory::getDbo();
        
        $this->add_log('JHackGuard initialised','debug');
        
        /* Maintenance steps start */
        $probability = rand(0,100);
        
        //Clear the expired logs (probability of running 10%)
        if($probability < 11)
        {
            $days = (int) $this->params->get('log_garbage_collection',7);
            if(!$days)
            {
                $days = 7;
            }
            $query = $db->getQuery(true);      
            $query->delete($db->quoteName('#__jhackguard_logs'));
            $query->where($db->quoteName('time') . ' < DATE_SUB(CURRENT_DATE, INTERVAL '.$days.' DAY)');
            $db->setQuery($query);
            $db->query();
            $this->add_log('Logs garbage collector ran.','debug');
        }
        
        //Clear the expired IP address filters (probability: 60%)
        if($probability < 61)
        {
            $query = $db->getQuery(true);      
            $query->delete($db->quoteName('#__jhackguard_ip_filters'));
            $query->where($db->quoteName('expires') . ' BETWEEN DATE_SUB(CURRENT_DATE, INTERVAL 36500 DAY) AND DATE_SUB(CURRENT_DATE, INTERVAL 1 DAY)');
            $db->setQuery($query);
            $db->query();
            $this->add_log('IP filters garbage collector ran.','debug');
        }
        
        //Clear botscout cached records (probability: 50%)
        if($probability < 51)
        {
            $query = $db->getQuery(true);      
            $query->delete($db->quoteName('#__jhackguard_bot_scout'));
            $query->where($db->quoteName('expires') . ' <= CURRENT_DATE');
            $db->setQuery($query);
            $db->query();
            $this->add_log('BotScout garbage collector ran.','debug');
        }
        /* Maintenance steps end */

        /* Whitelisted Groups Check */
        $user = JFactory::getUser();
        $whg = $this->params->get('whitelisted_groups',array());
        foreach($user->groups as $g)
        {
            if(in_array($g, $whg))
            {
                $this->add_log('JHackGuard execution stopped because user group is whitelisted.','debug');
                return 0;
            }
        }
        
        /* IP black/white listing check */
        if($this->ip_is('whitelisted'))
        {
            //Cease further processing
            $this->add_log('Encountered whitelisted IP','debug');
            return 0;
        }
        
        if($this->ip_is('blacklisted'))
        {
            $this->add_log('Found blacklisted IP address. Script terminated.','standard');
            die(include_once(JPATH_ADMINISTRATOR.'/components/com_jhackguard/blocked.html'));
        }
        
        /* Botscout checks */
        if($this->params->get('botscout_enabled',1))
        {
            //Botscout is enabled. Check for a cached record.
            if(!$this->bot_scout_cache())
            {
                //No cache available. Fetch and insert one.
                $this->bot_scout_check();
            }
            
            if($this->bot_scout_result)
            {
                $this->add_log('Found BotScout listed IP address. Script terminated.','standard');
                die(include_once(JPATH_ADMINISTRATOR.'/components/com_jhackguard/botscout.html'));
            }
        }
        /* Botscout checks end */
        
        /* Administrator secret key addon */
        if($this->params->get('admin_protection',0))
        {
            //Verify we have a secret word setup in the configuration.
            if(strlen($this->params->get('admin_keyword','')) < 1)
            {
                $this->add_log('Administrator folder protection enabled but no keyword found.','debug');
            } else {
                //Check if the current page is in the administrator area.
                if(JFactory::getApplication()->isAdmin() AND JFactory::getUser()->guest)
                {
                    if(is_array($_POST) AND isset($_POST['option']) AND $_POST['option'] == "com_login" AND isset($_POST['task']) AND $_POST['task'] == "login")
                    {
                        //This is post login processing script and we do not want to filter it. 
                        //TODO: check if we have actually came from a login page.
                        //Probably CSRF token would do the trick here.
                    } else {
                        if(!array_key_exists($this->params->get('admin_keyword',''),$_GET))
                        {
                            //We have no secret word added to the URL. Redirecting to main page.
                            $this->add_log('No admin keyword found in request to administrator folder. Redirecting to main page. ','debug');
                            header("Location: ".JURI::base()."..");
                            die();
                        }
                    }
                }
            }
        }
        /* Administrator secret word checks end */

	/* Filter Administrator panel, jHackGuard pages. We do not want to filter our own content, after all */
	if(JFactory::getApplication()->isAdmin() AND !JFactory::getUser()->guest)
	{
		if(isset($_REQUEST['option']) AND $_REQUEST['option'] == "com_jhackguard")
		{
			$this->add_log('Admin page com_jhackguard is whitelisted. Filters skipped.','debug');
			return 0;
		}
	}
        
        /* Input filters Start */
        if(file_exists(JPATH_ADMINISTRATOR.'/components/com_jhackguard/data/input_rules.php') AND !file_exists(JPATH_ADMINISTRATOR.'/components/com_jhackguard/data/.disable_input_rules'))
        {
            require_once(JPATH_ADMINISTRATOR.'/components/com_jhackguard/data/input_rules.php');
            if (class_exists('JHackGuard_Input_Filters')) {
                $rules = new JHackGuard_Input_Filters();
                $rules->run();
            } else {
                $this->add_log('Cannot locate JHackGuard_Input_Filters class in data/input_rules.php. Perhaps no rules have been defined yet.','debug');
            }
            
        } else {
            $this->add_log('No input filters found or .disable_input_rules was defined.','debug');
        }
        /* End of Input filters */
        
        /* Scan through or disable the upload files, if configured */
        if($this->params->get('disable_uploads',0))
        {
            if(is_array($_FILES) AND count($_FILES) > 0)
            {
                JFactory::getApplication()->enqueueMessage("File upload denied by JHackGuard.","warning");
                $_FILES = array(); //TADAAA. This is not OK. We should probably delete the tmp files.
            }
        } 

        //Scan using or own rules.
        if($this->params->get('scan_uploads',1))
        {
            if(is_array($_FILES) AND count($_FILES) > 0 AND is_readable(JPATH_ADMINISTRATOR.'/components/com_jhackguard/data/scans/rules.php'))
            {
                foreach($_FILES as $key=>$file)
                {
                    $hit = 0;
                    if(isset($file['tmp_name']))
                    {
                        if(is_array($file['tmp_name']))
                        {
                            foreach($file['tmp_name'] as $tmp_file)
                            {
                                include_once(JPATH_ADMINISTRATOR.'/components/com_jhackguard/data/scans/rules.php');
                                $it = new SplFileInfo($tmp_file);
                                if($it->isFile() AND $it->isReadable())
                                {
                                    $s = new JHackGuard_OnDemand_Scan_Rules();
                                    $s->scan($it);
                                    if($s->score > 99)
                                    {
                                        $hit = 1;
                                        JFactory::getApplication()->enqueueMessage("File upload denied by JHackGuard. Internal scan hit.","warning");
                                        $this->add_log('Possible malicious file upload (score:'.$s->score.').File deleted.','standard');
                                    } else {
                                        $this->add_log('Clean file upload (score:'.$s->score.').','debug');
                                    }
                                }
                            }
                        }
                    }
                    //Do we have a hit?
                    if($hit)
                    {
                        unset($_FILES[$key]);
                    }                
                } //End foreach in $_FILES
            } 
        } //End if scan_uploads

        //Scan using CYMRU Malware Hash Registry.
        if($this->params->get('use_cymru',1))
        {
            if(is_array($_FILES) AND count($_FILES) > 0)
            {
                foreach($_FILES as $key=>$file)
                {
                    $hit = 0;
                    if(isset($file['tmp_name']))
                    {
                        if(is_array($file['tmp_name']))
                        {
                            foreach($file['tmp_name'] as $tmp_file)
                            {
                                $hash = md5_file($tmp_file);
                                if(checkdnsrr ($hash.".malware.hash.cymru.com","A"))
                                {
                                    JFactory::getApplication()->enqueueMessage("File upload denied by JHackGuard. Cymru malware hash database hit.","warning");
                                    $this->add_log('Possible malicious file upload (CYMRU Hash DB Hit).File deleted.','standard');
                                    $hit = 1;
                                }
                            }
                        }
                    }
                    //Do we have a hit?
                    if($hit)
                    {
                        unset($_FILES[$key]);
                    }                
                } //End foreach in $_FILES
            }
        } //End if use_cymru option.

        //END OF UPLOADED FILES CHECKS
        
    }
    
    public function bot_scout_check()
    {
        $this->add_log('No BotScout cache available. Performing lookup...','debug');
        //Check if API key is filled in.
        if(strlen($this->params->get('botscout_api','')) < 2)
        {
            $this->add_log('No botscout API key present, but botscout checks turned on.','debug');
            return 0;
        }
        
        //Build the test string.
        $test_string = "http://botscout.com/test/?ip=".$_SERVER['REMOTE_ADDR']."&key=".$this->params->get('botscout_api','');
        
        if(function_exists('file_get_contents')){
        	$returned_data = file_get_contents($test_string);
        }else{
        	$ch = curl_init($test_string);
        	curl_setopt($ch, CURLOPT_HEADER, 0);
        	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        	$returned_data = curl_exec($ch);
        	curl_close($ch);
        }
        $botdata = explode('|', $returned_data); 
        //Check for errors 
        if($botdata[0] == "!")
        {
            //This is bad. There is an error.
            $this->add_log('Botscout returned an error: '.$returned_data,'debug');
            return 0;
        }
        
        $bot_entry = new stdClass();
        $bot_entry->state = 1;
        $bot_entry->result = $botdata[0];
        $bot_entry->expires  = date('Y-m-d H:i:s',mktime(0, 0, 0, date("m")  , date("d")+1, date("Y")));
        $bot_entry->ip_address = $_SERVER['REMOTE_ADDR']; //TODO: Shouldn't we check this value first?
        JFactory::getDbo()->insertObject('#__jhackguard_bot_scout', $bot_entry);
        
        if($botdata[0] == "Y")
        {
            $this->bot_scout_result = 1;
        } 
        return TRUE;
    }
    
    public function bot_scout_cache()
    {
        // Get a db connection.
        $db = JFactory::getDbo();
        
        // Create a new query object.
        $query = $db->getQuery(true);
        $query->select($db->quoteName(array('result')));
        $query->from($db->quoteName('#__jhackguard_bot_scout'));
        $query->where($db->quoteName('ip_address') . ' = '. $db->quote($_SERVER['REMOTE_ADDR']));
        $query->where($db->quoteName('expires') . ' > '.$db->quote(date('Y-m-d H:i:s')));
        $query->where($db->quoteName('state') . ' = 1');
        $query->order('ordering ASC');
        
        $db->setQuery($query);
        $result = $db->loadObject();
        if(is_null($result)){
            return FALSE;
        } else {
            $this->add_log('Found BotScout cache record. No lookup is required.','debug');
            if(strtolower($result->result) == "n")
            {
                $this->bot_scout_result = 0;
            } else {
                $this->bot_scout_result = 1;
            }
            return TRUE;
        }
    }
    
    public function ip_is($type)
    {
        if($this->ip_checked)
        {
            //We already checked this IP address. No need to query the DB again.
            if($this->ip_found)
            {
                return ($type == $this->ip_type ? TRUE : FALSE);    
            } else {
                return FALSE;
            }
        }
        
        // Get a db connection.
        $db = JFactory::getDbo();
        
        // Create a new query object.
        $query = $db->getQuery(true);
        $query->select($db->quoteName(array('state', 'rule_type')));
        $query->from($db->quoteName('#__jhackguard_ip_filters'));
        $query->where($db->quoteName('ip') . ' = '. $db->quote($_SERVER['REMOTE_ADDR']));
        $query->where($db->quoteName('state') . ' = 1');
        $query->order('ordering ASC');
        
        $db->setQuery($query);
        $result = $db->loadObject();        
        
        if(is_null($result))
        {
            //No such record found
            $this->ip_checked = TRUE;
            $this->ip_found = FALSE;
        } 
        else 
        {
            $this->ip_checked = TRUE;
            $this->ip_found = TRUE;
            if($result->rule_type == 'wl'){
                $this->ip_type = 'whitelisted';
            } else {
                $this->ip_type = 'blacklisted';
            }
        }
        unset($query,$result);
        return $this->ip_is($type);
    }
}
//End of file: jhackguard.php
//Location: plugins/system/jhackguard/
