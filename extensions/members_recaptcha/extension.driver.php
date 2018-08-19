<?php
/**
 * Copyright: Deux Huit Huit 2017
 * License: MIT, see the LICENSE file
 */

if (!defined("__IN_SYMPHONY__")) die("<h2>Error</h2><p>You cannot directly access this file</p>");

class extension_members_recaptcha extends Extension
{
    /**
     * Name of the extension
     * @var string
     */
    const EXT_NAME = 'Members reCaptcha';

    /* ********* INSTALL/UPDATE/UNISTALL ******* */

    protected function checkDependency($depname)
    {
        $status = ExtensionManager::fetchStatus(array('handle' => $depname));
        $status = current($status);
        if ($status != EXTENSION_ENABLED) {
            Administration::instance()->Page->pageAlert("Could not load `$depname` extension.", Alert::ERROR);
            return false;
        }
        return true;
    }

    protected function checkDependencyVersion($depname, $version)
    {
        $installedVersion = ExtensionManager::fetchInstalledVersion($depname);
        if (version_compare($installedVersion, $version) == -1) {
            Administration::instance()->Page->pageAlert("Extension `$depname` must have version $version or newer.", Alert::ERROR);
            return false;
        }
        return true;
    }

    /**
     * Creates the table needed for the settings of the field
     */
    public function install()
    {
        // depends on "members"
        if (!$this->checkDependencyVersion('members', '1.8.0')) {
            return false;
        }
        // depends on "google_recaptcha"
        if (!$this->checkDependencyVersion('google_recaptcha', '1.1.0')) {
            return false;
        }
        return true;
    }
    
    /**
     * Creates the table needed for the settings of the field
     */
    public function update($previousVersion = false)
    {
        $ret = true;
        return $ret;
    }

    /**
     *
     * Drops the table needed for the settings of the field
     */
    public function uninstall()
    {
        return true;
    }

    /*------------------------------------------------------------------------------------------------*/
    /*  Delegates  */
    /*------------------------------------------------------------------------------------------------*/

    public function getSubscribedDelegates()
    {
        return array(
            array(
                'page'     => '/frontend/',
                'delegate' => 'MembersPreLogin',
                'callback' => 'membersPreLogin'
            ),
        );
    }

    public function membersPreLogin(array $context)
    {
        if (!$context['can-logged-in']) {
            return;
        }
        $reCaptcha = false;
        if (!empty($_POST['fields']) && is_array($_POST['fields']) && !empty($_POST['fields']['google_recaptcha'])) {
            $ext = ExtensionManager::getInstance('google_recaptcha');
            $reCaptcha = $ext->validateChallenge($_POST['fields']['google_recaptcha']);
            if (!$reCaptcha) {
                $context['errors']['google_recaptcha'] = 'Invalid reCaptcha';
                Symphony::Log()->pushToLog("Prevent member login: Invalid reCaptcha", E_NOTICE, true);
            }
        }
        $context['can-logged-in'] = $reCaptcha;
    }
}
