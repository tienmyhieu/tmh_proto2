<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once(__DIR__ . '/defines.php');
require_once(__DIR__ . '/TmhBase.php');
require_once(__DIR__ . '/TmhDomain.php');
require_once(__DIR__ . '/TmhElementTree.php');
require_once(__DIR__ . '/TmhHtml.php');
require_once(__DIR__ . '/TmhJson.php');
require_once(__DIR__ . '/TmhLocale.php');
require_once(__DIR__ . '/TmhOutput.php');
require_once(__DIR__ . '/TmhPdf.php');
require_once(__DIR__ . '/TmhResource.php');
require_once(__DIR__ . '/TmhRender.php');
require_once(__DIR__ . '/TmhRoute.php');
require_once(__DIR__ . '/TmhTransform.php');
require_once(__DIR__ . '/pdf/TCPDF/tcpdf.php');

if (!defined('TMH_MIME_TYPE')) {
    define("TMH_MIME_TYPE", 'html');
}

$tcpdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$domain = new TmhDomain();
$elementTree = new TmhElementTree();
$json = new TmhJson();
$render = new TmhRender();
$html = new TmhHtml($render);
$locale = new TmhLocale();
$pdf = new TmhPdf($render, $tcpdf);
$resource = new TmhResource();
$route = new TmhRoute();
$transform = new TmhTransform($domain, $json, $locale, $resource, $route, TMH_MIME_TYPE);
$output = new TmhOutput($elementTree, $html, $pdf, $transform);
//$base = new TmhBase($transform);
$output->output(TMH_MIME_TYPE);
//echo "<pre>";
//print_r($_SERVER);
//echo "</pre>";
