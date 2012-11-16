<?php

/**
 * NoCaptcha class to protect web forms from spam.
 * @author Alexander Selle <kontakt@msxstudios.de>
 * @version 1.0
 * @license http://URL MIT
 */

abstract class Proteus {
    // --------------------------------------------------
    // ---- NoCaptcha Begin Configuration
    // --------------------------------------------------
    
    /**
     * Adress to the webservice that is offering the anti spam protection 
     */
    const provider_url = 'http://localhost/proteus/server/';
    
    /**
     * Username to log in to your Antispam Webservice.
     */
    const authUsername = '';
    
    /**
     * Password to login in to your Antispam Webservice.
     */
    const authPassword = '';    // Enter the password you got after your registration here. 
    
    
    /**
     * This Feature adds hidden fields to your form, that must not be filled. As soon as one of these fields
     * get filled, form submission will not be successful. Hidden fields will not be visible for normal users so they
     * wont fill these fields.
     */
    const feature_hiddenfields = true;
    
    /**
     * This feature will change your original names of form fields to completely random ones. If feature_hiddenfields is enabled
     * these fields will be named to something spammers usually fill, e.g. "email" and "password".
     */
    const feature_randomnames = true;

    // --------------------------------------------------
    // ---- NoCaptcha End Configuration
    // --------------------------------------------------
    
    
    private static $forms;
    
    public static function BeginForm() {
        ob_start();
    }
    
    
    public static function EndForm() {
        $form = ob_get_clean();
        $new_form = self::EditForm($form);
        
        echo $new_form;
    }
    
    private static function EditForm($form) {
        
    }
}