<?php

/**
 * kitFramework::Basic
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de
 * @copyright 2013 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 *
 */

namespace phpManufaktur\Basic\Control\kitFilter;

use Silex\Application;

class MailHide extends BasicFilter
{
    public function exec(Application $app)
    {
        $this->initClass($app);

        if (!$app['recaptcha']->MailHideIsActive()) {
            // MailHide is not active, remove the filter expression and return the content
            $this->removeExpression();
            return $this->getContent();
        }

        // first search part to find all mailto email addresses
        $pattern = '#(<a[^<]*href\s*?=\s*?"\s*?mailto\s*?:\s*?)([A-Z0-9._%+-]+@(?:[A-Z0-9-]+\.)+[A-Z]{2,4})([^"]*?)"([^>]*>)(.*?)</a>';
        // second part to find all non mailto email addresses
        $pattern .= '|(value\s*=\s*"|\')??\b([A-Z0-9._%+-]+@(?:[A-Z0-9-]+\.)+[A-Z]{2,4})\b#i';

        preg_match_all($pattern, self::$content, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            if (count($match) == 6) {
                $title = preg_match("/^([0-9a-zA-Z]+[-._+&])*[0-9a-zA-Z]+@([-0-9a-zA-Z]+[.])+[a-zA-Z]{2,6}$/i", $match[5]) ? '' : trim($match[5]);
                $replace = $app['recaptcha']->MailHideGetHTML($match[2], $title);
                $this->replace($match[0], $replace);
            }
            elseif (count($match) == 8) {
                $replace = $app['recaptcha']->MailHideGetHTML($match[7]);
                $this->replace($match[0], $replace);
            }
        }
        $this->removeExpression();
        return $this->getContent();
    }
}
