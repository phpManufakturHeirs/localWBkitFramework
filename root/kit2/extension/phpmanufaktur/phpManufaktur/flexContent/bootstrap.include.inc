<?php

/**
 * flexContent
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/flexContent
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

// permalink route to a content
$app->get('/{language}/content/{name}',
    'phpManufaktur\flexContent\Control\PermanentLink::ControllerContentName')
    ->value('language', 'de');

// permalink route to a category
$app->get('/{language}/content/category/{name}',
    'phpManufaktur\flexContent\Control\PermanentLink::ControllerCategoryName')
    ->value('language', 'de');

// permalink route to a tag
$app->get('/{language}/content/buzzword/{name}',
    'phpManufaktur\flexContent\Control\PermanentLink::ControllerTagName')
    ->value('language', 'de');

// permalink route to a list (content, category, tag ...)
$app->get('/{language}/content/list/{name}',
    'phpManufaktur\flexContent\Control\PermanentLink::ControllerListName')
    ->value('language', 'de');

// permalink route to a FAQ
$app->get('/{language}/content/faq/{name}',
    'phpManufaktur\flexContent\Control\PermanentLink::ControllerFAQName')
    ->value('language', 'de');

// permalink route to a RSS Channel
$app->get('/{language}/rss/{channel}',
    'phpManufaktur\flexContent\Control\PermanentLink::ControllerRSSChannel')
    ->value('language', 'de');
