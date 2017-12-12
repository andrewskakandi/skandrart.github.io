<?php
/**
 * EBR - Easybook Reloaded for Joomla! 3
 * License: GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * Author: Viktor Vogel
 * Projectsite: http://joomla-extensions.kubik-rubik.de/ebr-easybook-reloaded
 *
 * @license GNU/GPL
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.model');

class EasybookReloadedModelEntry extends JModelLegacy
{
    protected $_data = null;
    protected $_id = null;
    protected $_badwords = null;
    protected $_input;

    function __construct()
    {
        parent::__construct();

        $this->_input = JFactory::getApplication()->input;

        if($hashrequest = $this->_input->getString('hash'))
        {
            $hash_data = explode('-', $hashrequest);

            if(count($hash_data) != 2)
            {
                return false;
            }

            if(!empty($hash_data[0]) AND is_numeric($hash_data[0]))
            {
                $id = $hash_data[0];
            }
        }
        else
        {
            $id = $this->_input->getInt('cid', 0);
        }

        $this->setId($id);
    }

    function setId($id)
    {
        $this->_id = $id;
        $this->_data = null;
    }

    function getData()
    {
        $mainframe = JFactory::getApplication();
        $params = JComponentHelper::getParams('com_easybookreloaded');
        $user = JFactory::getUser();

        if($this->_input->get('retry') == 'true')
        {
            $this->_data = $this->getTable('entry', 'EasybookReloadedTable');
            $this->_data->bind($mainframe->getUserState('eb_validation_data'));
        }

        if(empty($this->_data))
        {
            $query = "SELECT * FROM ".$this->_db->quoteName('#__easybook')." WHERE ".$this->_db->quoteName('id')." = ".$this->_db->quote($this->_id);
            $this->_db->setQuery($query);
            $this->_data = $this->_db->loadObject();
        }

        if(!$this->_data)
        {
            $this->_data = $this->getTable('entry', 'EasybookReloadedTable');
            $this->_data->id = 0;

            if($user->get('id'))
            {
                if($params->get('registered_username'))
                {
                    $this->_data->gbname = $user->get('name');
                }
                else
                {
                    $this->_data->gbname = $user->get('username');
                }
                $this->_data->gbmail = $user->get('email');
            }
        }

        return $this->_data;
    }

    function store()
    {
        jimport('joomla.utilities.date');

        $row = $this->getTable('entry', 'EasybookReloadedTable');
        $params = JComponentHelper::getParams('com_easybookreloaded');
        $user = JFactory::getUser();

        // Load all request variables - becaus JInput doesn't allow to load the whole data at once, a workaround
        //is used. This was easily possible with the deprecated JRequest (e.g. JRequest::get('post');)
        $data = $_REQUEST;
        array_walk($data, create_function('&$data', '$data = htmlspecialchars(strip_tags(trim($data)));'));

        // Get unfiltered request variable is only with a trick with JInput possible, so direct access is used instead
        // Possible solution: list($gbtext) = ($this->_input->get('gbtext', array(0), 'array') - use the filter array
        // With JRequest one could use - JRequest::getVar('gbtext', NULL, 'post', 'none', JREQUEST_ALLOWRAW)
        $data['gbtext'] = htmlspecialchars($_REQUEST['gbtext'], ENT_QUOTES);

        $session = JFactory::getSession();
        $date = JFactory::getDate();

        if($user->guest == 0 AND !_EASYBOOK_CANEDIT)
        {
            if($params->get('registered_username'))
            {
                $data['gbname'] = $user->get('name');
            }
            else
            {
                $data['gbname'] = $user->get('username');
            }

            $data['gbmail'] = $user->get('email');
        }

        if(!isset($data['id']))
        {
            $data['gbdate'] = $date->toSql();
            $data['published'] = $params->get('default_published', 1);

            if($params->get('enable_log', true))
            {
                $data['gbip'] = getenv('REMOTE_ADDR');
            }
            else
            {
                $data['gbip'] = '0.0.0.0';
            }

            $data['gbcomment'] = null;
        }

        if(!$this->validate($data))
        {
            return false;
        }

        if(!$row->bind($data))
        {
            $this->setError($this->_db->getErrorMsg());

            return false;
        }

        if(!$row->store())
        {
            $this->setError($this->_db->getErrorMsg());

            return false;
        }

        $session->clear('spamcheck1', 'easybookreloaded');
        $session->clear('spamcheck2', 'easybookreloaded');
        $session->clear('spamcheckresult', 'easybookreloaded');
        $session->clear('spamcheck_field_name', 'easybookreloaded');
        $session->clear('operator', 'easybookreloaded');
        $session->clear('time', 'easybookreloaded');
        $session->clear('spamcheck_question_field_name', 'easybookreloaded');

        return $row;
    }

    function delete()
    {
        $row = $this->getTable('entry', 'EasybookReloadedTable');

        if(!$row->delete($this->_id))
        {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        return true;
    }

    function publish()
    {
        $data = $this->getData();
        $status = $data->published;

        $query = "UPDATE ".$this->_db->quoteName('#__easybook')." SET ".$this->_db->quoteName('published')." = ".$this->_db->quote(!$status)." WHERE ".$this->_db->quoteName('id')." = ".$this->_db->quote($this->_id)." LIMIT 1;";
        $this->_db->SetQuery($query);

        if(!$this->_db->query())
        {
            $this->setError($this->_db->getErrorMsg());
            return -1;
        }

        return (int)!$status;
    }

    function getCalcCheck()
    {
        $params = JComponentHelper::getParams('com_easybookreloaded');
        $session = JFactory::getSession();
        $user = JFactory::getUser();

        if($params->get('enable_spam_reg') OR $user->guest)
        {
            $session->set('time', time(), 'easybookreloaded');

            if($params->get('enable_spam', true))
            {
                $spamcheck1 = mt_rand(1, $params->get('max_value', 20));
                $spamcheck2 = mt_rand(1, $params->get('max_value', 20));

                if($params->get('operator') == 0)
                {
                    $operator = 0;
                }
                elseif($params->get('operator') == 1)
                {
                    $operator = 1;
                }
                elseif($params->get('operator') == 2)
                {
                    $operator = mt_rand(0, 1);
                }

                if($operator == 0)
                {
                    $spamcheckresult = $spamcheck1 + $spamcheck2;
                    $operator = '+';
                }
                elseif($operator == 1)
                {
                    $spamcheckresult = $spamcheck1 - $spamcheck2;
                    $operator = '-';
                }

                $spamcheck_field_name = $this->getRandomValue();

                $session->set('spamcheck1', $spamcheck1, 'easybookreloaded');
                $session->set('spamcheck2', $spamcheck2, 'easybookreloaded');
                $session->set('spamcheckresult', $spamcheckresult, 'easybookreloaded');
                $session->set('spamcheck_field_name', $spamcheck_field_name, 'easybookreloaded');
                $session->set('operator', $operator, 'easybookreloaded');
            }

            if($params->get('spamcheck_question') AND ($params->get('spamcheck_question_question') AND $params->get('spamcheck_question_answer')))
            {
                $spamcheck_question_field_name = $this->getRandomValue();
                $session->set('spamcheck_question_field_name', $spamcheck_question_field_name, 'easybookreloaded');
            }
        }
    }

    function validate(&$data)
    {
        $mainframe = JFactory::getApplication();
        $params = JComponentHelper::getParams('com_easybookreloaded');
        $session = JFactory::getSession();
        $user = JFactory::getUser();
        jimport('joomla.mail.helper');
        $errors = array();
        $error = false;

        if($params->get('enable_spam_reg') OR $user->guest)
        {
            $time = $session->get('time', null, 'easybookreloaded');

            if($time == '')
            {
                $error = true;
                $errors['sessionvariable'] = true;
            }
            else
            {
                if((time() - $params->get('type_time_sec')) <= $time)
                {
                    $error = true;
                    $errors['easycalccheck_time'] = true;
                }
            }

            if($params->get('enable_spam', true))
            {
                $spamcheck1 = $session->get('spamcheck1', null, 'easybookreloaded');
                $spamcheck2 = $session->get('spamcheck2', null, 'easybookreloaded');
                $spamcheckresult = $session->get('spamcheckresult', null, 'easybookreloaded');
                $spamcheck_field_name = $session->get('spamcheck_field_name', null, 'easybookreloaded');

                if(($spamcheck1 == '') OR ($spamcheck2 == '') OR ($spamcheckresult == '') OR ($spamcheck_field_name == ''))
                {
                    $error = true;
                    $errors['sessionvariable'] = true;
                }
                else
                {
                    if($data[$spamcheck_field_name] != $spamcheckresult)
                    {
                        $error = true;
                        $errors['easycalccheck'] = true;
                    }
                }
            }

            if($params->get('spamcheck_question') AND ($params->get('spamcheck_question_question') AND $params->get('spamcheck_question_answer')))
            {
                $spamcheck_question_field_name = $session->get('spamcheck_question_field_name', null, 'easybookreloaded');

                if($spamcheck_question_field_name == '')
                {
                    $error = true;
                    $errors['sessionvariable'] = true;
                }
                else
                {
                    if(strtolower($data[$spamcheck_question_field_name]) != strtolower($params->get('spamcheck_question_answer')))
                    {
                        $error = true;
                        $errors['easycalccheck_question'] = true;
                    }
                }
            }
        }

        if($params->get('block_ip'))
        {
            $gbip = getenv('REMOTE_ADDR');
            $ips = array_map('trim', explode(',', $params->get('block_ip')));

            foreach($ips as $ip)
            {
                $ip_regexp = str_replace('x', '..?.?', preg_quote($ip));

                if(preg_match('@'.$ip_regexp.'@', $gbip))
                {
                    $error = true;
                    $errors['easycalccheck'] = true;
                }
            }
        }

        if($params->get('timelock_ip') AND $params->get('enable_log'))
        {
            $gbip = getenv('REMOTE_ADDR');
            $date_last_entry = $this->lastEntryDate($gbip);
            date_default_timezone_set('UTC');
            $date_back = strftime("%Y-%m-%d %H:%M:%S", time() - $params->get('timelock_ip'));

            if($date_last_entry > $date_back)
            {
                $error = true;
                $errors['iptimelock'] = true;
            }
        }

        if(empty($data['gbname']))
        {
            $error = true;
            $errors['name'] = true;
        }

        if(empty($data['gbtext']))
        {
            $error = true;
            $errors['text'] = true;
        }
        else
        {
            if(preg_match_all('@\[img\].+\[/img\]@isU', $data['gbtext'], $matches))
            {
                $text = $data['gbtext'];

                foreach($matches[0] as $value)
                {
                    $img = str_replace(array('\'', "\""), '', $value);

                    if(strpos($img, ' ') == true)
                    {
                        $img_neu = substr($img, 0, strpos($img, ' ')).'[/img]';
                        $text = str_replace($value, $img_neu, $text);
                    }
                }

                $data['gbtext'] = $text;
            }

            if(preg_match_all('@https?://(www\.)?([a-zA-Z0-9-]+\.)?([a-zA-Z0-9-]{3,65})(\.(com|edu|gov|int|mil|net|org|biz|arpa|info|name|pro|aero|coop|museum|[a-zA-Z]{2}))@is', $data['gbtext'], $matches))
            {
                if(count($matches[0]) > $params->get('maxnumberlinks'))
                {
                    $error = true;
                    $errors['toomanylinks'] = true;
                }
            }

            if(preg_match('@\[link.*\].*\[/link\]@isU', $data['gbtext']))
            {
                $error = true;
                $errors['easycalccheck'] = true;
            }
        }

        if(!empty($data['gbaim']))
        {
            $allowed = '@^[A-Za-z0-9_\.]+$@';

            if(!preg_match($allowed, $data['gbaim']))
            {
                $error = true;
                $errors['aim'] = true;
            }
        }

        if(!empty($data['gbicq']))
        {
            $allowed = '@^[0-9]+$@';

            if(!preg_match($allowed, $data['gbicq']))
            {
                $error = true;
                $errors['icq'] = true;
            }
        }

        if(!empty($data['gbyah']))
        {
            $allowed = '@^[A-Za-z0-9_\.]+$@';

            if(!preg_match($allowed, $data['gbyah']))
            {
                $error = true;
                $errors['yah'] = true;
            }
        }

        if(!empty($data['gbskype']))
        {
            $allowed = '@^[A-Za-z0-9_\.-]+$@';

            if(!preg_match($allowed, $data['gbskype']))
            {
                $error = true;
                $errors['skype'] = true;
            }
        }

        if(!empty($data['gbpage']))
        {
            $data['gbpage'] = str_replace(array('\'', "\""), '', $data['gbpage']);

            if(strpos($data['gbpage'], ' ') == true)
            {
                $data['gbpage'] = substr($data['gbpage'], 0, strpos($data['gbpage'], ' '));
            }

            $data['gbpage'] = htmlspecialchars($data['gbpage'], ENT_QUOTES);
        }

        if((!empty($data['gbmail']) OR $params->get('require_mail', true)) AND !JMailHelper::isEmailAddress($data['gbmail']))
        {
            $error = true;
            $errors['mail'] = true;
        }

        if(($params->get('show_title', true)) AND (empty($data['gbtitle']) AND $params->get('require_title', true)))
        {
            $error = true;
            $errors['title'] = true;
        }
        elseif(!empty($data['gbtitle']))
        {
            $data['gbtitle'] = htmlspecialchars($data['gbtitle'], ENT_QUOTES);
        }

        if(!empty($data['gbmsn']) AND !JMailHelper::isEmailAddress($data['gbmsn']))
        {
            $error = true;
            $errors['msn'] = true;
        }

        if($params->get('badwordfilter', true))
        {
            $badwords = $this->_getBadwordList();
            $badwordfilter_regexp = $params->get('badwordfilter_regexp', false);

            if(!empty($badwordfilter_regexp))
            {
                foreach($badwords as $badword)
                {
                    $data['gbtext'] = preg_replace('@'.$badword.'@iU', '***', $data['gbtext']);

                    if(!empty($data['gbtitle']))
                    {
                        $data['gbtitle'] = preg_replace('@'.$badword.'@iU', '***', $data['gbtitle']);
                    }
                }
            }
            else
            {
                $data['gbtext'] = str_replace($badwords, '***', $data['gbtext']);

                if(!empty($data['gbtitle']))
                {
                    $data['gbtitle'] = str_replace($badwords, '***', $data['gbtitle']);
                }
            }
        }

        if($error)
        {
            $session->set('errors', $errors, 'easybookreloaded');
            $mainframe->setUserState('eb_validation_errors', $errors);
            $mainframe->setUserState('eb_validation_data', $data);

            return false;
        }
        else
        {
            return true;
        }
    }

    function savecomment()
    {
        $row = $this->getTable('entry', 'EasybookReloadedTable');

        // Load all request variables - becaus JInput doesn't allow to load the whole data at once, a workaround
        //is used. This was easily possible with the deprecated JRequest (e.g. JRequest::get('post');)
        $data = $_REQUEST;
        array_walk($data, create_function('&$data', '$data = htmlspecialchars(strip_tags(trim($data)));'));

        // Get unfiltered request variable is only with a trick with JInput possible, so direct access is used instead
        // Possible solution: list($gbtext) = ($this->_input->get('gbtext', array(0), 'array') - use the filter array
        // With JRequest one could use - JRequest::getVar('gbtext', NULL, 'post', 'none', JREQUEST_ALLOWRAW)
        $data['gbcomment'] = htmlspecialchars($_REQUEST['gbcomment'], ENT_QUOTES);

        if(!$row->bind($data))
        {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        if(!$row->store())
        {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        return $data;
    }

    function _getBadwordList()
    {
        if(empty($this->_badwords))
        {
            $query = "SELECT ".$this->_db->quoteName('word')." FROM ".$this->_db->quoteName('#__easybook_badwords')." ORDER BY length(word) DESC";
            $this->_db->setQuery($query);
            $this->_badwords = $this->_db->loadResultArray();
        }

        return $this->_badwords;
    }

    function lastEntryDate($ip)
    {
        $query = "SELECT ".$this->_db->quoteName('gbdate')." FROM ".$this->_db->quoteName('#__easybook')." WHERE ".$this->_db->quoteName('gbip')." = ".$this->_db->quote($ip)." ORDER BY gbdate DESC";
        $this->_db->setQuery($query);
        $date_last_entry = $this->_db->loadResult();

        return $date_last_entry;
    }

    function getRow($id)
    {
        $id = (int)$id;
        $table = $this->getTable('entry', 'EasybookReloadedTable');
        $table->load($id);

        return $table;
    }

    // Create random string
    private function getRandomValue()
    {
        $pw = '';

        // first character has to be a letter
        $characters = range('a', 'z');
        $pw .= $characters[mt_rand(0, 25)];

        // other characters arbitrarily
        $numbers = range(0, 9);
        $characters = array_merge($characters, $numbers);

        $pw_length = mt_rand(4, 12);

        for($i = 0; $i < $pw_length; $i++)
        {
            $pw .= $characters[mt_rand(0, 35)];
        }

        return $pw;
    }
}
