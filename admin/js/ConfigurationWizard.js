/**
 * Pronamic Configuration Wizard Object
 * 
 * @type object literal
 * @author Leon Rowland <leon@rowland.nl>
 */
var Pronamic_ConfigurationWizard = {
    /**
     * Holds all current configuration options for this module.
     * 
     * @type object
     */
    config: {
        /**
         * Holds all current dom elements used in this module.
         * 
         * @type object
         */
        dom: {}
    }

    /**
     * Fills the configuration object with all the dom elements used.  Then calls
     * the binds() method to start the event listeners.
     * 
     * @returns void
     */
    , ready: function() {
        Pronamic_ConfigurationWizard.config.dom.form = jQuery('.pronamic_configuration_wizard_form');
        Pronamic_ConfigurationWizard.config.dom.nextStepButton = jQuery('.pronamic_configuration_wizard_next_step_button');
        Pronamic_ConfigurationWizard.config.dom.previousStepButton = jQuery('.pronamic_configuration_wizard_previous_step_button');
        Pronamic_ConfigurationWizard.config.dom.currentStepsHolder = jQuery('.pronamic_configuration_wizard_steps_holder');
        Pronamic_ConfigurationWizard.config.dom.currentSteps = jQuery('.pronamic_configuration_wizard_step');

        Pronamic_ConfigurationWizard.binds();
    }

    /**
     * Starts the event listeners.
     * 
     * @returns void
     */
    , binds: function() {

        // Listens on the nextStepButton
        Pronamic_ConfigurationWizard.config.dom.nextStepButton.click(
                Pronamic_ConfigurationWizard.loadNextStep
        );

        // Listens on the previousStepButton
        Pronamic_ConfigurationWizard.config.dom.previousStepButton.click(
                Pronamic_ConfigurationWizard.loadPreviousStep
        );
    }

    /**
     * Looks for the current step number, gets the next step number.  Hides the current step
     * and shows the next step.
     * 
     * If the last step, will show the submission button.
     * 
     * @param event e
     * @returns void
     */
    , loadNextStep: function(e) {
        e.preventDefault();
        
        // Get the next step number
        var nextStepNumber = Pronamic_ConfigurationWizard.getNextStepNumber();
        
        // Hide the current step
        Pronamic_ConfigurationWizard.getCurrentStepDOM()
            .removeClass('pronamic_configuration_wizard_current_step');

        // Bug with jquery 1.8 I assume. Extra space in selector tag
        // is causing the saved config in the dom object to not correctly
        // work when searching for a data attribute.
        jQuery('[data-step="' + nextStepNumber + '"]')
            .addClass('pronamic_configuration_wizard_current_step');
    }

    /**
     * Looks for the current step number, and gets the previous step number. Hides the current step
     * and shows the previous step.
     * 
     * @param event e
     * @returns void
     */
    , loadPreviousStep: function(e) {
        e.preventDefault();
        
        // Get the previous step number
        var previousStepNumber = Pronamic_ConfigurationWizard.getPreviousStepNumber();
        
        // Hides the current step
        Pronamic_ConfigurationWizard.getCurrentStepDOM()
            .removeClass('pronamic_configuration_wizard_current_step');

        jQuery('[data-step="' + previousStepNumber + '"]')
            .addClass('pronamic_configuration_wizard_current_step');
    }

    /**
     * Returns a jquery object of the current step. It looks in the dom
     * fresh each time, for the class pronamic_configuration_wizard_current_step.
     * 
     * @returns jQuery Object
     */
    , getCurrentStepDOM: function() {
        return jQuery('.pronamic_configuration_wizard_current_step');
    }

    /**
     * Returns the current step number. It gets the current step dom, from the
     * getCurrentStepDOM method and looks for the data 'step'. It returns the 
     * value of this data
     * 
     * @returns {@exp;@exp;Pronamic_ConfigurationWizard@pro;getCurrentStepDOM@call;@call;data|@exp;Pronamic_ConfigurationWizard@pro;getCurrentStepDOM@call;@call;data}
     */
    , getCurrentStepNumber: function() {
        return Pronamic_ConfigurationWizard.getCurrentStepDOM().data('step');
    }

    /**
     * Returns the next step number. It gets the current step number from the 
     * method getCurrentStepNumber and increments it by one.
     * 
     * @returns {String|@exp;Pronamic_ConfigurationWizard@call;currentStepNumber}
     */
    , getNextStepNumber: function() {
        return Pronamic_ConfigurationWizard.getCurrentStepNumber() + 1;
    }

    /**
     * Returns the previous step number. It gets the current step number from the
     * method getCurrentStepNumber and decreases it by one.
     * 
     * @returns {String|@exp;Pronamic_ConfigurationWizard@call;getCurrentStepNumber}
     */
    , getPreviousStepNumber: function() {
        return Pronamic_ConfigurationWizard.getCurrentStepNumber() - 1;
    }
};

// Load the Configuration Wizard
jQuery(Pronamic_ConfigurationWizard.ready);