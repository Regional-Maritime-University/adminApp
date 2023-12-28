<?php

class SessionSettings
{
    // NOTE: This code is not fully working code, but an example!
    // my_session_start() and my_session_regenerate_id() avoid lost sessions by
    // unstable network. In addition, this code may prevent exploiting stolen
    // session by attackers.

    public function my_session_start()
    {
        if (isset($_SESSION['destroyed'])) {
            if ($_SESSION['destroyed'] < time() - 300) {
                echo "Session expired!";
                // Should not happen usually. This could be attack or due to unstable network.
                // Remove all authentication status of this users session.
                //remove_all_authentication_flag_from_active_sessions($_SESSION['userid']);
                session_unset();
                session_destroy();
                //throw (new DestroyedSessionAccessException);
            }
            if (isset($_SESSION['new_session_id'])) {
                // Not fully expired yet. Could be lost cookie by unstable network.
                // Try again to set proper session ID cookie.
                // NOTE: Do not try to set session ID again if you would like to remove
                // authentication flag.
                session_commit();
                session_id($_SESSION['new_session_id']);
                // New session ID should exist
                //session_start();
                return;
            }
        }
    }

    // My session regenerate id function
    function my_session_regenerate_id()
    {
        // Call session_create_id() while session is active to 
        // make sure collision free.
        if (session_status() != PHP_SESSION_ACTIVE) {
            session_start();
        }
        // WARNING: Never use confidential strings for prefix!
        $newid = session_create_id('myprefix-');
        $_SESSION['new_session_id'] = $newid;
        // Set deleted timestamp. Session data must not be deleted immediately for reasons.
        $_SESSION['deleted_time'] = time();
        // Finish session
        session_commit();
        // Make sure to accept user defined session ID
        // NOTE: You must enable use_strict_mode for normal operations.
        ini_set('session.use_strict_mode', 1);
        // Set new custom session ID
        session_id($newid);
        // Start with custom session ID
        //session_start();

        // New session does not need them
        //unset($_SESSION['destroyed']);
        //unset($_SESSION['new_session_id']);
    }
}
