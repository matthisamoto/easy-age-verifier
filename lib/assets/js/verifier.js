//For the max value of the input in the age form
taseavCurrDate = new Date();
var taseavWrapper = jQuery('#taseav-age-verify');
var verifier = new EavVerifier();

/**
 * Gets the verifier form when the site loads
 */
jQuery(document).ready(function(){
  verifier.getForm();
});

/**
 * Parses and grabs the submitted age from the visitor
 *
 * @constructor
 */
function EavAge(){

  /**
   * Gets the the age of a value from a specified date of birth
   *
   * @returns Age|False
   */
  this.getAge = function(dob){
    var result;
    var submittedDob = new Date(dob).getTime();
    if(submittedDob){
      var ageDifMs = Date.now() - submittedDob;
      var ageDate = new Date(ageDifMs);
      result = Math.abs(ageDate.getUTCFullYear() - 1970);
    }
    else{
      result = false;
    }
    return result;
  },

    /**
     * Stores the submitted age as a cookie
     *
     * @returns the cookie value
     */
    this.storeAsCookie = function(){
      var result = this.getSubmittedValue();
      if(!eav.isCustomizer){
        document.cookie = "eav_age=" + result + ";" + eav.cookieParameters;
      }
      else{
        console.log("Since the age confirmation was executed in the WordPress customizer, the cookie was not saved");
      }
      return result;
    },

    /**
     * gets the date of birth, or age confirmation from the submitted form
     *
     * @returns the submitted form value
     */
    this.getSubmittedValue = function(){
      var wrapper = jQuery('#taseav-age-verify');
      var result;
      if(eav.formType == 'eav_enter_age'){
        dob = this.parseDob();
        result = this.getAge(dob);
      }
      if(eav.formType == 'eav_confirm_age'){
        result = wrapper.find('input[selected="selected"]').attr('name');
      }
      return result;
    },

    /**
     * Parses the date of birth value from the enter age verifier form
     *
     * @returns {string}
     */
    this.parseDob = function(){
      var wrapper = jQuery('#taseav-age-verify');
      var month = wrapper.find('input[name="month"]').val();
      var day = wrapper.find('input[name="day"]').val();
      var year = wrapper.find('input[name="year"]').val();
      if(month < 10){
        month = "0" + month;
      }
      if(day < 10){
        day = "0" + day;
      }
      return year + "-" + month + "-" + day;
    }
}

/**
 * Checks the submitted age against the specified verifications
 *
 * @param age
 * @constructor
 */
function EavVerification(age){
  this.age = age;

  /**
   * Checks if the verification failed. Returns true if so
   *
   * @returns boolean
   */
  this.failed = function(){
    var visitorAge = this.age.getSubmittedValue();
    if(eav.formType == 'eav_enter_age'){
      ageToCheck = eav.verification.minAge;
      return (visitorAge < ageToCheck);
    }
    else{
      return (visitorAge == 'underAge');
    }
  }
}

/**
 * Builds the form, and executes actions
 *
 * @constructor
 */
function EavVerifier(){
  this.age = new EavAge();
  this.verification = new EavVerification(this.age);

  this.doActions = function(){
    if(this.verification.failed() == true){
      alert(eav.underageMessage);
      if(!eav.isCustomizer){
        history.back();
      }
    }
    else{
      verifier.age.storeAsCookie();
      jQuery('#taseav-age-verify').remove();
    }
  },

    this.buildForm = function(wrapper){
      if(!wrapper){
        wrapper = taseavWrapper;
      }
      wrapper.find('form').submit(function(e){
        e.preventDefault();
      });
      //Disables mouse-wheel when gallery is open
      wrapper.bind("mousewheel",function(){
        return false;
      });

      if(eav.formType == 'eav_confirm_age'){
        wrapper.find('input[type=submit]').click(function(){
          jQuery("input[type=submit]",jQuery(this).parents("form")).removeAttr("selected");
          jQuery(this).attr("selected","true");
        });
      }

      wrapper.submit(function(){
        verifier.doActions();
      });
    };

  /**
   * Gets the form
   * @returns {boolean}
   */
  this.getForm = function(){
    if(eav.verificationFailed){
      jQuery("body").prepend(eav.template);
      var taseavWrapper = jQuery('#taseav-age-verify');
      verifier.buildForm(taseavWrapper);
      return true;
    }
    return false;
  }
}