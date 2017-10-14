// Updated on March 12, 2017
//----------------------------------------------------------------------------------------------------
//----------------------------------------------------------------------------------------------------
//----------------------------------------------------------------------------------------------------

var Routines =
  {
    CONTACT_BLOCK: "#block-contactblock",

    //----------------------------------------------------------------------------------------------------
    initializeRoutines: function ()
    {
      Beo.initializeBrowserFixes();

      // Google Analytics code.
      (function (i, s, o, g, r, a, m)
      {
        i['GoogleAnalyticsObject'] = r;
        i[r] = i[r] || function ()
          {
            (i[r].q = i[r].q || []).push(arguments)
          }, i[r].l = 1 * new Date();
        a = s.createElement(o),
          m = s.getElementsByTagName(o)[0];
        a.async = 1;
        a.src = g;
        m.parentNode.insertBefore(a, m)
      })(window, document, 'script', 'https://www.google-analytics.com/analytics.js', 'ga');

      ga('create', 'UA-100906746-1', 'auto');
      ga('send', 'pageview');

    },

    //----------------------------------------------------------------------------------------------------
    setupTabs: function ()
    {
      jQuery("#pb_tabs").tabs({
        show: {effect: "fadeIn", duration: 400},
        hide: {effect: "fadeOut", duration: 400}
      });

    },

    //----------------------------------------------------------------------------------------------------
    setupFlexSlider: function ()
    {
      var loSliderImages = jQuery("#pb_image_list .flexslider");

      // FlexSlide setup should come before Beo.setupImageDialogBox. Otherwise, there appears
      // to be some issues with the first image linking to the image and not Beo.setupImageDialogBox.
      // Strange. . . .

      if (loSliderImages.length > 0)
      {
        if (loSliderImages.find("a.dialogbox-image").length > 0)
        {
          alert("Routines.setupFlexSlider must be run before Beo.setupImageDialogBox.");
          return;
        }

        loSliderImages.flexslider(
          {
            slideshow: false,
            directionNav: (jQuery(window).width() >= 768),
            prevText: "",
            nextText: "",
            controlNav: false,
            animation: "slide",
            animationLoop: true,
          });
      }

      var loSliderBookImages = jQuery("#pb_custom_book_carousel .flexslider");

      if (loSliderBookImages.length > 0)
      {
        if (loSliderBookImages.find("a.dialogbox-image").length > 0)
        {
          alert("Routines.setupFlexSlider must be run before Beo.setupImageDialogBox.");
          return;
        }

        loSliderBookImages.flexslider(
          {
            slideshow: false,
            directionNav: (jQuery(window).width() >= 768),
            prevText: "",
            nextText: "",
            controlNav: false,
            animation: "slide",
            animationLoop: true,
            itemWidth: 160,
            itemMargin: 1,
            minItems: 2,
            maxItems: 4,
            move: 1
          });
      }

    },

    //----------------------------------------------------------------------------------------------------
    showHiddenContent: function ()
    {
      jQuery("#pb_custom_text_list").fadeIn();
      jQuery("#pb_custom_text_tabs").fadeIn();
      jQuery("#pb_custom_image").fadeIn();
      jQuery("#pb_custom_book_carousel").fadeIn();
      jQuery(".field--name-field-image").fadeIn();

      // Now make one of the quotes appear, if they are on the page.
      var loQuotes = jQuery("#pb_custom_quotes .pb_quote_row");
      var lnCount = loQuotes.length;

      if (lnCount > 0)
      {
        var lnVisible = Math.floor(Math.random() * lnCount);

        // Use eq() not get(): get() returns a DOM element.
        loQuotes.eq(lnVisible).fadeIn();
      }
    },

    //----------------------------------------------------------------------------------------------------
    setupWatermarks: function ()
    {
      var lcForm = this.CONTACT_BLOCK;
      if (jQuery(lcForm).length == 0)
      {
        return;
      }

      Beo.setupWatermark(lcForm + " #edit-name", "Your Name");
      Beo.setupWatermark(lcForm + " #edit-mail", "Your@E-mail.com");
      Beo.setupWatermark(lcForm + " #edit-subject-0-value", "Subject");
      Beo.setupWatermark(lcForm + " #edit-message-0-value", "Message");

    },
    //----------------------------------------------------------------------------------------------------
    // Unfortunately, when I tried to place the Contact Form block programmatically,
    // the <script src="https://www.google.com/recaptcha/api.js?hl=en" async defer></script>
    // would not get added to the page, which kind of makes sense. I had
    // to manually add to html.html.twig, which seems odd. In addition, the "Send yourself a copy" wouldn't appear.
    // So now I just move it if the form exists on the page.
    moveContactForm: function ()
    {
      var loContactForm = jQuery(this.CONTACT_BLOCK);
      if (loContactForm.length == 0)
      {
        return;
      }

      var loCustomImage = jQuery("#pb_custom_image");
      loCustomImage.after(loContactForm);
      loCustomImage.after("<hr />");
    },
    //----------------------------------------------------------------------------------------------------
  }

//----------------------------------------------------------------------------------------------------
//----------------------------------------------------------------------------------------------------
//----------------------------------------------------------------------------------------------------
