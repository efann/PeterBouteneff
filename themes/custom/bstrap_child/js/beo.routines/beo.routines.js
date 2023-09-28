// Updated on September 27, 2017
//----------------------------------------------------------------------------------------------------
//----------------------------------------------------------------------------------------------------
//----------------------------------------------------------------------------------------------------
var Beo =
  {
    foDialogImage: null,
    foDialogImageImg: null,

    fnDialogImageTitleBarHeight: 0,
    fcImageDialogFadeEffect: 'explode',

    //----------------------------------------------------------------------------------------------------
    initializeBrowserFixes: function ()
    {
      // Simple, brilliant solution from
      // https://amp.reddit.com/r/drupal/comments/3qq70k/d7_cannot_read_property_msie_of_undefined_error
      // Fix Drupal admin bar JavaScript errors that look for this deprecated feature.
      // And because jQuery and not $ is used, jQuery Migrate can't fix the issue.
      jQuery.browser = {version: 0};
      var laBrowsers = ['webkit', 'safari', 'opera', 'msie', 'mozilla'];
      for (var i = 0; i < laBrowsers.length; i++)
      {
        jQuery.browser[laBrowsers[i]] = false;
      }

      try
      {
        // From https://stackoverflow.com/questions/17367736/jquery-ui-dialog-missing-close-icon
        var loBootstrapButton = jQuery.fn.button.noConflict(); // return $.fn.button to previously assigned value
        jQuery.fn.bootstrapBtn = loBootstrapButton;            // give $().bootstrapBtn the Bootstrap functionality
      }
      catch (loErr)
      {
      }

      // For browser console
      window.onerror = function (message, url, line)
      {
        console.log("window.onerror was invoked with message = " + message + ", url  " + url + ", line = " + line);
      };

    },
    //----------------------------------------------------------------------------------------------------
    getCurrentYear: function ()
    {
      var loNow = new Date();
      var lnYear = loNow.getYear();

      return ((lnYear < 1000) ? lnYear + 1900 : lnYear);
    },
    //----------------------------------------------------------------------------------------------------
    writeCurrentYear: function ()
    {
      document.write(this.getCurrentYear());
    }
    ,
    //----------------------------------------------------------------------------------------------------
    // http://stackoverflow.com/questions/148901/is-there-a-better-way-to-do-optional-function-parameters-in-javascript
    writeCurrentDate: function (tlLongVersion)
    {
      var llLongVersion = (typeof tlLongVersion === "undefined") ? true : tlLongVersion;

      var laMonths = new Array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
      var laDays = new Array("Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday");

      var lnYear = this.getCurrentYear();
      var loNow = new Date();

      var lcDay = laDays[loNow.getDay()];
      var lcMonth = laMonths[loNow.getMonth()];

      if (!llLongVersion)
      {
        lcDay = lcDay.substring(0, 3);
        lcMonth = lcMonth.substring(0, 3);
      }

      document.write(lcDay + ", " + lcMonth + " " + loNow.getDate() + ", " + lnYear);
    },
    //----------------------------------------------------------------------------------------------------
    // Set the cursor for buttons, radio, checkboxes and combo boxes.
    standardizeControls: function ()
    {
      jQuery("input:submit, input:button, button, td.views-field.views-field-edit-node a, td.views-field.views-field-delete-node a, a.make-button-from-link, div.date-prev a, div.date-next a, li.calendar-year a, li.calendar-month a, li.calendar-day a").each(function (tnIndex)
      {
        // If already has been stamped with ui-button, like with the Close Icon for jQuery.dialog.
        if (jQuery(this).hasClass("ui-button"))
        {
          // Equivalent of continue. Return false is the equivalent of break;
          return (true);
        }

        // Used by Bootstrap
        if (jQuery(this).hasClass("btn-primary"))
        {
          // Equivalent of continue. Return false is the equivalent of break;
          return (true);
        }

        // If already has an element with an icon. . . .
        if (jQuery(this).find("[class^='icon']").length > 0)
        {
          // Equivalent of continue. Return false is the equivalent of break;
          return (true);
        }

        jQuery(this).button();
      });

      jQuery("select").each(function (tnIndex)
      {
        jQuery(this).css('cursor', 'pointer');
      });

      jQuery("input:checkbox, input:radio").each(function (tnIndex)
      {
        jQuery(this).css('cursor', 'pointer');
        // Get the label which is in the parent due to the Zen theme.
        jQuery(this).parent().css('cursor', 'pointer');
      });

    },
    // -------------------------------------------------------------------------------------------------------------------
    // From http://www.mkyong.com/jquery/jquery-watermark-effect-on-text-input/
    setupWatermark: function (tcID, tcWatermark)
    {
      var loInput = jQuery(tcID);

      if (loInput.length == 0)
      {
        return;
      }

      // initialization: set watermark text and class if empty.
      if (loInput.val().trim().length == 0)
      {
        loInput.val(tcWatermark).addClass('watermark');
      }

      //if blur and no value inside, set watermark text and class again.
      loInput.blur(function ()
      {
        if (jQuery(this).val().trim().length == 0)
        {
          jQuery(this).val(tcWatermark).addClass('watermark');
        }
      });

      // if focus and text is watermark, set it to empty and remove the watermark class
      loInput.focus(function ()
      {
        if (jQuery(this).val() == tcWatermark)
        {
          jQuery(this).val('').removeClass('watermark');
        }
      });


      var loForm = loInput.closest("form");
      loForm.submit(function ()
      {
        if (loInput.val() == tcWatermark)
        {
          loInput.val('').removeClass('watermark');
        }
      });
    },
    //----------------------------------------------------------------------------------------------------
    // When an image is clicked, using jQuery dialog, the picture is displayed like
    // that of FancyBox.
    //
    // Unfortunately, at the moment, I don't have a way to determine the title bar height
    // before it displays. So I use Chrome Inspect when viewing a dialog box to determine
    // the height.
    setupImageDialogBox: function (tnTitleBarHeight, tcFadeEffect, tlCheckClass)
    {
      tlCheckClass = (typeof tlCheckClass !== 'undefined') ? tlCheckClass : true;

      Beo.fnDialogImageTitleBarHeight = tnTitleBarHeight;

      Beo.fcImageDialogFadeEffect = tcFadeEffect;
      Beo.createImageDialog();

      // Unfortunately, I can't get the title in the template of field.html.twig.
      // to override the image output.
      var lcPageTitle = jQuery(document).attr('title').split('|')[0].trim();

      jQuery("div.main-container img").each(function ()
      {
        var loImage = jQuery(this);
        if (!loImage.attr('alt'))
        {
          loImage.attr('alt', lcPageTitle);
        }

        if (!loImage.attr('title'))
        {
          loImage.attr('title', lcPageTitle);
        }

        loImage.removeAttr('width');
        loImage.removeAttr('height');

        if (tlCheckClass)
        {
          var lcClasses = loImage.attr('class');

          if ((typeof lcClasses === 'undefined') || (lcClasses.indexOf('responsive-image') < 0))
          {
            loImage.addClass("responsive-image-regular");
          }
        }

        if (!loImage.parent().is('a'))
        {
          var lcSource = loImage.attr('src');
          loImage.wrap('<a class="dialogbox-image" href="' + lcSource + '"></a>');
        }
      });

      jQuery("a.dialogbox-image").click(function (toEvent)
      {
        toEvent.preventDefault();
        Beo.onDialogImageClick(jQuery(this));
      });

    },

    // -------------------------------------------------------------------------------------------------------------------
    onDialogImageClick: function (toImageLink)
    {
      var loDialog = Beo.foDialogImage;
      var loDialogImg = Beo.foDialogImageImg;

      var loImage = toImageLink.find('img');
      var lcSource = loImage.attr('src');

      var lcAlt = loImage.attr('alt');
      if ((typeof lcAlt === "undefined") || (lcAlt.trim().length == 0))
      {
        lcAlt = "Image";
      }
      var lcTitle = loImage.attr('title');
      if ((typeof lcTitle === "undefined") || (lcTitle.trim().length == 0))
      {
        lcTitle = lcAlt;
      }

      loDialogImg.attr('src', lcSource);
      loDialogImg.attr('alt', lcAlt);
      loDialogImg.attr('title', lcTitle);

      var loContainer = loDialogImg.parent();
      var lnPaddingWidth = parseFloat(loContainer.css('padding-left')) + parseFloat(loContainer.css('padding-right')) + parseFloat(loContainer.css('margin-left')) + parseFloat(loContainer.css('margin-right'));
      var lnPaddingHeight = parseFloat(loContainer.css('padding-top')) + parseFloat(loContainer.css('padding-bottom')) + parseFloat(loContainer.css('margin-top')) + parseFloat(loContainer.css('margin-bottom'));

      lnPaddingWidth += parseFloat(loDialog.css('border-top-left-radius')) + parseFloat(loDialog.css('border-top-right-radius'));
      lnPaddingHeight += parseFloat(loDialog.css('border-bottom-left-radius')) + parseFloat(loDialog.css('border-top-left-radius'));

      // For title bar
      lnPaddingHeight += Beo.fnDialogImageTitleBarHeight;

      // So that on a mobile or any small screen, the dialog box won't fill the absolute
      // entire screen. Leaves a small border.
      lnPaddingWidth += 10;
      lnPaddingHeight += 20;

      // This technique is used to get the actual image size.
      var loLoadImage = new Image();
      loLoadImage.onload = function ()
      {
        var lnWidth = this.width * 1.0;
        var lnHeight = this.height * 1.0;

        // Subtract estimated borders and such.
        var lnWidthRatio = (jQuery(window).width() - lnPaddingWidth) / lnWidth;
        var lnHeightRatio = (jQuery(window).height() - lnPaddingHeight) / lnHeight;

        var lnMinRatio = Math.min(lnHeightRatio, lnWidthRatio);

        if (lnMinRatio < 1.0)
        {
          lnHeight *= lnMinRatio;
          lnWidth *= lnMinRatio;
        }

        loDialogImg.css('width', lnWidth + 'px');
        loDialogImg.css('height', lnHeight + 'px');

        loDialog.dialog("option", "title", lcTitle);
        loDialog.dialog("open");
      };

      // Now load the image so the above loLoadImage.onload fires.
      loLoadImage.src = lcSource;

    },

    // -------------------------------------------------------------------------------------------------------------------
    createImageDialog: function ()
    {
      var lcDialog = 'BeoDialogForImage';
      var lcDialogImg = 'BeoDialogForImageImg';

      jQuery('body').append('<div id="' + lcDialog + '" style="display: none;"><img id="' + lcDialogImg + '" alt="" /></div>');

      Beo.foDialogImage = jQuery("#" + lcDialog);
      Beo.foDialogImageImg = jQuery("#" + lcDialogImg);

      // Unfortunately, I tried querying css('width'): it returns a width in
      // pixels. So the below is the actual default: I verified in the
      // jquery.ui.css file.
      var lcDefaultTitleCSS = '90%';

      // Shadowbox advice from http://stackoverflow.com/questions/3448813/jqueryui-how-to-make-a-shadow-around-a-dialog-box
      Beo.foDialogImage.dialog({
        width: 'auto',
        height: 'auto',
        modal: true,
        autoOpen: false,
        draggable: false,
        resizable: false,
        show: {
          effect: 'fade',
          duration: 300
        },
        hide: {
          effect: Beo.fcImageDialogFadeEffect,
          duration: 400
        },
        open: function (toEvent, toUI)
        {
          // This step is needed: if the title is wider than the image
          // then there will be empty space to the right of the image.
          var loTitleSpan = jQuery(this).parent().find('span.ui-dialog-title');
          var lnTitleWidth = loTitleSpan.width();
          var lnWidth = parseFloat(jQuery(this).parent().find('img').css('width'));

          if ((lnTitleWidth + 50) > lnWidth)
          {
            var lnNewWidth = lnWidth - 50;
            // 120 is really small. . . .
            if (lnNewWidth < 120)
            {
              lnNewWidth = 120;
            }

            loTitleSpan.css('width', lnNewWidth + 'px');
          }
        },
        beforeClose: function (toEvent, toUI)
        {
          // Reset to the default so subsequent dialog opens with
          // larger images aren't effected.
          var loTitleSpan = jQuery(this).parent().find('span.ui-dialog-title');
          loTitleSpan.css('width', lcDefaultTitleCSS);
        }
      });

    },

    // -------------------------------------------------------------------------------------------------------------------
    // From https://drupal.org/node/249933
    disableFormReturn: function ()
    {
      jQuery('input').keypress(function (toEvent)
      {
        return ((toEvent.keyCode == 13) ? false : true);
      });
    },
    // -------------------------------------------------------------------------------------------------------------------
    isWindowInIFrame: function ()
    {
      return (window.location != window.parent.location);
    }
    //----------------------------------------------------------------------------------------------------
  };

//----------------------------------------------------------------------------------------------------
//----------------------------------------------------------------------------------------------------
//----------------------------------------------------------------------------------------------------