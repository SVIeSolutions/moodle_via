<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This file defines the version of Via - Virtual teaching
 * 
 * updated 19/09/2012
 * In this version the cron function : via_update_enrolment() was uncommented to synchronize users
 * but instructions were provided to run in only once before commenting it out again
 * also a last minute change was made in mod/via/lib.php in function via_update_participants_list
 * as an error was occurring in manual inscription when users were removed.
 *
 * updated 27/11/2012
 * Corrections were made to the reminder email.
 * Corrections were made in the automatic enrollment for the animators and presenters, these are now modifiable.
 * 
 * last update 01/05/2013
 * In the settings page we added a via_adminID, this id will be used to create and modify activities. 
 * In the settings we also added a check box to chose if the users' via information should be synchronized with the moodle's values.
 * If checked users' information will be updated when they connect to an activity, but the user name, password and user type will not be affected.
 * When synchronizing the information we validate if the user's email exists as email or as login, if it does we assume it is the same person. 
 * 
 * last update 06/02/2013
 * Corrections were brought to the send invite function.
 * 
 *  last update 27/03/2013
 *  Addition of a visible version to the settings page for quick reference.
 * 	Correction to sql search for user type, mssql vs. mysql. 
 * 
 *  last update 01/05/2013
 *  Modification to UserGetSSOtoken to create link accessible from mobiles.
 * 	We validate the user and the plugin version, needs via 5.2 or above to work.
 *  Validations were also added to the emails and reminders, reminders can now only be sent for activities with a fixed time and date. 
 *  The emails are different for the permanent activities.
 *  
 * last update 02/07/2013
 * In this version we have removed the added code to the moodle core. Users will be synchronised with the help of the cron.
 * So changes to the courses' participants will not be instananious, rather it can take up to 10 minutes before the changes are made to the via participants table.
 * For this to work a new column was added to the via participants table and new functions were added to via_cron. 
 * The categories in Via are reproduced in moodle, the admin can choose which categories will be available and can even add one as default. 
 * Then when an activity is created the teacher can chose from the available categories.
 * These are only helpful for invoicing; the category can only be seen when editing an activity.
 * The connection test was modified, we test the API connection, then we test the new moodle key, independently.
 * 
 * Last update 20/09/2013 Version 20130920
 * In this version we have made modifications to the cron synchronisation of users for all types of enrollments!
 * We have also added validations at many levels to add users that were added in moodle but not in via.
 * We have added a log to keep track of these errors and later additions.
 * We have also given it a new more modern look!!!
 * For connexion on mobiles using moodle with a mobile theme we have made modifications to the connexion
 *  * As well as improvements following feedback from moodle.org : 
 * - GPL licence
 * - changing the page encode from Western European (Windows) to UTF-8 with signature
 * 
 * Last update 06/11/2013 Version 2013092001
 * Correction to the playback list - recordings
 * 
 * Last update 21/11/2013
 * Mofications made for Moodle 2.6
 * Added proxy information
 * Made corrections after errors were reported
 * 
 * Last update 01/04/2014
 * Validations added in case a user is deleted or deactivated in Via after being associated in moodle
 * Validations added in order not to give more than one role to a user in one activity
 * Validations added in order to always have one presenter and only one presenter. 
 * * If a presenter is replaced and the enrollment is set to automatic then we add the user in the standard role. 
 * A modification was made to the automatic enrolment method, animators remain modifiable.
 * * If an animator is removed and the enrollment is set to automatic then we add the user as participant. 
 * A new feature was added in order to add all students as animators
 * Extra validations were added in case an activity is created with manual enrollment then changed to automatic, etc.
 * A bug was fixed when students with the animator role were set back to participant at access of an activite.
 * Modifications were made so that recordings are always visible for the users were editing rights, they can then display or hide any recording
 * OR set them all to public.
 * Hides recordings on tablettes and mobiles, as they can not be opened.
 * Modifications were made to the way config information is stored, it is now in the 'config_plugin' table, instead of in 'config'
 * 
 * 
 * Last update 01/07/2014
 * Corrections we made for Moodle 2.7
 * 
 * 
 * Last update 01/08/2014
 * NEEDS VIA 6.2!
 * Recordings can be deleted individually
 * Activities can be duplicated, documents and associated users follow - with or without whiteboards and surveys.
 * Activities can be backupes and restored, documents follow and associated users if desired - with or without whiteboards and surveys.
 * New settings were added to select if the whitebords and surveys will be added during the duplication or backup & restore process. The parameter applies to all activities.
 * Duplicated activities will be created one month in the future to the present, leaving enough time to go modify the actual date and time. Unless it is a permanent activity, in which case there is no start time or duration.
 * If a grouping is selected for an activity, only the users associated with the grouping will be synchronised. If manual enrollemnt is chosen, other users may be added to the activity but will not be synchronised.
 * A new list of users was added to the details page for a quick over view. We also added their 'set up wizard' state. And added the confirmation status to this table, we removed the confirmation table from underneath the participants table.
 * A new parmeter was added to the settings page, to limit deletion to moodle. If checked activities will be deleted in Moodle but not in Via.
 * The log tables were droped!
 * The file UApi was renamed to uapi and moved inside mod/via to satisfy the moodle community.
 * More information will be synchronised with Via if the option is checked, example the phone number and the moodle name will be added as the organisation name.
 * Users deleted in Moodle will be deleted in the via_users table and removed from the activities in which they were meant to take part.
 */  



/**
 *
 * @package    mod-via
 * @copyright  2011 - 2014 SVIeSolutions http://sviesolutions.com
 * @author     Alexandra Dinan-Mitchell
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * 
 */
defined('MOODLE_INTERNAL') || die();

$module = new stdClass();
$plugin = new stdClass();

$plugin->version = 2014080162;	
$plugin->component = 'mod_via'; 

$module->version  = 2014080162;	 // YYYYMMDDHH (year, month, day, 24-hr time) (needs API version 6.2 or greater)
$module->release  = 2.20140801;
$module->requires = 2011033003;  // Moodle version required to run it (2.0.3 )
$module->cron     = 300;         // Number of seconds between cron calls.
$module->component = 'mod_via'; 
$module->maturity  = MATURITY_STABLE;