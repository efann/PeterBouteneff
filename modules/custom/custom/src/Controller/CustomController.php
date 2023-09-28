<?php


namespace Drupal\custom\Controller;

use Drupal\taxonomy\Entity\Term;
use Drupal\views\Views;

//-------------------------------------------------------------------------------------------------
//-------------------------------------------------------------------------------------------------
//-------------------------------------------------------------------------------------------------
class CustomController
{
  const NO_DATA = 'Not much data to show here. . . .';

  const DATA_TEXT = 'content_text';
  const DATA_IMAGE = 'content_image';
  const DATA_WRITING = 'content_writing';

  const DATA_CONTACT_FORM = 'content_contact_form';

  const CATEGORY_CONTACT = 'Contact';
  const CATEGORY_WRITING = 'Writing';
  const CATEGORY_QUOTE = 'Quotes';
  const CATEGORY_HOME = 'Home';

  const VIEW_BLOCK_WEIGHTS = 'block_nodes_by_weight';

  private $faTexts = [];
  private $faImages = [];
  private $faQuotes = [];
  private $faBookCarousel = [];

  // From drupal-8.3.3\core\modules\views\src\Plugin\views\field\FieldPluginBase.php
  private $faTrimTextOptions = ['max_length' => '800', 'word_boundary' => true, 'ellipsis' => true, 'html' => true];

  private $flUseTabs = false;

  private $fcCategory = '';
  // The controller method receives these parameters as arguments.
  // The parameters are mapped to the arguments with the same name.
  // So in this case, the page method of the NodeController has one argument: $tcCustomCategory. There may be multiple parameters in a
  // route, but their names should be unique.
  //-------------------------------------------------------------------------------------------------
  public function getContent($tcCustomCategory)
  {
    $loViewExecutableFirst = Views::getView('content_management');
    if (!is_object($loViewExecutableFirst))
    {
      return array(
        '#type' => 'markup',
        '#markup' => t(self::NO_DATA),
      );
    }

    $this->fcCategory = $tcCustomCategory;

    if (!$this->loadStandardVariables($loViewExecutableFirst))
    {
      return array(
        '#type' => 'markup',
        '#markup' => t(self::NO_DATA),
      );
    }

    // Apparently, you have to reload the View or getView. Otherwise, it will
    // just give one the previous results. At the moment, one is unable to
    // reset the view.
    $this->loadQuotes(Views::getView('content_management'));
    $this->loadBookCarousel(Views::getView('content_management'));

    return array(
      '#type' => 'markup',
      '#markup' => t($this->buildContent()),
    );
  }

  //-------------------------------------------------------------------------------------------------

  private function getTermID($tcCategory)
  {
    $lnID = -1;

    // From https://drupal.stackexchange.com/questions/225209/load-term-by-name
    $laTerms = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadByProperties(['name' => $tcCategory]);

    if (!$laTerms)
    {
      return ($lnID);
    }

    // reset() rewinds array's internal pointer to the first element and returns the
    // value of the first array element, or FALSE if the array is empty.
    $lnID = (int)reset($laTerms)->id();

    return ($lnID);
  }

  //-------------------------------------------------------------------------------------------------

  public function getTitle()
  {
    $lcValue = trim($this->fcCategory);
    $lcValue = str_replace('-', ' ', $lcValue);
    $lcValue = str_replace('_', ' ', $lcValue);

    return (ucwords($lcValue, " "));
  }

  //-------------------------------------------------------------------------------------------------

  private function getTermField($tnID, $tcField)
  {
    // From https://stackoverflow.com/questions/2172715/try-catch-block-in-php-not-catching-exception
    // Don't forget the back slash exception when using namespaces.
    $lcValue = '';
    try
    {
      $loTerm = Term::load($tnID);

      if ($loTerm->hasField($tcField))
      {
        $loValue = $loTerm->get($tcField);

        $lcValue = $loValue->value;
      }
    }
    catch (\Exception $loErr)
    {
      dpm($loErr);
    }

    return ($lcValue);
  }

  //-------------------------------------------------------------------------------------------------

  private function getUrl($toNode)
  {
    // If the node URL can't be found, then something like /node/<<id>> will be returned.
    return (\Drupal\Core\Url::fromRoute('entity.node.canonical', ['node' => $toNode->id()])->toString());
  }

  //-------------------------------------------------------------------------------------------------

  private function getNodeField($toNode, $tcField)
  {
    $lcValue = '';
    if ($toNode->hasField($tcField))
    {
      $loField = $toNode->get($tcField);

      if ($loField->entity instanceof \Drupal\file\Entity\File)
      {
        $lcPublicValue = $loField->entity->uri->value;
        $lcURL = \Drupal::service('stream_wrapper_manager')->getViaUri($lcPublicValue)->getExternalUrl();

        $laURL = parse_url($lcURL);
        $lcValue = $laURL['path'];
      }
      else
      {
        $lcValue = $loField->value;
      }
    }

    return ($lcValue);
  }

  //-------------------------------------------------------------------------------------------------
  // Quotes for the random list appearing in the Writings will always be an un-formatted list.

  private function loadStandardVariables($toViewExecutable)
  {
    $lnTaxonomyID = $this->getTermID($this->fcCategory);
    if ($lnTaxonomyID < 0)
    {
      return (false);
    }

    $this->flUseTabs = ($this->getTermField($lnTaxonomyID, 'field_use_tabs') > 0);

    $laArgs = [$lnTaxonomyID];

    $toViewExecutable->setArguments($laArgs);
    $toViewExecutable->execute(Self::VIEW_BLOCK_WEIGHTS);

    $lnTrackText = 0;
    $lnTrackImage = 0;

    foreach ($toViewExecutable->result as $lnIndex => $loRow)
    {
      $loNode = $loRow->_entity;
      $lcType = $loNode->getType();

      $laInfo = [];
      $laInfo['type'] = $lcType;
      $laInfo['url'] = $this->getUrl($loNode);
      $laInfo['title'] = $loNode->get('title')->value;
      $laInfo['body'] = $loNode->get('body')->value;
      $laInfo['weight'] = $this->getNodeField($loNode, 'field_weight');
      $laInfo['image'] = $this->getNodeField($loNode, 'field_image');

      if (($lcType === Self::DATA_TEXT) || ($lcType === Self::DATA_WRITING))
      {
        $this->faTexts[$lnTrackText++] = $laInfo;
      }
      elseif ($lcType === Self::DATA_IMAGE)
      {
        $this->faImages[$lnTrackImage++] = $laInfo;
      }
    }

    return (true);
  }

  //-------------------------------------------------------------------------------------------------

  private function loadQuotes($toViewExecutable)
  {
    // Load up the quotes. . . .
    if ($this->fcCategory !== Self::CATEGORY_WRITING)
    {
      return (false);
    }

    $lnQuoteID = $this->getTermID(Self::CATEGORY_QUOTE);
    $laArgs = [$lnQuoteID];

    $toViewExecutable->setArguments($laArgs);
    $toViewExecutable->execute(Self::VIEW_BLOCK_WEIGHTS);

    $lnTrackText = 0;
    foreach ($toViewExecutable->result as $lnIndex => $loRow)
    {
      $loNode = $loRow->_entity;
      $lcType = $loNode->getType();

      $laInfo = [];
      $laInfo['type'] = $lcType;
      $laInfo['url'] = $this->getUrl($loNode);
      $laInfo['title'] = $loNode->get('title')->value;
      $laInfo['body'] = $loNode->get('body')->value;
      $laInfo['weight'] = $this->getNodeField($loNode, 'field_weight');

      if ($lcType === Self::DATA_TEXT)
      {
        $this->faQuotes[$lnTrackText++] = $laInfo;
      }
    }

    return (true);
  }

  //-------------------------------------------------------------------------------------------------

  private function loadBookCarousel($toViewExecutable)
  {
    // Load up the quotes. . . .
    if ($this->fcCategory !== Self::CATEGORY_HOME)
    {
      return (false);
    }

    $lnCarouselID = $this->getTermID(Self::CATEGORY_WRITING);
    $laArgs = [$lnCarouselID];

    $toViewExecutable->setArguments($laArgs);
    $toViewExecutable->execute(Self::VIEW_BLOCK_WEIGHTS);

    $lnTrackWriting = 0;
    foreach ($toViewExecutable->result as $lnIndex => $loRow)
    {
      $loNode = $loRow->_entity;
      $lcType = $loNode->getType();

      $laInfo = [];
      $laInfo['type'] = $lcType;
      $laInfo['url'] = $this->getUrl($loNode);
      $laInfo['title'] = $loNode->get('title')->value;
      $laInfo['body'] = $loNode->get('body')->value;
      $laInfo['weight'] = $this->getNodeField($loNode, 'field_weight');
      $laInfo['image'] = $this->getNodeField($loNode, 'field_image');

      if ($lcType === Self::DATA_WRITING)
      {
        $this->faBookCarousel[$lnTrackWriting++] = $laInfo;
      }
    }

    return (true);
  }

  //-------------------------------------------------------------------------------------------------

  private function buildContent()
  {
    $lcContent = '<div id="custom-content">';

    $lcContent .= $this->buildContentImages();

    $lcContent .= $this->buildQuotes();

    $lcContent .= '<hr />';

    $lcContent .= $this->buildBookCarousel();

    $lcContent .= $this->buildContentTexts();

    $lcContent .= '</div>';

    return ($lcContent);
  }

  //-------------------------------------------------------------------------------------------------

  private function buildContentImages()
  {
    $lcContent = '';

    // Add the image content. . . .
    $lcContent .= "<div id='pb_custom_image'>\n";

    $lnCount = count($this->faImages);
    if ($lnCount > 1)
    {
      $lcContent .= "<div id='pb_image_list' class='col-sm-12' style='text-align: center;'>\n";
      $lcContent .= "<div class='flexslider'>\n";
      $lcContent .= "<ul class='slides'>\n";
      for ($i = 0; $i < $lnCount; ++$i)
      {
        $lcText = $this->faImages[$i]['title'];
        $lcImage = $this->faImages[$i]['image'];

        $lcContent .= "<li><img class='responsive-image-large' src='$lcImage' alt='$lcText' title='$lcText'/></li>\n";
      }
      $lcContent .= "</ul>\n";

      $lcContent .= "</div>\n";
      $lcContent .= "</div>\n";

    }
    elseif ($lnCount == 1)
    {
      $lcText = $this->faImages[0]['title'];
      $lcImage = $this->faImages[0]['image'];

      $lcContent .= "<div class='col-sm-12' style='text-align: center;'><img class='responsive-image-large' src='$lcImage' alt='$lcText' title='$lcText'/></div>\n";
    }
    else
    {
      $lcContent .= self::NO_DATA;
    }

    $lcContent .= "</div>\n";

    return ($lcContent);
  }

  //-------------------------------------------------------------------------------------------------

  private function buildQuotes()
  {
    $lcContent = '';

    $lnCount = count($this->faQuotes);
    if ($lnCount == 0)
    {
      return ($lcContent);
    }

    $lcContent .= "<div id='pb_custom_quotes'>\n";

    for ($i = 0; $i < $lnCount; ++$i)
    {
      $lcBody = $this->faQuotes[$i]['body'];

      $lcContent .= "<div class='col-sm-12 pb_quote_row'>$lcBody</div>\n";
    }

    $lcContent .= "</div>\n";

    return ($lcContent);
  }

  //-------------------------------------------------------------------------------------------------

  private function buildBookCarousel()
  {
    $lcContent = '';

    $lnCount = count($this->faBookCarousel);
    if ($lnCount == 0)
    {
      return ($lcContent);
    }

    $lcContent .= "<div id='pb_custom_book_carousel' class='col-sm-12' style='text-align: center;'>\n";

    $lcContent .= "<div class='flexslider'>\n";
    $lcContent .= "<ul class='slides'>\n";

    for ($i = 0; $i < $lnCount; ++$i)
    {
      $lcText = $this->faBookCarousel[$i]['title'];
      $lcImage = $this->faBookCarousel[$i]['image'];
      $lcUrl = $this->faBookCarousel[$i]['url'];

      $lcContent .= "<li><a href='$lcUrl'><img class='responsive-image-small' src='$lcImage' alt='$lcText' title='$lcText'/></a></li>\n";
    }

    $lcContent .= "</ul>\n";
    $lcContent .= "</div>\n";

    $lcContent .= "</div>\n";

    return ($lcContent);
  }

  //-------------------------------------------------------------------------------------------------

  private function buildContentTexts()
  {
    $lcContent = $this->flUseTabs ? '<div id="pb_custom_text_tabs">' : '<div id="pb_custom_text_list">';
    $lcContent .= "\n";

    $lnCount = count($this->faTexts);

    if ($lnCount == 0)
    {
      // If Home category, then due to the presence of the book carousel, one doesn't need to print
      // any info about no data.
      $lcContent .= ($this->fcCategory === Self::CATEGORY_HOME) ? '' : self::NO_DATA;
    }
    elseif ($this->flUseTabs)
    {
      $lcContent .= "<div id='pb_tabs'>\n";
      $lcContent .= "<ul>";
      for ($i = 0; $i < $lnCount; ++$i)
      {
        $lcText = $this->faTexts[$i]['title'];
        $lcContent .= "<li><a href='#pb_tab-$i'>$lcText</a></li>\n";
      }
      $lcContent .= "</ul>\n";

      for ($i = 0; $i < $lnCount; ++$i)
      {
        $lcBody = $this->faTexts[$i]['body'];
        $lcContent .= "<div id='pb_tab-$i'>$lcBody</div>\n";
      }

      $lcContent .= "</div>\n";
    }
    else
    {
      $lcContent .= "<div id='pb_list'>\n";

      for ($i = 0; $i < $lnCount; ++$i)
      {
        $lcTitle = $this->faTexts[$i]['title'];
        $lcUrl = $this->faTexts[$i]['url'];
        $lcBody = $this->faTexts[$i]['body'];

        $lcSideText = $lcTitle;
        $lcBodyText = $lcBody;

        $lcContent .= "<div style='overflow: hidden;'>\n";

        if ($this->fcCategory === Self::CATEGORY_WRITING)
        {
          $lcImage = $this->faTexts[$i]['image'];
          $lcSideText = "<p><img src='$lcImage' class='responsive-image-small' alt='$lcTitle' title='$lcTitle' /></p>\n";

          $lcBodyText = "<p><a href='$lcUrl'>$lcTitle</a></p>\n";
          $lcBodyText .= \Drupal\views\Plugin\views\field\FieldPluginBase::trimText($this->faTrimTextOptions, $lcBody);
          $lcBodyText .= "<p><a href='$lcUrl'>Read more. . . .</a></p>\n";
        }

        $lcContent .= "<div class='col-sm-3 title'>$lcSideText</div>\n";

        $lcContent .= "<div class='col-sm-9'>$lcBodyText</div>\n";

        $lcContent .= "</div>\n";

        $lcContent .= "<hr />\n";
      }

      $lcContent .= "</div>\n";
    }

    $lcContent .= "</div>\n";

    return ($lcContent);
  }
  //-------------------------------------------------------------------------------------------------

}

//-------------------------------------------------------------------------------------------------
//-------------------------------------------------------------------------------------------------
//-------------------------------------------------------------------------------------------------
