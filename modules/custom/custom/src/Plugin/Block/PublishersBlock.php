<?php

// From http://valuebound.com/resources/blog/drupal-8-how-to-create-a-custom-block-programatically

namespace Drupal\custom\Plugin\Block;

use Drupal\Core\Block\BlockBase;

//-------------------------------------------------------------------------------------------------
//-------------------------------------------------------------------------------------------------
//-------------------------------------------------------------------------------------------------

/**
 * Provides a 'publishers' block.
 *
 * @Block(
 *   id = "publishers_block",
 *   admin_label = @Translation("Publishers Block"),
 *   category = @Translation("Custom block for displaying the publishers of Content Writing.")
 * )
 */
class PublishersBlock extends BlockBase
{

  //-------------------------------------------------------------------------------------------------
  /**
   * {@inheritdoc}
   */
  public function build()
  {
    $lcContent = '';

    $lcField = 'field_publishers';
    // From https://drupal.stackexchange.com/questions/145823/how-do-i-get-the-current-node-id
    $loNode = \Drupal::routeMatch()->getParameter('node');

    if ((!$loNode) || (!$loNode->hasField($lcField)))
    {
      return (array(
        '#type' => 'markup',
        '#cache' => array('max-age' => 0),
        '#markup' => $lcContent,
      ));
    }

    $loField = $loNode->get($lcField);

    // From https://stackoverflow.com/questions/3997336/explode-php-string-by-new-line
    $laContent = preg_split("/\\r\\n|\\r|\\n/", $loField->value);

    $lnCount = count($laContent);

    if ($lnCount === 0)
    {
      return (array(
        '#type' => 'markup',
        '#cache' => array('max-age' => 0),
        '#markup' => $lcContent,
      ));
    }

    $lcContent .= "<div id='publishers_block' style='overflow: hidden; clear: both;'>\n";

    $lcContent .= "<hr />\n";
    $lcContent .= "<h4>Publishers</h4>\n";

    $lcContent .= "<ul>\n";
    for ($i = 0; $i < $lnCount; ++$i)
    {
      $lcUrl = trim($laContent[$i]);
      if (empty($lcUrl))
      {
        continue;
      }

      $lcTitle = 'Unknown';
      if (stripos($lcUrl, 'amazon'))
      {
        $lcTitle = 'Amazon';
      }
      else if (stripos($lcUrl, 'svspress'))
      {
        $lcTitle = 'SVS Press';
      }
      else if (stripos($lcUrl, 'barnesandnoble'))
      {
        $lcTitle = 'Barnes &amp; Noble';
      }
      else if (stripos($lcUrl, 'bakerpublishinggroup'))
      {
        $lcTitle = 'Baker Publishing Group';
      }
      else if (stripos($lcUrl, 'nowyouknowmedia'))
      {
        $lcTitle = 'Now You Know Media';
      }

      $lcContent .= "<li>$lcTitle\n";
      $lcContent .= "<ul><li><a href='$lcUrl' target='_blank'>$lcUrl</a></li></ul>\n";
      $lcContent .= "</li>\n";
    }

    $lcContent .= "</ul>\n";
    $lcContent .= "</div>\n";

    // From https://drupal.stackexchange.com/questions/199527/how-do-i-correctly-setup-caching-for-my-custom-block-showing-content-depending-o
    return (array(
      '#type' => 'markup',
      '#cache' => array('max-age' => 0),
      '#markup' => $lcContent,
    ));

  }
  //-------------------------------------------------------------------------------------------------

}

//-------------------------------------------------------------------------------------------------
//-------------------------------------------------------------------------------------------------
//-------------------------------------------------------------------------------------------------
