<?php

/**
 * SIMPLE-Scraper-XML-HTML
 *
 * PHP version 5.3
 *
 *  @category HTML_Tools
 *  @package  HTML
 *  @author   Jorge Ivan Botero Hernandez <jibotero@msn.com>
 *  @license  https://github.com/botero/SIMPLE-Scraper-XML-HTML/blob/master/licence.txt GPL V3 License
 *  @link     https://github.com/botero/SIMPLE-Scraper-XML-HTML
 *
 */

/**
 * this class is a simple and fast, with no dependency, scraping tool for XML
 * and HTML files, require PHP 5.3 or later and it's PSR1 standard compliant
 *
 *  @category HTML_Tools
 *  @package  HTML
 *  @author   Jorge Ivan Botero Hernandez <jibotero@msn.com>
 *  @license  https://github.com/botero/SIMPLE-Scraper-XML-HTML/blob/master/licence.txt GPL V3 License
 *  @link     https://github.com/botero/SIMPLE-Scraper-XML-HTML
 *
 */

interface IScraper
{
    public function execute($rules, $html);

}
