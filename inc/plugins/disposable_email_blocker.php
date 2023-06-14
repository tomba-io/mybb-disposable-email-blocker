<?php
/**
 * Disposable Email Blocker will help protecting your form by accepting only valid email addresses with only javascript.
 *
 * @author: tomba.io
 * @date: 2023-06-14
 * @version: 1.0
 * @contact: support@tomba.io
 */

// Disallow direct access to this file for security reasons
if (!defined("IN_MYBB")) {
    die("Direct initialization of this file is not allowed.");
}

// Plugin information
function disposable_email_blocker_info()
{
    return array(
        "name" => "Disposable Email Blocker",
        "description" => "Blocks users from registering with disposable email addresses.",
        'website'       => 'https://tomba.io/',
        "author" => "Tomba Email Finder",
        'authorsite'    => 'https://github.com/tomba-io/mybb-disposable-email-blocker',
        'codename'      => 'defaultdisposable',
        "version" => "1.0",
        "compatibility" => "18*"
    );
}

// Activate the plugin
function disposable_email_blocker_activate()
{
    global $db;

    // Add necessary settings to the settings table
    $settings_group = array(
        "name" => "disposable_email_blocker",
        "title" => "Disposable Email Blocker Settings",
        "description" => "Settings for the Disposable Email Blocker plugin.",
        "disporder" => 1,
        "isdefault" => 0
    );

    $db->insert_query("settinggroups", $settings_group);

    $setting = [];
    $setting[] = array(
        "name" => "de_blocker_enabled",
        "title" => "Enable Disposable Email Blocker",
        "description" => "Enable or disable the disposable email blocker.",
        "optionscode" => "onoff",
        "value" => 1,
        "disporder" => 1,
        "gid" => intval($db->insert_id())
    );

    $setting[] = array(
        "name" => "de_blocker_disposable_input",
        "title" => "Disposable Error Message",
        "description" => "This message displays on the input if the email is Disposable.",
        "optionscode" => "text",
        "value" => "Abuses, strongly encourage you to stop using disposable email.",
        "disporder" => 2,
        "gid" => intval($db->insert_id())
    );

    $setting[] = array(
        "name" => "de_blocker_webmail_input",
        "title" => "Webmail Error Message	",
        "description" => "This message displays on the input if the email is Webmail.",
        "optionscode" => "text",
        "value" => "Warning, You can create an account with this email address, but we strongly encourage you to use a professional email address.",
        "disporder" => 2,
        "gid" => intval($db->insert_id())
    );

    $setting[] = array(
        "name" => "de_blocker_webmail_enabled",
        "title" => "Webmail Block	",
        "description" => " This Detect and Block webmail emails.",
        "optionscode" => "onoff",
        "value" => 0,
        "disporder" => 1,
        "gid" => intval($db->insert_id())
    );

    foreach ($setting as $array => $content) {
        $db->insert_query("settings", $content);
    }

    rebuild_settings();
}

// Deactivate the plugin
function disposable_email_blocker_deactivate()
{
    global $db;

    // Remove settings from the settings table
    $db->delete_query("settinggroups", "name = 'disposable_email_blocker'");

    rebuild_settings();
}

// Hook to add JavaScript alert on every page
function disposable_email_blocker_pre_output_page(&$contents)
{
    global $mybb;

    if ($mybb->settings['de_blocker_enabled']) {
        $disposable_message = $mybb->settings['de_blocker_disposable_input'];
        $webmail_message = $mybb->settings['de_blocker_webmail_input'];
        $webmail_block = $mybb->settings['de_blocker_webmail_enabled'];
        $npm = '<script src="https://cdn.jsdelivr.net/npm/disposable-email-blocker/disposable-email-blocker.min.js"></script>';
        $defaults = "<script>const defaults = { disposable: { message: '$disposable_message', }, webmail: { message: '$webmail_message', block: $webmail_block, }}; \n new Disposable.Blocker(defaults);</script>";
        $contents = str_replace("</head>", $npm . "</head>", $contents);
        $contents = str_replace("</head>", $defaults . "</head>", $contents);
    }
}

// Register hooks
$plugins->add_hook("pre_output_page", "disposable_email_blocker_pre_output_page");
