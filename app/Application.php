<?php
namespace StadiumGrabber;
use DOMElement;
use GuzzleHttp\Client;
use QueryPath\DOMQuery;
use RuntimeException;

/**
 * @author Nikita <nikita.electronick@gmail.com>
 * @created 2019-06-25 10:31
 */

class Application
{
    public function grab($url)
    {
        $source = $this->getSource($url);
        $data = $this->parse($source);
        $this->output($data);
    }

    private function getSource($url)
    {
        $client = new Client(['timeout' => 30.0]);
        $response = $client->get($url);
        if ($response->getStatusCode() !== 200) {
            throw new RuntimeException("Can not load requested url \"{$url}\"");
        }
        return $response->getBody();
    }

    private function parse(\Psr\Http\Message\StreamInterface $source)
    {
        $result = [];
        $qp = html5qp((string)$source, 'ul.products-grid>li.item');
        $iterator = $qp->getIterator();
        foreach ($iterator as $item) {
            /** @var DOMQuery $item */
            $name = $item->attr('data-flow-item-name');
            $price = $item->find("span.price")->text();
            $result[] = [$name, $price];
        }
        return $result;
    }

    private function output($data)
    {
        $handle = fopen("php://output", "w");
        fputcsv($handle, ["Product Name","Price"]);
        foreach ($data as $item) {
            fputcsv($handle, $item);
        }
    }

}