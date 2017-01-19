<?php
/**
 * Verification class for Easy Age Verifier
 * Everything related to the verification process of the visitor
 * @author: Alex Standiford
 * @date  : 1/15/2017
 */

namespace eav\app;

if(!defined('ABSPATH')) exit;

use eav\config\option;

/**
 * Class verification
 * Handles verifications that determine if the verifier will display on this page
 * @package eav\app
 */
class verification{

  public $isOfAge = null;
  public $checks = null;

  public function __construct($dob = null){
    $this->minAge = option::get('minimum_age');
    $this->visitorAge = age::get();
  }

  /**
   * Checks if the visitor is of-age.
   * Stores the result into a value, so it doesn't need to be re-ran.
   * @return bool
   */
  public function isOfAge(){
    if(isset($this->isOfAge)){
      return $this->isOfAge;
    }

    $checks = array(
      $this->visitorAge >= $this->minAge,
      $this->visitorAge != false,
      $this->visitorAge == 'overAge',
    );

    if(in_array(true, $checks)){
      $this->isOfAge = true;

      return true;
    }
    else{
      $this->isOfAge = false;

      return false;
    }
  }

  /**
   * Checks if the WordPress customizer is active
   * @return bool
   */
  public function customizerIsActive(){
    $result = false;
    $active_in_customizer = option::get('active_in_customizer');
    if(is_customize_preview() && $active_in_customizer){
      $result = true;
    }
    return $result;
  }

  /**
   * Determines if the logged-in user should see the verifier
   * @return bool
   */
  public function userChecksPassed(){
    $checks = array(
      is_user_logged_in(),
      option::getCheckbox('show_verifier_to_logged_in_users'),
    );
    $passed = $this->verifyChecks($checks);

    return $passed;
  }

  /**
   * Checks if all custom logic tests passed
   * @return bool
   */
  public function verify(){
    $custom_checks = array();
    $custom_checks = apply_filters('eav_custom_modal_logic', $custom_checks);
    $passed = $this->verifyChecks($custom_checks);
    return $passed || $this->isOfAge();
  }

  /**
   * Runs an if statement loop on an array of checks
   * Think of this as an if statement where everything is checked with an OR (||)
   *
   * @param array $checks A collection of boolean operations, in the order they need to be checked
   *
   * @return bool
   */
  public static function verifyChecks($checks){
    //Guilty until proven innocent
    $passed = true;
    if(!empty($checks)){
      if(is_array($checks)){
        foreach($checks as $check){
          if(!$check){
            $passed = false;
            break;
          }
        }
      }
    }
    else{
      $passed = false;
    }

    return $passed;
  }

  /**
   * Checks if verification has passed. Verifier will not pop up if verification passed
   * @return bool
   */
  public function passed(){
    // Before we get started - is this the customizer?
    if(!is_customize_preview()){
      // Alright, so we're not in the customizer. In that case, are you logged in?
      if(is_user_logged_in()){
        // Cool! You're logged in. Is the verifier configured to display to logged in users?
        if(option::getCheckbox('show_verifier_to_logged_in_users')){
          //Then in that case, run the custom checks, and make sure you're also of-age
          $passed = $this->verify();
        }
        else{
          // Oh, well if the verifier shouldn't display to logged in users, then this check passes
          $passed = true;
        }
      }
      // Alright stranger, it seems you're not logged in. Let's run the custom checks, and make sure you're also of-age
      else{
        $passed = $this->verify();
      }
    }
    // Oh, this is the customizer? That's a special case. I'm going to need to know a little more before we go on
    // Tell me, is the customizer active? In other words, are you on the customizer screen right now?
    // If so, is the verifier configured to display in the customizer?
    else{
      // If so, the check passes
      $passed = !$this->customizerIsActive();
    }

    return $passed;
  }

  /**
   * The inverse of the passed function. Simply exists for better code read-ability
   * @return bool
   */
  public function failed(){
    return !$this->passed();
  }
}