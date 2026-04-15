<?php

namespace App\Extension;

use App\Utilities\Twig;
use App\Utilities\Num2Txt;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class TwigExtension extends AbstractExtension {
    public function getFilters() {
		return [
			new TwigFilter('json_decode', function ($string) {
				return json_decode($string);
			}),
			new TwigFilter('catalog', function($value, $code) {
				if (!isset(Twig::$store[$code])) {
					return '';
				}
		
				$items = Twig::$store[$code];
		
				return isset($items[$value]) ? $items[$value] : '';
			}),
			new TwigFilter('image_b64', function($image, $mime='') {
				if (empty($mime)) {
					$mime = Twig::getMimeType($image);
				}
				$content = base64_encode($image);
		
				return 'data:image/'.$mime.';base64,'.$content;
			}),
			new TwigFilter('n_format', function($number, $decimals = 2) {
				return number_format((float) $number, $decimals);
			})
		];
        // return [
        //     new TwigFilter('catalog', ['Greenter\Report\Filter\DocumentFilter', 'getValueCatalog']),
        //     new TwigFilter('image_b64', ['Greenter\Report\Filter\ImageFilter', 'toBase64']),
        //     new TwigFilter('n_format', ['Greenter\Report\Filter\FormatFilter', 'number']),
        // ];
    }

    public function getFunctions() {
		return [
			new TwigFunction('num2Txt', function($num) {
				return 'SON ' . strtoupper((new Num2Txt())->toString($num)) . '/100 SOLES';
			}),
			new TwigFunction('legend', function($legends, $code) {
				foreach ($legends as $legend) {
					if ($legend->getCode() == $code) {
						return $legend->getValue();
					}
				}
		
				return '';
			}),
			new TwigFunction('qrCode', function($sale) {
				// $client = $sale->getClient();
				// $params = [
				//     $sale->getCompany()->getRuc(),
				//     $sale->getTipoDoc(),
				//     $sale->getSerie(),
				//     $sale->getCorrelativo(),
				//     number_format($sale->getMtoIGV(), 2, '.', ''),
				//     number_format($sale->getMtoImpVenta(), 2, '.', ''),
				//     $sale->getFechaEmision()->format('Y-m-d'),
				//     $client->getTipoDoc(),
				//     $client->getNumDoc(),
				// ];
				// $content = implode('|', $params).'|';
		
				// return $this->getQrImage($content);
				return "fix this";
			})
		];
        // return [
        //     new TwigFunction('legend', ['Greenter\Report\Filter\ResolveFilter', 'getValueLegend']),
        //     new TwigFunction('qrCode', ['Greenter\Report\Render\QrRender', 'getImage']),
        // ];
    }
}
