/**
 * Plugin Admin JS
 * License: Licensed under the AGPL license.
 */

// Dynamic variables
if(typeof ExpandableFAQ_Vars === "undefined")
{
    // The values here will come from WordPress script localizations,
    // but in case if they wouldn't, we have a backup initializer below
    var ExpandableFAQ_Vars = {};
}

// Dynamic language
if(typeof ExpandableFAQ_Lang === "undefined")
{
    // The values here will come from WordPress script localizations,
    // but in case if they wouldn't, we have a backup initializer below
    var ExpandableFAQ_Lang = {};
}

// NOTE: For object-oriented language experience, this variable name should always match current file name
var ExpandableFAQ_Admin = {
    vars: ExpandableFAQ_Vars,
    lang: ExpandableFAQ_Lang,

    getValidCode: function(paramCode, paramDefaultValue, paramToUppercase, paramSpacesAllowed, paramDotsAllowed)
    {
        var regexp = '';
        if(paramDotsAllowed)
        {
            regexp = paramSpacesAllowed ? /[^-_0-9a-zA-Z. ]/g : /[^-_0-9a-zA-Z.]/g; // There is no need to escape dot char
        } else
        {
            regexp = paramSpacesAllowed ?  /[^-_0-9a-zA-Z ]/g : /[^-_0-9a-zA-Z]/g;
        }
        var rawData = Array.isArray(paramCode) === false ? paramCode : paramDefaultValue;
        var validCode = rawData.replace(regexp, '');

        if(paramToUppercase)
        {
            validCode = validCode.toUpperCase();
        }

        return validCode;
    },

    getValidPrefix: function(paramPrefix, paramDefaultValue)
    {
        var rawData = Array.isArray(paramPrefix) === false ? paramPrefix : paramDefaultValue;
        return rawData.replace(/[^-_0-9a-z]/g, '');
    },

    deleteFAQ: function(paramFAQId)
    {
        var approved = confirm(this.lang['LANG_FAQ_DELETING_DIALOG_TEXT']);
        if (approved)
        {
            window.location = 'admin.php?page=expandable-faq-add-edit-faq&noheader=true&delete_faq=' + paramFAQId;
        }
    }
};